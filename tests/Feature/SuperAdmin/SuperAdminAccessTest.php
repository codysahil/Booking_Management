<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Super Admin panel is a separate, platform-level actor — a tenant's own
 * staff must never be able to reach it, and it must not be revealed to exist.
 */
class SuperAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_tenants_own_admin_cannot_reach_the_super_admin_panel()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $this->actingAs($admin)->get(route('super-admin.dashboard'))->assertNotFound();
        $this->actingAs($admin)->get(route('super-admin.tenants.index'))->assertNotFound();
    }

    public function test_a_guest_visiting_the_super_admin_panel_is_sent_to_the_super_admin_login()
    {
        $this->get(route('super-admin.dashboard'))->assertRedirect(route('super-admin.login'));
    }

    public function test_a_super_admin_can_log_in_and_reach_the_dashboard()
    {
        $superAdmin = User::factory()->create([
            'tenant_id' => null,
            'is_super_admin' => true,
            'is_active' => false,
            'password' => bcrypt('super-secret-1'),
        ]);

        $response = $this->post(route('super-admin.login'), [
            'email' => $superAdmin->email,
            'password' => 'super-secret-1',
        ]);

        $response->assertRedirect(route('super-admin.dashboard'));
        $this->assertAuthenticatedAs($superAdmin);

        $this->get(route('super-admin.dashboard'))->assertOk();
    }

    /** A super admin's own inactive/no-tenant account must not let them into a tenant's admin panel. */
    public function test_a_super_admin_cannot_sign_in_to_a_tenants_admin_panel()
    {
        $superAdmin = User::factory()->create([
            'tenant_id' => null,
            'is_super_admin' => true,
            'is_active' => false,
            'password' => bcrypt('super-secret-1'),
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => $superAdmin->email,
            'password' => 'super-secret-1',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
