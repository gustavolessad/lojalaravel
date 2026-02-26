<?php

namespace App\Services\Payment;

use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function createCharge(PaymentPayload $payload): PaymentResult;

    public function handleWebhook(Request $request): void;

    public function refund(string $transactionId, float $amount): bool;
}
