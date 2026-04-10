<?php

namespace App\Http\Controllers\Front;

use App\Exceptions\GemTransferException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;

class BookingHistoryController extends Controller
{
    /**
     * Show booking history list for authenticated user
     */
    public function index(): View
    {
        $user = auth()->user();
        
        // Get all bookings for the authenticated user, ordered by latest first
        $bookings = Booking::where('user_id', $user->id)
            ->with(['court', 'court.stadium'])
            ->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        return view('front.booking-history.index', compact('bookings', 'user'));
    }

    /**
     * Show single booking details
     */
    public function show(Booking $booking): View
    {
        // Check if user owns this booking
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $booking->load(['court', 'court.stadium']);

        return view('front.booking-history.show', compact('booking'));
    }

    /**
     * Huy booking va hoan Gems (neu thanh toan bang Gems trong cua so refund).
     */
    public function cancel(Booking $booking): RedirectResponse
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'Không thể hủy đơn đặt ở trạng thái hiện tại.');
        }

        // Hoan Gems trong cua so refund (neu bat flag)
        if (config('gems.transfer_enabled')) {
            try {
                app(\App\Services\GemPaymentProcessor::class)->refundFor($booking);
            } catch (ModelNotFoundException $e) {
                // Booking khong thanh toan bang Gems — bo qua.
            } catch (GemTransferException $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        $booking->cancel();

        return redirect()->route('user.booking-history.index')
            ->with('success', 'Đã hủy đơn đặt sân thành công.');
    }
}
