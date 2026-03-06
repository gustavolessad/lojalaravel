<?php

namespace App\Data;

use App\Models\Catalog\AttributeValue;
use App\Models\Catalog\Product;

class CatalogEntry
{
    public function __construct(
        public readonly Product $product,
        public readonly string $displayName,
        public readonly string $url,
        public readonly float $price,
        public readonly ?float $originalPrice,
        public readonly ?string $imageUrl,
        public readonly bool $isNew,
        public readonly bool $isOnSale,
        public readonly bool $inStock = true,
        public readonly ?AttributeValue $expandedBy = null,
    ) {}

    /**
     * Cria um CatalogEntry a partir de um Product model.
     * Assume que 'variants' e 'media' já estão carregados (ou serão carregados lazy).
     */
    public static function fromProduct(Product $product): static
    {
        $cover = $product->getFirstMediaUrl('cover', 'thumb')
            ?: $product->getFirstMediaUrl('cover')
            ?: null;

        $price         = $product->getCurrentPrice();
        $originalPrice = $product->isOnSale() ? (float) $product->price : null;

        if ($product->isSimple()) {
            $inStock = ($product->stock ?? 0) > 0;
        } else {
            $inStock = $product->relationLoaded('variants')
                ? $product->variants->where('stock', '>', 0)->isNotEmpty()
                : $product->variants()->where('stock', '>', 0)->exists();
        }

        return new static(
            product:       $product,
            displayName:   $product->name,
            url:           route('product.show', ['slug' => $product->slug]),
            price:         $price,
            originalPrice: $originalPrice,
            imageUrl:      $cover,
            isNew:         (bool) $product->is_new,
            isOnSale:      $product->isOnSale(),
            inStock:       $inStock,
        );
    }
}
