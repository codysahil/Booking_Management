<?php

namespace Tests\Feature\Console;

use App\Models\Bed;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReleaseExpiredBookingsTest extends TestCase
{
    use RefreshDatabase;

    private function makePendingBooking(string $reservedUntil, string $customerCode)
    {
        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);
        $room = $branch->rooms()->create(['room_number' => '101', 'capacity' => 2, 'type' => 'AC', 'gender_allowed' => 'Female']);
        $bed = $room->beds()->create(['bed_number' => '101-A', 'monthly_rent' => 5000, 'status' => 'reserved', 'reserved_until' => $reservedUntil]);

        $customer = Customer::create([
            'customer_code' => $customerCode,
            'name' => 'Test Customer',
            'phone' => '9876500010',
            'password' => bcrypt('9876500010'),
            'dob' => now()->subYears(20),
            'address' => 'Addr',
            'guardian_phone' => '9876500010',
        ]);

        $booking = $customer->bookings()->create([
            'booking_reference' => 'BK-' . $customerCode,
            'bed_id' => $bed->id,
            'check_in_date' => now(),
            'status' => 'pending_payment',
            'advance_paid' => 0,
        ]);

        $payment = $customer->payments()->create([
            'booking_id' => $booking->id,
            'amount' => 5000,
            'payment_type' => 'Advance',
            'payment_method' => 'pending',
            'transaction_ref' => 'PENDING-' . $customerCode,
            'status' => 'pending',
        ]);

        return [$booking, $bed, $payment];
    }

    public function test_expired_unpaid_bookings_are_cancelled_and_their_beds_freed()
    {
        [$booking, $bed, $payment] = $this->makePendingBooking(now()->subMinute()->toDateTimeString(), 'SS-EXP1-0001');

        $this->artisan('bookings:release-expired')->assertSuccessful();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('beds', ['id' => $bed->id, 'status' => 'vacant', 'reserved_until' => null]);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'failed']);
    }

    public function test_bookings_still_within_their_hold_window_are_left_alone()
    {
        [$booking, $bed, $payment] = $this->makePendingBooking(now()->addMinutes(20)->toDateTimeString(), 'SS-EXP2-0001');

        $this->artisan('bookings:release-expired')->assertSuccessful();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'pending_payment']);
        $this->assertDatabaseHas('beds', ['id' => $bed->id, 'status' => 'reserved']);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'pending']);
    }
}
