<?php

namespace Tests\Feature\Admin;

use App\Models\IntakeApplication;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;

class IntakeApplicationManagementTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    /** Submits a real application through the public form so it's created exactly the way production data is. */
    private function submitApplication(Tenant $tenant, array $overrides = []): void
    {
        $this->post(route('register.store', $tenant), array_merge([
            'name' => 'Applicant One', 'phone' => '9876500080', 'dob' => '2001-01-01',
            'address' => 'Some address', 'guardian_phone' => '9876500081',
        ], $overrides));
    }

    public function test_admin_sees_the_shareable_intake_link_and_pending_applications()
    {
        $tenant = $this->bindTenant();
        $this->submitApplication($tenant);
        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true], $tenant);

        $response = $this->get(route('admin.intake-applications.index'));

        $response->assertOk();
        $response->assertSee('Applicant One');
        $response->assertSee(route('register.create', $tenant));
    }

    public function test_admin_cannot_see_another_tenants_applications()
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $this->submitApplication($tenantA, ['name' => 'Tenant A Applicant']);
        $this->submitApplication($tenantB, ['name' => 'Tenant B Applicant']);

        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true], $tenantB);

        $response = $this->get(route('admin.intake-applications.index'));

        $response->assertOk();
        $response->assertSee('Tenant B Applicant');
        $response->assertDontSee('Tenant A Applicant');
    }

    public function test_admin_cannot_view_another_tenants_application_directly_by_id()
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $this->submitApplication($tenantA);
        $application = IntakeApplication::first();

        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true], $tenantB);

        $this->get(route('admin.intake-applications.show', $application))->assertNotFound();
    }

    public function test_admin_can_reject_a_pending_application()
    {
        $tenant = $this->bindTenant();
        $this->submitApplication($tenant);
        $application = IntakeApplication::first();
        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true], $tenant);

        $response = $this->patch(route('admin.intake-applications.reject', $application), [
            'admin_notes' => 'Already found a room elsewhere.',
        ]);

        $response->assertRedirect(route('admin.intake-applications.index'));
        $application->refresh();
        $this->assertSame(IntakeApplication::STATUS_REJECTED, $application->status);
        $this->assertSame('Already found a room elsewhere.', $application->admin_notes);
        $this->assertNotNull($application->reviewed_at);
    }

    public function test_the_check_in_form_pre_fills_from_a_pending_application()
    {
        $tenant = $this->bindTenant();
        $this->submitApplication($tenant, ['name' => 'Pre Fill Me', 'phone' => '9876500082']);
        $application = IntakeApplication::first();
        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true], $tenant);

        $response = $this->get(route('admin.customers.create', ['intake' => $application->id]));

        $response->assertOk();
        $response->assertSee('Pre Fill Me');
        $response->assertSee('9876500082');
    }

    public function test_converting_an_application_creates_the_customer_and_marks_it_converted()
    {
        $tenant = $this->bindTenant();
        $branch = \App\Models\Branch::create(['name' => 'Main Branch', 'address' => 'Addr']);
        $room = $branch->rooms()->create(['room_number' => '1', 'capacity' => 1, 'type' => 'AC', 'gender_allowed' => 'Male']);
        $bed = $room->beds()->create(['bed_number' => '1-A', 'monthly_rent' => 5000, 'status' => 'vacant']);

        $this->submitApplication($tenant, ['name' => 'Convert Me']);
        $application = IntakeApplication::first();
        $owner = $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true], $tenant);

        $response = $this->post(route('admin.customers.store'), [
            'intake_id' => $application->id,
            'name' => 'Convert Me',
            'phone' => '9876500080',
            'dob' => '2001-01-01',
            'address' => 'Some address',
            'guardian_phone' => '9876500081',
            'branch_id' => $branch->id,
            'bed_id' => $bed->id,
            'check_in_date' => now()->format('Y-m-d'),
            'stay_type' => 'permanent',
            'advance_amount' => 5000,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('admin.customers.index'));
        $application->refresh();
        $this->assertSame(IntakeApplication::STATUS_CONVERTED, $application->status);
        $this->assertNotNull($application->customer_id);
        $this->assertDatabaseHas('customers', ['name' => 'Convert Me', 'tenant_id' => $tenant->id]);
    }

    public function test_an_already_converted_application_cannot_be_reused_to_prefill_check_in()
    {
        $tenant = $this->bindTenant();
        $this->submitApplication($tenant);
        $application = IntakeApplication::first();
        $application->update(['status' => IntakeApplication::STATUS_CONVERTED]);
        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true], $tenant);

        $response = $this->get(route('admin.customers.create', ['intake' => $application->id]));

        $response->assertOk();
        $response->assertDontSee('Applicant One');
    }
}
