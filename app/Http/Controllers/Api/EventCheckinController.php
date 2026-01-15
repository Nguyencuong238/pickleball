<?php

namespace App\Http\Controllers\Api;

use App\Events\EventCheckedIn;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCheckin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventCheckinController extends Controller
{
    /**
     * Get ongoing events list for check-in
     */
    public function getEvents(): JsonResponse
    {
        $events = Event::active()
            ->ongoing()
            ->with('stadium:id,name')
            ->select(['id', 'uuid', 'title', 'location', 'stadium_id', 'start_datetime', 'end_datetime', 'points', 'max_attendees'])
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->uuid,
                    'title' => $event->title,
                    'location' => $event->location,
                    'stadium' => $event->stadium?->name,
                    'start_datetime' => $event->start_datetime->format('Y-m-d H:i'),
                    'end_datetime' => $event->end_datetime->format('Y-m-d H:i'),
                    'points' => $event->points,
                    'remaining_slots' => $event->getRemainingSlots(),
                    'status' => $event->getStatusLabel(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * Check-in to an event via QR code
     */
    public function checkin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_code' => 'required|string|min:8|max:255|regex:/^EVT-[A-Z0-9]+$/i',
        ], [
            'qr_code.regex' => 'Ma QR khong dung dinh dang',
            'qr_code.min' => 'Ma QR qua ngan',
            'qr_code.max' => 'Ma QR qua dai',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui long dang nhap',
            ], 401);
        }

        // Find event by QR code
        $event = Event::findByQrCode($validated['qr_code']);
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Ma QR khong hop le',
            ], 404);
        }

        // Check if event is active and ongoing
        if (!$event->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Su kien khong hoat dong',
            ], 400);
        }

        if (!$event->isOngoing()) {
            if ($event->isUpcoming()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Su kien chua bat dau',
                ], 400);
            }
            if ($event->hasEnded()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Su kien da ket thuc',
                ], 400);
            }
        }

        // Check capacity
        if ($event->hasReachedLimit()) {
            return response()->json([
                'success' => false,
                'message' => 'Su kien da het cho',
            ], 400);
        }

        try {
            // Use transaction + unique constraint to prevent race condition
            $checkin = DB::transaction(function () use ($event, $user) {
                // Create check-in record (DB unique constraint prevents duplicates)
                $checkin = EventCheckin::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'checked_in_at' => now(),
                    'check_in_method' => Event::CHECK_IN_QR_CODE,
                ]);

                // Dispatch event for point earning
                event(new EventCheckedIn($user, $event));

                return $checkin;
            });

            Log::info('Event check-in successful', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'checkin_id' => $checkin->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check-in thanh cong',
                'data' => [
                    'event_title' => $event->title,
                    'points_earned' => $event->points,
                    'checked_in_at' => $checkin->checked_in_at->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (UniqueConstraintViolationException $e) {
            // Race condition caught - user already checked in
            return response()->json([
                'success' => false,
                'message' => 'Ban da check-in su kien nay roi',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Event check-in failed', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Khong the check-in, vui long thu lai',
            ], 500);
        }
    }

    /**
     * Get user's check-in history
     */
    public function history(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui long dang nhap',
            ], 401);
        }

        $checkins = EventCheckin::where('user_id', $user->id)
            ->with('event:id,uuid,title,location,points,start_datetime')
            ->orderByDesc('checked_in_at')
            ->limit(50)
            ->get()
            ->map(function ($checkin) {
                return [
                    'id' => $checkin->id,
                    'event_id' => $checkin->event->uuid,
                    'event_title' => $checkin->event->title,
                    'location' => $checkin->event->location,
                    'points' => $checkin->event->points,
                    'checked_in_at' => $checkin->checked_in_at->format('Y-m-d H:i'),
                    'method' => $checkin->check_in_method,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $checkins,
        ]);
    }
}
