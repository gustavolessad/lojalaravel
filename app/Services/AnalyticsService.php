<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyticsService
{
    // ── Rastreamento de eventos ───────────────────────────────────────────

    public function track(string $type, array $data = []): void
    {
        try {
            // Deduplicação: product_viewed não repete para o mesmo session+produto em 30 min
            if ($type === 'product_viewed' && isset($data['product_id'])) {
                $exists = AnalyticsEvent::where('event_type', 'product_viewed')
                    ->where('session_id', $data['session_id'] ?? '')
                    ->where('product_id', $data['product_id'])
                    ->where('created_at', '>=', now()->subMinutes(30))
                    ->exists();

                if ($exists) {
                    return;
                }
            }

            AnalyticsEvent::create([
                'event_type'  => $type,
                'session_id'  => $data['session_id'] ?? session()->getId(),
                'customer_id' => $data['customer_id'] ?? null,
                'product_id'  => $data['product_id'] ?? null,
                'variant_id'  => $data['variant_id'] ?? null,
                'order_id'    => $data['order_id'] ?? null,
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            // Não deixa falha de analytics quebrar o fluxo principal
            Log::warning('AnalyticsService: falha ao registrar evento', [
                'type'  => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ── Funil de conversão ────────────────────────────────────────────────

    public function funnel(Carbon $from, Carbon $to): array
    {
        $views     = AnalyticsEvent::where('event_type', 'product_viewed')
            ->whereBetween('created_at', [$from, $to])->count();

        $addToCart = AnalyticsEvent::where('event_type', 'add_to_cart')
            ->whereBetween('created_at', [$from, $to])->count();

        $checkouts = AnalyticsEvent::where('event_type', 'checkout_started')
            ->whereBetween('created_at', [$from, $to])->count();

        $orders    = Order::whereBetween('created_at', [$from, $to])->count();

        $paid      = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$from, $to])->count();

        $base = max($views, 1);

        return [
            ['label' => 'Visualizações de produto', 'count' => $views,     'pct' => 100],
            ['label' => 'Adicionados ao carrinho',  'count' => $addToCart, 'pct' => round($addToCart / $base * 100, 1)],
            ['label' => 'Checkout iniciado',         'count' => $checkouts, 'pct' => round($checkouts / $base * 100, 1)],
            ['label' => 'Pedidos criados',           'count' => $orders,    'pct' => round($orders / $base * 100, 1)],
            ['label' => 'Pedidos pagos',             'count' => $paid,      'pct' => round($paid / $base * 100, 1)],
        ];
    }

    // ── Faturamento diário ────────────────────────────────────────────────

    public function revenueByDay(Carbon $from, Carbon $to): Collection
    {
        $rows = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw('DATE(paid_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue', 'date');

        // Preenche dias sem venda com 0
        $result = collect();
        $current = $from->copy()->startOfDay();

        while ($current->lte($to)) {
            $key = $current->format('Y-m-d');
            $result->put($key, (float) ($rows[$key] ?? 0));
            $current->addDay();
        }

        return $result;
    }

    // ── Top produtos ──────────────────────────────────────────────────────

    /**
     * @param  string $by  'views' | 'cart' | 'orders' | 'revenue'
     */
    public function topProducts(Carbon $from, Carbon $to, string $by = 'orders', int $limit = 10): Collection
    {
        if ($by === 'views') {
            return AnalyticsEvent::where('event_type', 'product_viewed')
                ->whereBetween('created_at', [$from, $to])
                ->whereNotNull('product_id')
                ->selectRaw('product_id, COUNT(*) as total')
                ->groupBy('product_id')
                ->orderByDesc('total')
                ->limit($limit)
                ->with('product:id,name,slug')
                ->get();
        }

        if ($by === 'cart') {
            return AnalyticsEvent::where('event_type', 'add_to_cart')
                ->whereBetween('created_at', [$from, $to])
                ->whereNotNull('product_id')
                ->selectRaw('product_id, COUNT(*) as total')
                ->groupBy('product_id')
                ->orderByDesc('total')
                ->limit($limit)
                ->with('product:id,name,slug')
                ->get();
        }

        // orders ou revenue — usa order_items
        $select = $by === 'revenue'
            ? 'product_name, SUM(total) as total, SUM(quantity) as qty'
            : 'product_name, SUM(quantity) as total, SUM(total) as revenue';

        return DB::table('order_items')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw($select)
            ->groupBy('product_name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    // ── Estatísticas de um produto específico ─────────────────────────────

    public function productStats(int $productId, Carbon $from, Carbon $to): array
    {
        $views = AnalyticsEvent::where('event_type', 'product_viewed')
            ->where('product_id', $productId)
            ->whereBetween('created_at', [$from, $to])->count();

        $addToCart = AnalyticsEvent::where('event_type', 'add_to_cart')
            ->where('product_id', $productId)
            ->whereBetween('created_at', [$from, $to])->count();

        $purchases = DB::table('order_items')
            ->where('product_id', $productId)
            ->whereBetween('created_at', [$from, $to])
            ->sum('quantity');

        $revenue = DB::table('order_items')
            ->where('product_id', $productId)
            ->whereBetween('created_at', [$from, $to])
            ->sum('total');

        $variants = DB::table('order_items')
            ->where('product_id', $productId)
            ->whereNotNull('variant_label')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('variant_label, SUM(quantity) as qty, SUM(total) as revenue')
            ->groupBy('variant_label')
            ->orderByDesc('qty')
            ->get();

        return [
            'views'      => $views,
            'add_to_cart' => $addToCart,
            'purchases'  => (int) $purchases,
            'revenue'    => (float) $revenue,
            'conversion_view_to_cart'    => $views > 0 ? round($addToCart / $views * 100, 1) : 0,
            'conversion_cart_to_purchase' => $addToCart > 0 ? round($purchases / $addToCart * 100, 1) : 0,
            'variants'   => $variants,
        ];
    }

    // ── KPIs gerais ───────────────────────────────────────────────────────

    public function generalStats(Carbon $from, Carbon $to): array
    {
        $revenue   = (float) Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$from, $to])->sum('total');

        $orders    = Order::whereBetween('created_at', [$from, $to])->count();
        $paid      = Order::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$from, $to])->count();

        $avgTicket = $paid > 0
            ? (float) Order::where('payment_status', 'paid')
                ->whereBetween('paid_at', [$from, $to])->avg('total')
            : 0;

        $customers = \App\Models\Customer::whereBetween('created_at', [$from, $to])->count();

        $conversion = $orders > 0 ? round($paid / $orders * 100, 1) : 0;

        return compact('revenue', 'orders', 'paid', 'avgTicket', 'customers', 'conversion');
    }

    // ── Limpeza de eventos antigos ─────────────────────────────────────────

    public function pruneOlderThan(int $months = 13): int
    {
        return AnalyticsEvent::where('created_at', '<', now()->subMonths($months))->delete();
    }
}
