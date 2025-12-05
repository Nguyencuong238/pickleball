<?php

namespace App\Services;

use App\Models\CommunityActivity;
use App\Models\OcrMatch;
use App\Models\Social;
use App\Models\Stadium;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class CommunityService
{
    public function __construct(
        private OprsService $oprsService
    ) {}

    /**
     * Record check-in at stadium
     *
     * @throws InvalidArgumentException
     */
    public function checkIn(User $user, Stadium $stadium): CommunityActivity
    {
        // Check daily limit for this stadium
        if (!$this->canCheckInToday($user, $stadium->id)) {
            throw new InvalidArgumentException('Already checked in at this location today');
        }

        return DB::transaction(function () use ($user, $stadium) {
            $points = CommunityActivity::getPoints(CommunityActivity::TYPE_CHECK_IN);

            $activity = CommunityActivity::create([
                'user_id' => $user->id,
                'activity_type' => CommunityActivity::TYPE_CHECK_IN,
                'points_earned' => $points,
                'reference_id' => $stadium->id,
                'reference_type' => Stadium::class,
                'metadata' => [
                    'stadium_name' => $stadium->name,
                    'location' => $stadium->address ?? '',
                ],
            ]);

            $this->awardPoints($user, $activity);

            return $activity;
        });
    }

    /**
     * Record event participation
     *
     * @throws InvalidArgumentException
     */
    public function recordEventParticipation(User $user, Social $event): CommunityActivity
    {
        // Check if already recorded for this event
        $existing = CommunityActivity::where('user_id', $user->id)
            ->where('activity_type', CommunityActivity::TYPE_EVENT)
            ->where('reference_id', $event->id)
            ->where('reference_type', Social::class)
            ->exists();

        if ($existing) {
            throw new InvalidArgumentException('Already recorded participation for this event');
        }

        return DB::transaction(function () use ($user, $event) {
            $points = CommunityActivity::getPoints(CommunityActivity::TYPE_EVENT);

            $activity = CommunityActivity::create([
                'user_id' => $user->id,
                'activity_type' => CommunityActivity::TYPE_EVENT,
                'points_earned' => $points,
                'reference_id' => $event->id,
                'reference_type' => Social::class,
                'metadata' => [
                    'event_name' => $event->name ?? 'Event',
                    'event_date' => $event->date?->format('Y-m-d'),
                ],
            ]);

            $this->awardPoints($user, $activity);

            return $activity;
        });
    }

    /**
     * Record referral
     *
     * @throws InvalidArgumentException
     */
    public function recordReferral(User $referrer, User $referredUser): CommunityActivity
    {
        // Validate referred user is new (created within 7 days)
        if ($referredUser->created_at < Carbon::now()->subDays(7)) {
            throw new InvalidArgumentException('Referred user is not a new registration');
        }

        // Check if already claimed
        $existing = CommunityActivity::where('user_id', $referrer->id)
            ->where('activity_type', CommunityActivity::TYPE_REFERRAL)
            ->where('reference_id', $referredUser->id)
            ->where('reference_type', User::class)
            ->exists();

        if ($existing) {
            throw new InvalidArgumentException('Referral already recorded for this user');
        }

        return DB::transaction(function () use ($referrer, $referredUser) {
            $points = CommunityActivity::getPoints(CommunityActivity::TYPE_REFERRAL);

            $activity = CommunityActivity::create([
                'user_id' => $referrer->id,
                'activity_type' => CommunityActivity::TYPE_REFERRAL,
                'points_earned' => $points,
                'reference_id' => $referredUser->id,
                'reference_type' => User::class,
                'metadata' => [
                    'referred_user_name' => $referredUser->name,
                    'referred_user_email' => $referredUser->email,
                ],
            ]);

            $this->awardPoints($referrer, $activity);

            return $activity;
        });
    }

    /**
     * Check and award weekly match bonus
     */
    public function checkWeeklyMatchBonus(User $user): ?CommunityActivity
    {
        // Check if already awarded this week
        $startOfWeek = Carbon::now()->startOfWeek();

        $alreadyAwarded = CommunityActivity::where('user_id', $user->id)
            ->where('activity_type', CommunityActivity::TYPE_WEEKLY_MATCHES)
            ->where('created_at', '>=', $startOfWeek)
            ->exists();

        if ($alreadyAwarded) {
            return null;
        }

        // Count matches this week
        $matchCount = OcrMatch::forUser($user->id)
            ->where('status', OcrMatch::STATUS_CONFIRMED)
            ->where('confirmed_at', '>=', $startOfWeek)
            ->count();

        if ($matchCount < 5) {
            return null;
        }

        return DB::transaction(function () use ($user, $matchCount) {
            $points = CommunityActivity::getPoints(CommunityActivity::TYPE_WEEKLY_MATCHES);

            $activity = CommunityActivity::create([
                'user_id' => $user->id,
                'activity_type' => CommunityActivity::TYPE_WEEKLY_MATCHES,
                'points_earned' => $points,
                'metadata' => [
                    'matches_played' => $matchCount,
                    'week_start' => Carbon::now()->startOfWeek()->format('Y-m-d'),
                ],
            ]);

            $this->awardPoints($user, $activity);

            return $activity;
        });
    }

    /**
     * Record monthly challenge completion
     *
     * @param array{challenge_id?: int, description?: string} $challengeData
     * @throws InvalidArgumentException
     */
    public function recordMonthlyChallenge(User $user, array $challengeData): CommunityActivity
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        // Check if already completed this month
        $existing = CommunityActivity::where('user_id', $user->id)
            ->where('activity_type', CommunityActivity::TYPE_MONTHLY_CHALLENGE)
            ->where('created_at', '>=', $startOfMonth)
            ->exists();

        if ($existing) {
            throw new InvalidArgumentException('Monthly challenge already completed');
        }

        return DB::transaction(function () use ($user, $challengeData) {
            $points = CommunityActivity::getPoints(CommunityActivity::TYPE_MONTHLY_CHALLENGE);

            $activity = CommunityActivity::create([
                'user_id' => $user->id,
                'activity_type' => CommunityActivity::TYPE_MONTHLY_CHALLENGE,
                'points_earned' => $points,
                'metadata' => array_merge($challengeData, [
                    'month' => Carbon::now()->format('Y-m'),
                ]),
            ]);

            $this->awardPoints($user, $activity);

            return $activity;
        });
    }

    /**
     * Award points and recalculate OPRS
     */
    private function awardPoints(User $user, CommunityActivity $activity): void
    {
        $newScore = $user->community_score + $activity->points_earned;

        $user->update([
            'community_score' => $newScore,
        ]);

        $this->oprsService->recalculateAfterActivity($user, $activity->id);
    }

    /**
     * Check if user can check in today at stadium
     */
    public function canCheckInToday(User $user, int $stadiumId): bool
    {
        $today = Carbon::today();

        return !CommunityActivity::where('user_id', $user->id)
            ->where('activity_type', CommunityActivity::TYPE_CHECK_IN)
            ->where('reference_id', $stadiumId)
            ->where('reference_type', Stadium::class)
            ->whereDate('created_at', $today)
            ->exists();
    }

    /**
     * Get user's activity history
     *
     * @return Collection<int, CommunityActivity>
     */
    public function getActivityHistory(User $user, int $limit = 50): Collection
    {
        return $user->communityActivities()
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get activity statistics
     *
     * @return array{total_points: float, by_type: array<string, array{count: int, points: float, info: array}>, recent_count: int}
     */
    public function getActivityStats(User $user): array
    {
        $byType = $user->communityActivities()
            ->selectRaw('activity_type, COUNT(*) as count, SUM(points_earned) as points')
            ->groupBy('activity_type')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->activity_type => [
                    'count' => (int) $row->count,
                    'points' => (float) $row->points,
                    'info' => CommunityActivity::getActivityInfo($row->activity_type),
                ],
            ])
            ->toArray();

        $recentCount = $user->communityActivities()
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();

        return [
            'total_points' => (float) $user->community_score,
            'by_type' => $byType,
            'recent_count' => $recentCount,
        ];
    }

    /**
     * Get all activity types with info
     *
     * @return array<string, array{name: string, description: string, points: int, limit: string|null, icon: string}>
     */
    public function getAllActivityTypes(): array
    {
        $types = [];
        foreach (CommunityActivity::getAllTypes() as $type) {
            $types[$type] = CommunityActivity::getActivityInfo($type);
        }
        return $types;
    }

    /**
     * Process weekly bonuses for all eligible users (scheduled job)
     */
    public function processWeeklyBonuses(): int
    {
        $count = 0;
        $startOfWeek = Carbon::now()->startOfWeek();

        // Find users with confirmed matches this week
        $eligibleUsers = User::whereHas('ocrMatchesAsChallenger', function ($q) use ($startOfWeek) {
            $q->where('status', OcrMatch::STATUS_CONFIRMED)
              ->where('confirmed_at', '>=', $startOfWeek);
        })
        ->orWhereHas('ocrMatchesAsOpponent', function ($q) use ($startOfWeek) {
            $q->where('status', OcrMatch::STATUS_CONFIRMED)
              ->where('confirmed_at', '>=', $startOfWeek);
        })
        ->get();

        foreach ($eligibleUsers as $user) {
            try {
                $result = $this->checkWeeklyMatchBonus($user);
                if ($result) {
                    $count++;
                }
            } catch (\Exception $e) {
                Log::warning('Weekly bonus failed for user ' . $user->id, [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Get available activities for user (with eligibility status)
     *
     * @return array<string, array{available: bool, reason: string|null, info: array, next_available: string|null}>
     */
    public function getAvailableActivities(User $user): array
    {
        $available = [];

        foreach (CommunityActivity::getAllTypes() as $type) {
            $canSubmit = true;
            $reason = null;
            $nextAvailable = null;

            switch ($type) {
                case CommunityActivity::TYPE_WEEKLY_MATCHES:
                    $startOfWeek = Carbon::now()->startOfWeek();
                    $alreadyAwarded = CommunityActivity::where('user_id', $user->id)
                        ->where('activity_type', $type)
                        ->where('created_at', '>=', $startOfWeek)
                        ->exists();
                    if ($alreadyAwarded) {
                        $canSubmit = false;
                        $reason = 'Already earned this week';
                        $nextAvailable = Carbon::now()->endOfWeek()->addSecond()->format('Y-m-d');
                    }
                    break;

                case CommunityActivity::TYPE_MONTHLY_CHALLENGE:
                    $startOfMonth = Carbon::now()->startOfMonth();
                    $alreadyAwarded = CommunityActivity::where('user_id', $user->id)
                        ->where('activity_type', $type)
                        ->where('created_at', '>=', $startOfMonth)
                        ->exists();
                    if ($alreadyAwarded) {
                        $canSubmit = false;
                        $reason = 'Already completed this month';
                        $nextAvailable = Carbon::now()->endOfMonth()->addSecond()->format('Y-m-d');
                    }
                    break;
            }

            $available[$type] = [
                'available' => $canSubmit,
                'reason' => $reason,
                'info' => CommunityActivity::getActivityInfo($type),
                'next_available' => $nextAvailable,
            ];
        }

        return $available;
    }
}
