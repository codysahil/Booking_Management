<?php

namespace Tests\Feature\Public;

use App\Models\IntakeApplication;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IntakeApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_prospect_can_view_an_active_tenants_intake_form_with_that_tenants_own_branding()
    {
        $tenant = Tenant::factory()->create();
        app()->instance('currentTenantId', $tenant->id);
        \App\Models\Setting::putMany(['hostel_name' => 'Sunrise PG']);
        app()->forgetInstance('currentTenantId');

        $response = $this->get(route('register.create', $tenant));

        $response->assertOk();
        $response->assertSee('Sunrise PG');
        $response->assertSee('Full Name');
    }

    public function test_a_suspended_tenants_intake_link_404s()
    {
        $tenant = Tenant::factory()->create(['status' => 'suspended']);

        $this->get(route('register.create', $tenant))->assertNotFound();
    }

    public function test_an_unknown_slug_404s()
    {
        $this->get('/register/no-such-hostel')->assertNotFound();
    }

    public function test_submitting_the_form_creates_a_pending_application_scoped_to_that_tenant()
    {
        Storage::fake('public');
        $tenant = Tenant::factory()->create();

        $response = $this->post(route('register.store', $tenant), [
            'name' => 'Prospective Resident',
            'phone' => '9876500099',
            'dob' => '2001-05-20',
            'address' => '12 Example Street',
            'guardian_phone' => '9876500098',
            'photo' => UploadedFile::fake()->image('photo.jpg'),
            'id_proof' => UploadedFile::fake()->create('aadhaar.pdf'),
        ]);

        $response->assertOk();
        $response->assertSee('Application Received');

        $application = IntakeApplication::first();
        $this->assertNotNull($application);
        $this->assertSame($tenant->id, $application->tenant_id);
        $this->assertSame(IntakeApplication::STATUS_PENDING, $application->status);
        $this->assertNotNull($application->photo_path);
        $this->assertNotNull($application->id_proof_path);
        Storage::disk('public')->assertExists($application->photo_path);
    }

    public function test_photo_and_id_proof_are_optional()
    {
        $tenant = Tenant::factory()->create();

        $response = $this->post(route('register.store', $tenant), [
            'name' => 'No Documents Yet',
            'phone' => '9876500097',
            'dob' => '2001-05-20',
            'address' => '12 Example Street',
            'guardian_phone' => '9876500096',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('intake_applications', ['name' => 'No Documents Yet', 'photo_path' => null]);
    }

    public function test_missing_required_fields_are_rejected()
    {
        $tenant = Tenant::factory()->create();

        $response = $this->post(route('register.store', $tenant), ['name' => 'Incomplete']);

        $response->assertSessionHasErrors(['phone', 'dob', 'address', 'guardian_phone']);
        $this->assertSame(0, IntakeApplication::count());
    }

    /**
     * Laravel's plain exists:branches,id rule runs a raw query-builder check that
     * bypasses Branch's tenant scope — without an explicit tenant_id constraint,
     * a submitter on tenant A's public link could reference tenant B's branch.
     */
    public function test_a_branch_id_belonging_to_another_tenant_is_rejected()
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        app()->instance('currentTenantId', $tenantB->id);
        $branchB = \App\Models\Branch::create(['name' => 'Tenant B Branch', 'address' => 'Addr']);
        app()->forgetInstance('currentTenantId');

        $response = $this->post(route('register.store', $tenantA), [
            'name' => 'Cross Tenant Branch', 'phone' => '9876500093', 'dob' => '2001-05-20',
            'address' => 'Addr', 'guardian_phone' => '9876500092',
            'branch_id' => $branchB->id,
        ]);

        $response->assertSessionHasErrors('branch_id');
        $this->assertSame(0, IntakeApplication::withoutGlobalScopes()->where('name', 'Cross Tenant Branch')->count());
    }

    public function test_a_branch_id_belonging_to_the_same_tenant_is_accepted()
    {
        $tenant = Tenant::factory()->create();

        app()->instance('currentTenantId', $tenant->id);
        $branch = \App\Models\Branch::create(['name' => 'Own Branch', 'address' => 'Addr']);
        app()->forgetInstance('currentTenantId');

        $response = $this->post(route('register.store', $tenant), [
            'name' => 'Own Branch Applicant', 'phone' => '9876500091', 'dob' => '2001-05-20',
            'address' => 'Addr', 'guardian_phone' => '9876500090',
            'branch_id' => $branch->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('intake_applications', ['name' => 'Own Branch Applicant', 'branch_id' => $branch->id]);
    }

    public function test_an_application_is_scoped_to_the_tenant_it_was_submitted_to_even_with_another_tenant_bound()
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        // Simulate some earlier, unrelated request having bound tenant B.
        app()->instance('currentTenantId', $tenantB->id);

        $this->post(route('register.store', $tenantA), [
            'name' => 'Cross Tenant Check', 'phone' => '9876500095', 'dob' => '2001-05-20',
            'address' => 'Addr', 'guardian_phone' => '9876500094',
        ]);

        $application = IntakeApplication::withoutGlobalScopes()->where('name', 'Cross Tenant Check')->first();
        $this->assertSame($tenantA->id, $application->tenant_id);
    }
}
