<?php

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    /**
     * "Pending Dues" must reflect everything actually still owed: unpaid rent
     * whether pending or overdue, plus unpaid fines/dues — not just charges
     * still narrowly in "pending" status, which understates real receivables.
     */
    public function test_pending_dues_includes_overdue_charges_and_unpaid_dues()
    {
        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $customer = Customer::create([
            'customer_code' => 'SS-TEST-0001', 'name' => 'Test Customer', 'phone' => '9876543210',
            'password' => bcrypt('password'), 'dob' => '2000-01-01', 'address' => 'Addr', 'guardian_phone' => '9876543211',
        ]);

        $branch = \App\Models\Branch::create(['name' => 'Branch', 'address' => 'Addr']);
        $room = $branch->rooms()->create(['room_number' => '101', 'capacity' => 1, 'type' => 'AC', 'gender_allowed' => 'Any']);
        $bed = $room->beds()->create(['bed_number' => '101-A', 'monthly_rent' => 5000, 'status' => 'occupied']);
        $booking = $customer->bookings()->create([
            'booking_reference' => 'BK-TEST0001', 'bed_id' => $bed->id, 'check_in_date' => now()->subMonths(2),
            'status' => 'active', 'advance_paid' => 5000,
        ]);

        $customer->monthlyCharges()->create([
            'booking_id' => $booking->id, 'month_year' => now()->format('Y-m'), 'rent_amount' => 5000, 'eb_amount' => 0, 'other_charges' => 0,
            'total_amount' => 5000, 'status' => 'overdue', 'due_date' => now()->subDays(5),
        ]);
        $customer->monthlyCharges()->create([
            'booking_id' => $booking->id, 'month_year' => now()->subMonth()->format('Y-m'), 'rent_amount' => 3000, 'eb_amount' => 0, 'other_charges' => 0,
            'total_amount' => 3000, 'status' => 'pending', 'due_date' => now()->addDays(2),
        ]);
        $customer->dues()->create([
            'due_type' => 'fine', 'title' => 'Fine', 'amount' => 200, 'status' => 'pending', 'due_date' => now(),
        ]);

        $response = $this->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertViewHas('pendingDues', 8200.0);
    }
}
