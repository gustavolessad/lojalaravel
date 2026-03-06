<?php

namespace App\Providers;

use App\Contracts\CartServiceInterface;
use App\Contracts\CouponServiceInterface;
use App\Contracts\OrderServiceInterface;
use App\Services\Cart\CartService;
use App\Services\Order\CouponService;
use App\Services\Order\OrderService;
use App\Services\Shipping\ManualCarrierDriver;
use App\Services\Shipping\MelhorEnvioDriver;
use App\Services\Shipping\ShippingManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CartServiceInterface::class, CartService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(CouponServiceInterface::class, CouponService::class);

        $this->app->singleton(ShippingManager::class, function ($app) {
            $manager = new ShippingManager();
            $manager->registerDriver('melhor_envio', $app->make(MelhorEnvioDriver::class));
            $manager->registerDriver('manual', $app->make(ManualCarrierDriver::class));
            return $manager;
        });
    }

    public function boot(): void
    {
        //
    }
}
