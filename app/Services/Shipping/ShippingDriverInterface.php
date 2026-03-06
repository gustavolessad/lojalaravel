<?php

namespace App\Services\Shipping;

interface ShippingDriverInterface
{
    /**
     * Retorna array de opções de frete.
     *
     * @return array<int, array{name: string, company: string, price: float, days: int, service_id?: int|null}>
     */
    public function quote(ShippingContext $context): array;

    /** Verifica se o driver está configurado e pode cotar. */
    public function isConfigured(): bool;

    /** Label amigável para exibição no admin. */
    public function getLabel(): string;
}
