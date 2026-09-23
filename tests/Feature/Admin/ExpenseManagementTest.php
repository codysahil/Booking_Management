<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;

class ExpenseManagementTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true]);
    }

    public function test_admin_can_view_expenses_index()
    {
        $response = $this->get(route('admin.expenses.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_record_an_expense()
    {
        $branch = Branch::create(['name' => 'Main Branch', 'address' => 'Addr']);

        $response = $this->post(route('admin.expenses.store'), [
            'branch_id' => $branch->id,
            'category' => 'Electricity',
            'amount' => 4500,
            'date' => now()->format('Y-m-d'),
            'vendor' => 'TN Electricity Board',
            'payment_method' => 'upi',
        ]);

        $response->assertRedirect(route('admin.expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'branch_id' => $branch->id,
            'category' => 'Electricity',
            'amount' => 4500,
        ]);
    }

    public function test_admin_can_update_and_delete_an_expense()
    {
        $expense = Expense::create([
            'category' => 'Maintenance & Repairs',
            'amount' => 1000,
            'date' => now(),
            'payment_method' => 'cash',
            'created_by' => auth()->id(),
        ]);

        $response = $this->put(route('admin.expenses.update', $expense), [
            'category' => 'Maintenance & Repairs',
            'amount' => 1500,
            'date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('admin.expenses.index'));
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'amount' => 1500]);

        $response = $this->delete(route('admin.expenses.destroy', $expense));
        $response->assertRedirect();
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_expenses_can_be_exported_as_csv()
    {
        Expense::create([
            'category' => 'Food & Groceries',
            'amount' => 2000,
            'date' => now(),
            'payment_method' => 'cash',
            'created_by' => auth()->id(),
        ]);

        $response = $this->get(route('admin.expenses.export'));

        $response->assertStatus(200);
        $this->assertStringContainsString('csv', $response->headers->get('content-type'));
    }
}
