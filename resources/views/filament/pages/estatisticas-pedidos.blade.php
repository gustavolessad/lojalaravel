<x-filament-panels::page>

    @include('filament.components.period-filter')

    @php
        $data = $this->getViewData();
        extract($data);
    @endphp

    {{-- KPIs topo --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Total de pedidos</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Cancelados / estornados</p>
            <p class="text-2xl font-bold {{ $cancelRate > 10 ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">{{ $cancelled }}</p>
            <p class="text-xs text-gray-400">{{ $cancelRate }}% do total</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Clientes recorrentes</p>
            <p class="text-2xl font-bold text-indigo-600">{{ $recurring }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-gray-500 mb-1">Novos compradores</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $newBuyers }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Por status --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Pedidos por status</h3>
            @if ($byStatusFormatted->isEmpty())
                <p class="text-sm text-gray-400">Sem dados no período.</p>
            @else
                <div class="space-y-2">
                    @foreach ($byStatusFormatted as $s)
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-700 dark:text-gray-300">{{ $s['label'] }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $s['count'] }}</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full bg-indigo-500" style="width: {{ round($s['count'] / $maxStatus * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Por dia da semana --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Pedidos por dia da semana</h3>
            <div class="flex items-end gap-2 h-32">
                @foreach ($dowData as $d)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $d['count'] ?: '' }}</span>
                        <div class="w-full bg-indigo-500 rounded-t transition-all" style="height: {{ max(4, round($d['count'] / $maxDow * 100)) }}px"></div>
                        <span class="text-xs text-gray-500">{{ $d['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- Por hora do dia --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Pedidos por hora do dia</h3>
        <div class="flex items-end gap-1 h-20">
            @foreach ($hourData as $h)
                <div class="flex-1 flex flex-col items-center" title="{{ $h['hour'] }}: {{ $h['count'] }} pedidos">
                    <div class="w-full bg-violet-400 rounded-t"
                         style="height: {{ max(2, round($h['count'] / $maxHour * 60)) }}px"></div>
                </div>
            @endforeach
        </div>
        <div class="flex justify-between text-xs text-gray-400 mt-1">
            <span>0h</span><span>6h</span><span>12h</span><span>18h</span><span>23h</span>
        </div>
    </div>

</x-filament-panels::page>
