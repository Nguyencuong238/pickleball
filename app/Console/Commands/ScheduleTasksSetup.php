<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScheduleTasksSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup cron job for booking scheduler. Run: php artisan schedule:work (for local) or use crontab for production.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Schedule setup instructions:');
        $this->newLine();
        
        $this->info('For local development, run:');
        $this->line('  php artisan schedule:work');
        
        $this->newLine();
        $this->info('For production, add to crontab:');
        $this->line('  * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1');
        
        $this->newLine();
        $this->info('✓ Scheduler is configured in app/Console/Kernel.php');
        $this->info('✓ CancelExpiredTransferBookings job will run every minute');
    }
}
