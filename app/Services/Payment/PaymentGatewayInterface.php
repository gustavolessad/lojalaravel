<?php

namespace App\Services\Payment;

/**
 * Contrato para gateways de pagamento.
 *
 * Implementações: Drivers/AsaasGateway (atual), futuramente Stripe, PagSeguro...
 * Todos os métodos recebem PaymentPayload com dados brutos (sem depender de
 * modelos Eloquent), permitindo cobrar o cartão ANTES de criar o pedido.
 */
interface PaymentGatewayInterface
{
    /**
     * Verifica se o gateway está devidamente configurado (token, chaves, etc.).
     */
    public function isConfigured(): bool;

    /**
     * Processa a cobrança (PIX ou cartão) com base em $payload->method.
     *
     * @throws \RuntimeException  se o gateway não estiver configurado
     */
    public function createCharge(PaymentPayload $payload): PaymentResult;

    /**
     * Retorna o último erro retornado pelo gateway (após falha em createCharge).
     */
    public function getLastError(): ?array;
}
