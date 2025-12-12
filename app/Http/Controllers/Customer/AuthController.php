<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
