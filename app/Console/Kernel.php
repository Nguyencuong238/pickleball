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

        // Point Earning - Check and award weekly 5-match bonus
        $schedule->command('points:check-weekly')->dailyAt('00:05');

        // Cancel expired transfer bookings after 15 minutes
        $schedule->job(new \App\Jobs\CancelExpiredTransferBookings())->everyMinute();

        // Auto-update status of old matches from in_progress to completed
        $schedule->command('app:update-old-matches-status')->daily();

        // Club Activities - Generate recurring meet instances 7 days ahead
        $schedule->command('clubs:generate-recurring-meets')->daily()->at('06:00');
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
