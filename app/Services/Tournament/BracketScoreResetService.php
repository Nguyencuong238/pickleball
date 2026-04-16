<?php

namespace App\Services\Tournament;

use App\Models\MatchModel;
use App\Models\Round;
use Illuminate\Support\Facades\DB;

class BracketScoreResetService
{
    public function __construct(
        private KnockoutBracketQuery $bracketQuery
    ) {}

    /**
     * Xóa tỉ số và reset trận đấu về trạng thái scheduled.
     * Cascade clear các trận vòng sau (bao gồm score).
     *
     * @return array{success: bool, message: string}
     */
    public function resetMatchScore(MatchModel $match): array
    {
        return DB::transaction(function () use ($match) {
            $match = MatchModel::lockForUpdate()->findOrFail($match->id);

            // Capture loser_id TRUOC khi clear winner (cho trận tranh hạng ba)
            $loserId = $match->loser_id;

            // Thu thập downstream round IDs trước khi cascade
            $affectedRoundIds = $this->collectDownstreamRoundIds($match);

            // Xóa score fields của trận hiện tại
            $match->set_scores      = null;
            $match->final_score     = null;
            $match->winner_id       = null;
            $match->athlete1_score  = 0;
            $match->athlete2_score  = 0;
            $match->actual_end_time = null;
            $match->status          = 'scheduled';
            $match->save();

            // Cascade clear downstream (xóa VĐV + score đã advance)
            $this->bracketQuery->cascadeClearDownstream($match);

            // Cập nhật round completion count
            $allRoundIds = array_unique(array_merge(
                [$match->round_id],
                $affectedRoundIds
            ));
            $this->refreshRoundStatuses($allRoundIds);

            // Clear third-place routing nếu là semifinal
            $this->clearThirdPlaceRouting($match, $loserId);

            return ['success' => true, 'message' => 'Đã xóa tỉ số thành công'];
        });
    }

    /**
     * @return array<int>
     */
    private function collectDownstreamRoundIds(MatchModel $match): array
    {
        $roundIds = [];
        $current = $match;
        while ($current->next_match_id) {
            $next = MatchModel::find($current->next_match_id);
            if (!$next) break;
            $roundIds[] = $next->round_id;
            $current = $next;
        }
        return $roundIds;
    }

    /**
     * @param array<int> $roundIds
     */
    private function refreshRoundStatuses(array $roundIds): void
    {
        $rounds = Round::whereIn('id', $roundIds)->get();
        foreach ($rounds as $round) {
            $completed = $round->matches()
                ->whereIn('status', ['completed', 'bye'])
                ->count();
            $status = $completed >= $round->total_matches ? 'completed'
                : ($completed > 0 ? 'in_progress' : 'pending');
            $round->update([
                'completed_matches' => $completed,
                'status' => $status,
            ]);
        }
    }

    /**
     * Xóa VĐV thua cụ thể từ trận tranh hạng ba (nếu là trận bán kết).
     */
    private function clearThirdPlaceRouting(MatchModel $match, ?int $loserId): void
    {
        if (!$loserId) {
            return;
        }

        $round = $match->round;
        if (!$round || $round->round_type !== 'semifinal') {
            return;
        }

        $bronzeRound = Round::where('tournament_id', $match->tournament_id)
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

        // Chỉ xóa VĐV cụ thể của trận bán kết này
        if ((int) $bronzeMatch->athlete1_id === $loserId) {
            $bronzeMatch->update(['athlete1_id' => null, 'athlete1_name' => null]);
        } elseif ((int) $bronzeMatch->athlete2_id === $loserId) {
            $bronzeMatch->update(['athlete2_id' => null, 'athlete2_name' => null]);
        } else {
            return;
        }

        // Reset score bronze match nếu đã có score nhưng mất VĐV
        $bronzeMatch->refresh();
        if ($bronzeMatch->set_scores && (!$bronzeMatch->athlete1_id || !$bronzeMatch->athlete2_id)) {
            $bronzeMatch->update([
                'set_scores'      => null,
                'final_score'     => null,
                'winner_id'       => null,
                'athlete1_score'  => 0,
                'athlete2_score'  => 0,
                'actual_end_time' => null,
                'status'          => 'scheduled',
            ]);
        }

        $this->refreshRoundStatuses([$bronzeRound->id]);
    }
}
