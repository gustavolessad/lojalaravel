<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ResolvesDateRange;
use App\Services\AnalyticsService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class RevenueChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;
    use ResolvesDateRange;

    protected static ?int $sort = 3;

    protected static ?string $heading = 'Faturamento diário';

    protected static ?string $maxHeight = '280px';

    protected int | string | array $columnSpan = 2;

    protected function getData(): array
    {
        [$from, $to] = $this->getDateRange();

        $revenue = app(AnalyticsService::class)->revenueByDay($from, $to);

        return [
            'datasets' => [
                [
                    'label'           => 'Faturamento (R$)',
                    'data'            => $revenue->values()->toArray(),
                    'borderColor'     => '#6366f1',
                    'backgroundColor' => 'rgba(99,102,241,0.10)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'pointRadius'     => 3,
                ],
            ],
            'labels' => $revenue->keys()
                ->map(fn (string $d) => \Carbon\Carbon::parse($d)->format('d/m'))
                ->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'ticks' => [
                        'callback' => 'function(v){ return "R$ " + v.toLocaleString("pt-BR", {minimumFractionDigits:2}) }',
                    ],
                ],
            ],
        ];
    }
}
