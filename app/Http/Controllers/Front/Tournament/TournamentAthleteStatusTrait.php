<?php

namespace App\Http\Controllers\Front\Tournament;

use App\Models\Tournament;
use App\Models\TournamentAthlete;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait TournamentAthleteStatusTrait
{
    /**
     * Cập nhật trạng thái vận động viên
     */
    public function updateStatus(Request $request, Tournament $tournament, TournamentAthlete $athlete): JsonResponse
    {
        $this->authorizeOwner($tournament);
        abort_if($athlete->tournament_id !== $tournament->id, 404);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $athlete->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Cập nhật trạng thái thành công.',
            'status'  => $athlete->status,
        ]);
    }

    /**
     * Duyệt vận động viên
     */
    public function approve(Tournament $tournament, TournamentAthlete $athlete): JsonResponse
    {
        $this->authorizeOwner($tournament);
        abort_if($athlete->tournament_id !== $tournament->id, 404);

        $athlete->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Đã duyệt vận động viên.',
            'status'  => 'approved',
        ]);
    }

    /**
     * Từ chối vận động viên
     */
    public function reject(Tournament $tournament, TournamentAthlete $athlete): JsonResponse
    {
        $this->authorizeOwner($tournament);
        abort_if($athlete->tournament_id !== $tournament->id, 404);

        $athlete->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Đã từ chối vận động viên.',
            'status'  => 'rejected',
        ]);
    }

    /**
     * Duyệt hàng loạt vận động viên
     */
    public function bulkApprove(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:tournament_athletes,id',
        ]);

        $count = TournamentAthlete::where('tournament_id', $tournament->id)
            ->whereIn('id', $validated['ids'])
            ->update(['status' => 'approved']);

        return response()->json([
            'message' => "Đã duyệt {$count} vận động viên.",
            'count'   => $count,
        ]);
    }
}
