<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubActivity;
use App\Services\ClubActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class ClubActivityParticipantController extends Controller
{
    public function __construct(private ClubActivityService $service)
    {
    }

    /**
     * Display participants for activity.
     */
    public function index(Club $club, ClubActivity $activity)
    {
        $this->authorize('view', $club);

        if ($activity->club_id !== $club->id) {
            return response()->json(['message' => 'Không tìm thấy'], 404);
        }

        $confirmed = $activity->confirmedParticipants()->with('user:id,name,avatar')->get();
        $waitlisted = $activity->waitlistedParticipants()->with('user:id,name,avatar')->get();

        return response()->json([
            'confirmed' => $confirmed,
            'waitlisted' => $waitlisted,
            'spots_left' => $activity->spotsLeft(),
        ]);
    }

    /**
     * RSVP to activity.
     */
    public function store(Request $request, Club $club, ClubActivity $activity)
    {
        $this->authorize('joinActivity', $club);

        if ($activity->club_id !== $club->id) {
            return response()->json(['message' => 'Không tìm thấy'], 404);
        }

        try {
            $participant = $this->service->rsvp($activity, Auth::user());

            return response()->json([
                'success' => true,
                'status' => $participant->status,
                'message' => $participant->status === 'confirmed'
                    ? 'Đăng ký thành công!'
                    : 'Bạn đã được thêm vào danh sách chờ.',
                'data' => $participant,
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel RSVP.
     */
    public function destroy(Club $club, ClubActivity $activity)
    {
        $this->authorize('joinActivity', $club);

        if ($activity->club_id !== $club->id) {
            return response()->json(['message' => 'Không tìm thấy'], 404);
        }

        try {
            $this->service->cancelRsvp($activity, Auth::user());

            return response()->json([
                'success' => true,
                'message' => 'Đã hủy đăng ký.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy đăng ký.',
            ], 422);
        }
    }
}
