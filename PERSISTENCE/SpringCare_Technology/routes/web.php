<?php

use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\NewsletterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('storefront.home');
Route::get('/shop', [StorefrontController::class, 'shop'])->name('storefront.shop');
Route::get('/shop/{slug}', [StorefrontController::class, 'show'])->name('storefront.product');
Route::post('/cart', [StorefrontController::class, 'addToCart'])->middleware('throttle:60,1')->name('storefront.cart.add');
Route::delete('/cart/{unit}', [StorefrontController::class, 'removeFromCart'])->whereNumber('unit')->name('storefront.cart.remove');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->middleware('throttle:5,1')->name('newsletter.subscribe');
Route::get('/newsletter/confirm/{subscriber}', [NewsletterController::class, 'showConfirmation'])->middleware('signed')->name('newsletter.confirm');
Route::post('/newsletter/confirm/{subscriber}', [NewsletterController::class, 'confirm'])->middleware('signed')->name('newsletter.confirm.submit');
Route::get('/newsletter/unsubscribe/{subscriber}', [NewsletterController::class, 'showUnsubscribe'])->middleware('signed')->name('newsletter.unsubscribe');
Route::post('/newsletter/unsubscribe/{subscriber}', [NewsletterController::class, 'unsubscribe'])->middleware('signed')->name('newsletter.unsubscribe.submit');
