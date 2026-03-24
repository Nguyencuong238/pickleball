<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClubDashboardController extends Controller
{
    public function dashboard(Club $club, ClubActivity $activity): View
    {
        $this->authorize('manageActivity', $club);

        if ($activity->club_id !== $club->id) {
            abort(404);
        }

        return view('home-yard.clubs.activity-dashboard', compact('club', 'activity'));
    }

    public function dashboardState(Club $club, ClubActivity $activity): JsonResponse
    {
        $this->authorize('manageActivity', $club);

        if ($activity->club_id !== $club->id) {
            abort(404);
        }

        $courts = [];
        for ($i = 1; $i <= $activity->courts_count; $i++) {
            $match = $activity->matches()
                ->where('scheduled_court', $i)
                ->whereNotNull('started_at')
                ->whereNull('ended_at')
                ->with(['player1:id,name', 'player2:id,name', 'player3:id,name', 'player4:id,name'])
                ->first();

            $courts[] = [
                'court' => $i,
                'status' => $match ? 'playing' : 'available',
                'match' => $match,
            ];
        }

        return response()->json([
            'courts' => $courts,
            'queue' => $activity->participants()
                ->queued()
                ->with('user:id,name,total_oprs')
                ->orderBy('queue_position')
                ->get(),
            'playing' => $activity->participants()
                ->playing()
                ->with('user:id,name')
                ->get(),
            'stats' => [
                'total_checked_in' => $activity->participants()->checkedIn()->count(),
                'total_matches' => $activity->matches()->count(),
                'queued_count' => $activity->participants()->queued()->count(),
                'playing_count' => $activity->participants()->playing()->count(),
            ],
        ]);
    }

    public function members(Club $club): View
    {
        $this->authorize('manageActivity', $club);

        $members = $club->members()
            ->withPivot('role', 'initial_oprs', 'notes', 'member_status')
            ->paginate(20);

        return view('home-yard.clubs.members', compact('club', 'members'));
    }

    public function addMember(Club $club, Request $request): JsonResponse
    {
        $this->authorize('manageActivity', $club);

        $validated = $request->validate([
            'phone' => 'required|string',
            'initial_oprs' => 'nullable|numeric|min:0|max:999.99',
        ]);

        $phone = preg_replace('/\s+/', '', $validated['phone']);
        if (str_starts_with($phone, '+84')) {
            $phone = '0' . substr($phone, 3);
        }

        $user = User::where('phone', $phone)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng với số điện thoại này.',
            ], 404);
        }

        if ($club->members()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng đã là thành viên.',
            ], 422);
        }

        $club->members()->attach($user->id, [
            'role' => 'member',
            'initial_oprs' => $validated['initial_oprs'] ?? null,
            'member_status' => 'active',
            'joined_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Đã thêm thành viên.']);
    }

    public function updateMemberOprs(Club $club, User $user, Request $request): JsonResponse
    {
        $this->authorize('manageActivity', $club);

        $request->validate(['initial_oprs' => 'required|numeric|min:0|max:999.99']);

        $club->members()->updateExistingPivot($user->id, [
            'initial_oprs' => $request->initial_oprs,
        ]);

        return response()->json(['success' => true]);
    }

    public function updateMemberStatus(Club $club, User $user, Request $request): JsonResponse
    {
        $this->authorize('manageActivity', $club);

        $request->validate(['member_status' => 'required|in:active,inactive,suspended']);

        $club->members()->updateExistingPivot($user->id, [
            'member_status' => $request->member_status,
        ]);

        return response()->json(['success' => true]);
    }
}
