<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\CheckPlatformAdmin;
use App\Http\Middleware\CheckTenantRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToPriorityList(
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
            \Illuminate\Session\Middleware\StartSession::class
        );
        
        $middleware->prependToPriorityList(
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class
        );
        
        $middleware->alias([
            'platform-admin' => CheckPlatformAdmin::class,
            'tenant-role' => CheckTenantRole::class,
        ]);
        
        $middleware->redirectGuestsTo(fn (Request $request) => 
            tenant('id') ? route('tenant.login') : route('login')
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
