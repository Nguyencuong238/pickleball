<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubActivity;
use App\Services\ClubActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class ClubActivityParticipantController extends Controller
{
    public function __construct(private ClubActivityService $service)
    {
    }

    /**
     * POST /clubs/{club}/activities/{activity}/rsvp
     */
    public function rsvp(Club $club, ClubActivity $activity): JsonResponse
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
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * DELETE /clubs/{club}/activities/{activity}/rsvp
     */
    public function cancelRsvp(Club $club, ClubActivity $activity): JsonResponse
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

    /**
     * GET /clubs/{club}/activities/{activity}/participants
     */
    public function index(Club $club, ClubActivity $activity): JsonResponse
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
}
