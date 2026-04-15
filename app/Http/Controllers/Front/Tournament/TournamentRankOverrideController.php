<?php

namespace App\Http\Controllers\Front\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupStanding;
use App\Models\Tournament;
use App\Services\Tournament\TournamentStandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Single-action controller cho BTC nhập manual_rank_override sau khi tier 1-4 vẫn tied.
 */
class TournamentRankOverrideController extends Controller
{
    public function __construct(
        private TournamentStandingService $standingService,
        private TournamentRankingController $rankingController,
    ) {
        $this->middleware(['auth']);
    }

    public function update(Request $request, Tournament $tournament, int $group): JsonResponse
    {
        abort_unless($tournament->user_id === Auth::id(), 403);

        $groupModel = Group::where('id', $group)
            ->whereHas('category', fn($q) => $q->where('tournament_id', $tournament->id))
            ->firstOrFail();

        $data = $request->validate([
            'overrides'              => ['required', 'array', 'min:1'],
            'overrides.*.athlete_id' => ['required', 'integer'],
            'overrides.*.rank'       => ['nullable', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($data, $group) {
            foreach ($data['overrides'] as $override) {
                GroupStanding::where('group_id', $group)
                    ->where('athlete_id', $override['athlete_id'])
                    ->update(['manual_rank_override' => $override['rank']]);
            }
            $this->standingService->recalculateGroupRankings($group);
        });

        Log::info('rank_override', [
            'user_id'       => Auth::id(),
            'tournament_id' => $tournament->id,
            'group_id'      => $group,
            'overrides'     => $data['overrides'],
        ]);

        return $this->rankingController->getCategoryRankings($tournament, $groupModel->category_id);
    }
}
