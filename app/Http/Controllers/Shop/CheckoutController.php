<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View
    {
        return view('shop.checkout.index');
    }

    public function confirmation(string $orderNumber): View
    {
        $order = Order::with('items')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return view('shop.checkout.confirmation', compact('order'));
    }
}
