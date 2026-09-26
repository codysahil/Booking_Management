<?php

namespace Tests\Feature\Webhook;

use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RazorpaySubscriptionWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-subscription-webhook-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.razorpay.subscription_webhook_secret' => self::SECRET]);
    }

    private function postSignedWebhook(array $event)
    {
        $payload = json_encode($event);
        $signature = hash_hmac('sha256', $payload, self::SECRET);

        return $this->call(
            'POST',
            route('webhook.razorpay.subscriptions'),
            server: ['HTTP_X-Razorpay-Signature' => $signature, 'CONTENT_TYPE' => 'application/json'],
            content: $payload,
        );
    }

    private function makeSubscription(string $status = Subscription::STATUS_TRIALING): Subscription
    {
        $tenant = Tenant::factory()->create();

        return Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => $status,
            'razorpay_subscription_id' => 'sub_test123',
        ]);
    }

    public function test_rejects_a_request_with_an_invalid_signature()
    {
        $subscription = $this->makeSubscription();

        $response = $this->postJson(route('webhook.razorpay.subscriptions'), [
            'event' => 'subscription.activated',
            'payload' => ['subscription' => ['entity' => ['id' => $subscription->razorpay_subscription_id]]],
        ]);

        $response->assertStatus(400);
        $this->assertSame(Subscription::STATUS_TRIALING, $subscription->fresh()->status);
    }

    public function test_subscription_activated_marks_it_active_and_sets_the_period()
    {
        $subscription = $this->makeSubscription();

        $response = $this->postSignedWebhook([
            'event' => 'subscription.activated',
            'payload' => ['subscription' => ['entity' => [
                'id' => $subscription->razorpay_subscription_id,
                'current_start' => now()->timestamp,
                'current_end' => now()->addMonth()->timestamp,
            ]]],
        ]);

        $response->assertOk();
        $subscription->refresh();
        $this->assertSame(Subscription::STATUS_ACTIVE, $subscription->status);
        $this->assertNotNull($subscription->current_period_start);
        $this->assertNotNull($subscription->current_period_end);
    }

    public function test_subscription_charged_keeps_it_active_and_advances_the_period()
    {
        $subscription = $this->makeSubscription(Subscription::STATUS_ACTIVE);

        $response = $this->postSignedWebhook([
            'event' => 'subscription.charged',
            'payload' => ['subscription' => ['entity' => [
                'id' => $subscription->razorpay_subscription_id,
                'current_start' => now()->timestamp,
                'current_end' => now()->addMonth()->timestamp,
            ]]],
        ]);

        $response->assertOk();
        $this->assertSame(Subscription::STATUS_ACTIVE, $subscription->fresh()->status);
    }

    public function test_subscription_pending_marks_it_past_due_with_a_three_day_grace()
    {
        $subscription = $this->makeSubscription(Subscription::STATUS_ACTIVE);

        $this->postSignedWebhook([
            'event' => 'subscription.pending',
            'payload' => ['subscription' => ['entity' => ['id' => $subscription->razorpay_subscription_id]]],
        ])->assertOk();

        $subscription->refresh();
        $this->assertSame(Subscription::STATUS_PAST_DUE, $subscription->status);
        $this->assertTrue($subscription->grace_ends_at->isFuture());
        $this->assertTrue($subscription->isWritable());
    }

    public function test_subscription_halted_expires_it_immediately()
    {
        $subscription = $this->makeSubscription(Subscription::STATUS_PAST_DUE);

        $this->postSignedWebhook([
            'event' => 'subscription.halted',
            'payload' => ['subscription' => ['entity' => ['id' => $subscription->razorpay_subscription_id]]],
        ])->assertOk();

        $subscription->refresh();
        $this->assertSame(Subscription::STATUS_EXPIRED, $subscription->status);
        $this->assertFalse($subscription->isWritable());
    }

    public function test_subscription_cancelled_marks_it_cancelled()
    {
        $subscription = $this->makeSubscription(Subscription::STATUS_ACTIVE);

        $this->postSignedWebhook([
            'event' => 'subscription.cancelled',
            'payload' => ['subscription' => ['entity' => ['id' => $subscription->razorpay_subscription_id]]],
        ])->assertOk();

        $subscription->refresh();
        $this->assertSame(Subscription::STATUS_CANCELLED, $subscription->status);
        $this->assertNotNull($subscription->cancelled_at);
    }

    public function test_an_unknown_subscription_id_is_ignored_without_error()
    {
        $response = $this->postSignedWebhook([
            'event' => 'subscription.activated',
            'payload' => ['subscription' => ['entity' => ['id' => 'sub_doesnotexist']]],
        ]);

        $response->assertOk();
    }
}
