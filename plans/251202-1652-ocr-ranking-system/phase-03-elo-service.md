# Phase 3: Elo Service

## Context Links

- [Parent Plan](./plan.md)
- [Phase 2: Core Models](./phase-02-core-models.md)
- [Code Standards](../../docs/code-standards.md)

## Overview

- **Date**: 2025-12-02
- **Priority**: High
- **Implementation Status**: Pending
- **Review Status**: Pending
- **Dependencies**: Phase 2 (Core Models)

Implement Elo rating calculation service following standard Elo algorithm with configurable K-factor.

## Key Insights

1. Standard Elo formula: `R' = R + K * (S - E)`
   - R = current rating
   - K = K-factor (how much ratings can change)
   - S = actual score (1 for win, 0 for loss, 0.5 for draw)
   - E = expected score based on rating difference
2. K-factor varies by experience level (higher for new players)
3. For doubles, average team Elo used
4. Record history for every change

## Requirements

### Functional

- Calculate expected win probability
- Calculate rating change after match
- Apply changes to all participants
- Record history entries
- Support both singles and doubles

### Non-Functional

- Transactional updates (all or nothing)
- Precision: round to nearest integer
- Max change capped at K-factor

## Architecture

### Elo Calculation Flow

```
Match Confirmed
      |
      v
Calculate Expected Scores (E)
      |
      v
Calculate Rating Changes
      |
      v
Begin Transaction
      |
      +-- Update User Elo Ratings
      +-- Update User Stats (wins/losses)
      +-- Create EloHistory Records
      +-- Update Match Elo Fields
      +-- Check Badge Eligibility
      |
      v
Commit Transaction
```

### K-Factor Strategy

| Matches Played | K-Factor | Rationale |
|----------------|----------|-----------|
| 0-30 | 40 | New players adjust quickly |
| 31-100 | 24 | Moderate adjustment |
| 100+ | 16 | Established players change slowly |

## Related Code Files

### Files to Create

| File | Action | Description |
|------|--------|-------------|
| `app/Services/EloService.php` | Create | Core Elo calculation service |
| `app/Services/BadgeService.php` | Create | Badge checking/awarding |

## Implementation Steps

### Step 1: Create EloService

