<?php

namespace App\Services\Payment;

class PaymentManager
{
    protected array $gateways = [];

    public function registerGateway(string $key, PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$key] = $gateway;
    }

    public function gateway(string $key): PaymentGatewayInterface
    {
        if (! isset($this->gateways[$key])) {
            throw new \InvalidArgumentException("Gateway [{$key}] not registered.");
        }

        return $this->gateways[$key];
    }

    public function active(): PaymentGatewayInterface
    {
        $activeKey = config('payment.active_gateway', 'asaas');

        return $this->gateway($activeKey);
    }
}
