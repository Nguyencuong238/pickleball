<?php

namespace App\Console\Commands;

use App\Models\OcrMatch;
use App\Models\PointTask;
use App\Models\User;
use App\Models\UserPointTransaction;
use App\Services\PointEarningService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckWeeklyMatchBonusCommand extends Command
{
    protected $signature = 'points:check-weekly';
    protected $description = 'Check and award weekly 5-match bonus points';

    public function __construct(
        private PointEarningService $pointEarningService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $count = 0;

        // Find users with 5+ confirmed matches this week (returns userId => matchCount)
        $eligibleUsers = $this->getEligibleUserIds($startOfWeek);

        if (empty($eligibleUsers)) {
            $this->info('No eligible users found');
            return self::SUCCESS;
        }

        // Eager load all users at once (fix N+1)
        $users = User::whereIn('id', array_keys($eligibleUsers))->get()->keyBy('id');

        // Get already awarded user IDs this week in one query
        $alreadyAwardedIds = UserPointTransaction::whereIn('user_id', array_keys($eligibleUsers))
            ->where('metadata->task_code', PointTask::CODE_WEEKLY_5_MATCHES)
            ->where('created_at', '>=', $startOfWeek)
            ->pluck('user_id')
            ->toArray();

        foreach ($eligibleUsers as $userId => $matchCount) {
            // Skip if user not found
            if (!isset($users[$userId])) {
                continue;
            }

            // Skip if already awarded
            if (in_array($userId, $alreadyAwardedIds, true)) {
                continue;
            }

            $user = $users[$userId];

            try {
                $awarded = $this->pointEarningService->awardPoints(
                    $user,
                    PointTask::CODE_WEEKLY_5_MATCHES,
                    [
                        'matches_played' => $matchCount,
                        'week_start' => $startOfWeek->format('Y-m-d'),
                    ]
                );

                if ($awarded) {
                    $count++;
                    $this->info("Awarded weekly bonus to user #{$userId} ({$matchCount} matches)");
                }
            } catch (\Exception $e) {
                Log::warning("Weekly bonus failed for user {$userId}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Awarded weekly bonus to {$count} users");
        return self::SUCCESS;
    }

    /**
     * Get user IDs with 5+ confirmed matches this week
     *
     * @return array<int, int> userId => matchCount
     */
    private function getEligibleUserIds(Carbon $startOfWeek): array
    {
        // Get all confirmed matches this week
        $matches = OcrMatch::where('status', OcrMatch::STATUS_CONFIRMED)
            ->where('confirmed_at', '>=', $startOfWeek)
            ->get();

        // Count matches per user
        $userMatchCounts = [];
        foreach ($matches as $match) {
            foreach ($match->getAllParticipantIds() as $userId) {
                if (!isset($userMatchCounts[$userId])) {
                    $userMatchCounts[$userId] = 0;
                }
                $userMatchCounts[$userId]++;
            }
        }

        // Filter users with 5+ matches, return userId => matchCount
        return array_filter($userMatchCounts, fn($count) => $count >= 5);
    }
}
