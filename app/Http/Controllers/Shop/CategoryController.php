<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function show(string $categoryPath): Response|\Illuminate\Contracts\View\View
    {
        $category = Category::findByPath($categoryPath);

        if (! $category || ! $category->active) {
            abort(404);
        }

        // First products for ItemList JSON-LD (server-side, for crawlers)
        $itemListProducts = Product::where('active', true)
            ->whereHas('categories', fn ($q) => $q->where('id', $category->id))
            ->with('media')
            ->orderBy('updated_at', 'desc')
            ->limit(30)
            ->get(['id', 'name', 'slug', 'price', 'sale_price', 'sale_start', 'sale_end']);

        return view('shop.category.show', compact('category', 'itemListProducts'));
    }
}
