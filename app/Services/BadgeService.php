<?php

namespace App\Services;

use App\Models\EloHistory;
use App\Models\OcrMatch;
use App\Models\User;
use App\Models\UserBadge;

class BadgeService
{
    /**
     * Check and award badges after match completion
     */
    public function checkBadgesAfterMatch(User $user, OcrMatch $match, bool $won): void
    {
        // Refresh user to get updated stats
        $user->refresh();

        // First win badge
        if ($won && $user->ocr_wins === 1) {
            $this->awardBadgeIfNotExists($user, UserBadge::BADGE_FIRST_WIN);
        }

        // Win streak badges
        if ($won) {
            $this->checkStreakBadges($user);
        }

        // Match count badges
        $this->checkMatchCountBadges($user);

        // Rank badges
        $this->checkRankBadges($user);
    }

    /**
     * Check win streak badges
     */
    private function checkStreakBadges(User $user): void
    {
        $streak = $this->getCurrentWinStreak($user);

        if ($streak >= 10) {
            $this->awardBadgeIfNotExists($user, UserBadge::BADGE_STREAK_10, ['streak' => $streak]);
        }
        if ($streak >= 5) {
            $this->awardBadgeIfNotExists($user, UserBadge::BADGE_STREAK_5, ['streak' => $streak]);
        }
        if ($streak >= 3) {
            $this->awardBadgeIfNotExists($user, UserBadge::BADGE_STREAK_3, ['streak' => $streak]);
        }
    }

