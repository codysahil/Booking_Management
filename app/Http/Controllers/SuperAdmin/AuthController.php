<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // Only skip the form for someone already signed in as a super admin —
        // a tenant admin/manager may hold an active session under the same
        // guard, and sending them to the super-admin dashboard would just
        // 404 them there instead of ever showing this page.
        if (Auth::check() && Auth::user()->isSuperAdmin()) {
            return redirect()->route('super-admin.dashboard');
        }

        return view('super-admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // A tenant admin/customer session already active in this browser (or
        // request) may have bound a tenant into the container via
        // ResolveTenant — the super admin has no tenant at all, so that
        // binding must not scope this lookup or Auth::attempt will never
        // find them, even with the right credentials.
        app()->forgetInstance('currentTenantId');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (! Auth::user()->isSuperAdmin()) {
                Auth::logout();

                return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
            }

            return redirect()->intended(route('super-admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super-admin.login');
    }
}
