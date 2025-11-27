<?php

namespace App\Providers;

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
