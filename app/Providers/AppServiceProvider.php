<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
        if($this->app->environment('production')) {
    \URL::forceScheme('https');
}
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
