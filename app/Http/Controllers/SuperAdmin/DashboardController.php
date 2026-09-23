<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', Tenant::STATUS_ACTIVE)->count(),
            'on_trial' => Tenant::whereNotNull('trial_ends_at')->where('trial_ends_at', '>', now())->count(),
        ];

        $recentTenants = Tenant::latest()->limit(10)->get();

        return view('super-admin.dashboard', compact('stats', 'recentTenants'));
    }
}
