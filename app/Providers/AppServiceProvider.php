<?php

namespace App\Providers;

use App\Contracts\CartServiceInterface;
use App\Contracts\CouponServiceInterface;
use App\Contracts\OrderServiceInterface;
use App\Services\Cart\CartService;
use App\Services\Order\CouponService;
use App\Services\Order\OrderService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CartServiceInterface::class, CartService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(CouponServiceInterface::class, CouponService::class);
    }

    public function boot(): void
    {
        //
    }
}
