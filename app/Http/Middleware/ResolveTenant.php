<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Binds the logged-in user's tenant into the container so every tenant-scoped
 * model query (see App\Models\Concerns\BelongsToTenant) is automatically
 * restricted to it. Checks both the staff and customer guards, since both
 * carry a tenant_id. Binds nothing for a guest request or a super admin
 * (tenant_id null) — TenantScope no-ops when nothing is bound.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web') ?? $request->user('customer');

        if ($user && ! empty($user->tenant_id)) {
            app()->instance('currentTenantId', $user->tenant_id);
        }

        return $next($request);
    }
}
