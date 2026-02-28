<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ResolvesDateRange;
use App\Services\AnalyticsService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class ConversionFunnelWidget extends Widget
{
    use InteractsWithPageFilters;
    use ResolvesDateRange;

    protected static ?int $sort = 4;

    protected static string $view = 'filament.widgets.conversion-funnel';

    protected int | string | array $columnSpan = 1;

    public function getFunnelData(): array
    {
        [$from, $to] = $this->getDateRange();

        return app(AnalyticsService::class)->funnel($from, $to);
    }
}
