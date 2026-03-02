<div class="flex gap-8"
     x-data
     x-on:update-url.window="history.replaceState(null, '', $event.detail.url)">

    {{-- ═══════════════════════════════════════════════════════════════════
         SIDEBAR DE FILTROS
    ════════════════════════════════════════════════════════════════════ --}}
    <aside class="hidden lg:block w-60 flex-shrink-0 space-y-6">

        {{-- Subcategorias --}}
        @if ($this->subcategories->isNotEmpty())
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Subcategorias</p>
                <ul class="space-y-0.5">
                    @foreach ($this->subcategories as $sub)
                        <li>
                            <a href="{{ $sub->url }}"
                               class="block text-sm text-gray-600 hover:text-indigo-600 py-1 px-2 rounded-lg hover:bg-indigo-50 transition-colors">
                                {{ $sub->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <hr class="border-gray-100">
        @endif

        {{-- Marcas (apenas em páginas de categoria) --}}
        @if ($this->availableBrands->isNotEmpty())
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Marca</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($this->availableBrands as $brand)
                        @php $isActive = in_array($brand->slug, $brandIds); @endphp
                        <button
                            wire:click="toggleBrand('{{ $brand->slug }}')"
                            @class([
                                'inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-full border transition-all duration-150',
                                'bg-gray-900 border-gray-900 text-white shadow-sm' => $isActive,
                                'bg-white border-gray-200 text-gray-700 hover:border-gray-400 hover:bg-gray-50' => ! $isActive,
                            ])
                        >
                            @if ($brand->getFirstMediaUrl('logo', 'thumb'))
                                <img src="{{ $brand->getFirstMediaUrl('logo', 'thumb') }}"
                                     alt=""
                                     class="h-3 w-auto object-contain {{ $isActive ? 'brightness-0 invert' : '' }}">
                            @endif
                            {{ $brand->name }}
                        </button>
                    @endforeach
                </div>
            </div>
            <hr class="border-gray-100">
        @endif

        {{-- Preço --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Preço</p>
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">R$</span>
                    <input type="number"
                           wire:model.live.debounce.600ms="minPrice"
                           placeholder="{{ number_format($this->priceRange['min'], 0, ',', '.') }}"
                           min="0"
                           class="w-full pl-8 pr-2 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none bg-white">
                </div>
                <span class="text-gray-300 text-xs flex-shrink-0">até</span>
                <div class="relative flex-1">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs">R$</span>
                    <input type="number"
                           wire:model.live.debounce.600ms="maxPrice"
                           placeholder="{{ number_format($this->priceRange['max'], 0, ',', '.') }}"
                           min="0"
                           class="w-full pl-8 pr-2 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none bg-white">
                </div>
            </div>
        </div>

        <hr class="border-gray-100">

        {{-- Atributos --}}
        @foreach ($this->availableAttributes as $attribute)
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">
                    {{ $attribute->name }}
                </p>

                @if ($attribute->type === 'color')
                    {{-- Swatches de cor --}}
                    <div class="flex flex-wrap gap-2">
                        @foreach ($attribute->values as $value)
                            @php $isActive = in_array($value->slug, $attrs[$attribute->slug] ?? []); @endphp
                            <button
                                wire:click="toggleAttr('{{ $attribute->slug }}', '{{ $value->slug }}')"
                                title="{{ $value->getLabel() }}"
                                @class([
                                    'relative w-8 h-8 rounded-full border-2 transition-all duration-150',
                                    'border-gray-900 ring-2 ring-offset-2 ring-gray-900 scale-105' => $isActive,
                                    'border-transparent hover:border-gray-300 hover:scale-105' => ! $isActive,
                                ])
                                style="background-color: {{ $value->color_hex }}"
                            >
                                @if ($isActive)
                                    <span class="absolute inset-0 flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5 text-white drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @else
                    {{-- Pills de texto --}}
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($attribute->values as $value)
                            @php $isActive = in_array($value->slug, $attrs[$attribute->slug] ?? []); @endphp
                            <button
                                wire:click="toggleAttr('{{ $attribute->slug }}', '{{ $value->slug }}')"
                                @class([
                                    'px-3 py-1.5 text-xs font-medium rounded-full border transition-all duration-150',
                                    'bg-gray-900 border-gray-900 text-white shadow-sm' => $isActive,
                                    'bg-white border-gray-200 text-gray-700 hover:border-gray-400 hover:bg-gray-50' => ! $isActive,
                                ])
                            >{{ $value->getLabel() }}</button>
                        @endforeach
                    </div>
                @endif
            </div>
            <hr class="border-gray-100">
        @endforeach

        {{-- Em estoque --}}
        <div>
            <button
                wire:click="$toggle('inStock')"
                @class([
                    'w-full flex items-center justify-between px-4 py-2.5 text-sm font-medium rounded-xl border transition-all duration-150',
                    'bg-gray-900 border-gray-900 text-white shadow-sm' => $inStock,
                    'bg-white border-gray-200 text-gray-700 hover:border-gray-400 hover:bg-gray-50' => ! $inStock,
                ])
            >
                <span>Somente em estoque</span>
                @if ($inStock)
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                @endif
            </button>
        </div>

        {{-- Limpar todos (sidebar) --}}
        @if ($this->hasActiveFilters)
            <button wire:click="resetFilters"
                    class="w-full text-xs font-medium text-red-500 hover:text-red-700 transition-colors text-left py-1">
                ← Limpar todos os filtros
            </button>
        @endif

    </aside>

    {{-- ═══════════════════════════════════════════════════════════════════
         CONTEÚDO PRINCIPAL
    ════════════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 min-w-0">

        {{-- Barra de ordenação --}}
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-gray-500">
                <span wire:loading.remove>{{ $this->products->total() }} produto(s)</span>
                <span wire:loading class="animate-pulse text-gray-400">Buscando...</span>
            </p>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500">Ordenar:</label>
                <select wire:model.live="sort"
                        class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="newest">Mais recentes</option>
                    <option value="price_asc">Menor preço</option>
                    <option value="price_desc">Maior preço</option>
                    <option value="name_asc">A–Z</option>
                </select>
            </div>
        </div>

        {{-- ── Filtros ativos ──────────────────────────────────────────── --}}
        @if ($this->hasActiveFilters)
            <div class="flex flex-wrap items-center gap-2 mb-5 px-4 py-3 bg-gray-50 rounded-xl border border-gray-100">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide flex-shrink-0">Ativos:</span>

                {{-- Marcas --}}
                @foreach ($brandIds as $bSlug)
                    @php $brand = $this->availableBrands->firstWhere('slug', $bSlug); @endphp
                    @if ($brand)
                        <button wire:click="toggleBrand('{{ $bSlug }}')"
                                class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 text-xs font-medium bg-gray-900 text-white rounded-full hover:bg-gray-700 transition-colors">
                            {{ $brand->name }}
                            <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                @endforeach

                {{-- Atributos --}}
                @foreach ($attrs as $attrSlug => $valueSlugs)
                    @php $attribute = $this->availableAttributes->firstWhere('slug', $attrSlug); @endphp
                    @foreach ((array) $valueSlugs as $vSlug)
                        @php $value = $attribute?->values->firstWhere('slug', $vSlug); @endphp
                        @if ($value)
                            <button wire:click="toggleAttr('{{ $attrSlug }}', '{{ $vSlug }}')"
                                    class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 text-xs font-medium bg-gray-900 text-white rounded-full hover:bg-gray-700 transition-colors">
                                @if ($attribute?->type === 'color' && $value->color_hex)
                                    <span class="w-2.5 h-2.5 rounded-full border border-white/30 flex-shrink-0"
                                          style="background-color: {{ $value->color_hex }}"></span>
                                @endif
                                {{ $attribute->name }}: {{ $value->getLabel() }}
                                <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        @endif
                    @endforeach
                @endforeach

                {{-- Preço --}}
                @if ($minPrice !== '' || $maxPrice !== '')
                    <button wire:click="clearPriceFilter"
                            class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 text-xs font-medium bg-gray-900 text-white rounded-full hover:bg-gray-700 transition-colors">
                        Preço: R$ {{ $minPrice ?: '—' }} – R$ {{ $maxPrice ?: '—' }}
                        <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif

                {{-- Estoque --}}
                @if ($inStock)
                    <button wire:click="$set('inStock', false)"
                            class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 text-xs font-medium bg-gray-900 text-white rounded-full hover:bg-gray-700 transition-colors">
                        Em estoque
                        <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif

                <button wire:click="resetFilters"
                        class="ml-auto text-xs text-gray-400 hover:text-red-600 transition-colors font-medium flex-shrink-0">
                    Limpar todos
                </button>
            </div>
        @endif

        {{-- Loading bar --}}
        <div wire:loading.delay class="mb-4">
            <div class="h-0.5 bg-indigo-100 rounded-full overflow-hidden">
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
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-3 gap-6 gap-y-10" wire:loading.class="opacity-60">
                @foreach ($this->products as $entry)
                    <x-shop.product-card :entry="$entry" />
                @endforeach
            </div>

            {{-- Paginação --}}
            <div class="mt-8">
                {{ $this->products->links() }}
            </div>
        @endif

    </div>
</div>
