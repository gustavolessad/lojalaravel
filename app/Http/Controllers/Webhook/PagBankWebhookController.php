<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Sales\Order;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PagBankWebhookController extends Controller
{
    /**
     * Recebe notificações do PagBank.
     *
     * PIX: reference_id = order.id (int)
     * Cartão: reference_id = order_number (string, pré-gerado)
     *
     * Charge statuses: PAID, AUTHORIZED, DECLINED, CANCELED, IN_ANALYSIS
     */
    public function handle(Request $request, OrderService $orderService): Response
    {
        $data = $request->all();

        Log::info('PagBank webhook received', ['id' => $data['id'] ?? null, 'reference' => $data['reference_id'] ?? null]);

        $charges = $data['charges'] ?? [];
        $charge  = $charges[0] ?? null;

        if (! $charge) {
            // Pode ser notificação de QR Code — buscar por reference_id
            $referenceId = $data['reference_id'] ?? null;

            if (! $referenceId) {
                return response('missing data', 422);
            }

            // Para PIX, verificamos se qr_codes[0].status == PAID
            $qrCodes = $data['qr_codes'] ?? [];
            $qrPaid  = false;

            foreach ($qrCodes as $qr) {
                // Se o amount do QR foi pago, o arranjo no webhook inclui o status
                if (isset($qr['amount']['value'])) {
                    $qrPaid = true;
                    break;
                }
            }

            if (! $qrPaid) {
                return response('ignored', 200);
            }

            $order = Order::find((int) $referenceId);

            if (! $order) {
                Log::warning('PagBank webhook: pedido não encontrado (PIX)', ['reference_id' => $referenceId]);
                return response('order not found', 404);
            }

            if ($order->payment_status !== 'paid') {
                $orderService->markAsPaid($order, $data['id'] ?? '', [
                    'pagbank_event' => 'PIX_PAID',
                ]);

                Log::info('PagBank webhook: pedido PIX marcado como pago', [
                    'order_number' => $order->order_number,
                ]);
            }

            return response('ok', 200);
        }

        // ── Charge notification (cartão ou PIX via charges) ─────────────
        $chargeStatus = $charge['status'] ?? '';
        $referenceId  = $data['reference_id'] ?? null;
        $orderId      = $data['id'] ?? '';

        if (! in_array($chargeStatus, ['PAID', 'AUTHORIZED'], true)) {
            return response('ignored', 200);
        }

        if (! $referenceId) {
            return response('missing reference', 422);
        }

        // Tenta buscar pelo reference_id (order.id para PIX, order_number para cartão)
        $order = Order::find((int) $referenceId);

        if (! $order) {
            $order = Order::where('order_number', $referenceId)->first();
        }

        if (! $order) {
            Log::warning('PagBank webhook: pedido não encontrado', ['reference_id' => $referenceId]);
            return response('order not found', 404);
        }

        if ($order->payment_status !== 'paid') {
            $orderService->markAsPaid($order, $orderId, [
                'pagbank_charge_status' => $chargeStatus,
                'pagbank_charge_id'     => $charge['id'] ?? null,
            ]);

            Log::info('PagBank webhook: pedido marcado como pago', [
                'order_number'  => $order->order_number,
                'charge_status' => $chargeStatus,
            ]);
        }

        return response('ok', 200);
    }
}
