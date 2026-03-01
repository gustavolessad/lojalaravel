@extends('layouts.account')

@section('title', 'Minha Conta')

@section('page-content')

{{-- Boas-vindas --}}
<div class="bg-white rounded-2xl border border-gray-200 p-6 mb-4">
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gray-900 text-white flex items-center justify-center text-2xl font-bold shrink-0 select-none">
                {{ mb_strtoupper(mb_substr($customer->display_name, 0, 1)) }}
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Bem-vindo(a) de volta,</p>
                <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ $customer->display_name }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ $customer->email }}</p>
            </div>
        </div>
        <a href="{{ route('account.profile.edit') }}"
           class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-gray-900 border border-gray-200 hover:border-gray-300 px-3 py-1.5 rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
            </svg>
            Editar perfil
        </a>
    </div>
</div>

{{-- Cards de resumo --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
    <a href="{{ route('account.orders.index') }}"
       class="bg-white rounded-2xl border border-gray-200 p-4 hover:border-gray-300 hover:shadow-sm transition-all group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 bg-indigo-50 rounded-xl flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                </svg>
            </div>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $totalOrders }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Pedido{{ $totalOrders != 1 ? 's' : '' }}</p>
    </a>

    <a href="{{ route('account.addresses.index') }}"
       class="bg-white rounded-2xl border border-gray-200 p-4 hover:border-gray-300 hover:shadow-sm transition-all group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 bg-green-50 rounded-xl flex items-center justify-center group-hover:bg-green-100 transition-colors">
                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
            </div>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $addressCount }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Endereço{{ $addressCount != 1 ? 's' : '' }}</p>
    </a>

    <a href="{{ route('account.profile.edit') }}"
       class="bg-white rounded-2xl border border-gray-200 p-4 hover:border-gray-300 hover:shadow-sm transition-all group col-span-2 sm:col-span-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-9 h-9 bg-amber-50 rounded-xl flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </div>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </div>
        <p class="text-sm font-semibold text-gray-900">Meus Dados</p>
        <p class="text-xs text-gray-500 mt-0.5">Editar perfil e senha</p>
    </a>
</div>

{{-- Pedidos recentes --}}
@if ($recentOrders->isNotEmpty())
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-900">Pedidos Recentes</h2>
        <a href="{{ route('account.orders.index') }}"
           class="text-xs font-medium text-gray-500 hover:text-gray-900 transition-colors">
            Ver todos →
        </a>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach ($recentOrders as $order)
        @php
            $statusConfig = match($order->status) {
                'pending'    => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-400'],
                'processing' => ['bg' => 'bg-blue-50',  'text' => 'text-blue-700',  'border' => 'border-blue-200',  'dot' => 'bg-blue-400'],
                'paid'       => ['bg' => 'bg-indigo-50','text' => 'text-indigo-700','border' => 'border-indigo-200','dot' => 'bg-indigo-400'],
                'shipped'    => ['bg' => 'bg-purple-50','text' => 'text-purple-700','border' => 'border-purple-200','dot' => 'bg-purple-400'],
                'delivered'  => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-green-200', 'dot' => 'bg-green-500'],
                'cancelled'  => ['bg' => 'bg-red-50',   'text' => 'text-red-700',   'border' => 'border-red-200',   'dot' => 'bg-red-400'],
                default      => ['bg' => 'bg-gray-50',  'text' => 'text-gray-600',  'border' => 'border-gray-200',  'dot' => 'bg-gray-400'],
            };
        @endphp
        <a href="{{ route('account.orders.show', $order->order_number) }}"
           class="flex items-center justify-between gap-4 px-5 py-3.5 hover:bg-gray-50 transition-colors">
            <div class="min-w-0">
                <p class="text-sm font-medium text-gray-900">Pedido #{{ $order->order_number }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $order->created_at->format('d/m/Y') }}</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium border {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} {{ $statusConfig['border'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                    {{ $order->status_label }}
                </span>
                <span class="text-sm font-semibold text-gray-900">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
                <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </div>
        </a>
        @endforeach
    </div>
</div>
@else
<div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
        </svg>
    </div>
    <p class="text-sm font-medium text-gray-900 mb-1">Nenhum pedido ainda</p>
    <p class="text-xs text-gray-400 mb-4">Explore nossa loja e faça seu primeiro pedido!</p>
    <a href="/" class="inline-flex items-center gap-1.5 bg-gray-900 text-white text-xs font-medium px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
        Explorar produtos
    </a>
</div>
@endif

@endsection
