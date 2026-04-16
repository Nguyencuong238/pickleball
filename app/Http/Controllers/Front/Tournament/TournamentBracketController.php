<?php

namespace App\Http\Controllers\Front\Tournament;

use App\Http\Controllers\Controller;
use App\Models\MatchModel;
use App\Models\Tournament;
use App\Services\Tournament\BracketScoreResetService;
use App\Services\Tournament\KnockoutBracketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class TournamentBracketController extends Controller
{
    public function __construct(
        private KnockoutBracketService $bracketService,
        private BracketScoreResetService $resetService
    ) {
        $this->middleware(['auth']);
    }

    public function index(Tournament $tournament): mixed
    {
        $this->authorizeOwner($tournament);

        $tournament->load('categories');

        return view('home-yard.tournaments.bracket', compact('tournament'));
    }

    public function getData(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $validated = $request->validate([
            'category_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('tournament_categories', 'id')->where('tournament_id', $tournament->id)],
        ]);

        try {
            $data = $this->bracketService->getBracketData($tournament->id, (int) $validated['category_id']);

            return response()->json(['success' => true, 'rounds' => $data]);
        } catch (\Exception $e) {
            Log::error('Lấy dữ liệu bracket thất bại: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Lấy dữ liệu bracket thất bại'], 500);
        }
    }

    public function generate(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $validated = $request->validate([
            'category_id'        => ['required', 'integer', \Illuminate\Validation\Rule::exists('tournament_categories', 'id')->where('tournament_id', $tournament->id)],
            'enable_third_place' => 'nullable|boolean',
        ]);

        try {
            $this->bracketService->generateBracket(
                $tournament,
                (int) $validated['category_id'],
                (bool) ($validated['enable_third_place'] ?? false)
            );

            return response()->json(['success' => true, 'message' => 'Đã tạo bracket thành công']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Tạo bracket thất bại: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Tạo bracket thất bại'], 500);
        }
    }

    public function swap(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $matchInTournament = \Illuminate\Validation\Rule::exists('matches', 'id')->where('tournament_id', $tournament->id);
        $validated = $request->validate([
            'match_id_1' => ['required', 'integer', $matchInTournament],
            'slot_1'     => 'required|string|in:athlete1,athlete2',
            'match_id_2' => ['required', 'integer', $matchInTournament],
            'slot_2'     => 'required|string|in:athlete1,athlete2',
        ]);

        try {
            $this->bracketService->swapAthletes(
                (int) $validated['match_id_1'],
                $validated['slot_1'],
                (int) $validated['match_id_2'],
                $validated['slot_2']
            );

            return response()->json(['success' => true, 'message' => 'Đổi vị trí thành công']);
        } catch (\Exception $e) {
            Log::error('Đổi vị trí VĐV thất bại: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Đổi vị trí thất bại'], 500);
        }
    }

    public function getEligibleAthletes(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $validated = $request->validate([
            'match_id' => ['required', 'integer', Rule::exists('matches', 'id')->where('tournament_id', $tournament->id)],
        ]);

        try {
            $match = MatchModel::findOrFail($validated['match_id']);
            $athletes = $this->bracketService->getEligibleAthletes($match);

            return response()->json(['success' => true, 'athletes' => $athletes]);
        } catch (\Exception $e) {
            Log::error('Lấy danh sách VĐV hợp lệ thất bại: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Lấy danh sách VĐV thất bại'], 500);
        }
    }

    public function updateMatch(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $validated = $request->validate([
            'match_id'         => ['required', 'integer', Rule::exists('matches', 'id')->where('tournament_id', $tournament->id)],
            'athlete1_id'      => ['nullable', 'integer', Rule::exists('tournament_athletes', 'id')->where('tournament_id', $tournament->id)],
            'athlete2_id'      => ['nullable', 'integer', Rule::exists('tournament_athletes', 'id')->where('tournament_id', $tournament->id)],
            'match_time'       => 'nullable|date_format:H:i',
            'match_date'       => 'nullable|date',
            'confirm_cascade'  => 'nullable|boolean',
            'best_of'          => 'nullable|integer|in:1,3,5',
            'notes'            => 'nullable|string|max:500',
        ]);

        if (!empty($validated['athlete1_id']) && !empty($validated['athlete2_id'])
            && (int) $validated['athlete1_id'] === (int) $validated['athlete2_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể chọn cùng một VĐV cho cả hai vị trí.',
            ], 422);
        }

        try {
            $match = MatchModel::findOrFail($validated['match_id']);

            $isCompleted = $match->status === 'completed'
                && $match->athlete1_id && $match->athlete2_id
                && $match->set_scores;

            if ($isCompleted) {
                $athleteChanging = (array_key_exists('athlete1_id', $validated) && (int) ($validated['athlete1_id'] ?? 0) !== (int) $match->athlete1_id)
                    || (array_key_exists('athlete2_id', $validated) && (int) ($validated['athlete2_id'] ?? 0) !== (int) $match->athlete2_id);

                if ($athleteChanging) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể thay đổi VĐV trên trận đấu đã hoàn thành có tỉ số. Xóa tỉ số trước.',
                    ], 422);
                }
            }

            $result = $this->bracketService->updateMatch($match, $validated);

            $statusCode = ($result['success'] ?? false) ? 200 : 422;
            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            Log::error('Cập nhật trận đấu thất bại: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Cập nhật trận đấu thất bại'], 500);
        }
    }

    public function resetScore(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $validated = $request->validate([
            'match_id' => ['required', 'integer', Rule::exists('matches', 'id')->where('tournament_id', $tournament->id)],
        ]);

        try {
            $match = MatchModel::findOrFail($validated['match_id']);

            if ($match->status === 'in_progress') {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa tỉ số trận đang diễn ra.',
                ], 422);
            }

            if ($match->status === 'bye') {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa tỉ số trận bye.',
                ], 422);
            }

            if (!$match->set_scores && !$match->winner_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trận đấu chưa có tỉ số để xóa.',
                ], 422);
            }

            $result = $this->resetService->resetMatchScore($match);
            $statusCode = ($result['success'] ?? false) ? 200 : 422;
            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            Log::error('Xóa tỉ số thất bại: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Xóa tỉ số thất bại'], 500);
        }
    }

    private function authorizeOwner(Tournament $tournament): void
    {
        abort_if($tournament->user_id !== auth()->id(), 403);
    }
}
