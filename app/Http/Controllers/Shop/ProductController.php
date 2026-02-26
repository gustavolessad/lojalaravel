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
            ->with(['categories'])
            ->firstOrFail();

        return view('shop.product.show', compact('product'));
    }
}
