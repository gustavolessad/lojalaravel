<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Mail\PaymentConfirmed;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AsaasWebhookController extends Controller
{
    /**
     * Eventos tratados:
     *  PAYMENT_RECEIVED  — PIX pago
     *  PAYMENT_CONFIRMED — Cartão aprovado
     */
    public function handle(Request $request, OrderService $orderService): Response
    {
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

        // Só processa se ainda não foi marcado como pago
        if ($order->payment_status !== 'paid') {
            $orderService->markAsPaid($order, $paymentId, [
                'asaas_event' => $event,
                'asaas_status' => $payment['status'] ?? null,
            ]);
        }

        return response('ok', 200);
    }
}
