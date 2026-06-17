<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::livewire('/', 'shop.home')->name('shop.home');
    Route::livewire('/products', 'shop.products')->name('shop.products');
    Route::livewire('/products/{product}', 'shop.product-detail')->name('shop.product-detail');
    Route::livewire('/cart', 'shop.cart')->name('shop.cart');

    Route::middleware('guest')->group(function () {
        Route::livewire('/login', 'auth.login')->name('tenant.login');
        Route::livewire('/register', 'auth.register')->name('tenant.register');
    });

    // Tenant Backoffice
    Route::middleware(['auth', 'tenant-role:shop_admin,shop_staff'])->prefix('seller')->group(function () {
        Route::livewire('/dashboard', 'seller.dashboard')->name('seller.dashboard');
        Route::livewire('/products', 'seller.products.index')->name('seller.products.index');
        Route::livewire('/products/create', 'seller.products.create')->name('seller.products.create');
        Route::livewire('/products/{product}/edit', 'seller.products.edit')->name('seller.products.edit');
    });
});
