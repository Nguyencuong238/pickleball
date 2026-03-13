<?php

namespace App\Services\Tournament;

use App\Models\Group;
use App\Models\TournamentAthlete;
use InvalidArgumentException;

class BracketSeedingHelper
{
    /**
     * Thu thập vận động viên đã vượt qua vòng bảng, sắp xếp theo hạt giống.
     *
     * @return array<int, array{athlete_id: int, seed: int, name: string, partner_name: string|null}>
     */
    public function collectSeededAthletes(int $tournamentId, int $categoryId): array
    {
        $groups = Group::where('tournament_id', $tournamentId)
            ->where('category_id', $categoryId)
            ->with(['standings' => function ($query) {
                $query->where('is_advanced', true)->orderBy('rank_position');
            }, 'standings.athlete.partner'])
            ->get();

        $seeded = [];

        foreach ($groups as $group) {
            foreach ($group->standings as $standing) {
                $athlete = $standing->athlete;
                if (!$athlete) {
                    continue;
                }

                $seeded[] = [
                    'athlete_id'   => $athlete->id,
                    'seed'         => $athlete->seed_number ?? 0,
                    'name'         => $athlete->athlete_name,
                    'partner_name' => $athlete->partner?->athlete_name,
                ];
            }
        }

        // Ưu tiên hạt giống có số nhỏ hơn (seed 1 = mạnh nhất), seed 0 = chưa xếp hạt giống
        usort($seeded, function (array $a, array $b): int {
            if ($a['seed'] === 0 && $b['seed'] === 0) {
                return 0;
            }
            if ($a['seed'] === 0) {
                return 1;
            }
            if ($b['seed'] === 0) {
                return -1;
            }
            return $a['seed'] <=> $b['seed'];
        });

        return $seeded;
    }

    /**
     * Sắp xếp hạt giống vào bảng đấu loại trực tiếp theo chuẩn quốc tế.
     * Hạt giống 1 và 2 ở hai nhánh đối nhau, không gặp nhau trước chung kết.
     *
     * @param array<int, array{athlete_id: int, seed: int, name: string, partner_name: string|null}> $seededAthletes
     * @return array<int, int|null> Mảng vị trí slot đã sắp xếp (null = bye)
     */
    public function arrangeSeedsIntoBracket(array $seededAthletes, int $bracketSize): array
    {
        // Vị trí chuẩn cho các hạt giống (chỉ số 1-based)
        $seedPositions = $this->buildSeedPositions($bracketSize);

        $slots = array_fill(0, $bracketSize, null);

        foreach ($seededAthletes as $index => $athlete) {
            $position = $seedPositions[$index] ?? null;
            if ($position !== null && $position <= $bracketSize) {
                $slots[$position - 1] = $athlete['athlete_id'];
            }
        }

        return $slots;
    }

    /**
     * Tính kích thước bảng đấu (lũy thừa của 2 gần nhất).
     */
    public function calculateBracketSize(int $athleteCount): int
    {
        if ($athleteCount < 2) {
            throw new InvalidArgumentException('Cần tối thiểu 2 vận động viên để tạo bảng đấu.');
        }

        $size = 1;
        while ($size < $athleteCount) {
            $size *= 2;
        }

        return $size;
    }

    /**
     * Xác định loại vòng đấu dựa vào khoảng cách đến trận chung kết.
     */
    public function getRoundType(int $roundFromFinal, int $totalRounds): string
    {
        if ($roundFromFinal === 0) {
            return 'final';
        }
        if ($roundFromFinal === 1) {
            return 'semifinal';
        }
        if ($roundFromFinal === 2) {
            return 'quarterfinal';
        }

        return 'knockout';
    }

    /**
     * Tên hiển thị tiếng Việt cho từng vòng đấu.
     */
    public function getRoundName(string $roundType, int $roundFromFinal, int $totalRounds): string
    {
        return match ($roundType) {
            'final'        => 'Chung kết',
            'semifinal'    => 'Bán kết',
            'quarterfinal' => 'Tứ kết',
            'bronze'       => 'Tranh hạng ba',
            default        => $this->buildKnockoutRoundName($roundFromFinal, $totalRounds),
        };
    }

    /**
     * Xây dựng tên vòng knockout theo số lượng VĐV còn lại.
     */
    private function buildKnockoutRoundName(int $roundFromFinal, int $totalRounds): string
    {
        $matchesInRound = (int) pow(2, $roundFromFinal);

        return "Vòng 1/{$matchesInRound}";
    }

    /**
     * Xây dựng danh sách vị trí slot theo chuẩn seeding.
     * Ví dụ 8 VĐV: [1,8,5,4,3,6,7,2]
     *
     * @return array<int, int>
     */
    private function buildSeedPositions(int $bracketSize): array
    {
        $positions = [1];

        while (count($positions) < $bracketSize) {
            $next = [];
            foreach ($positions as $pos) {
                $next[] = $pos;
                $next[] = $bracketSize + 1 - $pos;
            }
            $positions = $next;
        }

        return $positions;
    }
}
