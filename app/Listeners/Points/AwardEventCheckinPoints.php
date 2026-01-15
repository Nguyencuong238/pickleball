<?php

namespace App\Listeners\Points;

use App\Events\EventCheckedIn;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardEventCheckinPoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(EventCheckedIn $event): void
    {
        $this->pointEarningService->awardPoints(
            $event->user,
            PointTask::CODE_JOIN_EVENT,
            ['event_id' => $event->event->id]
        );
    }
}
