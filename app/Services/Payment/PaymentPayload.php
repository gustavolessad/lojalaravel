<?php

namespace App\Services\Payment;

class PaymentPayload
{
    public function __construct(
        public readonly string $method,
        public readonly float $amount,
        public readonly string $customerName,
        public readonly string $customerEmail,
        public readonly string $customerDocument,
        public readonly int $installments = 1,
        public readonly ?string $cardToken = null,
    ) {}
}
