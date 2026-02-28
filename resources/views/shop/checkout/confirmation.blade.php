@extends('layouts.shop')

@section('title', 'Pedido Confirmado — #' . $order->order_number)

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Cabeçalho --}}
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Pedido recebido!</h1>
        <p class="text-gray-500 mt-1">Número do pedido: <span class="font-semibold text-gray-700">#{{ $order->order_number }}</span></p>
    </div>

    {{-- PIX: QR Code com polling em tempo real --}}
    @if ($order->payment_method === 'pix')
        @php $pixData = $order->payment_data ?? []; @endphp

        <div
            x-data="pixPolling({
                statusUrl: '{{ route('order.payment-status', $order->order_number) }}',
                initialStatus: '{{ $order->payment_status }}'
            })"
            x-init="init()"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5 text-center"
        >
            {{-- Estado: aguardando pagamento --}}
            <div x-show="!paid" x-transition>
                <h2 class="text-base font-semibold text-gray-900 mb-1">Pague com PIX</h2>

                {{-- Indicador de monitoramento ativo --}}
                <div class="flex items-center justify-center gap-2 mb-4">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                    </span>
                    <p class="text-sm text-gray-500">Aguardando confirmação do pagamento...</p>
                </div>

                @if (! empty($pixData['pix_qrcode']))
                    <div class="flex justify-center mb-4">
                        <img src="data:image/png;base64,{{ $pixData['pix_qrcode'] }}"
                             alt="QR Code PIX" class="w-48 h-48 rounded-xl border border-gray-200">
                    </div>
                @endif

                @if (! empty($pixData['pix_copy_paste']))
                    <div class="mt-2">
                        <p class="text-xs text-gray-500 mb-2">Ou copie o código PIX:</p>
                        <div class="flex gap-2 items-center bg-gray-50 rounded-xl px-4 py-3">
                            <input id="pixCode" type="text" readonly value="{{ $pixData['pix_copy_paste'] }}"
                                   class="flex-1 text-xs text-gray-700 bg-transparent outline-none truncate">
                            <button
                                onclick="navigator.clipboard.writeText(document.getElementById('pixCode').value).then(()=>{ this.textContent='Copiado!'; setTimeout(()=>{ this.textContent='Copiar' }, 2000) })"
                                class="text-xs text-indigo-600 font-semibold flex-shrink-0 hover:text-indigo-800"
                            >Copiar</button>
                        </div>
                    </div>
                @endif

                <p class="text-xs text-gray-400 mt-4">
                    Expira em: {{ isset($pixData['expires_at']) ? \Carbon\Carbon::parse($pixData['expires_at'])->format('d/m/Y') : '24h' }}
                </p>
            </div>

            {{-- Estado: pagamento confirmado --}}
            <div x-show="paid" x-transition.duration.500ms style="display:none">
                <div class="flex flex-col items-center gap-3 py-2">
                    <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center">
                        <svg class="w-7 h-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-base font-semibold text-emerald-700">Pagamento PIX confirmado!</p>
                    <p class="text-sm text-gray-500">Seu pedido está sendo preparado.</p>
                </div>
            </div>
        </div>

    {{-- Cartão de crédito --}}
    @elseif ($order->payment_method === 'credit_card')

        <div
            x-data="pixPolling({
                statusUrl: '{{ route('order.payment-status', $order->order_number) }}',
                initialStatus: '{{ $order->payment_status }}'
            })"
            x-init="init()"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5"
        >
            {{-- Aguardando confirmação --}}
            <div x-show="!paid" class="flex items-center gap-3" x-transition>
                <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="relative flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-4 w-4 bg-amber-500"></span>
                    </span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Pagamento em processamento</p>
                    <p class="text-xs text-gray-500">Aguardando confirmação da operadora...</p>
                </div>
            </div>

            {{-- Cartão aprovado --}}
            <div x-show="paid" class="flex items-center gap-3" x-transition.duration.500ms style="display:none">
                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Pagamento aprovado!</p>
                    <p class="text-xs text-gray-500">Seu cartão foi cobrado com sucesso.</p>
                </div>
            </div>
        </div>

    @endif

    {{-- Detalhes do pedido --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Itens do pedido</h2>
        <div class="space-y-3">
            @foreach ($order->items as $item)
                <div class="flex justify-between text-sm">
                    <div>
                        <span class="text-gray-900">{{ $item->product_name }}</span>
                        @if ($item->variant_label)
                            <span class="text-gray-500"> — {{ $item->variant_label }}</span>
                        @endif
                        <span class="text-gray-400 ml-1">× {{ $item->quantity }}</span>
                    </div>
                    <span class="font-semibold text-gray-900">R$ {{ number_format($item->total, 2, ',', '.') }}</span>
                </div>
            @endforeach
        </div>

        <div class="border-t border-gray-100 mt-4 pt-4 space-y-2 text-sm">
            <div class="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span>R$ {{ number_format($order->subtotal, 2, ',', '.') }}</span>
            </div>
            @if ($order->discount > 0)
                <div class="flex justify-between text-green-600">
                    <span>Desconto{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}</span>
                    <span>− R$ {{ number_format($order->discount, 2, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between text-gray-600">
                <span>Frete ({{ $order->shipping_method }})</span>
                <span>{{ $order->shipping_cost == 0 ? 'Grátis' : 'R$ ' . number_format($order->shipping_cost, 2, ',', '.') }}</span>
            </div>
            @if ($order->pix_discount > 0)
                <div class="flex justify-between text-green-600">
                    <span>Desconto PIX</span>
                    <span>− R$ {{ number_format($order->pix_discount, 2, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between font-bold text-gray-900 text-base pt-2 border-t border-gray-100">
                <span>Total</span>
                <span>R$ {{ number_format($order->total, 2, ',', '.') }}</span>
            </div>

            {{-- Informação complementar de pagamento --}}
            @if ($order->payment_method === 'pix' && $order->pix_discount > 0)
                <p class="text-xs text-green-600 text-center pt-1">
                    Você economizou R$ {{ number_format($order->pix_discount, 2, ',', '.') }} pagando no PIX!
                </p>
            @elseif ($order->payment_method === 'credit_card' && ! empty($order->payment_data['installments']) && $order->payment_data['installments'] > 1)
                @php
                    $inst      = $order->payment_data['installments'];
                    $instVal   = $order->payment_data['installment_value'] ?? 0;
                    $noJuros   = $order->payment_data['interest_free'] ?? true;
                    $totalWI   = $order->payment_data['total_with_interest'] ?? 0;
                    $surcharge = round($totalWI - $order->total, 2);
                @endphp
                <p class="text-xs text-gray-500 text-center pt-1">
                    {{ $inst }}× de R$ {{ number_format($instVal, 2, ',', '.') }}
                    {{ $noJuros ? 'sem juros' : 'com juros' }}
                    @if (! $noJuros && $surcharge > 0)
                        (acréscimo de R$ {{ number_format($surcharge, 2, ',', '.') }})
                    @endif
                </p>
            @endif
        </div>
    </div>

    {{-- Endereço de entrega --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
        <h2 class="text-sm font-semibold text-gray-900 mb-2">Entrega</h2>
        <p class="text-sm text-gray-700">{{ $order->shipping_name }}</p>
        <p class="text-sm text-gray-500 mt-0.5">{{ $order->shipping_address_formatted }}</p>
        <p class="text-sm text-gray-500 mt-1">
            <span class="font-medium text-gray-700">{{ $order->shipping_method }}</span>
            — prazo estimado de {{ $order->shipping_days }} dias úteis
        </p>
    </div>

    {{-- Botões --}}
    <div class="flex flex-col sm:flex-row gap-3">
        @auth('customer')
            <a href="{{ route('account.dashboard') }}"
               class="flex-1 py-3 px-6 bg-indigo-600 text-white text-sm font-semibold rounded-xl text-center hover:bg-indigo-700 transition-colors">
                Ver meus pedidos
            </a>
        @endauth
        <a href="/"
           class="flex-1 py-3 px-6 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl text-center hover:bg-gray-200 transition-colors">
            Continuar comprando
        </a>
    </div>

</div>

@push('scripts')
<script>
function pixPolling({ statusUrl, initialStatus }) {
    return {
        paid: initialStatus === 'paid',
        timer: null,

        init() {
            if (this.paid) return;
            // Verifica a cada 5 segundos
            this.timer = setInterval(() => this.checkStatus(), 5000);
        },

        async checkStatus() {
            try {
                const res  = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.paid) {
                    this.paid = true;
                    clearInterval(this.timer);
                }
            } catch (_) {
                // ignora falhas pontuais de rede, continua tentando
            }
        },

        destroy() {
            clearInterval(this.timer);
        }
    }
}
</script>
@endpush

@endsection
