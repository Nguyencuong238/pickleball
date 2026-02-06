<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CancelUnpaidBooking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bookingId;
    protected $delay;

    /**
     * Create a new job instance.
     */
    public function __construct($bookingId, $delay = 300)
    {
        $this->bookingId = $bookingId;
        $this->delay = $delay;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $booking = Booking::find($this->bookingId);

        if (!$booking) {
            return;
        }

        // Only cancel if still in pending_payment status
        if ($booking->status === 'pending_payment') {
            $booking->update(['status' => 'cancelled']);
            \Log::info('Booking ' . $booking->id . ' auto-cancelled due to unpaid transfer');
        }
    }
}
