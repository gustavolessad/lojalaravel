{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

    {{-- Home --}}
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    {{-- Produtos + variações --}}
    @foreach ($products as $product)
    @php
        $productImage = $product->getFirstMediaUrl('cover', 'webp') ?: $product->getFirstMediaUrl('cover');
    @endphp
    <url>
        <loc>{{ url($product->slug . '/p') }}</loc>
        <lastmod>{{ $product->updated_at->toW3cString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
        @if ($productImage)
        <image:image>
            <image:loc>{{ $productImage }}</image:loc>
            <image:title>{{ htmlspecialchars($product->name, ENT_XML1) }}</image:title>
        </image:image>
        @endif
    </url>
    @if ($product->type === 'variable')
    @foreach ($product->variants as $variant)
    @php
        $params = $variant->attributeValues->mapWithKeys(fn ($av) => [$av->attribute->slug => $av->slug])->toArray();
        $qs = http_build_query($params);
        $variantImage = $variant->variantGroup?->getFirstMediaUrl('group-cover', 'webp')
                     ?: $variant->variantGroup?->getFirstMediaUrl('group-cover')
                     ?: $variant->getFirstMediaUrl('variant-cover', 'webp')
                     ?: $variant->getFirstMediaUrl('variant-cover')
                     ?: $productImage;
    @endphp
    @if ($qs)
    <url>
        <loc>{{ url($product->slug . '/p?' . $qs) }}</loc>
        <lastmod>{{ $variant->updated_at?->toW3cString() ?? $product->updated_at->toW3cString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
        @if ($variantImage)
        <image:image>
            <image:loc>{{ $variantImage }}</image:loc>
            <image:title>{{ htmlspecialchars($product->name . ' - ' . $variant->attributeValues->map(fn ($av) => $av->getLabel())->join(', '), ENT_XML1) }}</image:title>
        </image:image>
        @endif
    </url>
    @endif
    @endforeach
    @endif
    @endforeach

    {{-- Categorias --}}
    @foreach ($categories as $category)
    <url>
        <loc>{{ url($category->path . '/c') }}</loc>
        <lastmod>{{ $category->updated_at->toW3cString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

    {{-- Marcas --}}
    <url>
        <loc>{{ url('/marcas') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    @foreach ($brands as $brand)
    <url>
        <loc>{{ url($brand->slug . '/m') }}</loc>
        <lastmod>{{ $brand->updated_at->toW3cString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    @endforeach

    {{-- Páginas estáticas --}}
    @foreach ($pages as $page)
    <url>
        <loc>{{ url('pagina/' . $page->slug) }}</loc>
        <lastmod>{{ $page->updated_at->toW3cString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.4</priority>
    </url>
    @endforeach

</urlset>
