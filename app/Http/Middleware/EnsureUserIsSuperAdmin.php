<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only the platform owner (is_super_admin = true) may use the Super Admin
 * panel. 404s rather than 403s for a non-super-admin, so the panel's
 * existence isn't revealed to a tenant's own staff.
 */
class EnsureUserIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isSuperAdmin()) {
            abort(404);
        }

        return $next($request);
    }
}
