<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubActivity;
use App\Services\ClubActivityService;
use App\Services\GemWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use RuntimeException;

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

            $response = [
                'success' => true,
                'status' => $participant->status,
                'message' => $participant->status === 'confirmed'
                    ? 'Đăng ký thành công!'
                    : 'Bạn đã được thêm vào danh sách chờ.',
                'data' => $participant,
            ];

            if ($participant->status === 'confirmed' && $activity->hasFee()) {
                $response['gems_charged'] = $activity->fee_gems;
                $response['message'] = "Đăng ký thành công! Đã trừ {$activity->fee_gems} Gems.";
            }

            return response()->json($response, 201);
        } catch (RuntimeException $e) {
            $balance = app(GemWalletService::class)->getBalance(Auth::user());
            return response()->json([
                'success' => false,
                'message' => 'Số dư Gems không đủ. Cần ' . $activity->fee_gems . ' Gems, hiện có ' . $balance . ' Gems.',
                'insufficient_gems' => true,
                'required' => $activity->fee_gems,
                'balance' => $balance,
            ], 422);
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
            $result = $this->service->cancelRsvp($activity, Auth::user());
            $gemsRefunded = $result['gems_refunded'];

            $message = $gemsRefunded > 0
                ? "Đã hủy đăng ký. Hoàn {$gemsRefunded} Gems."
                : 'Đã hủy đăng ký.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'gems_refunded' => $gemsRefunded,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy đăng ký.',
            ], 422);
        }
    }
}
