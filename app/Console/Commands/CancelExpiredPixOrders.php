<?php

namespace App\Console\Commands;

use App\Models\Sales\Order;
use App\Services\Order\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExpiredPixOrders extends Command
{
    protected $signature   = 'orders:cancel-expired-pix
                                {--dry-run : Lista os pedidos que seriam cancelados sem fazer nada}';

    protected $description = 'Cancela pedidos PIX com pagamento pendente há mais de 24 horas e restaura o estoque';

    public function handle(OrderService $orderService): int
    {
        $expiredAt = now()->subHours(24);

        $query = Order::query()
            ->where('payment_method',  'pix')
            ->where('payment_status',  'pending')
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->where('created_at', '<', $expiredAt);

        $count = $query->count();

        if ($count === 0) {
            $this->info('Nenhum pedido PIX expirado encontrado.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn("Modo dry-run: {$count} pedido(s) seriam cancelados:");
            $query->each(function (Order $order) {
                $this->line("  • #{$order->order_number} — criado em {$order->created_at->format('d/m/Y H:i')} — R$ " . number_format($order->total, 2, ',', '.'));
            });
            return self::SUCCESS;
        }

        $this->info("Cancelando {$count} pedido(s) PIX expirado(s)...");

        $cancelled = 0;
        $failed    = 0;

        $query->each(function (Order $order) use ($orderService, &$cancelled, &$failed) {
            try {
                $orderService->cancelAndRestoreStock(
                    order:  $order,
                    reason: 'PIX não pago dentro de 24 horas — cancelamento automático',
                );
                $cancelled++;
                $this->line("  ✓ #{$order->order_number}");

                Log::info('CancelExpiredPixOrders: pedido cancelado', [
                    'order'      => $order->order_number,
                    'created_at' => $order->created_at->toIso8601String(),
                    'total'      => $order->total,
                ]);
            } catch (\Throwable $e) {
                $failed++;
                $this->error("  ✗ #{$order->order_number}: {$e->getMessage()}");

                Log::error('CancelExpiredPixOrders: falha ao cancelar pedido', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        $this->info("Concluído: {$cancelled} cancelado(s), {$failed} erro(s).");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
