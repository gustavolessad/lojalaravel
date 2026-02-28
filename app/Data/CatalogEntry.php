<?php

namespace App\Data;

use App\Models\AttributeValue;
use App\Models\Product;

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
}
