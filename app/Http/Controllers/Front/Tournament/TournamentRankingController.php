<?php

namespace App\Http\Controllers\Front\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupStanding;
use App\Models\MatchModel;
use App\Models\Tournament;
use App\Models\TournamentCategory;
use App\Services\Tournament\DoublesStandingDeduplicator;
use App\Services\Tournament\GroupRankingSorter;
use App\Services\Tournament\TournamentStandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TournamentRankingController extends Controller
{
    public function __construct(
        private TournamentStandingService $standingService,
        private GroupRankingSorter $rankingSorter,
        private DoublesStandingDeduplicator $doublesDedup,
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

        return view('home-yard.tournaments.tournament-rankings', compact('tournament', 'categories'));
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
                    'athlete' => fn($aq) => $aq->select('id', 'athlete_name', 'category_id', 'partner_id', 'group_id'),
                    'athlete.partner' => fn($pq) => $pq->select('id', 'athlete_name'),
                ])->orderBy('rank_position'),
            ])
            ->orderBy('group_code')
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
            // Loc standing stale: athlete.group_id phai trung group.id moi hien thi.
            // Phong ngua truong hop legacy data con sot sau khi athletes bi re-draw.
            $validStandings = $group->standings->filter(
                fn(GroupStanding $s) => $s->athlete && (int) $s->athlete->group_id === (int) $group->id
            )->values();

            $standings = $isDoubles
                ? $this->doublesDedup->dedupe($validStandings)
                : $validStandings;

            $tiedIds = array_flip($this->rankingSorter->getUnresolvedTiedAthleteIds(
                $standings->all(),
                $group->id
            ));

            $standingsArray = $standings->map(function (GroupStanding $standing, int $index) use ($group, $gameScores, $tiedIds) {
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
                    'points'             => $standing->points ?? 0,
                    'games_differential' => $standing->games_differential ?? 0,
                    'games_lost'         => $standing->games_lost ?? 0,
                    'manual_rank_override' => $standing->manual_rank_override,
                    'is_tied'            => isset($tiedIds[(int) $standing->athlete_id]),
                    'is_advanced'        => (bool) ($standing->is_advanced ?? false),
                ];
            })->all();

            return [
                'group_id'        => $group->id,
                'group_name'      => $group->group_name,
                'advancing_count' => $group->advancing_count ?? 0,
                'standings'       => $standingsArray,
            ];
        });

        return response()->json([
            'groups'      => $groupsData,
            'category_id' => $categoryId,
        ]);
    }

}
