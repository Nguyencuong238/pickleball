<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Update match status to in_progress when match time arrives
        $schedule->command('match:update-status')->everyMinute();

        // Auto-confirm OCR matches after 24h with no dispute
        $schedule->command('ocr:auto-confirm')->hourly();

        // Delete old temporary files from media library
        $schedule->command('media:clean-temp')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
