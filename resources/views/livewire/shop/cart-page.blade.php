<div>

    {{-- ── Aviso de itens removidos por falta de estoque ───────────────── --}}
    @if (! empty($removedItems))
    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3">
        <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-amber-800">
                {{ count($removedItems) === 1 ? '1 item removido' : count($removedItems) . ' itens removidos' }} por falta de estoque
            </p>
            <ul class="mt-1 space-y-0.5">
                @foreach ($removedItems as $name)
                    <li class="text-xs text-amber-700">• {{ $name }}</li>
                @endforeach
            </ul>
        </div>
        <button wire:click="dismissRemovedNotice" class="text-amber-400 hover:text-amber-600 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    @if (!$cart || $cart->items->isEmpty())

    {{-- ── Carrinho vazio ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
            </svg>
        </div>
        <h2 class="text-base font-semibold text-gray-900 mb-1">Seu carrinho está vazio</h2>
        <p class="text-sm text-gray-500 mb-6">Explore nossos produtos e adicione ao carrinho.</p>
        <a href="/produtos"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-xl hover:bg-gray-800 transition-colors">
            Ver produtos
        </a>
    </div>

    @else

    {{-- ── Layout principal ────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ════════════════════════════════════════════════════════════════
             COLUNA ESQUERDA: itens + frete + cupom
        ════════════════════════════════════════════════════════════════ --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- ── Lista de itens ───────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">

                {{-- Header da lista --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $cart->item_count }} {{ $cart->item_count === 1 ? 'item' : 'itens' }}
                    </p>
                    <button wire:click="clearCart"
                        wire:confirm="Tem certeza que deseja limpar o carrinho?"
                        class="text-xs text-gray-400 hover:text-red-500 transition-colors">
                        Limpar tudo
                    </button>
                </div>

                {{-- Itens --}}
                <div class="divide-y divide-gray-100">
                    @foreach ($cart->items as $item)
                    @php $maxStock = $item->max_stock; @endphp
                    <div class="flex gap-3 sm:gap-4 p-4 sm:p-5">

                        {{-- Imagem --}}
                        <div class="w-20 h-20 sm:w-24 sm:h-24 flex-shrink-0 rounded-xl overflow-hidden bg-gray-100 border border-gray-100">
                            @if ($item->image_url)
                                <img src="{{ $item->image_url }}" alt="{{ $item->product->name }}"
                                    class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Detalhes --}}
                        <div class="flex-1 min-w-0 flex flex-col justify-between">
                            <div>
                                <a href="{{ route('product.show', $item->product->slug) }}"
                                    class="text-sm font-medium text-gray-900 hover:text-indigo-600 transition-colors line-clamp-2 leading-snug">
                                    {{ $item->product->name }}
                                </a>
                                @if ($item->variant)
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $item->variant->label }}</p>
                                @endif
                                @if ($maxStock > 0 && $maxStock <= 5)
                                    <p class="text-xs text-amber-600 mt-1 font-medium">
                                        apenas {{ $maxStock }} {{ $maxStock === 1 ? 'disponível' : 'disponíveis' }}
                                    </p>
                                @endif
                            </div>

                            {{-- Preço e controles --}}
                            <div class="flex items-center justify-between mt-3 gap-3">
                                <div>
                                    @if ($item->original_price)
                                        <span class="text-xs text-gray-400 line-through block">
                                            R$ {{ number_format($item->original_price, 2, ',', '.') }}
                                        </span>
                                        <span class="text-sm font-bold text-red-600">
                                            R$ {{ number_format($item->unit_price, 2, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-sm font-bold text-gray-900">
                                            R$ {{ number_format($item->unit_price, 2, ',', '.') }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2">
                                    {{-- Quantidade --}}
                                    <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden text-sm">
                                        <button
                                            wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                            @disabled($item->quantity <= 1)
                                            class="w-8 h-8 flex items-center justify-center text-gray-500 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
                                        </button>
                                        <span class="w-8 text-center font-semibold text-gray-900 tabular-nums text-sm">{{ $item->quantity }}</span>
                                        <button
                                            wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                            @disabled($item->quantity >= $maxStock)
                                            class="w-8 h-8 flex items-center justify-center text-gray-500 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        </button>
                                    </div>

                                    {{-- Subtotal (oculto em xs) --}}
                                    <span class="hidden sm:block text-sm font-bold text-gray-900 tabular-nums w-20 text-right">
                                        R$ {{ number_format($item->subtotal, 2, ',', '.') }}
                                    </span>

                                    {{-- Remover --}}
                                    <button wire:click="removeItem({{ $item->id }})"
                                        class="w-7 h-7 flex items-center justify-center text-gray-300 hover:text-red-500 transition-colors rounded-lg hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ── Frete + Cupom (accordions independentes lado a lado) ──── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-start">

                {{-- Frete --}}
                <div x-data="{ open: @js((bool) $shipping) }"
                    class="bg-white rounded-2xl border transition-colors"
                    :class="open ? 'border-gray-300' : 'border-gray-200'">
                    <button type="button" @click="open = !open"
                        class="w-full flex items-center gap-2.5 px-4 py-3 text-sm font-medium text-gray-700 text-left">
                        <svg class="w-4 h-4 shrink-0 transition-colors" :class="open ? 'text-gray-900' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                        @if ($shipping && $shippingCity)
                            <span class="truncate flex-1 text-gray-700">{{ $shippingCity }}</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 shrink-0"></span>
                        @else
                            <span class="truncate flex-1">Calcular frete</span>
                        @endif
                        <svg class="w-3.5 h-3.5 text-gray-300 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="px-4 pb-4 border-t border-gray-100 pt-3">
                        <form wire:submit.prevent="simulateShipping" class="flex gap-2">
                            <input type="text"
                                wire:model="cep"
                                wire:keydown.enter.prevent="simulateShipping"
                                placeholder="00000-000"
                                maxlength="9"
                                class="flex-1 min-w-0 px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition">
                            <button type="submit"
                                wire:loading.attr="disabled"
                                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors disabled:opacity-50 shrink-0">
                                <span wire:loading.remove wire:target="simulateShipping">Calcular</span>
                                <span wire:loading wire:target="simulateShipping">
                                    <svg class="w-4 h-4 animate-spin text-gray-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                </span>
                            </button>
                        </form>
                        @if ($shippingError)
                            <p class="mt-2 text-xs text-red-500">{{ $shippingError }}</p>
                        @endif
                        @if ($shipping)
                            <ul class="mt-4 space-y-2">
                                @foreach ($shipping as $option)
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
                        @endif
                    </div>
                </div>

                {{-- Cupom --}}
                <div x-data="{ open: @js((bool) $cart->coupon_code) }"
                    class="bg-white rounded-2xl border transition-colors"
                    :class="open ? 'border-gray-300' : 'border-gray-200'">
                    <button type="button" @click="open = !open"
                        class="w-full flex items-center gap-2.5 px-4 py-3 text-sm font-medium text-left">
                        @if ($cart->coupon_code)
                            <svg class="w-4 h-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span class="truncate flex-1 text-green-700 font-semibold">{{ $cart->coupon_code }}</span>
                        @else
                            <svg class="w-4 h-4 shrink-0 transition-colors" :class="open ? 'text-gray-900' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185Z" />
                            </svg>
                            <span class="truncate flex-1 text-gray-700">Cupom</span>
                        @endif
                        <svg class="w-3.5 h-3.5 text-gray-300 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="px-4 pb-4 border-t border-gray-100 pt-3">
                        @if ($cart->coupon_code)
                            <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-3 h-[38px]">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="font-mono font-semibold text-green-700 text-xs truncate">{{ $cart->coupon_code }}</span>
                                    <span class="text-xs text-green-600 shrink-0">− Economizou R$ {{ number_format($cart->coupon_discount, 2, ',', '.') }}</span>
                                </div>
                                <button wire:click="removeCoupon" class="text-xs text-gray-400 hover:text-red-500 transition-colors ml-2 shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"></path>
                                        </svg>
                                </button>
                            </div>
                        @else
                            <form wire:submit.prevent="applyCoupon" class="flex gap-2">
                                <input wire:model="couponCode"
                                    type="text"
                                    placeholder="Código"
                                    class="flex-1 min-w-0 text-sm border border-gray-200 rounded-xl px-3 py-2 uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition">
                                <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors disabled:opacity-50 shrink-0">
                                    Aplicar
                                </button>
                            </form>
                            @if ($couponError)
                                <p class="mt-2 text-xs text-red-500">{{ $couponError }}</p>
                            @endif
                            @if ($couponSuccess)
                                <p class="mt-2 text-xs text-green-600">{{ $couponSuccess }}</p>
                            @endif
                        @endif
                    </div>
                </div>

            </div>

        </div>

        {{-- ════════════════════════════════════════════════════════════════
             COLUNA DIREITA: resumo do pedido
        ════════════════════════════════════════════════════════════════ --}}
        <div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5 sticky top-6">

                <h3 class="text-sm font-semibold text-gray-900 mb-4">Resumo do pedido</h3>

                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span>R$ {{ number_format($cart->subtotal, 2, ',', '.') }}</span>
                    </div>

                    @if ($cart->coupon_discount > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Desconto ({{ $cart->coupon_code }})</span>
                        <span>− R$ {{ number_format($cart->coupon_discount, 2, ',', '.') }}</span>
                    </div>
                    @endif

                    <div class="flex justify-between text-gray-400 text-xs">
                        <span>Frete</span>
                        <span>Calcular ao lado</span>
                    </div>
                </div>

                <div class="border-t border-gray-100 mt-4 pt-4 flex justify-between font-bold text-gray-900">
                    <span>Total</span>
                    <span>R$ {{ number_format($cart->total, 2, ',', '.') }}</span>
                </div>

                {{-- Hints de pagamento --}}
                @php
                    $calc = app(\App\Services\PaymentCalculator::class);
                    $pixTotal = $calc->pixPrice((float) $cart->total);
                    $instLabel = $calc->bestFreeInstallmentLabel((float) $cart->total);
                @endphp
                @if ($pixTotal || $instLabel)
                <div class="mt-3 space-y-1 text-xs text-right text-gray-500 border-t border-gray-50 pt-3">
                    @if ($pixTotal)
                        <p class="text-green-600">ou <span class="font-semibold">R$ {{ number_format($pixTotal, 2, ',', '.') }}</span> no PIX</p>
                    @endif
                    @if ($instLabel)
                        <p>ou {{ $instLabel }}</p>
                    @endif
                </div>
                @endif

                <a href="{{ route('checkout.index') }}"
                    class="mt-5 w-full flex items-center justify-center gap-2 px-5 py-3 bg-green-700 text-white text-sm font-semibold rounded-xl hover:bg-green-800 transition-colors">
                    Finalizar compra
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>

                <a href="/produtos"
                    class="mt-2.5 w-full flex items-center justify-center px-5 py-2.5 border border-gray-200 text-gray-600 text-sm font-medium rounded-xl hover:bg-gray-50 transition-colors">
                    Continuar comprando
                </a>
            </div>
        </div>

    </div>

    @endif

</div>