    /**
     * Get current win streak from Elo history
     */
    private function getCurrentWinStreak(User $user): int
    {
        $histories = $user->eloHistories()
            ->orderBy('created_at', 'desc')
            ->take(20) // Check last 20 matches max
            ->get();

        $streak = 0;
        foreach ($histories as $history) {
            if ($history->change_reason === EloHistory::REASON_MATCH_WIN) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Check match count badges
     */
    private function checkMatchCountBadges(User $user): void
    {
        $total = $user->total_ocr_matches;

        if ($total >= 100) {
            $this->awardBadgeIfNotExists($user, UserBadge::BADGE_MATCHES_100, ['matches' => $total]);
        }
        if ($total >= 50) {
            $this->awardBadgeIfNotExists($user, UserBadge::BADGE_MATCHES_50, ['matches' => $total]);
        }
        if ($total >= 10) {
            $this->awardBadgeIfNotExists($user, UserBadge::BADGE_MATCHES_10, ['matches' => $total]);
        }
    }

    /**
     * Check rank badges
     */
    private function checkRankBadges(User $user): void
    {
        $rank = $user->elo_rank;
        $elo = $user->elo_rating;

        $rankBadges = [
            'Grandmaster' => UserBadge::BADGE_RANK_GRANDMASTER,
            'Master' => UserBadge::BADGE_RANK_MASTER,
            'Diamond' => UserBadge::BADGE_RANK_DIAMOND,
            'Platinum' => UserBadge::BADGE_RANK_PLATINUM,
            'Gold' => UserBadge::BADGE_RANK_GOLD,
            'Silver' => UserBadge::BADGE_RANK_SILVER,
        ];

        foreach ($rankBadges as $rankName => $badgeType) {
            if ($rank === $rankName) {
                $this->awardBadgeIfNotExists($user, $badgeType, ['rank' => $rankName, 'elo' => $elo]);
            }
        }
    }

    /**
     * Award a badge to user if they don't already have it
     *
     * @param User $user
     * @param string $badgeType
     * @param array<string, mixed> $metadata
     * @return UserBadge|null
     */
    public function awardBadgeIfNotExists(User $user, string $badgeType, array $metadata = []): ?UserBadge
    {
        if ($user->hasBadge($badgeType)) {
            return null;
        }

        return UserBadge::create([
            'user_id' => $user->id,
            'badge_type' => $badgeType,
            'earned_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Award a badge (force create)
     *
     * @param User $user
     * @param string $badgeType
     * @param array<string, mixed> $metadata
     * @return UserBadge
     */
    public function awardBadge(User $user, string $badgeType, array $metadata = []): UserBadge
    {
        return UserBadge::create([
            'user_id' => $user->id,
            'badge_type' => $badgeType,
            'earned_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get all available badge types with info
     *
     * @return array<string, array{name: string, description: string, icon: string}>
     */
    public function getAllBadgeTypesWithInfo(): array
    {
        $types = UserBadge::getAllBadgeTypes();
        $result = [];

        foreach ($types as $type) {
            $result[$type] = UserBadge::getBadgeInfo($type);
        }

        return $result;
    }

    /**
     * Get user's earned badges with full info
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection<int, UserBadge>
     */
    public function getUserBadges(User $user)
    {
        return $user->badges()->orderBy('earned_at', 'desc')->get();
    }

    /**
     * Get user's missing badges
     *
     * @param User $user
     * @return array<string, array{name: string, description: string, icon: string}>
     */
    public function getMissingBadges(User $user): array
    {
        $allTypes = UserBadge::getAllBadgeTypes();
        $earnedTypes = $user->badges()->pluck('badge_type')->toArray();
        $missing = array_diff($allTypes, $earnedTypes);

        $result = [];
        foreach ($missing as $type) {
            $result[$type] = UserBadge::getBadgeInfo($type);
        }

        return $result;
    }

    /**
     * Revoke a badge from user
     *
     * @param User $user
     * @param string $badgeType
     * @return bool
     */
    public function revokeBadge(User $user, string $badgeType): bool
    {
        return $user->badges()->where('badge_type', $badgeType)->delete() > 0;
    }

    /**
     * Get badge progress for user
     *
     * @param User $user
     * @return array<string, array{current: int, target: int, percent: float}>
     */
    public function getBadgeProgress(User $user): array
    {
        $progress = [];

        // Streak progress (current streak out of next milestone)
        $currentStreak = $this->getCurrentWinStreak($user);
        $nextStreakMilestone = match (true) {
            $currentStreak < 3 => 3,
            $currentStreak < 5 => 5,
            $currentStreak < 10 => 10,
            default => null,
        };
        if ($nextStreakMilestone) {
            $progress['streak'] = [
                'current' => $currentStreak,
                'target' => $nextStreakMilestone,
                'percent' => round(($currentStreak / $nextStreakMilestone) * 100),
            ];
        }

        // Match count progress
        $total = $user->total_ocr_matches;
        $nextMatchMilestone = match (true) {
            $total < 10 => 10,
            $total < 50 => 50,
            $total < 100 => 100,
            default => null,
        };
        if ($nextMatchMilestone) {
            $progress['matches'] = [
                'current' => $total,
                'target' => $nextMatchMilestone,
                'percent' => round(($total / $nextMatchMilestone) * 100),
            ];
        }

        // Rank progress
        $ranks = User::getEloRanks();
        $currentRank = $user->elo_rank;
        $currentElo = $user->elo_rating;

        // Find next rank
        $rankKeys = array_keys($ranks);
        $currentIndex = array_search($currentRank, $rankKeys);
        if ($currentIndex !== false && $currentIndex < count($rankKeys) - 1) {
            $nextRank = $rankKeys[$currentIndex + 1];
            $nextRankMin = $ranks[$nextRank]['min'];
            $currentRankMin = $ranks[$currentRank]['min'];

            $rangeSize = $nextRankMin - $currentRankMin;
            $progress['rank'] = [
                'current_rank' => $currentRank,
                'current_elo' => $currentElo,
                'next_rank' => $nextRank,
                'next_rank_min' => $nextRankMin,
                'points_needed' => $nextRankMin - $currentElo,
                'percent' => $rangeSize > 0 ? round((($currentElo - $currentRankMin) / $rangeSize) * 100) : 0,
            ];
        }

        return $progress;
    }

    /**
     * Get all users with specific badge
     *
     * @param string $badgeType
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function getUsersWithBadge(string $badgeType)
    {
        return User::whereHas('badges', fn($q) => $q->where('badge_type', $badgeType))
            ->with(['badges' => fn($q) => $q->where('badge_type', $badgeType)])
            ->get();
    }

    /**
     * Get all badge types
     *
     * @return array<string>
     */
    public function getAllBadgeTypes(): array
    {
        return UserBadge::getAllBadgeTypes();
    }
}
