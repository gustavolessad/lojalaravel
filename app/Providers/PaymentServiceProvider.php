<?php

namespace App\Providers;

use App\Services\Payment\Drivers\AsaasGateway;
use App\Services\Payment\Drivers\PagBankGateway;
use App\Services\Payment\PaymentManager;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class, function ($app) {
            $manager = new PaymentManager();
            $manager->registerGateway('asaas', $app->make(AsaasGateway::class));
            $manager->registerGateway('pagbank', $app->make(PagBankGateway::class));
            return $manager;
        });
    }
}
