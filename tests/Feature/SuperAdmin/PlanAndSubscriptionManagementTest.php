<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanAndSubscriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    private function loginAsSuperAdmin(): User
    {
        $superAdmin = User::factory()->create([
            'tenant_id' => null,
            'is_super_admin' => true,
            'is_active' => false,
            'password' => bcrypt('super-secret-1'),
        ]);

        $this->post(route('super-admin.login'), [
            'email' => $superAdmin->email,
            'password' => 'super-secret-1',
        ]);

        return $superAdmin;
    }

    /** Razorpay isn't configured in tests, so this exercises the fallback path: a plan can still be created and tracked manually. */
    public function test_super_admin_can_create_a_plan_without_razorpay_configured()
    {
        $this->loginAsSuperAdmin();

        $response = $this->post(route('super-admin.plans.store'), [
            'name' => 'Standard',
            'price' => 1999,
            'billing_interval' => Plan::INTERVAL_MONTHLY,
        ]);

        $response->assertRedirect(route('super-admin.plans.index'));
        $this->assertDatabaseHas('plans', ['name' => 'Standard', 'price' => 1999.00, 'razorpay_plan_id' => null]);
    }

    public function test_super_admin_can_edit_a_plans_limits_and_active_state()
    {
        $this->loginAsSuperAdmin();
        $plan = Plan::factory()->create(['is_active' => true]);

        $response = $this->put(route('super-admin.plans.update', $plan), [
            'name' => 'Standard Plus',
            'max_beds' => 50,
            'is_active' => 0,
        ]);

        $response->assertRedirect(route('super-admin.plans.index'));
        $this->assertDatabaseHas('plans', ['id' => $plan->id, 'name' => 'Standard Plus', 'max_beds' => 50, 'is_active' => false]);
    }

    public function test_super_admin_can_assign_a_plan_to_a_tenant()
    {
        $this->loginAsSuperAdmin();
        $tenant = Tenant::factory()->create();
        Subscription::factory()->create(['tenant_id' => $tenant->id, 'status' => Subscription::STATUS_TRIALING]);
        $plan = Plan::factory()->create();

        $response = $this->put(route('super-admin.tenants.subscription.assign-plan', $tenant), [
            'plan_id' => $plan->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subscriptions', ['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);
    }

    public function test_assigning_a_plan_creates_a_subscription_row_if_the_tenant_has_none()
    {
        $this->loginAsSuperAdmin();
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->create();

        $this->put(route('super-admin.tenants.subscription.assign-plan', $tenant), ['plan_id' => $plan->id]);

        $this->assertDatabaseHas('subscriptions', ['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);
    }

    public function test_super_admin_can_manually_override_a_tenants_subscription_status()
    {
        $this->loginAsSuperAdmin();
        $tenant = Tenant::factory()->create();
        Subscription::factory()->create(['tenant_id' => $tenant->id, 'status' => Subscription::STATUS_EXPIRED]);

        $response = $this->put(route('super-admin.tenants.subscription.update', $tenant), [
            'status' => Subscription::STATUS_ACTIVE,
            'current_period_end' => now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $tenant->refresh();
        $this->assertSame(Subscription::STATUS_ACTIVE, $tenant->subscription->status);
        $this->assertNull($tenant->subscription->grace_ends_at);
    }

    public function test_starting_a_razorpay_subscription_without_a_plan_shows_an_error()
    {
        $this->loginAsSuperAdmin();
        $tenant = Tenant::factory()->create();
        Subscription::factory()->create(['tenant_id' => $tenant->id, 'status' => Subscription::STATUS_TRIALING]);

        $response = $this->post(route('super-admin.tenants.subscription.start-razorpay', $tenant));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_a_tenants_own_admin_cannot_manage_plans_or_subscriptions()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $this->actingAs($admin)->get(route('super-admin.plans.index'))->assertNotFound();
        $this->actingAs($admin)->get(route('super-admin.tenants.index'))->assertNotFound();
    }
}
