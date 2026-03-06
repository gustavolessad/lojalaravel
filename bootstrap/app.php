<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        \App\Providers\EventServiceProvider::class,
        \App\Providers\PaymentServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/shop.php'));

            Route::middleware('web')
                ->group(base_path('routes/account.php'));

            Route::middleware('web')
                ->group(base_path('routes/webhook.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: ['webhook/*']);
        $middleware->web(append: [
            \App\Http\Middleware\HandleRedirects::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
