<?php

use Illuminate\Support\Facades\Route;

// ============================================
// WEBHOOK ROUTES (No CSRF)
// ============================================
Route::post('/webhook/razorpay', [App\Http\Controllers\Webhook\RazorpayWebhookController::class, 'handle'])
    ->name('webhook.razorpay')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// ============================================
// ROOT — this is a private management platform, not a public storefront.
// ============================================
Route::get('/', fn () => redirect()->route('admin.login'))->name('home');

// ============================================
// ADMIN AUTH ROUTES
// ============================================
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes (not logged in)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [App\Http\Controllers\Admin\AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [App\Http\Controllers\Admin\AuthController::class, 'login'])->middleware('throttle:5,1');
        Route::get('/forgot-password', [App\Http\Controllers\Admin\AuthController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('/forgot-password', [App\Http\Controllers\Admin\AuthController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [App\Http\Controllers\Admin\AuthController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::post('/reset-password', [App\Http\Controllers\Admin\AuthController::class, 'resetPassword'])->name('password.update');
    });

    // Protected routes (any active staff account)
    Route::middleware(['auth', 'admin'])->group(function () {
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
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Branch & Room Management
    Route::resource('branches', App\Http\Controllers\Admin\BranchController::class);
    Route::resource('rooms', App\Http\Controllers\Admin\RoomController::class);
    Route::post('/rooms/{room}/beds', [App\Http\Controllers\Admin\BedController::class, 'store'])->name('rooms.beds.store');
    Route::delete('/beds/{bed}', [App\Http\Controllers\Admin\BedController::class, 'destroy'])->name('beds.destroy');
    Route::put('/beds/{bed}/status', [App\Http\Controllers\Admin\BedController::class, 'updateStatus'])->name('beds.update-status');
    Route::delete('/rooms/{room}/images/{image}', [App\Http\Controllers\Admin\RoomController::class, 'destroyImage'])->name('rooms.images.destroy');

    // Hero Slider Management
    Route::resource('sliders', App\Http\Controllers\Admin\HeroSliderController::class);
    Route::patch('/sliders/{slider}/toggle', [App\Http\Controllers\Admin\HeroSliderController::class, 'toggleStatus'])->name('sliders.toggle');

    // Reports & Analytics
    Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [App\Http\Controllers\Admin\ReportController::class, 'export'])->name('reports.export');

    // Customer & Employee Management
    Route::resource('customers', App\Http\Controllers\Admin\CustomerController::class);
    Route::patch('/customers/{customer}/deactivate', [App\Http\Controllers\Admin\CustomerController::class, 'deactivate'])->name('customers.deactivate');
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
    Route::get('/payments/{payment}/receipt', [App\Http\Controllers\Admin\PaymentHistoryController::class, 'receipt'])->name('payments.receipt');

    // Expenses
    Route::get('/expenses/export', [App\Http\Controllers\Admin\ExpenseController::class, 'export'])->name('expenses.export');
    Route::resource('expenses', App\Http\Controllers\Admin\ExpenseController::class)->except(['show']);

    // Dues
    Route::post('/customers/{customer}/dues', [App\Http\Controllers\Admin\DueController::class, 'store'])->name('dues.store');
    Route::patch('/dues/{due}/mark-paid', [App\Http\Controllers\Admin\DueController::class, 'markPaid'])->name('dues.mark-paid');
    Route::delete('/dues/{due}', [App\Http\Controllers\Admin\DueController::class, 'destroy'])->name('dues.destroy');

    // Profit & Loss report
    Route::get('/reports/profit-loss', [App\Http\Controllers\Admin\ReportController::class, 'profitLoss'])->name('reports.profit-loss');

    // Resident requests
    Route::get('/requests', [App\Http\Controllers\Admin\RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{request}', [App\Http\Controllers\Admin\RequestController::class, 'show'])->name('requests.show');
    Route::put('/requests/{request}', [App\Http\Controllers\Admin\RequestController::class, 'update'])->name('requests.update');

    // Announcements
    Route::resource('announcements', App\Http\Controllers\Admin\AnnouncementController::class)->except(['show']);

    // Notification centre
    Route::get('/notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/read', [App\Http\Controllers\Admin\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications/read-all', [App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Settings (owner only)
    Route::middleware('admin:admin')->group(function () {
        Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');

        // Team (manager accounts)
        Route::resource('team', App\Http\Controllers\Admin\TeamController::class)->except(['show']);
    });
});

// ============================================
// CUSTOMER PORTAL ROUTES
// ============================================
Route::prefix('customer')->name('customer.')->group(function () {
    Route::get('login', [App\Http\Controllers\Customer\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [App\Http\Controllers\Customer\AuthController::class, 'login'])->middleware('throttle:5,1');
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
        Route::get('payments/{payment}/receipt', [App\Http\Controllers\Customer\PaymentController::class, 'receipt'])->name('payments.receipt');

        // Announcements & resident requests
        Route::get('announcements', [App\Http\Controllers\Customer\DashboardController::class, 'announcements'])->name('announcements.index');
        Route::get('requests', [App\Http\Controllers\Customer\RequestController::class, 'index'])->name('requests.index');
        Route::get('requests/create', [App\Http\Controllers\Customer\RequestController::class, 'create'])->name('requests.create');
        Route::post('requests', [App\Http\Controllers\Customer\RequestController::class, 'store'])->name('requests.store');
        Route::get('requests/{request}', [App\Http\Controllers\Customer\RequestController::class, 'show'])->name('requests.show');
    });
});

// Redirect /dashboard to admin login
Route::get('/dashboard', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('admin.login');
});
