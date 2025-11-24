<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateMatchStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update match status to in_progress when match_date + match_time <= now';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $now = now();
            
            // Find all matches with status 'scheduled' where match_date + match_time <= now
            $matches = MatchModel::where('status', 'scheduled')
                ->whereNotNull('match_time')
                ->get();
            
            $updated = 0;
            
            foreach ($matches as $match) {
                try {
                    // Combine match_date and match_time to create a datetime
                    $dateStr = $match->match_date instanceof Carbon 
                        ? $match->match_date->format('Y-m-d') 
                        : $match->match_date;
                    
                    $timeStr = is_object($match->match_time) 
                        ? $match->match_time->format('H:i:s')
                        : trim($match->match_time);
                    
                    $matchDateTime = Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        "$dateStr $timeStr",
                        config('app.timezone')
                    );
                    
                    // Check if match should have started
                    if ($matchDateTime <= $now) {
                        $match->update(['status' => 'in_progress']);
                        $updated++;
                        
                        Log::info('Match status updated to in_progress', [
                            'match_id' => $match->id,
                            'tournament_id' => $match->tournament_id,
                            'match_date' => $match->match_date,
                            'match_time' => $match->match_time,
                            'match_datetime' => $matchDateTime,
                            'current_time' => $now
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to parse match datetime', [
                        'match_id' => $match->id,
                        'match_date' => $match->match_date,
                        'match_time' => $match->match_time,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $this->info("Updated $updated match(es) to in_progress status.");
            return 0;
        } catch (\Exception $e) {
            $this->error('Error updating match status: ' . $e->getMessage());
            Log::error('Update match status error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return 1;
        }
    }
}
