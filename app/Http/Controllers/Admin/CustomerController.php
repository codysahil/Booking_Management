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
        \Log::info('=== CUSTOMER STORE METHOD CALLED ===');
        \Log::info('Request method: ' . $request->method());
        \Log::info('Request URL: ' . $request->fullUrl());
        \Log::info('Request data (without files):', $request->except(['photo', 'id_proof', '_token']));

        // Debug: Return JSON with request data to see what's being received
        if ($request->has('debug_mode')) {
            return response()->json([
                'received' => $request->except(['photo', 'id_proof', '_token']),
                'has_photo' => $request->hasFile('photo'),
                'has_proof' => $request->hasFile('id_proof'),
            ]);
        }

        \Log::info('Customer store method called', $request->all());

        try {
            $validated = $request->validate([
                'customer_id' => 'nullable|exists:customers,id',
                'booking_id' => 'nullable|exists:bookings,id',
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
                'bed_id' => 'nullable|exists:beds,id',
                'check_in_date' => 'required|date',
                'stay_type' => 'required|in:permanent,day_basis',
                'advance_amount' => 'required|numeric|min:0',
                'payment_method' => 'nullable|string', // Added payment_method
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            // Temporary: Return JSON to see errors on Railway
            if (app()->environment('production')) {
                return response()->json(['validation_errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        }

        // Validate bed_id is present when not updating existing booking
        if (!$request->booking_id && !$request->bed_id) {
            return back()->withErrors(['bed_id' => 'Please select a bed'])->withInput();
        }

        // Handle File Uploads BEFORE transaction (Cloudinary errors shouldn't abort DB transaction)
        $photoPath = null;
        $proofPath = null;

        try {
            if ($request->hasFile('photo')) {
                \Log::info('Attempting photo upload');
                $photoPath = $request->file('photo')->store('customers/photos');
                \Log::info('Photo uploaded', ['path' => $photoPath]);
            }
            if ($request->hasFile('id_proof')) {
                \Log::info('Attempting ID proof upload');
                $proofPath = $request->file('id_proof')->store('customers/proofs');
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
        $customerCode = null;
        if (!$request->customer_id) {
            $customerCode = $this->generateUniqueCustomerCode();
            \Log::info('Generated customer code', ['customer_code' => $customerCode]);
        }

        // Reconnect to ensure clean transaction state (PostgreSQL fix)
        \DB::reconnect();
        
        \DB::beginTransaction();
        try {
            \Log::info('Starting customer creation process');

            // Update existing customer or create new
            if ($request->customer_id) {
                \Log::info('Updating existing customer', ['customer_id' => $request->customer_id]);
                // Update existing customer from online booking
                $customer = Customer::find($request->customer_id);
                if (!$customer) {
                    \Log::error('Customer not found for update', ['customer_id' => $request->customer_id]);
                    \DB::rollBack();
                    return back()->withErrors(['error' => 'Customer not found'])->withInput();
                }
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
                \Log::info('Customer updated successfully', ['customer_id' => $customer->id]);
            } else {
                \Log::info('Creating new walk-in customer');

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
            }

            // Handle booking
            \Log::info('Starting booking creation', ['booking_id' => $request->booking_id, 'bed_id' => $request->bed_id]);
            if ($request->booking_id) {
                // Update existing booking
                $booking = \App\Models\Booking::find($request->booking_id);
                \Log::info('Updating existing booking', ['booking_id' => $booking->id]);
                $booking->update([
                    'status' => 'active',
                    'check_in_date' => $request->check_in_date,
                    'advance_paid' => $request->advance_amount,
                ]);
                $bed = $booking->bed;
            } else {
                // Create new booking for walk-in
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
            }

            // Update bed status
            \Log::info('Updating bed status', ['bed_id' => $bed->id]);
            $bed->update(['status' => 'occupied']);

            // Create payment record
            \Log::info('Creating payment record');
            $customer->payments()->create([
                'booking_id' => $booking->id,
                'amount' => $request->advance_amount,
                'payment_type' => 'Advance',
                'payment_method' => $request->payment_method ?? 'cash',
                'transaction_ref' => 'ADV-' . strtoupper(\Str::random(12)),
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            \Log::info('Payment record created');

            \DB::commit();
            \Log::info('Transaction committed successfully', ['customer_id' => $customer->id]);
            return redirect()->route('admin.customers.index')->with('success', 'Customer check-in completed successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            \DB::rollBack();
            \Log::error('Database error during customer creation', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);

            $errorMsg = 'Database error: ' . $e->getMessage();
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'unique constraint')) {
                $errorMsg = 'A customer with this phone number or email already exists.';
            }

            // Temporary: Return JSON to see errors on Railway
            if (app()->environment('production')) {
                return response()->json(['database_error' => $errorMsg], 500);
            }
            return back()->withErrors(['error' => $errorMsg])->withInput();
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Customer creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['photo', 'id_proof', 'password'])
            ]);
            
            // Temporary: Return JSON to see errors on Railway
            if (app()->environment('production')) {
                return response()->json(['exception_error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
            }
            return back()->withErrors(['error' => 'Failed to complete check-in: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Customer $customer)
    {
        $customer->load(['bookings.bed.room.branch', 'payments', 'requests']);
        return view('admin.customers.show', compact('customer'));
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
                $validated['photo_path'] = $request->file('photo')->store('customers/photos');
            }

            if ($request->hasFile('id_proof')) {
                // Delete old proof
                if ($customer->id_proof_path) {
                    Storage::delete($customer->id_proof_path);
                }
                $validated['id_proof_path'] = $request->file('id_proof')->store('customers/proofs');
            }

            $customer->update($validated);

            return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update customer: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Deactivate/Vacate a customer
     */
    public function deactivate(Customer $customer)
    {
        \DB::beginTransaction();
        try {
            // Get active booking and free up the bed
            $activeBooking = $customer->bookings()->where('status', 'active')->first();
            
            if ($activeBooking) {
                // Update booking status to completed
                $activeBooking->update([
                    'status' => 'completed',
                    'check_out_date' => now(),
                ]);
                
                // Free up the bed
                $activeBooking->bed->update(['status' => 'vacant']);
            }
            
            // Deactivate customer (keeps the record but prevents login)
            $customer->update([
                'is_active' => false,
            ]);
            
            \DB::commit();
            
            \Log::info('Customer vacated', [
                'customer_id' => $customer->id,
                'customer_code' => $customer->customer_code,
                'booking_id' => $activeBooking?->id,
            ]);
            
            return redirect()->route('admin.customers.index')
                ->with('success', "Customer {$customer->name} ({$customer->customer_code}) has been vacated successfully. The bed is now available.");
                
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Customer deactivation failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Failed to vacate customer: ' . $e->getMessage()]);
        }
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
