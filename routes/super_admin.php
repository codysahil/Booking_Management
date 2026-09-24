<?php

use App\Http\Controllers\SuperAdmin\AuthController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\TenantController;
use Illuminate\Support\Facades\Route;

Route::prefix('super-admin')->name('super-admin.')->group(function () {
    // Deliberately not the blanket 'guest' middleware: it only checks whether
    // any web-guard user is logged in, so a tenant admin with an active
    // session here would get bounced to the super-admin dashboard (and then
    // 404 there, since they're not a super admin) instead of ever seeing this
    // form. AuthController::showLoginForm() does the correct, identity-aware
    // check itself; Auth::attempt() in login() safely swaps the session to
    // whichever account the submitted credentials belong to.
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(['auth', 'super_admin'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('tenants', TenantController::class)->except(['destroy']);
    });
});
