<?php

namespace Tests\Feature\Console;

use App\Models\Bed;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\MonthlyCharge;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChargeCommandsTest extends TestCase
{
    use RefreshDatabase;

    private function makeActiveBooking(): array
    {
        $branch = Branch::create(['name' => 'Branch 1', 'address' => 'Addr']);
        $room = Room::create(['branch_id' => $branch->id, 'room_number' => '101', 'capacity' => 2, 'type' => 'AC', 'gender_allowed' => 'Female']);
        $bed = Bed::create(['room_id' => $room->id, 'bed_number' => '101-A', 'monthly_rent' => 6000, 'status' => 'occupied']);

        $customer = Customer::create([
            'customer_code' => 'SS-TEST-0001',
            'name' => 'Test Customer',
            'phone' => '9876543210',
            'password' => bcrypt('password'),
            'dob' => '2000-01-01',
            'address' => 'Test Address',
            'guardian_phone' => '9876543211',
        ]);

        $booking = $customer->bookings()->create([
            'booking_reference' => 'BK-TEST0001',
            'bed_id' => $bed->id,
            'check_in_date' => now(),
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
}
