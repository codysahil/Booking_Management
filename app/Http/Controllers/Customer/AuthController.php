<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('customer.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'customer_code' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // A staff or different-tenant customer session already active in this
        // browser may have bound a tenant into the container via
        // ResolveTenant — that must not scope this lookup, or logging in here
        // could wrongly fail to find an otherwise-valid account.
        app()->forgetInstance('currentTenantId');

        // Try to login with customer_code and check if active
        if (Auth::guard('customer')->attempt([
            'customer_code' => $request->customer_code,
            'password' => $request->password,
            'is_active' => true,
        ])) {
            $request->session()->regenerate();
            return redirect()->intended(route('customer.dashboard'));
        }

        // Check if customer exists but is deactivated
        $customer = \App\Models\Customer::where('customer_code', $request->customer_code)->first();
        if ($customer && !$customer->is_active) {
            return back()->withErrors([
                'customer_code' => 'Your account has been deactivated. Please contact the hostel administration.',
            ])->onlyInput('customer_code');
        }

        return back()->withErrors([
            'customer_code' => 'The provided credentials do not match our records.',
        ])->onlyInput('customer_code');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }

    public function showForgotPasswordForm()
    {
        return view('customer.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        app()->forgetInstance('currentTenantId');

        $status = Password::broker('customers')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPasswordForm(string $token)
    {
        return view('customer.auth.reset-password', ['token' => $token, 'email' => request('email')]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        app()->forgetInstance('currentTenantId');

        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Customer $customer, string $password) {
                $customer->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('customer.login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
