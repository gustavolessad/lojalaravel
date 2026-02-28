@extends('layouts.shop')

@section('title', 'Marcas')

@section('content')

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-sm text-gray-500 mb-6">
        <a href="/" class="hover:text-gray-900 transition-colors">Início</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-medium">Marcas</span>
    </nav>

    <h1 class="text-2xl font-bold text-gray-900 mb-8">Marcas</h1>

    @if ($brands->isEmpty())
        <p class="text-gray-500 text-center py-16">Nenhuma marca cadastrada.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach ($brands as $brand)
                <a href="{{ $brand->url }}"
                   class="group flex flex-col items-center justify-center gap-3 p-5 bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-md hover:border-indigo-200 transition-all">

                    {{-- Logo ou inicial --}}
                    @if ($brand->getFirstMediaUrl('logo', 'thumb'))
                        <img src="{{ $brand->getFirstMediaUrl('logo', 'thumb') }}"
                             alt="{{ $brand->name }}"
                             class="h-12 w-auto object-contain grayscale group-hover:grayscale-0 transition-all duration-300">
                    @else
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-xl font-bold text-gray-400 group-hover:bg-indigo-50 group-hover:text-indigo-500 transition-colors">
                            {{ mb_strtoupper(mb_substr($brand->name, 0, 1)) }}
                        </div>
                    @endif

                    {{-- Nome --}}
                    <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-600 text-center transition-colors">
                        {{ $brand->name }}
                    </span>
                </a>
            @endforeach
        </div>
    @endif

@endsection
