<?php

namespace App\Providers;

use App\Services\Payment\Drivers\AsaasGateway;
use App\Services\Payment\PaymentManager;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class, function ($app) {
            $manager = new PaymentManager();
            $manager->registerGateway('asaas', $app->make(AsaasGateway::class));
            return $manager;
        });
    }
}
