<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;

class CartController extends Controller
{
    public function index()
    {
        return view('shop.cart.index');
    }
}
