<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::get('academy-advertisement', 'AcademyAdvertisementController@edit')->name('admin.academy-advertisement.edit');
    Route::put('academy-advertisement', 'AcademyAdvertisementController@update')->name('admin.academy-advertisement.update');
    Route::crud('role', 'RoleCrudController');
    Route::crud('category', 'CategoryCrudController');
    Route::crud('brand', 'BrandCrudController');
    Route::crud('product', 'ProductCrudController');
    Route::get('product/{productId}/units', 'ProductCrudController@units');
    Route::post('product/{productId}/units', 'ProductCrudController@storeUnit');
    Route::crud('product-unit', 'ProductUnitCrudController');
    Route::crud('delivery-schedule', 'DeliveryScheduleCrudController');
    Route::crud('order', 'OrderCrudController');
}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
