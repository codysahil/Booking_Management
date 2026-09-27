<?php

namespace Tests\Feature\Console;

use App\Models\Bed;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\MonthlyCharge;
use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;

class ChargeCommandsTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    private function makeActiveBooking(): array
    {
        return $this->makeActiveBookingForTenant($this->bindTenant());
    }

    /** @return array{0: Customer, 1: \App\Models\Booking} */
    private function makeActiveBookingForTenant(Tenant $tenant, string $customerCode = 'SS-TEST-0001', string $bookingRef = 'BK-TEST0001'): array
    {
        $this->bindTenant($tenant);

        $branch = Branch::create(['name' => 'Branch 1', 'address' => 'Addr']);
        $room = Room::create(['branch_id' => $branch->id, 'room_number' => '101', 'capacity' => 2, 'type' => 'AC', 'gender_allowed' => 'Female']);
        $bed = Bed::create(['room_id' => $room->id, 'bed_number' => '101-A', 'monthly_rent' => 6000, 'status' => 'occupied']);

        $customer = Customer::create([
            'customer_code' => $customerCode,
            'name' => 'Test Customer',
            'phone' => '9876543210',
            'password' => bcrypt('password'),
            'dob' => '2000-01-01',
            'address' => 'Test Address',
            'guardian_phone' => '9876543211',
        ]);

        // Check-in is safely in the past (not "this month") so charges generated
        // for the current month are a normal full month, not the resident's own
        // prorated first month — see ChargeProrationTest for that coverage.
        $booking = $customer->bookings()->create([
            'booking_reference' => $bookingRef,
            'bed_id' => $bed->id,
            'check_in_date' => now()->subMonths(2),
            'status' => 'active',
            'advance_paid' => 6000,
        ]);

        return [$customer, $booking];
    }

    public function test_charges_generate_creates_one_charge_per_active_booking()
    {
        [$customer] = $this->makeActiveBooking();

        $this->artisan('charges:generate')->assertSuccessful();

        $this->assertDatabaseHas('monthly_charges', [
            'customer_id' => $customer->id,
            'total_amount' => 6000,
            'status' => 'pending',
        ]);

        // Running it again for the same month should not duplicate the charge.
        $this->artisan('charges:generate');
        $this->assertSame(1, MonthlyCharge::where('customer_id', $customer->id)->count());
    }

    /** A customer holding two beds (two Booking rows) must be billed for both, every month. */
    public function test_charges_generate_bills_every_booking_even_for_the_same_customer()
    {
        [$customer, $bookingA] = $this->makeActiveBooking();

        $bedB = Bed::create(['room_id' => $bookingA->bed->room_id, 'bed_number' => '101-B', 'monthly_rent' => 6000, 'status' => 'occupied']);
        $bookingB = $customer->bookings()->create([
            'booking_reference' => 'BK-TEST0002',
            'bed_id' => $bedB->id,
            'check_in_date' => now(),
            'status' => 'active',
            'advance_paid' => 6000,
        ]);

        $this->artisan('charges:generate')->assertSuccessful();

        $this->assertSame(2, MonthlyCharge::where('customer_id', $customer->id)->count());
        $this->assertDatabaseHas('monthly_charges', ['booking_id' => $bookingA->id]);
        $this->assertDatabaseHas('monthly_charges', ['booking_id' => $bookingB->id]);

        // Running it again for the same month should not duplicate either charge.
        $this->artisan('charges:generate');
        $this->assertSame(2, MonthlyCharge::where('customer_id', $customer->id)->count());
    }

    public function test_charges_mark_overdue_flags_past_due_pending_charges()
    {
        [$customer, $booking] = $this->makeActiveBooking();

        $charge = MonthlyCharge::create([
            'customer_id' => $customer->id,
            'booking_id' => $booking->id,
            'month_year' => now()->subMonth()->format('Y-m'),
            'rent_amount' => 6000,
            'eb_amount' => 0,
            'other_charges' => 0,
            'total_amount' => 6000,
            'status' => 'pending',
            'due_date' => now()->subDays(10),
        ]);

        $this->artisan('charges:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('monthly_charges', ['id' => $charge->id, 'status' => 'overdue']);
    }

    /** The configured late fee must actually land on the charge once it goes overdue. */
    public function test_charges_mark_overdue_applies_the_configured_late_fee()
    {
        [$customer, $booking] = $this->makeActiveBooking();

        \App\Models\Setting::putMany(['late_fee' => 200]);

        $charge = MonthlyCharge::create([
            'customer_id' => $customer->id,
            'booking_id' => $booking->id,
            'month_year' => now()->subMonth()->format('Y-m'),
            'rent_amount' => 6000,
            'eb_amount' => 0,
            'other_charges' => 0,
            'total_amount' => 6000,
            'status' => 'pending',
            'due_date' => now()->subDays(10),
        ]);

        $this->artisan('charges:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('monthly_charges', [
            'id' => $charge->id,
            'status' => 'overdue',
            'other_charges' => 200,
            'total_amount' => 6200,
        ]);
    }

    /**
     * A scheduled run has no tenant bound going in — each tenant must get
     * ITS OWN rent_due_day, not whichever tenant's setting the command
     * happened to read first (or, before this was fixed, an unscoped
     * Setting query that could return any tenant's value at all).
     */
    public function test_charges_generate_uses_each_tenants_own_rent_due_day()
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        [$customerA] = $this->makeActiveBookingForTenant($tenantA, 'SS-TEST-A', 'BK-TEST-A');
        \App\Models\Setting::putMany(['rent_due_day' => 10]);

        [$customerB] = $this->makeActiveBookingForTenant($tenantB, 'SS-TEST-B', 'BK-TEST-B');
        \App\Models\Setting::putMany(['rent_due_day' => 20]);

        $this->artisan('charges:generate')->assertSuccessful();

        $chargeA = MonthlyCharge::where('customer_id', $customerA->id)->first();
        $chargeB = MonthlyCharge::where('customer_id', $customerB->id)->first();

        $this->assertSame(10, $chargeA->due_date->day);
        $this->assertSame(20, $chargeB->due_date->day);
    }

    /** Same guarantee as above, for the late fee applied when marking a charge overdue. */
    public function test_charges_mark_overdue_uses_each_tenants_own_late_fee()
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        [, $bookingA] = $this->makeActiveBookingForTenant($tenantA, 'SS-TEST-A', 'BK-TEST-A');
        \App\Models\Setting::putMany(['late_fee' => 100]);
        $chargeA = MonthlyCharge::create([
            'customer_id' => $bookingA->customer_id, 'booking_id' => $bookingA->id,
            'month_year' => now()->subMonth()->format('Y-m'), 'rent_amount' => 6000, 'eb_amount' => 0,
            'other_charges' => 0, 'total_amount' => 6000, 'status' => 'pending', 'due_date' => now()->subDays(10),
        ]);

        [, $bookingB] = $this->makeActiveBookingForTenant($tenantB, 'SS-TEST-B', 'BK-TEST-B');
        \App\Models\Setting::putMany(['late_fee' => 500]);
        $chargeB = MonthlyCharge::create([
            'customer_id' => $bookingB->customer_id, 'booking_id' => $bookingB->id,
            'month_year' => now()->subMonth()->format('Y-m'), 'rent_amount' => 6000, 'eb_amount' => 0,
            'other_charges' => 0, 'total_amount' => 6000, 'status' => 'pending', 'due_date' => now()->subDays(10),
        ]);

        $this->artisan('charges:mark-overdue')->assertSuccessful();

        $this->assertDatabaseHas('monthly_charges', ['id' => $chargeA->id, 'status' => 'overdue', 'total_amount' => 6100]);
        $this->assertDatabaseHas('monthly_charges', ['id' => $chargeB->id, 'status' => 'overdue', 'total_amount' => 6500]);
    }
}
