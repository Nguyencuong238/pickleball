<?php

namespace App\Http\Controllers\Front\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Services\Tournament\KnockoutBracketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class TournamentBracketController extends Controller
{
    public function __construct(
        private KnockoutBracketService $bracketService
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

    private function authorizeOwner(Tournament $tournament): void
    {
        abort_if($tournament->user_id !== auth()->id(), 403);
    }
}
