<div
    x-data="{ open: false }"
    @click.outside="open = false"
    class="relative flex-1 max-w-sm"
>
    <form wire:submit="search" @submit="open = false" class="flex items-center">
        <div class="relative w-full">
            <input
                wire:model.live.debounce.300ms="query"
                @input="open = $event.target.value.length >= 2"
                @focus="open = $el.value.length >= 2"
                @keydown.escape="open = false; $el.blur()"
                type="search"
                placeholder="Buscar produtos..."
                autocomplete="off"
                class="w-full pl-4 pr-10 py-3 text-sm rounded-xl bg-gray-100 focus:outline-none focus:ring-1 focus:ring-gray-300 focus:border-transparent transition-all"
            >
            <button
                type="submit"
                class="absolute right-2 top-1/2 -translate-y-1/2 bg-green-600 h-8 w-8 flex items-center justify-center rounded-lg text-white hover:bg-green-700 transition"
                aria-label="Buscar"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                </svg>
            </button>
        </div>
    </form>

    {{-- Dropdown de resultados --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden"
    >
        @if ($this->results->isNotEmpty())
            <ul class="divide-y divide-gray-100">
                @foreach ($this->results as $result)
                    <li>
                        <a
                            href="{{ $result['url'] }}"
                            class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition"
                            @click="open = false"
                        >
                            @if ($result['image'])
                                <img
                                    src="{{ $result['image'] }}"
                                    alt="{{ $result['name'] }}"
                                    class="w-10 h-10 rounded-lg object-cover flex-shrink-0 bg-gray-100"
                                >
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex-shrink-0"></div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900 truncate">{{ $result['name'] }}</p>
                                <p class="text-xs text-indigo-600 font-medium">
                                    R$ {{ number_format($result['price'], 2, ',', '.') }}
                                </p>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="border-t border-gray-100 px-4 py-2 bg-gray-50">
                <a
                    href="/busca?q={{ urlencode(trim($query)) }}"
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium"
                    @click="open = false"
                >
                    Ver todos os resultados para "{{ trim($query) }}" →
                </a>
            </div>
        @elseif (mb_strlen(trim($query)) >= 2)
            <div class="px-4 py-4 text-sm text-gray-500 text-center">
                Nenhum produto encontrado para "{{ trim($query) }}"
            </div>
        @endif
    </div>
</div>
