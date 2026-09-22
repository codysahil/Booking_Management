<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only active staff accounts (owner/admin or manager) may use the admin panel.
 * Pass a role to restrict further, e.g. `admin:admin` for owner-only screens.
 */
class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isStaff()) {
            Auth::guard('web')->logout();

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Your account does not have access to the admin panel.']);
        }

        if ($role && $user->role !== $role) {
            abort(403, 'Only the account owner can access this page.');
        }

        return $next($request);
    }
}
