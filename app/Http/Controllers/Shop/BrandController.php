<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Brand;

class BrandController extends Controller
{
    public function index(): \Illuminate\Contracts\View\View
    {
        $brands = Brand::where('active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return view('shop.brand.index', compact('brands'));
    }

    public function show(string $slug): \Illuminate\Contracts\View\View
    {
        $brand = Brand::where('slug', $slug)->firstOrFail();

        if (! $brand->active) {
            abort(404);
        }

        return view('shop.brand.show', compact('brand'));
    }
}
