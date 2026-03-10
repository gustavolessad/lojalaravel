<div
    x-data
    x-on:update-url.window="history.replaceState(null, '', $event.detail.url)">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-12">

        {{-- ═══════════════════════════════════════════════════════════════
             GALERIA DE IMAGENS (Alpine.js — reinicializa via wire:key)
             Layout: thumbnails verticais à esquerda + imagem principal
             Lightbox: clique na imagem principal para abrir
        ═══════════════════════════════════════════════════════════════ --}}
        <div
            x-data='{
                images: @json($this->currentImages->toArray()),
                activeIdx: 0,
                prev() { this.activeIdx = (this.activeIdx - 1 + this.images.length) % this.images.length },
                next() { this.activeIdx = (this.activeIdx + 1) % this.images.length },
                openAt(idx) { this.activeIdx = idx; $dispatch("open-lightbox", { images: this.images, index: idx }) }
            }'
            wire:key="gallery-{{ $this->currentVariant?->id ?? 'base' }}">
            {{-- ── Galeria: mobile = imagem + thumbs baixo | desktop = thumbs esquerda + imagem ── --}}

            {{-- Imagem principal --}}
            <div
                @click="images.length > 0 && openAt(activeIdx)"
                :class="images.length > 0 ? 'cursor-zoom-in' : ''"
                class="relative aspect-square bg-gray-100 rounded-2xl overflow-hidden group sm:hidden">
                <template x-if="images.length > 0">
                    <img :src="images[activeIdx]"
                        alt="{{ $this->product->name }}"
                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.02]">
                </template>
                <template x-if="images.length === 0">
                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                        <svg class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </template>
                <div x-show="images.length > 0"
                    class="absolute bottom-3 right-3 bg-black/30 text-white rounded-lg p-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>
                </div>
            </div>

            {{-- Thumbnails horizontais — mobile only (embaixo da imagem) --}}
            <div x-show="images.length > 1"
                class="flex gap-2 mt-2 overflow-x-auto pb-1 sm:hidden">
                <template x-for="(img, idx) in images" :key="'mob-' + idx">
                    <button
                        @click="activeIdx = idx"
                        :class="activeIdx === idx ? 'opacity-100' : 'opacity-40 hover:opacity-75'"
                        class="w-[60px] h-[60px] shrink-0 rounded-xl overflow-hidden bg-gray-100 transition-opacity duration-150">
                        <img :src="img" class="w-full h-full object-cover">
                    </button>
                </template>
            </div>

            {{-- Layout desktop: thumbnails esquerda + imagem principal --}}
            <div class="hidden sm:flex gap-3">

                {{-- Miniaturas verticais --}}
                <div x-show="images.length > 1"
                    class="flex flex-col gap-2 shrink-0 w-[72px] overflow-y-auto max-h-[480px]">
                    <template x-for="(img, idx) in images" :key="'desk-' + idx">
                        <button
                            @click="activeIdx = idx"
                            :class="activeIdx === idx ? 'opacity-100' : 'opacity-40 hover:opacity-75'"
                            class="w-[68px] h-[68px] shrink-0 rounded-xl overflow-hidden bg-gray-100 transition-opacity duration-150">
                            <img :src="img" class="w-full h-full object-cover">
                        </button>
                    </template>
                </div>

                {{-- Imagem principal --}}
                <div
                    @click="images.length > 0 && openAt(activeIdx)"
                    :class="images.length > 0 ? 'cursor-zoom-in' : ''"
                    class="flex-1 relative aspect-square bg-gray-100 rounded-2xl overflow-hidden group">
                    <template x-if="images.length > 0">
                        <img :src="images[activeIdx]"
                            alt="{{ $this->product->name }}"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.02]">
                    </template>
                    <template x-if="images.length === 0">
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </template>
                    {{-- Ícone de zoom (aparece no hover) --}}
                    <div x-show="images.length > 0"
                        class="absolute bottom-3 right-3 bg-black/30 text-white rounded-lg p-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                        </svg>
                    </div>
                </div>
            </div>

        </div>{{-- /gallery --}}

        {{-- ═══════════════════════════════════════════════════════════════
             INFORMAÇÕES DO PRODUTO
        ═══════════════════════════════════════════════════════════════ --}}
        <div class="flex flex-col space-y-5">

            {{-- Título --}}
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight mb-1">
                {{ $this->product->name }}
            </h1>

            {{-- SKU + Marca --}}
            @php $sku = $this->currentVariant?->sku ?? $this->product->sku; @endphp
            @if ($sku || $this->product->brand)
            <div class="flex items-center gap-3 flex-wrap mb-4">
                @if ($sku)
                <p class="text-xs text-gray-400">REF: {{ $sku }}</p>
                @endif
                @if ($sku && $this->product->brand)
                <span class="text-gray-200 select-none">|</span>
                @endif
                @if ($this->product->brand)
                <p class="text-xs text-gray-400">Marca: <a href="{{ $this->product->brand->url }}"
                        class="text-xs text-gray-500 hover:text-gray-800 transition-colors">
                        {{ $this->product->brand->name }}
                    </a></p>
                @endif
            </div>
            @endif

            {{-- Estrelas de avaliação --}}
            @if ($this->product->rating_count > 0)
            <a href="#avaliacoes" class="flex items-center gap-1.5 mb-4 group">
                <div class="flex items-center gap-0.5">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 {{ $i <= round($this->product->rating_avg) ? 'text-amber-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                </div>
                <span class="text-xs text-gray-500 group-hover:text-gray-700 transition-colors">
                    {{ number_format($this->product->rating_avg, 1, ',', '') }}
                    ({{ $this->product->rating_count }})
                </span>
            </a>
            @endif

            {{-- Preço + hints PIX/Parcelamento --}}
            {{-- Oculto quando a variante resolvida está sem estoque (produto simples oos
                 ou variável com variante selecionada oos). Mostrado enquanto o usuário
                 ainda está escolhendo (produto variável sem variante resolvida). --}}
            @if ($this->inStock || ($this->product->isVariable() && $this->currentVariant === null))
            {{-- Bloco de preço com overlay de loading durante troca de variante --}}
            <div class="relative">
                <div wire:loading wire:target="selectValue"
                     class="absolute inset-0 z-11 rounded-lg m-0 bg-white/70 backdrop-blur-[1px]"></div>

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
                $calc = app(\App\Services\Payment\PaymentCalculator::class);
                $cardMode = $calc->cardDisplayMode();
                $pixP = $calc->pixPrice($this->currentPrice);
                $instLabel = $calc->bestFreeInstallmentLabel($this->currentPrice);
                @endphp
                @if (($cardMode === 'pix' || $cardMode === 'both') && $pixP)
                <p class="text-sm text-green-600 font-medium">
                    <span class="font-bold">R$ {{ number_format($pixP, 2, ',', '.') }}</span> no PIX
                    <span class="ml-2 text-xs text-white bg-green-600 py-[2px] px-[8px] rounded-full">economize R$ {{ number_format($calc->pixSavings($this->currentPrice), 2, ',', '.') }}</span>
                </p>
                @endif
                @if (($cardMode === 'installments' || $cardMode === 'both') && $instLabel)
                <p class="text-sm text-gray-700 font-medium">ou {{ $instLabel }}</p>
                @endif
            </div>
            @endif

            {{-- ─── Seletores de variante ──────────────────────────── --}}
            @if ($this->product->isVariable() && $this->product->attributes->isNotEmpty())
            <div class="relative space-y-4 pt-1">
                <div wire:loading wire:target="selectValue"
                     class="absolute inset-0 z-11 m-0 rounded-lg bg-white/70 backdrop-blur-[1px]"></div>
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
                        $isSelected = ($selected[$attribute->slug] ?? null) === $value->slug;
                        $isAvailable = in_array($value->slug, $this->availableValueSlugs[$attribute->slug] ?? []);
                        $isOutOfStock = in_array($value->slug, $this->outOfStockValueSlugs[$attribute->slug] ?? []);
                        // cascade = clicável mas muda outra seleção para manter combinação válida
                        $isCascade = ! $isSelected && ! $isAvailable && ! $isOutOfStock;
                        // Imagem da variante para atributo de expansão
                        $variantThumb = $this->variantImageMap[$attribute->slug][$value->slug] ?? null;

                        // SVG badge sem-estoque reutilizado nos botões
                        $xBadge = '<span class="absolute -top-1 -right-1 z-10 w-4 h-4 bg-red-500 border-2 border-white rounded-full flex items-center justify-center pointer-events-none"><svg class="w-2 h-2 text-white" viewBox="0 0 8 8" fill="none">
                                <path d="M1.5 1.5l5 5M6.5 1.5l-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            </svg></span>';
                        @endphp

                        @if ($variantThumb)
                        {{-- ── Botão com imagem da variante (sem label, com tooltip) ── --}}
                        <span class="relative inline-block" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
                            <button
                                wire:click="selectValue('{{ $attribute->slug }}', '{{ $value->slug }}')"
                                @class([ 'p-1 rounded-xl border transition-all duration-150' , 'border-gray-500 bg-gray-50'=> $isSelected,
                                'border-gray-200 hover:border-gray-400' => ! $isSelected && $isAvailable && ! $isOutOfStock,
                                'border-gray-200 opacity-60' => $isAvailable && $isOutOfStock,
                                'border-dashed border-gray-300 hover:border-gray-400' => $isCascade,
                                'border-dashed border-gray-200 opacity-60' => ! $isAvailable && $isOutOfStock,
                                ])
                                >
                                <img src="{{ $variantThumb }}"
                                    alt="{{ $value->getLabel() }}"
                                    class="w-12 h-12 rounded-lg object-cover {{ ($isAvailable && $isOutOfStock) || (! $isAvailable && $isOutOfStock) ? 'opacity-50' : '' }}">
                            </button>
                            <span
                                x-show="show"
                                x-cloak
                                style="position:absolute; top:-28px; left:50%; transform:translateX(-50%);"
                                class="pointer-events-none whitespace-nowrap rounded bg-gray-900 px-2 py-1 text-xs text-white z-20"
                            >{{ $value->getLabel() }}@if ($isOutOfStock) (sem estoque)@endif</span>
                            @if ($isOutOfStock) {!! $xBadge !!} @endif
                        </span>
                        @else
                        {{-- ── Chip de texto (padrão) ── --}}
                        <span class="relative inline-block">
                            <button
                                wire:click="selectValue('{{ $attribute->slug }}', '{{ $value->slug }}')"
                                @class([ 'px-3.5 py-1.5 text-sm rounded-xl border font-medium transition-all duration-150' , 'border-gray-500 bg-gray-50 text-gray-700'=> $isSelected,
                                'border-gray-200 text-gray-700 hover:border-gray-400' => ! $isSelected && $isAvailable && ! $isOutOfStock,
                                'border-gray-200 text-gray-400' => $isAvailable && $isOutOfStock,
                                'border-dashed border-gray-300 text-gray-600 hover:border-gray-400 hover:text-gray-800' => $isCascade,
                                'border-dashed border-gray-200 text-gray-300' => ! $isAvailable && $isOutOfStock,
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



            {{-- ─── Ações de compra ────────────────────────────────── --}}
            @php
            // Variante ok = simples OU variável com variante selecionada
            $variantOk = $this->product->isSimple() || $this->currentVariant !== null;
            // Pode comprar = tem estoque E variante ok
            $canBuy = $this->inStock && $variantOk;
            // Precisa selecionar = variável sem variante resolvida
            $mustSelect = $this->product->isVariable() && $this->currentVariant === null;
            // Fora de estoque com tudo selecionado
            $outOfStock = ! $this->inStock && $variantOk;
            @endphp

            @if ($canBuy)
            {{-- ── Em estoque: quantidade + botão ──────────────── --}}
            <div class="flex items-center gap-2.5 pt-1">

                {{-- Seletor de quantidade (oculto por ora — ajuste no carrinho) --}}
                <div class="hidden flex items-center border border-gray-200 rounded-xl overflow-hidden select-none">
                    <button
                        wire:click="decrementQty"
                        class="px-3 py-2 text-gray-500 hover:bg-gray-100 transition-colors text-base leading-none">−</button>
                    <span class="px-3 py-2 text-sm font-semibold min-w-[36px] text-center tabular-nums border-x border-gray-200">
                        {{ $quantity }}
                    </span>
                    <button
                        wire:click="incrementQty"
                        class="px-3 py-2 text-gray-500 hover:bg-gray-100 transition-colors text-base leading-none">+</button>
                </div>

                {{-- Botão adicionar ao carrinho --}}
                <button
                    wire:click="addToCart"
                    wire:loading.attr="disabled"
                    wire:target="addToCart, selectValue"
                    class="w-full lg:w-sm py-3 px-6 rounded-xl font-bold
                               bg-green-700 text-white hover:bg-green-800
                               transition-colors flex items-center justify-center gap-2
                               disabled:opacity-60 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="addToCart, selectValue">
                        Adicionar ao Carrinho
                    </span>
                    <svg wire:loading wire:target="addToCart" class="animate-spin h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <span wire:loading wire:target="addToCart">Adicionando...</span>
                    <svg wire:loading wire:target="selectValue" class="animate-spin h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <span wire:loading wire:target="selectValue">Carregando...</span>
                </button>
            </div>

            @elseif ($mustSelect)
            {{-- ── Variável sem variante selecionada: orientar usuário -- --}}
            <div class="pt-1">
                <button disabled
                    class="w-full py-2 px-5 rounded-xl text-sm font-semibold
                               bg-gray-100 text-gray-400 cursor-not-allowed border border-gray-200">
                    Selecione as opções acima
                </button>
            </div>

            @elseif ($outOfStock)
            {{-- ── Fora de estoque: formulário Avise-me ─────────── --}}
            <div class="pt-1">
                @if ($notified)
                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
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
                                           focus:ring-2 focus:ring-gray-500 focus:border-gray-500 focus:outline-none
                                           @error('notifyEmail') border-red-400 @enderror">
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
                                       disabled:opacity-60">
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

            {{-- ─── Simulador de frete — só quando em estoque ─────── --}}
            @if ($canBuy)
            <div
                x-data="{ open: @js(! empty($shippingOptions)) }"
                class="rounded-xl border transition-colors w-full lg:w-sm"
                :class="open ? 'border-gray-300' : 'border-gray-200'">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center gap-2.5 px-4 py-3 text-sm font-medium text-gray-700 text-left">
                    <svg class="w-4 h-4 shrink-0 transition-colors" :class="open ? 'text-gray-900' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                    @if (! empty($shippingOptions))
                    <span class="truncate flex-1 text-gray-700">Frete calculado</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 shrink-0"></span>
                    @else
                    <span class="truncate flex-1">Calcular frete</span>
                    @endif
                    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>

                <div x-show="open" x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="px-4 pb-4 border-t border-gray-100 pt-3">

                    <form wire:submit.prevent="calculateShipping" class="flex gap-2">
                        <input type="text"
                            wire:model="shippingCep"
                            wire:keydown.enter.prevent="calculateShipping"
                            placeholder="00000-000"
                            maxlength="9"
                            x-mask="99999-999"
                            class="flex-1 min-w-0 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition">
                        <button type="submit"
                            wire:loading.attr="disabled"
                            class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors disabled:opacity-50 shrink-0">
                            <span wire:loading.remove wire:target="calculateShipping">Calcular</span>
                            <span wire:loading wire:target="calculateShipping">
                                <svg class="w-4 h-4 animate-spin text-gray-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                            </span>
                        </button>
                    </form>

                    @if ($shippingError)
                    <p class="mt-2 text-xs text-red-500">{{ $shippingError }}</p>
                    @endif

                    @if (! empty($shippingOptions))
                    <ul class="mt-4 space-y-2">
                        @foreach ($shippingOptions as $option)
                        <li class="flex items-start justify-between gap-2 text-xs pt-2 border-t border-gray-50 first:border-0 first:pt-0">
                            <span class="text-gray-700 leading-snug">
                                {{ trim(($option['company'] ?? '') . ' ' . $option['name']) }}
                                <span class="text-gray-400 block">{{ $option['days'] }} dias úteis</span>
                            </span>
                            <span class="font-semibold shrink-0 {{ $option['price'] == 0 ? 'text-green-600' : 'text-gray-900' }}">
                                {{ $option['price'] == 0 ? 'Grátis' : 'R$ ' . number_format($option['price'], 2, ',', '.') }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                    <p class="mt-2 text-[11px] text-gray-400 leading-tight">* Valores aproximados. O frete definitivo será calculado na finalização da compra.</p>
                    @endif
                </div>
            </div>
            @endif {{-- /canBuy --}}

        </div>{{-- /info --}}
    </div>{{-- /grid --}}

    {{-- ═══════════════════════════════════════════════════════════════
         COMPRE JUNTO
    ═══════════════════════════════════════════════════════════════ --}}
    @if ($this->product->compraJunto->isNotEmpty())
    @livewire('shop.buy-together', ['product' => $this->product], key('buy-together-' . $this->productId))
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         DESCRIÇÃO DO PRODUTO
    ═══════════════════════════════════════════════════════════════ --}}
    @if ($this->product->description)
    <div class="mt-18 lg:max-w-5xl mx-auto">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Mais detalhes sobre {{ $this->product->name }} </h2>
        <div class="prose prose-sm max-w-none text-gray-600">
            {!! $this->product->description !!}
        </div>
    </div>
    @endif

    {{-- ─── Características do produto ─────────────────────── --}}
    @if ($this->product->characteristicValues->isNotEmpty())
    @php
    $charsByAttribute = $this->product->characteristicValues
    ->groupBy('attribute_id')
    ->map(fn ($values) => [
    'name' => $values->first()->attribute->name,
    'values' => $values->map(fn ($v) => $v->getLabel())->implode(', '),
    ]);
    @endphp
    <div class="mt-8 lg:max-w-5xl mx-auto">
        <h3 class="text-xl font-semibold text-gray-900 mb-6">Características</h3>
        @foreach ($charsByAttribute as $char)
        <div class="flex items-baseline gap-2 mb-3 pb-3 border-b border-gray-100">
            <span class="text-gray-500 shrink-0">{{ $char['name'] }}:</span>
            <span class="text-gray-800 font-medium">{{ $char['values'] }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         PRODUTOS RECOMENDADOS
    ═══════════════════════════════════════════════════════════════ --}}
    @if ($this->recommendedProducts->isNotEmpty())
    <div class="mt-18">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Produtos recomendados</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @foreach ($this->recommendedProducts as $entry)
            <x-shop.product-card :entry="$entry" />
            @endforeach
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         PRODUTOS RELACIONADOS (mesma categoria)
    ═══════════════════════════════════════════════════════════════ --}}
    @if ($this->categoryProducts->isNotEmpty())
    <div class="mt-18">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Produtos relacionados</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
            @foreach ($this->categoryProducts as $entry)
            <x-shop.product-card :entry="$entry" />
            @endforeach
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         AVALIAÇÕES DO PRODUTO
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="mt-18">
        @livewire('shop.product-reviews', ['productId' => $this->productId], key('reviews-' . $this->productId))
    </div>

</div>