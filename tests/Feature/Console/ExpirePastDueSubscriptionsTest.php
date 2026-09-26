<?php

namespace Tests\Feature\Console;

use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpirePastDueSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_expires_a_past_due_subscription_whose_grace_has_passed()
    {
        $subscription = Subscription::factory()->create([
            'tenant_id' => Tenant::factory()->create()->id,
            'status' => Subscription::STATUS_PAST_DUE,
            'grace_ends_at' => now()->subHour(),
        ]);

        $this->artisan('subscriptions:expire-past-due')->assertSuccessful();

        $this->assertSame(Subscription::STATUS_EXPIRED, $subscription->fresh()->status);
    }

    public function test_does_not_expire_a_past_due_subscription_still_within_grace()
    {
        $subscription = Subscription::factory()->create([
            'tenant_id' => Tenant::factory()->create()->id,
            'status' => Subscription::STATUS_PAST_DUE,
            'grace_ends_at' => now()->addHour(),
        ]);

        $this->artisan('subscriptions:expire-past-due');

        $this->assertSame(Subscription::STATUS_PAST_DUE, $subscription->fresh()->status);
    }

    public function test_expires_a_trialing_subscription_whose_tenants_trial_has_ended()
    {
        $tenant = Tenant::factory()->create(['trial_ends_at' => now()->subDay()]);
        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => Subscription::STATUS_TRIALING,
        ]);

        $this->artisan('subscriptions:expire-past-due');

        $this->assertSame(Subscription::STATUS_EXPIRED, $subscription->fresh()->status);
    }

    public function test_does_not_expire_a_trialing_subscription_still_within_its_trial()
    {
        $tenant = Tenant::factory()->create(['trial_ends_at' => now()->addDay()]);
        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => Subscription::STATUS_TRIALING,
        ]);

        $this->artisan('subscriptions:expire-past-due');

        $this->assertSame(Subscription::STATUS_TRIALING, $subscription->fresh()->status);
    }

    public function test_leaves_active_and_internal_subscriptions_untouched()
    {
        $active = Subscription::factory()->create(['tenant_id' => Tenant::factory()->create()->id, 'status' => Subscription::STATUS_ACTIVE]);
        $internal = Subscription::factory()->create(['tenant_id' => Tenant::factory()->create()->id, 'status' => Subscription::STATUS_INTERNAL]);

        $this->artisan('subscriptions:expire-past-due');

        $this->assertSame(Subscription::STATUS_ACTIVE, $active->fresh()->status);
        $this->assertSame(Subscription::STATUS_INTERNAL, $internal->fresh()->status);
    }
}
