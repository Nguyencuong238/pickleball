<?php

namespace App\Services\Tournament;

use App\Models\ActivityLog;
use App\Models\MatchModel;
use App\Models\Tournament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TournamentMatchService
{
    public function __construct(
        private TournamentDrawService $drawService,
        private MatchCreationHelper $creationHelper
    ) {}

    public function createMatchesForGroups(Tournament $tournament, int $categoryId, iterable $groups, int $bestOf = 3): void
    {
        $isDouble = $this->drawService->isDoubleCategory($categoryId, $tournament);
        $this->creationHelper->createMatchesForGroups($tournament, $categoryId, $groups, $isDouble, $bestOf);
    }

    public function updateMatchScore(MatchModel $match, array $validated): void
    {
        DB::transaction(function () use ($match, $validated) {
            $actionType = $validated['action'] ?? 'update';

            if ($actionType === 'end_set') {
                $this->handleEndSet($match, $validated);
            } elseif ($actionType === 'end_match') {
                $this->handleEndMatch($match, $validated);
            } else {
                $this->handleRegularUpdate($match, $validated);
            }
        });
    }

    public function handleEndSet(MatchModel $match, array $validated): void
    {
        $setScores = $match->set_scores ? json_decode($match->set_scores, true) : [];
        if (!is_array($setScores)) {
            $setScores = [];
        }

        $setScores[] = [
            'athlete1_score' => $validated['athlete1_score'],
            'athlete2_score' => $validated['athlete2_score'],
            'completed_at' => now()->toDateTimeString()
        ];

        $match->update(['set_scores' => json_encode($setScores), 'status' => 'in_progress']);
        $match->athlete1_score = 0;
        $match->athlete2_score = 0;
        $match->save();

        ActivityLog::log(
            "Set " . count($setScores) . " của trận đấu kết thúc với tỉ số {$validated['athlete1_score']}-{$validated['athlete2_score']}",
            'Match',
            $match->id
        );
    }

    public function handleEndMatch(MatchModel $match, array $validated): void
    {
        $match->load('tournament');

        if ($validated['athlete1_score'] > 0 || $validated['athlete2_score'] > 0) {
            $setScores = $match->set_scores ? json_decode($match->set_scores, true) : [];
            if (!is_array($setScores)) {
                $setScores = [];
            }
            $setScores[] = [
                'athlete1_score' => $validated['athlete1_score'],
                'athlete2_score' => $validated['athlete2_score'],
                'completed_at' => now()->toDateTimeString()
            ];
            $match->set_scores = json_encode($setScores);
        }

        $match->athlete1_score = $validated['athlete1_score'];
        $match->athlete2_score = $validated['athlete2_score'];
        $match->status = 'completed';
        $match->final_score = $validated['final_score'];

        [$setsWon1, $setsWon2] = $this->parseSetsFromFinalScore($validated['final_score'] ?? '');

        if ($setsWon1 > $setsWon2) {
            $match->winner_id = $match->athlete1_id;
        } elseif ($setsWon2 > $setsWon1) {
            $match->winner_id = $match->athlete2_id;
        } else {
            $match->winner_id = null;
        }

        if (!$match->actual_end_time) {
            $match->actual_end_time = now();
        }

        $match->save();

        $winnerName = $match->winner_id
            ? ($match->winner_id === $match->athlete1_id ? $match->athlete1->athlete_name : $match->athlete2->athlete_name)
            : 'Hòa';

        ActivityLog::log(
            "Trận đấu kết thúc với kết quả {$validated['final_score']} - Người thắng: {$winnerName}",
            'Match',
            $match->id
        );
    }

    public function handleRegularUpdate(MatchModel $match, array $validated): void
    {
        $wasCompleted = $match->status === 'completed';
        $isNowCompleted = ($validated['status'] ?? $match->status) === 'completed';
        $justCompleted = !$wasCompleted && $isNowCompleted;

        $match->update([
            'athlete1_score' => $validated['athlete1_score'],
            'athlete2_score' => $validated['athlete2_score'],
            'status' => $validated['status'] ?? $match->status,
            'final_score' => $validated['final_score'] ?? $match->final_score,
        ]);

        if ($justCompleted && $match->athlete1_id && $match->athlete2_id) {
            if ($validated['athlete1_score'] > $validated['athlete2_score']) {
                $match->winner_id = $match->athlete1_id;
            } elseif ($validated['athlete2_score'] > $validated['athlete1_score']) {
                $match->winner_id = $match->athlete2_id;
            } else {
                $match->winner_id = null;
            }

            if (!$match->actual_end_time) {
                $match->actual_end_time = now();
            }

            $match->save();
        }
    }

    public function parseSetsFromFinalScore(string $finalScore): array
    {
        $setsWon1 = 0;
        $setsWon2 = 0;

        if ($finalScore !== '') {
            foreach (explode(',', $finalScore) as $set) {
                $scores = explode('-', trim($set));
                if (count($scores) === 2) {
                    $s1 = (int)trim($scores[0]);
                    $s2 = (int)trim($scores[1]);
                    if ($s1 > $s2) {
                        $setsWon1++;
                    } elseif ($s2 > $s1) {
                        $setsWon2++;
                    }
                }
            }
        }

        return [$setsWon1, $setsWon2];
    }
}
