<x-filament-panels::page>

    <div class="flex flex-wrap items-end gap-3 mb-6 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        {{-- Filtro de período --}}
        <div>
            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 block mb-1">Período</label>
            <select wire:model.live="period"
                    class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500">
                <option value="7">Últimos 7 dias</option>
                <option value="14">Últimos 14 dias</option>
                <option value="30">Últimos 30 dias</option>
                <option value="month">Mês atual</option>
                <option value="custom">Personalizado</option>
            </select>
        </div>
        @if ($period === 'custom')
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 block mb-1">De</label>
                <input type="date" wire:model.live="startDate"
                       class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 block mb-1">Até</label>
                <input type="date" wire:model.live="endDate"
                       class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200">
            </div>
        @endif

        {{-- Tab top por --}}
        <div class="ml-auto">
            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 block mb-1">Top produtos por</label>
            <select wire:model.live="topBy"
                    class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500">
                <option value="orders">Vendas (un.)</option>
                <option value="revenue">Receita (R$)</option>
                <option value="views">Visualizações</option>
                <option value="cart">Add to cart</option>
            </select>
        </div>
    </div>

    @php
        $data = $this->getViewData();
        extract($data);
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Top produtos --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top produtos</h3>
            @if ($topProducts->isEmpty())
                <p class="text-sm text-gray-400">Sem dados no período.</p>
            @else
                @php $maxTop = $topProducts->max('total') ?: 1; @endphp
                <div class="space-y-2">
                    @foreach ($topProducts as $p)
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-700 dark:text-gray-300 truncate mr-2">{{ $p->product_name ?? $p->product?->name ?? '—' }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white whitespace-nowrap">{{ number_format($p->total) }}</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full bg-indigo-500" style="width: {{ round($p->total / $maxTop * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Top variantes --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Variantes mais vendidas</h3>
            @if ($topVariants->isEmpty())
                <p class="text-sm text-gray-400">Sem dados no período.</p>
            @else
                <div class="space-y-2">
                    @foreach ($topVariants as $v)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300 truncate mr-2">
                                {{ $v->product_name }} <span class="text-gray-400">— {{ $v->variant_label }}</span>
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white whitespace-nowrap">{{ $v->qty }} un.</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- Funil por produto --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Funil por produto (top 10 por visualizações)</h3>
        @if ($productFunnel->isEmpty())
            <p class="text-sm text-gray-400">Sem visualizações registradas no período.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-500 border-b border-gray-100 dark:border-gray-700">
                            <th class="text-left pb-2 font-medium">Produto</th>
                            <th class="text-right pb-2 font-medium">Views</th>
                            <th class="text-right pb-2 font-medium">Carrinho</th>
                            <th class="text-right pb-2 font-medium">Compras</th>
                            <th class="text-right pb-2 font-medium">View→Cart</th>
                            <th class="text-right pb-2 font-medium">Cart→Buy</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                        @foreach ($productFunnel as $pf)
                            <tr>
                                <td class="py-2 text-gray-800 dark:text-gray-200 font-medium max-w-[200px] truncate">{{ $pf['name'] }}</td>
                                <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($pf['views']) }}</td>
                                <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($pf['add_to_cart']) }}</td>
                                <td class="py-2 text-right font-semibold text-gray-900 dark:text-white">{{ number_format($pf['purchases']) }}</td>
                                <td class="py-2 text-right text-indigo-600 font-semibold">{{ $pf['cart_rate'] }}%</td>
                                <td class="py-2 text-right text-emerald-600 font-semibold">{{ $pf['buy_rate'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Produtos sem venda --}}
    @if ($noSaleProducts->isNotEmpty())
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-300 mb-3">
                Produtos ativos sem venda no período ({{ $noSaleProducts->count() }}{{ $noSaleProducts->count() === 20 ? '+' : '' }})
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach ($noSaleProducts as $np)
                    <span class="text-xs bg-amber-100 dark:bg-amber-800 text-amber-800 dark:text-amber-200 rounded-lg px-2 py-1">
                        {{ $np->name }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

</x-filament-panels::page>