```php
<?php
// app/Services/EloService.php

namespace App\Services;

use App\Models\EloHistory;
use App\Models\OcrMatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EloService
{
    // K-factor thresholds
    private const K_NEW_PLAYER = 40;      // 0-30 matches
    private const K_INTERMEDIATE = 24;    // 31-100 matches
    private const K_EXPERIENCED = 16;     // 100+ matches

    // Thresholds for K-factor selection
    private const NEW_PLAYER_THRESHOLD = 30;
    private const INTERMEDIATE_THRESHOLD = 100;

    /**
     * Calculate expected score based on rating difference
     * E = 1 / (1 + 10^((Rb - Ra) / 400))
     */
    public function calculateExpectedScore(int $ratingA, int $ratingB): float
    {
        return 1 / (1 + pow(10, ($ratingB - $ratingA) / 400));
    }

    /**
     * Get K-factor based on player experience
     */
    public function getKFactor(User $user): int
    {
        $matchesPlayed = $user->total_ocr_matches;

        if ($matchesPlayed <= self::NEW_PLAYER_THRESHOLD) {
            return self::K_NEW_PLAYER;
        }

        if ($matchesPlayed <= self::INTERMEDIATE_THRESHOLD) {
            return self::K_INTERMEDIATE;
        }

        return self::K_EXPERIENCED;
    }

    /**
     * Calculate rating change
     * Change = K * (S - E)
     * S = 1 for win, 0 for loss
     */
    public function calculateRatingChange(int $rating, int $opponentRating, bool $won, int $kFactor): int
    {
        $expectedScore = $this->calculateExpectedScore($rating, $opponentRating);
        $actualScore = $won ? 1.0 : 0.0;

        return (int) round($kFactor * ($actualScore - $expectedScore));
    }

    /**
     * Get team average Elo for doubles
     */
    public function getTeamElo(User $player1, ?User $player2): int
    {
        if ($player2 === null) {
            return $player1->elo_rating;
        }

        return (int) round(($player1->elo_rating + $player2->elo_rating) / 2);
    }

    /**
     * Process match result and update all Elo ratings
     * @throws InvalidArgumentException
     */
    public function processMatchResult(OcrMatch $match): void
    {
        if ($match->status !== OcrMatch::STATUS_CONFIRMED) {
            throw new InvalidArgumentException('Match must be confirmed to process Elo');
        }

        if ($match->winner_team === null) {
            throw new InvalidArgumentException('Winner team not set');
        }

        $challengerWon = $match->winner_team === 'challenger';

        DB::transaction(function () use ($match, $challengerWon) {
            $this->processEloForMatch($match, $challengerWon);
        });
    }

    /**
     * Core Elo processing logic (runs inside transaction)
     */
    private function processEloForMatch(OcrMatch $match, bool $challengerWon): void
    {
        // Load participants
        $challenger = User::find($match->challenger_id);
        $opponent = User::find($match->opponent_id);
        $challengerPartner = $match->challenger_partner_id ? User::find($match->challenger_partner_id) : null;
        $opponentPartner = $match->opponent_partner_id ? User::find($match->opponent_partner_id) : null;

        // Calculate team Elo
        $challengerTeamElo = $this->getTeamElo($challenger, $challengerPartner);
        $opponentTeamElo = $this->getTeamElo($opponent, $opponentPartner);

        // Store before values
        $match->update([
            'elo_challenger_before' => $challengerTeamElo,
            'elo_opponent_before' => $opponentTeamElo,
        ]);

        // Calculate changes for each participant
        $participants = array_filter([
            ['user' => $challenger, 'isChallengerTeam' => true],
            ['user' => $challengerPartner, 'isChallengerTeam' => true],
            ['user' => $opponent, 'isChallengerTeam' => false],
            ['user' => $opponentPartner, 'isChallengerTeam' => false],
        ], fn($p) => $p['user'] !== null);

        $totalChange = 0;

        foreach ($participants as $participant) {
            $user = $participant['user'];
            $isChallengerTeam = $participant['isChallengerTeam'];
            $won = $challengerWon ? $isChallengerTeam : !$isChallengerTeam;

            $opponentTeamEloForCalc = $isChallengerTeam ? $opponentTeamElo : $challengerTeamElo;
            $kFactor = $this->getKFactor($user);
            $change = $this->calculateRatingChange(
                $user->elo_rating,
                $opponentTeamEloForCalc,
                $won,
                $kFactor
            );

            // Track total change for match record
            if ($isChallengerTeam && $user->id === $challenger->id) {
                $totalChange = abs($change);
            }

            $this->applyEloChange($user, $match, $change, $won);
        }

        // Update match with final Elo values
        $challenger->refresh();
        $opponent->refresh();

        $match->update([
            'elo_challenger_after' => $this->getTeamElo($challenger, $challengerPartner ? User::find($match->challenger_partner_id) : null),
            'elo_opponent_after' => $this->getTeamElo($opponent, $opponentPartner ? User::find($match->opponent_partner_id) : null),
            'elo_change' => $totalChange,
        ]);
    }

    /**
     * Apply Elo change to single user
     */
    private function applyEloChange(User $user, OcrMatch $match, int $change, bool $won): void
    {
        $eloBefore = $user->elo_rating;
        $eloAfter = max(100, $eloBefore + $change); // Minimum 100 Elo

        // Update user stats
        $user->update([
            'elo_rating' => $eloAfter,
            'total_ocr_matches' => $user->total_ocr_matches + 1,
            'ocr_wins' => $won ? $user->ocr_wins + 1 : $user->ocr_wins,
            'ocr_losses' => !$won ? $user->ocr_losses + 1 : $user->ocr_losses,
        ]);

        // Update rank
        $user->updateEloRank();

        // Record history
        EloHistory::create([
            'user_id' => $user->id,
            'ocr_match_id' => $match->id,
            'elo_before' => $eloBefore,
            'elo_after' => $eloAfter,
            'change_amount' => $change,
            'change_reason' => $won ? EloHistory::REASON_MATCH_WIN : EloHistory::REASON_MATCH_LOSS,
        ]);
    }

    /**
     * Rollback Elo changes for a match (for disputed/cancelled)
     */
    public function rollbackMatchElo(OcrMatch $match): void
    {
        DB::transaction(function () use ($match) {
            $histories = $match->eloHistories;

            foreach ($histories as $history) {
                $user = $history->user;
                if (!$user) continue;

                // Reverse the change
                $user->update([
                    'elo_rating' => $history->elo_before,
                    'total_ocr_matches' => max(0, $user->total_ocr_matches - 1),
                    'ocr_wins' => $history->change_reason === EloHistory::REASON_MATCH_WIN
                        ? max(0, $user->ocr_wins - 1)
                        : $user->ocr_wins,
                    'ocr_losses' => $history->change_reason === EloHistory::REASON_MATCH_LOSS
                        ? max(0, $user->ocr_losses - 1)
                        : $user->ocr_losses,
                ]);

                $user->updateEloRank();
            }

            // Delete history records
            $match->eloHistories()->delete();

            // Clear match Elo fields
            $match->update([
                'elo_challenger_after' => null,
                'elo_opponent_after' => null,
                'elo_change' => null,
            ]);
        });
    }

    /**
     * Admin adjustment (manual Elo change)
     */
    public function adminAdjustment(User $user, int $change, string $reason = 'Admin adjustment'): void
    {
        $eloBefore = $user->elo_rating;
        $eloAfter = max(100, $eloBefore + $change);

        $user->update(['elo_rating' => $eloAfter]);
        $user->updateEloRank();

        EloHistory::create([
            'user_id' => $user->id,
            'ocr_match_id' => null,
            'elo_before' => $eloBefore,
            'elo_after' => $eloAfter,
            'change_amount' => $change,
            'change_reason' => EloHistory::REASON_ADMIN_ADJUSTMENT,
        ]);
    }

    /**
     * Get rating difference for matchmaking display
     */
    public function getRatingDifference(User $user1, User $user2): int
    {
        return abs($user1->elo_rating - $user2->elo_rating);
    }

    /**
     * Get win probability for display
     */
    public function getWinProbability(User $user, User $opponent): float
    {
        return round($this->calculateExpectedScore($user->elo_rating, $opponent->elo_rating) * 100, 1);
    }
}
```

