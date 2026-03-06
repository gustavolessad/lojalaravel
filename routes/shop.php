<?php

use App\Http\Controllers\Shop\BrandController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CategoryController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Shop\StorePageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Loja — Carrinho & Checkout
|--------------------------------------------------------------------------
*/
Route::get('/carrinho', [CartController::class, 'index'])->name('cart.index');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::get('/pedido/{orderNumber}/obrigado', [CheckoutController::class, 'confirmation'])->name('order.confirmation');
Route::get('/pedido/{orderNumber}/pagamento/status', [CheckoutController::class, 'paymentStatus'])->name('order.payment-status');

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
