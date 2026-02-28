<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Estatisticas;
use App\Models\Product;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class EstatisticasProdutosPage extends Page
{
    protected static ?string $cluster         = Estatisticas::class;
    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Produtos';
    protected static ?int    $navigationSort  = 2;
    protected static string  $view            = 'filament.pages.estatisticas-produtos';

    public string  $period    = '30';
    public ?string $startDate = null;
    public ?string $endDate   = null;
    public string  $topBy     = 'orders';

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

        // Top produtos (por tab selecionada)
        $topProducts = $svc->topProducts($from, $to, $this->topBy, 10);

        // Top variantes mais vendidas
        $topVariants = DB::table('order_items')
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('variant_label')
            ->selectRaw('product_name, variant_label, SUM(quantity) as qty, SUM(total) as revenue')
            ->groupBy('product_name', 'variant_label')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        // Produtos sem venda no período
        $soldProductNames = DB::table('order_items')
            ->whereBetween('created_at', [$from, $to])
            ->pluck('product_id')
            ->unique();

        $noSaleProducts = Product::where('active', true)
            ->whereNotIn('id', $soldProductNames)
            ->select('id', 'name', 'slug')
            ->limit(20)
            ->get();

        // Funil por produto (top 10 por visualizações)
        $productFunnel = DB::table('analytics_events')
            ->where('event_type', 'product_viewed')
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('product_id')
            ->selectRaw('product_id, COUNT(*) as views')
            ->groupBy('product_id')
            ->orderByDesc('views')
            ->limit(10)
            ->get()
            ->map(function ($row) use ($from, $to) {
                $addToCart = DB::table('analytics_events')
                    ->where('event_type', 'add_to_cart')
                    ->where('product_id', $row->product_id)
                    ->whereBetween('created_at', [$from, $to])
                    ->count();

                $purchases = (int) DB::table('order_items')
                    ->where('product_id', $row->product_id)
                    ->whereBetween('created_at', [$from, $to])
                    ->sum('quantity');

                $name = DB::table('products')->where('id', $row->product_id)->value('name') ?? "#{$row->product_id}";

                return [
                    'name'       => $name,
                    'views'      => $row->views,
                    'add_to_cart' => $addToCart,
                    'purchases'  => $purchases,
                    'cart_rate'  => $row->views > 0 ? round($addToCart / $row->views * 100, 1) : 0,
                    'buy_rate'   => $addToCart > 0 ? round($purchases / $addToCart * 100, 1) : 0,
                ];
            });

        return compact('topProducts', 'topVariants', 'noSaleProducts', 'productFunnel');
    }
}
