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

        // Enforce middleware priority for Tenancy to prevent auth running before tenancy initialization
        $this->app->booted(function () {
            $router = $this->app['router'];
            $refRouter = new \ReflectionClass(get_class($router));
            if ($refRouter->hasProperty('middlewarePriority')) {
                $prop = $refRouter->getProperty('middlewarePriority');
                $prop->setAccessible(true);
                $priority = $prop->getValue($router);
                
                $tenancyMiddlewares = [
                    'Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains',
                    'Stancl\Tenancy\Middleware\InitializeTenancyByDomain',
                ];
                
                $priority = array_values(array_diff($priority, $tenancyMiddlewares));
                $priority = array_merge($tenancyMiddlewares, $priority);
                
                $prop->setValue($router, $priority);
            }
        });
    }
}
