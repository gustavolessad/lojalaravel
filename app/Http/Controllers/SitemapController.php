<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\StorePage;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $products = Product::where('active', true)
            ->with([
                'media',
                'variants' => function ($q) {
                    $q->where('active', true)
                        ->with(['attributeValues.attribute', 'variantGroup.media', 'media']);
                },
            ])
            ->select('id', 'slug', 'name', 'type', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->get();

        $categories = Category::with('parent.parent.parent')
            ->select('id', 'slug', 'parent_id', 'updated_at')
            ->get();

        $brands = Brand::where('active', true)
            ->select('slug', 'updated_at')
            ->get();

        $pages = StorePage::select('slug', 'updated_at')->get();

        $xml = view('sitemap', compact('products', 'categories', 'brands', 'pages'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
