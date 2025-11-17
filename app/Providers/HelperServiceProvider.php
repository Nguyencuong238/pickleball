<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load helper files
        $this->loadHelpers();
    }

    protected function loadHelpers()
    {
        $helpers = glob(app_path('Helpers') . '/*.php');
        
        foreach ($helpers as $helper) {
            require_once $helper;
        }
    }
}
