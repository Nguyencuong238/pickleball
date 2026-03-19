<?php

namespace App\Http\Controllers\Front\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupStanding;
use App\Models\MatchModel;
use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Services\Tournament\TournamentStandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TournamentRankingController extends Controller
{
    public function __construct(
        private TournamentStandingService $standingService
    ) {
        $this->middleware(['auth']);
    }

    /**
     * Trang xep hang tong hop
     */
    public function index(Request $request, Tournament $tournament): mixed
    {
        abort_unless($tournament->user_id === auth()->id(), 403);

        $categories = TournamentCategory::where('tournament_id', $tournament->id)
            ->select('id', 'category_name', 'category_type')
            ->get();

        if ($request->wantsJson()) {
            $defaultCategory = $categories->first();
            if (!$defaultCategory) {
                return response()->json(['groups' => []]);
            }
            return $this->buildCategoryRankingsResponse($tournament, $defaultCategory->id);
        }

        return view('home-yard.tournaments.rankings', compact('tournament', 'categories'));
    }

    /**
     * Xep hang theo noi dung thi dau
     */
    public function getCategoryRankings(Tournament $tournament, int $categoryId): JsonResponse
    {
        abort_unless($tournament->user_id === auth()->id(), 403);

        return $this->buildCategoryRankingsResponse($tournament, $categoryId);
    }

    /**
     * Danh sach bang dau theo noi dung
     */
    public function getCategoryGroups(Tournament $tournament, int $categoryId): JsonResponse
    {
        abort_unless($tournament->user_id === auth()->id(), 403);
        $groups = Group::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->select('id', 'group_name', 'advancing_count')
            ->get()
            ->map(fn($g) => [
                'id' => $g->id,
                'group_name' => $g->group_name,
                'advancing_count' => $g->advancing_count ?? 0,
            ]);

        return response()->json(['groups' => $groups]);
    }

    /**
     * Build standings JSON response for a category
     */
    private function buildCategoryRankingsResponse(Tournament $tournament, int $categoryId): JsonResponse
    {
        $category = TournamentCategory::find($categoryId);
        $isDoubles = $category && str_contains($category->category_type ?? '', 'double');

        $groups = Group::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->with([
                'standings' => fn($q) => $q->with([
                    'athlete' => fn($aq) => $aq->select('id', 'athlete_name', 'category_id', 'partner_id'),
                    'athlete.partner' => fn($pq) => $pq->select('id', 'athlete_name'),
                ])->orderBy('rank_position'),
            ])
            ->get();

        // Compute cumulative game scores from set_scores JSON
        $groupIds = $groups->pluck('id');
        $matches = MatchModel::whereIn('group_id', $groupIds)
            ->where('status', 'completed')
            ->whereNotNull('set_scores')
            ->select('group_id', 'athlete1_id', 'athlete2_id', 'set_scores')
            ->get();

        $gameScores = [];
        foreach ($matches as $m) {
            $setScores = $m->set_scores ?? [];
            $total1 = $total2 = 0;
            foreach ($setScores as $set) {
                $total1 += $set['athlete1_score'] ?? $set['athlete1'] ?? 0;
                $total2 += $set['athlete2_score'] ?? $set['athlete2'] ?? 0;
            }
            $gameScores[$m->group_id][$m->athlete1_id]['scored'] =
                ($gameScores[$m->group_id][$m->athlete1_id]['scored'] ?? 0) + $total1;
            $gameScores[$m->group_id][$m->athlete1_id]['conceded'] =
                ($gameScores[$m->group_id][$m->athlete1_id]['conceded'] ?? 0) + $total2;
            $gameScores[$m->group_id][$m->athlete2_id]['scored'] =
                ($gameScores[$m->group_id][$m->athlete2_id]['scored'] ?? 0) + $total2;
            $gameScores[$m->group_id][$m->athlete2_id]['conceded'] =
                ($gameScores[$m->group_id][$m->athlete2_id]['conceded'] ?? 0) + $total1;
        }

        $groupsData = $groups->map(function (Group $group) use ($isDoubles, $gameScores) {
            $standings = $group->standings;

            if ($isDoubles) {
                // Deduplicate: for each pair (A<->B), keep the standing with actual match data
                $dedupMap = [];
                foreach ($standings as $standing) {
                    $athleteId = $standing->athlete_id;
                    $partnerId = $standing->athlete?->partner_id;

                    if ($partnerId && isset($dedupMap[$partnerId])) {
                        $existing = $dedupMap[$partnerId];
                        if ($standing->matches_played > $existing->matches_played) {
                            unset($dedupMap[$partnerId]);
                            $dedupMap[$athleteId] = $standing;
                        }
                    } else {
                        $dedupMap[$athleteId] = $standing;
                    }
                }
                $standings = collect(array_values($dedupMap))->sortBy('rank_position')->values();
            }

            $standings = $standings->map(function (GroupStanding $standing, int $index) use ($group, $gameScores) {
                $athlete = $standing->athlete;
                $athleteName = $athlete?->athlete_name ?? 'N/A';
                if ($athlete?->partner) {
                    $athleteName .= ' / ' . $athlete->partner->athlete_name;
                }

                return [
                    'rank'         => $index + 1,
                    'athlete_id'   => $standing->athlete_id,
                    'athlete_name' => $athleteName,
                    'played'       => $standing->matches_played ?? 0,
                    'won'          => $standing->matches_won ?? 0,
                    'lost'         => $standing->matches_lost ?? 0,
                    'sets_won'     => $standing->sets_won ?? 0,
                    'sets_lost'    => $standing->sets_lost ?? 0,
                    'set_diff'     => $standing->sets_differential ?? 0,
                    'games_scored'   => $gameScores[$group->id][$standing->athlete_id]['scored'] ?? 0,
                    'games_conceded' => $gameScores[$group->id][$standing->athlete_id]['conceded'] ?? 0,
                    'points'       => $standing->points ?? 0,
                    'is_advanced'  => (bool) ($standing->is_advanced ?? false),
                ];
            });

            return [
                'group_id'        => $group->id,
                'group_name'      => $group->group_name,
                'advancing_count' => $group->advancing_count ?? 0,
                'standings'       => $standings,
            ];
        });

        return response()->json([
            'groups'      => $groupsData,
            'category_id' => $categoryId,
        ]);
    }
}
