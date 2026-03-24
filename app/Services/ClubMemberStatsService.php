<?php

namespace App\Services;

use App\Models\ClubActivityMatch;
use App\Models\ClubMemberStat;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClubMemberStatsService
{
    /**
     * Cap nhat thong ke thanh vien sau moi tran dau
     */
    public function updateAfterMatch(ClubActivityMatch $match, string $winner): void
    {
        DB::transaction(function () use ($match, $winner) {
            $this->processMatchStats($match, $winner);
        });
    }

    private function processMatchStats(ClubActivityMatch $match, string $winner): void
    {
        $clubId = $match->activity->club_id;

        $winnerIds = $winner === 'team1'
            ? [$match->player1_id, $match->player2_id]
            : [$match->player3_id, $match->player4_id];

        $allIds = array_filter([
            $match->player1_id,
            $match->player2_id,
            $match->player3_id,
            $match->player4_id,
        ]);

        $totalT1 = collect($match->set_scores)->sum('team1');
        $totalT2 = collect($match->set_scores)->sum('team2');

        foreach ($allIds as $userId) {
            $isWinner = in_array($userId, $winnerIds);
            $isTeam1 = in_array($userId, [$match->player1_id, $match->player2_id]);

            $stat = ClubMemberStat::firstOrCreate(
                ['club_id' => $clubId, 'user_id' => $userId],
                []
            );

            $stat->increment('total_matches');
            $stat->increment($isWinner ? 'total_wins' : 'total_losses');
            $stat->increment('total_points_scored', $isTeam1 ? $totalT1 : $totalT2);
            $stat->increment('total_points_against', $isTeam1 ? $totalT2 : $totalT1);

            $user = User::find($userId);
            $stat->update([
                'last_played_at' => now(),
                'current_oprs' => $user->total_oprs ?? 0,
            ]);
        }
    }
}
