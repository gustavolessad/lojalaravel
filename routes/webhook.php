<?php

use App\Http\Controllers\Webhook\AsaasWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhooks (sem CSRF — excluído em bootstrap/app.php)
|--------------------------------------------------------------------------
*/
Route::post('/webhook/asaas', [AsaasWebhookController::class, 'handle'])->name('webhook.asaas');
