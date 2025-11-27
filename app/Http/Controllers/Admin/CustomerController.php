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
        \Log::info('Customer store method called', $request->all());

        try {
            $validated = $request->validate([
                'customer_id' => 'nullable|exists:customers,id',
                'booking_id' => 'nullable|exists:bookings,id',
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email',
                'dob' => 'required|date',
                'address' => 'required|string',
                'guardian_phone' => 'required|string|max:20',
                'work_details' => 'nullable|string',
                'photo' => 'nullable|image|max:10240', // Increased to 10MB
                'id_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // Increased to 10MB
                'bed_id' => 'nullable|exists:beds,id',
                'check_in_date' => 'required|date',
                'stay_type' => 'required|in:permanent,day_basis',
                'advance_amount' => 'required|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        }

        // Validate bed_id is present when not updating existing booking
        if (!$request->booking_id && !$request->bed_id) {
            return back()->withErrors(['bed_id' => 'Please select a bed'])->withInput();
        }

        \DB::beginTransaction();
        try {
            \Log::info('Starting customer creation process');

            // Handle File Uploads (uses Cloudinary in production)
            $photoPath = null;
            $proofPath = null;

            try {
                if ($request->hasFile('photo')) {
                    $photoPath = $request->file('photo')->store('customers/photos');
                    \Log::info('Photo uploaded', ['path' => $photoPath]);
                }
                if ($request->hasFile('id_proof')) {
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

            // Update existing customer or create new
            if ($request->customer_id) {
                // Update existing customer from online booking
                $customer = Customer::find($request->customer_id);
                if (!$customer) {
                    \Log::error('Customer not found for update', ['customer_id' => $request->customer_id]);
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
            } else {
                // Create new walk-in customer
                // Use max ID instead of count to avoid race conditions
                $lastCustomer = Customer::orderBy('id', 'desc')->first();
                $nextNumber = $lastCustomer ? ($lastCustomer->id + 1) : 1;
                $customerCode = 'SS-' . date('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                \Log::info('Creating new customer', ['customer_code' => $customerCode]);

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
            if ($request->booking_id) {
                // Update existing booking
                $booking = \App\Models\Booking::find($request->booking_id);
                $booking->update([
                    'status' => 'active',
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
                    'status' => 'active',
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
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            \DB::commit();
            \Log::info('Customer created successfully', ['customer_id' => $customer->id]);
            return redirect()->route('admin.customers.index')->with('success', 'Customer check-in completed successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            \DB::rollBack();
            \Log::error('Database error during customer creation', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);

            // Check for specific database errors
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'unique constraint')) {
                return back()->withErrors(['error' => 'A customer with this phone number or email already exists.'])->withInput();
            }

            return back()->withErrors(['error' => 'Database error: ' . $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Customer creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['photo', 'id_proof', 'password'])
            ]);
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
}
