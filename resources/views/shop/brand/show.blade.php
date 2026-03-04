@extends('layouts.shop')

@section('title', $brand->name)

@section('content')

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-2">
        <a href="/" class="hover:text-gray-900 transition-colors">Início</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-medium">{{ $brand->name }}</span>
    </nav>

    {{-- Cabeçalho da marca --}}
    <div class="flex items-center gap-4 mb-4">
        @if ($brand->getFirstMediaUrl('logo', 'optimized'))
            <img src="{{ $brand->getFirstMediaUrl('logo', 'optimized') }}"
                 alt="{{ $brand->name }}"
                 class="h-14 w-auto object-contain">
        @endif
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ $brand->name }}</h1>
            @if ($brand->description)
                <p class="text-gray-500 mt-1 text-sm max-w-2xl">{{ $brand->description }}</p>
            @endif
        </div>
    </div>

    {{-- Listagem com filtros (Livewire) --}}
    @livewire('shop.product-list', ['scopeType' => 'brand', 'scopeId' => $brand->id])

@endsection
