<?php

namespace App\Services;

use App\Models\ClubActivity;
use App\Models\ClubActivityMatch;
use App\Models\ClubActivityMatchRound;
use App\Models\ClubActivityMatchStanding;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ClubMatchService
{
    /**
     * Generate match rounds for an activity.
     */
    public function generateMatches(ClubActivity $activity, string $format, int $courtCount): void
    {
        $playerIds = $activity->confirmedParticipants()->pluck('user_id')->toArray();

        $minPlayers = in_array($format, ['rotating_doubles', 'fixed_doubles']) ? 4 : 2;
        if (count($playerIds) < $minPlayers) {
            throw new InvalidArgumentException(
                "Cần ít nhất {$minPlayers} người chơi cho thể thức này. Hiện có " . count($playerIds) . " người."
            );
        }

        DB::transaction(function () use ($activity, $format, $courtCount, $playerIds) {
            $this->deleteExistingMatches($activity);

            $rounds = match ($format) {
                'singles_rr' => $this->generateSinglesRR($playerIds, $courtCount),
                'rotating_doubles' => $this->generateRotatingDoubles($playerIds, $courtCount),
                'fixed_doubles' => $this->generateFixedDoubles($playerIds, $courtCount),
                default => throw new InvalidArgumentException("Thể thức không hợp lệ: {$format}"),
            };

            foreach ($rounds as $roundNum => $matches) {
                $round = $activity->matchRounds()->create([
                    'round_number' => $roundNum + 1,
                    'status' => 'pending',
                ]);
                foreach ($matches as $match) {
                    $round->matches()->create($match);
                }
            }
        });
    }

    /**
     * Singles round-robin using polygon rotation.
     */
    private function generateSinglesRR(array $playerIds, int $courts): array
    {
        $players = $playerIds;
        $isOdd = count($players) % 2 !== 0;
        if ($isOdd) {
            $players[] = null; // bye
        }
        $n = count($players);
        $rounds = [];

        for ($r = 0; $r < $n - 1; $r++) {
            $matches = [];
            for ($i = 0; $i < $n / 2; $i++) {
                $p1 = $players[$i];
                $p2 = $players[$n - 1 - $i];
                if ($p1 === null || $p2 === null) {
                    continue; // skip bye match
                }
                $matches[] = [
                    'match_type' => 'singles',
                    'player1_id' => $p1,
                    'player2_id' => null,
                    'player3_id' => $p2,
                    'player4_id' => null,
                ];
            }
            $matches = $this->distributeMatchesToCourts($matches, $courts);
            $rounds[] = $matches;

            // Rotate: fix players[0], rotate rest
            $last = $players[$n - 1];
            for ($i = $n - 1; $i > 1; $i--) {
                $players[$i] = $players[$i - 1];
            }
            $players[1] = $last;
        }

        return $rounds;
    }

    /**
     * Rotating doubles: each round assigns new partner pairs, avoiding repeats.
     */
    private function generateRotatingDoubles(array $playerIds, int $courts): array
    {
        shuffle($playerIds);
        $n = count($playerIds);
        $matchesPerRound = min(floor($n / 4), $courts);
        if ($matchesPerRound < 1) {
            throw new InvalidArgumentException("Không đủ người chơi cho số sân.");
        }

        $partnerHistory = []; // tracks who has been partnered
        $totalRounds = max(3, intval(ceil($n / 4) + 1));
        $rounds = [];

        for ($r = 0; $r < $totalRounds; $r++) {
            $available = $playerIds;
            shuffle($available);
            $matches = [];

            for ($m = 0; $m < $matchesPerRound && count($available) >= 4; $m++) {
                // Try to find 4 players avoiding repeated partners
                $group = $this->pickDoublesGroup($available, $partnerHistory);
                if ($group === null) {
                    break;
                }

                // Record partner history
                $partnerHistory[$group[0]][$group[1]] = true;
                $partnerHistory[$group[1]][$group[0]] = true;
                $partnerHistory[$group[2]][$group[3]] = true;
                $partnerHistory[$group[3]][$group[2]] = true;

                $matches[] = [
                    'match_type' => 'doubles',
                    'player1_id' => $group[0],
                    'player2_id' => $group[1],
                    'player3_id' => $group[2],
                    'player4_id' => $group[3],
                ];
            }

            if (!empty($matches)) {
                $matches = $this->distributeMatchesToCourts($matches, $courts);
                $rounds[] = $matches;
            }
        }

        return $rounds;
    }

    /**
     * Pick 4 players for a doubles match, preferring new partner combos.
     */
    private function pickDoublesGroup(array &$available, array $partnerHistory): ?array
    {
        if (count($available) < 4) {
            return null;
        }

        // Take first 4 available players
        $group = array_splice($available, 0, 4);

        // Try to arrange into teams avoiding repeated partners
        $arrangements = [
            [$group[0], $group[1], $group[2], $group[3]],
            [$group[0], $group[2], $group[1], $group[3]],
            [$group[0], $group[3], $group[1], $group[2]],
        ];

        foreach ($arrangements as $arr) {
            $t1Repeated = !empty($partnerHistory[$arr[0]][$arr[1]]);
            $t2Repeated = !empty($partnerHistory[$arr[2]][$arr[3]]);
            if (!$t1Repeated && !$t2Repeated) {
                return $arr;
            }
        }

        // Fallback: use first arrangement even if repeated
        return $arrangements[0];
    }

    /**
     * Fixed doubles: permanent pairs play round-robin.
     */
    private function generateFixedDoubles(array $playerIds, int $courts): array
    {
        shuffle($playerIds);

        // Form permanent pairs
        $pairs = [];
        for ($i = 0; $i + 1 < count($playerIds); $i += 2) {
            $pairs[] = [$playerIds[$i], $playerIds[$i + 1]];
        }

        if (count($pairs) < 2) {
            throw new InvalidArgumentException("Cần ít nhất 4 người chơi (2 cặp đôi) cho thể thức này.");
        }

        // Round-robin on pairs (same polygon rotation)
        $isOdd = count($pairs) % 2 !== 0;
        if ($isOdd) {
            $pairs[] = null; // bye pair
        }
        $pn = count($pairs);
        $rounds = [];

        for ($r = 0; $r < $pn - 1; $r++) {
            $matches = [];
            for ($i = 0; $i < $pn / 2; $i++) {
                $p1 = $pairs[$i];
                $p2 = $pairs[$pn - 1 - $i];
                if ($p1 === null || $p2 === null) {
                    continue;
                }
                $matches[] = [
                    'match_type' => 'doubles',
                    'player1_id' => $p1[0],
                    'player2_id' => $p1[1],
                    'player3_id' => $p2[0],
                    'player4_id' => $p2[1],
                ];
            }
            $matches = $this->distributeMatchesToCourts($matches, $courts);
            $rounds[] = $matches;

            $last = $pairs[$pn - 1];
            for ($i = $pn - 1; $i > 1; $i--) {
                $pairs[$i] = $pairs[$i - 1];
            }
            $pairs[1] = $last;
        }

        return $rounds;
    }

    /**
     * Assign court numbers cycling 1..N.
     */
    private function distributeMatchesToCourts(array $matches, int $courts): array
    {
        foreach ($matches as $i => &$match) {
            $match['court_number'] = ($i % $courts) + 1;
        }
        return $matches;
    }

    /**
     * Delete all existing rounds (cascade deletes matches).
     */
    private function deleteExistingMatches(ClubActivity $activity): void
    {
        $activity->matchRounds()->delete();
        $activity->matchStandings()->delete();
    }

    /**
     * Save score for a match and recalculate standings.
     */
    public function saveScore(ClubActivity $activity, ClubActivityMatch $match, int $team1Score, int $team2Score): void
    {
        DB::transaction(function () use ($activity, $match, $team1Score, $team2Score) {
            $match->update([
                'team1_score' => $team1Score,
                'team2_score' => $team2Score,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $playerIds = array_filter([
                $match->player1_id,
                $match->player2_id,
                $match->player3_id,
                $match->player4_id,
            ]);

            $this->recalculateStandings($activity, $playerIds);
        });
    }

    /**
     * Recalculate standings for specific players from all completed matches.
     */
    private function recalculateStandings(ClubActivity $activity, array $userIds): void
    {
        $roundIds = $activity->matchRounds()->pluck('id');

        foreach ($userIds as $userId) {
            $completedMatches = ClubActivityMatch::whereIn('round_id', $roundIds)
                ->where('status', 'completed')
                ->where(function ($q) use ($userId) {
                    $q->where('player1_id', $userId)
                      ->orWhere('player2_id', $userId)
                      ->orWhere('player3_id', $userId)
                      ->orWhere('player4_id', $userId);
                })
                ->get();

            $wins = 0;
            $losses = 0;
            $scored = 0;
            $against = 0;

            foreach ($completedMatches as $m) {
                $isTeam1 = ($m->player1_id === $userId || $m->player2_id === $userId);
                $myScore = $isTeam1 ? $m->team1_score : $m->team2_score;
                $oppScore = $isTeam1 ? $m->team2_score : $m->team1_score;

                $scored += $myScore;
                $against += $oppScore;

                if ($myScore > $oppScore) {
                    $wins++;
                } elseif ($myScore < $oppScore) {
                    $losses++;
                }
                // Tie: neither win nor loss counted
            }

            ClubActivityMatchStanding::updateOrCreate(
                ['club_activity_id' => $activity->id, 'user_id' => $userId],
                [
                    'matches_played' => count($completedMatches),
                    'wins' => $wins,
                    'losses' => $losses,
                    'points_scored' => $scored,
                    'points_against' => $against,
                ]
            );
        }
    }

    /**
     * Get standings sorted by wins desc, point differential desc.
     */
    public function getStandings(ClubActivity $activity): array
    {
        $standings = $activity->matchStandings()
            ->with('user')
            ->orderByDesc('wins')
            ->orderByRaw('(CAST(points_scored AS SIGNED) - CAST(points_against AS SIGNED)) DESC')
            ->get();

        return $standings->map(function ($s, $i) {
            return [
                'rank' => $i + 1,
                'user' => ['id' => $s->user_id, 'name' => $s->user->name ?? 'N/A'],
                'matches_played' => $s->matches_played,
                'wins' => $s->wins,
                'losses' => $s->losses,
                'points_scored' => $s->points_scored,
                'points_against' => $s->points_against,
                'point_differential' => $s->point_differential,
            ];
        })->toArray();
    }

    /**
     * Create a custom ad-hoc match.
     */
    public function createCustomMatch(
        ClubActivity $activity,
        string $matchType,
        int $player1Id,
        ?int $player2Id,
        int $player3Id,
        ?int $player4Id,
        ?int $courtNumber,
        ?int $roundId
    ): ClubActivityMatch {
        $confirmedIds = $activity->confirmedParticipants()->pluck('user_id')->toArray();
        $playerIds = array_filter([$player1Id, $player2Id, $player3Id, $player4Id]);

        foreach ($playerIds as $pid) {
            if (!in_array($pid, $confirmedIds)) {
                throw new InvalidArgumentException("Người chơi ID {$pid} không nằm trong danh sách xác nhận.");
            }
        }

        if (count($playerIds) !== count(array_unique($playerIds))) {
            throw new InvalidArgumentException("Không được chọn trùng người chơi trong cùng một trận.");
        }

        return DB::transaction(function () use (
            $activity, $matchType, $player1Id, $player2Id, $player3Id, $player4Id, $courtNumber, $roundId
        ) {
            if ($roundId) {
                $round = ClubActivityMatchRound::where('id', $roundId)
                    ->where('club_activity_id', $activity->id)
                    ->firstOrFail();
            } else {
                $nextNum = ($activity->matchRounds()->max('round_number') ?? 0) + 1;
                $round = $activity->matchRounds()->create([
                    'round_number' => $nextNum,
                    'status' => 'in_progress',
                ]);
            }

            $match = $round->matches()->create([
                'match_type' => $matchType,
                'player1_id' => $player1Id,
                'player2_id' => $player2Id,
                'player3_id' => $player3Id,
                'player4_id' => $player4Id,
                'court_number' => $courtNumber,
                'status' => 'scheduled',
            ]);

            $match->load(['player1', 'player2', 'player3', 'player4', 'round']);

            return $match;
        });
    }
}
