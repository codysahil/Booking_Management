<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Due;
use App\Services\PaymentRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DueController extends Controller
{
    public function __construct(private PaymentRecorder $recorder)
    {
    }

    /** Add a fine/EB/damage/other due to a customer's account. */
    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'due_type' => 'required|string|in:' . implode(',', array_keys(Due::TYPES)),
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date',
        ]);

        $customer->dues()->create($validated + ['status' => 'pending']);

        return back()->with('success', 'Due added to customer account.');
    }

    public function markPaid(Request $request, Due $due)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        $this->recorder->settle(
            $due->customer,
            collect(),
            collect([$due]),
            $validated['payment_method'],
            $validated['transaction_id'] ?? null,
            ['recorded_by' => Auth::id()],
        );

        return back()->with('success', 'Due marked as paid!');
    }

    public function destroy(Due $due)
    {
        $due->delete();

        return back()->with('success', 'Due removed.');
    }
}
