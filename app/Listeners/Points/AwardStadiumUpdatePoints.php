<?php

namespace App\Listeners\Points;

use App\Events\StadiumUpdated;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardStadiumUpdatePoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(StadiumUpdated $event): void
    {
        // Award once per stadium
        $this->pointEarningService->awardPoints(
            $event->owner,
            PointTask::CODE_UPDATE_STADIUM_INFO,
            ['stadium_id' => $event->stadium->id]
        );
    }
}
