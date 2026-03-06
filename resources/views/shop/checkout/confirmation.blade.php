@extends('layouts.minimal')

@section('title', 'Pedido Confirmado — #' . $order->order_number)

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Cabeçalho --}}
    <div class="text-center mb-6">
        <div class="w-14 h-14 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-xl font-semibold text-gray-900">Pedido recebido!</h1>
        <p class="text-sm text-gray-500 mt-1">Pedido <span class="font-medium text-gray-700">#{{ $order->order_number }}</span></p>
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
            class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-4"
        >
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">Pague com PIX</h2>
            </div>

            <div class="p-5 text-center">
                {{-- Estado: aguardando pagamento --}}
                <div x-show="!paid" x-transition>
                    <div class="flex items-center justify-center gap-2 mb-5">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                        <p class="text-sm text-gray-500">Aguardando confirmação do pagamento...</p>
                    </div>

                    @if (! empty($pixData['pix_qrcode']))
                        <div class="flex justify-center mb-4">
                            <img src="data:image/png;base64,{{ $pixData['pix_qrcode'] }}"
                                 alt="QR Code PIX" class="w-44 h-44 rounded-xl border border-gray-200 p-2">
                        </div>
                    @endif

                    @if (! empty($pixData['pix_copy_paste']))
                        <div class="mt-2">
                            <p class="text-xs text-gray-500 mb-2">Ou copie o código PIX:</p>
                            <div class="flex gap-2 items-center bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                                <input id="pixCode" type="text" readonly value="{{ $pixData['pix_copy_paste'] }}"
                                       class="flex-1 text-xs text-gray-700 bg-transparent outline-none truncate">
                                <button
                                    onclick="navigator.clipboard.writeText(document.getElementById('pixCode').value).then(()=>{ this.textContent='Copiado!'; setTimeout(()=>{ this.textContent='Copiar' }, 2000) })"
                                    class="text-xs font-semibold text-gray-900 flex-shrink-0 hover:text-gray-600 transition-colors"
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
                        <div class="w-12 h-12 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-900">Pagamento PIX confirmado!</p>
                        <p class="text-sm text-gray-500">Seu pedido está sendo preparado.</p>
                    </div>
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
            class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-4"
        >
            <div class="p-5">
                {{-- Aguardando confirmação --}}
                <div x-show="!paid" class="flex items-center gap-3" x-transition>
                    <div class="w-9 h-9 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Pagamento em processamento</p>
                        <p class="text-xs text-gray-500 mt-0.5">Aguardando confirmação da operadora...</p>
                    </div>
                </div>

                {{-- Cartão aprovado --}}
                <div x-show="paid" class="flex items-center gap-3" x-transition.duration.500ms style="display:none">
                    <div class="w-9 h-9 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Pagamento aprovado!</p>
                        <p class="text-xs text-gray-500 mt-0.5">Seu cartão foi cobrado com sucesso.</p>
                    </div>
                </div>
            </div>
        </div>

    @endif

    {{-- Detalhes do pedido --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-4">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                </svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-900">Itens do pedido</h2>
        </div>

        <div class="p-5">
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
                        <span class="font-medium text-gray-900">R$ {{ number_format($item->total, 2, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-gray-100 mt-4 pt-4 space-y-2 text-sm">
                <div class="flex justify-between text-gray-500">
                    <span>Subtotal</span>
                    <span>R$ {{ number_format($order->subtotal, 2, ',', '.') }}</span>
                </div>
                @if ($order->discount > 0)
                    <div class="flex justify-between text-emerald-600">
                        <span>Desconto{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}</span>
                        <span>− R$ {{ number_format($order->discount, 2, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-gray-500">
                    <span>Frete ({{ $order->shipping_method }})</span>
                    <span>{{ $order->shipping_cost == 0 ? 'Grátis' : 'R$ ' . number_format($order->shipping_cost, 2, ',', '.') }}</span>
                </div>
                @if ($order->pix_discount > 0)
                    <div class="flex justify-between text-emerald-600">
                        <span>Desconto PIX</span>
                        <span>− R$ {{ number_format($order->pix_discount, 2, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-semibold text-gray-900 text-base pt-2 border-t border-gray-100">
                    <span>Total</span>
                    <span>R$ {{ number_format($order->total, 2, ',', '.') }}</span>
                </div>

                {{-- Informação complementar de pagamento --}}
                @if ($order->payment_method === 'pix' && $order->pix_discount > 0)
                    <p class="text-xs text-emerald-600 text-center pt-1">
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
    </div>

    {{-- Endereço de entrega --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
            </div>
            <h2 class="text-sm font-semibold text-gray-900">Entrega</h2>
        </div>

        <div class="p-5">
            <p class="text-sm font-medium text-gray-900">{{ $order->shipping_name }}</p>
            <p class="text-sm text-gray-500 mt-0.5">{{ $order->shipping_address_formatted }}</p>
            <p class="text-sm text-gray-500 mt-2">
                <span class="font-medium text-gray-700">{{ $order->shipping_method }}</span>
                — prazo estimado de {{ $order->shipping_days }} dia{{ $order->shipping_days != 1 ? 's' : '' }} útil{{ $order->shipping_days != 1 ? 'eis' : '' }}
            </p>
        </div>
    </div>

    {{-- Botões --}}
    <div class="flex flex-col sm:flex-row gap-3">
        @auth('customer')
            <a href="{{ route('account.orders.index') }}"
               class="flex-1 py-2.5 px-5 bg-gray-900 text-white text-sm font-medium rounded-xl text-center hover:bg-gray-700 transition-colors">
                Ver meus pedidos
            </a>
        @endauth
        <a href="/"
           class="flex-1 py-2.5 px-5 border border-gray-200 text-gray-600 text-sm font-medium rounded-xl text-center hover:bg-gray-50 transition-colors">
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
