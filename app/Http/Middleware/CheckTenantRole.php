<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        // Platform admins have super-access to all tenants
        if ($user->isPlatformAdmin()) {
            return $next($request);
        }

        $tenantId = tenant('id');

        if (! $tenantId) {
            abort(400, 'Not in a tenant context.');
        }

        // Check if user has any of the required roles in the current tenant
        foreach ($roles as $role) {
            if ($user->hasRoleInTenant($tenantId, $role)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized. Required tenant role: ' . implode(', ', $roles));
    }
}
