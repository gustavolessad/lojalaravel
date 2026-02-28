<x-filament-panels::page>

    @include('filament.components.period-filter')

    @php
        ['stats' => $stats, 'funnel' => $funnel, 'topProducts' => $topProducts] = $this->getViewData();
    @endphp

    {{-- KPIs --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        @php
        $kpis = [
            ['label' => 'Faturamento',    'value' => 'R$ ' . number_format($stats['revenue'], 2, ',', '.'),  'icon' => 'heroicon-o-banknotes',          'color' => 'emerald'],
            ['label' => 'Pedidos',        'value' => number_format($stats['orders']),                         'icon' => 'heroicon-o-shopping-bag',       'color' => 'indigo'],
            ['label' => 'Pedidos pagos',  'value' => number_format($stats['paid']),                           'icon' => 'heroicon-o-check-circle',       'color' => 'green'],
            ['label' => 'Ticket médio',   'value' => 'R$ ' . number_format($stats['avgTicket'], 2, ',', '.'), 'icon' => 'heroicon-o-receipt-percent',    'color' => 'amber'],
            ['label' => 'Clientes novos', 'value' => number_format($stats['customers']),                      'icon' => 'heroicon-o-user-plus',          'color' => 'blue'],
        ];
        @endphp

        @foreach ($kpis as $kpi)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $kpi['label'] }}</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $kpi['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Funil de conversão --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Funil de conversão</h3>
            <div class="space-y-3">
                @php $colors = ['indigo','blue','violet','purple','green']; @endphp
                @foreach ($funnel as $i => $step)
                    @php $c = $colors[$i] ?? 'gray'; @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-700 dark:text-gray-300">{{ $step['label'] }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ number_format($step['count']) }}
                                @if ($i > 0)
                                    <span class="text-{{ $c }}-500 ml-1">{{ $step['pct'] }}%</span>
                                @endif
                            </span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full bg-{{ $c }}-500" style="width: {{ min($step['pct'], 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Top produtos por pedidos --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top 5 produtos (por vendas)</h3>
            @if ($topProducts->isEmpty())
                <p class="text-sm text-gray-400">Sem dados no período.</p>
            @else
                <div class="space-y-2">
                    @foreach ($topProducts as $p)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300 truncate mr-2">{{ $p->product_name }}</span>
                            <span class="font-semibold text-gray-900 dark:text-white whitespace-nowrap">{{ number_format($p->total) }} un.</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

</x-filament-panels::page>
