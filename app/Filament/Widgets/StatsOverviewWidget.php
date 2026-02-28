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

        $revenue = (float) Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$from, $to])
            ->sum('total');

        $totalOrders = Order::whereBetween('created_at', [$from, $to])->count();

        $newCustomers = Customer::whereBetween('created_at', [$from, $to])->count();

        $paidOrders = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$from, $to])->count();

        $avgTicket = $paidOrders > 0 ? $revenue / $paidOrders : 0;

        return [
            Stat::make('Faturamento', 'R$ ' . number_format($revenue, 2, ',', '.'))
                ->description('Pedidos pagos no período')
                ->icon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Novos pedidos', number_format($totalOrders))
                ->description('Pedidos criados no período')
                ->icon('heroicon-o-shopping-bag')
                ->color('primary'),

            Stat::make('Novos clientes', number_format($newCustomers))
                ->description('Cadastros no período')
                ->icon('heroicon-o-user-plus')
                ->color('info'),

            Stat::make('Ticket médio', 'R$ ' . number_format($avgTicket, 2, ',', '.'))
                ->description("Faturamento ÷ {$paidOrders} pedidos pagos")
                ->icon('heroicon-o-receipt-percent')
                ->color('warning'),
        ];
    }
}
