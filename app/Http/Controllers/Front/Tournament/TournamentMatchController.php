<?php

namespace App\Http\Controllers\Front\Tournament;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\Tournament\Traits\MatchListFormatterTrait;
use App\Http\Controllers\Front\Tournament\Traits\MatchScheduleTrait;
use App\Http\Controllers\Front\Tournament\Traits\MatchScoreTrait;
use App\Models\Group;
use App\Models\MatchModel;
use App\Models\Tournament;
use App\Services\Tournament\TournamentMatchService;
use App\Services\Tournament\TournamentStandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TournamentMatchController extends Controller
{
    use MatchScoreTrait, MatchListFormatterTrait, MatchScheduleTrait;

    public function __construct(
        private TournamentMatchService $matchService,
        private TournamentStandingService $standingService
    ) {
        $this->middleware(['auth']);
    }

    public function index(Request $request, Tournament $tournament): mixed
    {
        abort_unless($tournament->user_id === auth()->id(), 403);

        $categories = $tournament->categories()->get();

        if ($request->wantsJson() || $request->ajax()) {
            return $this->jsonMatchList($request, $tournament);
        }

        return view('home-yard.tournaments.matches', compact('tournament', 'categories'));
    }

    public function store(Request $request, Tournament $tournament): JsonResponse
    {
        abort_unless($tournament->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'category_id' => 'required|integer',
            'group_id'    => 'nullable|integer',
            'athlete1_id' => 'required|integer',
            'athlete2_id' => 'required|integer|different:athlete1_id',
            'match_date'  => 'nullable|date',
            'match_time'  => 'nullable|string',
            'best_of'     => 'nullable|integer|in:1,3,5',
        ]);

        try {
            $match = DB::transaction(function () use ($tournament, $validated) {
                $matchNumber = MatchModel::where('tournament_id', $tournament->id)
                    ->where('category_id', $validated['category_id'])
                    ->lockForUpdate()
                    ->max('match_number') + 1;

                return MatchModel::create([
                    'tournament_id' => $tournament->id,
                    'category_id'   => $validated['category_id'],
                    'group_id'      => $validated['group_id'] ?? null,
                    'athlete1_id'   => $validated['athlete1_id'],
                    'athlete2_id'   => $validated['athlete2_id'],
                    'match_number'  => $matchNumber,
                    'status'        => 'scheduled',
                    'best_of'       => $validated['best_of'] ?? 3,
                    'match_date'    => $validated['match_date'] ?? null,
                    'match_time'    => $validated['match_time'] ?? null,
                ]);
            });

            $match->load(['athlete1', 'athlete2', 'group', 'category']);

            return response()->json(['success' => true, 'match' => $match]);
        } catch (\Exception $e) {
            Log::error('Tạo trận đấu thất bại: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Tạo trận đấu thất bại'], 500);
        }
    }

    public function show(Tournament $tournament, MatchModel $match): JsonResponse
    {
        abort_unless($tournament->user_id === auth()->id(), 403);
        abort_unless($match->tournament_id === $tournament->id, 404);

        $match->load(['athlete1', 'athlete2', 'group', 'category', 'court']);

        return response()->json([
            'success' => true,
            'match'   => [
                'id'             => $match->id,
                'match_number'   => $match->match_number,
                'athlete1_id'    => $match->athlete1_id,
                'athlete1_name'  => $match->athlete1_name ?? ($match->athlete1->athlete_name ?? 'TBD'),
                'athlete2_id'    => $match->athlete2_id,
                'athlete2_name'  => $match->athlete2_name ?? ($match->athlete2->athlete_name ?? 'TBD'),
                'athlete1_score' => $match->athlete1_score,
                'athlete2_score' => $match->athlete2_score,
                'winner_id'      => $match->winner_id,
                'status'         => $match->status,
                'final_score'    => $match->final_score,
                'set_scores'     => $match->set_scores ?? [],
                'best_of'        => $match->best_of ?? 3,
                'match_date'     => $match->match_date?->format('d/m/Y'),
                'match_time'     => $match->match_time,
                'court_name'     => $match->court->name ?? null,
                'category_name'  => $match->category->category_name ?? null,
                'group_name'     => $match->group->group_name ?? null,
            ],
        ]);
    }

    public function updateScore(Request $request, Tournament $tournament, MatchModel $match): JsonResponse
    {
        abort_unless($tournament->user_id === auth()->id(), 403);
        abort_unless($match->tournament_id === $tournament->id, 404);

        return $this->processScoreUpdate($request, $match, $this->standingService);
    }

    public function destroy(Tournament $tournament, MatchModel $match): JsonResponse
    {
        abort_unless($tournament->user_id === auth()->id(), 403);
        abort_unless($match->tournament_id === $tournament->id, 404);

        try {
            $groupId = $match->group_id;
            $match->delete();

            if ($groupId) {
                $this->standingService->recalculateGroupRankings($groupId);
            }

            return response()->json(['success' => true, 'message' => 'Đã xóa trận đấu']);
        } catch (\Exception $e) {
            Log::error('Xóa trận đấu thất bại: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Xóa trận đấu thất bại'], 500);
        }
    }

    public function createForGroups(Request $request, Tournament $tournament): JsonResponse
    {
        abort_unless($tournament->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'category_id' => 'required|integer',
            'best_of'     => 'nullable|integer|in:1,3,5',
        ]);
        $categoryId = (int) $validated['category_id'];
        $bestOf = (int) ($validated['best_of'] ?? 3);

        try {
            $groups = Group::where('tournament_id', $tournament->id)
                ->where('category_id', $categoryId)
                ->get();

            if ($groups->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có bảng nào. Hãy thực hiện bốc thăm trước.',
                ], 422);
            }

            $this->matchService->createMatchesForGroups($tournament, $categoryId, $groups, $bestOf);

            $count = MatchModel::where('tournament_id', $tournament->id)
                ->where('category_id', $categoryId)
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Đã tạo ' . $count . ' trận đấu',
                'count'   => $count,
            ]);
        } catch (\Exception $e) {
            Log::error('Tạo trận đấu vòng bảng thất bại: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Tạo trận đấu thất bại'], 500);
        }
    }
}
