<?php

namespace App\Livewire\Shop;

use App\Catalog\BrandScope;
use App\Catalog\CategoryScope;
use App\Catalog\SearchScope;
use App\Contracts\ProductScopeInterface;
use App\Data\CatalogEntry;
use App\Models\Catalog\Attribute;
use App\Models\Catalog\AttributeValue;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public string $scopeType = 'category'; // 'category' | 'brand' | 'search'
    public int $scopeId = 0;
    public string $searchQuery = '';

    // URL base da página (salva no mount para uso em requests AJAX do Livewire)
    public string $pageUrl = '';

    // Filtros — gerenciados manualmente para URL amigável
    public array $attrs    = []; // [attribute_slug => [value_slug, ...]]
    public array $brandIds = []; // [brand_slug, ...]
    public string $minPrice = '';
    public string $maxPrice = '';
    public bool $inStock    = false;
    public string $sort     = 'newest';

    // Parâmetros reservados que não são slugs de atributos na URL
    private const RESERVED_PARAMS = ['min', 'max', 'estoque', 'ordem', 'marca', 'page', 'v', 'q'];

    // ── Inicialização ─────────────────────────────────────────────────────

    public function mount(): void
    {
        // Salva a URL real da página — request()->url() em requests AJAX aponta para /livewire/update
        $this->pageUrl = request()->url();

        $q = request()->query();

        // Atributos: ?tamanho=p,g&cor=preto,branco
        foreach ($q as $key => $value) {
            if (! in_array($key, self::RESERVED_PARAMS) && is_string($value) && $value !== '') {
                $parsed = array_values(array_filter(array_map('trim', explode(',', $value))));
                if (! empty($parsed)) {
                    $this->attrs[$key] = $parsed;
                }
            }
        }

        // Marcas: ?marca=nike,adidas
        if (! empty($q['marca']) && is_string($q['marca'])) {
            $this->brandIds = array_values(array_filter(array_map('trim', explode(',', $q['marca']))));
        }

        // Preço, estoque, ordenação
        if (! empty($q['min']))    $this->minPrice = (string) $q['min'];
        if (! empty($q['max']))    $this->maxPrice = (string) $q['max'];
        if (! empty($q['estoque'])) $this->inStock = $q['estoque'] !== '0';
        if (! empty($q['ordem']))  $this->sort     = $q['ordem'];
    }

    // ── URL amigável ──────────────────────────────────────────────────────

    /**
     * Parâmetros de filtro atuais como array (sem página).
     * Usado pelo paginator e por buildUrl().
     */
    private function filterParams(): array
    {
        $params = [];

        // Mantém o termo de busca na URL em modo search
        if ($this->scopeType === 'search' && $this->searchQuery !== '') {
            $params['q'] = $this->searchQuery;
        }

        foreach ($this->attrs as $slug => $values) {
            if (! empty($values)) {
                $params[$slug] = implode(',', $values);
            }
        }

        if (! empty($this->brandIds)) {
            $params['marca'] = implode(',', $this->brandIds);
        }

        if ($this->minPrice !== '') $params['min']    = $this->minPrice;
        if ($this->maxPrice !== '') $params['max']    = $this->maxPrice;
        if ($this->inStock)         $params['estoque'] = '1';
        if ($this->sort !== 'newest') $params['ordem'] = $this->sort;

        return $params;
    }

    private function buildUrl(): string
    {
        $params = $this->filterParams();
        $page   = $this->getPage();
        if ($page > 1) {
            $params['page'] = $page;
        }

        $qs = http_build_query($params);
        return $this->pageUrl . ($qs ? '?' . $qs : '');
    }

    /** Empurra a URL atual para o histórico do browser via evento JS. */
    private function pushUrl(): void
    {
        $this->dispatch('update-url', url: $this->buildUrl());
    }

    // ── Lifecycle hooks ───────────────────────────────────────────────────

    public function updatedMinPrice(): void { $this->resetPage(); $this->pushUrl(); }
    public function updatedMaxPrice(): void { $this->resetPage(); $this->pushUrl(); }
    public function updatedInStock(): void  { $this->resetPage(); $this->pushUrl(); }
    public function updatedSort(): void     { $this->resetPage(); $this->pushUrl(); }

    // ── Ações de filtro ───────────────────────────────────────────────────

    /** Toggle multi-select de atributo (OR dentro do grupo, AND entre grupos). */
    public function toggleAttr(string $attrSlug, string $valueSlug): void
    {
        $current = $this->attrs[$attrSlug] ?? [];

        if (in_array($valueSlug, $current)) {
            $current = array_values(array_filter($current, fn ($v) => $v !== $valueSlug));
        } else {
            $current[] = $valueSlug;
        }

        if (empty($current)) {
            unset($this->attrs[$attrSlug]);
            $this->attrs = $this->attrs;
        } else {
            $this->attrs[$attrSlug] = $current;
        }

        $this->resetPage();
        $this->pushUrl();
    }

    /** Toggle multi-select de marca. */
    public function toggleBrand(string $brandSlug): void
    {
        if (in_array($brandSlug, $this->brandIds)) {
            $this->brandIds = array_values(array_filter($this->brandIds, fn ($s) => $s !== $brandSlug));
        } else {
            $this->brandIds[] = $brandSlug;
        }
        $this->resetPage();
        $this->pushUrl();
    }

    public function clearPriceFilter(): void
    {
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->resetPage();
        $this->pushUrl();
        $this->dispatch('price-range-reset');
    }

    public function resetFilters(): void
    {
        $this->attrs    = [];
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->inStock  = false;
        $this->brandIds = [];
        $this->sort     = 'newest';
        $this->resetPage();
        $this->pushUrl();
        $this->dispatch('price-range-reset');
    }

    // ── Escopo ────────────────────────────────────────────────────────────

    #[Computed]
    public function scope(): ProductScopeInterface
    {
        return match ($this->scopeType) {
            'brand'  => new BrandScope(Brand::findOrFail($this->scopeId)),
            'search' => new SearchScope($this->searchQuery),
            default  => new CategoryScope(Category::findOrFail($this->scopeId)),
        };
    }

    // ── Dados computados ──────────────────────────────────────────────────

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        $query = $this->scope->applyToQuery(
            Product::query()->where('active', true)
        );

        // Filtro de preço — considera preço efetivo (promoção + herança variante→produto)
        if ($this->minPrice !== '' || $this->maxPrice !== '') {
            $min = $this->minPrice !== '' ? (float) $this->minPrice : null;
            $max = $this->maxPrice !== '' ? (float) $this->maxPrice : null;

            $productPrice = "CASE
                WHEN products.sale_price IS NOT NULL
                    AND (products.sale_start IS NULL OR products.sale_start <= NOW())
                    AND (products.sale_end IS NULL OR products.sale_end >= NOW())
                THEN products.sale_price
                ELSE products.price
            END";

            $variantPrice = "COALESCE(
                product_variants.sale_price,
                product_variants.price,
                (SELECT CASE
                    WHEN p.sale_price IS NOT NULL
                        AND (p.sale_start IS NULL OR p.sale_start <= NOW())
                        AND (p.sale_end IS NULL OR p.sale_end >= NOW())
                    THEN p.sale_price ELSE p.price
                 END FROM products p WHERE p.id = product_variants.product_id)
            )";

            $query->where(function ($q) use ($min, $max, $productPrice, $variantPrice) {
                $q->where(function ($q) use ($min, $max, $productPrice) {
                    $q->where('type', 'simple');
                    if ($min !== null) $q->whereRaw("($productPrice) >= ?", [$min]);
                    if ($max !== null) $q->whereRaw("($productPrice) <= ?", [$max]);
                })->orWhere(function ($q) use ($min, $max, $variantPrice) {
                    $q->where('type', 'variable')
                      ->whereHas('variants', function ($q) use ($min, $max, $variantPrice) {
                          $q->where('active', true);
                          if ($min !== null) $q->whereRaw("($variantPrice) >= ?", [$min]);
                          if ($max !== null) $q->whereRaw("($variantPrice) <= ?", [$max]);
                      });
                });
            });
        }

        // Filtro de estoque
        if ($this->inStock) {
            $query->where(function ($q) {
                $q->where(function ($q) {
                    $q->where('type', 'simple')->where('stock', '>', 0);
                })->orWhere(function ($q) {
                    $q->where('type', 'variable')
                      ->whereHas('variants', fn ($q) => $q->where('active', true)->where('stock', '>', 0));
                });
            });
        }

        // Filtro de marca: OR entre marcas selecionadas
        if (! empty($this->brandIds)) {
            $query->whereHas('brand', fn ($q) => $q->whereIn('slug', $this->brandIds));
        }

        // Filtro de atributos: OR dentro do mesmo atributo (variante OU característica), AND entre atributos
        foreach ($this->attrs as $attrSlug => $valueSlugs) {
            if (! empty($valueSlugs)) {
                $query->where(function ($q) use ($attrSlug, $valueSlugs) {
                    $q->whereHas('variants.attributeValues', fn ($q) =>
                        $q->whereIn('attribute_values.slug', $valueSlugs)
                          ->whereHas('attribute', fn ($q) => $q->where('slug', $attrSlug))
                    )
                    ->orWhereHas('characteristicValues', fn ($q) =>
                        $q->whereIn('attribute_values.slug', $valueSlugs)
                          ->whereHas('attribute', fn ($q) => $q->where('slug', $attrSlug))
                    );
                });
            }
        }

        // Ordenação SQL apenas para newest e name (campos do produto, sem expansão)
        // price_asc/price_desc são aplicados após expandIntoEntries() no preço efetivo
        match ($this->sort) {
            'name_asc'   => $query->orderBy('name', 'asc'),
            default      => $query->latest(),
        };

        $rawProducts = $query
            ->with([
                'media',
                'attributes' => fn ($q) => $q->withPivot('expand_in_catalog'),
                'variants'   => fn ($q) => $q->where('active', true)
                                             ->with(['attributeValues', 'media'])
                                             ->orderBy('order'),
            ])
            ->withCount('variants')
            ->get();

        $entries = $this->expandIntoEntries($rawProducts);

        $page    = $this->getPage();
        $perPage = 24;

        return new LengthAwarePaginator(
            $entries->forPage($page, $perPage)->values(),
            $entries->count(),
            $perPage,
            $page,
            // pageUrl + filterParams() garantem links corretos mesmo em requests AJAX
            ['path' => $this->pageUrl, 'query' => $this->filterParams()]
        );
    }

    /**
     * Expande produtos com atributo "expand_in_catalog" em múltiplos CatalogEntry.
     * URLs geradas usam ?v[attr]=value para pré-selecionar variante no ProductDetail.
     */
    private function expandIntoEntries(Collection $products): Collection
    {
        $entries = collect();

        // Filtros de atributo com seleção única são carregados na URL do produto,
        // para que o ProductDetail pré-selecione a variante correspondente ao filtro ativo.
        // Multi-seleção é ignorada (não dá para pré-selecionar múltiplos valores).
        // Apenas atributos de VARIANTE são carregados — características não se mapeiam
        // para valores de variante e causariam matchingVariants vazio se incluídas.
        $variantAttrSlugs = $this->availableAttributes->pluck('slug')->flip()->toArray();
        $carryParams = [];
        foreach ($this->attrs as $attrSlug => $valueSlugs) {
            if (count($valueSlugs) === 1 && isset($variantAttrSlugs[$attrSlug])) {
                $carryParams[$attrSlug] = $valueSlugs[0];
            }
        }

        foreach ($products as $product) {
            $expandAttr = $product->attributes
                ->first(fn ($attr) => (bool) $attr->pivot->expand_in_catalog);

            if (! $expandAttr) {
                $url = '/' . $product->slug . '/p';
                if (! empty($carryParams)) {
                    $url .= '?' . http_build_query($carryParams);
                }

                $inStock = $product->type === 'simple'
                    ? ($product->stock ?? 0) > 0
                    : $product->variants->where('stock', '>', 0)->isNotEmpty();

                $entries->push(new CatalogEntry(
                    product:       $product,
                    displayName:   $product->name,
                    url:           $url,
                    price:         $product->getCurrentPrice(),
                    originalPrice: $product->isOnSale() ? (float) $product->price : null,
                    imageUrl:      $product->getFirstMediaUrl('cover', 'thumb') ?: null,
                    isNew:         (bool) $product->is_new,
                    isOnSale:      $product->isOnSale(),
                    inStock:       $inStock,
                ));
                continue;
            }

            $values = $expandAttr->values
                ->filter(fn ($value) =>
                    $product->variants->some(fn ($v) =>
                        $v->attributeValues->contains('id', $value->id)
                    )
                );

            foreach ($values as $value) {
                // Se o atributo de expansão está filtrado, pular valores não selecionados
                if (
                    ! empty($this->attrs[$expandAttr->slug]) &&
                    ! in_array($value->slug, $this->attrs[$expandAttr->slug])
                ) {
                    continue;
                }

                // Coleta TODAS as variantes que satisfazem o valor de expansão + carry params.
                // Se não houver nenhuma, este card não faz sentido exibir.
                $matchingVariants = $product->variants->filter(function ($v) use ($value, $carryParams) {
                    $slugs = $v->attributeValues->pluck('slug')->toArray();
                    if (! in_array($value->slug, $slugs)) {
                        return false;
                    }
                    foreach ($carryParams as $carrySlug) {
                        if (! in_array($carrySlug, $slugs)) {
                            return false;
                        }
                    }
                    return true;
                });

                if ($matchingVariants->isEmpty()) continue;

                // Variante representativa para imagem e preço: prefere a com estoque
                // (ex: G-Vermelho sem estoque + M-Vermelho com estoque → usa M-Vermelho)
                $variant = $matchingVariants->where('stock', '>', 0)->first()
                    ?? $matchingVariants->first();

                $inStock = $matchingVariants->where('stock', '>', 0)->isNotEmpty();

                $imageUrl = $variant->variantGroup?->getFirstMediaUrl('group-cover', 'thumb')
                    ?: $variant->getFirstMediaUrl('variant-cover', 'thumb')
                    ?: $product->getFirstMediaUrl('cover', 'thumb')
                    ?: null;

                if ($variant->price !== null) {
                    $price         = $variant->getEffectivePrice();
                    $originalPrice = $variant->isOnSale() ? (float) $variant->price : null;
                } else {
                    $price         = $product->getCurrentPrice();
                    $originalPrice = $product->isOnSale() ? (float) $product->price : null;
                }

                if ($this->minPrice !== '' && $price < (float) $this->minPrice) continue;
                if ($this->maxPrice !== '' && $price > (float) $this->maxPrice) continue;

                // URL: atributo de expansão + demais filtros ativos de atributo (carry)
                // O atributo de expansão sobrescreve o carry caso haja sobreposição.
                $urlParams = array_merge($carryParams, [$expandAttr->slug => $value->slug]);
                $entries->push(new CatalogEntry(
                    product:       $product,
                    displayName:   $product->name . ' ' . $value->getLabel(),
                    url:           '/' . $product->slug . '/p?' . http_build_query($urlParams),
                    price:         $price,
                    originalPrice: $originalPrice,
                    imageUrl:      $imageUrl,
                    isNew:         (bool) $product->is_new,
                    isOnSale:      $originalPrice !== null,
                    inStock:       $inStock,
                    expandedBy:    $value,
                ));
            }
        }

        // Ordenação final sobre o preço efetivo dos CatalogEntry (inclui promoção + variantes)
        $sorted = match ($this->sort) {
            'price_asc'  => $entries->sortBy('price'),
            'price_desc' => $entries->sortByDesc('price'),
            'best_rated' => $entries->sortByDesc(fn ($e) => $e->product->rating_avg),
            default      => $entries, // newest e name_asc já ordenados via SQL
        };

        // Produtos sem estoque sempre ao final, preservando a ordem relativa dentro de cada grupo
        return $sorted->sortBy(fn ($e) => $e->inStock ? 0 : 1)->values();
    }

    #[Computed]
    public function subcategories(): Collection
    {
        if ($this->scopeType !== 'category') {
            return collect();
        }

        return Category::findOrFail($this->scopeId)
            ->children()
            ->get();
    }

    #[Computed]
    public function availableBrands(): Collection
    {
        if ($this->scopeType === 'brand') {
            return collect();
        }

        return Brand::where('active', true)
            ->whereHas('products', fn ($q) => $this->scope->applyToQuery(
                $q->where('active', true)
            ))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function availableAttributes(): Collection
    {
        $productIds = $this->scope->baseProductIds();

        if ($productIds->isEmpty()) {
            return collect();
        }

        $attributeIds = DB::table('product_attributes')
            ->whereIn('product_id', $productIds)
            ->pluck('attribute_id')
            ->unique();

        return Attribute::whereIn('id', $attributeIds)
            ->orderBy('order')
            ->with(['values' => function ($q) use ($productIds) {
                $q->whereHas('variants', fn ($q) => $q->whereIn('product_id', $productIds))
                  ->orderBy('order');
            }])
            ->get()
            ->filter(fn ($attr) => $attr->values->isNotEmpty())
            ->values();
    }

    #[Computed]
    public function availableCharacteristics(): Collection
    {
        $productIds = $this->scope->baseProductIds();

        if ($productIds->isEmpty()) {
            return collect();
        }

        $valueIds = DB::table('product_characteristic_values')
            ->whereIn('product_id', $productIds)
            ->pluck('attribute_value_id')
            ->unique();

        if ($valueIds->isEmpty()) {
            return collect();
        }

        $attributeIds = AttributeValue::whereIn('id', $valueIds)
            ->pluck('attribute_id')
            ->unique();

        return Attribute::whereIn('id', $attributeIds)
            ->orderBy('order')
            ->with(['values' => function ($q) use ($valueIds) {
                $q->whereIn('id', $valueIds)->orderBy('order');
            }])
            ->get()
            ->filter(fn ($attr) => $attr->values->isNotEmpty())
            ->values();
    }

    #[Computed]
    public function priceRange(): array
    {
        $base = $this->scope->applyToQuery(Product::where('active', true));

        $effectivePrice = "CASE
            WHEN sale_price IS NOT NULL
                AND (sale_start IS NULL OR sale_start <= NOW())
                AND (sale_end IS NULL OR sale_end >= NOW())
            THEN sale_price
            ELSE price
        END";

        return [
            'min' => (float) ($base->clone()->selectRaw("MIN($effectivePrice) as ep")->value('ep') ?? 0),
            'max' => (float) ($base->clone()->selectRaw("MAX($effectivePrice) as ep")->value('ep') ?? 9999),
        ];
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return collect($this->attrs)->some(fn ($v) => ! empty($v))
            || $this->minPrice !== ''
            || $this->maxPrice !== ''
            || $this->inStock
            || ! empty($this->brandIds);
    }

    public function render(): View
    {
        return view('livewire.shop.product-list');
    }
}
