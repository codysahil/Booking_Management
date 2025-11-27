<?php

use Illuminate\Support\Facades\Route;

// ============================================
// PUBLIC ROUTES (Booking System)
// ============================================
Route::get('/', [App\Http\Controllers\Public\BookingController::class, 'index'])->name('home');
Route::get('/about', function () {
    return view('public.about');
})->name('about');
Route::get('/gallery', function () {
    return view('public.gallery');
})->name('gallery');
Route::get('/contact', function () {
    return view('public.contact');
})->name('contact');
Route::get('/branch/{branch}', [App\Http\Controllers\Public\BookingController::class, 'showBranch'])->name('booking.branch');
Route::get('/branch/{branch}/room/{room}', [App\Http\Controllers\Public\BookingController::class, 'showRoom'])->name('booking.room');
Route::post('/booking/select-beds', [App\Http\Controllers\Public\BookingController::class, 'selectBeds'])->name('booking.select-beds');
Route::get('/booking/checkout', [App\Http\Controllers\Public\BookingController::class, 'checkout'])->name('booking.checkout');
Route::post('/booking/process-payment', [App\Http\Controllers\Public\BookingController::class, 'processPayment'])->name('booking.process-payment');
Route::get('/booking/confirmation/{booking}', [App\Http\Controllers\Public\BookingController::class, 'confirmation'])->name('booking.confirmation');

// Debug route for Railway testing
Route::get('/debug/db-test', function () {
    try {
        $branches = \App\Models\Branch::count();
        $customers = \App\Models\Customer::count();
        $bookings = \App\Models\Booking::count();
        $beds = \App\Models\Bed::count();

        return response()->json([
            'status' => 'success',
            'database' => config('database.default'),
            'counts' => [
                'branches' => $branches,
                'customers' => $customers,
                'bookings' => $bookings,
                'beds' => $beds,
            ],
            'latest_booking' => \App\Models\Booking::with('customer')->latest()->first(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

Route::get('/debug-config', function () {
    try {
        $disk = \Illuminate\Support\Facades\Storage::disk('cloudinary');

        // 1. Test Upload
        $filename = 'debug_test_' . time() . '.txt';
        $disk->put($filename, 'Hello Cloudinary!');

        // 2. Test URL Generation
        $url = $disk->url($filename);

        // 3. Test Delete (Optional, maybe keep it to see it?)
        // $disk->delete($filename);

        return response()->json([
            'status' => 'success',
            'message' => 'Cloudinary config is working!',
            'uploaded_file' => $filename,
            'url' => $url,
            'config_dump' => [
                'cloud' => config('filesystems.disks.cloudinary.cloud'),
                'key' => substr(config('filesystems.disks.cloudinary.key'), 0, 5) . '...',
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

// ============================================
// ADMIN AUTH ROUTES
// ============================================
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes (not logged in)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [App\Http\Controllers\Admin\AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [App\Http\Controllers\Admin\AuthController::class, 'login']);
        Route::get('/forgot-password', [App\Http\Controllers\Admin\AuthController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('/forgot-password', [App\Http\Controllers\Admin\AuthController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [App\Http\Controllers\Admin\AuthController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::post('/reset-password', [App\Http\Controllers\Admin\AuthController::class, 'resetPassword'])->name('password.update');
    });

    // Protected routes (logged in)
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');

        // Profile
        Route::get('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password');
    });
});

// ============================================
// ADMIN ROUTES (Protected)
// ============================================
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Branch & Room Management
    Route::resource('branches', App\Http\Controllers\Admin\BranchController::class);
    Route::resource('rooms', App\Http\Controllers\Admin\RoomController::class);
    Route::post('/rooms/{room}/beds', [App\Http\Controllers\Admin\BedController::class, 'store'])->name('rooms.beds.store');
    Route::delete('/beds/{bed}', [App\Http\Controllers\Admin\BedController::class, 'destroy'])->name('beds.destroy');
    Route::put('/beds/{bed}/status', [App\Http\Controllers\Admin\BedController::class, 'updateStatus'])->name('beds.update-status');
    Route::delete('/rooms/{room}/images/{image}', [App\Http\Controllers\Admin\RoomController::class, 'destroyImage'])->name('rooms.images.destroy');

    // Customer & Employee Management
    Route::resource('customers', App\Http\Controllers\Admin\CustomerController::class);
    Route::resource('employees', App\Http\Controllers\Admin\EmployeeController::class);

    // Bookings Management
    Route::get('/bookings', [App\Http\Controllers\Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::put('/bookings/{booking}/status', [App\Http\Controllers\Admin\BookingController::class, 'updateStatus'])->name('bookings.update-status');

    // Monthly Charges Management
    Route::get('/charges', [App\Http\Controllers\Admin\MonthlyChargeController::class, 'index'])->name('charges.index');
    Route::get('/charges/generate', [App\Http\Controllers\Admin\MonthlyChargeController::class, 'generate'])->name('charges.generate');
    Route::post('/charges/generate', [App\Http\Controllers\Admin\MonthlyChargeController::class, 'generate']);
    Route::get('/charges/{charge}/edit', [App\Http\Controllers\Admin\MonthlyChargeController::class, 'edit'])->name('charges.edit');
    Route::put('/charges/{charge}', [App\Http\Controllers\Admin\MonthlyChargeController::class, 'update'])->name('charges.update');
    Route::patch('/charges/{charge}/mark-paid', [App\Http\Controllers\Admin\MonthlyChargeController::class, 'markPaid'])->name('charges.mark-paid');
    Route::delete('/charges/{charge}', [App\Http\Controllers\Admin\MonthlyChargeController::class, 'destroy'])->name('charges.destroy');
    Route::get('/customers/{customer}/charges', [App\Http\Controllers\Admin\MonthlyChargeController::class, 'customerCharges'])->name('customers.charges');
    Route::post('/customers/{customer}/update-rent', [App\Http\Controllers\Admin\MonthlyChargeController::class, 'updateRent'])->name('customers.update-rent');

    // Payment History
    Route::get('/payments/history', [App\Http\Controllers\Admin\PaymentHistoryController::class, 'index'])->name('payments.history');
});

// ============================================
// CUSTOMER PORTAL ROUTES
// ============================================
Route::prefix('customer')->name('customer.')->group(function () {
    Route::get('login', [App\Http\Controllers\Customer\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [App\Http\Controllers\Customer\AuthController::class, 'login']);
    Route::post('logout', [App\Http\Controllers\Customer\AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:customer')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Customer\DashboardController::class, 'index'])->name('dashboard');
        Route::get('bookings/{booking}', [App\Http\Controllers\Customer\DashboardController::class, 'showBooking'])->name('bookings.show');

        // Payment routes
        Route::get('payments', [App\Http\Controllers\Customer\PaymentController::class, 'index'])->name('payments.index');
        Route::post('payments/create-order', [App\Http\Controllers\Customer\PaymentController::class, 'createOrder'])->name('payments.create-order');
        Route::post('payments/verify', [App\Http\Controllers\Customer\PaymentController::class, 'verifyPayment'])->name('payments.verify');
        Route::get('payments/success', [App\Http\Controllers\Customer\PaymentController::class, 'success'])->name('payments.success');
        Route::get('payments/failed', [App\Http\Controllers\Customer\PaymentController::class, 'failed'])->name('payments.failed');
        Route::get('payments/history', [App\Http\Controllers\Customer\PaymentController::class, 'history'])->name('payments.history');
    });
});

// Redirect /dashboard to admin login
Route::get('/dashboard', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('admin.login');
});
