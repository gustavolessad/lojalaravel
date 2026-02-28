<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\AnalyticsService;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('active', true)
            ->with(['categories'])
            ->firstOrFail();

        app(AnalyticsService::class)->track('product_viewed', [
            'product_id' => $product->id,
        ]);

        return view('shop.product.show', compact('product'));
    }
}
