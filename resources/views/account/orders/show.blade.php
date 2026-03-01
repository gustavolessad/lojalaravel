@extends('layouts.account')

@section('title', 'Pedido #' . $order->order_number)

@section('page-content')

@php
    $statusConfig = match($order->status) {
        'pending'    => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-400'],
        'processing' => ['bg' => 'bg-blue-50',  'text' => 'text-blue-700',  'border' => 'border-blue-200',  'dot' => 'bg-blue-400'],
        'paid'       => ['bg' => 'bg-indigo-50','text' => 'text-indigo-700','border' => 'border-indigo-200','dot' => 'bg-indigo-400'],
        'shipped'    => ['bg' => 'bg-purple-50','text' => 'text-purple-700','border' => 'border-purple-200','dot' => 'bg-purple-400'],
        'delivered'  => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-green-200', 'dot' => 'bg-green-500'],
        'cancelled'  => ['bg' => 'bg-red-50',   'text' => 'text-red-700',   'border' => 'border-red-200',   'dot' => 'bg-red-400'],
        'refunded'   => ['bg' => 'bg-gray-50',  'text' => 'text-gray-600',  'border' => 'border-gray-200',  'dot' => 'bg-gray-400'],
        default      => ['bg' => 'bg-gray-50',  'text' => 'text-gray-600',  'border' => 'border-gray-200',  'dot' => 'bg-gray-400'],
    };
@endphp

{{-- Voltar --}}
<div class="mb-5">
    <a href="{{ route('account.orders.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Meus Pedidos
    </a>
</div>

{{-- Header --}}
<div class="bg-white rounded-2xl border border-gray-200 p-5 mb-4">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Pedido #{{ $order->order_number }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">Realizado em {{ $order->created_at->format('d/m/Y \à\s H:i') }}</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium border {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} {{ $statusConfig['border'] }}">
            <span class="w-2 h-2 rounded-full {{ $statusConfig['dot'] }}"></span>
            {{ $order->status_label }}
        </span>
    </div>
</div>

