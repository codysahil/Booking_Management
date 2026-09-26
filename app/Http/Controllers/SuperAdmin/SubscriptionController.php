<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\Razorpay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function __construct(private Razorpay $razorpay)
    {
    }

    /** Assigns/changes which plan a tenant is on. Doesn't touch billing state by itself — see startRazorpaySubscription() and update() for that. */
    public function assignPlan(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'plan_id' => 'nullable|exists:plans,id',
        ]);

        $subscription = $tenant->subscription ?? $tenant->subscription()->create(['status' => Subscription::STATUS_TRIALING]);
        $subscription->update(['plan_id' => $validated['plan_id'] ?? null]);

        return back()->with('success', 'Plan updated for ' . $tenant->name . '.');
    }

    /**
     * Starts a real Razorpay Subscription mandate for the tenant's currently
     * assigned plan and returns the hosted checkout link (short_url) for the
     * hostel owner to authorize it — nothing here marks the subscription
     * active; that only happens once the subscription.activated webhook
     * confirms the owner actually completed the mandate.
     */
    public function startRazorpaySubscription(Tenant $tenant)
    {
        $subscription = $tenant->subscription;
        $plan = $subscription?->plan;

        if (! $subscription || ! $plan) {
            return back()->with('error', 'Assign a plan to this hostel first.');
        }

        if (! $plan->hasRazorpayPlan()) {
            return back()->with('error', 'This plan has no matching Razorpay plan — Razorpay may not be configured, or it was created before Razorpay was set up.');
        }

        try {
            $remoteSubscription = $this->razorpay->createSubscription($plan->razorpay_plan_id, [
                'tenant_id' => (string) $tenant->id,
                'tenant_slug' => $tenant->slug,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create Razorpay subscription', ['tenant_id' => $tenant->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Could not start the Razorpay subscription: ' . $e->getMessage());
        }

        $subscription->update([
            'razorpay_subscription_id' => $remoteSubscription->id,
            'meta' => ['short_url' => $remoteSubscription->short_url ?? null],
        ]);

        return back()->with('success', 'Subscription created — share the checkout link below with the hostel owner to activate billing.');
    }

    /** Manual override for emergencies (bank transfer, a Razorpay outage) — bypasses Razorpay entirely. */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Subscription::STATUSES))],
            'current_period_end' => 'nullable|date',
        ]);

        $subscription = $tenant->subscription ?? $tenant->subscription()->create(['status' => Subscription::STATUS_TRIALING]);

        $subscription->update([
            'status' => $validated['status'],
            'current_period_end' => $validated['current_period_end'] ?? $subscription->current_period_end,
            'grace_ends_at' => null,
        ]);

        return back()->with('success', 'Subscription status updated manually for ' . $tenant->name . '.');
    }
}
