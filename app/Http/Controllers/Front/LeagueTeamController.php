<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\League;
use App\Models\LeagueTeam;
use App\Models\LeagueTeamPlayer;
use App\Services\LeagueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class LeagueTeamController extends Controller
{
    public function __construct(private LeagueService $leagueService)
    {
        $this->middleware(['auth']);
    }

    public function store(Request $request, League $league)
    {
        abort_if($league->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'captain_user_id' => 'nullable|exists:users,id',
            'logo' => 'nullable|image|max:2048',
        ]);

        // Xử lý upload logo
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')
                ->store('leagues/teams', 'public');
        }

        try {
            $team = $this->leagueService->addTeam($league, $validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thêm đội thành công.',
                    'team' => $team->load('captain'),
                ]);
            }

            return redirect()->to(route('homeyard.leagues.show', $league) . '#teams')->with('success', 'Thêm đội thành công.');
        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, League $league, LeagueTeam $team)
    {
        abort_if($league->user_id !== auth()->id(), 403);
        abort_if($team->league_id !== $league->id, 404);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'captain_user_id' => 'nullable|exists:users,id',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            // Xóa logo cũ nếu có
            if ($team->logo) {
                Storage::disk('public')->delete($team->logo);
            }
            $validated['logo'] = $request->file('logo')
                ->store('leagues/teams', 'public');
        }

        $team = $this->leagueService->updateTeam($team, $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật đội thành công.',
                'team' => $team,
            ]);
        }

        return redirect()->to(route('homeyard.leagues.show', $league) . '#teams')->with('success', 'Cập nhật đội thành công.');
    }

    public function destroy(Request $request, League $league, LeagueTeam $team)
    {
        abort_if($league->user_id !== auth()->id(), 403);
        abort_if($team->league_id !== $league->id, 404);

        try {
            $this->leagueService->removeTeam($team);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Xóa đội thành công.']);
            }

            return redirect()->back()->with('success', 'Xóa đội thành công.');
        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function addPlayer(Request $request, League $league, LeagueTeam $team)
    {
        abort_if($league->user_id !== auth()->id(), 403);
        abort_if($team->league_id !== $league->id, 404);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'position' => 'nullable|string|max:50',
            'gender' => 'required|in:male,female',
        ]);

        try {
            $player = $this->leagueService->addPlayer($team, $validated);

            // Tự set captain nếu đội chưa có
            if (!$team->captain_user_id) {
                $team->update(['captain_user_id' => $validated['user_id']]);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thêm người chơi thành công.',
                    'player' => $player->load('user'),
                ]);
            }

            return redirect()->to(route('homeyard.leagues.show', $league) . '#teams')->with('success', 'Thêm người chơi thành công.');
        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function updatePlayerOrder(Request $request, League $league, LeagueTeam $team)
    {
        abort_if($league->user_id !== auth()->id(), 403);
        abort_if($team->league_id !== $league->id, 404);

        $validated = $request->validate([
            'player_ids' => 'required|array',
            'player_ids.*' => 'integer|exists:league_team_players,id',
        ]);

        foreach ($validated['player_ids'] as $order => $playerId) {
            LeagueTeamPlayer::where('id', $playerId)
                ->where('league_team_id', $team->id)
                ->update(['order' => $order + 1]);
        }

        return response()->json(['success' => true, 'message' => 'Cập nhật thứ tự VĐV thành công.']);
    }

    public function removePlayer(Request $request, League $league, LeagueTeam $team, LeagueTeamPlayer $player)
    {
        abort_if($league->user_id !== auth()->id(), 403);
        abort_if($team->league_id !== $league->id, 404);
        abort_if($player->league_team_id !== $team->id, 404);

        $this->leagueService->removePlayer($player);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Xóa người chơi thành công.']);
        }

        return redirect()->to(route('homeyard.leagues.show', $league) . '#teams')->with('success', 'Xóa người chơi thành công.');
    }
}
