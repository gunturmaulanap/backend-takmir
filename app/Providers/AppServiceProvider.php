<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Models\Event;
use App\Models\JadwalKhutbah;
use App\Observers\EventObserver;
use App\Observers\JadwalKhutbahObserver;

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
        // Force HTTPS scheme when not in local environment so generated
        // URLs (assets) use https. This helps prevent mixed-content
        // errors when the app runs behind a proxy/Load Balancer.
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        // Register observers
        Event::observe(EventObserver::class);
        JadwalKhutbah::observe(JadwalKhutbahObserver::class);
    }
}
