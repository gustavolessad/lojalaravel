<?php

namespace App\Services\Payment;

use App\Models\Setting;

/**
 * Gerenciador de gateways de pagamento.
 *
 * Registra implementações de PaymentGatewayInterface e expõe
 * o gateway ativo conforme a configuração.
 */
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

    /**
     * Retorna o gateway ativo (configurado via settings ou .env).
     */
    public function active(): PaymentGatewayInterface
    {
        $activeKey = Setting::get('payment_gateway', 'asaas');

        return $this->gateway($activeKey);
    }

    // ── Atalhos convenientes ──────────────────────────────────────────────

    public function isConfigured(): bool
    {
        return $this->active()->isConfigured();
    }

    public function createCharge(PaymentPayload $payload): PaymentResult
    {
        return $this->active()->createCharge($payload);
    }

    public function getLastError(): ?array
    {
        return $this->active()->getLastError();
    }
}
