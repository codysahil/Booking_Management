<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Customer;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_login_page()
    {
        $response = $this->get(route('customer.login'));
        $response->assertStatus(200);
    }

    public function test_customer_can_login()
    {
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
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_customer_can_view_dashboard()
    {
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
}