### Step 2: Create BadgeService

```php
<?php
// app/Services/BadgeService.php

namespace App\Services;

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
        // First win badge
        if ($won && $user->ocr_wins === 1) {
            $this->awardBadge($user, UserBadge::BADGE_FIRST_WIN);
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

        if ($streak >= 10 && !$user->hasBadge(UserBadge::BADGE_STREAK_10)) {
            $this->awardBadge($user, UserBadge::BADGE_STREAK_10, ['streak' => $streak]);
        } elseif ($streak >= 5 && !$user->hasBadge(UserBadge::BADGE_STREAK_5)) {
            $this->awardBadge($user, UserBadge::BADGE_STREAK_5, ['streak' => $streak]);
        } elseif ($streak >= 3 && !$user->hasBadge(UserBadge::BADGE_STREAK_3)) {
            $this->awardBadge($user, UserBadge::BADGE_STREAK_3, ['streak' => $streak]);
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
            if ($history->change_reason === 'match_win') {
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

        if ($total >= 100 && !$user->hasBadge(UserBadge::BADGE_MATCHES_100)) {
            $this->awardBadge($user, UserBadge::BADGE_MATCHES_100, ['matches' => $total]);
        } elseif ($total >= 50 && !$user->hasBadge(UserBadge::BADGE_MATCHES_50)) {
            $this->awardBadge($user, UserBadge::BADGE_MATCHES_50, ['matches' => $total]);
        } elseif ($total >= 10 && !$user->hasBadge(UserBadge::BADGE_MATCHES_10)) {
            $this->awardBadge($user, UserBadge::BADGE_MATCHES_10, ['matches' => $total]);
        }
    }

    /**
     * Check rank badges
     */
    private function checkRankBadges(User $user): void
    {
        $rank = $user->elo_rank;

        $rankBadges = [
            'Diamond' => UserBadge::BADGE_RANK_DIAMOND,
            'Platinum' => UserBadge::BADGE_RANK_PLATINUM,
            'Gold' => UserBadge::BADGE_RANK_GOLD,
            'Silver' => UserBadge::BADGE_RANK_SILVER,
        ];

        foreach ($rankBadges as $rankName => $badgeType) {
            if ($rank === $rankName && !$user->hasBadge($badgeType)) {
                $this->awardBadge($user, $badgeType, ['rank' => $rankName, 'elo' => $user->elo_rating]);
                break; // Only award highest applicable rank badge
            }
        }
    }

    /**
     * Award a badge to user
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
     * Get all available badge types
     */
    public function getAllBadgeTypes(): array
    {
        return [
            UserBadge::BADGE_FIRST_WIN,
            UserBadge::BADGE_STREAK_3,
            UserBadge::BADGE_STREAK_5,
            UserBadge::BADGE_STREAK_10,
            UserBadge::BADGE_RANK_SILVER,
            UserBadge::BADGE_RANK_GOLD,
            UserBadge::BADGE_RANK_PLATINUM,
            UserBadge::BADGE_RANK_DIAMOND,
            UserBadge::BADGE_MATCHES_10,
            UserBadge::BADGE_MATCHES_50,
            UserBadge::BADGE_MATCHES_100,
        ];
    }
}
```

## Todo List

- [ ] Create EloService class
- [ ] Create BadgeService class
- [ ] Register services in AppServiceProvider
- [ ] Unit test Elo calculations
- [ ] Test badge awarding logic

## Success Criteria

1. Elo calculation matches standard formula
2. K-factor varies by experience
3. All changes within transaction
4. History recorded for every change
5. Rollback works correctly

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Calculation errors | High | Unit test with known values |
| Transaction failures | High | DB::transaction wraps all |
| Badge spam | Low | Unique constraint per badge type |

## Security Considerations

- Only confirmed matches processed
- Admin adjustment logged
- Minimum Elo (100) prevents negative

## Next Steps

After services complete, proceed to [Phase 4: API Controllers](./phase-04-api-controllers.md)
