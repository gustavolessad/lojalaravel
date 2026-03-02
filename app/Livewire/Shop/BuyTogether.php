<?php

namespace App\Livewire\Shop;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class BuyTogether extends Component
{
    public int $mainProductId;

    /** Variante atual do produto principal (sincronizada via evento do ProductDetail) */
    public ?int $mainVariantId = null;

    /** Preço atual do produto principal (sincronizado via evento do ProductDetail) */
    public float $mainProductPrice = 0;

    /** IDs dos produtos relacionados que o usuário manteve selecionados */
    public array $selectedIds = [];

    /**
     * Seleções de variante para cada produto relacionado.
     * Chaves SEMPRE strings para evitar ambiguidade na serialização JSON do Livewire.
     * Formato: ['<productId>' => ['<attrSlug>' => '<valueSlug>', ...], ...]
     */
    public array $choices = [];

    public function mount(Product $product): void
    {
        $this->mainProductId    = $product->id;
        $this->mainProductPrice = $product->getCurrentPrice();

        // Resolve variante inicial do produto principal (mesmo algoritmo do ProductDetail)
        if ($product->isVariable()) {
            $variants = $product->variants()
                ->where('active', true)
                ->with('attributeValues.attribute')
                ->orderBy('order')
                ->get();

            $first = $variants->where('stock', '>', 0)->first() ?? $variants->first();

            if ($first) {
                $this->mainVariantId    = $first->id;
                $this->mainProductPrice = $first->getEffectivePrice();
            }
        }

        // Pré-seleciona todos os relacionados e auto-escolhe a primeira variante válida
        $related = $product->compraJunto()
            ->with([
                'variants' => fn ($q) => $q->where('active', true)
                    ->with('attributeValues.attribute')
                    ->orderBy('order'),
            ])
            ->get();

        $this->selectedIds = $related->pluck('id')->toArray();

        $choices = [];
        foreach ($related as $rel) {
            if ($rel->isVariable()) {
                $first = $rel->variants->where('stock', '>', 0)->first() ?? $rel->variants->first();
                if ($first) {
                    foreach ($first->attributeValues as $av) {
                        $choices[(string) $rel->id][$av->attribute->slug] = $av->slug;
                    }
                }
            }
        }
        $this->choices = $choices;
    }

    // ── Evento: variante do produto principal foi alterada ─────────────────

    #[On('main-variant-updated')]
    public function onMainVariantUpdated(?int $variantId, float $price): void
    {
        $this->mainVariantId    = $variantId;
        $this->mainProductPrice = $price;
    }

    // ── Produto principal ──────────────────────────────────────────────────

    #[Computed]
    public function mainProduct(): Product
    {
        return Product::with('media')->find($this->mainProductId);
    }

    // ── Produtos relacionados com todas as relações necessárias ───────────

    #[Computed]
    public function relatedProducts(): Collection
    {
        return Product::find($this->mainProductId)
            ->compraJunto()
            ->where('products.id', '!=', $this->mainProductId) // nunca inclui o próprio produto
            ->with([
                'variants' => fn ($q) => $q->where('active', true)
                    ->with(['attributeValues.attribute', 'media'])
                    ->orderBy('order'),
                'attributes' => fn ($q) => $q->with([
                    'values' => fn ($vq) => $vq->orderBy('order'),
                ]),
                'media',
            ])
            ->get();
    }

    // ── Estado de variantes por produto (disponível / sem estoque) ─────────
    //
    // Retorna: ['<productId>' => ['<attrSlug>' => ['available' => [...], 'outOfStock' => [...]]]]
    // Mesmo algoritmo de availableValueSlugs + outOfStockValueSlugs do ProductDetail,
    // mas aplicado a cada produto relacionado individualmente.

