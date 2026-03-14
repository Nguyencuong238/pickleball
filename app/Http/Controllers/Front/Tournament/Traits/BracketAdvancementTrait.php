<?php

namespace App\Http\Controllers\Front\Tournament\Traits;

use App\Models\MatchModel;
use App\Models\Round;
use App\Models\TournamentAthlete;
use App\Services\Tournament\KnockoutBracketService;

trait BracketAdvancementTrait
{
    /**
     * Xử lý thắng thua sau khi trận knockout kết thúc.
     */
    protected function handleBracketAdvancement(MatchModel $match): void
    {
        if (!$match->winner_id) {
            return;
        }

        // Chuyển người thắng vào trận tiếp theo (nếu có)
        if ($match->next_match_id) {
            $bracketService = app(KnockoutBracketService::class);
            $bracketService->advanceWinner($match, $match->winner_id);
        }

        // Luôn cập nhật trạng thái vòng đấu (kể cả trận chung kết)
        $this->updateRoundCompletionStatus($match);
        $this->handleThirdPlaceRouting($match);
    }

    /**
     * Cập nhật trạng thái hoàn thành của vòng đấu.
     */
    private function updateRoundCompletionStatus(MatchModel $match): void
    {
        $round = $match->round;
        if (!$round) {
            return;
        }

        $completed = $round->matches()
            ->whereIn('status', ['completed', 'bye'])
            ->count();

        $round->update(['completed_matches' => $completed]);

        if ($completed >= $round->total_matches) {
            $round->update(['status' => 'completed']);
        }
    }

    /**
     * Chuyển người thua bán kết vào trận tranh hạng ba.
     */
    protected function handleThirdPlaceRouting(MatchModel $match): void
    {
        $round = $match->round;
        if (!$round || $round->round_type !== 'semifinal') {
            return;
        }

        $tournament = $match->tournament;
        if (!$tournament) {
            return;
        }

        $loserId = $match->loser_id;
        if (!$loserId) {
            return;
        }

        $bronzeRound = Round::where('tournament_id', $tournament->id)
            ->where('category_id', $match->category_id)
            ->where('round_type', 'bronze')
            ->first();

        if (!$bronzeRound) {
            return;
        }

        $bronzeMatch = MatchModel::where('round_id', $bronzeRound->id)->first();
        if (!$bronzeMatch) {
            return;
        }

        $loserName = TournamentAthlete::find($loserId)?->pair_name ?? 'Chưa xác định';

        if (!$bronzeMatch->athlete1_id) {
            $bronzeMatch->update(['athlete1_id' => $loserId, 'athlete1_name' => $loserName]);
        } elseif (!$bronzeMatch->athlete2_id) {
            $bronzeMatch->update(['athlete2_id' => $loserId, 'athlete2_name' => $loserName]);
        }
    }
}
