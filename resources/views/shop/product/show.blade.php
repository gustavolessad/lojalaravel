@extends('layouts.shop')

@php
    use App\Models\Setting;

    $metaTitle       = $product->seo_title       ?: $product->name;
    $metaDescription = $product->seo_description ?: ($product->description ?: Setting::get('seo_meta_description', ''));
    $metaDescription = Str::limit(strip_tags($metaDescription), 160);
    $metaKeywords    = $product->seo_keywords ?: '';
    $canonicalUrl    = route('product.show', $product->slug);
    // Resolve variant image from URL query params (e.g. ?cor=preta&tamanho=30x40)
    $ogImage = null;
    if ($product->type === 'variable' && request()->query()) {
        $queryParams = request()->query();
        $variant = $product->variants->first(function ($v) use ($queryParams) {
            $map = $v->attributeValues->mapWithKeys(fn ($av) => [$av->attribute->slug => $av->slug]);
            foreach ($queryParams as $key => $value) {
                if (! is_string($value) || ! isset($map[$key]) || $map[$key] !== $value) {
                    return false;
                }
            }
            return $map->count() === count(array_filter($queryParams, 'is_string'));
        });

        if ($variant) {
            $ogImage = $variant->variantGroup?->getFirstMediaUrl('group-cover', 'webp')
                    ?: $variant->variantGroup?->getFirstMediaUrl('group-cover')
                    ?: $variant->getFirstMediaUrl('variant-cover', 'webp')
                    ?: $variant->getFirstMediaUrl('variant-cover')
                    ?: null;
        }
    }

    // Fallback: product cover
    if (! $ogImage) {
        $ogImage = $product->getFirstMediaUrl('cover', 'webp')
                ?: $product->getFirstMediaUrl('cover');
    }
    $productPrice    = $product->getCurrentPrice();
    $storeName       = Setting::get('store_name', config('app.name'));
    $inStock         = $product->isSimple()
                         ? $product->stock > 0
                         : $product->variants()->where('active', true)->where('stock', '>', 0)->exists();
@endphp

{{-- ── Title ─────────────────────────────────────────────────────────────── --}}
@section('title', $metaTitle)

{{-- ── Meta descrição e keywords ──────────────────────────────────────────── --}}
@section('meta_description', $metaDescription)
@if ($metaKeywords)
    @section('meta_keywords', $metaKeywords)
@endif

{{-- ── Canonical ───────────────────────────────────────────────────────────── --}}
@section('canonical', $canonicalUrl)

{{-- ── Open Graph + Twitter Card ─────────────────────────────────────────── --}}
@section('og_tags')
    {{-- Open Graph --}}
    <meta property="og:type"                   content="product">
    <meta property="og:site_name"              content="{{ $storeName }}">
    <meta property="og:title"                  content="{{ $metaTitle }}">
    <meta property="og:description"            content="{{ $metaDescription }}">
    <meta property="og:url"                    content="{{ $canonicalUrl }}">
    @if ($ogImage)
        <meta property="og:image"              content="{{ $ogImage }}">
        <meta property="og:image:alt"          content="{{ $metaTitle }}">
    @endif
    @if ($product->brand)
        <meta property="product:brand"         content="{{ $product->brand->name }}">
    @endif
    <meta property="product:price:amount"      content="{{ number_format($productPrice, 2, '.', '') }}">
    <meta property="product:price:currency"    content="BRL">
    <meta property="product:availability"      content="{{ $inStock ? 'in stock' : 'out of stock' }}">
    @if ($product->sku)
        <meta property="product:retailer_item_id" content="{{ $product->sku }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if ($ogImage)
        <meta name="twitter:image"   content="{{ $ogImage }}">
        <meta name="twitter:image:alt" content="{{ $metaTitle }}">
    @endif
@endsection

