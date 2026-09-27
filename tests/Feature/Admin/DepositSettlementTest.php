<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Due;
use App\Models\MonthlyCharge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;

/**
 * Vacating a resident used to just free the bed with no record of what
 * happened to their deposit. Now it settles it: outstanding dues/charges
 * and an optional damage deduction come off the deposit (advance_paid),
 * and whatever's left is the refund owed.
 */
class DepositSettlementTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    /**
     * @return array{0: Customer, 1: \App\Models\Bed, 2: \App\Models\Booking, 3: User}
     *
     * The admin performing the vacate MUST belong to the same tenant as the
     * customer/booking — ResolveTenant middleware rebinds currentTenantId to
     * whichever user is authenticated on each request (running after route
     * model binding but before the controller runs), so an admin from a
     * different tenant would silently scope every query inside the
     * controller to their own (wrong) tenant instead.
     */
    private function makeCheckedInCustomer(float $depositAmount = 5000): array
    {
        $tenant = $this->bindTenant();

        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);
        $room = $branch->rooms()->create(['room_number' => '101', 'capacity' => 2, 'type' => 'AC', 'gender_allowed' => 'Female']);
        $bed = $room->beds()->create(['bed_number' => '101-A', 'monthly_rent' => 5000, 'status' => 'occupied']);

        $customer = Customer::create([
            'customer_code' => 'SS-DEP-01', 'name' => 'Resident', 'phone' => '9876500030',
            'password' => bcrypt('password'), 'dob' => now()->subYears(20),
            'address' => 'Addr', 'guardian_phone' => '9876500031',
        ]);

        $booking = $customer->bookings()->create([
            'booking_reference' => 'BK-DEP01', 'bed_id' => $bed->id, 'check_in_date' => now()->subMonths(3),
            'status' => 'active', 'advance_paid' => $depositAmount,
        ]);

        $owner = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_ADMIN, 'is_active' => true]);

        return [$customer, $bed, $booking, $owner];
    }

    public function test_vacating_with_no_dues_or_deduction_refunds_the_full_deposit()
    {
        [$customer, $bed, $booking, $owner] = $this->makeCheckedInCustomer(5000);

        $response = $this->actingAs($owner)->patch(route('admin.customers.deactivate', $customer));

        $response->assertRedirect(route('admin.customers.index'));
        $booking->refresh();
        $this->assertSame('completed', $booking->status);
        $this->assertSame('vacant', $bed->fresh()->status);
        $this->assertFalse($customer->fresh()->is_active);
        $this->assertEquals(5000.00, $booking->deposit_refund_amount);
        $this->assertNull($booking->deposit_deduction_amount);
        $this->assertNotNull($booking->settled_at);
    }

    /**
     * The real vacate modal's Alpine x-model always submits a literal "0" for an
     * untouched deduction field (not an empty string) — required_with treats "0"
     * as present, which used to reject every ordinary no-deduction vacate. This
     * reproduces exactly what the browser actually sends.
     */
    public function test_vacating_with_a_zero_deduction_amount_and_no_reason_succeeds()
    {
        [$customer, , $booking, $owner] = $this->makeCheckedInCustomer(5000);

        $response = $this->actingAs($owner)->patch(route('admin.customers.deactivate', $customer), [
            'deposit_deduction_amount' => '0',
            'deposit_deduction_reason' => '',
        ]);

        $response->assertRedirect(route('admin.customers.index'));
        $response->assertSessionDoesntHaveErrors();
        $this->assertFalse($customer->fresh()->is_active);
        $this->assertEquals(5000.00, $booking->fresh()->deposit_refund_amount);
    }

    /** A real (> 0) deduction still requires a reason. */
    public function test_a_nonzero_deduction_without_a_reason_is_still_rejected()
    {
        [$customer, , , $owner] = $this->makeCheckedInCustomer(5000);

        $response = $this->actingAs($owner)->patch(route('admin.customers.deactivate', $customer), [
            'deposit_deduction_amount' => 500,
        ]);

        $response->assertSessionHasErrors('deposit_deduction_reason');
        $this->assertTrue($customer->fresh()->is_active);
    }

    public function test_outstanding_dues_and_charges_come_off_the_deposit_before_refund()
    {
        [$customer, , $booking, $owner] = $this->makeCheckedInCustomer(5000);

        MonthlyCharge::create([
            'customer_id' => $customer->id, 'booking_id' => $booking->id,
            'month_year' => now()->format('Y-m'), 'rent_amount' => 1500, 'eb_amount' => 0,
            'other_charges' => 0, 'total_amount' => 1500, 'status' => 'overdue', 'due_date' => now()->subDays(5),
        ]);
        Due::create([
            'customer_id' => $customer->id, 'due_type' => 'fine', 'title' => 'Late fine',
            'amount' => 200, 'status' => 'pending', 'due_date' => now(),
        ]);

        $this->actingAs($owner)->patch(route('admin.customers.deactivate', $customer));

        // 5000 deposit - 1500 overdue charge - 200 pending due = 3300 refund.
        $this->assertEquals(3300.00, $booking->fresh()->deposit_refund_amount);
    }

    public function test_a_damage_deduction_reduces_the_refund_and_is_recorded()
    {
        [$customer, , $booking, $owner] = $this->makeCheckedInCustomer(5000);

        $response = $this->actingAs($owner)->patch(route('admin.customers.deactivate', $customer), [
            'deposit_deduction_amount' => 800,
            'deposit_deduction_reason' => 'Broken window pane',
        ]);

        $response->assertRedirect(route('admin.customers.index'));
        $booking->refresh();
        $this->assertEquals(800.00, $booking->deposit_deduction_amount);
        $this->assertSame('Broken window pane', $booking->deposit_deduction_reason);
        $this->assertEquals(4200.00, $booking->deposit_refund_amount);
    }

    /** Outstanding dues can legitimately exceed the deposit — the refund must floor at zero, never go negative. */
    public function test_the_refund_never_goes_negative_when_dues_exceed_the_deposit()
    {
        [$customer, , $booking, $owner] = $this->makeCheckedInCustomer(1000);

        MonthlyCharge::create([
            'customer_id' => $customer->id, 'booking_id' => $booking->id,
            'month_year' => now()->format('Y-m'), 'rent_amount' => 5000, 'eb_amount' => 0,
            'other_charges' => 0, 'total_amount' => 5000, 'status' => 'overdue', 'due_date' => now()->subDays(5),
        ]);

        $this->actingAs($owner)->patch(route('admin.customers.deactivate', $customer));

        $this->assertEquals(0.00, $booking->fresh()->deposit_refund_amount);
    }

    /** A deduction can't be larger than what's actually left of the deposit after dues — the server must not trust an arbitrary client-submitted amount. */
    public function test_a_deduction_larger_than_the_remaining_deposit_is_rejected()
    {
        [$customer, , $booking, $owner] = $this->makeCheckedInCustomer(1000);

        $response = $this->actingAs($owner)->patch(route('admin.customers.deactivate', $customer), [
            'deposit_deduction_amount' => 5000,
            'deposit_deduction_reason' => 'Way too much',
        ]);

        $response->assertSessionHasErrors('deposit_deduction_amount');
        $this->assertTrue($customer->fresh()->is_active, 'Vacate must not proceed when the deduction fails validation.');
    }
}
