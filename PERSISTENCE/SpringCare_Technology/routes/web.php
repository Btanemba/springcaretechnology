<?php

use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('storefront.home');
Route::get('/shop', [StorefrontController::class, 'shop'])->name('storefront.shop');
Route::get('/shop/{slug}', [StorefrontController::class, 'show'])->name('storefront.product');
Route::post('/cart', [StorefrontController::class, 'addToCart'])->middleware('throttle:60,1')->name('storefront.cart.add');
Route::delete('/cart/{unit}', [StorefrontController::class, 'removeFromCart'])->whereNumber('unit')->name('storefront.cart.remove');
