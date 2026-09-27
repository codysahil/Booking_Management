<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            \Illuminate\Support\Facades\Route::middleware('web')->group(__DIR__.'/../routes/super_admin.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\AddNgrokHeaders::class,
            \App\Http\Middleware\ResolveTenant::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsStaff::class,
            'super_admin' => \App\Http\Middleware\EnsureUserIsSuperAdmin::class,
            'subscription' => \App\Http\Middleware\EnsureSubscriptionActive::class,
        ]);

        // Payment gateways post server-to-server without a CSRF token.
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
        ]);

        // Send unauthenticated visitors to the right login screen.
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('customer', 'customer/*')) {
                return route('customer.login');
            }

            return $request->is('super-admin', 'super-admin/*')
                ? route('super-admin.login')
                : route('admin.login');
        });

        // Logged-in visitors hitting a guest-only page go to their dashboard.
        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->is('customer', 'customer/*')) {
                return route('customer.dashboard');
            }

            return $request->is('super-admin', 'super-admin/*')
                ? route('super-admin.dashboard')
                : route('admin.dashboard');
        });
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        // Commands are added in a follow-up commit; only schedule them once they exist.
        if (! class_exists(\App\Console\Commands\GenerateMonthlyCharges::class)) {
            return;
        }

        // Create next month's rent charges on the 1st and flag unpaid ones as overdue daily.
        $schedule->command('charges:generate')->monthlyOn(1, '00:30');
        $schedule->command('charges:mark-overdue')->dailyAt('01:00');

        if (class_exists(\App\Console\Commands\ExpirePastDueSubscriptions::class)) {
            $schedule->command('subscriptions:expire-past-due')->dailyAt('01:30');
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // No-ops until SENTRY_LARAVEL_DSN is set — see config/sentry.php.
        Integration::handles($exceptions);
    })->create();
