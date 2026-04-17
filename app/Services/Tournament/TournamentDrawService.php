<?php

namespace App\Services\Tournament;

use App\Models\Group;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use App\Models\TournamentCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TournamentDrawService
{
    public function __construct(
        private DrawAssignmentHelper $assignmentHelper,
        private ManualDrawPersistenceHelper $persistenceHelper,
    ) {}

    public function isDoubleCategory(int $categoryId, Tournament $tournament): bool
    {
        $category = TournamentCategory::where('id', $categoryId)
            ->where('tournament_id', $tournament->id)
            ->first();

        if (!$category) {
            Log::warning('Category not found', ['category_id' => $categoryId, 'tournament_id' => $tournament->id]);
            return false;
        }

        return str_contains($category->category_type, 'double');
    }

    public function getPairsFromAthletes(Collection $athletes): array
    {
        $pairs = [];
        $processed = [];

        foreach ($athletes as $athlete) {
            if (in_array($athlete->id, $processed)) {
                continue;
            }

            if ($athlete->partner_id && $athlete->partner) {
                $pairs[] = [
                    'primary' => $athlete,
                    'partner' => $athlete->partner,
                    'pair_name' => $athlete->athlete_name . ' - ' . $athlete->partner->athlete_name
                ];
                $processed[] = $athlete->id;
                $processed[] = $athlete->partner_id;
            } else {
                Log::warning('Athlete without partner', [
                    'athlete_id' => $athlete->id,
                    'athlete_name' => $athlete->athlete_name
                ]);
            }
        }

        return $pairs;
    }

    public function drawPairsByRandom(array $pairs, Collection $groups): void
    {
        $this->assignmentHelper->drawPairsByRandom($pairs, $groups);
    }

    public function drawPairsBySeeding(array $pairs, Collection $groups): void
    {
        $this->assignmentHelper->drawPairsBySeeding($pairs, $groups);
    }

    public function drawAthletesByRandom(Collection $athletes, Collection $groups): void
    {
        $this->assignmentHelper->drawAthletesByRandom($athletes, $groups);
    }

    public function drawAthletesBySeeding(Collection $athletes, Collection $groups): void
    {
        $this->assignmentHelper->drawAthletesBySeeding($athletes, $groups);
    }

    public function getGroupedAthletes(Collection $groups): array
    {
        $result = [];

        foreach ($groups as $group) {
            $athletes = TournamentAthlete::where('group_id', $group->id)
                ->whereNotNull('draw_order')
                ->with('partner')
                ->orderBy('draw_order')
                ->orderBy('seed_number')
                ->orderBy('id')
                ->get();

            $result[] = [
                'group_id' => $group->id,
                'group_name' => $group->group_name,
                'group_code' => $group->group_code,
                'athletes' => $athletes->map(fn($a) => [
                    'id' => $a->id,
                    'name' => $a->athlete_name,
                    'pair_name' => $a->partner_id
                        ? $a->athlete_name . ' / ' . ($a->partner?->athlete_name ?? '')
                        : null,
                    'seed_number' => $a->seed_number,
                    'position' => $a->position,
                    'partner_id' => $a->partner_id,
                    'partner_name' => $a->partner?->athlete_name
                ])->toArray()
            ];
        }

        return $result;
    }

    public function resetDraw(Tournament $tournament, int $categoryId): void
    {
        $this->persistenceHelper->resetDraw($tournament, $categoryId);
    }

    public function getManualDrawData(Tournament $tournament, int $categoryId): array
    {
        $athletes = TournamentAthlete::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->where('status', 'approved')
            ->select('id', 'athlete_name', 'group_id', 'partner_id')
            ->get();

        $category = TournamentCategory::find($categoryId);
        $isDouble = $category && str_contains($category->category_type, 'double');

        $groups = Group::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->select('id', 'group_name', 'max_participants', 'current_participants')
            ->get();

        $pairsData = [];
        if ($isDouble) {
            $processed = [];
            foreach ($athletes as $athlete) {
                if (in_array($athlete->id, $processed)) {
                    continue;
                }
                if ($athlete->partner_id && $athlete->partner_id > 0) {
                    $partner = $athletes->firstWhere('id', $athlete->partner_id);
                    if ($partner) {
                        $pairsData[] = [
                            'pair_id' => 'pair_' . $athlete->id . '_' . $partner->id,
                            'athlete1_id' => $athlete->id,
                            'athlete1_name' => $athlete->athlete_name,
                            'athlete2_id' => $partner->id,
                            'athlete2_name' => $partner->athlete_name,
                            'group_id' => $athlete->group_id
                        ];
                        $processed[] = $athlete->id;
                        $processed[] = $partner->id;
                    }
                }
            }
        }

        return [
            'athletes' => $isDouble ? $pairsData : $athletes,
            'groups' => $groups,
            'is_double' => $isDouble
        ];
    }

    public function saveManualDraw(Tournament $tournament, int $categoryId, array $assignments): int
    {
        return $this->persistenceHelper->saveManualDraw($tournament, $categoryId, $assignments);
    }
}
