<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\Razorpay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function __construct(private Razorpay $razorpay)
    {
    }

    public function index()
    {
        $plans = Plan::orderBy('price')->get();

        return view('super-admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('super-admin.plans.create');
    }

    /** Also creates the matching Plan on Razorpay's side when Razorpay is configured, so a Subscription can be started against it right away. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:1',
            'billing_interval' => ['required', Rule::in(array_keys(Plan::INTERVALS))],
            'max_branches' => 'nullable|integer|min:1',
            'max_beds' => 'nullable|integer|min:1',
            'max_staff' => 'nullable|integer|min:1',
        ]);

        do {
            $slug = Str::slug($validated['name']) . '-' . Str::lower(Str::random(4));
        } while (Plan::where('slug', $slug)->exists());

        $razorpayPlanId = null;

        if ($this->razorpay->isConfigured()) {
            try {
                $remotePlan = $this->razorpay->createPlan($validated['name'], (float) $validated['price'], $validated['billing_interval']);
                $razorpayPlanId = $remotePlan->id;
            } catch (\Exception $e) {
                Log::error('Failed to create matching Razorpay plan', ['error' => $e->getMessage()]);

                return back()->withInput()->withErrors([
                    'name' => 'Could not create the matching plan on Razorpay: ' . $e->getMessage(),
                ]);
            }
        }

        Plan::create([
            ...$validated,
            'slug' => $slug,
            'razorpay_plan_id' => $razorpayPlanId,
            'is_active' => true,
        ]);

        $message = $razorpayPlanId
            ? 'Plan created and linked to Razorpay.'
            : 'Plan created. Razorpay is not configured, so subscriptions on this plan can only be tracked manually for now.';

        return redirect()->route('super-admin.plans.index')->with('success', $message);
    }

    public function edit(Plan $plan)
    {
        return view('super-admin.plans.edit', compact('plan'));
    }

    /** Price/interval/razorpay_plan_id are fixed once created — Razorpay plans are immutable, so changing them here would silently desync from what's actually billed. Create a new plan instead. */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'max_branches' => 'nullable|integer|min:1',
            'max_beds' => 'nullable|integer|min:1',
            'max_staff' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $plan->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('super-admin.plans.index')->with('success', 'Plan updated.');
    }
}
