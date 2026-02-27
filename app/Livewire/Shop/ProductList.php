<?php

namespace App\Livewire\Shop;

use App\Catalog\BrandScope;
use App\Catalog\CategoryScope;
use App\Contracts\ProductScopeInterface;
use App\Data\CatalogEntry;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public string $scopeType = 'category'; // 'category' | 'brand'
    public int $scopeId;

    // Filtros sincronizados com a URL (query string)
    #[Url(as: 'attrs', except: [])]
    public array $attrs = []; // [attribute_slug => value_slug]

    #[Url(as: 'min', except: '')]
    public string $minPrice = '';

    #[Url(as: 'max', except: '')]
    public string $maxPrice = '';

    #[Url(as: 'estoque', except: false)]
    public bool $inStock = false;

    #[Url(as: 'marca', except: '')]
    public string $brandId = ''; // slug da marca selecionada (só usado em scopeType=category)

    #[Url(as: 'ordem', except: 'newest')]
    public string $sort = 'newest';

    // Reinicia a paginação ao mudar qualquer filtro
    public function updatedAttrs(): void      { $this->resetPage(); }
    public function updatedMinPrice(): void   { $this->resetPage(); }
    public function updatedMaxPrice(): void   { $this->resetPage(); }
    public function updatedInStock(): void    { $this->resetPage(); }
    public function updatedBrandId(): void    { $this->resetPage(); }
    public function updatedSort(): void       { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->attrs    = [];
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->inStock  = false;
        $this->brandId  = '';
        $this->sort     = 'newest';
        $this->resetPage();
    }

    // ── Escopo ────────────────────────────────────────────────────────────

    #[Computed]
    public function scope(): ProductScopeInterface
    {
        return match ($this->scopeType) {
            'brand'  => new BrandScope(Brand::findOrFail($this->scopeId)),
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

            // Preço efetivo do produto (respeita datas de promoção)
            $productPrice = "CASE
                WHEN products.sale_price IS NOT NULL
                    AND (products.sale_start IS NULL OR products.sale_start <= NOW())
                    AND (products.sale_end IS NULL OR products.sale_end >= NOW())
                THEN products.sale_price
                ELSE products.price
            END";

            // Preço efetivo da variante: sale_price > price > preço efetivo do produto pai
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
                // Produto simples: filtra pelo preço efetivo do produto
                $q->where(function ($q) use ($min, $max, $productPrice) {
                    $q->where('type', 'simple');
                    if ($min !== null) $q->whereRaw("($productPrice) >= ?", [$min]);
                    if ($max !== null) $q->whereRaw("($productPrice) <= ?", [$max]);
                })
                // Produto variável: alguma variante ativa com preço efetivo no intervalo
                ->orWhere(function ($q) use ($min, $max, $variantPrice) {
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

        // Filtro de marca (apenas em páginas de categoria)
        if ($this->brandId !== '') {
            $query->whereHas('brand', fn ($q) => $q->where('slug', $this->brandId));
        }

        // Filtro de atributos por slug (cada atributo selecionado filtra com AND)
        foreach ($this->attrs as $attrSlug => $valueSlug) {
            if ($valueSlug) {
                $query->whereHas('variants.attributeValues', fn ($q) =>
                    $q->where('attribute_values.slug', $valueSlug)
                      ->whereHas('attribute', fn ($q) => $q->where('slug', $attrSlug))
                );
            }
        }

        // Ordenação
        match ($this->sort) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
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
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Expande produtos com atributo "expand_in_catalog" em múltiplos CatalogEntry.
     */
    private function expandIntoEntries(Collection $products): Collection
    {
        $entries = collect();

        foreach ($products as $product) {
            $expandAttr = $product->attributes
                ->first(fn ($attr) => (bool) $attr->pivot->expand_in_catalog);

            if (! $expandAttr) {
                // Produto normal — um único card
                $entries->push(new CatalogEntry(
                    product:       $product,
                    displayName:   $product->name,
                    url:           '/' . $product->slug . '/p',
                    price:         $product->getCurrentPrice(),
                    originalPrice: $product->isOnSale() ? (float) $product->price : null,
                    imageUrl:      $product->getFirstMediaUrl('cover') ?: null,
                    isNew:         (bool) $product->is_new,
                    isOnSale:      $product->isOnSale(),
                ));
                continue;
            }

            // Coleta valores do atributo de expansão que têm variantes ativas neste produto
            $values = $expandAttr->values
                ->filter(fn ($value) =>
                    $product->variants->some(fn ($v) =>
                        $v->attributeValues->contains('id', $value->id)
                    )
                );

            foreach ($values as $value) {
                // Se o atributo de expansão está filtrado, pular outros valores
                if (
                    isset($this->attrs[$expandAttr->slug]) &&
                    $this->attrs[$expandAttr->slug] !== $value->slug
                ) {
                    continue;
                }

                // Primeira variante com este valor
                $variant = $product->variants->first(
                    fn ($v) => $v->attributeValues->contains('id', $value->id)
                );

                $imageUrl = $variant?->getFirstMediaUrl('variant-cover') ?: null;
                if (! $imageUrl) {
                    $imageUrl = $product->getFirstMediaUrl('cover') ?: null;
                }

                if ($variant && $variant->price !== null) {
                    // Variante tem preço próprio
                    $price         = $variant->getEffectivePrice();
                    $originalPrice = $variant->isOnSale() ? (float) $variant->price : null;
                } else {
                    // Variante sem preço — herda do produto pai
                    $price         = $product->getCurrentPrice();
                    $originalPrice = $product->isOnSale() ? (float) $product->price : null;
                }

                // Filtra entries expandidos pelo intervalo de preço (o SQL filtra por produto,
                // mas cada entry tem seu próprio preço efetivo que precisa ser validado)
                if ($this->minPrice !== '' && $price < (float) $this->minPrice) {
                    continue;
                }
                if ($this->maxPrice !== '' && $price > (float) $this->maxPrice) {
                    continue;
                }

                $entries->push(new CatalogEntry(
                    product:       $product,
                    displayName:   $product->name . ' ' . $value->getLabel(),
                    url:           '/' . $product->slug . '/p?v[' . $expandAttr->slug . ']=' . $value->slug,
                    price:         $price,
                    originalPrice: $originalPrice,
                    imageUrl:      $imageUrl,
                    isNew:         (bool) $product->is_new,
                    isOnSale:      $originalPrice !== null,
                    expandedBy:    $value,
                ));
            }
        }

        return $entries;
    }

    #[Computed]
    public function subcategories(): Collection
    {
        if ($this->scopeType !== 'category') {
            return collect();
        }

        return Category::findOrFail($this->scopeId)
            ->children()
            ->where('active', true)
            ->get();
    }

    #[Computed]
    public function availableBrands(): Collection
    {
        if ($this->scopeType !== 'category') {
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
            ->with(['values' => function ($q) use ($productIds) {
                $q->whereHas('variants', fn ($q) => $q->whereIn('product_id', $productIds))
                  ->orderBy('order');
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
        return ! empty(array_filter($this->attrs))
            || $this->minPrice !== ''
            || $this->maxPrice !== ''
            || $this->inStock
            || $this->brandId !== '';
    }

    public function render(): View
    {
        return view('livewire.shop.product-list');
    }
}
