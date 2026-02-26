<?php

namespace App\Services\Shipping;

interface ShippingDriverInterface
{
    public function calculate(ShippingPayload $payload): ShippingResult;

    public function getLabel(): string;
}
