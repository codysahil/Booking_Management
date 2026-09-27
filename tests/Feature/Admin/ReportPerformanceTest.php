<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;

/**
 * getMonthlyRevenue() used to run 12 separate sum() queries (one per month)
 * and getRevenueByBranch() ran one Payment query per branch inside a map() —
 * real N+1s on a page every admin loads. These lock in both the fixed query
 * count and that the numbers are still correct, including the one subtlety
 * worth preserving exactly: a payment from a customer with bookings in two
 * different branches counts toward both branches' totals (the original
 * whereHas-based query had that same "any matching booking" behavior).
 */
class ReportPerformanceTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    private function makeBranchWithOccupiedBed(string $roomNumber): array
    {
        $branch = Branch::create(['name' => "Branch {$roomNumber}", 'address' => 'Addr']);
        $room = $branch->rooms()->create(['room_number' => $roomNumber, 'capacity' => 1, 'type' => 'AC', 'gender_allowed' => 'Any']);
        $bed = $room->beds()->create(['bed_number' => "{$roomNumber}-A", 'monthly_rent' => 5000, 'status' => 'occupied']);

        return [$branch, $bed];
    }

    public function test_monthly_revenue_sums_payments_into_the_correct_month_with_few_queries()
    {
        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true]);
        [, $bed] = $this->makeBranchWithOccupiedBed('101');

        $customer = Customer::create([
            'customer_code' => 'CUST-PERF-1', 'name' => 'Perf Test', 'phone' => '9990000001',
            'password' => bcrypt('password'), 'dob' => '2000-01-01', 'address' => 'Addr', 'guardian_phone' => '9990000002',
        ]);
        $booking = $customer->bookings()->create([
            'booking_reference' => 'BK-PERF-1', 'bed_id' => $bed->id, 'check_in_date' => now()->subMonths(2),
            'status' => 'active', 'advance_paid' => 5000,
        ]);

        Payment::forceCreate([
            'customer_id' => $customer->id, 'booking_id' => $booking->id, 'amount' => 4500,
            'payment_type' => 'rent', 'status' => 'completed', 'payment_method' => 'cash', 'created_at' => now(), 'updated_at' => now(),
        ]);
        Payment::forceCreate([
            'customer_id' => $customer->id, 'booking_id' => $booking->id, 'amount' => 3200,
            'payment_type' => 'rent', 'status' => 'completed', 'payment_method' => 'cash', 'created_at' => now()->subMonth(), 'updated_at' => now()->subMonth(),
        ]);

        DB::enableQueryLog();
        $response = $this->get(route('admin.reports.index'));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();

        $monthly = collect($response->viewData('monthlyRevenue'));
        $thisMonth = $monthly->firstWhere('month', now()->format('M Y'));
        $lastMonth = $monthly->firstWhere('month', now()->subMonth()->format('M Y'));

        $this->assertEquals(4500, $thisMonth['revenue']);
        $this->assertEquals(3200, $lastMonth['revenue']);

        // Was 12+ queries just for this one chart before the fix (one sum() per month).
        $this->assertLessThan(40, $queryCount, "Reports page ran {$queryCount} queries — expected the monthly chart to use one query, not one per month.");
    }

    public function test_revenue_by_branch_counts_a_payment_toward_every_branch_the_customer_has_a_booking_in()
    {
        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true]);
        [$branchA, $bedA] = $this->makeBranchWithOccupiedBed('201');
        [$branchB, $bedB] = $this->makeBranchWithOccupiedBed('202');

        $customer = Customer::create([
            'customer_code' => 'CUST-PERF-2', 'name' => 'Multi Branch', 'phone' => '9990000003',
            'password' => bcrypt('password'), 'dob' => '2000-01-01', 'address' => 'Addr', 'guardian_phone' => '9990000004',
        ]);
        // Same customer has bookings in both branches (e.g. moved locations).
        $customer->bookings()->create([
            'booking_reference' => 'BK-PERF-2A', 'bed_id' => $bedA->id, 'check_in_date' => now()->subMonths(3),
            'status' => 'completed', 'advance_paid' => 5000,
        ]);
        $bookingB = $customer->bookings()->create([
            'booking_reference' => 'BK-PERF-2B', 'bed_id' => $bedB->id, 'check_in_date' => now(),
            'status' => 'active', 'advance_paid' => 5000,
        ]);

        Payment::forceCreate([
            'customer_id' => $customer->id, 'booking_id' => $bookingB->id, 'amount' => 6000,
            'payment_type' => 'rent', 'status' => 'completed', 'payment_method' => 'cash', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->get(route('admin.reports.index'));
        $response->assertOk();

        $byBranch = collect($response->viewData('revenueByBranch'));
        $this->assertEquals(6000, $byBranch->firstWhere('name', $branchA->name)['revenue']);
        $this->assertEquals(6000, $byBranch->firstWhere('name', $branchB->name)['revenue']);
    }
}
