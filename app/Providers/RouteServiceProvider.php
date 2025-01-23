<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->response(function () {
                return response()->json(
                    ['Too many requests. Try again later.', 429],
                    429
                );
            });
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(10)->response(function () {
                return response()->json(
                    ['Too many requests. Try again later.', 429],
                    429
                );
            });
        });
    }
}
