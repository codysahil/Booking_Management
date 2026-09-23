<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::latest()->paginate(20);

        return view('super-admin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('super-admin.tenants.create');
    }

    /** Onboarding: creates the hostel's tenant account and its first admin login in one step. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'owner_email' => 'nullable|email|max:255',
            'owner_phone' => 'nullable|string|max:20',
            'trial_days' => 'required|integer|min:0|max:365',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        do {
            $slug = Str::slug($validated['name']) . '-' . Str::lower(Str::random(5));
        } while (Tenant::where('slug', $slug)->exists());

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'status' => Tenant::STATUS_ACTIVE,
            'owner_name' => $validated['owner_name'] ?? null,
            'owner_email' => $validated['owner_email'] ?? null,
            'owner_phone' => $validated['owner_phone'] ?? null,
            'trial_ends_at' => now()->addDays((int) $validated['trial_days']),
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => bcrypt($validated['admin_password']),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        return redirect()->route('super-admin.tenants.show', $tenant)->with('success', "{$tenant->name} is set up — share the admin login with the owner.");
    }

    public function show(Tenant $tenant)
    {
        $tenant->load('users');

        return view('super-admin.tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        return view('super-admin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => ['required', Rule::in([Tenant::STATUS_ACTIVE, Tenant::STATUS_SUSPENDED, Tenant::STATUS_CANCELLED])],
            'owner_name' => 'nullable|string|max:255',
            'owner_email' => 'nullable|email|max:255',
            'owner_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $tenant->update($validated);

        return redirect()->route('super-admin.tenants.show', $tenant)->with('success', 'Hostel account updated.');
    }
}
