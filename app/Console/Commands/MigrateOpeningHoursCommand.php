<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stadium;

class MigrateOpeningHoursCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stadium:migrate-opening-hours';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate opening_hours to opening_time and closing_time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $stadiums = Stadium::all();
        $updated = 0;
        $failed = 0;

        $this->output->progressStart($stadiums->count());

        foreach ($stadiums as $stadium) {
            try {
                $opening_time = '00:00';
                $closing_time = '24:00';

                if ($stadium->opening_hours) {
                    // Parse opening_time from the start of opening_hours (format: "00:00 - 24:00")
                    if (preg_match('/(\d{1,2}):(\d{2})/', $stadium->opening_hours, $matches)) {
                        $opening_time = $this->formatTime($matches[0]);
                    }

                    // Parse closing_time from the end of opening_hours
                    if (preg_match('/(\d{1,2}):(\d{2})\s*$/', substr($stadium->opening_hours, strpos($stadium->opening_hours, '-') + 1), $matches)) {
                        $closing_time = $this->formatTime($matches[0]);
                    }

                    // If opening_time >= closing_time, set closing_time to 24:00
                    if (strtotime($opening_time) >= strtotime($closing_time)) {
                        $closing_time = '24:00';
                    }
                }

                $stadium->update([
                    'opening_time' => $opening_time,
                    'closing_time' => $closing_time,
                ]);

                $updated++;
                $this->output->progressAdvance();
            } catch (\Exception $e) {
                $failed++;
                $this->output->progressAdvance();
                $this->warn("Failed to update Stadium ID {$stadium->id}: {$e->getMessage()}");
            }
        }

        $this->output->progressFinish();
        $this->info("Migration completed. Updated: $updated, Failed: $failed");
    }

    /**
     * Format time string to HH:MM format (e.g., "6:00" -> "06:00")
     */
    private function formatTime(string $time): string
    {
        list($hour, $minute) = explode(':', $time);
        return sprintf('%02d:%02d', (int)$hour, (int)$minute);
    }
}
