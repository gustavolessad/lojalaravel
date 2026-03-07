<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Sales\Order;
use App\Models\Setting;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    /**
     * Recebe notificações do Mercado Pago.
     *
     * O MercadoPago envia { "action": "payment.updated", "data": { "id": "123" }, "type": "payment" }
     * Fazemos GET /v1/payments/{id} para obter o status real e a external_reference (order.id).
     */
    public function handle(Request $request, OrderService $orderService): Response
    {
        $data = $request->all();

        Log::info('MercadoPago webhook received', [
            'type'   => $data['type'] ?? null,
            'action' => $data['action'] ?? null,
            'data'   => $data['data'] ?? null,
        ]);

        // Só processa notificações de pagamento
        if (($data['type'] ?? '') !== 'payment') {
            return response('ignored', 200);
        }

        $paymentId = $data['data']['id'] ?? null;

        if (! $paymentId) {
            return response('missing payment id', 422);
        }

        // Busca detalhes do pagamento na API
        $token    = (string) Setting::get('payment_mercadopago_token', '');
        $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
            ->timeout(15)
            ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

        if (! $response->successful()) {
            Log::warning('MercadoPago webhook: falha ao buscar pagamento', [
                'payment_id' => $paymentId,
                'status'     => $response->status(),
            ]);
            return response('failed to fetch payment', 500);
        }

        $payment          = $response->json();
        $status           = $payment['status'] ?? '';
        $externalRef      = $payment['external_reference'] ?? '';

        if ($status !== 'approved') {
            Log::info('MercadoPago webhook: status ignorado', [
                'payment_id' => $paymentId,
                'status'     => $status,
            ]);
            return response('ignored', 200);
        }

        // Busca o pedido pela external_reference (order.id ou order_number)
        $order = Order::find((int) $externalRef);

        if (! $order) {
            $order = Order::where('order_number', $externalRef)->first();
        }

        if (! $order) {
            Log::warning('MercadoPago webhook: pedido não encontrado', [
                'external_reference' => $externalRef,
                'payment_id'         => $paymentId,
            ]);
            return response('order not found', 404);
        }

        if ($order->payment_status !== 'paid') {
            $orderService->markAsPaid($order, (string) $paymentId, [
                'mercadopago_status'     => $status,
                'mercadopago_payment_id' => $paymentId,
            ]);

            Log::info('MercadoPago webhook: pedido marcado como pago', [
                'order_number' => $order->order_number,
                'payment_id'   => $paymentId,
            ]);
        }

        return response('ok', 200);
    }
}
