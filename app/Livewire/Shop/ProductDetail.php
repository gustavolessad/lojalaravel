<?php

namespace App\Livewire\Shop;

use App\Data\CatalogEntry;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockNotification;
use App\Services\CartService;
use App\Services\ShippingCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProductDetail extends Component
{
    public int $productId;

    /** [attribute_slug => value_slug] — gerenciado manualmente para URL amigável */
    public array $selected = [];

    // URL base da página (salva no mount para uso em requests AJAX do Livewire)
    public string $pageUrl = '';

    public int $quantity = 1;

    // Formulário "Avise-me"
    public string $notifyEmail = '';
    public bool $notified = false;

    // Simulador de frete
    public string $shippingCep = '';
    public array $shippingOptions = [];
    public ?string $shippingError = null;

    public function mount(Product $product): void
    {
        $this->productId = $product->id;
        $this->pageUrl   = request()->url();

        // Pré-seleção via URL amigável: ?cor=vermelho&tamanho=g
        $q = request()->query();
        foreach ($q as $key => $value) {
            if (is_string($value) && $value !== '') {
                $this->selected[$key] = $value;
            }
        }

        // Auto-seleciona completando as seleções do URL com a variante mais adequada.
        // Usa somente variantes COMPATÍVEIS com os valores já selecionados (ex: ?cor=vermelho
        // deve completar com tamanho G, não P — pois vermelho P não existe).
        if ($product->isVariable()) {
            $variants = ProductVariant::where('product_id', $product->id)
                ->where('active', true)
                ->with('attributeValues.attribute')
                ->orderBy('order')
                ->get();

            $urlSlugs = array_values($this->selected); // slugs vindos da URL

            // Filtra variantes compatíveis com os valores pré-selecionados
            $compatible = empty($urlSlugs)
                ? $variants
                : $variants->filter(function ($v) use ($urlSlugs) {
                    $slugs = $v->attributeValues->pluck('slug')->toArray();
                    foreach ($urlSlugs as $slug) {
                        if (! in_array($slug, $slugs)) {
                            return false;
                        }
                    }
                    return true;
                });

            // Se a combinação da URL não existe (ex: cor=vermelho&tamanho=P inválido),
            // ignora as seleções do URL e cai no auto-select geral
            if ($compatible->isEmpty()) {
                $this->selected = [];
                $compatible     = $variants;
            }

            $first = $compatible->where('stock', '>', 0)->first()
                ?? $compatible->first();

            if ($first) {
                foreach ($first->attributeValues as $av) {
                    if (! array_key_exists($av->attribute->slug, $this->selected)) {
                        $this->selected[$av->attribute->slug] = $av->slug;
                    }
                }
            }
        }
    }

    // ── URL amigável ──────────────────────────────────────────────────────

    private function buildUrl(): string
    {
        $params = array_filter($this->selected);
        $qs     = http_build_query($params);
        return $this->pageUrl . ($qs ? '?' . $qs : '');
    }

    private function pushUrl(): void
    {
        $this->dispatch('update-url', url: $this->buildUrl());
    }

    // ── Produto completo com todas as relações ────────────────────────────

    #[Computed]
    public function product(): Product
    {
        return Product::with([
            'variants' => fn ($q) => $q->where('active', true)
                ->with(['attributeValues.attribute', 'media', 'variantGroup.media'])
                ->orderBy('order'),
            'attributes' => fn ($q) => $q->withPivot('expand_in_catalog', 'variant_display')->with([
                'values' => fn ($vq) => $vq
                    ->with('media')
                    ->whereHas('variants', fn ($varq) => $varq
                        ->where('product_id', $this->productId)
                        ->where('active', true)
                    )
                    ->orderBy('order'),
            ]),
            'media',
            'categories',
            'brand',
            'compraJunto',
            'characteristicValues.attribute',
        ])->findOrFail($this->productId);
    }

    // ── Variante correspondente às seleções atuais ────────────────────────

    #[Computed]
    public function currentVariant(): ?ProductVariant
    {
        if ($this->product->isSimple()) {
            return null;
        }

        $selected = array_filter($this->selected);

        if (empty($selected)) {
            return null;
        }

        return $this->product->variants->first(function (ProductVariant $variant) use ($selected) {
            $valueSlugs = $variant->attributeValues->pluck('slug')->toArray();

            foreach ($selected as $valueSlug) {
                if (! in_array($valueSlug, $valueSlugs)) {
                    return false;
                }
            }

            return count($valueSlugs) === count($selected);
        });
    }

    // ── Preço ─────────────────────────────────────────────────────────────

    #[Computed]
    public function currentPrice(): float
    {
        return $this->currentVariant
            ? $this->currentVariant->getEffectivePrice()
            : $this->product->getCurrentPrice();
    }

    #[Computed]
    public function originalPrice(): ?float
    {
        if ($this->currentVariant) {
            if ($this->currentVariant->price !== null) {
                // Variante tem preço próprio
                return $this->currentVariant->isOnSale() ? (float) $this->currentVariant->price : null;
            }
            // Variante sem preço — herda promoção do produto pai
            return $this->product->isOnSale() ? (float) $this->product->price : null;
        }

        return $this->product->isOnSale() ? (float) $this->product->price : null;
    }

    // ── Estoque ───────────────────────────────────────────────────────────

    #[Computed]
    public function inStock(): bool
    {
        if ($this->product->isSimple()) {
            return ($this->product->stock ?? 0) > 0;
        }

        if ($this->currentVariant) {
            return ($this->currentVariant->stock ?? 0) > 0;
        }

        // Nenhuma variante selecionada: verifica se existe alguma com estoque
        return $this->product->variants->where('stock', '>', 0)->isNotEmpty();
    }

    #[Computed]
    public function currentStock(): int
    {
        if ($this->product->isSimple()) {
            return (int) ($this->product->stock ?? 0);
        }

        return (int) ($this->currentVariant?->stock ?? 0);
    }

    // ── Imagens ───────────────────────────────────────────────────────────

    #[Computed]
    public function currentImages(): Collection
    {
        if ($this->currentVariant) {
            // 1. Usa o grupo de imagens da variante (se tiver)
            $group = $this->currentVariant->variantGroup;
            if ($group) {
                $cover = $group->getFirstMediaUrl('group-cover', 'optimized');
                if ($cover !== '') {
                    $images = collect([$cover]);
                    foreach ($group->getMedia('group-gallery') as $media) {
                        $images->push($media->getUrl('optimized') ?: $media->getUrl());
                    }
                    return $images;
                }
            }

            // 2. Backward compat: imagens próprias da variante
            $cover = $this->currentVariant->getFirstMediaUrl('variant-cover', 'optimized');
            if ($cover !== '') {
                $images = collect([$cover]);
                foreach ($this->currentVariant->getMedia('variant-gallery') as $media) {
                    $images->push($media->getUrl('optimized') ?: $media->getUrl());
                }
                return $images;
            }
        }

        $images = collect();
        $cover  = $this->product->getFirstMediaUrl('cover', 'optimized');

        if ($cover !== '') {
            $images->push($cover);
        }

        foreach ($this->product->getMedia('gallery') as $media) {
            $url = $media->getUrl('optimized') ?: $media->getUrl();
            if ($url !== $cover) {
                $images->push($url);
            }
        }

        return $images->values();
    }

    // ── Slugs de valores disponíveis por atributo (considerando outras seleções) ──

    #[Computed]
    public function availableValueSlugs(): array
    {
        if ($this->product->isSimple()) {
            return [];
        }

        $variants = $this->product->variants;
        $result   = [];

        foreach ($this->product->attributes as $attribute) {
            // Seleções de OUTROS atributos
            $otherSelected = array_filter(
                $this->selected,
                fn ($v, $k) => $k !== $attribute->slug && $v,
                ARRAY_FILTER_USE_BOTH
            );

            $available = $variants
                ->filter(function (ProductVariant $variant) use ($otherSelected) {
                    $slugs = $variant->attributeValues->pluck('slug')->toArray();
                    foreach ($otherSelected as $valueSlug) {
                        if (! in_array($valueSlug, $slugs)) {
                            return false;
                        }
                    }
                    return true;
                })
                ->flatMap(fn ($v) => $v->attributeValues->pluck('slug'))
                ->unique()
                ->toArray();

            $result[$attribute->slug] = array_values($available);
        }

        return $result;
    }

    /**
     * Slugs de valores cujas variantes compatíveis com a seleção atual de OUTROS
     * atributos estão todas sem estoque. Mesmo approach contextual de availableValueSlugs.
     *
     * Exemplo: branco-M (sem estoque) + branco-P (com estoque).
     * Com M selecionado → branco aparece aqui (única compatível, branco-M, tem stock=0).
     * Sem M selecionado → branco NÃO aparece (branco-P tem estoque).
     */
    #[Computed]
    public function outOfStockValueSlugs(): array
    {
        if ($this->product->isSimple()) {
            return [];
        }

        $variants = $this->product->variants;
        $result   = [];

        foreach ($this->product->attributes as $attribute) {
            $otherSelected = array_filter(
                $this->selected,
                fn ($v, $k) => $k !== $attribute->slug && $v,
                ARRAY_FILTER_USE_BOTH
            );

            $result[$attribute->slug] = $attribute->values
                ->filter(function ($value) use ($variants, $otherSelected) {
                    $compatible = $variants->filter(function ($v) use ($value, $otherSelected) {
                        $slugs = $v->attributeValues->pluck('slug')->toArray();
                        if (! in_array($value->slug, $slugs)) {
                            return false;
                        }
                        foreach ($otherSelected as $otherSlug) {
                            if (! in_array($otherSlug, $slugs)) {
                                return false;
                            }
                        }
                        return true;
                    });

                    return $compatible->isNotEmpty()
                        && $compatible->every(fn ($v) => ($v->stock ?? 0) === 0);
                })
                ->pluck('slug')
                ->toArray();
        }

        return $result;
    }

    /**
     * Mapa de imagens de variante por atributo.
     * Retorna [attribute_slug => [value_slug => image_url]] conforme o
     * variant_display configurado no produto (text, attribute_icon, group_cover).
     */
    #[Computed]
    public function variantImageMap(): array
    {
        $map = [];

        foreach ($this->product->attributes as $attribute) {
            $display = $attribute->pivot->variant_display ?? 'text';

            if ($display === 'text') {
                continue;
            }

            $map[$attribute->slug] = [];

            foreach ($attribute->values as $value) {
                $url = null;

                if ($display === 'attribute_icon') {
                    $url = $value->getFirstMediaUrl('icon', 'thumb');
                } elseif ($display === 'group_cover') {
                    $variant = $this->product->variants
                        ->filter(fn ($v) => $v->attributeValues->contains('id', $value->id))
                        ->sortByDesc(fn ($v) => ($v->stock ?? 0) > 0 ? 1 : 0)
                        ->first();

                    if ($variant) {
                        $url = $variant->variantGroup?->getFirstMediaUrl('group-cover', 'thumb')
                            ?: $variant->getFirstMediaUrl('variant-cover', 'thumb');
                    }
                }

                if ($url) {
                    $map[$attribute->slug][$value->slug] = $url;
                }
            }
        }

        return $map;
    }

    // ── Produtos Recomendados (selecionados manualmente no admin) ─────────

    #[Computed]
    public function recommendedProducts(): Collection
    {
        return $this->product->recommended()
            ->where('active', true)
            ->with('media')
            ->get()
            ->map(fn (Product $p) => CatalogEntry::fromProduct($p));
    }

    // ── Produtos da mesma categoria (dinâmico, exclui o atual) ────────────

    #[Computed]
    public function categoryProducts(): Collection
    {
        $categoryIds = $this->product->categories->pluck('id');

        if ($categoryIds->isEmpty()) {
            return collect();
        }

        return Product::query()
            ->where('active', true)
            ->where('id', '!=', $this->productId)
            ->whereHas('categories', fn ($q) => $q->whereIn('id', $categoryIds))
            ->with('media')
            ->limit(8)
            ->get()
            ->map(fn (Product $p) => CatalogEntry::fromProduct($p));
    }

    // ── Ações ─────────────────────────────────────────────────────────────

    public function selectValue(string $attrSlug, string $valueSlug): void
    {
        $this->selected[$attrSlug] = $valueSlug;
        $this->notified      = false;
        $this->notifyEmail   = '';
        $this->shippingOptions = [];
        $this->shippingError   = null;

        // Verifica se a nova combinação tem uma variante válida.
        // Se não tiver (ex: cor=vermelho com tamanho=P que não existe),
        // faz cascade: mantém o valor clicado e ajusta os demais atributos
        // para a variante mais próxima (com estoque, se possível).
        $currentSlugs = array_values(array_filter($this->selected));
        $variants     = $this->product->variants;

        $isValid = $variants->some(function ($v) use ($currentSlugs) {
            $slugs = $v->attributeValues->pluck('slug')->toArray();
            foreach ($currentSlugs as $slug) {
                if (! in_array($slug, $slugs)) {
                    return false;
                }
            }
            return true;
        });

        if (! $isValid) {
            // Pega a melhor variante que contenha o valor recém-selecionado
            $best = $variants
                ->filter(fn ($v) => $v->attributeValues->pluck('slug')->contains($valueSlug))
                ->sortByDesc('stock')
                ->first();

            if ($best) {
                foreach ($best->attributeValues as $av) {
                    $this->selected[$av->attribute->slug] = $av->slug;
                }
            }
        }

        $this->pushUrl();

        // Notifica o BuyTogether sobre a nova variante/preço do produto principal
        $this->dispatch('main-variant-updated',
            variantId: $this->currentVariant?->id,
            price:     (float) $this->currentPrice,
        );
    }

    public function incrementQty(): void
    {
        $max = $this->currentStock;

        if ($max > 0 && $this->quantity >= $max) {
            return;
        }

        $this->quantity++;
    }

    public function decrementQty(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart(): void
    {
        if ($this->product->isVariable() && ! $this->currentVariant) {
            session()->flash('cart-error', 'Selecione todas as opções antes de adicionar ao carrinho.');
            return;
        }

        if (! $this->inStock) {
            session()->flash('cart-error', 'Produto fora de estoque.');
            return;
        }

        app(CartService::class)->add(
            productId: $this->productId,
            quantity:  max(1, $this->quantity),
            variantId: $this->currentVariant?->id,
        );

        $this->dispatch('cart-updated');

        // Dispara evento de browser — Alpine.js abre o modal de confirmação
        $this->dispatch('product-added-to-cart',
            name:    $this->product->name,
            image:   $this->product->getFirstMediaUrl('cover', 'thumb') ?: '',
            variant: $this->currentVariant?->label ?: '',
        );
    }

    public function notifyMe(): void
    {
        $this->validate(['notifyEmail' => 'required|email|max:255']);

        StockNotification::firstOrCreate([
            'product_id' => $this->productId,
            'variant_id' => $this->currentVariant?->id,
            'email'      => strtolower(trim($this->notifyEmail)),
        ]);

        $this->notified    = true;
        $this->notifyEmail = '';
    }

    public function calculateShipping(): void
    {
        $digits = preg_replace('/\D/', '', $this->shippingCep);

        if (strlen($digits) !== 8) {
            $this->shippingError   = 'Informe um CEP válido com 8 dígitos.';
            $this->shippingOptions = [];
            return;
        }

        // Para produtos variáveis exige variante selecionada (para ter peso/dimensões corretos)
        if ($this->product->isVariable() && ! $this->currentVariant) {
            $this->shippingError   = 'Selecione as opções do produto antes de calcular o frete.';
            $this->shippingOptions = [];
            return;
        }

        // Constrói item simulado com dados do produto/variante atual
        $variant = $this->currentVariant;
        $product = $this->product;

        $fakeProduct = (object) [
            'id'     => $product->id,
            'weight' => $variant?->weight ?? $product->weight,
            'width'  => $variant?->width  ?? $product->width,
            'height' => $variant?->height ?? $product->height,
            'length' => $variant?->length ?? $product->length,
        ];

        $fakeItem = (object) [
            'id'         => $variant?->id ?? $product->id,
            'product_id' => $product->id,
            'quantity'   => 1,
            'unit_price' => $this->currentPrice,
            'product'    => $fakeProduct,
        ];

        $options = app(ShippingCalculator::class)->calculate(
            $digits,
            $this->currentPrice,
            collect([$fakeItem]),
        );

        if (empty($options)) {
            $this->shippingError   = 'Não foi possível calcular o frete para este CEP.';
            $this->shippingOptions = [];
        } else {
            $this->shippingError   = null;
            $this->shippingOptions = $options;
        }
    }

    public function render(): View
    {
        return view('livewire.shop.product-detail');
    }
}
