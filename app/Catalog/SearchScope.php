<?php

namespace App\Catalog;

use App\Contracts\ProductScopeInterface;
use App\Models\Catalog\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SearchScope implements ProductScopeInterface
{
    public function __construct(private readonly string $query) {}

    public function applyToQuery(Builder $query): Builder
    {
        return $query->where('name', 'like', '%' . trim($this->query) . '%');
    }

    public function baseProductIds(): Collection
    {
        return Product::where('active', true)
            ->where('name', 'like', '%' . trim($this->query) . '%')
            ->pluck('id');
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}
