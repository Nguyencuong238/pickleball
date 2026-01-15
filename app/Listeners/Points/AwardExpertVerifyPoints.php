<?php

namespace App\Listeners\Points;

use App\Events\EloVerified;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardExpertVerifyPoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(EloVerified $event): void
    {
        $this->pointEarningService->awardPoints(
            $event->verifier,
            PointTask::CODE_EXPERT_VERIFY_ELO,
            [
                'verification_request_id' => $event->request->id,
                'verified_user_id' => $event->verifiedUser->id,
            ]
        );
    }
}
