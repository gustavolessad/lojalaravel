@extends('layouts.account')

@section('title', 'Efetuar Pagamento — Pedido #' . $order->order_number)

@section('page-content')
<div>

{{-- Voltar --}}
<div class="mb-5">
    <a href="{{ route('account.orders.show', $order->order_number) }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Pedido #{{ $order->order_number }}
    </a>
</div>

{{-- Header --}}
<div class="bg-white rounded-2xl border border-gray-200 p-5 mb-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Efetuar Pagamento</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Pedido #{{ $order->order_number }} · Total:
                <span class="font-semibold text-gray-800">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
            </p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium border bg-amber-50 text-amber-700 border-amber-200">
            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
            Aguardando pagamento
        </span>
    </div>
</div>

{{-- Erro geral --}}
@if ($errorMessage)
<div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 mb-4 flex items-start gap-3">
    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
    </svg>
    <p class="text-sm text-red-700">{{ $errorMessage }}</p>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Coluna principal: escolha de método + formulário --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Seletor de método --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Forma de Pagamento</h2>
            </div>
            <div class="p-4 grid grid-cols-2 gap-3">

                {{-- PIX --}}
                <button type="button"
                        wire:click="switchMethod('pix')"
                        class="flex items-center gap-3 rounded-xl border-2 px-4 py-3 transition-all text-left
                               {{ $paymentMethod === 'pix' ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:border-gray-300 bg-white' }}">
                    <div class="w-9 h-9 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-green-700" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.944 17.97L4.58 10.607 7.41 7.78l4.534 4.534 4.534-4.534 2.828 2.828-7.362 7.362z"/>
                            <path d="M7.41 16.22l-2.828-2.828 7.362-7.362 7.362 7.362-2.828 2.828-4.534-4.534-4.534 4.534z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900">PIX</p>
                        @if ($pixTotal && $pixTotal < $order->total)
                            <p class="text-xs text-green-600 font-medium mt-0.5">
                                R$ {{ number_format($pixTotal, 2, ',', '.') }} com desconto
                            </p>
                        @else
                            <p class="text-xs text-gray-500 mt-0.5">Aprovação imediata</p>
                        @endif
                    </div>
                    @if ($paymentMethod === 'pix')
                        <svg class="w-4 h-4 text-gray-900 ml-auto shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    @endif
                </button>

                {{-- Cartão --}}
                <button type="button"
                        wire:click="switchMethod('credit_card')"
                        class="flex items-center gap-3 rounded-xl border-2 px-4 py-3 transition-all text-left
                               {{ $paymentMethod === 'credit_card' ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:border-gray-300 bg-white' }}">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900">Cartão de Crédito</p>
                        <p class="text-xs text-gray-500 mt-0.5">Até {{ count($installmentOptions) }}× parcelas</p>
                    </div>
                    @if ($paymentMethod === 'credit_card')
                        <svg class="w-4 h-4 text-gray-900 ml-auto shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    @endif
                </button>

            </div>
        </div>

        {{-- ── PIX ──────────────────────────────────────────────────────── --}}
        @if ($paymentMethod === 'pix')
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Pagamento via PIX</h2>
            </div>
            <div class="p-5">

                @if ($pixData)
                {{-- QR code existente ou recém-gerado --}}
                <div class="flex flex-col sm:flex-row items-center gap-6">
                    @if (!empty($pixData['qrcode']))
                    <div class="shrink-0">
                        <img src="data:image/png;base64,{{ $pixData['qrcode'] }}"
                             alt="QR Code PIX"
                             class="w-44 h-44 rounded-xl border border-gray-200 bg-white p-1">
                    </div>
                    @endif

                    <div class="flex-1 w-full space-y-4">
                        @if (!empty($pixData['expires_at']))
                        <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                            QR Code válido até {{ \Carbon\Carbon::parse($pixData['expires_at'])->format('d/m/Y \à\s H:i') }}
                        </p>
                        @endif

                        @if (!empty($pixData['copy_paste']))
                        <div x-data="{ copied: false }">
                            <p class="text-xs font-medium text-gray-700 mb-2">PIX Copia e Cola</p>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly value="{{ $pixData['copy_paste'] }}"
                                       class="flex-1 min-w-0 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs font-mono text-gray-700 focus:outline-none select-all truncate">
                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ $pixData['copy_paste'] }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                        class="shrink-0 inline-flex items-center gap-1.5 bg-gray-900 hover:bg-gray-700 text-white text-xs font-medium px-3 py-2.5 rounded-xl transition-colors">
                                    <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                                    </svg>
                                    <svg x-show="copied" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                    <span x-text="copied ? 'Copiado!' : 'Copiar'"></span>
                                </button>
                            </div>
                        </div>
                        @endif

                        <p class="text-xs text-gray-500">Após o pagamento, o pedido será confirmado automaticamente.</p>

                        {{-- Botão para gerar novo QR --}}
                        <button type="button"
                                wire:click="generatePix"
                                wire:loading.attr="disabled"
                                class="text-xs text-gray-500 hover:text-gray-700 underline underline-offset-2 transition-colors">
                            <span wire:loading.remove wire:target="generatePix">Gerar novo QR Code</span>
                            <span wire:loading wire:target="generatePix">Gerando...</span>
                        </button>
                    </div>
                </div>

                @else
                {{-- Nenhum QR disponível: exibir botão para gerar --}}
                <div class="text-center py-6">
                    <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-green-700" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.944 17.97L4.58 10.607 7.41 7.78l4.534 4.534 4.534-4.534 2.828 2.828-7.362 7.362z"/>
                            <path d="M7.41 16.22l-2.828-2.828 7.362-7.362 7.362 7.362-2.828 2.828-4.534-4.534-4.534 4.534z"/>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-700 font-medium mb-1">Gere o QR Code PIX para pagar</p>
                    <p class="text-xs text-gray-500 mb-5">O código expira em 24 horas após a geração.</p>
                    <button type="button"
                            wire:click="generatePix"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 bg-gray-900 hover:bg-gray-700 disabled:opacity-60 text-white text-sm font-medium px-6 py-3 rounded-xl transition-colors">
                        <span wire:loading.remove wire:target="generatePix">
                            <svg class="w-4 h-4 inline mr-1" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M11.944 17.97L4.58 10.607 7.41 7.78l4.534 4.534 4.534-4.534 2.828 2.828-7.362 7.362z"/>
                                <path d="M7.41 16.22l-2.828-2.828 7.362-7.362 7.362 7.362-2.828 2.828-4.534-4.534-4.534 4.534z"/>
                            </svg>
                            Gerar QR Code PIX
                        </span>
                        <span wire:loading wire:target="generatePix" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Gerando...
                        </span>
                    </button>
                </div>
                @endif

            </div>
        </div>
        @endif

        {{-- ── Cartão ──────────────────────────────────────────────────── --}}
        @if ($paymentMethod === 'credit_card')
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Dados do Cartão</h2>
            </div>
            <div class="p-5 space-y-4"
                 x-data="{
                     formatCard(e) {
                         let v = e.target.value.replace(/\D/g, '').substring(0, 19);
                         e.target.value = v.replace(/(\d{4})(?=\d)/g, '$1 ');
                         $wire.cardNumber = v;
                     },
                     formatExpiry(e) {
                         let v = e.target.value.replace(/\D/g, '').substring(0, 4);
                         if (v.length >= 3) v = v.substring(0,2) + '/' + v.substring(2);
                         e.target.value = v;
                         $wire.cardExpiry = v;
                     }
                 }">

                {{-- Nome no cartão --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Nome no cartão</label>
                    <input type="text"
                           wire:model.blur="cardHolder"
                           placeholder="Como está impresso no cartão"
                           class="w-full rounded-xl border @error('cardHolder') border-red-300 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
                    @error('cardHolder') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Número do cartão --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Número do cartão</label>
                    <input type="text"
                           inputmode="numeric"
                           maxlength="23"
                           placeholder="0000 0000 0000 0000"
                           @input="formatCard($event)"
                           class="w-full rounded-xl border @error('cardNumber') border-red-300 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-sm font-mono text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
                    @error('cardNumber') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Validade + CVV --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Validade</label>
                        <input type="text"
                               inputmode="numeric"
                               maxlength="5"
                               placeholder="MM/AA"
                               @input="formatExpiry($event)"
                               class="w-full rounded-xl border @error('cardExpiry') border-red-300 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-sm font-mono text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
                        @error('cardExpiry') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">CVV</label>
                        <input type="text"
                               wire:model.blur="cardCvv"
                               inputmode="numeric"
                               maxlength="4"
                               placeholder="000"
                               class="w-full rounded-xl border @error('cardCvv') border-red-300 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-sm font-mono text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
                        @error('cardCvv') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Parcelas --}}
                @if (count($installmentOptions) > 1)
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Parcelas</label>
                    <select wire:model="installments"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10">
                        @foreach ($installmentOptions as $opt)
                        <option value="{{ $opt['value'] }}">
                            {{ $opt['label'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Botão pagar --}}
                <button type="button"
                        wire:click="payWithCard"
                        wire:loading.attr="disabled"
                        class="w-full flex items-center justify-center gap-2 bg-gray-900 hover:bg-gray-700 disabled:opacity-60 text-white text-sm font-semibold px-6 py-3.5 rounded-xl transition-colors">
                    <span wire:loading.remove wire:target="payWithCard">
                        <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        Pagar com Cartão
                    </span>
                    <span wire:loading wire:target="payWithCard" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processando...
                    </span>
                </button>

            </div>
        </div>
        @endif

    </div>

    {{-- Resumo do pedido (coluna estreita) --}}
    <div class="space-y-4">

        {{-- Itens --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Itens do Pedido</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($order->items as $item)
                <div class="flex items-start gap-3 px-5 py-3.5">
                    @php
                        $thumb = $item->variant?->getFirstMediaUrl('variant-cover', 'thumb')
                              ?: $item->product?->getFirstMediaUrl('cover', 'thumb');
                    @endphp
                    @if ($thumb)
                    <img src="{{ $thumb }}" alt="{{ $item->product_name }}"
                         class="w-10 h-10 rounded-lg object-cover border border-gray-100 shrink-0">
                    @else
                    <div class="w-10 h-10 rounded-lg bg-gray-100 shrink-0"></div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-900 leading-snug">{{ $item->product_name }}</p>
                        @if ($item->variant_label)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $item->variant_label }}</p>
                        @endif
                        <p class="text-xs text-gray-500 mt-0.5">{{ $item->quantity }}× R$ {{ number_format($item->unit_price, 2, ',', '.') }}</p>
                    </div>
                    <p class="text-xs font-semibold text-gray-900 shrink-0">R$ {{ number_format($item->total, 2, ',', '.') }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Totais --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="space-y-2.5 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="text-gray-900">R$ {{ number_format($order->subtotal, 2, ',', '.') }}</span>
                </div>

                @if ($order->shipping_cost > 0)
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Frete ({{ $order->shipping_method }})</span>
                    <span class="text-gray-900">R$ {{ number_format($order->shipping_cost, 2, ',', '.') }}</span>
                </div>
                @else
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Frete</span>
                    <span class="text-green-600 font-medium">Grátis</span>
                </div>
                @endif

                @if ($order->discount > 0)
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Desconto</span>
                    <span class="text-green-600">−R$ {{ number_format($order->discount, 2, ',', '.') }}</span>
                </div>
                @endif

                @if ($order->pix_discount > 0)
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Desconto PIX</span>
                    <span class="text-green-600">−R$ {{ number_format($order->pix_discount, 2, ',', '.') }}</span>
                </div>
                @endif

                <div class="border-t border-gray-100 pt-2.5 flex items-center justify-between font-semibold">
                    <span class="text-gray-900">Total</span>
                    <span class="text-gray-900 text-base">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Endereço de entrega --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Entrega</h2>
            <div class="text-xs text-gray-600 space-y-0.5">
                <p class="font-medium text-gray-900">{{ $order->shipping_name }}</p>
                <p>{{ $order->shipping_street }}, {{ $order->shipping_number }}{{ $order->shipping_complement ? ' — ' . $order->shipping_complement : '' }}</p>
                <p>{{ $order->shipping_district }}, {{ $order->shipping_city }}/{{ $order->shipping_state }}</p>
                <p class="font-mono text-gray-500">CEP {{ $order->shipping_zip }}</p>
                @if ($order->shipping_method)
                <p class="text-gray-400 pt-1">{{ $order->shipping_method }}{{ $order->shipping_days ? ' · ' . $order->shipping_days . ' dias úteis' : '' }}</p>
                @endif
            </div>
        </div>

    </div>

</div>

</div>
@endsection
