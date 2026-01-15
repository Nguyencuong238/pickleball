<?php

namespace App\Listeners\Points;

use App\Events\SocialCreated;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardSocialCreatePoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(SocialCreated $event): void
    {
        // Award once per stadium (social is linked to stadium)
        $stadiumId = $event->social->stadium_id;

        $this->pointEarningService->awardPoints(
            $event->creator,
            PointTask::CODE_CREATE_SOCIAL_SCHEDULE,
            ['stadium_id' => $stadiumId, 'social_id' => $event->social->id]
        );
    }
}
