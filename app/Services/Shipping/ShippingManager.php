<?php

namespace App\Services\Shipping;

use Illuminate\Support\Collection;

class ShippingManager
{
    protected array $drivers = [];

    public function registerDriver(string $key, ShippingDriverInterface $driver): void
    {
        $this->drivers[$key] = $driver;
    }

    public function calculate(ShippingPayload $payload): Collection
    {
        $results = collect();

        foreach ($this->drivers as $driver) {
            $results->push($driver->calculate($payload));
        }

        return $results->sortBy('cost');
    }

    public function driver(string $key): ShippingDriverInterface
    {
        return $this->drivers[$key];
    }
}
