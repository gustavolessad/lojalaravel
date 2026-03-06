<?php

namespace App\Catalog;

use App\Contracts\ProductScopeInterface;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BrandScope implements ProductScopeInterface
{
    public function __construct(private readonly Brand $brand) {}

    public function applyToQuery(Builder $query): Builder
    {
        return $query->where('brand_id', $this->brand->id);
    }

    public function baseProductIds(): Collection
    {
        return Product::where('active', true)
            ->where('brand_id', $this->brand->id)
            ->pluck('id');
    }

    public function getBrand(): Brand
    {
        return $this->brand;
    }
}
