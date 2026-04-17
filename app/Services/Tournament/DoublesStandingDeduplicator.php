<?php

namespace App\Services\Tournament;

use App\Models\GroupStanding;
use Illuminate\Support\Collection;

/**
 * Doubles dedup: moi cap partner chi giu 1 standing (row co matches_played cao hon thang).
 * Tach khoi TournamentRankingController de giam LOC + tai su dung.
 */
class DoublesStandingDeduplicator
{
    /**
     * @param iterable<GroupStanding> $standings
     */
    public function dedupe(iterable $standings): Collection
    {
        $dedupMap = [];
        foreach ($standings as $standing) {
            $athleteId = $standing->athlete_id;
            $partnerId = $standing->athlete?->partner_id;

            if ($partnerId && isset($dedupMap[$partnerId])) {
                $existing = $dedupMap[$partnerId];
                if ($standing->matches_played > $existing->matches_played) {
                    unset($dedupMap[$partnerId]);
                    $dedupMap[$athleteId] = $standing;
                }
            } else {
                $dedupMap[$athleteId] = $standing;
            }
        }

        return collect(array_values($dedupMap))->sortBy('rank_position')->values();
    }
}
