<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Models\Stadium;
use App\Models\Tournament;
use App\Models\Instructor;
use App\Models\Club;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Route model bindings for slug-based URLs
        Route::bind('stadium', function ($slug) {
            return Stadium::where('slug', $slug)->firstOrFail();
        });

        Route::bind('tournament', function ($slug) {
            return Tournament::where('slug', $slug)->firstOrFail();
        });

        Route::bind('instructor', function ($slug) {
            return Instructor::where('slug', $slug)->firstOrFail();
        });

        Route::bind('club', function ($slug) {
            return Club::where('slug', $slug)->firstOrFail();
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
