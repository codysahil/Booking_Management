<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;

/**
 * The core guarantee of multi-tenancy: a hostel's admin can never see or act on
 * another hostel's data, whether through a listing page or by guessing/tampering
 * with an id in a URL. This is the single most important test in the app.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    private function makeTenantWithData(string $name): array
    {
        $tenant = Tenant::factory()->create(['name' => $name]);
        $this->bindTenant($tenant);

        $branch = Branch::create(['name' => "{$name} Branch", 'address' => 'Addr']);
        $room = $branch->rooms()->create(['room_number' => '101', 'capacity' => 2, 'type' => 'AC', 'gender_allowed' => 'Female']);
        $bed = $room->beds()->create(['bed_number' => '101-A', 'monthly_rent' => 5000, 'status' => 'occupied']);

        $customer = Customer::create([
            'customer_code' => strtoupper($name) . '-0001',
            'name' => "{$name} Resident",
            'phone' => '9000000000',
            'password' => bcrypt('password'),
            'dob' => '2000-01-01',
            'address' => 'Addr',
            'guardian_phone' => '9000000001',
        ]);

        $booking = $customer->bookings()->create([
            'booking_reference' => 'BK-' . strtoupper($name),
            'bed_id' => $bed->id,
            'check_in_date' => now(),
            'status' => 'active',
            'advance_paid' => 5000,
        ]);

        $due = $customer->dues()->create([
            'due_type' => 'fine',
            'title' => 'Test fine',
            'amount' => 100,
            'status' => 'pending',
            'due_date' => now(),
        ]);

        return compact('tenant', 'branch', 'room', 'bed', 'customer', 'booking', 'due');
    }

    public function test_a_tenants_admin_cannot_see_another_tenants_customers_or_branches_in_listings()
    {
        $tenantA = $this->makeTenantWithData('Alpha');
        $tenantB = $this->makeTenantWithData('Beta');

        $admin = User::factory()->create(['tenant_id' => $tenantB['tenant']->id, 'role' => User::ROLE_ADMIN, 'is_active' => true]);
        $this->actingAs($admin);

        $customersResponse = $this->get(route('admin.customers.index'));
        $customersResponse->assertStatus(200);
        $customersResponse->assertSee($tenantB['customer']->name);
        $customersResponse->assertDontSee($tenantA['customer']->name);

        $branchesResponse = $this->get(route('admin.branches.index'));
        $branchesResponse->assertStatus(200);
        $branchesResponse->assertSee($tenantB['branch']->name);
        $branchesResponse->assertDontSee($tenantA['branch']->name);
    }

    public function test_a_tenants_admin_cannot_load_another_tenants_records_by_guessing_the_id()
    {
        $tenantA = $this->makeTenantWithData('Alpha');
        $tenantB = $this->makeTenantWithData('Beta');

        $admin = User::factory()->create(['tenant_id' => $tenantB['tenant']->id, 'role' => User::ROLE_ADMIN, 'is_active' => true]);
        $this->actingAs($admin);

        // Direct tenant_id column models
        $this->get(route('admin.customers.show', $tenantA['customer']))->assertNotFound();
        $this->get(route('admin.branches.edit', $tenantA['branch']))->assertNotFound();

        // Transitively-scoped models (via branch / customer relation)
        $this->get(route('admin.rooms.edit', $tenantA['room']))->assertNotFound();
        $this->put(route('admin.bookings.update-status', $tenantA['booking']), ['status' => 'completed'])->assertNotFound();
        $this->patch(route('admin.dues.mark-paid', $tenantA['due']), ['payment_method' => 'cash'])->assertNotFound();

        // Sanity check: the same routes DO work for tenant B's own records.
        $this->get(route('admin.customers.show', $tenantB['customer']))->assertOk();
        $this->get(route('admin.branches.edit', $tenantB['branch']))->assertOk();
        $this->get(route('admin.rooms.edit', $tenantB['room']))->assertOk();
    }

    public function test_a_tenants_admin_cannot_create_a_booking_against_another_tenants_bed()
    {
        $tenantA = $this->makeTenantWithData('Alpha');
        $tenantB = $this->makeTenantWithData('Beta');

        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true], $tenantB['tenant']);

        $response = $this->post(route('admin.customers.store'), [
            'name' => 'Sneaky Customer',
            'phone' => '9111111111',
            'dob' => '2000-01-01',
            'address' => 'Addr',
            'guardian_phone' => '9111111112',
            'bed_id' => $tenantA['bed']->id, // tampered: belongs to a different tenant
            'check_in_date' => now()->format('Y-m-d'),
            'stay_type' => 'permanent',
            'advance_amount' => 3000,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors();

        // The bed must not have been claimed by the other tenant's booking attempt —
        // only the one booking created in setup exists for it. withoutGlobalScopes()
        // here is deliberate: this checks true DB state, bypassing the tenant scope
        // the rest of the app relies on for isolation.
        $this->assertSame(1, \App\Models\Booking::withoutGlobalScopes()->where('bed_id', $tenantA['bed']->id)->count());
        $this->assertDatabaseHas('beds', ['id' => $tenantA['bed']->id, 'status' => 'occupied']);
    }

    /**
     * The admin dashboard aggregates beds/dues/charges/recent-bookings with raw
     * queries on models that have no tenant_id column of their own (Bed, Due,
     * Booking, ...) — it must not silently sum or list another tenant's data
     * just because those queries don't happen to join through the tenant-scoped
     * ancestor themselves.
     */
    public function test_dashboard_aggregates_do_not_leak_across_tenants()
    {
        $tenantA = $this->makeTenantWithData('Alpha');
        $tenantB = $this->makeTenantWithData('Beta');

        $admin = User::factory()->create(['tenant_id' => $tenantB['tenant']->id, 'role' => User::ROLE_ADMIN, 'is_active' => true]);
        $this->actingAs($admin);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Only tenant B's one bed/booking should count toward the dashboard's stats.
        $response->assertViewHas('stats', fn ($stats) => $stats['beds'] === 1 && $stats['customers'] === 1);
        $response->assertViewHas('pendingDuesAmount', fn ($amount) => (float) $amount === 100.0);
        $response->assertViewHas('recentBookings', fn ($bookings) => $bookings->pluck('id')->all() === [$tenantB['booking']->id]);
        $response->assertDontSee($tenantA['customer']->name);
    }

    /**
     * ResolveTenant binds the currently-logged-in user's tenant into the
     * container on every request. If that binding isn't cleared before a
     * fresh login attempt, Auth::attempt's lookup for a DIFFERENT tenant's
     * admin gets wrongly scoped to the tenant already active in the browser
     * and always reports "credentials do not match" — even with the right
     * password.
     */
    public function test_a_tenant_as_admin_session_does_not_block_logging_into_a_different_tenants_admin_account()
    {
        $tenantA = Tenant::factory()->create(['name' => 'Alpha']);
        $tenantB = Tenant::factory()->create(['name' => 'Beta']);

        $adminA = User::factory()->create([
            'tenant_id' => $tenantA->id, 'role' => User::ROLE_ADMIN, 'is_active' => true,
        ]);
        $adminB = User::factory()->create([
            'tenant_id' => $tenantB->id, 'role' => User::ROLE_ADMIN, 'is_active' => true,
            'password' => bcrypt('tenant-b-secret'),
        ]);

        $response = $this->actingAs($adminA)->post(route('admin.login'), [
            'email' => $adminB->email,
            'password' => 'tenant-b-secret',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($adminB);
    }
}
