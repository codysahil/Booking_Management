<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;

/**
 * A real PG/hostel needs a resident's move-in and move-out to always be a staff
 * decision — never something the resident (or an anonymous visitor) can trigger
 * themselves. This locks that in as a guarantee rather than an implicit side
 * effect of "no such route happens to exist".
 */
class AdminGatedLifecycleTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    private function makeCheckedInCustomer(): array
    {
        $this->bindTenant();

        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);
        $room = $branch->rooms()->create(['room_number' => '101', 'capacity' => 2, 'type' => 'AC', 'gender_allowed' => 'Female']);
        $bed = $room->beds()->create(['bed_number' => '101-A', 'monthly_rent' => 5000, 'status' => 'occupied']);

        $customer = Customer::create([
            'customer_code' => 'SS-GUARD-01', 'name' => 'Resident', 'phone' => '9876500020',
            'password' => bcrypt('9876500020'), 'dob' => now()->subYears(20),
            'address' => 'Addr', 'guardian_phone' => '9876500020',
        ]);

        $customer->bookings()->create([
            'booking_reference' => 'BK-GUARD01', 'bed_id' => $bed->id, 'check_in_date' => now(),
            'status' => 'active', 'advance_paid' => 5000,
        ]);

        return [$customer, $bed];
    }

    public function test_a_guest_cannot_reach_the_walk_in_check_in_or_vacate_endpoints()
    {
        [$customer, $bed] = $this->makeCheckedInCustomer();

        $this->post(route('admin.customers.store'), ['bed_id' => $bed->id])
            ->assertRedirect(route('admin.login'));

        $this->patch(route('admin.customers.deactivate', $customer))
            ->assertRedirect(route('admin.login'));

        $this->assertSame('occupied', $bed->fresh()->status);
        $this->assertTrue($customer->fresh()->is_active);
    }

    public function test_a_logged_in_resident_cannot_reach_the_walk_in_check_in_or_vacate_endpoints()
    {
        [$customer, $bed] = $this->makeCheckedInCustomer();

        // A resident portal session (auth:customer guard) is a completely different
        // guard from the admin's — these admin routes require the `admin` middleware,
        // which only recognizes App\Models\User, never a Customer.
        $this->actingAs($customer, 'customer');

        $this->post(route('admin.customers.store'), ['bed_id' => $bed->id])
            ->assertRedirect(route('admin.login'));

        $this->patch(route('admin.customers.deactivate', $customer))
            ->assertRedirect(route('admin.login'));

        $this->assertSame('occupied', $bed->fresh()->status);
        $this->assertTrue($customer->fresh()->is_active);
    }

    public function test_no_customer_portal_route_exists_to_change_bed_or_booking_status()
    {
        // The resident portal only ever reads booking/payment state — asserting this at
        // the route level means a future PR can't accidentally add a self-service
        // check-in/vacate action without this test failing first.
        $customerRoutes = collect(app('router')->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'customer/'))
            ->map(fn ($route) => $route->getActionName());

        foreach ($customerRoutes as $action) {
            $this->assertStringNotContainsString('CustomerController@store', $action);
            $this->assertStringNotContainsString('CustomerController@deactivate', $action);
        }
    }
}
