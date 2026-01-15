<?php

namespace App\Listeners\Points;

use App\Events\TournamentCreated;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardTournamentCreatePoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(TournamentCreated $event): void
    {
        // Award once per stadium
        $stadiumId = $event->tournament->stadium_id;

        $this->pointEarningService->awardPoints(
            $event->creator,
            PointTask::CODE_CREATE_TOURNAMENT,
            ['stadium_id' => $stadiumId, 'tournament_id' => $event->tournament->id]
        );
    }
}
