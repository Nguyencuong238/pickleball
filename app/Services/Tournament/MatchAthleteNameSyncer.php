<?php

namespace App\Services\Tournament;

use App\Models\MatchModel;
use App\Models\TournamentAthlete;

/**
 * Keeps denormalized athlete1_name / athlete2_name in `matches` in sync when
 * a TournamentAthlete is edited. Handles both singles (raw name) and doubles
 * (composite "Player1 / Player2" string).
 */
class MatchAthleteNameSyncer
{
    /**
     * Sync cached athlete names in all matches referencing the given athlete IDs.
     * Only athletes that appear as a primary (athlete1_id / athlete2_id) will
     * have their display name refreshed; a doubles partner that is not the
     * primary is still accounted for via the primary's composite name.
     *
     * @param array<int> $athleteIds
     */
    public function syncMany(array $athleteIds): void
    {
        $ids = array_values(array_unique(array_filter($athleteIds)));
        if (empty($ids)) {
            return;
        }

        $athletes = TournamentAthlete::with(['category', 'partner'])
            ->whereIn('id', $ids)
            ->get();

        foreach ($athletes as $athlete) {
            $this->syncForAthlete($athlete);
        }
    }

    /**
     * Recompute and persist the display name for all matches where this
     * athlete is recorded as athlete1 or athlete2.
     */
    public function syncForAthlete(TournamentAthlete $athlete): void
    {
        $athlete->loadMissing(['category', 'partner']);
        $displayName = $this->buildDisplayName($athlete);

        // Limit to matches in the athlete's current category so that orphaned
        // rows from a prior category (if the athlete was moved) are left alone.
        $categoryId = $athlete->category_id;

        MatchModel::where('athlete1_id', $athlete->id)
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->update(['athlete1_name' => $displayName]);

        MatchModel::where('athlete2_id', $athlete->id)
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->update(['athlete2_name' => $displayName]);
    }

    /**
     * Build display name: composite "A / B" for doubles with a partner,
     * otherwise the athlete's own name.
     */
    private function buildDisplayName(TournamentAthlete $athlete): string
    {
        $ownName = $athlete->athlete_name ?? ($athlete->user?->name ?? 'Unknown');

        if ($athlete->category?->isDoubles() && $athlete->partner) {
            $partnerName = $athlete->partner->athlete_name
                ?? ($athlete->partner->user?->name ?? 'Unknown');
            return $ownName . ' / ' . $partnerName;
        }

        return $ownName;
    }
}
