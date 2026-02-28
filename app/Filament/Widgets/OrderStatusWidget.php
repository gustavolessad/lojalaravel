<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatusWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $awaitingPayment = Order::where('status', 'pending')
            ->where('payment_status', 'pending')
            ->count();

        $processing = Order::where('payment_status', 'paid')
            ->whereIn('status', ['processing', 'paid'])
            ->count();

        $shipped = Order::where('status', 'shipped')->count();

        // Produtos simples com estoque baixo
        $lowStockProducts = Product::where('active', true)
            ->whereNotNull('stock')
            ->where('stock', '<=', 5)
            ->count();

        // Variantes com estoque baixo
        $lowStockVariants = ProductVariant::where('active', true)
            ->whereNotNull('stock')
            ->where('stock', '<=', 5)
            ->count();

        $lowStock = $lowStockProducts + $lowStockVariants;

        return [
            Stat::make('Aguardando pagamento', $awaitingPayment)
                ->description('PIX / boleto pendente')
                ->icon('heroicon-o-clock')
                ->color($awaitingPayment > 0 ? 'warning' : 'gray'),

            Stat::make('Em processamento', $processing)
                ->description('Pagos, aguardando envio')
                ->icon('heroicon-o-cog-6-tooth')
                ->color($processing > 0 ? 'info' : 'gray'),

            Stat::make('Enviados', $shipped)
                ->description('Pedidos em trânsito')
                ->icon('heroicon-o-truck')
                ->color('success'),

            Stat::make('Estoque baixo', $lowStock)
                ->description('Produtos com ≤ 5 unidades')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($lowStock > 0 ? 'danger' : 'success'),
        ];
    }
}
