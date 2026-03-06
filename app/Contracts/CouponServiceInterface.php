<?php

namespace App\Contracts;

use App\Models\Marketing\Coupon;
use App\Models\Customer\Customer;
use App\Models\Sales\Order;
use Illuminate\Support\Collection;

interface CouponServiceInterface
{
    /**
     * Valida um cupom. Retorna array com 'coupon' e 'discount' se válido,
     * ou string com mensagem de erro se inválido.
     */
    public function validate(string $code, float $subtotal, ?Customer $customer = null, ?Collection $cartItems = null): array|string;

    public function calculateDiscount(Coupon $coupon, float $subtotal): float;

    public function recordUsage(Coupon $coupon, Order $order, ?Customer $customer, float $discountAmount): void;
}
