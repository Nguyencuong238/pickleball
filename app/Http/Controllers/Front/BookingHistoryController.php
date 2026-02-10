<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Contracts\View\View;

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
}
