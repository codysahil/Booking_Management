<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantCreationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A real login POST, not $this->actingAs() — this test also logs in as a
     * different (tenant admin) user later via a real login, and mixing
     * actingAs() with a subsequent real login in the same test doesn't
     * reliably switch the authenticated user for later requests.
     */
    private function loginAsSuperAdmin(): User
    {
        $superAdmin = User::factory()->create([
            'tenant_id' => null,
            'is_super_admin' => true,
            'is_active' => false,
            'password' => bcrypt('super-secret-1'),
        ]);

        $this->post(route('super-admin.login'), [
            'email' => $superAdmin->email,
            'password' => 'super-secret-1',
        ]);

        return $superAdmin;
    }

    public function test_super_admin_can_onboard_a_new_hostel_end_to_end()
    {
        $this->loginAsSuperAdmin();

        $response = $this->post(route('super-admin.tenants.store'), [
            'name' => 'Sunrise PG',
            'owner_name' => 'Ravi Kumar',
            'owner_email' => 'ravi@example.com',
            'owner_phone' => '9876500000',
            'trial_days' => 14,
            'admin_name' => 'Ravi Kumar',
            'admin_email' => 'ravi-admin@example.com',
            'admin_password' => 'a-strong-password',
        ]);

        $tenant = Tenant::where('name', 'Sunrise PG')->firstOrFail();
        $response->assertRedirect(route('super-admin.tenants.show', $tenant));

        $this->assertDatabaseHas('tenants', ['name' => 'Sunrise PG', 'status' => 'active']);

        $admin = User::where('email', 'ravi-admin@example.com')->firstOrFail();
        $this->assertSame($tenant->id, $admin->tenant_id);
        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertFalse($admin->isSuperAdmin());

        // The new admin can log in with the credentials just created.
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('a-strong-password', $admin->password));

        // ...and their own admin panel starts empty — not sharing any other tenant's data.
        $this->actingAs($admin);
        $dashboard = $this->get(route('admin.dashboard'));
        $dashboard->assertOk();
        $dashboard->assertViewHas('stats', fn ($stats) => $stats['branches'] === 0 && $stats['customers'] === 0);
    }

    public function test_a_new_tenants_admin_cannot_see_an_existing_tenants_branch()
    {
        $existingTenant = Tenant::factory()->create();
        app()->instance('currentTenantId', $existingTenant->id);
        Branch::create(['name' => 'Existing Branch', 'address' => 'Addr']);
        app()->instance('currentTenantId', null); // don't let this leak into the queries below

        $this->loginAsSuperAdmin();

        $this->post(route('super-admin.tenants.store'), [
            'name' => 'New Hostel',
            'trial_days' => 14,
            'admin_name' => 'New Owner',
            'admin_email' => 'new-owner@example.com',
            'admin_password' => 'a-strong-password',
        ]);

        $newAdmin = User::withoutGlobalScopes()->where('email', 'new-owner@example.com')->firstOrFail();
        $this->actingAs($newAdmin);

        $response = $this->get(route('admin.branches.index'));
        $response->assertOk();
        $response->assertDontSee('Existing Branch');
    }
}
