<?php

namespace App\Http\Controllers\Front\Tournament\Traits;

use App\Models\MatchModel;
use App\Services\Tournament\TournamentStandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait MatchScoreTrait
{
    /**
     * Xử lý cập nhật tỉ số: validate, lưu DB, cập nhật standings.
     */
    protected function processScoreUpdate(
        Request $request,
        MatchModel $match,
        TournamentStandingService $standingService
    ): JsonResponse {
        $bestOf = $match->best_of ?? 3;

        $validated = $request->validate([
            'sets'      => 'required|array|min:1|max:' . $bestOf,
            'sets.*.s1' => 'required|integer|min:0|max:30',
            'sets.*.s2' => 'required|integer|min:0|max:30',
            'status'    => 'nullable|in:in_progress,completed,scheduled',
        ]);

        $setsWon1 = 0;
        $setsWon2 = 0;

        try {
            DB::transaction(function () use ($match, $validated, &$setsWon1, &$setsWon2) {
                $sets = $validated['sets'];
                $setScoresArr = [];

                foreach ($sets as $set) {
                    $s1 = (int) $set['s1'];
                    $s2 = (int) $set['s2'];
                    if ($s1 > $s2) {
                        $setsWon1++;
                    } elseif ($s2 > $s1) {
                        $setsWon2++;
                    }
                    $setScoresArr[] = ['athlete1_score' => $s1, 'athlete2_score' => $s2];
                }

                $finalScore = implode(',', array_map(
                    fn($s) => $s['athlete1_score'] . '-' . $s['athlete2_score'],
                    $setScoresArr
                ));

                $status   = $validated['status'] ?? 'completed';
                $winnerId = null;

                if ($status === 'completed') {
                    if ($setsWon1 > $setsWon2) {
                        $winnerId = $match->athlete1_id;
                    } elseif ($setsWon2 > $setsWon1) {
                        $winnerId = $match->athlete2_id;
                    }
                }

                $match->set_scores     = $setScoresArr;
                $match->final_score    = $finalScore;
                $match->status         = $status;
                $match->winner_id      = $winnerId;
                $match->athlete1_score = $setsWon1;
                $match->athlete2_score = $setsWon2;

                if ($status === 'completed' && !$match->actual_end_time) {
                    $match->actual_end_time = now();
                }

                $match->save();
            });

            if ($match->group_id && $match->status === 'completed') {
                try {
                    $standingService->updateGroupStandingsWithSets($match, $setsWon1, $setsWon2);
                    $standingService->updateTournamentAthleteStats($match, $setsWon1, $setsWon2);
                } catch (\Exception $e) {
                    Log::warning('Cập nhật bảng xếp hạng thất bại: ' . $e->getMessage());
                }
            }

            $match->refresh();

            // Tự động chuyển người thắng vào vòng tiếp theo (knockout bracket)
            if ($match->bracket_position && $match->status === 'completed' && $match->winner_id) {
                if (method_exists($this, 'handleBracketAdvancement')) {
                    $this->handleBracketAdvancement($match);
                }
            }

            // Tự động tạo bracket khi vòng bảng hoàn thành
            if ($match->group_id && $match->status === 'completed') {
                try {
                    $bracketService = app(\App\Services\Tournament\KnockoutBracketService::class);
                    $categoryId = $match->category_id;
                    $tournamentId = $match->tournament_id;

                    if ($bracketService->checkCategoryCompletion($tournamentId, $categoryId)
                        && !$bracketService->isBracketGenerated($tournamentId, $categoryId)) {

                        $tournament = $match->tournament;

                        // Đánh dấu nội dung sẵn sàng tạo bracket
                        $bracketData = $tournament->bracket_data ?? [];
                        $bracketData["category_{$categoryId}_ready"] = true;
                        $tournament->update(['bracket_data' => $bracketData]);

                        // Tự động tạo bracket nếu cài đặt cho phép
                        if ($tournament->auto_bracket_generation) {
                            $bracketService->generateBracket(
                                $tournament,
                                $categoryId,
                                (bool) $tournament->enable_third_place
                            );
                            $bracketData["category_{$categoryId}_generated"] = true;
                            $tournament->update([
                                'bracket_data' => $bracketData,
                                'tournament_stage' => 'finals',
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Kiểm tra tự động tạo bracket thất bại: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật tỉ số thành công',
                'match'   => [
                    'id'             => $match->id,
                    'status'         => $match->status,
                    'final_score'    => $match->final_score,
                    'set_scores'     => $match->set_scores ?? [],
                    'winner_id'      => $match->winner_id,
                    'athlete1_score' => $match->athlete1_score,
                    'athlete2_score' => $match->athlete2_score,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Cập nhật tỉ số thất bại: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Cập nhật tỉ số thất bại'], 500);
        }
    }
}
