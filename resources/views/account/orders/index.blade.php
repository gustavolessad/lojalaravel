@extends('layouts.account')

@section('title', 'Meus Pedidos')

@section('page-content')

<div class="mb-5">
    <h1 class="font-medium text-gray-900">Meus Pedidos</h1>
    <p class="text-sm text-gray-500 mt-0.5">Acompanhe o histórico e status dos seus pedidos</p>
</div>

@if ($orders->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 py-16 px-6 text-center">
        <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
            </svg>
        </div>
        <h3 class="text-base font-semibold text-gray-900 mb-1">Nenhum pedido ainda</h3>
        <p class="text-sm text-gray-500 mb-5">Quando você fizer um pedido, ele aparecerá aqui.</p>
        <a href="/" class="inline-flex items-center gap-2 bg-gray-900 text-white text-sm font-medium px-5 py-2.5 rounded-xl hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
            </svg>
            Explorar produtos
        </a>
    </div>
@else
    <div class="space-y-3">
        @foreach ($orders as $order)
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
        <div class="bg-white rounded-2xl border border-gray-200 hover:border-gray-300 transition-colors overflow-hidden">
            {{-- Header do pedido --}}
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Pedido #{{ $order->order_number }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $order->created_at->format('d/m/Y \à\s H:i') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} {{ $statusConfig['border'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                        {{ $order->status_label }}
                    </span>
                </div>
            </div>

            {{-- Itens resumidos --}}
            <div class="px-5 py-3">
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($order->items->take(3) as $item)
                        <span class="text-xs text-gray-600 bg-gray-50 border border-gray-100 rounded-lg px-2.5 py-1">
                            {{ $item->quantity }}× {{ Str::limit($item->product_name, 30) }}
                        </span>
                    @endforeach
                    @if ($order->items->count() > 3)
                        <span class="text-xs text-gray-400 bg-gray-50 border border-gray-100 rounded-lg px-2.5 py-1">
                            +{{ $order->items->count() - 3 }} item(s)
                        </span>
                    @endif
                </div>
            </div>

            {{-- Footer do pedido --}}
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 bg-gray-50 border-t border-gray-100">
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-gray-500">Total</span>
                    <span class="font-semibold text-gray-900">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
                    <span class="text-gray-300">·</span>
                    <span class="text-gray-500">{{ $order->payment_method_label }}</span>
                </div>
                <a href="{{ route('account.orders.show', $order->order_number) }}"
                   class="flex items-center gap-1.5 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors">
                    Ver detalhes
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Paginação --}}
    @if ($orders->hasPages())
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
@endif

<div class="mt-4">
    <a href="{{ route('account.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-900">
        ← Voltar para minha conta
    </a>
</div>

@endsection
