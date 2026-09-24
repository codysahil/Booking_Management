<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;
use App\Models\Customer;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    public function test_customer_can_view_login_page()
    {
        $response = $this->get(route('customer.login'));
        $response->assertStatus(200);
    }

    public function test_customer_can_login()
    {
        $this->bindTenant();

        $customer = Customer::create([
            'customer_code' => 'CUST-001',
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'phone' => '1234567890',
            'dob' => '2000-01-01',
            'address' => 'Test Address',
            'guardian_phone' => '0987654321',
        ]);

        $response = $this->post(route('customer.login'), [
            'customer_code' => 'CUST-001',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_customer_can_view_dashboard()
    {
        $this->bindTenant();

        $customer = Customer::create([
            'customer_code' => 'CUST-001',
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'phone' => '1234567890',
            'dob' => '2000-01-01',
            'address' => 'Test Address',
            'guardian_phone' => '0987654321',
        ]);

        $response = $this->actingAs($customer, 'customer')->get(route('customer.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Test Customer');
    }

    /** An overdue charge is still owed — a resident's own dashboard must not say ₹0 while one sits unpaid. */
    public function test_dashboard_pending_dues_includes_overdue_charges_not_just_pending()
    {
        $tenant = $this->bindTenant();

        $customer = Customer::create([
            'customer_code' => 'CUST-002', 'name' => 'Overdue Resident', 'phone' => '1234567891',
            'password' => bcrypt('password'), 'dob' => '2000-01-01', 'address' => 'Addr', 'guardian_phone' => '0987654322',
        ]);

        $branch = \App\Models\Branch::create(['name' => 'Branch', 'address' => 'Addr']);
        $room = $branch->rooms()->create(['room_number' => '101', 'capacity' => 1, 'type' => 'AC', 'gender_allowed' => 'Any']);
        $bed = $room->beds()->create(['bed_number' => '101-A', 'monthly_rent' => 5000, 'status' => 'occupied']);
        $booking = $customer->bookings()->create([
            'booking_reference' => 'BK-TEST0002', 'bed_id' => $bed->id, 'check_in_date' => now()->subMonths(2),
            'status' => 'active', 'advance_paid' => 5000,
        ]);

        $customer->monthlyCharges()->create([
            'booking_id' => $booking->id, 'month_year' => now()->format('Y-m'), 'rent_amount' => 5000, 'eb_amount' => 0, 'other_charges' => 0,
            'total_amount' => 5000, 'status' => 'overdue', 'due_date' => now()->subDays(5),
        ]);

        $response = $this->actingAs($customer, 'customer')->get(route('customer.dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalPending', 5000.0);
    }
}
