<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Estatisticas;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Pages\Page;

class EstatisticasPagamentosPage extends Page
{
    protected static ?string $cluster         = Estatisticas::class;
    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Pagamentos';
    protected static ?int    $navigationSort  = 5;
    protected static string  $view            = 'filament.pages.estatisticas-pagamentos';

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

        // PIX vs cartão
        $methods = Order::whereBetween('created_at', [$from, $to])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $pixCount  = (int) ($methods['pix']->count ?? 0);
        $cardCount = (int) ($methods['credit_card']->count ?? 0);
        $pixRev    = (float) ($methods['pix']->revenue ?? 0);
        $cardRev   = (float) ($methods['credit_card']->revenue ?? 0);
        $total     = $pixCount + $cardCount;

        // Desconto PIX
        $pixWithDiscount = Order::whereBetween('created_at', [$from, $to])
            ->where('payment_method', 'pix')
            ->where('pix_discount', '>', 0)->count();
        $pixDiscountTotal = (float) Order::whereBetween('created_at', [$from, $to])
            ->where('payment_method', 'pix')
            ->sum('pix_discount');

        // Parcelamento (1x a 12x)
        $installmentsRaw = Order::whereBetween('created_at', [$from, $to])
            ->where('payment_method', 'credit_card')
            ->whereNotNull('payment_data')
            ->get()
            ->filter(fn ($o) => isset($o->payment_data['installments']))
            ->groupBy(fn ($o) => (int) $o->payment_data['installments'])
            ->map->count()
            ->sortKeys();

        $maxInstallment = $installmentsRaw->max() ?: 1;

        // Taxa de aprovação cartão
        $cardApproved = Order::whereBetween('created_at', [$from, $to])
            ->where('payment_method', 'credit_card')
            ->where('payment_status', 'paid')->count();
        $cardTotal = Order::whereBetween('created_at', [$from, $to])
            ->where('payment_method', 'credit_card')->count();
        $approvalRate = $cardTotal > 0 ? round($cardApproved / $cardTotal * 100, 1) : 0;

        // PIX expirados (pending há mais de 24h)
        $pixExpired = Order::where('payment_method', 'pix')
            ->where('payment_status', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->count();

        return compact(
            'pixCount', 'cardCount', 'pixRev', 'cardRev', 'total',
            'pixWithDiscount', 'pixDiscountTotal',
            'installmentsRaw', 'maxInstallment',
            'cardApproved', 'cardTotal', 'approvalRate',
            'pixExpired'
        );
    }
}
