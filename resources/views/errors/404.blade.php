@extends('layouts.shop')

@section('title', 'Página não encontrada')
@section('meta_robots', 'noindex, nofollow')

@section('content')
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <p class="text-7xl font-extrabold text-gray-200">404</p>

        <h1 class="mt-4 text-2xl font-bold text-gray-900">Página não encontrada</h1>
        <p class="mt-2 text-gray-500 max-w-md">
            A página que você está procurando pode ter sido removida, renomeada ou está temporariamente indisponível.
        </p>

        <div class="mt-8 flex flex-col sm:flex-row gap-3">
            <a href="/"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-900 text-white text-sm font-semibold rounded-lg hover:bg-gray-800 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Ir para a página inicial
            </a>
            <a href="/busca"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Buscar produtos
            </a>
        </div>
    </div>

    {{-- Produtos em destaque --}}
    @php
        $featuredProducts = \App\Models\Product::where('active', true)
            ->where('featured', true)
            ->inRandomOrder()
            ->take(4)
            ->get();
    @endphp

    @if ($featuredProducts->isNotEmpty())
        <div class="mt-16 border-t border-gray-200 pt-12">
            <h2 class="text-lg font-bold text-gray-900 mb-6 text-center">Produtos em destaque</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($featuredProducts as $product)
                    <x-shop.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    @endif
@endsection
