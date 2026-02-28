<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Estatisticas;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Pages\Page;

class EstatisticasFretesPage extends Page
{
    protected static ?string $cluster         = Estatisticas::class;
    protected static ?string $navigationIcon  = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Fretes';
    protected static ?int    $navigationSort  = 4;
    protected static string  $view            = 'filament.pages.estatisticas-fretes';

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

        // Métodos de frete usados
        $methods = Order::whereBetween('created_at', [$from, $to])
            ->whereNotNull('shipping_method')
            ->selectRaw('shipping_method, COUNT(*) as count, AVG(shipping_cost) as avg_cost, SUM(shipping_cost) as total_cost')
            ->groupBy('shipping_method')
            ->orderByDesc('count')
            ->get();

        $maxMethodCount = $methods->max('count') ?: 1;

        // Frete grátis vs pago
        $freeShipping = Order::whereBetween('created_at', [$from, $to])
            ->where('shipping_cost', 0)->count();
        $paidShipping = Order::whereBetween('created_at', [$from, $to])
            ->where('shipping_cost', '>', 0)->count();
        $shippingTotal = $freeShipping + $paidShipping;

        // Top estados
        $topStates = Order::whereBetween('created_at', [$from, $to])
            ->whereNotNull('shipping_state')
            ->selectRaw('shipping_state, COUNT(*) as count, AVG(shipping_cost) as avg_cost')
            ->groupBy('shipping_state')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $maxStateCount = $topStates->max('count') ?: 1;

        // Top cidades
        $topCities = Order::whereBetween('created_at', [$from, $to])
            ->whereNotNull('shipping_city')
            ->selectRaw('shipping_city, shipping_state, COUNT(*) as count')
            ->groupBy('shipping_city', 'shipping_state')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        return compact(
            'methods', 'maxMethodCount',
            'freeShipping', 'paidShipping', 'shippingTotal',
            'topStates', 'maxStateCount',
            'topCities'
        );
    }
}
