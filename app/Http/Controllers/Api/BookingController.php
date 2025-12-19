<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use App\Models\CourtPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Get all bookings for authenticated user
     */
    public function index(Request $request)
    {
        $query = Booking::where('user_id', Auth::id());

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by court
        if ($request->has('court_id')) {
            $query->where('court_id', $request->court_id);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('booking_date', $request->date);
        }

        // Pagination
        $per_page = $request->get('per_page', 10);
        $bookings = $query->with('court')->orderBy('booking_date', 'desc')->paginate($per_page);

        return response()->json([
            'success' => true,
            'data' => $bookings->items(),
            'pagination' => [
                'total' => $bookings->total(),
                'per_page' => $bookings->perPage(),
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
            ]
        ]);
    }

    /**
     * Create new booking
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'court_id' => 'required|exists:courts,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'required|email',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Calculate duration and total price
        $startTime = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
        $endTime = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);
        $duration = $endTime->diffInMinutes($startTime) / 60;

        $court = \App\Models\Court::find($request->court_id);
        $hourlyRate = $court->hourly_rate ?? 0;
        $totalPrice = $duration * $hourlyRate;
        $serviceFee = ceil($totalPrice * 0.1); // 10% service fee

        $booking = Booking::create([
            'user_id' => Auth::id(),
            'court_id' => $request->court_id,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'booking_date' => $request->booking_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration_hours' => $duration,
            'hourly_rate' => $hourlyRate,
            'total_price' => $totalPrice,
            'service_fee' => $serviceFee,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => $booking->load('court'),
        ], 201);
    }

    /**
     * Get booking details
     */
    public function show($id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Check if user owns this booking
        if ($booking->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $booking->load('court'),
        ]);
    }

    /**
     * Update booking
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Check if user owns this booking
        if ($booking->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Can only update if pending
        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Can only update pending bookings',
            ], 422);
        }

        $validated = $request->validate([
            'booking_date' => 'nullable|date|after_or_equal:today',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email',
            'notes' => 'nullable|string',
        ]);

        // Recalculate if times changed
        if ($request->has('start_time') && $request->has('end_time')) {
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);
            $duration = $endTime->diffInMinutes($startTime) / 60;

            $validated['duration_hours'] = $duration;
            $validated['total_price'] = $duration * $booking->hourly_rate;
            $validated['service_fee'] = ceil($validated['total_price'] * 0.1);
        }

        $booking->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'data' => $booking->load('court'),
        ]);
    }

    /**
     * Cancel booking
     */
    public function destroy($id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Check if user owns this booking
        if ($booking->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Can only cancel if pending or confirmed
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel this booking',
            ], 422);
        }

        $booking->cancel();

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
        ]);
    }

    public function bookingCourt(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'court_id' => 'required|exists:courts,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'duration_hours' => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,card,transfer,wallet',
            'notes' => 'nullable|string',
        ]);

        if($validation->fails())
        {
            return response()->json([
                'success' => false,
                'message' => $validation->errors()->first()
            ]);
        }

        // Get court details
        $court = Court::findOrFail($request->court_id);

        // Calculate duration in hours
        $startTime = \DateTime::createFromFormat('H:i', $request->start_time);
        
        $durationHours = (int) $request->duration_hours;
        $endTime = $startTime->modify('+' . $durationHours . ' hours');

        // Recalculate total price on server side with multi-price support
        $totalPrice = $this->calculateBookingTotalPrice(
            $court->id, 
            $request->booking_date, 
            $request->start_time, 
            $durationHours
        );

        $hourlyRate = (int) ($totalPrice / $durationHours);
        // Calculate service fee (5% of total price)
        $serviceFee = (int) round($totalPrice * 0.05);

        // Check if slot is already booked
        $existingBooking = Booking::where('court_id', $request->court_id)
            ->where('booking_date', $request->booking_date)
            ->where('status', '!=', 'cancelled')
            ->whereRaw("TIME(start_time) < ? AND TIME(end_time) > ?", [$endTime->format('H:i'), $request->start_time])
            ->first();

        if ($existingBooking) {
            return response()->json([
                'success' => false,
                'message' => 'Khoảng thời gian này đã được đặt. Vui lòng chọn thời gian khác.'
            ]);
        }

        // Create booking
        $booking = Booking::create([
            'court_id' => $request->court_id,
            'user_id' => auth()->id() ?? null,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'booking_date' => $request->booking_date,
            'start_time' => $request->start_time,
            'end_time' => $endTime->format('H:i'),
            'duration_hours' => $durationHours,
            'hourly_rate' => $hourlyRate,
            'total_price' => $totalPrice,
            'service_fee' => $serviceFee,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'notes' => $request->notes ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đặt sân thành công. Đơn đặt của bạn đang chờ xác nhận.',
            'booking' => [
                'id' => $booking->id,
                'booking_id' => 'BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
                'status' => $booking->status,
            ]
        ]);
        
    }
    
    private function calculateBookingTotalPrice($courtId, $bookingDate, $startTime, $durationHours)
    {
        $court = Court::findOrFail($courtId);
        $bookingDate = \Carbon\Carbon::createFromFormat('Y-m-d', $bookingDate);
        $dayOfWeek = $bookingDate->dayOfWeek;
        
        $startTimeObj = \DateTime::createFromFormat('H:i', $startTime);
        $totalPrice = 0;
        $currentTime = clone $startTimeObj;
        
        for ($i = 0; $i < $durationHours; $i++) {
            $hourStart = clone $currentTime;
            
            // Find matching court pricing for this hour
            $pricing = CourtPricing::where('court_id', $courtId)
                ->where('is_active', true)
                ->where(function ($query) use ($bookingDate) {
                    $query->whereNull('valid_from')
                          ->orWhere('valid_from', '<=', $bookingDate);
                })
                ->where(function ($query) use ($bookingDate) {
                    $query->whereNull('valid_to')
                          ->orWhere('valid_to', '>=', $bookingDate);
                })
                ->where(function ($query) use ($hourStart) {
                    $query->whereRaw('TIME(start_time) <= ?', [$hourStart->format('H:i:s')])
                          ->whereRaw('TIME(end_time) > ?', [$hourStart->format('H:i:s')]);
                })
                ->where(function ($query) use ($dayOfWeek) {
                    $query->whereNull('days_of_week')
                          ->orWhereJsonContains('days_of_week', $dayOfWeek);
                })
                ->orderByRaw('TIME(start_time) DESC')
                ->first();
            
            // Use court pricing if found, otherwise use court's rental_price
            $hourlyRate = $pricing ? $pricing->price_per_hour : ($court->rental_price ?? 0);
            $totalPrice += $hourlyRate;
            $currentTime->modify('+1 hour');
        }
        
        return $totalPrice;
    }
}
