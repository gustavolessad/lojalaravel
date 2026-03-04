<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('active', true)
            ->with([
                'categories',
                'variants' => fn ($q) => $q->where('active', true)
                    ->with(['attributeValues.attribute', 'variantGroup.media', 'media']),
            ])
            ->firstOrFail();

        return view('shop.product.show', compact('product'));
    }
}
