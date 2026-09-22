<?php

namespace Tests\Feature\Public;

use App\Models\Bed;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BookingConfirmationTest extends TestCase
{
    use RefreshDatabase;

    private function makeBooking()
    {
        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);
        $room = Room::create(['branch_id' => $branch->id, 'room_number' => '101', 'capacity' => 2, 'type' => 'AC', 'gender_allowed' => 'Female']);
        $bed = Bed::create(['room_id' => $room->id, 'bed_number' => '101-A', 'monthly_rent' => 5000, 'status' => 'reserved']);

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
            'advance_paid' => 0,
        ]);

        $customer->payments()->create([
            'booking_id' => $booking->id,
            'amount' => 5000,
            'payment_type' => 'Advance',
            'payment_method' => 'pending',
            'transaction_ref' => 'PENDING-TEST',
            'status' => 'pending',
        ]);

        return $booking;
    }

    public function test_confirmation_page_requires_a_valid_signature()
    {
        $booking = $this->makeBooking();

        $response = $this->get(route('booking.confirmation', $booking));

        $response->assertStatus(403);
    }

    public function test_confirmation_page_loads_with_a_valid_signed_url()
    {
        $booking = $this->makeBooking();

        $url = URL::signedRoute('booking.confirmation', ['booking' => $booking]);

        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertSee($booking->booking_reference);
    }

    public function test_advance_online_payment_is_unavailable_when_razorpay_is_not_configured()
    {
        $booking = $this->makeBooking();

        $response = $this->post(route('booking.advance.create-order', $booking));

        $response->assertStatus(404);
    }
}
