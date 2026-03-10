<?php

use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Account\AuthController;
use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\OrderController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Middleware\AuthenticateCustomer;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Autenticação do Cliente (rotas públicas)
|--------------------------------------------------------------------------
*/
Route::middleware('guest:customer')->group(function () {
    Route::get('/entrar', [AuthController::class, 'showLogin'])->name('account.login');
    Route::post('/entrar', [AuthController::class, 'login']);
    Route::get('/cadastro', [AuthController::class, 'showRegister'])->name('account.register');
    Route::post('/cadastro', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Painel do Cliente (rotas protegidas)
|--------------------------------------------------------------------------
*/
Route::prefix('minha-conta')->name('account.')->middleware(AuthenticateCustomer::class)->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/sair', [AuthController::class, 'logout'])->name('logout');

    // Pedidos
    Route::prefix('pedidos')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{orderNumber}', [OrderController::class, 'show'])->name('show');
    });

    // Perfil
    Route::get('/meus-dados', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/meus-dados', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/meus-dados/senha', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Trocas e Devoluções
    Route::get('/trocas-devolucoes', fn () => view('account.returns.index'))->name('returns.index');

    // Avaliações
    Route::get('/avaliacoes', fn () => view('account.reviews.index'))->name('reviews.index');

    // Endereços
    Route::prefix('enderecos')->name('addresses.')->group(function () {
        Route::get('/', [AddressController::class, 'index'])->name('index');
        Route::get('/novo', [AddressController::class, 'create'])->name('create');
        Route::post('/', [AddressController::class, 'store'])->name('store');
        Route::get('/{address}/editar', [AddressController::class, 'edit'])->name('edit');
        Route::put('/{address}', [AddressController::class, 'update'])->name('update');
        Route::delete('/{address}', [AddressController::class, 'destroy'])->name('destroy');
        Route::patch('/{address}/padrao', [AddressController::class, 'setDefault'])->name('set-default');
    });
});
