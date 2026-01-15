<?php

namespace App\Listeners\Points;

use App\Events\OcrMatchConfirmed;
use App\Models\PointTask;
use App\Services\PointEarningService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwardOcrMatchPoints implements ShouldQueue
{
    public function __construct(
        private PointEarningService $pointEarningService
    ) {}

    public function handle(OcrMatchConfirmed $event): void
    {
        $match = $event->match;

        // Award to challenger (match creator)
        $challenger = $match->challenger;
        if ($challenger) {
            $this->pointEarningService->awardPoints(
                $challenger,
                PointTask::CODE_CREATE_OCR_MATCH,
                ['match_id' => $match->id]
            );
        }
    }
}
