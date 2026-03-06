<?php

namespace App\Catalog;

use App\Contracts\ProductScopeInterface;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CategoryScope implements ProductScopeInterface
{
    public function __construct(private readonly Category $category) {}

    public function applyToQuery(Builder $query): Builder
    {
        return $query->whereHas(
            'categories',
            fn ($q) => $q->whereIn('categories.id', $this->category->getAllDescendantIds())
        );
    }

    public function baseProductIds(): Collection
    {
        return Product::where('active', true)
            ->whereHas(
                'categories',
                fn ($q) => $q->whereIn('categories.id', $this->category->getAllDescendantIds())
            )
            ->pluck('id');
    }

    public function getCategory(): Category
    {
        return $this->category;
    }
}
