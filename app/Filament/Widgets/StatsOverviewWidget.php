<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ResolvesDateRange;
use App\Models\Customer;
use App\Models\Order;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;
    use ResolvesDateRange;

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        [$from, $to] = $this->getDateRange();

        // Período anterior com a mesma duração
        $diffSeconds = (int) $from->diffInSeconds($to);
        $prevTo      = $from->copy()->subSecond();
        $prevFrom    = $prevTo->copy()->subSeconds($diffSeconds);

        // ── 1. Faturamento ────────────────────────────────────────────────
        $revenue     = (float) Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$from, $to])->sum('total');
        $prevRevenue = (float) Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$prevFrom, $prevTo])->sum('total');

        [$revDesc, $revIcon, $revColor] = $this->trend($revenue, $prevRevenue);

        // ── 2. Ticket médio ───────────────────────────────────────────────
        $paidOrders     = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$from, $to])->count();
        $prevPaidOrders = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$prevFrom, $prevTo])->count();

        $avgTicket     = $paidOrders > 0     ? $revenue     / $paidOrders     : 0;
        $prevAvgTicket = $prevPaidOrders > 0 ? $prevRevenue / $prevPaidOrders : 0;

        [$ticketDesc, $ticketIcon, $ticketColor] = $this->trend($avgTicket, $prevAvgTicket);

        // ── 3. Novos pedidos ──────────────────────────────────────────────
        $totalOrders   = Order::whereBetween('created_at', [$from, $to])->count();
        $pendingOrders = Order::where('payment_status', 'pending')
            ->whereBetween('created_at', [$from, $to])->count();

        // ── 4. Novos clientes ─────────────────────────────────────────────
        $newCustomers = Customer::whereBetween('created_at', [$from, $to])->count();

        $converted = Customer::whereBetween('customers.created_at', [$from, $to])
            ->whereHas('orders', fn ($q) => $q->whereBetween('orders.created_at', [$from, $to]))
            ->count();

        return [
            Stat::make('Faturamento', 'R$ ' . number_format($revenue, 2, ',', '.'))
                ->description($revDesc)
                ->descriptionIcon($revIcon)
                ->icon('heroicon-o-banknotes')
                ->color($revColor),

            Stat::make('Ticket médio', 'R$ ' . number_format($avgTicket, 2, ',', '.'))
                ->description($ticketDesc)
                ->descriptionIcon($ticketIcon)
                ->icon('heroicon-o-receipt-percent')
                ->color($ticketColor),

            Stat::make('Novos pedidos', number_format($totalOrders))
                ->description("{$paidOrders} pagos · {$pendingOrders} aguardando")
                ->icon('heroicon-o-shopping-bag')
                ->color('primary'),

            Stat::make('Novos clientes', number_format($newCustomers))
                ->description("{$converted} fizeram pedido")
                ->icon('heroicon-o-user-plus')
                ->color('info'),
        ];
    }

    /**
     * Retorna [descrição, ícone, cor] baseado na variação entre dois valores.
     *
     * @return array{string, string, string}
     */
    private function trend(float $current, float $previous): array
    {
        if ($previous <= 0) {
            return ['Sem dados do período anterior', 'heroicon-m-minus', 'gray'];
        }

        $pct    = round(($current - $previous) / $previous * 100, 1);
        $signal = $pct >= 0 ? '+' : '';
        $desc   = "{$signal}{$pct}% vs período anterior";

        if ($pct > 0) {
            return [$desc, 'heroicon-m-arrow-trending-up',   'success'];
        }

        if ($pct < 0) {
            return [$desc, 'heroicon-m-arrow-trending-down', 'danger'];
        }

        return [$desc, 'heroicon-m-minus', 'gray'];
    }
}
