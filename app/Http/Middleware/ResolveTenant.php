<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Binds the current tenant into the container so every tenant-scoped model
 * query (see App\Models\Concerns\BelongsToTenant) is automatically restricted
 * to it. Two ways a tenant gets resolved, tried in order:
 *
 *  1. By subdomain — when config('app.tenant_domain') is set (the app owner
 *     has configured a real domain with wildcard DNS) and the request's host
 *     is {slug}.{tenant_domain}, the tenant comes from that slug. This is
 *     what gives each hostel its own URL.
 *  2. By the logged-in user's own tenant_id — the fallback whenever there's
 *     no tenant subdomain to read (no tenant_domain configured yet, the
 *     request hit the bare apex domain, or it's a test). This is the
 *     original, single-shared-URL behavior, so everything built before
 *     tenant_domain existed keeps working unchanged.
 *
 * Binds nothing for a guest request with no resolvable tenant, or for a
 * super admin (tenant_id null) — TenantScope no-ops when nothing is bound.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveFromSubdomain($request);

        if ($tenant) {
            app()->instance('currentTenantId', $tenant->id);
            $this->guardAgainstCrossTenantSession($request, $tenant->id);

            return $next($request);
        }

        $user = $request->user('web') ?? $request->user('customer');

        if ($user && ! empty($user->tenant_id)) {
            app()->instance('currentTenantId', $user->tenant_id);
        }

        return $next($request);
    }

    private function resolveFromSubdomain(Request $request): ?Tenant
    {
        $baseDomain = config('app.tenant_domain');

        if (! $baseDomain) {
            return null;
        }

        $host = $request->getHost();

        if ($host === $baseDomain || $host === "www.{$baseDomain}") {
            return null;
        }

        if (! str_ends_with($host, ".{$baseDomain}")) {
            return null;
        }

        $slug = substr($host, 0, -(strlen($baseDomain) + 1));

        $tenant = Tenant::where('slug', $slug)->first();

        abort_unless($tenant, 404, 'No hostel found at this address.');

        return $tenant;
    }

    /** A session for a different hostel must not silently keep working if presented on this one's URL. */
    private function guardAgainstCrossTenantSession(Request $request, int $tenantId): void
    {
        $webUser = $request->user('web');

        if ($webUser && ! $webUser->isSuperAdmin() && (int) $webUser->tenant_id !== $tenantId) {
            Auth::guard('web')->logout();
        }

        $customer = $request->user('customer');

        if ($customer && (int) $customer->tenant_id !== $tenantId) {
            Auth::guard('customer')->logout();
        }
    }
}
