<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\AddNgrokHeaders::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsStaff::class,
        ]);

        // Payment gateways post server-to-server without a CSRF token.
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
        ]);

        // Send unauthenticated visitors to the right login screen.
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->is('customer', 'customer/*')
                ? route('customer.login')
                : route('admin.login');
        });

        // Logged-in visitors hitting a guest-only page go to their dashboard.
        $middleware->redirectUsersTo(function (Request $request) {
            return $request->is('customer', 'customer/*')
                ? route('customer.dashboard')
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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
