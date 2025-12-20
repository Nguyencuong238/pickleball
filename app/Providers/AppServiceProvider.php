<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Models\Tournament;
use App\Observers\TournamentObserver;

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
        // Register Tournament Observer
        Tournament::observe(TournamentObserver::class);

        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }

        // Share data with layout
        \View::composer('layouts.homeyard', function ($view) {
            if (\Auth::check()) {
                $userId = \Auth::id();
                
                // Count tournaments
                $tournamentsCount = \App\Models\Tournament::where('user_id', $userId)->count();
                
                // Count matches
                $matchesCount = \App\Models\MatchModel::whereHas('tournament', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->count();
                
                $view->with([
                    'tournamentsCount' => $tournamentsCount,
                    'matchesCount' => $matchesCount,
                ]);
            }
        });

        // Blade directives for media
        Blade::directive('mediaUrl', function ($media) {
            return "<?php echo ($media)->getUrl() ?? null; ?>";
        });

        Blade::directive('firstMediaUrl', function ($expression) {
            return "<?php echo ($expression)->getFirstMedia() ? ($expression)->getFirstMedia()->getUrl() : null; ?>";
        });

        Blade::directive('mediaExists', function ($expression) {
            return "<?php if (($expression)->getFirstMedia()): ?>";
        });

        Blade::directive('endmediaExists', function () {
            return "<?php endif; ?>";
        });
    }
}
