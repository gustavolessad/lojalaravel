<?php

namespace App\Services\Shipping;

use Illuminate\Support\Collection;

/**
 * DTO com todos os dados necessários para cotação de frete.
 */
class ShippingContext
{
    public function __construct(
        public readonly string $originZip,
        public readonly string $destZip,
        public readonly float $totalWeight,
        public readonly float $subtotal,
        public readonly ?Collection $cartItems = null,
    ) {}
}
