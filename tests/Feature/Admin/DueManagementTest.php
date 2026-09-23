<?php

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\Due;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;

class DueManagementTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    private function makeCustomer(): Customer
    {
        return Customer::create([
            'customer_code' => 'SS-TEST-0001',
            'name' => 'Test Customer',
            'phone' => '9876543210',
            'password' => bcrypt('password'),
            'dob' => '2000-01-01',
            'address' => 'Test Address',
            'guardian_phone' => '9876543211',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true]);
    }

    public function test_admin_can_add_a_due_to_a_customer()
    {
        $customer = $this->makeCustomer();

        $response = $this->post(route('admin.dues.store', $customer), [
            'due_type' => 'eb',
            'title' => 'May electricity bill',
            'amount' => 850,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dues', [
            'customer_id' => $customer->id,
            'due_type' => 'eb',
            'amount' => 850,
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_mark_a_due_as_paid_and_it_creates_a_ledger_row()
    {
        $customer = $this->makeCustomer();
        $due = $customer->dues()->create([
            'due_type' => 'fine',
            'title' => 'Late night fine',
            'amount' => 200,
            'status' => 'pending',
            'due_date' => now(),
        ]);

        $response = $this->patch(route('admin.dues.mark-paid', $due), [
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dues', ['id' => $due->id, 'status' => 'paid']);
        $this->assertDatabaseHas('payments', [
            'customer_id' => $customer->id,
            'due_id' => $due->id,
            'amount' => 200,
            'status' => 'paid',
        ]);
    }

    public function test_admin_can_delete_a_due()
    {
        $customer = $this->makeCustomer();
        $due = $customer->dues()->create([
            'due_type' => 'other',
            'title' => 'Mistaken entry',
            'amount' => 100,
            'status' => 'pending',
            'due_date' => now(),
        ]);

        $response = $this->delete(route('admin.dues.destroy', $due));

        $response->assertRedirect();
        $this->assertDatabaseMissing('dues', ['id' => $due->id]);
    }
}
