<?php

namespace App\Services;

use App\Mail\OrderPlaced;
use App\Mail\PaymentConfirmed;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Services\PaymentCalculator;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    /**
     * Cria um pedido a partir do carrinho atual.
     *
     * @param  Cart           $cart     Carrinho com itens carregados
     * @param  array          $address  Dados do endereço de entrega
     * @param  array          $shipping ['name' => 'PAC', 'price' => 14.90, 'days' => 8]
     * @param  string         $paymentMethod  pix | credit_card | boleto
     * @param  Customer|null  $customer Cliente logado (null = guest)
     * @param  array          $guest    ['name', 'email', 'phone'] — apenas para guests
     * @param  string|null    $notes
     */
    public function createFromCart(
        Cart     $cart,
        array    $address,
        array    $shipping,
        string   $paymentMethod,
        ?Customer $customer = null,
        array    $guest     = [],
        ?string  $notes       = null,
        float    $pixDiscount  = 0.0,
    ): Order {
        // O e-mail é enviado FORA da transaction para não quebrar o pedido
        // caso a fila (Redis) esteja indisponível.
        $order = DB::transaction(function () use ($cart, $address, $shipping, $paymentMethod, $customer, $guest, $notes, $pixDiscount) {

            $subtotal     = (float) $cart->subtotal;
            $shippingCost = (float) $shipping['price'];
            $discount     = (float) ($cart->coupon_discount ?? 0);
            $baseTotal    = max(0, $subtotal - $discount) + $shippingCost;
            $total        = max(0, $baseTotal - $pixDiscount);

            // Cria o pedido
            $order = Order::create([
                'customer_id' => $customer?->id,
                'guest_name'  => $customer ? null : ($guest['name'] ?? null),
                'guest_email' => $customer ? null : ($guest['email'] ?? null),
                'guest_phone' => $customer ? null : ($guest['phone'] ?? null),

                'status'         => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $paymentMethod,

                'shipping_name'       => $address['name'],
                'shipping_phone'      => $address['phone'] ?? null,
                'shipping_zip'        => $address['zip'],
                'shipping_street'     => $address['street'],
                'shipping_number'     => $address['number'],
                'shipping_complement' => $address['complement'] ?? null,
                'shipping_district'   => $address['district'],
                'shipping_city'       => $address['city'],
                'shipping_state'      => $address['state'],

                'shipping_method' => $shipping['name'],
                'shipping_days'   => $shipping['days'],
                'shipping_cost'   => $shippingCost,

                'subtotal'    => $subtotal,
                'discount'     => $discount,
                'pix_discount' => $pixDiscount,
                'total'        => $total,
                'coupon_code' => $cart->coupon_code,

                'notes' => $notes,
            ]);

            // Cria os itens (snapshot)
            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id'    => $item->product_id,
                    'variant_id'    => $item->variant_id,
                    'product_name'  => $item->product->name,
                    'variant_label' => $item->variant?->label,
                    'sku'           => $item->variant?->sku ?? $item->product->sku,
                    'quantity'      => $item->quantity,
                    'unit_price'    => $item->unit_price,
                    'total'         => $item->subtotal,
                ]);

                // Decrementa o estoque
                if ($item->variant) {
                    $item->variant->decrement('stock', $item->quantity);
                } elseif ($item->product->stock !== null) {
                    $item->product->decrement('stock', $item->quantity);
                }
            }

            // Registra uso do cupom
            if ($cart->coupon_code && $discount > 0) {
                $coupon = Coupon::where('code', $cart->coupon_code)->first();
                if ($coupon) {
                    app(CouponService::class)->recordUsage($coupon, $order, $customer, $discount);
                }
            }

            // Limpa o carrinho
            app(CartService::class)->clear();

            return $order;
        });

        // E-mail ao cliente — falha aqui não desfaz o pedido
        try {
            if ($order->buyer_email && $order->buyer_email !== '—') {
                Mail::to($order->buyer_email)->queue(new OrderPlaced($order));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('OrderService: falha ao enfileirar OrderPlaced', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }

        // Notificação no painel admin
        try {
            FilamentNotification::make()
                ->title('Novo pedido recebido')
                ->body("Pedido #{$order->order_number} de {$order->buyer_name} — R$ " . number_format($order->total, 2, ',', '.'))
                ->icon('heroicon-o-shopping-bag')
                ->iconColor('success')
                ->actions([
                    NotificationAction::make('ver')
                        ->label('Ver pedido')
                        ->url(\App\Filament\Resources\OrderResource::getUrl('view', ['record' => $order->id]))
                        ->button(),
                ])
                ->sendToDatabase(User::all());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('OrderService: falha ao enviar notificação admin', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }

        return $order;
    }

    /**
     * Marca o pedido como pago.
     */
    public function markAsPaid(Order $order, string $paymentId, array $paymentData = []): void
    {
        $order->update([
            'status'         => 'processing',
            'payment_status' => 'paid',
            'payment_id'     => $paymentId,
            'payment_data'   => array_merge($order->payment_data ?? [], $paymentData),
            'paid_at'        => now(),
        ]);

        // Envia e-mail de pagamento confirmado (via fila)
        if ($order->buyer_email && $order->buyer_email !== '—') {
            Mail::to($order->buyer_email)->queue(new PaymentConfirmed($order));
        }

        // Notificação no painel admin
        try {
            FilamentNotification::make()
                ->title('Pagamento confirmado')
                ->body("Pedido #{$order->order_number} de {$order->buyer_name} — R$ " . number_format($order->total, 2, ',', '.'))
                ->icon('heroicon-o-check-circle')
                ->iconColor('success')
                ->actions([
                    NotificationAction::make('ver')
                        ->label('Ver pedido')
                        ->url(\App\Filament\Resources\OrderResource::getUrl('view', ['record' => $order->id]))
                        ->button(),
                ])
                ->sendToDatabase(User::all());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('OrderService: falha ao enviar notificação admin (pagamento)', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Salva os dados de pagamento no pedido (ex: QR code PIX).
     */
    public function attachPaymentData(Order $order, string $paymentId, array $data): void
    {
        $order->update([
            'payment_id'   => $paymentId,
            'payment_data' => $data,
        ]);
    }
}
