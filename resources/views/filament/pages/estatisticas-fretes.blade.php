<x-filament-panels::page>

    @include('filament.components.period-filter')

    @php
        $data = $this->getViewData();
        extract($data);
        $freePct = $shippingTotal > 0 ? round($freeShipping / $shippingTotal * 100) : 0;
        $paidPct = $shippingTotal > 0 ? round($paidShipping / $shippingTotal * 100) : 0;
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Métodos de frete --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Métodos de frete utilizados</h3>
            @if ($methods->isEmpty())
                <p class="text-sm text-gray-400">Sem dados no período.</p>
            @else
                <div class="space-y-3">
                    @foreach ($methods as $m)
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $m->shipping_method }}</span>
                                <span class="text-gray-500">
                                    {{ $m->count }} pedidos — média R$ {{ number_format($m->avg_cost, 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full bg-blue-500" style="width: {{ round($m->count / $maxMethodCount * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Frete grátis vs pago --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Frete grátis vs pago</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-emerald-600 font-semibold">Frete grátis</span>
                        <span class="text-gray-700 dark:text-gray-300">{{ $freeShipping }} pedidos ({{ $freePct }}%)</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3">
                        <div class="h-3 rounded-full bg-emerald-500" style="width: {{ $freePct }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-blue-600 font-semibold">Frete pago</span>
                        <span class="text-gray-700 dark:text-gray-300">{{ $paidShipping }} pedidos ({{ $paidPct }}%)</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3">
                        <div class="h-3 rounded-full bg-blue-500" style="width: {{ $paidPct }}%"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Top estados --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top estados por volume</h3>
            @if ($topStates->isEmpty())
                <p class="text-sm text-gray-400">Sem dados no período.</p>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-500 border-b border-gray-100 dark:border-gray-700">
                            <th class="text-left pb-2">Estado</th>
                            <th class="text-right pb-2">Pedidos</th>
                            <th class="text-right pb-2">Frete médio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                        @foreach ($topStates as $s)
                            <tr>
                                <td class="py-1.5 font-medium text-gray-800 dark:text-gray-200">{{ $s->shipping_state }}</td>
                                <td class="py-1.5 text-right text-gray-700 dark:text-gray-300">{{ $s->count }}</td>
                                <td class="py-1.5 text-right text-gray-500">R$ {{ number_format($s->avg_cost, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Top cidades --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top cidades</h3>
            @if ($topCities->isEmpty())
                <p class="text-sm text-gray-400">Sem dados no período.</p>
            @else
                <div class="space-y-2">
                    @foreach ($topCities as $city)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ $city->shipping_city }} <span class="text-gray-400">({{ $city->shipping_state }})</span></span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $city->count }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

</x-filament-panels::page>
