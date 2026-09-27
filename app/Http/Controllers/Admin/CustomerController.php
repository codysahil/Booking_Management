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

    public function create()
    {
        $branches = Branch::with([
            'rooms.beds' => function ($q) {
                $q->where('status', 'vacant');
            }
        ])->get();

        return view('admin.customers.create', compact('branches'));
    }

    public function store(Request $request)
    {
        \Log::info('=== CUSTOMER STORE METHOD CALLED ===');
        \Log::info('Request method: ' . $request->method());
        \Log::info('Request URL: ' . $request->fullUrl());
        \Log::info('Request data (without files):', $request->except(['photo', 'id_proof', '_token']));

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|string|email|max:255',
                'dob' => 'required|date',
                'address' => 'required|string',
                'guardian_phone' => 'required|string|max:20',
                'work_details' => 'nullable|string',
                'photo' => 'nullable|image|max:10240', // Increased to 10MB
                'id_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // Increased to 10MB
                'branch_id' => 'nullable|exists:branches,id', // Added for walk-in customers
                'bed_id' => 'required|exists:beds,id',
                'check_in_date' => 'required|date',
                'stay_type' => 'required|in:permanent,day_basis',
                'advance_amount' => 'required|numeric|min:0',
                'payment_method' => 'nullable|string', // Added payment_method
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        }

        // Handle File Uploads BEFORE transaction (Cloudinary errors shouldn't abort DB transaction)
        $photoPath = null;
        $proofPath = null;

        try {
            if ($request->hasFile('photo')) {
                \Log::info('Attempting photo upload');
                $photoPath = $request->file('photo')->store('customers/photos', 'public');
                \Log::info('Photo uploaded', ['path' => $photoPath]);
            }
            if ($request->hasFile('id_proof')) {
                \Log::info('Attempting ID proof upload');
                $proofPath = $request->file('id_proof')->store('customers/proofs', 'public');
                \Log::info('ID proof uploaded', ['path' => $proofPath]);
            }
        } catch (\Exception $e) {
            \Log::warning('File upload failed, continuing without files', [
                'error' => $e->getMessage(),
                'has_cloudinary' => !empty(config('filesystems.disks.cloudinary.cloud_name'))
            ]);
            // Continue without files instead of failing
            $photoPath = null;
            $proofPath = null;
        }

        // Generate customer code BEFORE transaction (involves DB query)
        $customerCode = $this->generateUniqueCustomerCode();
        \Log::info('Generated customer code', ['customer_code' => $customerCode]);

        // Note: Removed transaction due to Neon PostgreSQL serverless connection pooling issues
        // Each operation will be atomic on its own
        try {
            \Log::info('Starting customer creation process');

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

            \Log::info('Customer created', ['customer_id' => $customer->id]);

            $bed = Bed::find($request->bed_id);
            if (!$bed) {
                \Log::error('Bed not found', ['bed_id' => $request->bed_id]);
                throw new \Exception('Bed not found');
            }
            \Log::info('Creating new booking', ['bed_id' => $bed->id]);
            $bookingReference = 'BK-' . strtoupper(\Str::random(8));
            $booking = $customer->bookings()->create([
                'booking_reference' => $bookingReference,
                'bed_id' => $bed->id,
                'check_in_date' => $request->check_in_date,
                'status' => 'active',
                'advance_paid' => $request->advance_amount,
            ]);
            \Log::info('Booking created', ['booking_id' => $booking->id]);

            // Update bed status
            \Log::info('Updating bed status', ['bed_id' => $bed->id]);
            $bed->update(['status' => 'occupied']);

            \Log::info('Creating payment record');
            $customer->payments()->create([
                'booking_id' => $booking->id,
                'amount' => $request->advance_amount,
                'payment_type' => 'Advance',
                'payment_method' => $request->payment_method ?? 'cash',
                'transaction_ref' => 'ADV-' . strtoupper(\Str::random(12)),
                'status' => 'paid',
                'paid_at' => now(),
                'recorded_by' => auth()->id(),
            ]);
            \Log::info('Payment record created');

            \Log::info('Customer creation completed successfully', ['customer_id' => $customer->id]);
            return redirect()->route('admin.customers.index')->with('success', 'Customer check-in completed successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error during customer creation', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);

            $errorMsg = 'Database error. Please try again.';
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'unique constraint')) {
                $errorMsg = 'A customer with this phone number or email already exists.';
            }
            return back()->withErrors(['error' => $errorMsg])->withInput();
        } catch (\Exception $e) {
            \Log::error('Customer creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['photo', 'id_proof', 'password'])
            ]);
            return back()->withErrors(['error' => 'Failed to complete check-in. Please try again.'])->withInput();
        }
    }

    public function show(Customer $customer)
    {
        $customer->load(['bookings.bed.room.branch', 'payments', 'requests', 'dues' => function ($q) {
            $q->latest('due_date');
        }]);

        $pendingAmount = $customer->monthlyCharges()->unpaid()->sum('total_amount')
            + $customer->dues()->where('status', 'pending')->sum('amount');

        return view('admin.customers.show', compact('customer', 'pendingAmount'));
    }

    public function edit(Customer $customer)
    {
        $branches = Branch::with(['rooms.beds'])->get();
        return view('admin.customers.edit', compact('customer', 'branches'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'dob' => 'required|date',
            'address' => 'required|string',
            'guardian_phone' => 'required|string|max:20',
            'work_details' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'id_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        try {
            // Handle file uploads
            if ($request->hasFile('photo')) {
                // Delete old photo
                if ($customer->photo_path) {
                    Storage::delete($customer->photo_path);
                }
                $validated['photo_path'] = $request->file('photo')->store('customers/photos', 'public');
            }

            if ($request->hasFile('id_proof')) {
                // Delete old proof
                if ($customer->id_proof_path) {
                    Storage::delete($customer->id_proof_path);
                }
                $validated['id_proof_path'] = $request->file('id_proof')->store('customers/proofs', 'public');
            }

            $customer->update($validated);

            return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update customer: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Deactivate/Vacate a customer — also settles their security deposit
     * (advance_paid doubles as the deposit in this app's convention): any
     * outstanding dues/charges and an optional damage deduction come off it
     * first, and whatever's left is the refund owed.
     */
    public function deactivate(Request $request, Customer $customer)
    {
        $activeBooking = $customer->bookings()->where('status', 'active')->first();

        $deposit = (float) ($activeBooking->advance_paid ?? 0);
        $outstanding = $customer->monthlyCharges()->unpaid()->sum('total_amount')
            + $customer->dues()->where('status', 'pending')->sum('amount');

        $validated = $request->validate([
            'deposit_deduction_amount' => ['nullable', 'numeric', 'min:0', 'max:' . max($deposit - $outstanding, 0)],
            'deposit_deduction_reason' => 'nullable|string|max:500|required_with:deposit_deduction_amount',
        ]);

        $deduction = (float) ($validated['deposit_deduction_amount'] ?? 0);
        $refund = max($deposit - $outstanding - $deduction, 0);

        // Note: Removed DB transaction due to Neon PostgreSQL serverless connection pooling issues
        try {
            // Get active booking and free up the bed
            if ($activeBooking) {
                // Update booking status to completed
                $activeBooking->update([
                    'status' => 'completed',
                    'check_out_date' => now(),
                    'deposit_deduction_amount' => $deduction > 0 ? $deduction : null,
                    'deposit_deduction_reason' => $validated['deposit_deduction_reason'] ?? null,
                    'deposit_refund_amount' => $refund,
                    'settled_at' => now(),
                ]);

                // Free up the bed
                $activeBooking->bed->update(['status' => 'vacant']);
            }

            // Deactivate customer (keeps the record but prevents login)
            $customer->update([
                'is_active' => false,
            ]);

            \Log::info('Customer vacated', [
                'customer_id' => $customer->id,
                'customer_code' => $customer->customer_code,
                'booking_id' => $activeBooking?->id,
                'deposit_refund_amount' => $refund,
            ]);

            $refundNote = $activeBooking ? " Deposit settled — ₹{$refund} refund due." : '';

            return redirect()->route('admin.customers.index')
                ->with('success', "Customer {$customer->name} ({$customer->customer_code}) has been vacated successfully. The bed is now available.{$refundNote}");

        } catch (\Exception $e) {
            \Log::error('Customer deactivation failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Failed to vacate customer: ' . $e->getMessage()]);
        }
    }

    /**
     * Record or update a resident's ID proof details and police
     * verification/registration status.
     */
    public function updatePoliceVerification(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'id_proof_type' => 'nullable|string|max:50',
            'id_proof_number' => 'nullable|string|max:50',
            'police_verification_status' => 'required|in:' . implode(',', array_keys(Customer::VERIFICATION_STATUSES)),
            'police_verification_notes' => 'nullable|string|max:1000',
        ]);

        $timestamps = [];
        if ($validated['police_verification_status'] === Customer::VERIFICATION_SUBMITTED && ! $customer->police_verification_submitted_at) {
            $timestamps['police_verification_submitted_at'] = now();
        }
        if ($validated['police_verification_status'] === Customer::VERIFICATION_VERIFIED && ! $customer->police_verification_verified_at) {
            $timestamps['police_verification_verified_at'] = now();
            $timestamps['police_verification_submitted_at'] = $customer->police_verification_submitted_at ?? now();
        }

        $customer->update(array_merge([
            'id_proof_type' => $validated['id_proof_type'] ?? null,
            'id_proof_number' => $validated['id_proof_number'] ?? null,
            'police_verification_status' => $validated['police_verification_status'],
            'police_verification_notes' => $validated['police_verification_notes'] ?? null,
        ], $timestamps));

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Police verification details updated.');
    }

    /**
     * A printable tenant-verification form pre-filled with the resident's
     * details — the paperwork a PG/hostel owner hands to (or files with)
     * the local police station.
     */
    public function printPoliceVerification(Customer $customer)
    {
        $customer->load(['bookings' => fn ($q) => $q->latest('check_in_date')->with('bed.room.branch')]);

        return view('admin.customers.police-verification-print', [
            'customer' => $customer,
            'booking' => $customer->bookings->first(),
            'backUrl' => route('admin.customers.show', $customer),
        ]);
    }

    /**
     * Generate a unique random customer code
     * Format: SS-XXXX-XXXX (where X is alphanumeric)
     */
    private function generateUniqueCustomerCode(): string
    {
        do {
            // Generate random alphanumeric code: SS-XXXX-XXXX
            $code = 'SS-' . strtoupper(\Str::random(4)) . '-' . strtoupper(\Str::random(4));
        } while (Customer::where('customer_code', $code)->exists());

        return $code;
    }
}
