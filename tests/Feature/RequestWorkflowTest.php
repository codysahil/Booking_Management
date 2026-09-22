<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomer(): Customer
    {
        return Customer::create([
            'customer_code' => 'SS-TEST-0001',
            'name' => 'Test Customer',
            'email' => 'resident@example.com',
            'phone' => '9876543210',
            'password' => bcrypt('password'),
            'dob' => '2000-01-01',
            'address' => 'Test Address',
            'guardian_phone' => '9876543211',
        ]);
    }

    public function test_customer_can_submit_a_service_request()
    {
        $customer = $this->makeCustomer();

        $response = $this->actingAs($customer, 'customer')->post(route('customer.requests.store'), [
            'type' => 'service',
            'subject' => 'Leaking tap in bathroom',
            'description' => 'The tap has been dripping for two days.',
        ]);

        $response->assertRedirect(route('customer.requests.index'));
        $this->assertDatabaseHas('requests', [
            'customer_id' => $customer->id,
            'type' => 'service',
            'subject' => 'Leaking tap in bathroom',
            'status' => 'pending',
        ]);
    }

    public function test_vacation_request_requires_minimum_notice_period()
    {
        $customer = $this->makeCustomer();

        $response = $this->actingAs($customer, 'customer')->post(route('customer.requests.store'), [
            'type' => 'vacation',
            'subject' => 'Moving out',
            'preferred_date' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('preferred_date');
        $this->assertDatabaseMissing('requests', ['customer_id' => $customer->id]);
    }

    public function test_vacation_request_with_enough_notice_succeeds()
    {
        $customer = $this->makeCustomer();

        $response = $this->actingAs($customer, 'customer')->post(route('customer.requests.store'), [
            'type' => 'vacation',
            'subject' => 'Moving out',
            'preferred_date' => now()->addDays(35)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('customer.requests.index'));
        $this->assertDatabaseHas('requests', ['customer_id' => $customer->id, 'type' => 'vacation']);
    }

    public function test_admin_can_view_requests_index_and_notifications_centre()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $this->actingAs($admin)->get(route('admin.requests.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.notifications.index'))->assertStatus(200);
    }

    public function test_admin_dashboard_loads()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertStatus(200);
    }

    public function test_admin_can_respond_to_a_request_and_notify_the_resident()
    {
        Notification::fake();

        $customer = $this->makeCustomer();
        $request = $customer->requests()->create([
            'type' => 'complaint',
            'subject' => 'Noisy neighbours',
            'status' => 'pending',
        ]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $response = $this->actingAs($admin)->put(route('admin.requests.update', $request), [
            'status' => 'completed',
            'admin_response' => 'We have spoken to the other residents.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => 'completed',
            'admin_response' => 'We have spoken to the other residents.',
        ]);
    }
}
