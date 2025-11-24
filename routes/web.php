<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ============================================
// PUBLIC ROUTES (Booking System)
// ============================================
Route::get('/', [App\Http\Controllers\Public\BookingController::class, 'index'])->name('home');
Route::get('/about', function () { return view('public.about'); })->name('about');
Route::get('/gallery', function () { return view('public.gallery'); })->name('gallery');
Route::get('/contact', function () { return view('public.contact'); })->name('contact');
Route::get('/branch/{branch}', [App\Http\Controllers\Public\BookingController::class, 'showBranch'])->name('booking.branch');
Route::get('/branch/{branch}/room/{room}', [App\Http\Controllers\Public\BookingController::class, 'showRoom'])->name('booking.room');
Route::post('/booking/select-beds', [App\Http\Controllers\Public\BookingController::class, 'selectBeds'])->name('booking.select-beds');
Route::get('/booking/checkout', [App\Http\Controllers\Public\BookingController::class, 'checkout'])->name('booking.checkout');
Route::post('/booking/process-payment', [App\Http\Controllers\Public\BookingController::class, 'processPayment'])->name('booking.process-payment');
Route::get('/booking/confirmation/{booking}', [App\Http\Controllers\Public\BookingController::class, 'confirmation'])->name('booking.confirmation');

// ============================================
// ADMIN ROUTES
// ============================================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Branch & Room Management
    Route::resource('branches', App\Http\Controllers\Admin\BranchController::class);
    Route::resource('rooms', App\Http\Controllers\Admin\RoomController::class);
    Route::post('/rooms/{room}/beds', [App\Http\Controllers\Admin\BedController::class, 'store'])->name('rooms.beds.store');
    Route::delete('/beds/{bed}', [App\Http\Controllers\Admin\BedController::class, 'destroy'])->name('beds.destroy');
    Route::put('/beds/{bed}/status', [App\Http\Controllers\Admin\BedController::class, 'updateStatus'])->name('beds.update-status');

    // Customer & Employee Management
    Route::resource('customers', App\Http\Controllers\Admin\CustomerController::class);
    Route::resource('employees', App\Http\Controllers\Admin\EmployeeController::class);
    
    // Bookings Management
    Route::get('/bookings', [App\Http\Controllers\Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::put('/bookings/{booking}/status', [App\Http\Controllers\Admin\BookingController::class, 'updateStatus'])->name('bookings.update-status');
});

// ============================================
// CUSTOMER PORTAL ROUTES
// ============================================
Route::prefix('portal')->name('customer.')->group(function () {
    Route::get('login', [App\Http\Controllers\Customer\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [App\Http\Controllers\Customer\AuthController::class, 'login']);
    Route::post('logout', [App\Http\Controllers\Customer\AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:customer')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Customer\DashboardController::class, 'index'])->name('dashboard');
    });
});

// ============================================
// DEFAULT LARAVEL AUTH ROUTES (Keep for future admin auth)
// ============================================
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
