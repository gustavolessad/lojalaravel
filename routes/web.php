<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SEO — Sitemap e robots.txt
|--------------------------------------------------------------------------
*/
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    $content = "User-agent: *\n";
    $content .= "Disallow: /carrinho\n";
    $content .= "Disallow: /checkout\n";
    $content .= "Disallow: /minha-conta\n";
    $content .= "Disallow: /admin\n";
    $content .= "Disallow: /entrar\n";
    $content .= "Disallow: /cadastro\n";
    $content .= "Disallow: /busca\n";
    $content .= "Disallow: /pedido\n";
    $content .= "Disallow: /livewire\n";
    $content .= "\n";
    $content .= "Sitemap: " . url('/sitemap.xml') . "\n";

    return response($content, 200)->header('Content-Type', 'text/plain');
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/busca', function () {
    return view('shop.search');
})->name('search');
