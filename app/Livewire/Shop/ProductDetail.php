<?php

namespace App\Livewire\Shop;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockNotification;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class ProductDetail extends Component
{
    public int $productId;

    /** [attribute_id => attribute_value_id] — sincronizado com URL para pré-seleção */
    #[Url(as: 'v', except: [])]
    public array $selected = [];

    public int $quantity = 1;

    // Formulário "Avise-me"
    public string $notifyEmail = '';
    public bool $notified = false;

    public function mount(Product $product): void
    {
        $this->productId = $product->id;

        // Auto-seleciona a primeira variante disponível (com estoque primeiro)
        if ($product->isVariable()) {
            $variants = ProductVariant::where('product_id', $product->id)
                ->where('active', true)
                ->with('attributeValues')
                ->orderBy('order')
                ->get();

            $first = $variants->where('stock', '>', 0)->first()
                ?? $variants->first();

            if ($first) {
                foreach ($first->attributeValues as $av) {
                    // Respeita valores pré-selecionados via URL (ex: vindo do catálogo expandido)
                    if (! array_key_exists($av->attribute_id, $this->selected)) {
                        $this->selected[$av->attribute_id] = $av->id;
                    }
                }
            }
        }
    }

    // ── Produto completo com todas as relações ────────────────────────────

    #[Computed]
    public function product(): Product
    {
        return Product::with([
            'variants' => fn ($q) => $q->where('active', true)
                ->with(['attributeValues.attribute', 'media'])
                ->orderBy('order'),
            'attributes' => fn ($q) => $q->with([
                'values' => fn ($vq) => $vq
                    ->whereHas('variants', fn ($varq) => $varq
                        ->where('product_id', $this->productId)
                        ->where('active', true)
                    )
                    ->orderBy('order'),
            ]),
            'media',
            'categories',
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
            $ids = $variant->attributeValues->pluck('id')->toArray();

            foreach ($selected as $valueId) {
                if (! in_array((int) $valueId, $ids)) {
                    return false;
                }
            }

            return count($ids) === count($selected);
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
        if ($this->currentVariant?->isOnSale()) {
            return (float) $this->currentVariant->price;
        }

        if (! $this->currentVariant && $this->product->isOnSale()) {
            return (float) $this->product->price;
        }

        return null;
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
            $cover = $this->currentVariant->getFirstMediaUrl('variant-cover');
            if ($cover !== '') {
                $images = collect([$cover]);
                foreach ($this->currentVariant->getMedia('variant-gallery') as $media) {
                    $images->push($media->getUrl());
                }
                return $images;
            }
        }

        $images = collect();
        $cover  = $this->product->getFirstMediaUrl('cover');

        if ($cover !== '') {
            $images->push($cover);
        }

        foreach ($this->product->getMedia('gallery') as $media) {
            $url = $media->getUrl();
            if ($url !== $cover) {
                $images->push($url);
            }
        }

        return $images->values();
    }

    // ── Valores disponíveis por atributo (considerando outras seleções) ───

    #[Computed]
    public function availableValueIds(): array
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
                fn ($v, $k) => $k != $attribute->id && $v,
                ARRAY_FILTER_USE_BOTH
            );

            $available = $variants
                ->filter(function (ProductVariant $variant) use ($otherSelected) {
                    $ids = $variant->attributeValues->pluck('id')->toArray();
                    foreach ($otherSelected as $valueId) {
                        if (! in_array((int) $valueId, $ids)) {
                            return false;
                        }
                    }
                    return true;
                })
                ->flatMap(fn ($v) => $v->attributeValues->pluck('id'))
                ->unique()
                ->toArray();

            $result[$attribute->id] = array_values($available);
        }

        return $result;
    }

    // ── Ações ─────────────────────────────────────────────────────────────

    public function selectValue(int $attrId, int $valueId): void
    {
        $this->selected[$attrId] = $valueId;
        $this->notified    = false;
        $this->notifyEmail = '';
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
            image:   $this->product->getFirstMediaUrl('cover') ?: '',
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

    public function render(): View
    {
        return view('livewire.shop.product-detail');
    }
}
