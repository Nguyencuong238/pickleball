<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\League;
use App\Models\LeagueRound;
use App\Services\LeagueScheduleService;
use App\Services\LeagueService;
use App\Services\LeagueStandingsService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class HomeYardLeagueController extends Controller
{
    public function __construct(
        private LeagueService $leagueService,
        private LeagueScheduleService $scheduleService,
        private LeagueStandingsService $standingsService
    ) {
        $this->middleware(['auth']);
    }

    public function index()
    {
        $userId = auth()->id();
        $leagues = League::where('user_id', $userId)
            ->withCount('teams')
            ->latest()
            ->paginate(12);

        $stats = [
            'total' => League::where('user_id', $userId)->count(),
            'active' => League::where('user_id', $userId)->where('status', 'active')->count(),
            'completed' => League::where('user_id', $userId)->where('status', 'completed')->count(),
        ];

        return view('home-yard.leagues.index', compact('leagues', 'stats'));
    }

    public function create()
    {
        $clubs = $this->getUserClubs();

        return view('home-yard.leagues.create', compact('clubs'));
    }

    public function store(Request $request)
    {
        $minPlayers = $request->input('competition_format') === 'mlp' ? 4 : 2;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'season_name' => 'nullable|string|max:100',
            'club_id' => 'nullable|exists:clubs,id',
            'competition_format' => 'nullable|in:traditional,mlp',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'registration_deadline' => 'nullable|date',
            'config' => 'nullable|array',
            'config.match_format' => 'nullable|array',
            'config.max_teams' => 'nullable|integer|min:2|max:32',
            'config.max_players_per_team' => "nullable|integer|min:{$minPlayers}|max:20",
            'config.points_for_win' => 'nullable|integer|min:0',
            'config.points_for_loss' => 'nullable|integer|min:0',
            'required_players_per_registration' => 'nullable|integer|in:1,2,4',
            'registration_fee' => 'nullable|numeric|min:0',
            'qr_code_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'config.max_players_per_team.min' => "Thể thức MLP yêu cầu tối thiểu {$minPlayers} VĐV/đội.",
        ]);

        if ($request->hasFile('qr_code_image')) {
            $imagePath = $request->file('qr_code_image')->store('leagues/qr_codes', config('filesystems.default'));
            $validated['qr_code_image'] = $imagePath;
        }

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('leagues/logos', config('filesystems.default'));
            $validated['logo'] = $logoPath;
        }

        $league = $this->leagueService->createLeague(auth()->user(), $validated);

        return redirect()
            ->route('homeyard.leagues.show', $league)
            ->with('success', 'Tạo giải đấu thành công.');
    }

    public function show(League $league)
    {
        abort_if($league->user_id !== auth()->id(), 403);

        $league->load([
            'club',
            'teams.captain',
            'teams.players.user',
            'rounds.matches.homeTeam',
            'rounds.matches.awayTeam',
            'rounds.matches.games.homePlayer1.user',
            'rounds.matches.games.homePlayer2.user',
            'rounds.matches.games.awayPlayer1.user',
            'rounds.matches.games.awayPlayer2.user',
            'standings.team',
        ]);

        // Sắp xếp standings theo rank
        $league->setRelation(
            'standings',
            $league->standings->sortBy('rank')->values()
        );

        $pendingRegistrationCount = $league->registrations()
            ->where('status', 'pending')->count();

        return view('home-yard.leagues.show', compact('league', 'pendingRegistrationCount'));
    }

    public function edit(League $league)
    {
        abort_if($league->user_id !== auth()->id(), 403);

        $clubs = $this->getUserClubs();

        return view('home-yard.leagues.edit', compact('league', 'clubs'));
    }

    public function update(Request $request, League $league)
    {
        abort_if($league->user_id !== auth()->id(), 403);

        $minPlayers = ($request->input('competition_format') ?? $league->competition_format) === 'mlp' ? 4 : 2;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'season_name' => 'nullable|string|max:100',
            'club_id' => 'nullable|exists:clubs,id',
            'competition_format' => 'nullable|in:traditional,mlp',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'registration_deadline' => 'nullable|date',
            'config' => 'nullable|array',
            'config.match_format' => 'nullable|array',
            'config.max_teams' => 'nullable|integer|min:2|max:32',
            'config.max_players_per_team' => "nullable|integer|min:{$minPlayers}|max:20",
            'config.points_for_win' => 'nullable|integer|min:0',
            'config.points_for_loss' => 'nullable|integer|min:0',
            'required_players_per_registration' => 'nullable|integer|in:1,2,4',
            'registration_fee' => 'nullable|numeric|min:0',
            'qr_code_image' => ($league->qr_code_image ? 'nullable' : 'required') . '|image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'config.max_players_per_team.min' => "Thể thức MLP yêu cầu tối thiểu {$minPlayers} VĐV/đội.",
        ]);

        if ($request->hasFile('qr_code_image')) {
            $imagePath = $request->file('qr_code_image')->store('leagues/qr_codes', config('filesystems.default'));
            $validated['qr_code_image'] = $imagePath;
        }

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('leagues/logos', config('filesystems.default'));
            $validated['logo'] = $logoPath;
        }

        $this->leagueService->updateLeague($league, $validated);

        return redirect()
            ->back()
            ->with('success', 'Cập nhật giải đấu thành công.');
    }

    public function destroy(League $league)
    {
        abort_if($league->user_id !== auth()->id(), 403);

        try {
            $this->leagueService->deleteLeague($league);
            return redirect()
                ->route('homeyard.leagues.index')
                ->with('success', 'Xóa giải đấu thành công.');
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request, League $league)
    {
        abort_if($league->user_id !== auth()->id(), 403);

        $request->validate([
            'status' => 'required|in:draft,registration,active,completed,cancelled',
        ]);

        try {
            $this->leagueService->updateStatus($league, $request->status);
            return redirect()
                ->back()
                ->with('success', 'Cập nhật trạng thái thành công.');
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function generateSchedule(League $league)
    {
        abort_if($league->user_id !== auth()->id(), 403);

        $activeTeams = $league->teams()->active()->count();
        if ($activeTeams < 2) {
            return redirect()
                ->back()
                ->with('error', 'Cần tối thiểu 2 đội để tạo lịch thi đấu.');
        }

        try {
            if ($league->competition_format === 'mlp') {
                $this->scheduleService->validateMlpTeams($league);
            }
            $this->scheduleService->generateRoundRobin($league);
            $this->standingsService->initializeStandings($league);

            return redirect()
                ->to(route('homeyard.leagues.show', $league) . '#schedule')
                ->with('success', 'Tạo lịch thi đấu thành công.');
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function updateRound(Request $request, League $league, LeagueRound $round)
    {
        abort_if($league->user_id !== auth()->id(), 403);
        abort_if($round->league_id !== $league->id, 404);

        $validated = $request->validate([
            'scheduled_date' => 'nullable|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'venue' => 'nullable|string|max:255',
        ]);

        $round->update($validated);

        return redirect()
            ->back()
            ->with('success', 'Cập nhật thông tin vòng đấu thành công.');
    }

    private function getUserClubs()
    {
        $userId = auth()->id();

        return Club::whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->select('id', 'name')->get();
    }
}
