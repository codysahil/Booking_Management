<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyCharge;
use App\Models\Customer;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonthlyChargeController extends Controller
{
    // List all monthly charges
    public function index(Request $request)
    {
        try {
            $month = $request->get('month', now()->format('Y-m'));

            $charges = MonthlyCharge::with(['customer', 'booking.bed'])
                ->where('month_year', $month)
                ->latest()
                ->paginate(20);

            $summary = [
                'total_charges' => MonthlyCharge::where('month_year', $month)->sum('total_amount'),
                'paid' => MonthlyCharge::where('month_year', $month)->where('status', 'paid')->sum('total_amount'),
                'pending' => MonthlyCharge::where('month_year', $month)->where('status', 'pending')->sum('total_amount'),
            ];

            return view('admin.charges.index', compact('charges', 'month', 'summary'));
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle case where monthly_charges table doesn't exist yet
            if (str_contains($e->getMessage(), 'monthly_charges') && str_contains($e->getMessage(), 'does not exist')) {
                return response()->view('errors.migration-pending', [
                    'message' => 'The Monthly Charges feature is being set up. Please wait a few moments for the deployment to complete, then refresh this page.',
                    'table' => 'monthly_charges'
                ], 503);
            }
            throw $e;
        }
    }

    // Generate charges for all active customers for a specific month
    public function generate(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'month' => 'required|date_format:Y-m',
            ]);

            $month = $validated['month'];
            $generated = 0;
            $skipped = 0;

            // Get all active bookings
            $activeBookings = Booking::where('status', 'active')
                ->with(['customer', 'bed'])
                ->get();

            foreach ($activeBookings as $booking) {
                // Check if charge already exists
                $exists = MonthlyCharge::where('customer_id', $booking->customer_id)
                    ->where('month_year', $month)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Create monthly charge
                $rentAmount = $booking->bed->monthly_rent;
                $dueDate = Carbon::parse($month . '-05'); // Due on 5th of the month

                MonthlyCharge::create([
                    'customer_id' => $booking->customer_id,
                    'booking_id' => $booking->id,
                    'month_year' => $month,
                    'rent_amount' => $rentAmount,
                    'eb_amount' => 0, // Admin will add EB separately
                    'other_charges' => 0,
                    'total_amount' => $rentAmount,
                    'status' => 'pending',
                    'due_date' => $dueDate,
                ]);

                $generated++;
            }

            return redirect()->route('admin.charges.index', ['month' => $month])
                ->with('success', "Generated {$generated} charges. Skipped {$skipped} existing charges.");
        }

        return view('admin.charges.generate', compact('month'));
    }

    // Edit specific charge (add EB, other charges)
    public function edit(MonthlyCharge $charge)
    {
        $charge->load(['customer', 'booking.bed.room']);
        return view('admin.charges.edit', compact('charge'));
    }

    // Update charge
    public function update(Request $request, MonthlyCharge $charge)
    {
        $validated = $request->validate([
            'rent_amount' => 'required|numeric|min:0',
            'eb_amount' => 'required|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'other_charges_description' => 'nullable|string',
            'due_date' => 'required|date',
        ]);

        $validated['total_amount'] = $validated['rent_amount'] + $validated['eb_amount'] + ($validated['other_charges'] ?? 0);

        $charge->update($validated);

        return redirect()->route('admin.charges.index', ['month' => $charge->month_year])
            ->with('success', 'Charge updated successfully!');
    }

    // Customer-specific charges view
    public function customerCharges(Customer $customer)
    {
        $charges = $customer->monthlyCharges()
            ->with('booking.bed.room')
            ->latest('month_year')
            ->paginate(12);

        $activeBooking = $customer->bookings()->where('status', 'active')->first();

        return view('admin.charges.customer', compact('customer', 'charges', 'activeBooking'));
    }

    // Update customer's rent amount (rent increase)
    public function updateRent(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'new_rent' => 'required|numeric|min:0',
            'effective_from' => 'required|date_format:Y-m',
            'reason' => 'nullable|string',
        ]);

        $activeBooking = $customer->bookings()->where('status', 'active')->first();

        if (!$activeBooking) {
            return back()->withErrors(['error' => 'No active booking found for this customer.']);
        }

        // Update the bed's monthly rent
        $oldRent = $activeBooking->bed->monthly_rent;
        $activeBooking->bed->update([
            'monthly_rent' => $validated['new_rent']
        ]);

        // Log the rent change
        \Log::info('Rent updated', [
            'customer_id' => $customer->id,
            'booking_id' => $activeBooking->id,
            'old_rent' => $oldRent,
            'new_rent' => $validated['new_rent'],
            'effective_from' => $validated['effective_from'],
            'reason' => $validated['reason'],
        ]);

        return back()->with('success', "Rent updated from ₹{$oldRent} to ₹{$validated['new_rent']} effective from {$validated['effective_from']}");
    }

    // Mark charge as paid (manual payment)
    public function markPaid(Request $request, MonthlyCharge $charge)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        $charge->update([
            'status' => 'paid',
            'paid_date' => now(),
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'],
        ]);

        return back()->with('success', 'Charge marked as paid!');
    }

    // Delete charge
    public function destroy(MonthlyCharge $charge)
    {
        $charge->delete();
        return back()->with('success', 'Charge deleted successfully!');
    }
}
