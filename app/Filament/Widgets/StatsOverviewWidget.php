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

    protected function getStats(): array
    {
        [$from, $to] = $this->getDateRange();

        $revenue = (float) Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$from, $to])
            ->sum('total');

        $totalOrders = Order::whereBetween('created_at', [$from, $to])->count();

        $paidOrders = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$from, $to])
            ->count();

        $avgTicket = $paidOrders > 0
            ? (float) Order::where('payment_status', 'paid')
                ->whereBetween('paid_at', [$from, $to])
                ->avg('total')
            : 0;

        $newCustomers = Customer::whereBetween('created_at', [$from, $to])->count();

        $conversion = $totalOrders > 0
            ? round($paidOrders / $totalOrders * 100, 1)
            : 0;

        return [
            Stat::make('Faturamento', 'R$ ' . number_format($revenue, 2, ',', '.'))
                ->description('Pedidos pagos no período')
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Pedidos', $totalOrders)
                ->description("{$paidOrders} pagos")
                ->icon('heroicon-o-shopping-bag')
                ->color('primary'),

            Stat::make('Clientes novos', $newCustomers)
                ->description('Cadastros no período')
                ->icon('heroicon-o-user-plus')
                ->color('info'),

            Stat::make('Ticket médio', 'R$ ' . number_format($avgTicket, 2, ',', '.'))
                ->description('Pedidos pagos')
                ->icon('heroicon-o-receipt-percent')
                ->color('warning'),

            Stat::make('Conversão', $conversion . '%')
                ->description('Pedidos pagos / criados')
                ->icon('heroicon-o-arrow-trending-up')
                ->color($conversion >= 50 ? 'success' : ($conversion >= 20 ? 'warning' : 'danger')),
        ];
    }
}
