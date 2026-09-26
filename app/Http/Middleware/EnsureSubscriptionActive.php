<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Read-only lockout: a tenant whose subscription isn't currently writable may
 * still GET/HEAD (view all their data) but every write is blocked, with a
 * message telling them to renew. Every non-GET admin route in this codebase
 * is already a create/update/delete action, so this coarse method-based
 * split needs no per-route allowlist.
 *
 * A super admin is exempt (they have no tenant to check). A staff user whose
 * tenant somehow has no subscription row at all is treated as writable —
 * that shouldn't happen once every tenant gets one at creation/backfill, but
 * failing open here rather than locking out on a data gap is the safer
 * default for a paying customer's account.
 */
class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), ['GET', 'HEAD'])) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user instanceof User || $user->isSuperAdmin()) {
            return $next($request);
        }

        $subscription = $user->tenant?->subscription;

        if ($subscription && ! $subscription->isWritable()) {
            $message = 'Your subscription has expired — contact your account manager to renew.';

            return $request->expectsJson()
                ? response()->json(['message' => $message], 402)
                : back()->with('error', $message);
        }

        return $next($request);
    }
}
