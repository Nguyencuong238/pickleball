<?php

namespace App\Listeners\Points;

use App\Events\MatchScored;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardRefereeScoringPoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(MatchScored $event): void
    {
        $this->pointEarningService->awardPoints(
            $event->referee,
            PointTask::CODE_REFEREE_SCORE_MATCH,
            ['match_id' => $event->match->id]
        );
    }
}
