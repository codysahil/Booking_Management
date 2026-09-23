<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * When TENANT_DOMAIN is configured, each hostel's own {slug}.{domain} URL
 * must resolve to that hostel's data — and a session for a different hostel
 * must not keep working if presented on this one's URL. Left unconfigured
 * (the default, and every other test in the suite), behavior is unchanged
 * from before subdomains existed.
 */
class TenantSubdomainTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.tenant_domain' => 'example.test']);
    }

    public function test_a_hostels_own_subdomain_shows_that_hostels_branding()
    {
        $tenant = Tenant::factory()->create(['name' => 'Sunrise PG']);
        app()->instance('currentTenantId', $tenant->id);
        \App\Models\Setting::putMany(['hostel_name' => 'Sunrise PG Official']);
        app()->forgetInstance('currentTenantId');

        $response = $this->get("http://{$tenant->slug}.example.test/admin/login");

        $response->assertOk();
        $response->assertSee('Sunrise PG Official');
    }

    public function test_an_unknown_subdomain_of_the_configured_base_domain_404s()
    {
        $response = $this->get('http://does-not-exist.example.test/admin/login');

        $response->assertNotFound();
    }

    public function test_the_bare_base_domain_does_not_404_and_falls_back_to_normal_resolution()
    {
        $response = $this->get('http://example.test/admin/login');

        $response->assertOk();
    }

    public function test_a_session_for_one_hostel_is_logged_out_if_presented_on_another_hostels_subdomain()
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $adminA = User::factory()->create(['tenant_id' => $tenantA->id, 'role' => User::ROLE_ADMIN, 'is_active' => true]);
        $this->actingAs($adminA);

        // Same session, but the request now arrives via tenant B's own URL.
        $response = $this->get("http://{$tenantB->slug}.example.test/admin/dashboard");

        // Logged out and sent to tenant B's own login page (not a generic one) —
        // adminA's session never gets to see tenant B's dashboard.
        $response->assertStatus(302);
        $this->assertStringContainsString("{$tenantB->slug}.example.test/admin/login", $response->headers->get('Location'));
        $this->assertGuest();
    }

    public function test_a_hostels_own_admin_can_use_their_own_subdomain_normally()
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_ADMIN, 'is_active' => true]);
        $this->actingAs($admin);

        $response = $this->get("http://{$tenant->slug}.example.test/admin/dashboard");

        $response->assertOk();
    }
}
