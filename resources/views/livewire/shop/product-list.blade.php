<div class="flex gap-8"
     x-data
     x-on:update-url.window="history.replaceState(null, '', $event.detail.url)">

    {{-- ═══════════════════════════════════════════════════════════════════
         SIDEBAR DE FILTROS
    ════════════════════════════════════════════════════════════════════ --}}
    <aside class="hidden lg:block w-60 flex-shrink-0">

        {{-- Subcategorias --}}
        @if ($this->subcategories->isNotEmpty())
        <div x-data="{ open: true }" class="border border-gray-200 rounded-xl mb-3">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full text-sm py-4 px-4 font-semibold text-gray-800 hover:text-black transition-colors">
                <span>Categorias</span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open && 'rotate-180'"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="pb-3 px-4">
                <ul class="space-y-0.5">
                    @foreach ($this->subcategories as $sub)
                    <li>
                        <a href="{{ $sub->url }}"
                           class="block text-sm text-gray-700 hover:text-black py-1 transition-colors">
                            {{ $sub->name }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- Marcas (apenas em páginas de categoria) --}}
        @if ($this->availableBrands->isNotEmpty())
        @php $brandOpen = !empty($brandIds); @endphp
        <div x-data="{ open: @js($brandOpen) }" class="border border-gray-200 rounded-xl mb-3">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full py-4 px-4 text-sm font-semibold text-gray-800 hover:text-black transition-colors">
                <span>Marca</span>
                <div class="flex items-center gap-1.5">
                    @if ($brandOpen)
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                    @endif
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open && 'rotate-180'"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </div>
            </button>
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="pb-4 px-4">
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
        </div>
        @endif

        {{-- Preço --}}
        @php
            $rangeMin    = (int) floor($this->priceRange['min']);
            $rangeMax    = (int) ceil($this->priceRange['max']);
            $currentFrom = $minPrice !== '' ? (int) $minPrice : $rangeMin;
            $currentTo   = $maxPrice !== '' ? (int) $maxPrice : $rangeMax;
            $priceOpen   = ($minPrice !== '' || $maxPrice !== '');
        @endphp
        @if ($rangeMax > $rangeMin)
        <div x-data="{
                open:     @js($priceOpen),
                rangeMin: {{ $rangeMin }},
                rangeMax: {{ $rangeMax }},
                from:     {{ $currentFrom }},
                to:       {{ $currentTo }},
                get fromPct() { return ((this.from - this.rangeMin) / (this.rangeMax - this.rangeMin)) * 100 },
                get toPct()   { return ((this.to   - this.rangeMin) / (this.rangeMax - this.rangeMin)) * 100 },
                applyMin() {
                    if (this.from > this.to) this.from = this.to;
                    $wire.set('minPrice', this.from <= this.rangeMin ? '' : this.from);
                },
                applyMax() {
                    if (this.to < this.from) this.to = this.from;
                    $wire.set('maxPrice', this.to >= this.rangeMax ? '' : this.to);
                },
                reset() { this.from = this.rangeMin; this.to = this.rangeMax; },
             }"
             @price-range-reset.window="reset()"
             class="border border-gray-200 rounded-xl mb-3">

            <button @click="open = !open"
                    class="flex items-center justify-between w-full py-4 px-4 text-sm font-semibold text-gray-700 hover:text-black transition-colors">
                <span>Preço</span>
                <div class="flex items-center gap-1.5">
                    @if ($priceOpen)
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                    @endif
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open && 'rotate-180'"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </div>
            </button>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="px-4 pb-5">

                {{-- Valores selecionados --}}
                <div class="flex justify-between text-xs font-semibold text-gray-700 mb-4">
                    <span>R$ <span x-text="from.toLocaleString('pt-BR')"></span></span>
                    <span>R$ <span x-text="to.toLocaleString('pt-BR')"></span></span>
                </div>

                {{-- Slider duplo --}}
                <div class="relative flex items-center h-5">
                    {{-- Track de fundo --}}
                    <div class="absolute inset-x-0 h-1.5 bg-gray-200 rounded-full pointer-events-none">
                        {{-- Preenchimento entre os dois thumbs --}}
                        <div class="absolute h-full bg-indigo-500 rounded-full"
                             :style="`left: ${fromPct}%; right: ${100 - toPct}%`"></div>
                    </div>

                    {{-- Thumb mínimo — z-index sobe quando encostado no máximo --}}
                    <input type="range"
                           :min="rangeMin" :max="rangeMax" step="1"
                           x-model.number="from"
                           @change="applyMin()"
                           :style="`z-index: ${from >= to ? 3 : 1}`"
                           class="price-range-input">

                    {{-- Thumb máximo --}}
                    <input type="range"
                           :min="rangeMin" :max="rangeMax" step="1"
                           x-model.number="to"
                           @change="applyMax()"
                           style="z-index: 2"
                           class="price-range-input">
                </div>

                {{-- Rótulos de extremo --}}
                <div class="flex justify-between text-xs text-gray-400 mt-3">
                    <span>R$ {{ number_format($rangeMin, 0, ',', '.') }}</span>
                    <span>R$ {{ number_format($rangeMax, 0, ',', '.') }}</span>
                </div>

            </div>
        </div>
        @endif

        {{-- Atributos --}}
        @foreach ($this->availableAttributes as $attribute)
        @php $attrOpen = !empty($attrs[$attribute->slug] ?? []); @endphp
        <div x-data="{ open: @js($attrOpen) }" class="border border-gray-200 rounded-xl mb-3">
            <button @click="open = !open"
                    class="flex items-center justify-between w-full py-4 px-4 text-sm font-semibold text-gray-700 hover:text-black transition-colors">
                <span>{{ $attribute->name }}</span>
                <div class="flex items-center gap-1.5">
                    @if ($attrOpen)
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                    @endif
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open && 'rotate-180'"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </div>
            </button>
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="pb-4 px-4">
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
            </div>
        </div>
        @endforeach

        {{-- Em estoque --}}
        <div class="py-3">
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
            <div class="flex flex-wrap items-center gap-2 mb-5">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide flex-shrink-0">Filtros:</span>

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
