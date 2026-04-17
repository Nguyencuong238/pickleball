<?php

namespace App\Http\Controllers\Front\Tournament;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\Tournament\Traits\DrawAuthorizationTrait;
use App\Models\Group;
use App\Models\MatchModel;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Services\Tournament\TournamentDrawService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TournamentDrawController extends Controller
{
    use DrawAuthorizationTrait;

    public function __construct(
        private TournamentDrawService $drawService
    ) {
        $this->middleware(['auth']);
    }

    public function index(Tournament $tournament)
    {
        $this->authorizeOwner($tournament);

        $categories = $tournament->categories()->get()->map(
            fn($cat) => $this->buildCategorySummary($cat, $tournament)
        );

        return view('home-yard.tournaments.draw', compact('tournament', 'categories'));
    }

    public function draw(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $validated = $request->validate([
            'category_id' => 'required|integer',
            'method'      => 'required|in:random,seeded',
        ]);

        $categoryId = (int) $validated['category_id'];
        $method     = $validated['method'];

        if (!$this->resolveCategory($tournament, $categoryId)) {
            return $this->invalidCategoryResponse();
        }

        $groups = $this->loadGroups($tournament, $categoryId);

        if ($groups->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa cấu hình bảng đấu. Hãy thiết lập bảng trước khi bốc thăm.',
            ], 422);
        }

        try {
            // Reset truoc khi draw lai: xoa standings cu + clear group_id,
            // tranh updateOrCreate giu lai standing stale neu athlete bi doi group.
            $this->drawService->resetDraw($tournament, $categoryId);

            $isDouble = $this->drawService->isDoubleCategory($categoryId, $tournament);

            if ($isDouble) {
                $athletes = $this->loadApprovedAthletes($tournament, $categoryId, true);
                $pairs    = $this->drawService->getPairsFromAthletes($athletes);

                if (empty($pairs)) {
                    return response()->json(['success' => false, 'message' => 'Không có cặp đôi hợp lệ.'], 422);
                }

                $method === 'random'
                    ? $this->drawService->drawPairsByRandom($pairs, $groups)
                    : $this->drawService->drawPairsBySeeding($pairs, $groups);
            } else {
                $athletes = $this->loadApprovedAthletes($tournament, $categoryId);

                if ($athletes->isEmpty()) {
                    return response()->json(['success' => false, 'message' => 'Chưa có vận động viên được duyệt.'], 422);
                }

                $method === 'random'
                    ? $this->drawService->drawAthletesByRandom($athletes, $groups)
                    : $this->drawService->drawAthletesBySeeding($athletes, $groups);
            }

            if ($tournament->tournament_stage === 'registration') {
                $tournament->update(['tournament_stage' => 'draw_completed']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bốc thăm thành công.',
                'groups'  => $this->drawService->getGroupedAthletes($groups),
            ]);
        } catch (\Exception $e) {
            Log::error('Bốc thăm thất bại: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Lỗi khi bốc thăm. Vui lòng thử lại.'], 500);
        }
    }

    public function getResults(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $categoryId = (int) $request->query('category_id');

        if (!$this->resolveCategory($tournament, $categoryId)) {
            return $this->invalidCategoryResponse();
        }

        $groups  = $this->loadGroups($tournament, $categoryId);
        $isDrawn = TournamentAthlete::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->whereNotNull('group_id')
            ->exists();

        return response()->json([
            'success'    => true,
            'groups'     => $this->drawService->getGroupedAthletes($groups),
            'is_drawn'   => $isDrawn,
            'is_doubles' => $this->drawService->isDoubleCategory($categoryId, $tournament),
        ]);
    }

    public function reset(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $categoryId = (int) $request->input('category_id');

        if (!$this->resolveCategory($tournament, $categoryId)) {
            return $this->invalidCategoryResponse();
        }

        $matchCount = MatchModel::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->count();

        if ($matchCount > 0 && !$request->boolean('force')) {
            return response()->json([
                'success'        => false,
                'requires_force' => true,
                'match_count'    => $matchCount,
                'message'        => "Có {$matchCount} trận đấu sẽ bị xóa. Xác nhận reset?",
            ]);
        }

        try {
            DB::transaction(function () use ($tournament, $categoryId, $matchCount) {
                if ($matchCount > 0) {
                    MatchModel::where('tournament_id', $tournament->id)
                        ->where('category_id', $categoryId)
                        ->delete();
                }
                $this->drawService->resetDraw($tournament, $categoryId);
            });

            // Revert stage if no other categories have draws
            $hasOtherDraws = $tournament->athletes()
                ->where('category_id', '!=', $categoryId)
                ->whereNotNull('group_id')
                ->exists();
            if (!$hasOtherDraws && $tournament->tournament_stage === 'draw_completed') {
                $tournament->update(['tournament_stage' => 'registration']);
            }

            return response()->json(['success' => true, 'message' => 'Đã reset kết quả bốc thăm.']);
        } catch (\Exception $e) {
            Log::error('Reset bốc thăm thất bại: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Lỗi khi reset. Vui lòng thử lại.'], 500);
        }
    }

}
