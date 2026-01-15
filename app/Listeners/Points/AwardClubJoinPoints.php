<?php

namespace App\Listeners\Points;

use App\Events\ClubMemberAdded;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardClubJoinPoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(ClubMemberAdded $event): void
    {
        // Only award once (first club join)
        $this->pointEarningService->awardPoints(
            $event->user,
            PointTask::CODE_JOIN_CLUB,
            ['club_id' => $event->club->id]
        );
    }
}
