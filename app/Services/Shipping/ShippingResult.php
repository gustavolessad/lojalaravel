<?php

namespace App\Services\Shipping;

class ShippingResult
{
    public function __construct(
        public readonly string $driver,
        public readonly string $label,
        public readonly float $cost,
        public readonly int $days,
        public readonly bool $isFree = false,
    ) {}
}
