<?php

namespace App\Services\Tournament;

use App\Models\MatchModel;
use App\Models\Round;
use App\Models\TournamentAthlete;
use InvalidArgumentException;

class KnockoutBracketQuery
{
    /**
     * Lấy toàn bộ dữ liệu bảng đấu để hiển thị giao diện.
     *
     * @return array<string, mixed>
     */
    public function getBracketData(int $tournamentId, int $categoryId): array
    {
        $roundTypes = ['knockout', 'quarterfinal', 'semifinal', 'final', 'bronze'];

        $rounds = Round::where('tournament_id', $tournamentId)
            ->where('category_id', $categoryId)
            ->whereIn('round_type', $roundTypes)
            ->orderBy('round_number')
            ->with(['matches' => function ($query) {
                $query->orderBy('bracket_position')
                    ->with(['athlete1.partner', 'athlete2.partner']);
            }])
            ->get();

        return $rounds->map(function (Round $round): array {
            return [
                'id'      => $round->id,
                'name'    => $round->round_name,
                'type'    => $round->round_type,
                'number'  => $round->round_number,
                'status'  => $round->status,
                'matches' => $round->matches->map(
                    fn (MatchModel $match): array => $this->formatMatch($match)
                )->values()->toArray(),
            ];
        })->values()->toArray();
    }

    /**
     * Hoán đổi vị trí VĐV giữa hai trận (chỉ khi cả hai ở trạng thái scheduled).
     */
    public function swapAthletes(int $matchId1, string $slot1, int $matchId2, string $slot2): void
    {
        $match1 = MatchModel::findOrFail($matchId1);
        $match2 = MatchModel::findOrFail($matchId2);

        $canSwap = function (MatchModel $m): bool {
            if ($m->status === 'scheduled') return true;
            return $m->status === 'completed'
                && ($m->athlete1_id === null || $m->athlete2_id === null);
        };

        if (!$canSwap($match1) || !$canSwap($match2)) {
            throw new InvalidArgumentException('Chỉ có thể hoán đổi VĐV trong các trận chưa diễn ra hoặc trận bye.');
        }

        $allowed = ['athlete1_id', 'athlete2_id'];
        $col1    = $slot1 . '_id';
        $col2    = $slot2 . '_id';

        if (!in_array($col1, $allowed) || !in_array($col2, $allowed)) {
            throw new InvalidArgumentException('Slot không hợp lệ. Chỉ chấp nhận athlete1 hoặc athlete2.');
        }

        $val1 = $match1->{$col1};
        $val2 = $match2->{$col2};

        $nameCol1 = $slot1 . '_name';
        $nameCol2 = $slot2 . '_name';
        $name1 = $match1->{$nameCol1};
        $name2 = $match2->{$nameCol2};

        $match1->update([$col1 => $val2, $nameCol1 => $name2]);
        $match2->update([$col2 => $val1, $nameCol2 => $name1]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMatch(MatchModel $match): array
    {
        return [
            'id'               => $match->id,
            'match_number'     => $match->match_number,
            'bracket_position' => $match->bracket_position,
            'status'           => $match->status,
            'next_match_id'    => $match->next_match_id,
            'athlete1'         => $this->formatAthleteSlot($match->athlete1),
            'athlete2'         => $this->formatAthleteSlot($match->athlete2),
            'winner_id'        => $match->winner_id,
            'athlete1_score'   => $match->athlete1_score,
            'athlete2_score'   => $match->athlete2_score,
            'set_scores'       => $match->set_scores,
        ];
    }

    /**
     * Định dạng thông tin VĐV cho giao diện, hỗ trợ đôi (doubles).
     *
     * @return array<string, mixed>|null
     */
    private function formatAthleteSlot(?TournamentAthlete $athlete): ?array
    {
        if (!$athlete) {
            return null;
        }

        return [
            'id'           => $athlete->id,
            'name'         => $athlete->athlete_name,
            'partner_name' => $athlete->partner?->athlete_name,
            'pair_name'    => $athlete->pair_name,
            'seed'         => $athlete->seed_number,
        ];
    }
}
