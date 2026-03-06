<?php

namespace App\Contracts;

use App\Models\Cart\Cart;
use App\Models\Customer\Customer;
use App\Models\Sales\Order;

interface OrderServiceInterface
{
    public function createFromCart(
        Cart $cart,
        array $address,
        array $shipping,
        string $paymentMethod,
        ?Customer $customer = null,
        array $guest = [],
        ?string $notes = null,
        float $pixDiscount = 0.0,
        float $cardInterest = 0.0,
        ?string $orderNumber = null,
    ): Order;

    public function markAsPaid(Order $order, string $paymentId, array $paymentData = []): void;

    public function cancelAndRestoreStock(Order $order, string $reason = 'Cancelado automaticamente'): void;

    public function logEvent(Order $order, string $type, string $description, array $metadata = []): void;

    public function attachPaymentData(Order $order, string $paymentId, array $data): void;

    public function recordFailedPayment(Order $order, string $method, float $amount, ?string $errorMessage = null): void;
}
