<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FormatBookingTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:format-times';

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
        $bookings = DB::table('bookings')->get();
        
        if ($bookings->isEmpty()) {
            $this->info('No booking records found.');
            return;
        }

        $this->output->progressStart($bookings->count());
        $updated = 0;

        foreach ($bookings as $booking) {
            try {
                // Convert HH:MM:SS to HH:MM
                $start_time = $this->formatTime($booking->start_time);
                $end_time = $this->formatTime($booking->end_time);

                DB::table('bookings')
                    ->where('id', $booking->id)
                    ->update([
                        'start_time' => $start_time,
                        'end_time' => $end_time,
                    ]);

                $updated++;
                $this->output->progressAdvance();
            } catch (\Exception $e) {
                $this->warn("Failed to update booking ID {$booking->id}: {$e->getMessage()}");
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
