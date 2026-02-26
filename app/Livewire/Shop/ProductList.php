<?php

namespace App\Livewire\Shop;

use App\Data\CatalogEntry;
use App\Models\Attribute;
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

    public Category $category;

    // Filtros sincronizados com a URL (query string)
    #[Url(as: 'attrs', except: [])]
    public array $attrs = []; // [attribute_id => attribute_value_id]

    #[Url(as: 'min', except: '')]
    public string $minPrice = '';

    #[Url(as: 'max', except: '')]
    public string $maxPrice = '';

    #[Url(as: 'estoque', except: false)]
    public bool $inStock = false;

    #[Url(as: 'ordem', except: 'newest')]
    public string $sort = 'newest';

    // Reinicia a paginação ao mudar qualquer filtro
    public function updatedAttrs(): void      { $this->resetPage(); }
    public function updatedMinPrice(): void   { $this->resetPage(); }
    public function updatedMaxPrice(): void   { $this->resetPage(); }
    public function updatedInStock(): void    { $this->resetPage(); }
    public function updatedSort(): void       { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->attrs    = [];
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->inStock  = false;
        $this->sort     = 'newest';
        $this->resetPage();
    }

    // ── Dados computados ──────────────────────────────────────────────────

    #[Computed]
    public function categoryIds(): array
    {
        return $this->category->getAllDescendantIds();
    }

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        $query = Product::query()
            ->where('active', true)
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $this->categoryIds));

        // Filtro de preço
        if ($this->minPrice !== '') {
            $query->where('price', '>=', (float) $this->minPrice);
        }
        if ($this->maxPrice !== '') {
            $query->where('price', '<=', (float) $this->maxPrice);
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

        // Filtro de atributos (cada atributo selecionado filtra com AND)
        foreach ($this->attrs as $attrId => $valueId) {
            if ($valueId) {
                $query->whereHas('variants.attributeValues', fn ($q) =>
                    $q->where('attribute_values.id', (int) $valueId)
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
                    isset($this->attrs[$expandAttr->id]) &&
                    (int) $this->attrs[$expandAttr->id] !== $value->id
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

                $entries->push(new CatalogEntry(
                    product:       $product,
                    displayName:   $product->name . ' ' . $value->getLabel(),
                    url:           '/' . $product->slug . '/p?v[' . $expandAttr->id . ']=' . $value->id,
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
        return $this->category->children()->where('active', true)->get();
    }

    #[Computed]
    public function availableAttributes(): Collection
    {
        $productIds = Product::where('active', true)
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $this->categoryIds))
            ->pluck('id');

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
        $base = Product::where('active', true)
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $this->categoryIds));

        return [
            'min' => (float) ($base->min('price') ?? 0),
            'max' => (float) ($base->max('price') ?? 9999),
        ];
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return ! empty(array_filter($this->attrs))
            || $this->minPrice !== ''
            || $this->maxPrice !== ''
            || $this->inStock;
    }

    public function render(): View
    {
        return view('livewire.shop.product-list');
    }
}
