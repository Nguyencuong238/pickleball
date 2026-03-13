<?php

namespace App\Services\Tournament;

use App\Models\Group;
use App\Models\GroupStanding;
use App\Models\TournamentAthlete;
use Illuminate\Support\Collection;

/**
 * Handles the actual distribution algorithms for draw assignment.
 * Used internally by TournamentDrawService.
 */
class DrawAssignmentHelper
{
    public function drawPairsByRandom(array $pairs, Collection $groups): void
    {
        $shuffled = collect($pairs)->shuffle();
        $groupsCollection = $groups->values();
        $groupIndex = 0;
        $groupDrawOrder = [];

        foreach ($groupsCollection as $g) {
            $groupDrawOrder[$g->id] = 0;
        }

        foreach ($shuffled as $pair) {
            $group = $groupsCollection[$groupIndex % $groupsCollection->count()];
            $groupDrawOrder[$group->id]++;
            $currentDrawOrder = $groupDrawOrder[$group->id];

            TournamentAthlete::where('id', $pair['primary']->id)
                ->update(['group_id' => $group->id, 'seed_number' => null, 'draw_order' => $currentDrawOrder]);

            TournamentAthlete::where('id', $pair['partner']->id)
                ->update(['group_id' => $group->id, 'seed_number' => null, 'draw_order' => null]);

            $this->createPairStandings($group->id, $pair['primary']->id, $pair['partner']->id);

            $groupIndex++;
        }

        $this->updateParticipantCounts($groupsCollection);
    }

    public function drawPairsBySeeding(array $pairs, Collection $groups): void
    {
        $sorted = collect($pairs)->sortBy(fn($pair) => $pair['primary']->position ?? 0)->values();
        $groupsCollection = $groups->values();
        $groupIndex = 0;
        $ascending = true;
        $groupDrawOrder = [];

        foreach ($groupsCollection as $g) {
            $groupDrawOrder[$g->id] = 0;
        }

        foreach ($sorted as $index => $pair) {
            $group = $groupsCollection[$groupIndex];
            $groupDrawOrder[$group->id]++;
            $currentDrawOrder = $groupDrawOrder[$group->id];

            TournamentAthlete::where('id', $pair['primary']->id)
                ->update(['group_id' => $group->id, 'seed_number' => $index + 1, 'draw_order' => $currentDrawOrder]);

            TournamentAthlete::where('id', $pair['partner']->id)
                ->update(['group_id' => $group->id, 'seed_number' => $index + 1, 'draw_order' => null]);

            $this->createPairStandings($group->id, $pair['primary']->id, $pair['partner']->id);

            [$groupIndex, $ascending] = $this->snakeDraftAdvance($groupIndex, $ascending, $groups->count());
        }

        $this->updateParticipantCounts($groupsCollection);
    }

    public function drawAthletesByRandom(Collection $athletes, Collection $groups): void
    {
        $shuffled = $athletes->shuffle();
        $groupsCollection = $groups->values();
        $groupIndex = 0;
        $groupDrawOrder = [];

        foreach ($groupsCollection as $g) {
            $groupDrawOrder[$g->id] = 0;
        }

        foreach ($shuffled as $athlete) {
            $group = $groupsCollection[$groupIndex % $groupsCollection->count()];
            $groupDrawOrder[$group->id]++;
            $currentDrawOrder = $groupDrawOrder[$group->id];

            $athlete->update(['group_id' => $group->id, 'seed_number' => null, 'draw_order' => $currentDrawOrder]);

            GroupStanding::updateOrCreate(
                ['group_id' => $group->id, 'athlete_id' => $athlete->id],
                $this->defaultStandingData()
            );

            $groupIndex++;
        }

        $this->updateParticipantCounts($groupsCollection);
    }

    public function drawAthletesBySeeding(Collection $athletes, Collection $groups): void
    {
        $sorted = $athletes->sortBy('position')->values();
        $groupsCollection = $groups->values();
        $groupIndex = 0;
        $ascending = true;
        $groupDrawOrder = [];

        foreach ($groupsCollection as $g) {
            $groupDrawOrder[$g->id] = 0;
        }

        foreach ($sorted as $index => $athlete) {
            $group = $groupsCollection[$groupIndex];
            $groupDrawOrder[$group->id]++;
            $currentDrawOrder = $groupDrawOrder[$group->id];

            $athlete->update(['group_id' => $group->id, 'seed_number' => $index + 1, 'draw_order' => $currentDrawOrder]);

            GroupStanding::updateOrCreate(
                ['group_id' => $group->id, 'athlete_id' => $athlete->id],
                $this->defaultStandingData()
            );

            [$groupIndex, $ascending] = $this->snakeDraftAdvance($groupIndex, $ascending, $groups->count());
        }

        $this->updateParticipantCounts($groupsCollection);
    }

    private function snakeDraftAdvance(int $groupIndex, bool $ascending, int $groupCount): array
    {
        if ($ascending) {
            $groupIndex++;
            if ($groupIndex >= $groupCount) {
                $groupIndex = $groupCount - 1;
                $ascending = false;
            }
        } else {
            $groupIndex--;
            if ($groupIndex < 0) {
                $groupIndex = 0;
                $ascending = true;
            }
        }

        return [$groupIndex, $ascending];
    }

    private function createPairStandings(int $groupId, int $primaryId, int $partnerId): void
    {
        GroupStanding::updateOrCreate(['group_id' => $groupId, 'athlete_id' => $primaryId], $this->defaultStandingData());
        GroupStanding::updateOrCreate(['group_id' => $groupId, 'athlete_id' => $partnerId], $this->defaultStandingData());
    }

    private function updateParticipantCounts(Collection $groups): void
    {
        foreach ($groups as $group) {
            $count = TournamentAthlete::where('group_id', $group->id)->count();
            $group->update(['current_participants' => $count]);
        }
    }

    private function defaultStandingData(): array
    {
        return [
            'rank_position' => 0, 'matches_played' => 0, 'matches_won' => 0,
            'matches_lost' => 0, 'matches_drawn' => 0, 'win_rate' => 0, 'points' => 0,
            'sets_won' => 0, 'sets_lost' => 0, 'sets_differential' => 0,
            'games_won' => 0, 'games_lost' => 0, 'games_differential' => 0, 'is_advanced' => false,
        ];
    }
}
