<div>
{{-- Mobile: link direto para o carrinho --}}
<a href="{{ route('cart.index') }}" class="relative inline-flex items-center p-2 text-gray-900 hover:text-gray-800 md:hidden">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>

    @if ($count > 0)
        <span class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-green-600 rounded-full">
            {{ $count > 99 ? '99+' : $count }}
        </span>
    @endif
</a>

{{-- Desktop: dropdown com hover --}}
<div class="relative hidden md:block" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
    <button class="relative inline-flex items-center p-2 text-gray-900 hover:text-gray-800">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>

        @if ($count > 0)
            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-green-600 rounded-full">
                {{ $count > 99 ? '99+' : $count }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        x-cloak
        class="absolute right-0 top-full mt-2 w-96 bg-white rounded-xl shadow-2xl border border-gray-200 z-50 overflow-hidden"
    >
        @if ($count === 0)
            {{-- Carrinho vazio --}}
            <div class="p-8 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="text-sm text-gray-500">Seu carrinho está vazio</p>
            </div>
        @else
            {{-- Lista de itens --}}
            <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                @foreach ($items as $item)
                    <div class="flex items-center gap-3 p-3 hover:bg-gray-50 transition" wire:key="dropdown-item-{{ $item->id }}">
                        {{-- Imagem --}}
                        <div class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100">
                            @if ($item->image_url)
                                <img src="{{ $item->image_url }}" alt="{{ $item->product->name }}"
                                     class="w-full h-full object-cover" loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <a href="{{ url($item->product->slug . '/p') }}" class="text-xs font-semibold text-black truncate block hover:underline">{{ $item->product->name }}</a>

                            @if ($item->variant)
                                <p class="text-xs text-gray-500 truncate">
                                    {{ $item->variant->attributeValues->map(fn ($av) => $av->getLabel())->join(' / ') }}
                                </p>
                            @endif

                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">
                                    {{ $item->quantity }}x R$ {{ number_format($item->unit_price, 2, ',', '.') }}
                                </span>
                                <span class="text-xs font-semibold text-black">
                                    R$ {{ number_format($item->subtotal, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        {{-- Remover --}}
                        <button wire:click="removeItem({{ $item->id }})"
                                class="flex-shrink-0 self-start p-1 text-gray-400 hover:text-red-500 transition"
                                title="Remover produto do carrinho">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>

            @if ($totalItems > 5)
                <div class="px-3 py-1.5 bg-gray-50 text-center">
                    <span class="text-xs text-gray-500">e mais {{ $totalItems - 5 }} {{ $totalItems - 5 === 1 ? 'item' : 'itens' }}</span>
                </div>
            @endif

            {{-- Subtotal + pagamento --}}
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Subtotal</span>
                    <span class="text-base font-bold text-gray-900">R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                </div>

                @if ($pixPrice)
                    <div class="text-end">
                        <span class="text-sm font-semibold text-green-700">R$ {{ number_format($pixPrice, 2, ',', '.') }} no pix</span>
                    </div>
                @endif

                @if ($instLabel)
                    <p class="text-xs text-gray-500 text-right">ou {{ $instLabel }}</p>
                @endif
            </div>

            {{-- Botões --}}
            <div class="px-4 py-3 space-y-2 border-t border-gray-200">
                <a href="{{ route('checkout.index') }}"
                   class="block w-full text-center px-4 py-2.5 bg-green-700 text-white text-sm font-semibold rounded-xl hover:bg-green-800 transition">
                    Finalizar compra
                </a>
                <a href="{{ route('cart.index') }}"
                   class="block w-full text-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition">
                    Ver carrinho
                </a>
            </div>
        @endif
    </div>
</div>
</div>
