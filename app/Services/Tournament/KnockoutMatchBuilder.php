<?php

namespace App\Services\Tournament;

use App\Models\MatchModel;
use App\Models\Round;
use App\Models\Tournament;
use App\Models\TournamentAthlete;

class KnockoutMatchBuilder
{
    /**
     * Tạo các trận đấu với bracket_position theo heap-style.
     * Tạo từ chung kết ngược về vòng 1 để next_match_id luôn tồn tại khi cần tham chiếu.
     *
     * @param array<int, int|null> $slots
     * @param array<int, Round> $rounds
     */
    public function createMatches(
        Tournament $tournament,
        int $categoryId,
        array $slots,
        array $rounds,
        int $totalRounds
    ): void {
        // Bản đồ bracket_position => MatchModel ID
        $positionToMatchId = [];
        $totalPositions = (int) \pow(2, $totalRounds + 1) - 1;

        for ($position = 1; $position <= $totalPositions; $position++) {
            $depth = (int) \floor(\log($position, 2));

            if ($depth >= $totalRounds) {
                continue;
            }

            $roundFromFinal = $depth;
            $round          = $rounds[$roundFromFinal] ?? null;

            if (!$round) {
                continue;
            }

            $parentPosition = (int) floor($position / 2);
            $nextMatchId    = $positionToMatchId[$parentPosition] ?? null;

            [$athlete1Id, $athlete2Id] = $this->resolveAthleteSlots(
                $position, $depth, $totalRounds, $slots
            );

            // Resolve athlete names (pair_name for doubles)
            $athlete1Name = $athlete1Id ? (TournamentAthlete::find($athlete1Id)?->pair_name) : null;
            $athlete2Name = $athlete2Id ? (TournamentAthlete::find($athlete2Id)?->pair_name) : null;

            $match = MatchModel::create([
                'tournament_id'    => $tournament->id,
                'category_id'      => $categoryId,
                'round_id'         => $round->id,
                'match_number'     => $position,
                'bracket_position' => $position,
                'match_date'       => $tournament->start_date,
                'athlete1_id'      => $athlete1Id,
                'athlete1_name'    => $athlete1Name,
                'athlete2_id'      => $athlete2Id,
                'athlete2_name'    => $athlete2Name,
                'status'           => 'scheduled',
                'next_match_id'    => $nextMatchId,
            ]);

            $positionToMatchId[$position] = $match->id;

            // Xử lý bye ngay khi tạo trận vòng đầu
            $isFirstRound = ($depth === $totalRounds - 1);
            if ($isFirstRound && ($athlete1Id === null || $athlete2Id === null)) {
                $this->handleBye($match);
            }
        }
    }

    /**
     * Xử lý trận bye: tự động hoàn thành và đưa VĐV có mặt lên vòng tiếp theo.
     */
    public function handleBye(MatchModel $match): void
    {
        $winnerId = $match->athlete1_id ?? $match->athlete2_id;

        if ($winnerId === null) {
            return;
        }

        $match->update([
            'status'    => 'completed',
            'winner_id' => $winnerId,
        ]);

        $this->advanceWinner($match, $winnerId);
    }

    /**
     * Đưa người thắng lên trận tiếp theo trong bảng đấu.
     */
    public function advanceWinner(MatchModel $match, int $winnerId): void
    {
        if (!$match->next_match_id) {
            return;
        }

        $nextMatch = MatchModel::find($match->next_match_id);
        if (!$nextMatch || $nextMatch->status !== 'scheduled') {
            return;
        }

        $athlete = TournamentAthlete::find($winnerId);
        $name    = $athlete?->pair_name ?? 'Chưa xác định';

        if ($nextMatch->athlete1_id === null) {
            $nextMatch->update(['athlete1_id' => $winnerId, 'athlete1_name' => $name]);
        } elseif ($nextMatch->athlete2_id === null) {
            $nextMatch->update(['athlete2_id' => $winnerId, 'athlete2_name' => $name]);
        }
    }

    /**
     * Xác định VĐV cho trận đấu dựa vào vị trí trong cây.
     * Chỉ vòng đầu tiên (depth = totalRounds - 1) mới phân bổ VĐV thực.
     *
     * @param array<int, int|null> $slots
     * @return array{0: int|null, 1: int|null}
     */
    private function resolveAthleteSlots(
        int $position,
        int $depth,
        int $totalRounds,
        array $slots
    ): array {
        if ($depth !== $totalRounds - 1) {
            return [null, null];
        }

        $slotIndex = $position - (int) \pow(2, $depth);

        return [
            $slots[$slotIndex * 2] ?? null,
            $slots[$slotIndex * 2 + 1] ?? null,
        ];
    }
}
