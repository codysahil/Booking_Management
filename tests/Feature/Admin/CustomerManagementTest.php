<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Branch;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]));
    }

    public function test_admin_can_view_create_customer_page()
    {
        $response = $this->get(route('admin.customers.create'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_customer_with_bed_assignment()
    {
        Storage::fake('public');

        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);
        $room = $branch->rooms()->create([
            'room_number' => '101',
            'capacity' => 2,
            'type' => 'AC',
            'gender_allowed' => 'Female',
        ]);
        $bed = $room->beds()->create([
            'bed_number' => '101-A',
            'monthly_rent' => 5000,
            'status' => 'vacant',
        ]);

        $photo = UploadedFile::fake()->image('photo.jpg');
        $idProof = UploadedFile::fake()->create('id_proof.pdf');

        $response = $this->post(route('admin.customers.store'), [
            'name' => 'Jane Doe',
            'phone' => '9876543210',
            'email' => 'jane@example.com',
            'dob' => '2000-01-01',
            'address' => '123 Main St',
            'guardian_phone' => '1234567890',
            'photo' => $photo,
            'id_proof' => $idProof,
            'bed_id' => $bed->id,
            'check_in_date' => '2023-11-01',
            'stay_type' => 'permanent',
            'advance_amount' => 5000,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('admin.customers.index'));

        $this->assertDatabaseHas('customers', ['email' => 'jane@example.com']);
        $this->assertDatabaseHas('beds', ['id' => $bed->id, 'status' => 'occupied']);
        $this->assertDatabaseHas('bookings', ['bed_id' => $bed->id, 'status' => 'active']);

        // Assert files were stored
        Storage::disk('public')->assertExists('customers/photos/' . $photo->hashName());
        Storage::disk('public')->assertExists('customers/proofs/' . $idProof->hashName());
    }

    /** A customer booked online, never paid, and shows up in person — staff checks them in with cash instead. */
    public function test_admin_can_check_in_a_pending_payment_online_booking_with_cash()
    {
        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);
        $room = $branch->rooms()->create(['room_number' => '101', 'capacity' => 2, 'type' => 'AC', 'gender_allowed' => 'Female']);
        $bed = $room->beds()->create(['bed_number' => '101-A', 'monthly_rent' => 5000, 'status' => 'reserved', 'reserved_until' => now()->addMinutes(20)]);

        $customer = Customer::create([
            'customer_code' => 'SS-TEST-0001',
            'name' => 'Online Booker',
            'phone' => '9876500009',
            'password' => bcrypt('9876500009'),
            'dob' => now()->subYears(20),
            'address' => 'To be collected on check-in',
            'guardian_phone' => '9876500009',
        ]);

        $booking = $customer->bookings()->create([
            'booking_reference' => 'BK-TESTPEND',
            'bed_id' => $bed->id,
            'check_in_date' => now(),
            'status' => 'pending_payment',
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

        $response = $this->post(route('admin.customers.store'), [
            'customer_id' => $customer->id,
            'booking_id' => $booking->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'dob' => '2000-01-01',
            'address' => '123 Main St',
            'guardian_phone' => '1234567890',
            'check_in_date' => now()->format('Y-m-d'),
            'stay_type' => 'permanent',
            'advance_amount' => 5000,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('admin.customers.index'));

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'active']);
        $this->assertDatabaseHas('beds', ['id' => $bed->id, 'status' => 'occupied', 'reserved_until' => null]);
        $this->assertDatabaseHas('payments', ['booking_id' => $booking->id, 'status' => 'paid', 'payment_method' => 'cash']);
        $this->assertSame(1, \App\Models\Payment::where('booking_id', $booking->id)->count());

        // recorded_by must be set to the staff member — this is what tells
        // PaymentRecorder::notifyStaff() not to ping staff about their own cash entry.
        $this->assertNotNull(\App\Models\Payment::where('booking_id', $booking->id)->first()->recorded_by);
    }

    /**
     * A multi-bed online booking shares one Advance payment across bookings, attached
     * to only one of them. Physically checking in the *other* bed — the one the
     * payment row isn't attached to, already settled online — must not create a
     * second, duplicate payment.
     */
    public function test_admin_check_in_does_not_duplicate_payment_for_an_already_paid_multi_bed_booking()
    {
        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);
        $room = $branch->rooms()->create(['room_number' => '101', 'capacity' => 2, 'type' => 'AC', 'gender_allowed' => 'Female']);
        $bedA = $room->beds()->create(['bed_number' => '101-A', 'monthly_rent' => 5000, 'status' => 'reserved']);
        $bedB = $room->beds()->create(['bed_number' => '101-B', 'monthly_rent' => 5000, 'status' => 'reserved']);

        $customer = Customer::create([
            'customer_code' => 'SS-TEST-0002',
            'name' => 'Group Booker',
            'phone' => '9876500010',
            'password' => bcrypt('9876500010'),
            'dob' => now()->subYears(20),
            'address' => 'Addr',
            'guardian_phone' => '9876500010',
        ]);

        $bookingA = $customer->bookings()->create([
            'booking_reference' => 'BK-GROUP01', 'bed_id' => $bedA->id, 'check_in_date' => now(),
            'status' => 'active', 'advance_paid' => 5000,
        ]);
        $bookingB = $customer->bookings()->create([
            'booking_reference' => 'BK-GROUP01', 'bed_id' => $bedB->id, 'check_in_date' => now(),
            'status' => 'active', 'advance_paid' => 5000,
        ]);

        // The one shared Advance payment is attached to bookingA only, already paid —
        // exactly what PaymentRecorder::settleAdvance() now produces for a group.
        $customer->payments()->create([
            'booking_id' => $bookingA->id, 'amount' => 10000, 'payment_type' => 'Advance',
            'payment_method' => 'upi', 'transaction_ref' => 'PAY-GROUP01', 'status' => 'paid', 'paid_at' => now(),
        ]);

        // Staff physically check in bookingB — the bed the payment row is NOT attached to.
        $response = $this->post(route('admin.customers.store'), [
            'customer_id' => $customer->id,
            'booking_id' => $bookingB->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'dob' => '2000-01-01',
            'address' => '123 Main St',
            'guardian_phone' => '1234567890',
            'check_in_date' => now()->format('Y-m-d'),
            'stay_type' => 'permanent',
            'advance_amount' => 3000,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('admin.customers.index'));

        $this->assertDatabaseHas('bookings', ['id' => $bookingB->id, 'status' => 'active']);
        $this->assertDatabaseHas('beds', ['id' => $bedB->id, 'status' => 'occupied']);
        $this->assertSame(1, \App\Models\Payment::where('customer_id', $customer->id)->where('payment_type', 'Advance')->count());
    }
}
