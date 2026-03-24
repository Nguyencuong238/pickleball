<?php

namespace App\Services;

use App\Models\ClubActivity;
use App\Models\ClubActivityMatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClubMatchmakingService
{
    /**
     * Tao tran dau tu hang doi nguoi choi
     * @return Collection<int, ClubActivityMatch>
     */
    public function generateMatches(ClubActivity $activity): Collection
    {
        return DB::transaction(function () use ($activity) {
            $queued = $activity->participants()
                ->queued()
                ->with('user:id,total_oprs')
                ->orderBy('queue_position')
                ->lockForUpdate()
                ->get();

            $busyCourts = $activity->matches()
                ->whereNull('ended_at')
                ->whereNotNull('started_at')
                ->pluck('scheduled_court');

            $availableCourts = collect(range(1, $activity->courts_count))
                ->diff($busyCourts)
                ->values();

            if ($queued->count() < 4 || $availableCourts->isEmpty()) {
                return collect();
            }

            $pods = $this->createPods($activity, $queued, $availableCourts->count());
            $matches = collect();
            $matchNumber = ($activity->matches()->lockForUpdate()->max('match_number') ?? 0) + 1;

            foreach ($pods as $i => $pod) {
                $court = $availableCourts->get($i);
                if (!$court) break;

                $players = collect($pod)->values();
                if ($players->count() < 4) continue;

                $match = ClubActivityMatch::create([
                    'club_activity_id' => $activity->id,
                    'round_id' => null,
                    'match_type' => 'doubles',
                    'match_number' => $matchNumber++,
                    'scheduled_court' => $court,
                    'player1_id' => $players[0]->user_id,
                    'player2_id' => $players[1]->user_id,
                    'player3_id' => $players[2]->user_id,
                    'player4_id' => $players[3]->user_id,
                    'started_at' => now(),
                    'status' => 'in_progress',
                ]);

                $matches->push($match);

                foreach ($players as $player) {
                    $player->update(['current_status' => 'playing']);
                }
            }

            return $matches;
        });
    }

    /**
     * Nhom nguoi choi thanh cac pod 4 nguoi theo rotation_mode
     */
    private function createPods(ClubActivity $activity, Collection $queued, int $maxPods): array
    {
        $playersNeeded = min($queued->count(), $maxPods * 4);
        $players = $queued->take($playersNeeded);

        if ($activity->rotation_mode === 'oprs_based') {
            $sorted = $players->sortBy(fn($p) => $p->user->total_oprs ?? 0);
            return $sorted->chunk(4)
                ->filter(fn($c) => $c->count() === 4)
                ->values()
                ->all();
        }

        if ($activity->rotation_mode === 'random') {
            return $players->shuffle()->chunk(4)
                ->filter(fn($c) => $c->count() === 4)
                ->values()
                ->all();
        }

        // round_robin: theo thu tu hang doi
        return $players->chunk(4)
            ->filter(fn($c) => $c->count() === 4)
            ->values()
            ->all();
    }

    /**
     * Hoan thanh tran dau va dua nguoi choi ve cuoi hang doi
     */
    public function completeMatch(ClubActivityMatch $match): void
    {
        DB::transaction(function () use ($match) {
            if (!$match->ended_at) {
                $match->update(['ended_at' => now(), 'status' => 'completed']);
            }

            $playerIds = array_filter([
                $match->player1_id,
                $match->player2_id,
                $match->player3_id,
                $match->player4_id,
            ]);

            $activity = $match->activity ?? ClubActivity::find($match->club_activity_id);
            if (!$activity) return;

            $maxPos = $activity->participants()->max('queue_position') ?? 0;

            foreach (array_values($playerIds) as $i => $playerId) {
                $activity->participants()
                    ->where('user_id', $playerId)
                    ->update([
                        'current_status' => 'queued',
                        'queue_position' => $maxPos + $i + 1,
                        'matches_played_count' => DB::raw('matches_played_count + 1'),
                        'last_match_ended_at' => now(),
                    ]);
            }
        });
    }
}
