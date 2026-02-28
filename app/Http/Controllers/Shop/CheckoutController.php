<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
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

    /**
     * Retorna o status de pagamento do pedido (usado para polling na página de confirmação).
     */
    public function paymentStatus(string $orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->select(['id', 'payment_status', 'paid_at'])
            ->firstOrFail();

        return response()->json([
            'payment_status' => $order->payment_status,
            'paid'           => $order->payment_status === 'paid',
        ]);
    }
}
