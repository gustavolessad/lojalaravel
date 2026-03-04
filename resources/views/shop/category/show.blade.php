@extends('layouts.shop')

@php
    use App\Models\Setting;

    $metaTitle       = $category->seo_title ?: $category->name;
    $metaDescription = $category->seo_description ?: ($category->description ?: '');
    $metaDescription = $metaDescription ? Str::limit(strip_tags($metaDescription), 160) : '';
    $metaKeywords    = $category->seo_keywords ?: '';
    $storeName       = Setting::get('store_name', config('app.name'));
    $currentPage     = (int) request()->get('page', 1);
    $canonicalUrl    = url($category->path . '/c') . ($currentPage > 1 ? '?page=' . $currentPage : '');
@endphp

@section('title', $metaTitle . ($currentPage > 1 ? ' - Página ' . $currentPage : ''))

@if ($metaDescription)
    @section('meta_description', $metaDescription)
@endif
@if ($metaKeywords)
    @section('meta_keywords', $metaKeywords)
@endif

{{-- Páginas 2+ não devem ser indexadas (conteúdo duplicado) --}}
@if ($currentPage > 1)
    @section('meta_robots', 'noindex, follow')
@endif

@section('canonical', $canonicalUrl)

{{-- ── Open Graph + Twitter Card ─────────────────────────────────────── --}}
@section('og_tags')
    <meta property="og:type"        content="website">
    <meta property="og:site_name"   content="{{ $storeName }}">
    <meta property="og:title"       content="{{ $metaTitle }}">
    @if ($metaDescription)
    <meta property="og:description" content="{{ $metaDescription }}">
    @endif
    <meta property="og:url"         content="{{ $canonicalUrl }}">
    <meta name="twitter:card"        content="summary">
    <meta name="twitter:title"       content="{{ $metaTitle }}">
    @if ($metaDescription)
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @endif
@endsection

{{-- ── BreadcrumbList JSON-LD ─────────────────────────────────────────── --}}
@push('head')
@php
    $breadcrumbItems = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Início', 'item' => url('/')]];
    $pos = 2;
    foreach ($category->breadcrumb as $crumb) {
        if ($crumb->id === $category->id) {
            $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => $pos++, 'name' => $crumb->name];
        } else {
            $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => $pos++, 'name' => $crumb->name, 'item' => url($crumb->url)];
        }
    }
@endphp
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $breadcrumbItems], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

{{-- ── ItemList JSON-LD ───────────────────────────────────────────────── --}}
@if ($itemListProducts->isNotEmpty())
@php
    $listItems = $itemListProducts->map(function ($p, $i) {
        $item = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'item'     => [
                '@type' => 'Product',
                'name'  => $p->name,
                'url'   => url($p->slug . '/p'),
            ],
        ];
        $img = $p->getFirstMediaUrl('cover', 'webp') ?: $p->getFirstMediaUrl('cover');
        if ($img) $item['item']['image'] = $img;
        $price = $p->getCurrentPrice();
        if ($price > 0) {
            $item['item']['offers'] = [
                '@type'         => 'Offer',
                'priceCurrency' => 'BRL',
                'price'         => number_format($price, 2, '.', ''),
                'availability'  => 'https://schema.org/InStock',
            ];
        }
        return $item;
    })->toArray();
@endphp
<script type="application/ld+json">{!! json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'ItemList',
    'name'            => $category->name,
    'numberOfItems'   => count($listItems),
    'itemListElement' => $listItems,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif
@endpush

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
