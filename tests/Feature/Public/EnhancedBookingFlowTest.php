<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Branch;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Customer;

class EnhancedBookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_booking_flow_with_group_booking()
    {
        // Create test data
        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);
        $room = Room::create([
            'branch_id' => $branch->id,
            'room_number' => 'R101',
            'capacity' => 4,
            'type' => 'AC',
            'gender_allowed' => 'Female',
        ]);

        $bed1 = Bed::create([
            'room_id' => $room->id,
            'bed_number' => 'B1',
            'monthly_rent' => 5000,
            'status' => 'vacant',
        ]);

        $bed2 = Bed::create([
            'room_id' => $room->id,
            'bed_number' => 'B2',
            'monthly_rent' => 5000,
            'status' => 'vacant',
        ]);

        // Step 1: View room and bed selection page
        $response = $this->get(route('booking.room', ['branch' => $branch->id, 'room' => $room->id]));
        $response->assertStatus(200);
        $response->assertSee('Select Your Beds');

        // Step 2: Select multiple beds
        $response = $this->post(route('booking.select-beds'), [
            'bed_ids' => [$bed1->id, $bed2->id],
        ]);
        $response->assertRedirect(route('booking.checkout'));
        $this->assertNotNull(session('selected_bed_ids'));

        // Verify beds are reserved
        $bed1->refresh();
        $bed2->refresh();
        $this->assertNotNull($bed1->reserved_until);
        $this->assertNotNull($bed2->reserved_until);

        // Step 3: View checkout page
        $response = $this->get(route('booking.checkout'));
        $response->assertStatus(200);
        $response->assertSee('Complete Your Booking');

        // Step 4: Process payment and create booking
        $response = $this->post(route('booking.process-payment'), [
            'name' => 'Jane Doe',
            'phone' => '9876543210',
            'email' => 'jane@example.com',
            'address' => '123 Main St',
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'payment_method' => 'upi',
        ]);

        // Verify customer created
        $this->assertDatabaseHas('customers', [
            'name' => 'Jane Doe',
            'phone' => '9876543210',
        ]);

        $customer = Customer::where('phone', '9876543210')->first();
        $this->assertNotNull($customer);
        $this->assertStringStartsWith('CUST-', $customer->customer_code);

        // Verify bookings created
        $this->assertDatabaseHas('bookings', [
            'customer_id' => $customer->id,
            'bed_id' => $bed1->id,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('bookings', [
            'customer_id' => $customer->id,
            'bed_id' => $bed2->id,
            'status' => 'confirmed',
        ]);

        // Verify payment record
        $this->assertDatabaseHas('payments', [
            'customer_id' => $customer->id,
            'amount' => 10000, // 2 beds * 5000
            'payment_type' => 'Advance',
            'status' => 'completed',
        ]);

        // Verify beds marked as occupied
        $bed1->refresh();
        $bed2->refresh();
        $this->assertEquals('occupied', $bed1->status);
        $this->assertEquals('occupied', $bed2->status);
        $this->assertNull($bed1->reserved_until);
        $this->assertNull($bed2->reserved_until);

        // Verify redirect to confirmation
        $booking = $customer->bookings->first();
        $response->assertRedirect(route('booking.confirmation', $booking));
    }

    public function test_advance_calculation_minimum_3000()
    {
        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);
        $room = Room::create([
            'branch_id' => $branch->id,
            'room_number' => 'R101',
            'capacity' => 2,
            'type' => 'Non-AC',
            'gender_allowed' => 'Female',
        ]);

        $bed = Bed::create([
            'room_id' => $room->id,
            'bed_number' => 'B1',
            'monthly_rent' => 2000, // Less than 3000
            'status' => 'vacant',
        ]);

        $this->post(route('booking.select-beds'), ['bed_ids' => [$bed->id]]);

        $response = $this->post(route('booking.process-payment'), [
            'name' => 'Test User',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'payment_method' => 'upi',
        ]);

        // Verify minimum advance of 3000 is charged
        $this->assertDatabaseHas('payments', [
            'amount' => 3000,
            'payment_type' => 'Advance',
        ]);
    }
}
