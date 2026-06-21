<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        \Livewire\Livewire::setUpdateRoute(function ($handle) {
            $isCentral = in_array(request()->getHost(), config('tenancy.central_domains', []));
            
            if ($isCentral) {
                return \Illuminate\Support\Facades\Route::post('/livewire/update', $handle)
                    ->middleware('web');
            }

            return \Illuminate\Support\Facades\Route::post('/livewire/update', $handle)
                ->middleware([
                    'web',
                    \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
                ]);
        });
    }
}
