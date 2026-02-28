<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\StorePage;

class StorePageController extends Controller
{
    public function show(string $slug): \Illuminate\View\View
    {
        $page = StorePage::where('slug', $slug)->firstOrFail();

        return view('shop.page.show', compact('page'));
    }
}
