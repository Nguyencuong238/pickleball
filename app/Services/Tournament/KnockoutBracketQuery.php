<?php

namespace App\Services\Tournament;

use App\Models\MatchModel;
use App\Models\Round;
use App\Models\TournamentAthlete;
use Carbon\Carbon;
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
     * Lấy danh sách VDV hợp lệ cho một trận đấu bracket.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getEligibleAthletes(MatchModel $match): array
    {
        $round = $match->round;
        $categoryId = $match->category_id;
        $tournamentId = $match->tournament_id;

        $bracketRoundTypes = ['knockout', 'quarterfinal', 'semifinal', 'final'];

        $firstBracketRound = Round::where('tournament_id', $tournamentId)
            ->where('category_id', $categoryId)
            ->whereIn('round_type', $bracketRoundTypes)
            ->orderBy('round_number')
            ->first();

        $currentRoundNumber = $round->round_number;

        if ($firstBracketRound && $currentRoundNumber === $firstBracketRound->round_number) {
            // Vong bracket dau tien: cho chon tat ca VDV trong noi dung (ve vot, hang 3 tot nhat, v.v.)
            $eligible = TournamentAthlete::where('tournament_id', $tournamentId)
                ->where('category_id', $categoryId)
                ->with('partner')
                ->get();
        } else {
            // Vong sau: chi VDV da thang o vong bracket truoc
            $previousRounds = Round::where('tournament_id', $tournamentId)
                ->where('category_id', $categoryId)
                ->whereIn('round_type', $bracketRoundTypes)
                ->where('round_number', '<', $currentRoundNumber)
                ->pluck('id');

            $winnerIds = MatchModel::whereIn('round_id', $previousRounds)
                ->whereNotNull('winner_id')
                ->pluck('winner_id')
                ->unique();

            $eligible = TournamentAthlete::where('tournament_id', $tournamentId)
                ->where('category_id', $categoryId)
                ->whereIn('id', $winnerIds)
                ->with('partner')
                ->get();
        }

        $usedInRound = MatchModel::where('round_id', $round->id)
            ->where('id', '!=', $match->id)
            ->get()
            ->flatMap(fn ($m) => [$m->athlete1_id, $m->athlete2_id])
            ->filter()
            ->unique();

        return $eligible->whereNotIn('id', $usedInRound)
            ->filter(fn ($a) => is_null($a->partner_id) || $a->id < $a->partner_id)
            ->map(fn ($a) => [
                'id'           => $a->id,
                'name'         => $a->athlete_name,
                'partner_name' => $a->partner?->athlete_name,
                'pair_name'    => $a->pair_name,
                'seed'         => $a->seed_number,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Cap nhat VDV va thuoc tinh tran dau bracket.
     */
    public function updateMatchAthletes(MatchModel $match, array $data): void
    {
        $updates = [];

        if (array_key_exists('athlete1_id', $data)) {
            $updates['athlete1_id'] = $data['athlete1_id'];
            $updates['athlete1_name'] = $data['athlete1_id']
                ? TournamentAthlete::find($data['athlete1_id'])?->pair_name
                : null;
        }
        if (array_key_exists('athlete2_id', $data)) {
            $updates['athlete2_id'] = $data['athlete2_id'];
            $updates['athlete2_name'] = $data['athlete2_id']
                ? TournamentAthlete::find($data['athlete2_id'])?->pair_name
                : null;
        }

        foreach (['match_time', 'match_date', 'best_of', 'notes'] as $field) {
            if (array_key_exists($field, $data)) {
                $updates[$field] = $data[$field];
            }
        }

        if (!empty($updates)) {
            $match->update($updates);
        }
    }

    /**
     * Dem so tran dau o cac vong sau bi anh huong khi thay doi VDV.
     */
    public function countCascadeAffected(MatchModel $match): int
    {
        $affected = 0;
        $currentMatch = $match;

        while ($currentMatch->next_match_id) {
            $nextMatch = MatchModel::find($currentMatch->next_match_id);
            if (!$nextMatch) break;

            if ($nextMatch->athlete1_id || $nextMatch->athlete2_id) {
                $affected++;
            }
            $currentMatch = $nextMatch;
        }

        return $affected;
    }

    /**
     * Xoa VDV da duoc advance o cac tran vong sau (cascade clear).
     */
    public function cascadeClearDownstream(MatchModel $match): void
    {
        $currentMatch = $match;

        while ($currentMatch->next_match_id) {
            $nextMatch = MatchModel::find($currentMatch->next_match_id);
            if (!$nextMatch) break;

            $nextMatch->update([
                'athlete1_id'     => null,
                'athlete1_name'   => null,
                'athlete2_id'     => null,
                'athlete2_name'   => null,
                'winner_id'       => null,
                'set_scores'      => null,
                'final_score'     => null,
                'athlete1_score'  => 0,
                'athlete2_score'  => 0,
                'actual_end_time' => null,
                'status'          => 'scheduled',
            ]);

            $currentMatch = $nextMatch;
        }
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
            'match_time'       => $match->match_time ? Carbon::parse($match->match_time)->format('H:i') : null,
            'match_date'       => $match->match_date ? Carbon::parse($match->match_date)->format('Y-m-d') : null,
            'best_of'          => $match->best_of,
            'notes'            => $match->notes,
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
