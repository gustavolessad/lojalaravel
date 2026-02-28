<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AsaasWebhookController extends Controller
{
    /**
     * Eventos tratados:
     *  PAYMENT_RECEIVED  — PIX pago
     *  PAYMENT_CONFIRMED — Cartão aprovado
     */
    public function handle(Request $request, OrderService $orderService): Response
    {
        // ── Validação do token ─────────────────────────────────────────────
        $configuredToken = (string) Setting::get('payment_asaas_webhook_token', '');

        if ($configuredToken !== '') {
            $receivedToken = $request->header('asaas-access-token', '');

            if (! hash_equals($configuredToken, $receivedToken)) {
                Log::warning('Asaas webhook: token inválido', [
                    'ip' => $request->ip(),
                ]);
                return response('unauthorized', 401);
            }
        }

        // ── Processamento do evento ────────────────────────────────────────
        $event   = $request->input('event');
        $payment = $request->input('payment', []);

        Log::info('Asaas webhook', ['event' => $event, 'payment_id' => $payment['id'] ?? null]);

        if (! in_array($event, ['PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED'], true)) {
            return response('ignored', 200);
        }

        $paymentId = $payment['id'] ?? null;
        $orderId   = $payment['externalReference'] ?? null;

        if (! $orderId || ! $paymentId) {
            return response('missing data', 422);
        }

        $order = Order::find((int) $orderId);

        if (! $order) {
            Log::warning('Asaas webhook: pedido não encontrado', ['order_id' => $orderId]);
            return response('order not found', 404);
        }

        // Idempotente: processa apenas se ainda não foi marcado como pago
        if ($order->payment_status !== 'paid') {
            $orderService->markAsPaid($order, $paymentId, [
                'asaas_event'  => $event,
                'asaas_status' => $payment['status'] ?? null,
            ]);

            Log::info('Asaas webhook: pedido marcado como pago', [
                'order_number' => $order->order_number,
                'event'        => $event,
            ]);
        }

        return response('ok', 200);
    }
}