{{-- Rastreamento (se disponível) --}}
{{-- PIX pendente --}}
@if ($order->payment_method === 'pix' && $order->payment_status === 'pending')
@php $pd = $order->payment_data ?? []; @endphp
@if (!empty($pd['pix_qrcode']) || !empty($pd['pix_copy_paste']))
<div class="bg-green-50 border border-green-200 rounded-2xl p-5 mb-4">
    <div class="flex items-start gap-3 mb-4">
        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4 h-4 text-green-700" viewBox="0 0 24 24" fill="currentColor">
                <path d="M11.944 17.97L4.58 10.607 7.41 7.78l4.534 4.534 4.534-4.534 2.828 2.828-7.362 7.362z"/>
                <path d="M7.41 16.22l-2.828-2.828 7.362-7.362 7.362 7.362-2.828 2.828-4.534-4.534-4.534 4.534z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-green-800">Pagamento PIX aguardando</p>
            <p class="text-sm text-green-700 mt-0.5">Escaneie o QR code ou copie o código PIX para finalizar o pagamento.</p>
            @if (!empty($pd['expires_at']))
                <p class="text-xs text-green-600 mt-1">Válido até: {{ \Carbon\Carbon::parse($pd['expires_at'])->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>

    <div class="flex flex-col sm:flex-row items-center gap-6">
        @if (!empty($pd['pix_qrcode']))
        <div class="shrink-0">
            <img src="data:image/png;base64,{{ $pd['pix_qrcode'] }}"
                 alt="QR Code PIX"
                 class="w-40 h-40 rounded-xl border border-green-200 bg-white p-1">
        </div>
        @endif

        @if (!empty($pd['pix_copy_paste']))
        <div class="flex-1 w-full" x-data="{ copied: false }">
            <p class="text-xs font-medium text-green-800 mb-2">Pix Copia e Cola</p>
            <div class="flex items-center gap-2">
                <input type="text" readonly value="{{ $pd['pix_copy_paste'] }}"
                       class="flex-1 min-w-0 rounded-xl border border-green-200 bg-white px-3 py-2 text-xs font-mono text-gray-700 focus:outline-none select-all truncate">
                <button type="button"
                        @click="navigator.clipboard.writeText('{{ $pd['pix_copy_paste'] }}'); copied = true; setTimeout(() => copied = false, 2500)"
                        class="shrink-0 inline-flex items-center gap-1.5 bg-green-700 hover:bg-green-800 text-white text-xs font-medium px-3 py-2 rounded-xl transition-colors">
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
    </div>
</div>
@endif
@endif

@if ($order->tracking_code)
<div class="bg-purple-50 border border-purple-200 rounded-2xl p-5 mb-4">
    <div class="flex items-start gap-3">
        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-purple-800">Pedido enviado!</p>
            <p class="text-sm text-purple-700 mt-0.5">
                Código de rastreamento: <span class="font-mono font-semibold">{{ $order->tracking_code }}</span>
            </p>
            @if ($order->tracking_url)
                <a href="{{ $order->tracking_url }}" target="_blank"
                   class="inline-flex items-center gap-1 mt-2 text-xs font-medium text-purple-700 hover:text-purple-900 underline underline-offset-2">
                    Rastrear envio
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            @endif
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Itens do pedido (coluna larga) --}}
    <div class="lg:col-span-2 space-y-4">

        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Itens do Pedido</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($order->items as $item)
                <div class="flex items-center gap-4 px-5 py-4">
                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $item->product_name }}</p>
                        @if ($item->variant_label)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $item->variant_label }}</p>
                        @endif
                        @if ($item->sku)
                            <p class="text-xs text-gray-400 mt-0.5 font-mono">SKU: {{ $item->sku }}</p>
                        @endif
                    </div>

                    {{-- Qty + Preço --}}
                    <div class="text-right shrink-0">
                        <p class="text-sm font-semibold text-gray-900">R$ {{ number_format($item->total, 2, ',', '.') }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $item->quantity }}× R$ {{ number_format($item->unit_price, 2, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Endereço de entrega --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Endereço de Entrega</h2>
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $order->shipping_name }}</p>
                    <p class="text-sm text-gray-600 mt-0.5">
                        {{ $order->shipping_street }}, {{ $order->shipping_number }}
                        @if ($order->shipping_complement) — {{ $order->shipping_complement }} @endif
                    </p>
                    <p class="text-sm text-gray-600">{{ $order->shipping_district }}, {{ $order->shipping_city }}/{{ $order->shipping_state }}</p>
                    <p class="text-sm text-gray-500 font-mono mt-0.5">CEP {{ $order->shipping_zip }}</p>
                    @if ($order->shipping_method)
                        <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            </svg>
                            {{ $order->shipping_method }}
                            @if ($order->shipping_days) · {{ $order->shipping_days }} dias úteis @endif
                        </p>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- Resumo financeiro (coluna estreita) --}}
    <div class="space-y-4">

        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Resumo do Pedido</h2>

            <div class="space-y-2.5 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="text-gray-900">R$ {{ number_format($order->subtotal, 2, ',', '.') }}</span>
                </div>

                @if ($order->shipping_cost > 0)
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Frete</span>
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
                    <span class="text-gray-500">
                        Desconto
                        @if ($order->coupon_code)
                            <span class="ml-1 font-mono text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">{{ $order->coupon_code }}</span>
                        @endif
                    </span>
                    <span class="text-green-600">−R$ {{ number_format($order->discount, 2, ',', '.') }}</span>
                </div>
                @endif

                @if ($order->pix_discount > 0)
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Desconto PIX</span>
                    <span class="text-green-600">−R$ {{ number_format($order->pix_discount, 2, ',', '.') }}</span>
                </div>
                @endif

                @php
                    $paymentData = $order->payment_data ?? [];
                    $hasInterest = !empty($paymentData['total_with_interest']) && $paymentData['total_with_interest'] > $order->total;
                @endphp
                @if ($hasInterest)
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Juros parcelamento</span>
                    <span class="text-gray-700">+R$ {{ number_format($paymentData['total_with_interest'] - $order->total, 2, ',', '.') }}</span>
                </div>
                @endif

                <div class="border-t border-gray-100 pt-2.5 flex items-center justify-between font-semibold">
                    <span class="text-gray-900">Total</span>
                    <span class="text-gray-900 text-base">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Pagamento --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Pagamento</h2>
            <div class="space-y-2 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center">
                        @if ($order->payment_method === 'pix')
                            <svg class="w-3.5 h-3.5 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M11.944 17.97L4.58 10.607 7.41 7.78l4.534 4.534 4.534-4.534 2.828 2.828-7.362 7.362z"/>
                                <path d="M7.41 16.22l-2.828-2.828 7.362-7.362 7.362 7.362-2.828 2.828-4.534-4.534-4.534 4.534z"/>
                            </svg>
                        @elseif ($order->payment_method === 'credit_card')
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                            </svg>
                        @else
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $order->payment_method_label }}</p>
                        @if (!empty($paymentData['installments']) && $paymentData['installments'] > 1)
                            <p class="text-xs text-gray-500">
                                {{ $paymentData['installments'] }}× de
                                R$ {{ number_format($paymentData['installment_value'] ?? 0, 2, ',', '.') }}
                                @if (!empty($paymentData['interest_free'])) · sem juros @endif
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <span class="text-gray-500">Status</span>
                    <span class="text-xs font-medium
                        @if($order->payment_status === 'paid') text-green-700
                        @elseif($order->payment_status === 'failed') text-red-700
                        @else text-amber-700 @endif">
                        {{ $order->payment_status_label }}
                    </span>
                </div>

                @if ($order->paid_at)
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Pago em</span>
                    <span class="text-gray-700">{{ $order->paid_at->format('d/m/Y') }}</span>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
