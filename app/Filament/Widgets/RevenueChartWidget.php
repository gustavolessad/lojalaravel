<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ResolvesDateRange;
use App\Models\Sales\Order;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class RevenueChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;
    use ResolvesDateRange;

    protected static ?int $sort = 2;

    protected static ?string $heading = 'Faturamento diário';

    protected static ?string $maxHeight = '260px';

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        [$from, $to] = $this->getDateRange();

        $rows = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw('DATE(paid_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue', 'date');

        $data    = [];
        $labels  = [];
        $current = $from->copy()->startOfDay();

        while ($current->lte($to)) {
            $key     = $current->format('Y-m-d');
            $data[]  = (float) ($rows[$key] ?? 0);
            $labels[] = $current->format('d/m');
            $current->addDay();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Faturamento (R$)',
                    'data'            => $data,
                    'borderColor'     => '#6366f1',
                    'backgroundColor' => 'rgba(99,102,241,0.10)',
                    'fill'            => true,
                    'tension'         => 0.35,
                    'pointRadius'     => 3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales'  => [
                'y' => ['ticks' => ['callback' => "function(v){ return 'R$ '+v.toLocaleString('pt-BR',{minimumFractionDigits:2}) }"]],
            ],
        ];
    }
}
