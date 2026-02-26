<?php

namespace App\Services\Shipping;

class ShippingPayload
{
    public function __construct(
        public readonly string $cepOrigin,
        public readonly string $cepDestination,
        public readonly float $weight,
        public readonly float $length,
        public readonly float $width,
        public readonly float $height,
        public readonly float $cartTotal,
    ) {}
}
