<?php

namespace App\Listeners\Points;

use App\Events\SkillQuizCompleted;
use App\Models\PointTask;
use App\Models\Referral;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardReferralPoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(SkillQuizCompleted $event): void
    {
        $user = $event->user;

        // Find referral where this user was referred
        $referral = Referral::where('referred_user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$referral) {
            return;
        }

        // Award points to referrer
        $referrer = $referral->referrer;
        if ($referrer) {
            $awarded = $this->pointEarningService->awardPoints(
                $referrer,
                PointTask::CODE_REFERRAL,
                ['referred_user_id' => $user->id]
            );

            if ($awarded) {
                // Mark referral as completed
                $referral->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }
        }
    }
}
