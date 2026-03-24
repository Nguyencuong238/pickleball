<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubMemberStat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClubLeaderboardController extends Controller
{
    public function index(Club $club, Request $request): View|JsonResponse
    {
        $period = $request->get('period', 'month');

        $query = ClubMemberStat::where('club_id', $club->id)
            ->with('user:id,name,avatar,total_oprs,opr_level');

        if ($period === 'month') {
            $query->where('last_played_at', '>=', now()->startOfMonth());
        }

        $leaderboard = $query->orderByDesc('current_oprs')->get();

        if ($request->wantsJson()) {
            return response()->json($leaderboard);
        }

        return view('front.clubs.leaderboard', compact('club', 'leaderboard', 'period'));
    }
}
