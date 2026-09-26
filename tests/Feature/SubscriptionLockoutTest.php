<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;

/**
 * A tenant whose subscription isn't writable must still be able to view all
 * their data (GET) but never create/edit/delete anything (any other verb)
 * until they renew.
 */
class SubscriptionLockoutTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    private function loginWithSubscription(string $status, array $subscriptionAttrs = []): User
    {
        $admin = $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        Subscription::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => $status,
            ...$subscriptionAttrs,
        ]);

        return $admin;
    }

    private function attemptCreateBranch()
    {
        return $this->post(route('admin.branches.store'), [
            'name' => 'New Branch',
            'address' => 'Somewhere',
        ]);
    }

    public function test_an_expired_tenant_can_still_view_data_but_not_write()
    {
        $this->loginWithSubscription(Subscription::STATUS_EXPIRED);

        $this->get(route('admin.customers.index'))->assertOk();

        $response = $this->attemptCreateBranch();
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('branches', ['name' => 'New Branch']);
    }

    public function test_a_cancelled_tenant_cannot_write()
    {
        $this->loginWithSubscription(Subscription::STATUS_CANCELLED);

        $this->attemptCreateBranch();
        $this->assertDatabaseMissing('branches', ['name' => 'New Branch']);
    }

    public function test_a_trialing_tenant_can_write()
    {
        $this->loginWithSubscription(Subscription::STATUS_TRIALING);

        $response = $this->attemptCreateBranch();
        $response->assertRedirect(route('admin.branches.index'));
        $this->assertDatabaseHas('branches', ['name' => 'New Branch']);
    }

    public function test_an_active_tenant_can_write()
    {
        $this->loginWithSubscription(Subscription::STATUS_ACTIVE);

        $response = $this->attemptCreateBranch();
        $response->assertRedirect(route('admin.branches.index'));
        $this->assertDatabaseHas('branches', ['name' => 'New Branch']);
    }

    public function test_an_internal_tenant_can_write()
    {
        $this->loginWithSubscription(Subscription::STATUS_INTERNAL);

        $response = $this->attemptCreateBranch();
        $response->assertRedirect(route('admin.branches.index'));
        $this->assertDatabaseHas('branches', ['name' => 'New Branch']);
    }

    public function test_a_past_due_tenant_within_grace_can_still_write()
    {
        $this->loginWithSubscription(Subscription::STATUS_PAST_DUE, ['grace_ends_at' => now()->addDay()]);

        $response = $this->attemptCreateBranch();
        $response->assertRedirect(route('admin.branches.index'));
        $this->assertDatabaseHas('branches', ['name' => 'New Branch']);
    }

    public function test_a_past_due_tenant_past_grace_cannot_write()
    {
        $this->loginWithSubscription(Subscription::STATUS_PAST_DUE, ['grace_ends_at' => now()->subDay()]);

        $this->attemptCreateBranch();
        $this->assertDatabaseMissing('branches', ['name' => 'New Branch']);
    }

    /** A tenant with no subscription row at all (a data gap, not an expiry) must not be locked out by it. */
    public function test_a_tenant_with_no_subscription_row_can_still_write()
    {
        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $response = $this->attemptCreateBranch();
        $response->assertRedirect(route('admin.branches.index'));
        $this->assertDatabaseHas('branches', ['name' => 'New Branch']);
    }
}
