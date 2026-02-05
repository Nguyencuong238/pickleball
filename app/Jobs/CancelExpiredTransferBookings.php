<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CancelExpiredTransferBookings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     * Hủy các booking chuyển khoản chưa xác nhận sau 15 phút
     */
    public function handle(): void
    {
        $now = time();

        // Tìm các booking transfer pending mà hết thời hạn khóa
        $expiredBookings = Booking::where('status', 'pending')
            ->where('payment_method', 'transfer')
            ->whereNotNull('lock_expires_at')
            ->where('lock_expires_at', '<=', $now)
            ->get();

        foreach ($expiredBookings as $booking) {
            // Hủy booking và tính lại is_locked -> available
            $booking->cancel();
            
            // Log nếu cần
            \Log::info("Cancelled expired transfer booking #{$booking->id} (expires at: {$booking->lock_expires_at})");
        }
    }
}
