<?php

namespace App\Jobs;

use App\Mail\AbandonedCart;
use App\Models\Cart\Cart;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Envia e-mail de carrinho abandonado para clientes que:
     * - Estão logados (customer_id not null)
     * - Têm itens no carrinho
     * - O carrinho não foi atualizado nas últimas 2h (mas é menor que 7 dias)
     * - O e-mail ainda não foi enviado para este ciclo (abandoned_cart_sent_at null)
     */
    public function handle(): void
    {
        $carts = Cart::with(['customer', 'items'])
            ->whereNotNull('customer_id')
            ->whereNull('abandoned_cart_sent_at')
            ->whereHas('items')
            ->where('updated_at', '<=', now()->subHours(2))
            ->where('updated_at', '>=', now()->subDays(7))
            ->get();

        foreach ($carts as $cart) {
            if (! $cart->customer || ! $cart->customer->email) {
                continue;
            }

            try {
                Mail::to($cart->customer->email)
                    ->queue(new AbandonedCart($cart->customer, $cart));

                $cart->update(['abandoned_cart_sent_at' => now()]);
            } catch (\Throwable $e) {
                Log::error('SendAbandonedCartEmails: erro ao enviar', [
                    'cart_id' => $cart->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }
}