{{-- ── JSON-LD — Schema.org Product ─────────────────────────────────────── --}}
@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "Product",
    "name": "{{ addslashes($product->name) }}",
    @if ($metaDescription)
    "description": "{{ addslashes($metaDescription) }}",
    @endif
    @if ($ogImage)
    "image": ["{{ $ogImage }}"],
    @endif
    @if ($product->sku)
    "sku": "{{ $product->sku }}",
    @endif
    @if ($product->barcode)
    "gtin": "{{ $product->barcode }}",
    @endif
    @if ($product->brand)
    "brand": {
        "@type": "Brand",
        "name": "{{ addslashes($product->brand->name) }}"
    },
    @endif
    "offers": {
        "@type": "Offer",
        "url": "{{ $canonicalUrl }}",
        "priceCurrency": "BRL",
        "price": "{{ number_format($productPrice, 2, '.', '') }}",
        "availability": "https://schema.org/{{ $inStock ? 'InStock' : 'OutOfStock' }}",
        "seller": {
            "@type": "Organization",
            "name": "{{ addslashes($storeName) }}"
        }
    }
}
</script>
@endpush

@section('content')

    {{-- Breadcrumb --}}
    @if ($product->categories->isNotEmpty())
        @php $firstCategory = $product->categories->first(); @endphp
        <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-3 flex-wrap">
            <a href="/" class="hover:text-gray-900 transition-colors">Início</a>
            @foreach ($firstCategory->breadcrumb as $crumb)
                <span class="text-gray-300">/</span>
                <a href="{{ $crumb->url }}" class="hover:text-gray-900 transition-colors">
                    {{ $crumb->name }}
                </a>
            @endforeach
            <span class="text-gray-300">/</span>
            <span class="text-gray-700 font-medium">{{ $product->name }}</span>
        </nav>
    @endif

    @livewire('shop.product-detail', ['product' => $product])

@endsection

@push('scripts')
{{-- ═══════════════════════════════════════════════════════════════
     MODAL "Produto adicionado ao carrinho" (Alpine.js)
     Ouve o evento disparado pelo componente Livewire
════════════════════════════════════════════════════════════════ --}}
<div
    x-data="{
        open: false,
        name: '',
        image: '',
        variant: '',
        close() { this.open = false },
    }"
    @product-added-to-cart.window="
        name    = $event.detail.name;
        image   = $event.detail.image;
        variant = $event.detail.variant;
        open    = true;
    "
    @keydown.escape.window="close()"
    x-cloak
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 sm:p-6"
        style="background: rgba(0,0,0,.45)"
        @click.self="close()"
    >
        {{-- Painel do modal --}}
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl p-6"
        >
            {{-- Fechar --}}
            <button
                @click="close()"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"
                aria-label="Fechar"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- Conteúdo --}}
            <div class="flex items-start gap-4 mb-5">
                {{-- Ícone de check --}}
                <div class="w-11 h-11 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                {{-- Informações do produto --}}
                <div class="flex-1 min-w-0 pt-0.5">
                    <p class="text-sm font-semibold text-gray-900">Produto adicionado!</p>
                    <p class="text-sm text-gray-600 mt-0.5 truncate" x-text="name"></p>
                    <p
                        x-show="variant"
                        class="text-xs text-indigo-600 font-medium mt-1 bg-indigo-50 inline-block px-2 py-0.5 rounded-full"
                        x-text="variant"
                    ></p>
                </div>
            </div>

            {{-- Botões de ação --}}
            <div class="flex flex-col gap-2">
                <a
                    href="{{ route('cart.index') }}"
                    class="w-full py-3 px-4 bg-green-700 text-white text-sm font-semibold
                           rounded-xl text-center hover:bg-green-800 transition-colors"
                >
                    Finalizar Compra
                </a>
                <button
                    @click="close()"
                    class="w-full py-2.5 px-5 border border-gray-200 text-gray-600 text-sm font-medium
                           rounded-xl hover:bg-gray-50 transition-colors"
                >
                    Continuar Comprando
                </button>

                
            </div>
        </div>
    </div>
</div>
@endpush
