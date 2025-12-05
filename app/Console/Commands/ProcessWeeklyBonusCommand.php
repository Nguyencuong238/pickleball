<?php

namespace App\Console\Commands;

use App\Services\CommunityService;
use Illuminate\Console\Command;

class ProcessWeeklyBonusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oprs:weekly-bonus
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process weekly match bonus for eligible users (5+ matches = 5 community points)';

    /**
     * Execute the console command.
     */
    public function handle(CommunityService $communityService): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Dry run mode - no changes will be made');
        }

        $this->info('Processing weekly match bonuses...');

        if ($dryRun) {
            // Just show statistics in dry run
            $this->info('Would check all users with confirmed matches this week');
            return 0;
        }

        $count = $communityService->processWeeklyBonuses();

        $this->info("Awarded weekly bonus to {$count} users");

        return 0;
    }
}
