<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function show(string $categoryPath): Response|\Illuminate\Contracts\View\View
    {
        $category = Category::findByPath($categoryPath);

        if (! $category || ! $category->active) {
            abort(404);
        }

        return view('shop.category.show', compact('category'));
    }
}
