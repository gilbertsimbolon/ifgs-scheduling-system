<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\URL;
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
        if (config('app.env') === 'production' || str_contains(request()->getHost(), 'ngrok') || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }

        RedirectIfAuthenticated::redirectUsing(fn() => '/');
    }
}
