<?php

use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains', ['127.0.0.1', 'localhost']) as $domain) {
    Route::domain($domain)->group(function () {
        Route::livewire('/', 'central-home');
        
        Route::middleware('guest')->group(function () {
            Route::livewire('/login', 'auth.login')->name('login');
            Route::livewire('/register', 'auth.register')->name('register');
        });
    });
}
