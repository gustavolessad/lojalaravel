<x-filament-panels::page>

    @include('filament.components.period-filter')

    @php
        $data = $this->getViewData();
        extract($data);
        $pixPct  = $total > 0 ? round($pixCount  / $total * 100) : 0;
        $cardPct = $total > 0 ? round($cardCount / $total * 100) : 0;
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- PIX vs Cartão --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">PIX vs Cartão de crédito</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-emerald-600 font-semibold">PIX</span>
                        <span class="text-gray-700 dark:text-gray-300">{{ $pixCount }} pedidos ({{ $pixPct }}%) — R$ {{ number_format($pixRev, 2, ',', '.') }}</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3">
                        <div class="h-3 rounded-full bg-emerald-500" style="width: {{ $pixPct }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-indigo-600 font-semibold">Cartão</span>
                        <span class="text-gray-700 dark:text-gray-300">{{ $cardCount }} pedidos ({{ $cardPct }}%) — R$ {{ number_format($cardRev, 2, ',', '.') }}</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3">
                        <div class="h-3 rounded-full bg-indigo-500" style="width: {{ $cardPct }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Métricas de aprovação e PIX --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Indicadores</h3>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Taxa de aprovação (cartão)</span>
                    <span class="font-bold {{ $approvalRate >= 80 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $approvalRate }}%</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Pedidos PIX com desconto</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $pixWithDiscount }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Economia total com desconto PIX</span>
                    <span class="font-semibold text-emerald-600">R$ {{ number_format($pixDiscountTotal, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">PIX expirados (> 24h pendentes)</span>
                    <span class="font-semibold {{ $pixExpired > 0 ? 'text-red-600' : 'text-gray-500' }}">{{ $pixExpired }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Distribuição de parcelamento --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Distribuição de parcelamento (cartão)</h3>
        @if ($installmentsRaw->isEmpty())
            <p class="text-sm text-gray-400">Sem pedidos parcelados no período.</p>
        @else
            <div class="flex items-end gap-3 h-32">
                @foreach ($installmentsRaw as $parcela => $count)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $count }}</span>
                        <div class="w-full bg-indigo-500 rounded-t" style="height: {{ max(8, round($count / $maxInstallment * 100)) }}px"></div>
                        <span class="text-xs text-gray-500">{{ $parcela }}×</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</x-filament-panels::page>
