<?php

namespace App\Services\Tournament;

use App\Models\MatchModel;
use App\Models\Round;
use App\Models\Tournament;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class KnockoutBracketService
{
    public function __construct(
        private BracketSeedingHelper $seedingHelper,
        private KnockoutMatchBuilder $matchBuilder,
        private KnockoutBracketQuery $bracketQuery
    ) {}

    /**
     * Tạo toàn bộ bảng đấu loại trực tiếp cho một hạng mục.
     */
    public function generateBracket(Tournament $tournament, int $categoryId, bool $enableThirdPlace = false): void
    {
        $seeded = $this->seedingHelper->collectSeededAthletes($tournament->id, $categoryId);

        if (count($seeded) < 2) {
            throw new InvalidArgumentException('Cần tối thiểu 2 vận động viên để tạo bảng đấu knockout.');
        }

        $bracketSize = $this->seedingHelper->calculateBracketSize(count($seeded));
        $slots       = $this->seedingHelper->arrangeSeedsIntoBracket($seeded, $bracketSize);
        $totalRounds = (int) log2($bracketSize);

        DB::transaction(function () use ($tournament, $categoryId, $slots, $totalRounds, $enableThirdPlace) {
            $this->clearExistingBracket($tournament->id, $categoryId);
            $rounds = $this->createRounds($tournament, $categoryId, $totalRounds);
            $this->matchBuilder->createMatches($tournament, $categoryId, $slots, $rounds, $totalRounds);

            if ($enableThirdPlace) {
                $this->createThirdPlaceMatch($tournament, $categoryId);
            }
        });
    }

    /**
     * Xoá tất cả các vòng và trận đấu knockout hiện có của hạng mục.
     */
    public function clearExistingBracket(int $tournamentId, int $categoryId): void
    {
        $roundTypes = ['knockout', 'quarterfinal', 'semifinal', 'final', 'bronze'];

        $roundIds = Round::where('tournament_id', $tournamentId)
            ->where('category_id', $categoryId)
            ->whereIn('round_type', $roundTypes)
            ->pluck('id');

        if ($roundIds->isNotEmpty()) {
            MatchModel::whereIn('round_id', $roundIds)->delete();
            Round::whereIn('id', $roundIds)->delete();
        }
    }

    /**
     * Tạo các Round record cho từng vòng đấu knockout.
     *
     * @return array<int, Round> key = roundFromFinal (0 = chung kết)
     */
    public function createRounds(Tournament $tournament, int $categoryId, int $totalRounds): array
    {
        $rounds = [];

        for ($roundFromFinal = $totalRounds - 1; $roundFromFinal >= 0; $roundFromFinal--) {
            $roundType   = $this->seedingHelper->getRoundType($roundFromFinal, $totalRounds);
            $roundName   = $this->seedingHelper->getRoundName($roundType, $roundFromFinal, $totalRounds);
            $matchCount  = (int) pow(2, $roundFromFinal);
            $roundNumber = $totalRounds - $roundFromFinal;

            $rounds[$roundFromFinal] = Round::create([
                'tournament_id'     => $tournament->id,
                'category_id'       => $categoryId,
                'round_name'        => $roundName,
                'round_number'      => $roundNumber,
                'round_type'        => $roundType,
                'status'            => 'pending',
                'total_matches'     => $matchCount,
                'completed_matches' => 0,
            ]);
        }

        return $rounds;
    }

    /**
     * Tạo vòng và trận tranh hạng ba.
     */
    public function createThirdPlaceMatch(Tournament $tournament, int $categoryId): void
    {
        $bronzeRound = Round::create([
            'tournament_id'     => $tournament->id,
            'category_id'       => $categoryId,
            'round_name'        => 'Tranh hạng ba',
            'round_number'      => 99,
            'round_type'        => 'bronze',
            'status'            => 'pending',
            'total_matches'     => 1,
            'completed_matches' => 0,
        ]);

        MatchModel::create([
            'tournament_id'    => $tournament->id,
            'category_id'      => $categoryId,
            'round_id'         => $bronzeRound->id,
            'match_number'     => 0,
            'bracket_position' => 0,
            'athlete1_id'      => null,
            'athlete2_id'      => null,
            'status'           => 'scheduled',
            'next_match_id'    => null,
        ]);
    }

    /**
     * Hoán đổi vị trí VĐV giữa hai trận.
     */
    public function swapAthletes(int $matchId1, string $slot1, int $matchId2, string $slot2): void
    {
        $this->bracketQuery->swapAthletes($matchId1, $slot1, $matchId2, $slot2);
    }

    /**
     * Lấy toàn bộ dữ liệu bảng đấu để hiển thị giao diện.
     *
     * @return array<string, mixed>
     */
    public function getBracketData(int $tournamentId, int $categoryId): array
    {
        return $this->bracketQuery->getBracketData($tournamentId, $categoryId);
    }

    /**
     * Xử lý trận bye (proxy sang matchBuilder).
     */
    public function handleBye(MatchModel $match): void
    {
        $this->matchBuilder->handleBye($match);
    }

    /**
     * Đưa người thắng lên trận tiếp theo (proxy sang matchBuilder).
     */
    public function advanceWinner(MatchModel $match, int $winnerId): void
    {
        $this->matchBuilder->advanceWinner($match, $winnerId);
    }

    /**
     * Kiểm tra tất cả bảng đấu trong nội dung đã hoàn thành chưa.
     */
    public function checkCategoryCompletion(int $tournamentId, int $categoryId): bool
    {
        $groups = \App\Models\Group::where('tournament_id', $tournamentId)
            ->where('category_id', $categoryId)
            ->get();

        if ($groups->isEmpty()) return false;

        foreach ($groups as $group) {
            $pending = $group->matches()
                ->whereNotIn('status', ['completed', 'bye'])
                ->count();
            if ($pending > 0) return false;
        }

        return true;
    }

    /**
     * Kiểm tra bracket đã được tạo cho nội dung này chưa.
     */
    public function isBracketGenerated(int $tournamentId, int $categoryId): bool
    {
        return Round::where('tournament_id', $tournamentId)
            ->where('category_id', $categoryId)
            ->whereIn('round_type', ['knockout', 'quarterfinal', 'semifinal', 'final'])
            ->exists();
    }
}
