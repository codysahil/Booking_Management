<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Keeps each tenant's Subscription row in sync with Razorpay's own view of
 * the mandate. Deliberately separate from RazorpayWebhookController (which
 * handles residents' own due/charge payments) — different concern, different
 * blast radius, own webhook secret.
 */
class RazorpaySubscriptionWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $webhookSecret = config('services.razorpay.subscription_webhook_secret');

        if (! $webhookSecret) {
            Log::error('Razorpay subscription webhook secret is not configured; refusing webhook.');

            return response()->json(['error' => 'Webhook not configured'], 500);
        }

        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        if (! $signature || ! hash_equals($expectedSignature, $signature)) {
            Log::warning('Razorpay subscription webhook signature verification failed');

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);
        $eventType = $event['event'] ?? '';
        $entity = $event['payload']['subscription']['entity'] ?? [];
        $razorpaySubscriptionId = $entity['id'] ?? null;

        Log::info('Razorpay subscription webhook received', ['event' => $eventType, 'subscription_id' => $razorpaySubscriptionId]);

        if (! $razorpaySubscriptionId) {
            return response()->json(['error' => 'Missing subscription id'], 400);
        }

        $subscription = Subscription::where('razorpay_subscription_id', $razorpaySubscriptionId)->first();

        if (! $subscription) {
            Log::warning('Subscription webhook for unknown subscription id', ['subscription_id' => $razorpaySubscriptionId]);

            return response()->json(['status' => 'ignored']);
        }

        match ($eventType) {
            'subscription.activated' => $this->activated($subscription, $entity),
            'subscription.charged' => $this->charged($subscription, $entity),
            'subscription.pending' => $this->pending($subscription),
            'subscription.halted' => $this->halted($subscription),
            'subscription.cancelled', 'subscription.completed' => $this->cancelled($subscription),
            default => null,
        };

        $subscription->update(['last_webhook_event' => $eventType]);

        return response()->json(['status' => 'ok']);
    }

    private function activated(Subscription $subscription, array $entity): void
    {
        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
            'current_period_start' => $this->toDate($entity['current_start'] ?? null),
            'current_period_end' => $this->toDate($entity['current_end'] ?? null),
            'grace_ends_at' => null,
        ]);
    }

    private function charged(Subscription $subscription, array $entity): void
    {
        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
            'current_period_start' => $this->toDate($entity['current_start'] ?? null),
            'current_period_end' => $this->toDate($entity['current_end'] ?? null),
            'grace_ends_at' => null,
        ]);
    }

    /** A charge failed — Razorpay is still retrying. Give it a few days before locking the tenant out. */
    private function pending(Subscription $subscription): void
    {
        $subscription->update([
            'status' => Subscription::STATUS_PAST_DUE,
            'grace_ends_at' => now()->addDays(3),
        ]);
    }

    /** Razorpay gave up retrying. */
    private function halted(Subscription $subscription): void
    {
        $subscription->update([
            'status' => Subscription::STATUS_EXPIRED,
            'grace_ends_at' => null,
        ]);
    }

    private function cancelled(Subscription $subscription): void
    {
        $subscription->update([
            'status' => Subscription::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'grace_ends_at' => null,
        ]);
    }

    private function toDate(?int $timestamp): ?string
    {
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }
}
