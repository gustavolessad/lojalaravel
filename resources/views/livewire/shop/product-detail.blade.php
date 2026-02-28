<div
    x-data
    x-on:update-url.window="history.replaceState(null, '', $event.detail.url)"
>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

        {{-- ═══════════════════════════════════════════════════════════════
             GALERIA DE IMAGENS (Alpine.js — reinicializa via wire:key)
        ═══════════════════════════════════════════════════════════════ --}}
        <div
            x-data='{ images: @json($this->currentImages->toArray()), activeIdx: 0 }'
            wire:key="gallery-{{ $this->currentVariant?->id ?? 'base' }}"
            class="space-y-3"
        >
            {{-- Imagem principal --}}
            <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden">
                <template x-if="images.length > 0">
                    <img :src="images[activeIdx]"
                         alt="{{ $this->product->name }}"
                         class="w-full h-full object-cover">
                </template>
                <template x-if="images.length === 0">
                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                        <svg class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </template>
            </div>

            {{-- Miniaturas --}}
            <template x-if="images.length > 1">
                <div class="flex gap-2 overflow-x-auto pb-1">
                    <template x-for="(img, idx) in images" :key="idx">
                        <button
                            @click="activeIdx = idx"
                            :class="activeIdx === idx
                                ? 'ring-2 ring-indigo-500 ring-offset-1'
                                : 'ring-1 ring-gray-200 hover:ring-gray-300'"
                            class="flex-shrink-0 w-16 h-16 rounded-xl overflow-hidden transition-all"
                        >
                            <img :src="img" class="w-full h-full object-cover">
                        </button>
                    </template>
                </div>
            </template>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════
             INFORMAÇÕES DO PRODUTO
        ═══════════════════════════════════════════════════════════════ --}}
        <div class="flex flex-col space-y-5">

            {{-- Categoria + Marca --}}
            @if ($this->product->categories->isNotEmpty() || $this->product->brand)
                <div class="flex items-center gap-2 text-sm flex-wrap">
                    @if ($this->product->categories->isNotEmpty())
                        @php $cat = $this->product->categories->first(); @endphp
                        <a href="{{ $cat->url }}"
                           class="font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                            {{ $cat->name }}
                        </a>
                    @endif
                    @if ($this->product->categories->isNotEmpty() && $this->product->brand)
                        <span class="text-gray-300">·</span>
                    @endif
                    @if ($this->product->brand)
                        <a href="{{ $this->product->brand->url }}"
                           class="flex items-center gap-1.5 text-gray-500 hover:text-gray-800 transition-colors">
                            @if ($this->product->brand->getFirstMediaUrl('logo'))
                                <img src="{{ $this->product->brand->getFirstMediaUrl('logo') }}"
                                     alt="{{ $this->product->brand->name }}"
                                     class="h-4 w-auto object-contain">
                            @else
                                {{ $this->product->brand->name }}
                            @endif
                        </a>
                    @endif
                </div>
            @endif

            {{-- Título --}}
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight">
                {{ $this->product->name }}
            </h1>

            {{-- SKU --}}
            @php $sku = $this->currentVariant?->sku ?? $this->product->sku; @endphp
            @if ($sku)
                <p class="text-xs text-gray-400 -mt-2">REF: {{ $sku }}</p>
            @endif

            {{-- Preço + hints PIX/Parcelamento --}}
            {{-- Oculto quando a variante resolvida está sem estoque (produto simples oos
                 ou variável com variante selecionada oos). Mostrado enquanto o usuário
                 ainda está escolhendo (produto variável sem variante resolvida). --}}
            @if ($this->inStock || ($this->product->isVariable() && $this->currentVariant === null))
                <div>
                    @if ($this->originalPrice)
                        <span class="block text-sm text-gray-400 line-through">
                            R$ {{ number_format($this->originalPrice, 2, ',', '.') }}
                        </span>
                    @endif
                    <span class="text-3xl font-bold {{ $this->originalPrice ? 'text-green-700' : 'text-green-700' }}">
                        R$ {{ number_format($this->currentPrice, 2, ',', '.') }}
                    </span>
                </div>

                @php
                    $calc      = app(\App\Services\PaymentCalculator::class);
                    $cardMode  = $calc->cardDisplayMode();
                    $pixP      = $calc->pixPrice($this->currentPrice);
                    $instLabel = $calc->bestFreeInstallmentLabel($this->currentPrice);
                @endphp
                @if (($cardMode === 'pix' || $cardMode === 'both') && $pixP)
                    <p class="text-sm text-green-600 font-medium -mt-3">
                        <span class="font-bold">R$ {{ number_format($pixP, 2, ',', '.') }}</span> no PIX
                        <span class="ml-2 text-xs text-white bg-green-600 py-[2px] px-[8px] rounded-full">economize R$ {{ number_format($calc->pixSavings($this->currentPrice), 2, ',', '.') }}</span>
                    </p>
                @endif
                @if (($cardMode === 'installments' || $cardMode === 'both') && $instLabel)
                    <p class="text-sm text-gray-700 font-medium -mt-3">ou {{ $instLabel }}</p>
                @endif
            @endif

            {{-- Descrição curta --}}
            @if ($this->product->short_description)
                <p class="text-sm text-gray-600 leading-relaxed">
                    {{ $this->product->short_description }}
                </p>
            @endif

            {{-- ─── Seletores de variante ──────────────────────────── --}}
            @if ($this->product->isVariable() && $this->product->attributes->isNotEmpty())
                <div class="space-y-4 pt-1">
                    @foreach ($this->product->attributes as $attribute)
                        <div>
                            <p class="text-sm font-semibold text-gray-800 mb-2">
                                {{ $attribute->name }}
                                @if (isset($selected[$attribute->slug]))
                                    @php
                                        $selectedValue = $attribute->values->firstWhere('slug', $selected[$attribute->slug]);
                                    @endphp
                                    @if ($selectedValue)
                                        <span class="font-normal text-gray-500">
                                            — {{ $selectedValue->getLabel() }}
                                        </span>
                                    @endif
                                @endif
                            </p>

                            <div class="flex flex-wrap gap-2">
                                @foreach ($attribute->values as $value)
                                    @php
                                        $isSelected   = ($selected[$attribute->slug] ?? null) === $value->slug;
                                        $isAvailable  = in_array($value->slug, $this->availableValueSlugs[$attribute->slug] ?? []);
                                        $isOutOfStock = in_array($value->slug, $this->outOfStockValueSlugs[$attribute->slug] ?? []);
                                        // cascade = clicável mas muda outra seleção para manter combinação válida
                                        $isCascade    = ! $isSelected && ! $isAvailable && ! $isOutOfStock;
                                    @endphp

                                    @php
                                        // SVG reutilizado nos dois tipos de botão
                                        $xBadge = '<span class="absolute -top-1 -right-1 z-10 w-4 h-4 bg-red-500 border-2 border-white rounded-full flex items-center justify-center pointer-events-none"><svg class="w-2 h-2 text-white" viewBox="0 0 8 8" fill="none"><path d="M1.5 1.5l5 5M6.5 1.5l-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg></span>';
                                    @endphp

                                    @if ($attribute->type === 'color' && $value->color_hex)
                                        {{-- ── Swatch de cor — wrapper relative para o X não herdar opacity do botão ── --}}
                                        <span class="relative inline-block">
                                            <button
                                                wire:click="selectValue('{{ $attribute->slug }}', '{{ $value->slug }}')"
                                                title="{{ $value->getLabel() }}{{ $isOutOfStock ? ' (sem estoque)' : ($isCascade ? ' — vai alterar outra seleção' : '') }}"
                                                @class([
                                                    'w-9 h-9 rounded-full border-2 transition-all duration-150',
                                                    'border-indigo-600 ring-2 ring-indigo-300 scale-110'                => $isSelected,
                                                    'border-gray-300 hover:scale-105 hover:border-gray-400'             => ! $isSelected && $isAvailable && ! $isOutOfStock,
                                                    'border-dashed border-gray-400 hover:scale-105 hover:border-indigo-400' => $isCascade,
                                                    'border-gray-200 opacity-40'                                        => $isOutOfStock,
                                                ])
                                                style="background-color: {{ $value->color_hex }}"
                                            ></button>
                                            @if ($isOutOfStock) {!! $xBadge !!} @endif
                                        </span>
                                    @else
                                        {{-- ── Chip de texto — wrapper relative para o X não herdar opacity do botão ── --}}
                                        <span class="relative inline-block">
                                            <button
                                                wire:click="selectValue('{{ $attribute->slug }}', '{{ $value->slug }}')"
                                                @class([
                                                    'px-4 py-2 text-sm rounded-lg border font-medium transition-all duration-150',
                                                    // Selecionado
                                                    'border-indigo-600 bg-indigo-50 text-indigo-700'                       => $isSelected,
                                                    // Disponível com seleção atual, em estoque
                                                    'border-gray-300 text-gray-700 hover:border-indigo-400'                => ! $isSelected && $isAvailable && ! $isOutOfStock,
                                                    // Disponível mas sem estoque (context-aware)
                                                    'border-gray-200 text-gray-400'                                        => $isAvailable && $isOutOfStock,
                                                    // Cascade: vai alterar outra seleção — borda tracejada
                                                    'border-dashed border-gray-400 text-gray-600 hover:border-indigo-400 hover:text-gray-800' => $isCascade,
                                                    // Sem estoque + cascade (fora do alcance com seleção atual)
                                                    'border-dashed border-gray-200 text-gray-300'                          => ! $isAvailable && $isOutOfStock,
                                                ])
                                            >{{ $value->getLabel() }}</button>
                                            @if ($isOutOfStock) {!! $xBadge !!} @endif
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- ─── Status de estoque ──────────────────────────────── --}}
            <div>
                @if ($this->inStock)
                    <span class="inline-flex items-center gap-1.5 text-sm text-emerald-700">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Em estoque
                        @if ($this->currentVariant && $this->currentStock > 0 && $this->currentStock <= 5)
                            <span class="text-amber-600 font-medium">
                                (apenas {{ $this->currentStock }} {{ $this->currentStock === 1 ? 'unidade' : 'unidades' }})
                            </span>
                        @endif
                    </span>
                @elseif ($this->product->isVariable() && ! $this->currentVariant)
                    <span class="text-sm text-gray-400">Selecione as opções para ver disponibilidade</span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-sm text-red-500">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        Fora de estoque
                    </span>
                @endif
            </div>

            {{-- ─── Ações de compra ────────────────────────────────── --}}
            @php
                // Variante ok = simples OU variável com variante selecionada
                $variantOk  = $this->product->isSimple() || $this->currentVariant !== null;
                // Pode comprar = tem estoque E variante ok
                $canBuy     = $this->inStock && $variantOk;
                // Precisa selecionar = variável sem variante resolvida
                $mustSelect = $this->product->isVariable() && $this->currentVariant === null;
                // Fora de estoque com tudo selecionado
                $outOfStock = ! $this->inStock && $variantOk;
            @endphp

            @if ($canBuy)
                {{-- ── Em estoque: quantidade + botão ──────────────── --}}
                <div class="flex items-center gap-3 pt-1">

                    {{-- Seletor de quantidade --}}
                    <div class="flex items-center border border-gray-300 rounded-xl overflow-hidden select-none">
                        <button
                            wire:click="decrementQty"
                            class="px-3.5 py-2.5 text-gray-600 hover:bg-gray-100 transition-colors text-lg leading-none"
                        >−</button>
                        <span class="px-4 py-2.5 text-sm font-semibold min-w-[44px] text-center tabular-nums">
                            {{ $quantity }}
                        </span>
                        <button
                            wire:click="incrementQty"
                            class="px-3.5 py-2.5 text-gray-600 hover:bg-gray-100 transition-colors text-lg leading-none"
                        >+</button>
                    </div>

                    {{-- Botão adicionar ao carrinho --}}
                    <button
                        wire:click="addToCart"
                        wire:loading.attr="disabled"
                        wire:target="addToCart"
                        class="flex-1 py-3 px-6 rounded-xl text-sm font-semibold
                               bg-green-700 text-white hover:bg-green-800
                               active:scale-95 transition-colors
                               disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="addToCart">
                            Adicionar ao Carrinho
                        </span>
                        <span wire:loading wire:target="addToCart" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Adicionando...
                        </span>
                    </button>
                </div>

            @elseif ($mustSelect)
                {{-- ── Variável sem variante selecionada: orientar usuário -- --}}
                <div class="pt-1">
                    <button disabled
                        class="w-full py-3 px-6 rounded-xl text-sm font-semibold
                               bg-gray-100 text-gray-400 cursor-not-allowed border border-gray-200"
                    >
                        Selecione as opções acima
                    </button>
                </div>

            @elseif ($outOfStock)
                {{-- ── Fora de estoque: formulário Avise-me ─────────── --}}
                <div class="pt-1">
                    @if ($notified)
                        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-emerald-800">Tudo certo!</p>
                                <p class="text-xs text-emerald-600 mt-0.5">
                                    Avisaremos você assim que este produto voltar ao estoque.
                                </p>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 mb-3">
                            Receba um aviso por e-mail quando voltar ao estoque:
                        </p>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <input
                                    type="email"
                                    wire:model="notifyEmail"
                                    placeholder="seu@email.com"
                                    class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl
                                           focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none
                                           @error('notifyEmail') border-red-400 @enderror"
                                >
                                @error('notifyEmail')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <button
                                wire:click="notifyMe"
                                wire:loading.attr="disabled"
                                wire:target="notifyMe"
                                class="flex-shrink-0 px-4 py-2.5 bg-gray-800 text-white text-sm font-medium
                                       rounded-xl hover:bg-gray-900 active:scale-95 transition-all duration-150
                                       disabled:opacity-60"
                            >
                                <span wire:loading.remove wire:target="notifyMe">Avise-me</span>
                                <span wire:loading wire:target="notifyMe">...</span>
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Mensagem de erro do carrinho --}}
            @if (session('cart-error'))
                <p class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2">
                    {{ session('cart-error') }}
                </p>
            @endif

            {{-- ─── Simulador de frete ─────────────────────────── --}}
            <div class="pt-4 border-t border-gray-100">
                <h3 class="font-semibold text-gray-900 mb-3">Calcular frete</h3>

                @if ($this->product->isVariable() && $this->currentVariant === null)
                    <p class="text-xs text-gray-400">Selecione as opções do produto para calcular o frete.</p>
                @else
                    <form wire:submit.prevent="calculateShipping" class="flex gap-2">
                        <input type="text"
                            wire:model="shippingCep"
                            wire:keydown.enter.prevent="calculateShipping"
                            placeholder="00000-000"
                            maxlength="9"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="submit"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                            <span wire:loading.remove wire:target="calculateShipping">Calcular</span>
                            <span wire:loading wire:target="calculateShipping">
                                <svg class="size-3 animate-spin text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </form>

                    @if ($shippingError)
                        <p class="mt-2 text-xs text-red-600">{{ $shippingError }}</p>
                    @endif

                    @if (! empty($shippingOptions))
                        <ul class="mt-3 space-y-2">
                            @foreach ($shippingOptions as $option)
                                <li class="flex justify-between text-sm">
                                    <span class="text-gray-700">
                                        {{ trim(($option['company'] ?? '') . ' ' . $option['name']) }}
                                        <span class="text-gray-400 text-xs">({{ $option['days'] }} dias)</span>
                                    </span>
                                    <span class="font-semibold {{ $option['price'] == 0 ? 'text-green-600' : 'text-gray-900' }}">
                                        {{ $option['price'] == 0 ? 'Grátis' : 'R$ ' . number_format($option['price'], 2, ',', '.') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @endif
            </div>

        </div>{{-- /info --}}
    </div>{{-- /grid --}}

    {{-- ═══════════════════════════════════════════════════════════════
         DESCRIÇÃO DO PRODUTO
    ═══════════════════════════════════════════════════════════════ --}}
    @if ($this->product->description)
        <div class="mt-12 pt-8 border-t border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Descrição</h2>
            <div class="prose prose-sm max-w-none text-gray-600">
                {!! $this->product->description !!}
            </div>
        </div>
    @endif

</div>
