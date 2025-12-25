<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FormatCourtPricingTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'court-pricing:format-times';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Format start_time and end_time from HH:MM:SS to HH:MM format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pricings = DB::table('court_pricing')->get();
        
        if ($pricings->isEmpty()) {
            $this->info('No court pricing records found.');
            return;
        }

        $this->output->progressStart($pricings->count());
        $updated = 0;

        foreach ($pricings as $pricing) {
            try {
                // Convert HH:MM:SS to HH:MM
                $start_time = $this->formatTime($pricing->start_time);
                $end_time = $this->formatTime($pricing->end_time);

                DB::table('court_pricing')
                    ->where('id', $pricing->id)
                    ->update([
                        'start_time' => $start_time,
                        'end_time' => $end_time,
                    ]);

                $updated++;
                $this->output->progressAdvance();
            } catch (\Exception $e) {
                $this->warn("Failed to update pricing ID {$pricing->id}: {$e->getMessage()}");
                $this->output->progressAdvance();
            }
        }

        $this->output->progressFinish();
        $this->info("Formatting completed. Updated: $updated records");
    }

    /**
     * Format time from HH:MM:SS to HH:MM
     */
    private function formatTime($time): string
    {
        // If already in HH:MM format, return as is
        if (strlen($time) <= 5) {
            return $time;
        }

        // Extract HH:MM from HH:MM:SS
        return substr($time, 0, 5);
    }
}
