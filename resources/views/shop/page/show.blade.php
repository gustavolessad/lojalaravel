@extends('layouts.shop')

@section('title', ($page->meta_title ?: $page->title) . ' — ' . config('app.name'))

@section('content')
    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2">
            <li><a href="/" class="hover:text-gray-700">Início</a></li>
            <li><span class="text-gray-300">/</span></li>
            <li class="text-gray-700 font-medium truncate">{{ $page->title }}</li>
        </ol>
    </nav>

    <div class="max-w-3xl mx-auto">
        {{-- Título --}}
        <h1 class="text-3xl font-bold text-gray-900 mb-8 pb-4 border-b border-gray-200">
            {{ $page->title }}
        </h1>

        {{-- Conteúdo --}}
        @if ($page->content)
            <div class="prose prose-gray max-w-none
                        prose-headings:font-semibold prose-headings:text-gray-900
                        prose-p:text-gray-700 prose-p:leading-relaxed
                        prose-a:text-gray-900 prose-a:underline hover:prose-a:no-underline
                        prose-ul:text-gray-700 prose-ol:text-gray-700
                        prose-blockquote:border-gray-300 prose-blockquote:text-gray-600">
                {!! $page->content !!}
            </div>
        @else
            <div class="text-center py-16 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm">Conteúdo em breve.</p>
            </div>
        @endif
    </div>
@endsection
