<div class="flex gap-8">

    {{-- ═══════════════════════════════════════════════════════════════════
         SIDEBAR DE FILTROS
    ════════════════════════════════════════════════════════════════════ --}}
    <aside class="hidden lg:block w-64 flex-shrink-0">

        {{-- Subcategorias --}}
        @if ($this->subcategories->isNotEmpty())
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">
                    Subcategorias
                </h3>
                <ul class="space-y-1">
                    @foreach ($this->subcategories as $sub)
                        <li>
                            <a href="{{ $sub->url }}"
                               class="flex items-center justify-between text-sm text-gray-600 hover:text-indigo-600 py-1 transition-colors">
                                <span>{{ $sub->name }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <hr class="mb-6 border-gray-200">
        @endif

        {{-- Preço --}}
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Preço</h3>
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">R$</span>
                    <input type="number"
                           wire:model.live.debounce.600ms="minPrice"
                           placeholder="{{ number_format($this->priceRange['min'], 0, ',', '.') }}"
                           min="0"
                           class="w-full pl-7 pr-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
                <span class="text-gray-400 text-xs">até</span>
                <div class="relative flex-1">
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">R$</span>
                    <input type="number"
                           wire:model.live.debounce.600ms="maxPrice"
                           placeholder="{{ number_format($this->priceRange['max'], 0, ',', '.') }}"
                           min="0"
                           class="w-full pl-7 pr-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>
        </div>

        <hr class="mb-6 border-gray-200">

        {{-- Atributos --}}
        @foreach ($this->availableAttributes as $attribute)
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">
                    {{ $attribute->name }}
                </h3>
                <div class="space-y-1.5">
                    @foreach ($attribute->values as $value)
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="radio"
                                   wire:model.live="attrs.{{ $attribute->slug }}"
                                   value="{{ $value->slug }}"
                                   class="text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            @if ($attribute->type === 'color' && $value->color_hex)
                                <span class="inline-block w-4 h-4 rounded-full border border-gray-300"
                                      style="background-color: {{ $value->color_hex }}"></span>
                            @endif
                            <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">
                                {{ $value->getLabel() }}
                            </span>
                        </label>
                    @endforeach
                    {{-- Opção de limpar este atributo --}}
                    @if (!empty($attrs[$attribute->slug]))
                        <button wire:click="$set('attrs.{{ $attribute->slug }}', null)"
                                class="text-xs text-indigo-600 hover:underline mt-1">
                            Limpar
                        </button>
                    @endif
                </div>
            </div>
            <hr class="mb-6 border-gray-200">
        @endforeach

        {{-- Em estoque --}}
        <div class="mb-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox"
                       wire:model.live="inStock"
                       class="rounded text-indigo-600 border-gray-300 focus:ring-indigo-500">
                <span class="text-sm text-gray-700">Somente em estoque</span>
            </label>
        </div>

        {{-- Limpar todos --}}
        @if ($this->hasActiveFilters)
            <button wire:click="resetFilters"
                    class="w-full text-sm text-red-600 hover:text-red-800 underline text-left transition-colors">
                Limpar todos os filtros
            </button>
        @endif

    </aside>

    {{-- ═══════════════════════════════════════════════════════════════════
         CONTEÚDO PRINCIPAL
    ════════════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 min-w-0">

        {{-- Barra de ordenação --}}
        <div class="flex items-center justify-between mb-6">
            <p class="text-sm text-gray-500">
                <span wire:loading.remove>{{ $this->products->total() }} item(ns)</span>
                <span wire:loading class="animate-pulse">Buscando...</span>
            </p>
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Ordenar:</label>
                <select wire:model.live="sort"
                        class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="newest">Mais recentes</option>
                    <option value="price_asc">Menor preço</option>
                    <option value="price_desc">Maior preço</option>
                    <option value="name_asc">A–Z</option>
                </select>
            </div>
        </div>

        {{-- Loading overlay --}}
        <div wire:loading.delay class="mb-4">
            <div class="h-1 bg-indigo-200 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-500 rounded-full animate-pulse w-3/4"></div>
            </div>
        </div>

        {{-- Grid de produtos --}}
        @if ($this->products->isEmpty())
            <div class="text-center py-20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500 text-lg mb-2">Nenhum produto encontrado</p>
                @if ($this->hasActiveFilters)
                    <button wire:click="resetFilters" class="text-indigo-600 hover:underline text-sm">
                        Limpar filtros
                    </button>
                @endif
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4" wire:loading.class="opacity-60">
                @foreach ($this->products as $entry)
                    <a href="{{ $entry->url }}"
                       class="group bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden flex flex-col">

                        {{-- Imagem --}}
                        <div class="aspect-square bg-gray-100 overflow-hidden relative">
                            @if ($entry->imageUrl)
                                <img src="{{ $entry->imageUrl }}"
                                     alt="{{ $entry->displayName }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif

                            {{-- Badges --}}
                            <div class="absolute top-2 left-2 flex flex-col gap-1">
                                @if ($entry->isNew)
                                    <span class="text-xs font-semibold px-2 py-0.5 bg-emerald-500 text-white rounded-full">Novo</span>
                                @endif
                                @if ($entry->isOnSale)
                                    <span class="text-xs font-semibold px-2 py-0.5 bg-red-500 text-white rounded-full">Promoção</span>
                                @endif
                            </div>
                        </div>

                        {{-- Informações --}}
                        <div class="p-3 flex flex-col flex-1">
                            <h3 class="text-sm font-medium text-gray-800 line-clamp-2 leading-snug mb-2 flex-1">
                                {{ $entry->displayName }}
                            </h3>

                            {{-- Preço --}}
                            <div class="mt-auto">
                                @if ($entry->originalPrice)
                                    <div class="flex flex-col">
                                        <span class="text-xs text-gray-400 line-through">
                                            R$ {{ number_format($entry->originalPrice, 2, ',', '.') }}
                                        </span>
                                        <span class="text-base font-bold text-red-600">
                                            R$ {{ number_format($entry->price, 2, ',', '.') }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-base font-bold text-gray-900">
                                        R$ {{ number_format($entry->price, 2, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Paginação --}}
            <div class="mt-8">
                {{ $this->products->links() }}
            </div>
        @endif

    </div>
</div>
