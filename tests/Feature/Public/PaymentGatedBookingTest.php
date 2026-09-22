<?php

namespace Tests\Feature\Public;

use App\Models\Bed;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Room;
use App\Services\Razorpay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatedBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Simulate a deployment that has configured Razorpay, without ever calling its API
        // during processPayment (it only checks isConfigured() at that point).
        config(['services.razorpay.key' => 'rzp_test_key', 'services.razorpay.secret' => 'rzp_test_secret']);
    }

    private function makeBed(): Bed
    {
        $branch = Branch::create(['name' => 'Test Branch', 'address' => 'Test Address']);
        $room = $branch->rooms()->create(['room_number' => '101', 'capacity' => 2, 'type' => 'AC', 'gender_allowed' => 'Female']);

        return $room->beds()->create(['bed_number' => '101-A', 'monthly_rent' => 5000, 'status' => 'vacant']);
    }

    public function test_online_booking_is_held_pending_payment_when_razorpay_is_configured()
    {
        $bed = $this->makeBed();

        $this->post(route('booking.select-beds'), ['bed_ids' => [$bed->id]]);

        $this->post(route('booking.process-payment'), [
            'bed_ids' => [$bed->id],
            'name' => 'Jane Doe',
            'phone' => '9876500011',
            'address' => 'Test Address',
            'check_in_date' => now()->addDay()->format('Y-m-d'),
            'payment_method' => 'upi',
            'accept_terms' => '1',
        ]);

        $this->assertDatabaseHas('bookings', ['bed_id' => $bed->id, 'status' => 'pending_payment']);

        $bed->refresh();
        $this->assertSame('reserved', $bed->status);
        $this->assertNotNull($bed->reserved_until);
        $this->assertTrue($bed->reserved_until->isFuture());
    }

    public function test_verifying_the_advance_payment_activates_the_booking_and_clears_the_hold()
    {
        $bed = $this->makeBed();
        $bed->update(['status' => 'reserved', 'reserved_until' => now()->addMinutes(30)]);

        $customer = Customer::create([
            'customer_code' => 'SS-GATE-0001', 'name' => 'Jane Doe', 'phone' => '9876500012',
            'password' => bcrypt('9876500012'), 'dob' => now()->subYears(20),
            'address' => 'Addr', 'guardian_phone' => '9876500012',
        ]);

        $booking = $customer->bookings()->create([
            'booking_reference' => 'BK-GATE0001', 'bed_id' => $bed->id, 'check_in_date' => now(),
            'status' => Booking::STATUS_PENDING_PAYMENT, 'advance_paid' => 0,
        ]);

        $payment = $customer->payments()->create([
            'booking_id' => $booking->id, 'amount' => 5000, 'payment_type' => 'Advance',
            'payment_method' => 'pending', 'transaction_ref' => 'PENDING-GATE1', 'status' => 'pending',
        ]);

        $this->mock(Razorpay::class, function ($mock) {
            $mock->shouldReceive('verifySignature')->once()->andReturn(null);
            $mock->shouldReceive('fetchPayment')->once()->andReturn(
                tap(\Mockery::mock(), fn ($m) => $m->shouldReceive('toArray')->andReturn(['method' => 'upi']))
            );
        });

        $response = $this->postJson(route('booking.advance.verify', $booking), [
            'razorpay_order_id' => 'order_test',
            'razorpay_payment_id' => 'pay_test',
            'razorpay_signature' => 'sig_test',
        ]);

        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => Booking::STATUS_ACTIVE]);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
        $this->assertNull($bed->fresh()->reserved_until);
        $this->assertSame('reserved', $bed->fresh()->status); // still not occupied — that only happens at physical check-in
    }

    public function test_webhook_settles_an_advance_purpose_order_the_browser_never_confirmed()
    {
        config(['services.razorpay.webhook_secret' => 'test-webhook-secret']);

        $bed = $this->makeBed();
        $bed->update(['status' => 'reserved', 'reserved_until' => now()->addMinutes(30)]);

        $customer = Customer::create([
            'customer_code' => 'SS-GATE-0002', 'name' => 'John Doe', 'phone' => '9876500013',
            'password' => bcrypt('9876500013'), 'dob' => now()->subYears(20),
            'address' => 'Addr', 'guardian_phone' => '9876500013',
        ]);

        $booking = $customer->bookings()->create([
            'booking_reference' => 'BK-GATE0002', 'bed_id' => $bed->id, 'check_in_date' => now(),
            'status' => Booking::STATUS_PENDING_PAYMENT, 'advance_paid' => 0,
        ]);

        $payment = $customer->payments()->create([
            'booking_id' => $booking->id, 'amount' => 5000, 'payment_type' => 'Advance',
            'payment_method' => 'pending', 'transaction_ref' => 'PENDING-GATE2', 'status' => 'pending',
        ]);

        $order = (object) [
            'id' => 'order_webhook_test',
            'notes' => (object) ['purpose' => 'advance', 'booking_id' => $booking->id, 'payment_id' => $payment->id],
        ];

        $this->mock(Razorpay::class, function ($mock) use ($order) {
            $mock->shouldReceive('fetchOrder')->once()->andReturn($order);
        });

        $payload = json_encode([
            'event' => 'payment.captured',
            'payload' => ['payment' => ['entity' => [
                'id' => 'pay_webhook_test',
                'order_id' => 'order_webhook_test',
                'method' => 'upi',
            ]]],
        ]);

        $signature = hash_hmac('sha256', $payload, 'test-webhook-secret');

        $response = $this->call('POST', route('webhook.razorpay'), [], [], [], [
            'HTTP_X-Razorpay-Signature' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $response->assertOk();
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => Booking::STATUS_ACTIVE]);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid', 'razorpay_payment_id' => 'pay_webhook_test']);
        $this->assertNull($bed->fresh()->reserved_until);
    }
}
