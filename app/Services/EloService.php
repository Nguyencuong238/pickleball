<?php

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

    // Minimum Elo rating
    private const MIN_ELO = 100;

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
     *
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

            // Track total change for match record (use challenger's change)
            if ($isChallengerTeam && $user->id === $challenger->id) {
                $totalChange = abs($change);
            }

            $this->applyEloChange($user, $match, $change, $won);
        }

        // Update match with final Elo values
        $challenger->refresh();
        $opponent->refresh();

        $match->update([
            'elo_challenger_after' => $this->getTeamElo(
                $challenger,
                $challengerPartner ? User::find($match->challenger_partner_id) : null
            ),
            'elo_opponent_after' => $this->getTeamElo(
                $opponent,
                $opponentPartner ? User::find($match->opponent_partner_id) : null
            ),
            'elo_change' => $totalChange,
        ]);
    }

    /**
     * Apply Elo change to single user
     */
    private function applyEloChange(User $user, OcrMatch $match, int $change, bool $won): void
    {
        $eloBefore = $user->elo_rating;
        $eloAfter = max(self::MIN_ELO, $eloBefore + $change);

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
                if (!$user) {
                    continue;
                }

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
        $eloAfter = max(self::MIN_ELO, $eloBefore + $change);

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
     * Get win probability for display (percentage)
     */
    public function getWinProbability(User $user, User $opponent): float
    {
        return round($this->calculateExpectedScore($user->elo_rating, $opponent->elo_rating) * 100, 1);
    }

    /**
     * Estimate rating change before match
     */
    public function estimateRatingChange(User $user, User $opponent): array
    {
        $kFactor = $this->getKFactor($user);

        return [
            'win' => $this->calculateRatingChange($user->elo_rating, $opponent->elo_rating, true, $kFactor),
            'loss' => $this->calculateRatingChange($user->elo_rating, $opponent->elo_rating, false, $kFactor),
        ];
    }
}
