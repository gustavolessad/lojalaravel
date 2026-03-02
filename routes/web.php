<?php

use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Account\AuthController;
use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\OrderController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\BrandController;
use App\Http\Controllers\Shop\CategoryController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Shop\StorePageController;
use App\Http\Controllers\Webhook\AsaasWebhookController;
use App\Http\Middleware\AuthenticateCustomer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/busca', function () {
    return view('shop.search');
})->name('search');

/*
|--------------------------------------------------------------------------
| Webhooks (sem CSRF — excluído em bootstrap/app.php)
|--------------------------------------------------------------------------
*/
Route::post('/webhook/asaas', [AsaasWebhookController::class, 'handle'])->name('webhook.asaas');

/*
|--------------------------------------------------------------------------
| Loja — Carrinho
|--------------------------------------------------------------------------
*/
Route::get('/carrinho', [CartController::class, 'index'])->name('cart.index');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::get('/pedido/{orderNumber}/obrigado', [CheckoutController::class, 'confirmation'])->name('order.confirmation');
Route::get('/pedido/{orderNumber}/pagamento/status', [CheckoutController::class, 'paymentStatus'])->name('order.payment-status');

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

/*
|--------------------------------------------------------------------------
| Loja — Páginas Institucionais
|--------------------------------------------------------------------------
*/
Route::get('/pagina/{slug}', [StorePageController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('page.show');

/*
|--------------------------------------------------------------------------
| Loja — Catálogo  (wildcards por último para não conflitar)
|--------------------------------------------------------------------------
*/

// Produto: /nome-do-produto/p
Route::get('{slug}/p', [ProductController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('product.show');

// Listagem de marcas: /marcas
Route::get('/marcas', [BrandController::class, 'index'])->name('brand.index');

// Marca: /nike/m
Route::get('{slug}/m', [BrandController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('brand.show');

// Categorias: /eletronicos/c, /eletronicos/smartphones/c, ...
Route::get('{categoryPath}/c', [CategoryController::class, 'show'])
    ->where('categoryPath', '[a-zA-Z0-9\-\/]+')
    ->name('category.show');
