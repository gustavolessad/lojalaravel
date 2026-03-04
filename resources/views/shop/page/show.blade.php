@extends('layouts.shop')

@section('title', $page->meta_title ?: $page->title)

@if ($page->meta_description)
    @section('meta_description', $page->meta_description)
@endif

@if ($page->meta_keywords)
    @section('meta_keywords', $page->meta_keywords)
@endif

@section('canonical', route('page.show', $page->slug))

@section('og_tags')
    <meta property="og:type"        content="article">
    <meta property="og:site_name"   content="{{ config('app.name') }}">
    <meta property="og:title"       content="{{ $page->meta_title ?: $page->title }}">
    @if ($page->meta_description)
        <meta property="og:description" content="{{ $page->meta_description }}">
    @endif
    <meta property="og:url"         content="{{ route('page.show', $page->slug) }}">
    @php
        $ogImage = \App\Models\Setting::get('seo_og_image', '');
        $ogImageUrl = $ogImage ? \Illuminate\Support\Facades\Storage::disk('public')->url($ogImage) : null;
        if (! $ogImageUrl) {
            $storeLogo = \App\Models\Setting::get('store_logo', '');
            $ogImageUrl = $storeLogo ? \Illuminate\Support\Facades\Storage::disk('public')->url($storeLogo) : null;
        }
    @endphp
    @if ($ogImageUrl)
        <meta property="og:image" content="{{ $ogImageUrl }}">
    @endif
    <meta name="twitter:card"        content="summary">
    <meta name="twitter:title"       content="{{ $page->meta_title ?: $page->title }}">
    @if ($page->meta_description)
        <meta name="twitter:description" content="{{ $page->meta_description }}">
    @endif
@endsection

@push('head')
    {{-- JSON-LD: BreadcrumbList --}}
    @php
        $breadcrumbLd = [
            '@context' => 'https://schema.org',
            '@type'    => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $page->title],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($breadcrumbLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

    {{-- JSON-LD: WebPage --}}
    @php
        $webPageLd = [
            '@context'    => 'https://schema.org',
            '@type'       => 'WebPage',
            'name'        => $page->meta_title ?: $page->title,
            'url'         => route('page.show', $page->slug),
            'dateModified' => $page->updated_at->toIso8601String(),
        ];
        if ($page->meta_description) {
            $webPageLd['description'] = $page->meta_description;
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($webPageLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
    {{-- Breadcrumb --}}
    <nav class="text-xs text-gray-500 mb-6" aria-label="Breadcrumb">
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
