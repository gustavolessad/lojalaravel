<?php

namespace App\Console\Commands;

use App\Models\Sales\Order;
use App\Models\Setting;
use App\Services\Order\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExpiredPixOrders extends Command
{
    protected $signature   = 'orders:cancel-expired-pending
                                {--dry-run : Lista os pedidos que seriam cancelados sem fazer nada}';

    protected $description = 'Cancela pedidos PIX e Boleto com pagamento pendente expirado e restaura o estoque';

    public function handle(OrderService $orderService): int
    {
        $pixExpiredAt    = now()->subHours(24);
        $boletoDueDays   = max(1, (int) Setting::get('payment_boleto_due_days', 3));
        $boletoExpiredAt = now()->subDays($boletoDueDays + 1);

        $query = Order::query()
            ->whereIn('payment_method', ['pix', 'boleto'])
            ->where('payment_status', 'pending')
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->where(function ($q) use ($pixExpiredAt, $boletoExpiredAt) {
                $q->where(function ($q2) use ($pixExpiredAt) {
                    $q2->where('payment_method', 'pix')
                       ->where('created_at', '<', $pixExpiredAt);
                })->orWhere(function ($q2) use ($boletoExpiredAt) {
                    $q2->where('payment_method', 'boleto')
                       ->where('created_at', '<', $boletoExpiredAt);
                });
            });

        $count = $query->count();

        if ($count === 0) {
            $this->info('Nenhum pedido pendente expirado encontrado.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn("Modo dry-run: {$count} pedido(s) seriam cancelados:");
            $query->each(function (Order $order) {
                $method = strtoupper($order->payment_method);
                $this->line("  • #{$order->order_number} [{$method}] — criado em {$order->created_at->format('d/m/Y H:i')} — R$ " . number_format($order->total, 2, ',', '.'));
            });
            return self::SUCCESS;
        }

        $this->info("Cancelando {$count} pedido(s) pendente(s) expirado(s)...");

        $cancelled = 0;
        $failed    = 0;

        $query->each(function (Order $order) use ($orderService, &$cancelled, &$failed) {
            $method = strtoupper($order->payment_method);
            $reason = $order->payment_method === 'pix'
                ? 'PIX não pago dentro de 24 horas — cancelamento automático'
                : 'Boleto não pago após vencimento — cancelamento automático';

            try {
                $orderService->cancelAndRestoreStock(
                    order:  $order,
                    reason: $reason,
                );
                $cancelled++;
                $this->line("  ✓ #{$order->order_number} [{$method}]");

                Log::info('CancelExpiredPendingOrders: pedido cancelado', [
                    'order'      => $order->order_number,
                    'method'     => $order->payment_method,
                    'created_at' => $order->created_at->toIso8601String(),
                    'total'      => $order->total,
                ]);
            } catch (\Throwable $e) {
                $failed++;
                $this->error("  ✗ #{$order->order_number} [{$method}]: {$e->getMessage()}");

                Log::error('CancelExpiredPendingOrders: falha ao cancelar pedido', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        $this->info("Concluído: {$cancelled} cancelado(s), {$failed} erro(s).");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
