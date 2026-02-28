<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Estatisticas;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Filament\Pages\Page;

class EstatisticasGeralPage extends Page
{
    protected static ?string $cluster         = Estatisticas::class;
    protected static ?string $navigationIcon  = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Geral';
    protected static ?int    $navigationSort  = 1;
    protected static string  $view            = 'filament.pages.estatisticas-geral';

    public string  $period    = '30';
    public ?string $startDate = null;
    public ?string $endDate   = null;

    protected function getDateRange(): array
    {
        return match ($this->period) {
            '7'      => [now()->subDays(7)->startOfDay(),   now()->endOfDay()],
            '14'     => [now()->subDays(14)->startOfDay(),  now()->endOfDay()],
            'month'  => [now()->startOfMonth()->startOfDay(), now()->endOfDay()],
            'custom' => [
                Carbon::parse($this->startDate ?? now()->subDays(30))->startOfDay(),
                Carbon::parse($this->endDate   ?? now())->endOfDay(),
            ],
            default  => [now()->subDays(30)->startOfDay(),  now()->endOfDay()],
        };
    }

    public function getViewData(): array
    {
        [$from, $to] = $this->getDateRange();
        $svc = app(AnalyticsService::class);

        return [
            'stats'   => $svc->generalStats($from, $to),
            'funnel'  => $svc->funnel($from, $to),
            'topProducts' => $svc->topProducts($from, $to, 'orders', 5),
        ];
    }
}
