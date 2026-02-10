<?php

namespace App\Console\Commands;

use App\Models\MatchModel;
use Illuminate\Console\Command;

class UpdateOldMatchesStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-old-matches-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-update status of old matches from in_progress to completed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = \Carbon\Carbon::now();
        
        // Find all matches with in_progress or scheduled/ready status that have match_date in the past
        // and don't have actual_end_time set
        $oldMatches = MatchModel::whereIn('status', ['in_progress', 'scheduled', 'ready'])
            ->where('match_date', '<', $now->toDateString())
            ->whereNull('actual_end_time')
            ->get();

        $updated = 0;
        foreach ($oldMatches as $match) {
            $match->update([
                'status' => 'completed',
                'actual_end_time' => $match->match_date->copy()->endOfDay(),
            ]);
            $updated++;
            $this->info("Updated match #{$match->id} from {$match->status} to completed");
        }

        $this->info("Total updated: $updated matches from old status to completed.");
    }
}
