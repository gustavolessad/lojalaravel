@extends('layouts.shop')

@section('title', $category->seo_title ?: $category->name)

@section('content')

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-2">
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
    <div class="mb-4">
        <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-900">
            @if ($category->parent)
                <a href="{{ $category->parent->url }}" class="text-gray-900 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            @endif
            {{ $category->name }}
        </h1>
        @if ($category->description)
            <p class="text-gray-500 mt-2 text-sm max-w-2xl">
                {{ $category->description }}
            </p>
        @endif
    </div>

    {{-- Listagem com filtros (Livewire) --}}
    @livewire('shop.product-list', ['scopeType' => 'category', 'scopeId' => $category->id])

@endsection
