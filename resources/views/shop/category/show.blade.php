@extends('layouts.shop')

@section('title', $category->seo_title ?: $category->name)

@section('content')

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-sm text-gray-500 mb-6">
        <a href="/" class="hover:text-gray-900 transition-colors">Início</a>
        @foreach ($category->breadcrumb as $crumb)
            <span class="text-gray-300">/</span>
            @if ($loop->last)
                <span class="text-gray-900 font-medium">{{ $crumb->name }}</span>
            @else
                <a href="{{ $crumb->url }}" class="hover:text-gray-900 transition-colors">{{ $crumb->name }}</a>
            @endif
        @endforeach
    </nav>

    {{-- Cabeçalho da categoria --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ $category->name }}</h1>
        @if ($category->description)
            <p class="text-gray-500 mt-2 text-sm max-w-2xl">{{ $category->description }}</p>
        @endif
    </div>

    {{-- Listagem com filtros (Livewire) --}}
    @livewire('shop.product-list', ['scopeType' => 'category', 'scopeId' => $category->id])

@endsection
