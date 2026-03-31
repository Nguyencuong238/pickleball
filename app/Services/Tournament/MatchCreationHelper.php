<?php

namespace App\Services\Tournament;

use App\Models\Group;
use App\Models\MatchModel;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use Illuminate\Support\Facades\Log;

/**
 * Handles round-robin match generation for groups.
 * Used internally by TournamentMatchService.
 */
class MatchCreationHelper
{
    public function createMatchesForGroups(
        Tournament $tournament,
        int $categoryId,
        iterable $groups,
        bool $isDouble,
        int $bestOf = 3
    ): void {
        try {
            foreach ($groups as $group) {
                $athletes = TournamentAthlete::where('group_id', $group->id)
                    ->with(['partner'])
                    ->get();

                if ($athletes->count() < 2) {
                    continue;
                }

                $matchCount = MatchModel::where('tournament_id', $tournament->id)
                    ->where('category_id', $categoryId)
                    ->where('group_id', $group->id)
                    ->count();

                if ($isDouble) {
                    $this->createDoubleMatches($tournament, $categoryId, $group, $athletes, $matchCount, $bestOf);
                } else {
                    $this->createSingleMatches($tournament, $categoryId, $group, $athletes, $matchCount, $bestOf);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error creating matches for groups: ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function createSingleMatches(
        Tournament $tournament,
        int $categoryId,
        Group $group,
        $athletes,
        int &$matchCount,
        int $bestOf = 3
    ): void {
        $schedule = $this->generateRoundRobinSchedule($athletes->values()->all());

        foreach ($schedule as [$athlete1, $athlete2]) {
            if ($athlete1->user_id && $athlete1->user_id === $athlete2->user_id) {
                continue;
            }

            $matchCount++;

            MatchModel::create([
                'tournament_id' => $tournament->id,
                'athlete1_id' => $athlete1->id,
                'athlete1_name' => $athlete1->athlete_name ?? ($athlete1->user?->name ?? 'Unknown'),
                'athlete2_id' => $athlete2->id,
                'athlete2_name' => $athlete2->athlete_name ?? ($athlete2->user?->name ?? 'Unknown'),
                'category_id' => $categoryId,
                'group_id' => $group->id,
                'match_number' => 'M' . $matchCount,
                'status' => 'scheduled',
                'best_of' => $bestOf,
                'match_date' => now()->toDateString()
            ]);
        }
    }

    public function createDoubleMatches(
        Tournament $tournament,
        int $categoryId,
        Group $group,
        $athletes,
        int &$matchCount,
        int $bestOf = 3
    ): void {
        $pairs = [];
        $processed = [];

        foreach ($athletes as $athlete) {
            if (in_array($athlete->id, $processed)) {
                continue;
            }
            if ($athlete->partner_id && $athlete->partner) {
                $pairs[] = ['player1' => $athlete, 'player2' => $athlete->partner];
                $processed[] = $athlete->id;
                $processed[] = $athlete->partner_id;
            }
        }

        $schedule = $this->generateRoundRobinSchedule($pairs);

        foreach ($schedule as [$pair1, $pair2]) {
            $matchCount++;

            MatchModel::create([
                'tournament_id' => $tournament->id,
                'athlete1_id' => $pair1['player1']->id,
                'athlete1_name' => $pair1['player1']->athlete_name . ' / ' . $pair1['player2']->athlete_name,
                'athlete2_id' => $pair2['player1']->id,
                'athlete2_name' => $pair2['player1']->athlete_name . ' / ' . $pair2['player2']->athlete_name,
                'category_id' => $categoryId,
                'group_id' => $group->id,
                'match_number' => 'M' . $matchCount,
                'status' => 'scheduled',
                'best_of' => $bestOf,
                'match_date' => now()->toDateString()
            ]);
        }
    }

    /**
     * Generate round-robin schedule with optimal match ordering.
     * Uses greedy algorithm to ensure no participant plays consecutive matches
     * whenever possible (unavoidable for 3 participants).
     *
     * @param array $participants List of participants (athletes or pairs)
     * @return array Ordered list of [participant1, participant2] matchups
     */
    private function generateRoundRobinSchedule(array $participants): array
    {
        $n = count($participants);
        if ($n < 2) {
            return [];
        }

        // Generate all matchups
        $allMatches = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $allMatches[] = [$participants[$i], $participants[$j]];
            }
        }

        // Greedy reorder: pick next match that doesn't share participant with previous
        $scheduled = [];
        $remaining = $allMatches;
        $lastMatch = null;

        while (!empty($remaining)) {
            $bestIdx = null;

            if ($lastMatch !== null) {
                // Find first match not sharing a participant with the last one
                foreach ($remaining as $idx => $match) {
                    if ($match[0] !== $lastMatch[0] && $match[0] !== $lastMatch[1]
                        && $match[1] !== $lastMatch[0] && $match[1] !== $lastMatch[1]) {
                        $bestIdx = $idx;
                        break;
                    }
                }
            }

            // Fallback: take first available match
            if ($bestIdx === null) {
                $bestIdx = array_key_first($remaining);
            }

            $lastMatch = $remaining[$bestIdx];
            $scheduled[] = $lastMatch;
            unset($remaining[$bestIdx]);
        }

        return $scheduled;
    }
}
