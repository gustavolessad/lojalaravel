<?php

namespace App\Services\Payment;

class PaymentResult
{
    public function __construct(
        public readonly string $transactionId,
        public readonly string $status,
        public readonly string $method,
        public readonly float $amount,
        public readonly ?string $pixCode = null,
        public readonly ?string $pixQrCode = null,
        public readonly ?string $boletoUrl = null,
        public readonly ?string $boletoBarCode = null,
    ) {}
}
