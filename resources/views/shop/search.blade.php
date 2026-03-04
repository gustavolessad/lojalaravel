@extends('layouts.shop')

@section('title', request('q') ? 'Busca: ' . request('q') . ' — ' . config('app.name') : 'Busca — ' . config('app.name'))
@section('meta_robots', 'noindex, follow')

@section('content')

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-6">
        <a href="/" class="hover:text-gray-900 transition-colors">Início</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-medium">Busca</span>
    </nav>

    {{-- Cabeçalho --}}
    <div class="mb-8">
        @if (request('q'))
            <h1 class="text-2xl font-bold text-gray-900">
                Resultados para: <span class="text-indigo-600">{{ request('q') }}</span>
            </h1>
        @else
            <h1 class="text-2xl font-bold text-gray-900">Busca</h1>
        @endif
    </div>

    @if (request('q'))
        @livewire('shop.product-list', ['scopeType' => 'search', 'searchQuery' => request()->string('q')->trim()->toString()])
    @else
        <p class="text-gray-500 text-sm">Digite algo na barra de busca para encontrar produtos.</p>
    @endif

@endsection
