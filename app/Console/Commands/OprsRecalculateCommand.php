<?php

namespace App\Console\Commands;

use App\Models\OprsHistory;
use App\Models\User;
use App\Services\OprsService;
use Illuminate\Console\Command;

class OprsRecalculateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oprs:recalculate
                            {--user= : Specific user ID to recalculate}
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate OPRS for all or specific user';

    /**
     * Execute the console command.
     */
    public function handle(OprsService $oprsService): int
    {
        $userId = $this->option('user');
        $dryRun = $this->option('dry-run');

        if ($userId) {
            return $this->recalculateSingleUser($oprsService, (int) $userId, $dryRun);
        }

        return $this->recalculateAllUsers($oprsService, $dryRun);
    }

    /**
     * Recalculate OPRS for a single user
     */
    private function recalculateSingleUser(OprsService $oprsService, int $userId, bool $dryRun): int
    {
        $user = User::find($userId);

        if (!$user) {
            $this->error("User {$userId} not found");
            return 1;
        }

        $currentOprs = $user->total_oprs;
        $newOprs = $oprsService->calculateOprs($user);
        $newLevel = $oprsService->calculateOprLevel($newOprs);

        $this->table(
            ['Field', 'Current', 'New'],
            [
                ['Elo Rating', $user->elo_rating, '-'],
                ['Challenge Score', $user->challenge_score, '-'],
                ['Community Score', $user->community_score, '-'],
                ['Total OPRS', $currentOprs, $newOprs],
                ['OPR Level', $user->opr_level, $newLevel],
            ]
        );

        if ($dryRun) {
            $this->warn('Dry run - no changes made');
            return 0;
        }

        $oprsService->updateUserOprs(
            $user,
            OprsHistory::REASON_ADMIN_ADJUSTMENT,
            ['reason' => 'Manual recalculation via CLI']
        );

        $this->info("Recalculated OPRS for user {$userId}");
        return 0;
    }

    /**
     * Recalculate OPRS for all users
     */
    private function recalculateAllUsers(OprsService $oprsService, bool $dryRun): int
    {
        $totalUsers = User::count();

        if ($dryRun) {
            $this->info("Dry run: Would recalculate OPRS for {$totalUsers} users");

            // Show distribution preview
            $this->info('Current level distribution:');
            $distribution = $oprsService->getLevelDistribution();
            $this->table(
                ['OPR Level', 'Count'],
                collect($distribution)->map(fn ($count, $level) => [$level, $count])->values()->toArray()
            );

            return 0;
        }

        if (!$this->confirm("This will recalculate OPRS for {$totalUsers} users. Continue?")) {
            $this->warn('Operation cancelled');
            return 0;
        }

        $this->info('Recalculating OPRS for all users...');
        $bar = $this->output->createProgressBar($totalUsers);
        $bar->start();

        $count = 0;
        User::chunk(100, function ($users) use ($oprsService, &$count, $bar) {
            foreach ($users as $user) {
                $oprsService->updateUserOprs(
                    $user,
                    OprsHistory::REASON_INITIAL_CALCULATION
                );
                $count++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("Recalculated OPRS for {$count} users");

        // Show new distribution
        $this->info('New level distribution:');
        $distribution = $oprsService->getLevelDistribution();
        $this->table(
            ['OPR Level', 'Count'],
            collect($distribution)->map(fn ($count, $level) => [$level, $count])->values()->toArray()
        );

        return 0;
    }
}