    #[Computed]
    public function variantState(): array
    {
        $result = [];

        foreach ($this->relatedProducts as $product) {
            if ($product->isSimple()) {
                continue;
            }

            $pid      = (string) $product->id;
            $variants = $product->variants;
            $current  = $this->choices[$pid] ?? [];

            $result[$pid] = [];

            foreach ($product->attributes as $attribute) {
                // Slugs que EXISTEM de fato nas variants deste produto para este atributo.
                // Fonte: as próprias variants carregadas (não $attr->values, que pode ter
                // valores de outros produtos porque o eager loading não é scoped por produto).
                $existsForAttr = $variants
                    ->flatMap(fn ($v) => $v->attributeValues
                        ->filter(fn ($av) => $av->attribute->slug === $attribute->slug)
                        ->pluck('slug')
                    )
                    ->unique()
                    ->values()
                    ->toArray();

                // Seleções dos OUTROS atributos deste produto
                $otherSelected = array_filter(
                    $current,
                    fn ($v, $k) => $k !== $attribute->slug && $v,
                    ARRAY_FILTER_USE_BOTH
                );

                // Slugs disponíveis (variantes compatíveis com outras seleções)
                $available = $variants
                    ->filter(function ($variant) use ($otherSelected) {
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
                    ->values()
                    ->toArray();

                // Slugs sem estoque (context-aware, igual ao ProductDetail)
                // Filtra apenas por valores que existem neste produto
                $outOfStock = $attribute->values
                    ->filter(fn ($value) => in_array($value->slug, $existsForAttr))
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

                $result[$pid][$attribute->slug] = [
                    'exists'      => $existsForAttr, // só valores reais deste produto
                    'available'   => $available,
                    'outOfStock'  => $outOfStock,
                ];
            }
        }

        return $result;
    }

    // ── Resolução de variante para um produto relacionado ─────────────────

    private function resolveVariant(Product $product): ?ProductVariant
    {
        if ($product->isSimple()) {
            return null;
        }

        $choices = array_filter($this->choices[(string) $product->id] ?? []);

        if (empty($choices)) {
            return null;
        }

        return $product->variants->first(function ($variant) use ($choices) {
            $slugs = $variant->attributeValues->pluck('slug')->toArray();

            foreach ($choices as $valueSlug) {
                if (! in_array($valueSlug, $slugs)) {
                    return false;
                }
            }

            return count($slugs) === count($choices);
        });
    }

    // ── Preço efetivo de um produto relacionado dado as escolhas atuais ────

    public function getProductPrice(int $productId): float
    {
        $product = $this->relatedProducts->firstWhere('id', $productId);

        if (! $product) {
            return 0.0;
        }

        if ($product->isSimple()) {
            return $product->getCurrentPrice();
        }

        $variant = $this->resolveVariant($product);

        return $variant
            ? $variant->getEffectivePrice()
            : $product->getCurrentPrice();
    }

    // ── Preço original (sem desconto) — null quando não há promoção ───────

    public function getProductOriginalPrice(int $productId): ?float
    {
        $product = $this->relatedProducts->firstWhere('id', $productId);

        if (! $product) {
            return null;
        }

        if ($product->isSimple()) {
            return $product->isOnSale() ? (float) $product->price : null;
        }

        $variant = $this->resolveVariant($product);

        if ($variant) {
            if ($variant->price !== null) {
                return $variant->isOnSale() ? (float) $variant->price : null;
            }

            return $product->isOnSale() ? (float) $product->price : null;
        }

        return $product->isOnSale() ? (float) $product->price : null;
    }

    // ── Verifica se o produto/variante atual tem estoque ──────────────────

    public function isProductInStock(int $productId): bool
    {
        $product = $this->relatedProducts->firstWhere('id', $productId);

        if (! $product) {
            return false;
        }

        if ($product->isSimple()) {
            return ($product->stock ?? 0) > 0;
        }

        $variant = $this->resolveVariant($product);

        if ($variant) {
            return ($variant->stock ?? 0) > 0;
        }

        return $product->variants->where('stock', '>', 0)->isNotEmpty();
    }

    // ── Total dos itens selecionados ───────────────────────────────────────

    #[Computed]
    public function total(): float
    {
        $sum = $this->mainProductPrice;

        foreach ($this->selectedIds as $id) {
            $sum += $this->getProductPrice((int) $id);
        }

        return round($sum, 2);
    }

    // ── Ações ──────────────────────────────────────────────────────────────

    public function toggleProduct(int $productId): void
    {
        if (in_array($productId, $this->selectedIds)) {
            $this->selectedIds = array_values(
                array_filter($this->selectedIds, fn ($id) => $id !== $productId)
            );
        } else {
            // Só adiciona se tiver estoque
            if ($this->isProductInStock($productId)) {
                $this->selectedIds[] = $productId;
            }
        }
    }

    public function selectValue(int $productId, string $attrSlug, string $valueSlug): void
    {
        $pid = (string) $productId;

        // Garante que a estrutura existe antes de manipular
        $current = $this->choices[$pid] ?? [];
        $current[$attrSlug] = $valueSlug;

        // Cascade: se a combinação ficou inválida, ajusta para melhor variante com o valor clicado
        $product = $this->relatedProducts->firstWhere('id', $productId);

        if ($product) {
            $currentSlugs = array_values(array_filter($current));

            $isValid = $product->variants->some(function ($v) use ($currentSlugs) {
                $slugs = $v->attributeValues->pluck('slug')->toArray();
                foreach ($currentSlugs as $slug) {
                    if (! in_array($slug, $slugs)) {
                        return false;
                    }
                }
                return true;
            });

            if (! $isValid) {
                $best = $product->variants
                    ->filter(fn ($v) => $v->attributeValues->pluck('slug')->contains($valueSlug))
                    ->sortByDesc('stock')
                    ->first();

                if ($best) {
                    $current = [];
                    foreach ($best->attributeValues as $av) {
                        $current[$av->attribute->slug] = $av->slug;
                    }
                }
            }
        }

        // Reassign o array inteiro para o Livewire detectar a mudança
        $choices       = $this->choices;
        $choices[$pid] = $current;
        $this->choices = $choices;

        // Se a nova seleção ficou sem estoque, remove da lista de selecionados
        if (in_array($productId, $this->selectedIds) && ! $this->isProductInStock($productId)) {
            $this->selectedIds = array_values(
                array_filter($this->selectedIds, fn ($id) => $id !== $productId)
            );
        }
    }

    public function addAllToCart(): void
    {
        $cart = app(CartService::class);

        // Produto principal
        if ($this->mainProduct->isVariable() && ! $this->mainVariantId) {
            session()->flash('cart-error', 'Selecione as opções do produto principal antes de adicionar.');
            return;
        }

        $cart->add($this->mainProductId, 1, $this->mainVariantId);

        // Produtos relacionados selecionados
        $adicionados = 0;

        foreach ($this->selectedIds as $productId) {
            $product = $this->relatedProducts->firstWhere('id', (int) $productId);

            if (! $product) {
                continue;
            }

            // Garante que tem estoque antes de adicionar
            if (! $this->isProductInStock((int) $productId)) {
                continue;
            }

            $variantId = null;

            if ($product->isVariable()) {
                $variant = $this->resolveVariant($product);

                if (! $variant) {
                    // Fallback: primeira variante ativa com estoque
                    $variant = $product->variants->where('stock', '>', 0)->first();
                }

                if (! $variant) {
                    continue;
                }

                $variantId = $variant->id;
            }

            $cart->add((int) $productId, 1, $variantId);
            $adicionados++;
        }

        $this->dispatch('cart-updated');

        $totalItens = $adicionados + 1;

        $this->dispatch('product-added-to-cart',
            name:    "Compre Junto ({$totalItens} itens)",
            image:   $this->mainProduct->getFirstMediaUrl('cover', 'thumb') ?: '',
            variant: '',
        );
    }

    public function render()
    {
        return view('livewire.shop.buy-together');
    }
}
