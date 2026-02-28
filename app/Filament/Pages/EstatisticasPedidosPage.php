<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Estatisticas;
use App\Models\Customer;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class EstatisticasPedidosPage extends Page
{
    protected static ?string $cluster         = Estatisticas::class;
    protected static ?string $navigationIcon  = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Pedidos';
    protected static ?int    $navigationSort  = 3;
    protected static string  $view            = 'filament.pages.estatisticas-pedidos';

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

        // Pedidos por status
        $byStatus = Order::whereBetween('created_at', [$from, $to])
            ->selectRaw('status, payment_status, COUNT(*) as total')
            ->groupBy('status', 'payment_status')
            ->get();

        $statusMap = [
            'pending'    => 'Pendente',
            'processing' => 'Em processamento',
            'paid'       => 'Pago',
            'shipped'    => 'Enviado',
            'delivered'  => 'Entregue',
            'cancelled'  => 'Cancelado',
            'refunded'   => 'Estornado',
        ];

        $byStatusFormatted = $byStatus->map(fn ($r) => [
            'label' => ($statusMap[$r->status] ?? $r->status)
                . ($r->payment_status !== $r->status ? ' / pag: ' . ($r->payment_status === 'paid' ? 'pago' : $r->payment_status) : ''),
            'count' => $r->total,
        ])->sortByDesc('count')->values();

        $totalOrders = $byStatusFormatted->sum('count');
        $maxStatus   = $byStatusFormatted->max('count') ?: 1;

        // Pedidos por dia da semana (0=dom, 1=seg ...)
        $byDow = Order::whereBetween('created_at', [$from, $to])
            ->selectRaw('DAYOFWEEK(created_at) as dow, COUNT(*) as total')
            ->groupBy('dow')
            ->orderBy('dow')
            ->pluck('total', 'dow');

        $dowLabels = [1 => 'Dom', 2 => 'Seg', 3 => 'Ter', 4 => 'Qua', 5 => 'Qui', 6 => 'Sex', 7 => 'Sáb'];
        $dowData = collect($dowLabels)->map(fn ($label, $dow) => [
            'label' => $label,
            'count' => (int) ($byDow[$dow] ?? 0),
        ])->values();
        $maxDow = $dowData->max('count') ?: 1;

        // Pedidos por hora
        $byHour = Order::whereBetween('created_at', [$from, $to])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('total', 'hour');

        $hourData = collect(range(0, 23))->map(fn ($h) => [
            'hour'  => str_pad($h, 2, '0', STR_PAD_LEFT) . 'h',
            'count' => (int) ($byHour[$h] ?? 0),
        ]);
        $maxHour = $hourData->max('count') ?: 1;

        // Taxa de cancelamento / estorno
        $cancelled = Order::whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['cancelled', 'refunded'])->count();
        $cancelRate = $totalOrders > 0 ? round($cancelled / $totalOrders * 100, 1) : 0;

        // Clientes recorrentes vs novos (no período)
        $customerIds = Order::whereBetween('created_at', [$from, $to])
            ->whereNotNull('customer_id')
            ->pluck('customer_id');

        $recurring = 0;
        $newBuyers = 0;
        foreach ($customerIds->unique() as $cid) {
            $countBefore = Order::where('customer_id', $cid)
                ->where('created_at', '<', $from)->count();
            $countBefore > 0 ? $recurring++ : $newBuyers++;
        }

        return compact(
            'byStatusFormatted', 'totalOrders', 'maxStatus',
            'dowData', 'maxDow',
            'hourData', 'maxHour',
            'cancelled', 'cancelRate',
            'recurring', 'newBuyers'
        );
    }
}
