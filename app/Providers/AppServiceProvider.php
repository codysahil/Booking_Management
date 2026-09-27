<?php

namespace App\Providers;

use App\Models\Customer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Laravel's default ResetPassword notification hard-codes route('password.reset'),
        // which doesn't exist here — this app has two separate, prefixed reset flows
        // instead (admin.password.reset, customer.password.reset). Without this, every
        // real password-reset request throws RouteNotFoundException.
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $routeName = $notifiable instanceof Customer ? 'customer.password.reset' : 'admin.password.reset';

            return url(route($routeName, [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
        });

        // Force HTTPS for all URLs when using ngrok or production
        if (
            config('app.env') !== 'local' || 
            request()->header('x-forwarded-proto') === 'https' ||
            str_contains(config('app.url'), 'ngrok')
        ) {
            \URL::forceScheme('https');
        }

        // Use build path for Vite when not in dev mode
        if (config('app.env') === 'production' || env('VITE_USE_BUILD', false)) {
            \Illuminate\Support\Facades\Vite::useBuildDirectory('build');
        }
    }
}
