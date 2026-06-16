<?php

use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains', ['127.0.0.1', 'localhost']) as $domain) {
    Route::domain($domain)->group(function () {
        Route::livewire('/', 'central-home');
    });
}
