<?php

namespace App\Services\Tournament;

use App\Models\Group;
use App\Models\GroupStanding;
use App\Models\Tournament;
use App\Models\TournamentAthlete;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Xu ly ghi DB cho reset/manual draw.
 * Dam bao xoa standings cu khi boc tham lai, tranh du lieu stale
 * gay sai khop athletes giua trang rankings va trang matches.
 */
class ManualDrawPersistenceHelper
{
    public function resetDraw(Tournament $tournament, int $categoryId): void
    {
        // Chu y: manual_rank_override bi xoa cung standing. Day la hanh vi co y:
        // sau khi boc tham lai, athlete co the o bang khac nen override cu khong con hop le.
        DB::transaction(function () use ($tournament, $categoryId) {
            $groupIds = $this->groupIdsFor($tournament, $categoryId);

            GroupStanding::whereIn('group_id', $groupIds)->delete();

            TournamentAthlete::where('tournament_id', $tournament->id)
                ->where('category_id', $categoryId)
                ->update(['group_id' => null, 'seed_number' => null, 'draw_order' => null]);

            Group::whereIn('id', $groupIds)->update(['current_participants' => 0]);
        });
    }

    public function saveManualDraw(Tournament $tournament, int $categoryId, array $assignments): int
    {
        return DB::transaction(function () use ($tournament, $categoryId, $assignments) {
            $groupIds = $this->groupIdsFor($tournament, $categoryId);

            GroupStanding::whereIn('group_id', $groupIds)->delete();

            TournamentAthlete::where('tournament_id', $tournament->id)
                ->where('category_id', $categoryId)
                ->update(['group_id' => null, 'draw_order' => null]);

            Group::whereIn('id', $groupIds)->update(['current_participants' => 0]);

            $athletesAssigned = 0;
            foreach ($assignments as $groupId => $athleteList) {
                if (empty($athleteList)) {
                    continue;
                }

                $groupIdInt = (int) $groupId;

                foreach ($athleteList as $athleteData) {
                    $athleteId = (int) $athleteData['athlete_id'];
                    TournamentAthlete::where('id', $athleteId)
                        ->where('tournament_id', $tournament->id)
                        ->update(['group_id' => $groupIdInt, 'draw_order' => $athleteData['draw_order'] ?? null]);
                    $this->createEmptyStanding($groupIdInt, $athleteId);
                    $athletesAssigned++;

                    if (!empty($athleteData['partner_id'])) {
                        $partnerId = (int) $athleteData['partner_id'];
                        TournamentAthlete::where('id', $partnerId)
                            ->where('tournament_id', $tournament->id)
                            ->update(['group_id' => $groupIdInt, 'draw_order' => null]);
                        $this->createEmptyStanding($groupIdInt, $partnerId);
                        $athletesAssigned++;
                    }
                }

                $count = array_reduce($athleteList, function ($carry, $item) {
                    return $carry + 1 + (!empty($item['partner_id']) ? 1 : 0);
                }, 0);
                Group::where('id', $groupIdInt)->update(['current_participants' => $count]);
            }

            return $athletesAssigned;
        });
    }

    private function groupIdsFor(Tournament $tournament, int $categoryId): Collection
    {
        return Group::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->pluck('id');
    }

    private function createEmptyStanding(int $groupId, int $athleteId): void
    {
        GroupStanding::updateOrCreate(
            ['group_id' => $groupId, 'athlete_id' => $athleteId],
            [
                'rank_position' => 0, 'matches_played' => 0, 'matches_won' => 0,
                'matches_lost' => 0, 'matches_drawn' => 0, 'win_rate' => 0, 'points' => 0,
                'sets_won' => 0, 'sets_lost' => 0, 'sets_differential' => 0,
                'games_won' => 0, 'games_lost' => 0, 'games_differential' => 0, 'is_advanced' => false,
            ]
        );
    }
}
