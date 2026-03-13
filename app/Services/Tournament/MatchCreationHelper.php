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
        for ($i = 0; $i < $athletes->count(); $i++) {
            for ($j = $i + 1; $j < $athletes->count(); $j++) {
                $athlete1 = $athletes[$i];
                $athlete2 = $athletes[$j];

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

        for ($i = 0; $i < count($pairs); $i++) {
            for ($j = $i + 1; $j < count($pairs); $j++) {
                $matchCount++;

                MatchModel::create([
                    'tournament_id' => $tournament->id,
                    'athlete1_id' => $pairs[$i]['player1']->id,
                    'athlete1_name' => $pairs[$i]['player1']->athlete_name . ' / ' . $pairs[$i]['player2']->athlete_name,
                    'athlete2_id' => $pairs[$j]['player1']->id,
                    'athlete2_name' => $pairs[$j]['player1']->athlete_name . ' / ' . $pairs[$j]['player2']->athlete_name,
                    'category_id' => $categoryId,
                    'group_id' => $group->id,
                    'match_number' => 'M' . $matchCount,
                    'status' => 'scheduled',
                    'best_of' => $bestOf,
                    'match_date' => now()->toDateString()
                ]);
            }
        }
    }
}
