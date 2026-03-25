<?php

namespace App\Services;

use App\Models\ClubActivityMatch;
use Illuminate\Support\Facades\DB;

class ClubScoreService
{
    public function adminSubmitScore(ClubActivityMatch $match, array $setScores, int $userId): void
    {
        $winner = $this->determineWinner($setScores);
        $totals = $this->calculateTotals($setScores);

        DB::transaction(function () use ($match, $setScores, $winner, $userId, $totals) {
            $match = ClubActivityMatch::lockForUpdate()->find($match->id);
            if ($match->result_confirmed || $match->status === 'completed') return;

            $match->update([
                'set_scores' => $setScores,
                'result_submitted_by' => $userId,
                'result_confirmed' => true,
                'score_status' => 'admin_confirmed',
                'ended_at' => $match->ended_at ?? now(),
                'status' => 'completed',
                'team1_score' => $totals['team1'],
                'team2_score' => $totals['team2'],
            ]);

            $this->processEloAndComplete($match, $winner);
        });
    }

    public function playerSubmitScore(ClubActivityMatch $match, array $setScores, int $userId): void
    {
        $totals = $this->calculateTotals($setScores);

        DB::transaction(function () use ($match, $setScores, $userId, $totals) {
            $match = ClubActivityMatch::lockForUpdate()->find($match->id);
            if ($match->score_status === 'pending_confirmation' || $match->result_confirmed) return;

            $match->update([
                'set_scores' => $setScores,
                'result_submitted_by' => $userId,
                'result_confirmed' => false,
                'score_status' => 'pending_confirmation',
                'ended_at' => $match->ended_at ?? now(),
                'status' => 'pending_score',
                'team1_score' => $totals['team1'],
                'team2_score' => $totals['team2'],
            ]);
        });
    }

    public function confirmScore(ClubActivityMatch $match, int $userId, bool $isAdmin): void
    {
        DB::transaction(function () use ($match, $userId, $isAdmin) {
            $match = ClubActivityMatch::lockForUpdate()->find($match->id);
            if ($match->score_status !== 'pending_confirmation') return;

            $match->update([
                'score_status' => $isAdmin ? 'admin_confirmed' : 'confirmed',
                'score_confirmed_by' => $userId,
                'result_confirmed' => true,
                'status' => 'completed',
            ]);

            $winner = $this->determineWinner($match->set_scores);
            $this->processEloAndComplete($match, $winner);
        });
    }

    public function rejectScore(ClubActivityMatch $match): void
    {
        DB::transaction(function () use ($match) {
            $match = ClubActivityMatch::lockForUpdate()->find($match->id);
            if ($match->score_status !== 'pending_confirmation') return;

            $match->update([
                'score_status' => 'rejected',
                'result_confirmed' => false,
                'set_scores' => null,
                'team1_score' => null,
                'team2_score' => null,
                'status' => 'in_progress',
            ]);
        });
    }

    public function determineWinner(array $setScores): string
    {
        $t1Wins = collect($setScores)->filter(fn($s) => $s['team1'] > $s['team2'])->count();
        $t2Wins = collect($setScores)->filter(fn($s) => $s['team2'] > $s['team1'])->count();
        return $t1Wins > $t2Wins ? 'team1' : 'team2';
    }

    private function calculateTotals(array $setScores): array
    {
        return [
            'team1' => collect($setScores)->sum('team1'),
            'team2' => collect($setScores)->sum('team2'),
        ];
    }

    private function processEloAndComplete(ClubActivityMatch $match, string $winner): void
    {
        if ($match->activity->oprs_weight > 0 && !$match->oprs_processed) {
            app(EloService::class)->processClubMatchElo($match, $winner);
            app(OprsService::class)->recalculateAfterClubMatch($match);
            $match->update(['oprs_processed' => true]);
        }

        app(ClubMatchmakingService::class)->completeMatch($match);
        app(ClubMemberStatsService::class)->updateAfterMatch($match, $winner);
    }
}
