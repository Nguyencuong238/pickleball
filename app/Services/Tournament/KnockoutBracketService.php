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
        $totalRounds = (int) \round(\log($bracketSize, 2));

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
            $matchCount  = (int) \pow(2, $roundFromFinal);
            $roundNumber = $totalRounds - $roundFromFinal;

            $rounds[$roundFromFinal] = Round::create([
                'tournament_id'     => $tournament->id,
                'category_id'       => $categoryId,
                'round_name'        => $roundName,
                'round_number'      => $roundNumber,
                'round_type'        => $roundType,
                'start_date'        => $tournament->start_date,
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
            'start_date'        => $tournament->start_date,
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
            'match_date'       => $tournament->start_date,
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
        DB::transaction(function () use ($matchId1, $slot1, $matchId2, $slot2) {
            $this->bracketQuery->swapAthletes($matchId1, $slot1, $matchId2, $slot2);

            $this->reEvaluateMatch(MatchModel::findOrFail($matchId1));
            if ($matchId1 !== $matchId2) {
                $this->reEvaluateMatch(MatchModel::findOrFail($matchId2));
            }
        });
    }

    /**
     * Sau swap, đánh giá lại trạng thái bye của trận đấu.
     * - Xóa winner cũ khỏi trận kế tiếp
     * - Nếu 1 VĐV + 1 null -> handleBye (tự động hoàn thành)
     * - Nếu 2 VĐV hoặc 0 VĐV -> scheduled, xóa winner
     */
    private function reEvaluateMatch(MatchModel $match): void
    {
        $oldWinner = $match->winner_id;

        if ($oldWinner && $match->next_match_id) {
            $this->removeAdvancedAthlete($match->next_match_id, $oldWinner);
        }

        $match->refresh();

        if ($match->athlete1_id && $match->athlete2_id) {
            $match->update(['status' => 'scheduled', 'winner_id' => null]);
        } elseif ($match->athlete1_id || $match->athlete2_id) {
            $match->update(['status' => 'scheduled', 'winner_id' => null]);
            $this->matchBuilder->handleBye($match->fresh());
        } else {
            $match->update(['status' => 'scheduled', 'winner_id' => null]);
        }
    }

    /**
     * Xóa VĐV đã được advance lên trận kế tiếp (khi bye cũ không còn hợp lệ).
     */
    private function removeAdvancedAthlete(int $nextMatchId, int $athleteId): void
    {
        $next = MatchModel::find($nextMatchId);
        if (!$next) return;

        if ($next->athlete1_id === $athleteId) {
            $next->update(['athlete1_id' => null, 'athlete1_name' => null]);
        } elseif ($next->athlete2_id === $athleteId) {
            $next->update(['athlete2_id' => null, 'athlete2_name' => null]);
        }
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
     * Lấy danh sách VDV hợp lệ cho trận đấu bracket.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getEligibleAthletes(MatchModel $match): array
    {
        return $this->bracketQuery->getEligibleAthletes($match);
    }

    /**
     * Cập nhật VDV và thuộc tính trận đấu bracket.
     *
     * @return array{success: bool, affected_count?: int, message?: string}
     */
    public function updateMatch(MatchModel $match, array $data): array
    {
        // Block in_progress matches
        if ($match->status === 'in_progress') {
            return ['success' => false, 'message' => 'Không thể chỉnh sửa trận đấu đang diễn ra.'];
        }

        $athleteChanged = (array_key_exists('athlete1_id', $data) && (int) ($data['athlete1_id'] ?? 0) !== (int) ($match->athlete1_id ?? 0))
            || (array_key_exists('athlete2_id', $data) && (int) ($data['athlete2_id'] ?? 0) !== (int) ($match->athlete2_id ?? 0));

        if ($athleteChanged) {
            $affectedCount = $this->bracketQuery->countCascadeAffected($match);

            if ($affectedCount > 0 && empty($data['confirm_cascade'])) {
                return [
                    'success'        => false,
                    'needs_confirm'  => true,
                    'affected_count' => $affectedCount,
                    'message'        => "Thay đổi VĐV sẽ ảnh hưởng {$affectedCount} trận đấu ở các vòng sau.",
                ];
            }
        }

        return DB::transaction(function () use ($match, $data, $athleteChanged) {
            $match = MatchModel::lockForUpdate()->findOrFail($match->id);

            if ($athleteChanged) {
                $affectedCount = $this->bracketQuery->countCascadeAffected($match);
                if ($affectedCount > 0) {
                    $this->bracketQuery->cascadeClearDownstream($match);
                }
            }

            $this->bracketQuery->updateMatchAthletes($match, $data);

            // Chỉ re-evaluate khi athlete thay đổi hoặc trận chưa completed
            // Trận completed chỉ sửa metadata -> không cần re-evaluate (sẽ phá hủy kết quả)
            if ($athleteChanged || $match->status !== 'completed') {
                $this->reEvaluateMatch($match->fresh());
            }

            return ['success' => true, 'message' => 'Cập nhật thành công'];
        });
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
