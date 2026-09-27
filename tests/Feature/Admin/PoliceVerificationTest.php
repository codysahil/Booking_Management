<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;

class PoliceVerificationTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    private function makeCustomer(): Customer
    {
        return Customer::create([
            'customer_code' => 'SS-PV-01', 'name' => 'Resident', 'phone' => '9876500040',
            'password' => bcrypt('password'), 'dob' => now()->subYears(20),
            'address' => 'Addr', 'guardian_phone' => '9876500041',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true]);
    }

    public function test_admin_can_record_id_proof_and_mark_it_submitted()
    {
        $customer = $this->makeCustomer();

        $response = $this->patch(route('admin.customers.police-verification.update', $customer), [
            'id_proof_type' => 'Aadhaar Card',
            'id_proof_number' => '1234-5678-9012',
            'police_verification_status' => Customer::VERIFICATION_SUBMITTED,
            'police_verification_notes' => 'Filed at Sector 62 police station',
        ]);

        $response->assertRedirect(route('admin.customers.show', $customer));
        $customer->refresh();
        $this->assertSame('Aadhaar Card', $customer->id_proof_type);
        $this->assertSame('1234-5678-9012', $customer->id_proof_number);
        $this->assertSame(Customer::VERIFICATION_SUBMITTED, $customer->police_verification_status);
        $this->assertNotNull($customer->police_verification_submitted_at);
        $this->assertNull($customer->police_verification_verified_at);
    }

    public function test_marking_verified_directly_backfills_the_submitted_timestamp_too()
    {
        $customer = $this->makeCustomer();

        $this->patch(route('admin.customers.police-verification.update', $customer), [
            'police_verification_status' => Customer::VERIFICATION_VERIFIED,
        ]);

        $customer->refresh();
        $this->assertNotNull($customer->police_verification_submitted_at);
        $this->assertNotNull($customer->police_verification_verified_at);
    }

    public function test_the_submitted_timestamp_is_not_overwritten_on_a_later_save()
    {
        $customer = $this->makeCustomer();

        $this->patch(route('admin.customers.police-verification.update', $customer), [
            'police_verification_status' => Customer::VERIFICATION_SUBMITTED,
        ]);
        $firstSubmittedAt = $customer->refresh()->police_verification_submitted_at;

        $this->travel(2)->days();
        $this->patch(route('admin.customers.police-verification.update', $customer), [
            'police_verification_status' => Customer::VERIFICATION_SUBMITTED,
            'police_verification_notes' => 'Still waiting',
        ]);

        $this->assertTrue($firstSubmittedAt->equalTo($customer->refresh()->police_verification_submitted_at));
    }

    public function test_an_invalid_verification_status_is_rejected()
    {
        $customer = $this->makeCustomer();

        $response = $this->patch(route('admin.customers.police-verification.update', $customer), [
            'police_verification_status' => 'not-a-real-status',
        ]);

        $response->assertSessionHasErrors('police_verification_status');
        $this->assertSame(Customer::VERIFICATION_PENDING, $customer->fresh()->police_verification_status);
    }

    public function test_the_printable_verification_form_shows_the_residents_details()
    {
        $branch = Branch::create(['name' => 'Test Branch', 'address' => '1 Test Road']);
        $room = $branch->rooms()->create(['room_number' => '201', 'capacity' => 1, 'type' => 'AC', 'gender_allowed' => 'Male']);
        $bed = $room->beds()->create(['bed_number' => '201-A', 'monthly_rent' => 6000, 'status' => 'occupied']);

        $customer = $this->makeCustomer();
        $customer->update(['id_proof_type' => 'Passport', 'id_proof_number' => 'Z1234567']);
        $customer->bookings()->create([
            'booking_reference' => 'BK-PV01', 'bed_id' => $bed->id, 'check_in_date' => now()->subDays(3),
            'status' => 'active', 'advance_paid' => 5000,
        ]);

        $response = $this->get(route('admin.customers.police-verification.print', $customer));

        $response->assertOk();
        $response->assertSee('Resident');
        $response->assertSee('Passport');
        $response->assertSee('Z1234567');
        $response->assertSee('Room 201', false);
    }
}
