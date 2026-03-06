<?php

namespace App\Services\Shipping;

class ShippingManager
{
    /** @var array<string, ShippingDriverInterface> */
    protected array $drivers = [];

    public function registerDriver(string $key, ShippingDriverInterface $driver): void
    {
        $this->drivers[$key] = $driver;
    }

    /**
     * Coleta cotações de todos os drivers configurados e retorna mescladas.
     *
     * @return array<int, array{name: string, company: string, price: float, days: int}>
     */
    public function quoteAll(ShippingContext $context): array
    {
        $options = [];

        foreach ($this->drivers as $driver) {
            if ($driver->isConfigured()) {
                $options = array_merge($options, $driver->quote($context));
            }
        }

        return $options;
    }

    public function driver(string $key): ShippingDriverInterface
    {
        if (! isset($this->drivers[$key])) {
            throw new \InvalidArgumentException("Shipping driver [{$key}] not registered.");
        }

        return $this->drivers[$key];
    }

    /** @return array<string, ShippingDriverInterface> */
    public function getDrivers(): array
    {
        return $this->drivers;
    }
}
