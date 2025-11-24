<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\Bed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with(['bookings.bed.room.branch'])->latest()->get();
        return view('admin.customers.index', compact('customers'));
    }

    public function create(Request $request)
    {
        $branches = Branch::with([
            'rooms.beds' => function ($q) {
                $q->whereIn('status', ['vacant', 'reserved']);
            }
        ])->get();
        
        // Check if there's a booking_id (coming from bookings page)
        $booking = null;
        if ($request->has('booking_id')) {
            $booking = \App\Models\Booking::with(['customer', 'bed.room.branch'])->find($request->booking_id);
        }
        
        return view('admin.customers.create', compact('branches', 'booking'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'dob' => 'required|date',
            'address' => 'required|string',
            'guardian_phone' => 'required|string|max:20',
            'work_details' => 'nullable|string',
            'photo' => 'required|image|max:2048',
            'id_proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'bed_id' => 'required_without:booking_id|exists:beds,id',
            'check_in_date' => 'required|date',
            'stay_type' => 'required|in:permanent,day_basis',
            'advance_amount' => 'required|numeric|min:0',
        ]);

        \DB::beginTransaction();
        try {
            // Handle File Uploads
            $photoPath = $request->file('photo')->store('customers/photos', 'public');
            $proofPath = $request->file('id_proof')->store('customers/proofs', 'public');

            // Update existing customer or create new
            if ($request->customer_id) {
                // Update existing customer from online booking
                $customer = Customer::find($request->customer_id);
                $customer->update([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'dob' => $request->dob,
                    'address' => $request->address,
                    'guardian_phone' => $request->guardian_phone,
                    'work_details' => $request->work_details,
                    'photo_path' => $photoPath,
                    'id_proof_path' => $proofPath,
                    'password' => bcrypt($request->phone), // Update password to phone
                ]);
            } else {
                // Create new walk-in customer
                $customerCode = 'SS-' . date('Y') . '-' . str_pad(Customer::count() + 1, 4, '0', STR_PAD_LEFT);
                $customer = Customer::create([
                    'customer_code' => $customerCode,
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'password' => bcrypt($request->phone),
                    'dob' => $request->dob,
                    'address' => $request->address,
                    'guardian_phone' => $request->guardian_phone,
                    'work_details' => $request->work_details,
                    'photo_path' => $photoPath,
                    'id_proof_path' => $proofPath,
                ]);
            }

            // Handle booking
            if ($request->booking_id) {
                // Update existing booking
                $booking = \App\Models\Booking::find($request->booking_id);
                $booking->update([
                    'status' => 'checked_in',
                    'check_in_date' => $request->check_in_date,
                    'advance_paid' => $request->advance_amount,
                ]);
                $bed = $booking->bed;
            } else {
                // Create new booking for walk-in
                $bed = Bed::find($request->bed_id);
                $bookingReference = 'BK-' . strtoupper(\Str::random(8));
                $booking = $customer->bookings()->create([
                    'booking_reference' => $bookingReference,
                    'bed_id' => $bed->id,
                    'check_in_date' => $request->check_in_date,
                    'status' => 'checked_in',
                    'advance_paid' => $request->advance_amount,
                ]);
            }

            // Update bed status
            $bed->update(['status' => 'occupied']);

            // Create payment record
            $customer->payments()->create([
                'booking_id' => $booking->id,
                'amount' => $request->advance_amount,
                'payment_type' => 'Advance',
                'payment_method' => $request->payment_method ?? 'cash',
                'transaction_ref' => 'ADV-' . strtoupper(\Str::random(12)),
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            \DB::commit();
            return redirect()->route('admin.customers.index')->with('success', 'Customer check-in completed successfully!');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => 'Failed to complete check-in: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Customer $customer)
    {
        $customer->load(['bookings.bed.room.branch', 'payments', 'requests']);
        return view('admin.customers.show', compact('customer'));
    }
}
