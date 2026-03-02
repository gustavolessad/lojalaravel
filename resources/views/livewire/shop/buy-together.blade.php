<div class="mt-18 bg-gray-100 rounded-2xl px-4 py-8 lg:p-10">

    {{-- Cabeçalho --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900">Aproveite e compre junto!</h2>
        <p class="text-sm text-gray-500 mt-1">Clique nos produtos que deseja adicionar junto ao carrinho!</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-4 lg:gap-5 items-start">

        {{-- ── COLUNA ESQUERDA: Produto Atual ─────────────────────────────── --}}
        {{-- Mobile: div vira row com produto (50%) + instrução (50%) | Desktop: só o card --}}
        <div class="flex gap-4 items-center lg:block shrink-0 lg:w-55">

            @php
            $mainCover = $this->mainProduct->getFirstMediaUrl('cover', 'thumb')
            ?: $this->mainProduct->getFirstMediaUrl('cover');

            $mainOriginalPrice = null;
            if ($mainVariantId) {
            $mainVariant = $this->mainProduct->variants->firstWhere('id', $mainVariantId);
            if ($mainVariant) {
            $mainOriginalPrice = $mainVariant->price !== null
            ? ($mainVariant->isOnSale() ? (float) $mainVariant->price : null)
            : ($this->mainProduct->isOnSale() ? (float) $this->mainProduct->price : null);
            }
            } else {
            $mainOriginalPrice = $this->mainProduct->isOnSale() ? (float) $this->mainProduct->price : null;
            }
            @endphp

            <div class="w-1/2 lg:w-full relative bg-white border border-gray-200 rounded-2xl overflow-hidden">
                {{-- Tag "Adicionado" --}}
                <div class="absolute top-4 right-4 z-10 pointer-events-none">
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-white bg-green-500 px-2 py-0.5 rounded-full">
                        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        Adicionado
                    </span>
                </div>

                {{-- Imagem --}}
                <div class="aspect-square bg-gray-50">
                    @if ($mainCover)
                    <img src="{{ $mainCover }}" alt="{{ $this->mainProduct->name }}"
                        class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="p-3">
                    {{-- Rótulo "produto em exibição" --}}
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1.5">
                        Produto em exibição
                    </p>

                    <p class="text-sm text-gray-800 font-medium leading-snug line-clamp-2 mb-1.5">
                        {{ $this->mainProduct->name }}
                    </p>

                    {{-- Variante selecionada (somente leitura — seleção feita acima no produto) --}}
                    @if ($this->mainProduct->isVariable())
                        @php
                            $mainVariantLabel = $mainVariantId
                                ? ($this->mainProduct->variants->firstWhere('id', $mainVariantId)?->label ?? null)
                                : null;
                        @endphp
                        @if ($mainVariantLabel)
                            <div class="flex items-center gap-2 mb-1.5">
                                <p class="text-xs text-gray-600">{{ $mainVariantLabel }}</p>
                                <button
                                    @click="window.scrollTo({top: 0, behavior: 'smooth'})"
                                    class="text-[10px] font-medium text-indigo-500 hover:text-indigo-700 hover:underline shrink-0"
                                >Alterar</button>
                            </div>
                        @else
                            <div class="flex items-center gap-2 mb-1.5">
                                <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded">
                                    Nenhuma opção selecionada
                                </p>
                                <button
                                    @click="window.scrollTo({top: 0, behavior: 'smooth'})"
                                    class="text-[10px] font-medium text-indigo-500 hover:text-indigo-700 hover:underline shrink-0"
                                >Selecionar</button>
                            </div>
                        @endif
                    @endif

                    {{-- Preço (mesma estilização dos cards horizontais) --}}
                    @if ($mainOriginalPrice)
                        <p class="text-xs text-gray-400 line-through leading-none mb-0.5">
                            R$ {{ number_format($mainOriginalPrice, 2, ',', '.') }}
                        </p>
                    @endif
                    <p class="text-sm font-bold text-green-700">
                        R$ {{ number_format($mainProductPrice, 2, ',', '.') }}
                    </p>
                </div>
            </div>
            {{-- Instrução — visível apenas no mobile, ao lado do produto --}}
            <div class="w-1/2 lg:hidden flex items-center">
                <p class="text-sm text-gray-500 leading-snug">
                    Selecione os produtos abaixo para comprar junto
                </p>
            </div>
        </div>

        {{-- ── Separador "+" — apenas desktop ────────────────────────────── --}}
        <div class="hidden lg:flex items-center justify-center shrink-0 lg:pt-8">
            <span class="flex items-center justify-center text-green-700 font-semibold text-5xl select-none">+</span>
        </div>

        {{-- ── COLUNA MEIO: Produtos Relacionados (cards horizontais) ─────── --}}
        <div class="flex-1 w-full">

            @php
            $xBadge = '<span class="absolute -top-0.5 -right-0.5 z-10 w-3 h-3 bg-red-500 border border-white rounded-full flex items-center justify-center pointer-events-none"><svg class="w-1.5 h-1.5 text-white" viewBox="0 0 8 8" fill="none">
                    <path d="M1.5 1.5l5 5M6.5 1.5l-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg></span>';
            @endphp

            <div class="space-y-2.5">
                @foreach ($this->relatedProducts as $relProduct)
                @php
                $pid = (string) $relProduct->id;
                $isSelected = in_array($relProduct->id, $selectedIds);
                $isInStock = $this->isProductInStock($relProduct->id);
                $relCover = $relProduct->getFirstMediaUrl('cover', 'thumb')
                ?: $relProduct->getFirstMediaUrl('cover');
                $relPrice = $this->getProductPrice($relProduct->id);
                $relOriginalPrice = $this->getProductOriginalPrice($relProduct->id);
                $state = $this->variantState[$pid] ?? [];
                @endphp

                {{-- Card clicável para toggle; variant buttons usam .stop para não propagar --}}
                <div
                    @if ($isInStock) wire:click="toggleProduct({{ $relProduct->id }})" @endif
                    @class([
                        'relative flex bg-white border p-2 rounded-2xl overflow-hidden transition-all duration-200',
                        'border-gray-200 cursor-pointer hover:border-gray-300' => $isInStock,
                        'border-gray-100 cursor-default opacity-80'            => ! $isInStock,
                    ])
                >
                    {{-- ── Indicador top-right (absoluto) ── --}}
                    <div class="absolute top-3 right-3 z-10 pointer-events-none">
                        @if (! $isInStock)
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-red-500 bg-red-50 border border-red-200 px-2 py-0.5 rounded-full">
                                Sem estoque
                            </span>
                        @elseif ($isSelected)
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-white bg-green-500 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                Adicionado
                            </span>
                        @else
                            <span class="w-6 h-6 rounded-full border-2 border-gray-300 bg-white block"></span>
                        @endif
                    </div>

                    {{-- ── Imagem ── --}}
                    <div class="w-16 h-16 shrink-0 bg-gray-100 rounded-xl">
                        @if ($relCover)
                        <img src="{{ $relCover }}" alt="{{ $relProduct->name }}"
                            class="w-full h-full object-cover rounded-xl">
                        @else
                        <div class="w-full h-full  flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                        </div>
                        @endif
                    </div>

                    {{-- ── Conteúdo: nome + variantes + preço ── --}}
                    {{-- pr-10 para não sobrepor o círculo/tag absoluto no canto direito --}}
                    <div class="flex-1 min-w-0 p-3">
                        <p class="text-sm text-gray-800 font-medium leading-snug line-clamp-2 mb-1 pr-20">
                            {{ $relProduct->name }}
                        </p>

                        <div class="flex items-end justify-between">

                            {{-- Seletores de variante (.stop impede o click de propagar e toglar o card) --}}
                            @if ($relProduct->isVariable() && $relProduct->attributes->isNotEmpty())
                            <div class="space-y-1">
                                @foreach ($relProduct->attributes as $attr)
                                @php
                                $currentVal = $choices[$pid][$attr->slug] ?? '';
                                $attrState = $state[$attr->slug] ?? ['exists' => [], 'available' => [], 'outOfStock' => []];
                                $existSlugs = $attrState['exists'] ?? [];
                                $availSlugs = $attrState['available'];
                                $oosSlugsList = $attrState['outOfStock'];
                                @endphp

                                <div class="flex items-center gap-2">
                                    <p class="text-[10px] text-gray-400 mb-0">{{ $attr->name }}</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($attr->values as $val)
                                        @continue(! in_array($val->slug, $existSlugs))
                                        @php
                                        $isSel = $currentVal === $val->slug;
                                        $isAvail = in_array($val->slug, $availSlugs);
                                        $isOos = in_array($val->slug, $oosSlugsList);
                                        $isCascade = ! $isSel && ! $isAvail && ! $isOos;
                                        @endphp

                                        @if ($attr->type === 'color' && $val->color_hex)
                                        <span class="relative inline-block">
                                            <button
                                                wire:click.stop="selectValue({{ $relProduct->id }}, '{{ $attr->slug }}', '{{ $val->slug }}')"
                                                title="{{ $val->getLabel() }}{{ $isOos ? ' (sem estoque)' : ($isCascade ? ' — altera outra seleção' : '') }}"
                                                @class([ 'w-5 h-5 rounded-full border-2 transition-all duration-150' , 'border-indigo-600 ring-2 ring-indigo-200 scale-110'=> $isSel,
                                                'border-gray-300 hover:scale-105 hover:border-gray-400' => ! $isSel && $isAvail && ! $isOos,
                                                'border-dashed border-gray-400 hover:scale-105 hover:border-indigo-400' => $isCascade,
                                                'border-gray-200 opacity-40' => $isOos,
                                                ])
                                                style="background-color: {{ $val->color_hex }}"
                                                ></button>
                                            @if ($isOos) {!! $xBadge !!} @endif
                                        </span>
                                        @else
                                        <span class="relative inline-block">
                                            <button
                                                wire:click.stop="selectValue({{ $relProduct->id }}, '{{ $attr->slug }}', '{{ $val->slug }}')"
                                                @class([ 'px-1.5 py-0.5 text-[10px] rounded border font-medium transition-all duration-150' , 'border-indigo-600 bg-indigo-50 text-indigo-700'=> $isSel,
                                                'border-gray-300 text-gray-700 hover:border-indigo-400' => ! $isSel && $isAvail && ! $isOos,
                                                'border-gray-200 text-gray-400' => $isAvail && $isOos,
                                                'border-dashed border-gray-400 text-gray-600 hover:border-indigo-400' => $isCascade,
                                                'border-dashed border-gray-200 text-gray-300' => ! $isAvail && $isOos,
                                                ])
                                                >{{ $val->getLabel() }}</button>
                                            @if ($isOos) {!! $xBadge !!} @endif
                                        </span>
                                        @endif
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            {{-- Preço --}}
                            <div class="flex items-baseline gap-1 ml-auto">
                                @if ($relOriginalPrice)
                                <p class="text-xs text-gray-400 line-through">
                                    R$ {{ number_format($relOriginalPrice, 2, ',', '.') }}
                                </p>
                                @endif

                                <p class="text-sm font-bold text-green-700">
                                    R$ {{ number_format($relPrice, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
                @endforeach
            </div>
        </div>

        {{-- ── COLUNA DIREITA: Resumo ──────────────────────────────────────── --}}
        <div class="shrink-0 w-full lg:w-80">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">

                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-900">Resumo</p>
                </div>

                <div class="p-4 space-y-2">
                    {{-- Produto principal --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="w-3.5 h-3.5 rounded-full bg-gray-900 flex items-center justify-center shrink-0">
                                <svg class="w-2 h-2 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </span>
                            <span class="text-xs text-gray-700 truncate">{{ $this->mainProduct->name }}</span>
                        </div>
                        <span class="text-xs font-semibold text-gray-900 shrink-0">
                            R$ {{ number_format($mainProductPrice, 2, ',', '.') }}
                        </span>
                    </div>

                    {{-- Relacionados --}}
                    @foreach ($this->relatedProducts as $relProduct)
                    @php
                    $isSel = in_array($relProduct->id, $selectedIds);
                    $isInStock = $this->isProductInStock($relProduct->id);
                    @endphp
                    <div @class([ 'flex items-start justify-between gap-2 transition-opacity' , 'opacity-35'=> ! $isSel,
                        ])>
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span @class([ 'w-3.5 h-3.5 rounded-full border flex items-center justify-center shrink-0 transition-colors' , 'bg-gray-900 border-gray-900'=> $isSel,
                                'border-gray-300' => ! $isSel,
                                ])>
                                @if ($isSel)
                                <svg class="w-2 h-2 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                                @endif
                            </span>
                            <span class="text-xs text-gray-700 truncate">{{ $relProduct->name }}</span>
                        </div>
                        <span class="text-xs font-semibold shrink-0 {{ $isSel ? 'text-gray-900' : 'text-gray-400' }}">
                            R$ {{ number_format($this->getProductPrice($relProduct->id), 2, ',', '.') }}
                        </span>
                    </div>
                    @endforeach
                </div>

                {{-- Total e botão --}}
                <div class="px-4 pb-4 pt-2 border-t border-gray-100 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-900">Total</span>
                        <span class="text-base font-bold text-gray-900">
                            R$ {{ number_format($this->total, 2, ',', '.') }}
                        </span>
                    </div>

                    {{-- Hints de pagamento (PIX / parcelamento) --}}
                    @php
                        $calc      = app(\App\Services\PaymentCalculator::class);
                        $cardMode  = $calc->cardDisplayMode();
                        $pixTotal  = $calc->pixPrice($this->total);
                        $instLabel = $calc->bestFreeInstallmentLabel($this->total);
                    @endphp
                    @if ((($cardMode === 'pix' || $cardMode === 'both') && $pixTotal) || (($cardMode === 'installments' || $cardMode === 'both') && $instLabel))
                    <div class="space-y-1 text-xs text-right text-gray-500 -mt-1">
                        @if (($cardMode === 'pix' || $cardMode === 'both') && $pixTotal)
                            <p class="text-green-600">ou <span class="font-semibold">R$ {{ number_format($pixTotal, 2, ',', '.') }}</span> no PIX</p>
                        @endif
                        @if (($cardMode === 'installments' || $cardMode === 'both') && $instLabel)
                            <p>ou {{ $instLabel }}</p>
                        @endif
                    </div>
                    @endif

                    @php $totalItens = count($selectedIds) + 1; @endphp

                    <button
                        wire:click="addAllToCart"
                        wire:loading.attr="disabled"
                        wire:target="addAllToCart"
                        class="w-full bg-green-700 text-white text-sm font-bold py-2.5 px-4 rounded-xl hover:bg-green-800 transition-colors disabled:opacity-60 flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="addAllToCart">
                            Adicionar {{ $totalItens }} {{ $totalItens === 1 ? 'item' : 'itens' }} ao carrinho
                        </span>
                        <span wire:loading wire:target="addAllToCart" class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Adicionando...</span>
                        </span>
                    </button>
                </div>

            </div>
        </div>

    </div>

</div>