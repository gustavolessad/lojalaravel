<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth('customer')->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->paginate(10);

        return view('account.orders.index', compact('orders'));
    }

    public function show(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('customer_id', auth('customer')->id())
            ->with('items.product', 'items.variant', 'payments', 'orderEvents')
            ->firstOrFail();

        return view('account.orders.show', compact('order'));
    }
}
