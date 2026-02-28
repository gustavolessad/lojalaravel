<x-filament-widgets::widget class="h-full">
    <x-filament::section heading="Estoque baixo ou zerado" class="h-full">

        @php $items = $this->getLowStockItems(); @endphp

        @if ($items->isEmpty())
            <div class="flex items-center justify-center py-8">
                <p class="text-sm text-gray-400">Nenhum produto com estoque baixo.</p>
            </div>
        @else
            <div class="overflow-y-auto" style="max-height: 320px;">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-white dark:bg-gray-800 z-10">
                        <tr class="text-xs text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                            <th class="text-left pb-2 pr-2 font-medium">Produto</th>
                            <th class="text-left pb-2 pr-2 font-medium">Variante (SKU)</th>
                            <th class="text-right pb-2 font-medium">Estoque</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach ($items as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="py-2 pr-2">
                                    <a href="{{ $item['url'] }}"
                                       class="font-medium text-primary-600 hover:underline truncate block max-w-[180px]">
                                        {{ $item['name'] }}
                                    </a>
                                </td>
                                <td class="py-2 pr-2 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $item['variant'] ?? '—' }}
                                </td>
                                <td class="py-2 text-right">
                                    <span class="inline-flex items-center justify-center w-10 h-6 rounded-md text-xs font-bold
                                        {{ $item['stock'] === 0
                                            ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                            : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                        {{ $item['stock'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </x-filament::section>
</x-filament-widgets::widget>
