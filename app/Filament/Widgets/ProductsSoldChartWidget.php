<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ResolvesDateRange;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductsSoldChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;
    use ResolvesDateRange;

    protected static ?int $sort = 3;

    protected static ?string $heading = 'Produtos mais vendidos';

    protected static ?string $maxHeight = '260px';

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        [$from, $to] = $this->getDateRange();

        $products = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.paid_at', [$from, $to])
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as qty')
            ->groupBy('order_items.product_name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label'           => 'Unidades vendidas',
                    'data'            => $products->pluck('qty')->map(fn ($v) => (int) $v)->toArray(),
                    'backgroundColor' => 'rgba(99,102,241,0.75)',
                    'borderColor'     => '#6366f1',
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                ],
            ],
            'labels' => $products->map(fn ($p) => Str::limit($p->product_name, 22))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales'  => [
                'x' => ['ticks' => ['maxRotation' => 35, 'minRotation' => 20]],
                'y' => ['ticks' => ['stepSize' => 1]],
            ],
        ];
    }
}
