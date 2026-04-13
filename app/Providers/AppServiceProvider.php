<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\URL;

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
        Paginator::useBootstrap();

        // Set default string length for MySQL
        Schema::defaultStringLength(191);

        // ✅ Register Order Observer untuk Auto Notifications
        Order::observe(OrderObserver::class);

        // Hanya force HTTPS di production, bukan di local
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

    }
    
}
