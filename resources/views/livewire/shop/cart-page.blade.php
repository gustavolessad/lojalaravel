<div>

    {{-- ── Aviso de itens removidos por falta de estoque ───────────────── --}}
    @if (! empty($removedItems))
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div class="flex-1">
                <p class="text-sm font-semibold text-amber-800">
                    {{ count($removedItems) === 1 ? 'Um item foi removido' : count($removedItems) . ' itens foram removidos' }}
                    do carrinho por falta de estoque:
                </p>
                <ul class="mt-1 space-y-0.5">
                    @foreach ($removedItems as $name)
                        <li class="text-sm text-amber-700">• {{ $name }}</li>
                    @endforeach
                </ul>
            </div>
            <button wire:click="dismissRemovedNotice" class="text-amber-400 hover:text-amber-600 transition-colors flex-shrink-0" aria-label="Fechar">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    @if (!$cart || $cart->items->isEmpty())
    {{-- Carrinho vazio --}}
    <div class="text-center py-20">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <h2 class="text-xl font-semibold text-gray-700 mb-2">Seu carrinho está vazio</h2>
        <p class="text-gray-500 mb-6">Explore nossos produtos e adicione ao carrinho.</p>
        <a href="/produtos"
            class="inline-block px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
            Ver produtos
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Lista de itens --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ $cart->item_count }} {{ $cart->item_count === 1 ? 'item' : 'itens' }}
                </h2>
                <button wire:click="clearCart"
                    wire:confirm="Tem certeza que deseja limpar o carrinho?"
                    class="text-sm text-red-600 hover:text-red-800 transition-colors">
                    Limpar carrinho
                </button>
            </div>

            @foreach ($cart->items as $item)
            @php $maxStock = $item->max_stock; @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex gap-4">
                {{-- Imagem --}}
                <div class="w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100">
                    @if ($item->image_url)
                    <img src="{{ $item->image_url }}" alt="{{ $item->product->name }}"
                        class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    @endif
                </div>

                {{-- Detalhes --}}
                <div class="flex-1 min-w-0">

                        <h3 class="font-medium text-sm truncate">
                            <a href="{{ route('product.show', $item->product->slug) }}"
                               class="text-gray-900 hover:text-indigo-600 transition-colors">
                                {{ $item->product->name }}
                            </a>
                        </h3>
                    @if ($item->variant)
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $item->variant->label }}
                    </p>
                    @endif
                    @if ($item->original_price)
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="text-xs text-gray-400 line-through">
                                R$ {{ number_format($item->original_price, 2, ',', '.') }}
                            </span>
                            <span class="text-sm font-semibold text-red-600">
                                R$ {{ number_format($item->unit_price, 2, ',', '.') }}
                            </span>
                        </div>
                    @else
                        <p class="text-sm font-semibold text-indigo-600 mt-1">
                            R$ {{ number_format($item->unit_price, 2, ',', '.') }}
                        </p>
                    @endif
                    @if ($maxStock > 0 && $maxStock <= 5)
                        <p class="text-xs text-amber-600 mt-1">
                            apenas {{ $maxStock }} {{ $maxStock === 1 ? 'disponível' : 'disponíveis' }}
                        </p>
                    @endif
                </div>

                {{-- Controles --}}
                <div class="flex flex-col items-end gap-2">
                    {{-- Quantidade --}}
                    <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden text-sm">
                        <button
                            wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                            @disabled($item->quantity <= 1)
                            class="px-2 py-1 text-gray-500 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed"
                        >−</button>
                        <span class="px-3 font-medium tabular-nums">{{ $item->quantity }}</span>
                        <button
                            wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                            @disabled($item->quantity >= $maxStock)
                            class="px-2 py-1 text-gray-500 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed"
                        >+</button>
                    </div>

                    {{-- Subtotal --}}
                    <p class="text-sm font-bold text-gray-900">
                        R$ {{ number_format($item->subtotal, 2, ',', '.') }}
                    </p>

                    {{-- Remover --}}
                    <button wire:click="removeItem({{ $item->id }})"
                        class="text-xs text-red-500 hover:text-red-700 transition-colors">
                        Remover
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Resumo --}}
        <div class="space-y-4">

            {{-- Simulação de frete --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Calcular frete</h3>
                <form wire:submit.prevent="simulateShipping" class="flex gap-2">
                    <input type="text"
                        wire:model="cep"
                        placeholder="00000-000"
                        maxlength="9"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="submit"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                        <span wire:loading.remove wire:target="simulateShipping">OK</span>
                        <span wire:loading wire:target="simulateShipping">...</span>
                    </button>
                </form>

                @if ($shippingError)
                <p class="mt-2 text-xs text-red-600">{{ $shippingError }}</p>
                @endif

                @if ($shipping)
                <ul class="mt-3 space-y-2">
                    @foreach ($shipping as $option)
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
            </div>

            {{-- Cupom de desconto --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Cupom de desconto</h3>

                @if ($cart->coupon_code)
                    {{-- Cupom aplicado --}}
                    <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                        <div>
                            <span class="font-mono font-semibold text-green-700 text-sm">{{ $cart->coupon_code }}</span>
                            <span class="ml-2 text-xs text-green-600">
                                − R$ {{ number_format($cart->coupon_discount, 2, ',', '.') }}
                            </span>
                        </div>
                        <button wire:click="removeCoupon"
                                class="text-green-500 hover:text-red-500 transition-colors ml-3 text-xs underline">
                            Remover
                        </button>
                    </div>
                @else
                    {{-- Campo de cupom --}}
                    <form wire:submit.prevent="applyCoupon" class="flex gap-2">
                        <input wire:model="couponCode"
                               type="text"
                               placeholder="Código do cupom"
                               class="flex-1 text-sm border border-gray-300 rounded-lg px-3 py-2 uppercase focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50">
                            Aplicar
                        </button>
                    </form>

                    @if ($couponError)
                        <p class="mt-2 text-xs text-red-600">{{ $couponError }}</p>
                    @endif
                    @if ($couponSuccess)
                        <p class="mt-2 text-xs text-green-600">{{ $couponSuccess }}</p>
                    @endif
                @endif
            </div>

            {{-- Totais e finalizar --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Resumo do pedido</h3>

                <div class="space-y-2 text-sm">
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

                    <div class="flex justify-between text-gray-600">
                        <span>Frete</span>
                        <span class="text-gray-400">Calcular acima</span>
                    </div>
                </div>

                <div class="border-t border-gray-100 mt-4 pt-4 flex justify-between font-bold text-gray-900">
                    <span>Total</span>
                    <span>R$ {{ number_format($cart->total, 2, ',', '.') }}</span>
                </div>

                {{-- Hints de pagamento --}}
                @php
                    $calc       = app(\App\Services\PaymentCalculator::class);
                    $pixTotal   = $calc->pixPrice((float) $cart->total);
                    $instLabel  = $calc->bestFreeInstallmentLabel((float) $cart->total);
                @endphp
                @if ($pixTotal || $instLabel)
                    <div class="mt-3 space-y-1 text-sm text-end">
                        @if ($pixTotal)
                            <p class="text-green-700">
                                ou <span class="font-semibold">R$ {{ number_format($pixTotal, 2, ',', '.') }}</span> no PIX
                            </p>
                        @endif
                        @if ($instLabel)
                            <p class="text-gray-500">ou {{ $instLabel }}</p>
                        @endif
                    </div>
                @endif

                <a href="{{ route('checkout.index') }}"
                    class="mt-5 w-full flex items-center justify-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors text-sm">
                    Finalizar compra
                </a>

                <a href="/produtos"
                    class="mt-3 w-full flex items-center justify-center px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors text-sm">
                    Continuar comprando
                </a>
            </div>

        </div>
    </div>
    @endif
</div>