<?php

use App\Http\Controllers\Webhook\AsaasWebhookController;
use App\Http\Controllers\Webhook\PagBankWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhooks (sem CSRF — excluído em bootstrap/app.php)
|--------------------------------------------------------------------------
*/
Route::post('/webhook/asaas', [AsaasWebhookController::class, 'handle'])->name('webhook.asaas');
Route::post('/webhook/pagbank', [PagBankWebhookController::class, 'handle'])->name('webhook.pagbank');
