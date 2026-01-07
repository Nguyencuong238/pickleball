# Phase 2: Backend Services

**Date**: 2026-01-02
**Priority**: Critical
**Status**: Completed
**Depends on**: Phase 1

## Context Links
- Spec: Quiz Specification v2.0
- Reference: `app/Services/OprsService.php`
- Reference: `app/Services/EloService.php`

## Overview

Create `SkillQuizService` to handle all quiz business logic: scoring, ELO calculation, cross-validation, time validation, and ELO caps.

## Requirements

### Core Functions
1. Start quiz attempt
2. Record answer
3. Calculate domain scores
4. Calculate weighted total score
5. Convert score to ELO (linear interpolation)
6. Apply cross-validation flags
7. Apply time validation flags
8. Apply ELO caps
9. Save final result
10. Update user ELO

### Scoring Rules
- Each question: 0-3 points (max 18 per domain)
- Domain score: (sum / 18) × 100
- Weights: Rules 10%, Consistency 20%, Serve 15%, Dink 20%, Reset 20%, Tactics 15%
- Total: weighted sum of domain scores

### ELO Conversion
- Linear interpolation: 25% → 850, 95% → 1500
- Formula: `ELO = 850 + (1500-850) × (score%-25) / (95-25)`

### Cross-Validation Rules
| Rule | Condition | Adjustment |
|------|-----------|------------|
| 1 | Tactics ≥70 && Consistency <55 | -80 |
| 2 | Reset ≥70 && Dink <50 | -60 |
| 3 | Serve ≥80 && Rules <60 | -50 |
| 4 | Dink ≥75 && Consistency <50 | -70 |

Max total adjustment from flags: -150

### Time Validation
| Duration | Flag | Adjustment |
|----------|------|------------|
| <3 min | TOO_FAST | -100 |
| >15 min | TOO_SLOW | -50 |
| >20 min | Auto-submit | N/A |

### ELO Caps
| Condition | Max ELO |
|-----------|---------|
| Consistency <50% | 1050 |
| Dink <50% | 1120 |
| Reset <50% | 1200 |
| Tactics <50% | 1280 |

## Related Code Files

### Create
- `app/Services/SkillQuizService.php`

### Modify
- `app/Services/OprsService.php` (add integration method)
- `app/Models/User.php` (add relationships, accessors)

## Implementation Steps

### Step 1: Create SkillQuizService

```php
<?php

namespace App\Services;

use App\Models\SkillDomain;
use App\Models\SkillQuestion;
use App\Models\SkillQuizAnswer;
use App\Models\SkillQuizAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SkillQuizService
{
    // Time constants (seconds)
    public const MIN_TIME_SECONDS = 180;      // 3 min
    public const RECOMMENDED_MIN = 480;       // 8 min
    public const RECOMMENDED_MAX = 600;       // 10 min
    public const MAX_TIME_SECONDS = 900;      // 15 min
    public const TIMEOUT_SECONDS = 1200;      // 20 min

    // ELO constants
    public const MIN_ELO = 850;
    public const MAX_ELO = 1500;
    public const MIN_PERCENT = 25;
    public const MAX_PERCENT = 95;

    // ELO caps by domain
    public const ELO_CAPS = [
        'consistency' => ['threshold' => 50, 'max_elo' => 1050],
        'dink_net' => ['threshold' => 50, 'max_elo' => 1120],
        'reset_defense' => ['threshold' => 50, 'max_elo' => 1200],
        'tactics' => ['threshold' => 50, 'max_elo' => 1280],
    ];

    // Cross-validation rules
    public const CROSS_VALIDATION_RULES = [
        [
            'check' => ['tactics' => ['>=', 70], 'consistency' => ['<', 55]],
            'type' => 'INCONSISTENT',
            'message' => 'Claim chien thuat cao nhung do on dinh thap',
            'adjustment' => -80,
        ],
        [
            'check' => ['reset_defense' => ['>=', 70], 'dink_net' => ['<', 50]],
            'type' => 'INCONSISTENT',
            'message' => 'Claim reset tot nhung net play yeu',
            'adjustment' => -60,
        ],
        [
            'check' => ['serve_return' => ['>=', 80], 'rules' => ['<', 60]],
            'type' => 'INCONSISTENT',
            'message' => 'Claim serve/return manh nhung chua nam luat',
            'adjustment' => -50,
        ],
        [
            'check' => ['dink_net' => ['>=', 75], 'consistency' => ['<', 50]],
            'type' => 'INCONSISTENT',
            'message' => 'Claim dink tot nhung consistency kem',
            'adjustment' => -70,
        ],
    ];

    private OprsService $oprsService;

    public function __construct(OprsService $oprsService)
    {
        $this->oprsService = $oprsService;
    }

    /**
     * Check if user can take skill quiz
     */
    public function canTakeQuiz(User $user): array
    {
        // First time - always allowed
        if ($user->skill_quiz_count === 0) {
            return ['allowed' => true, 'reason' => null];
        }

        // 20+ matches - always allowed
        if ($user->total_ocr_matches >= 20) {
            return ['allowed' => true, 'reason' => 'calibrated'];
        }

        $lastAttempt = SkillQuizAttempt::where('user_id', $user->id)
            ->where('status', SkillQuizAttempt::STATUS_COMPLETED)
            ->latest('completed_at')
            ->first();

        if (!$lastAttempt) {
            return ['allowed' => true, 'reason' => null];
        }

        // Check for flags
        $hasSerious = collect($lastAttempt->flags ?? [])
            ->contains(fn($f) => in_array($f['type'] ?? '', ['TOO_FAST', 'INCONSISTENT']));

        $cooldownDays = $hasSerious ? 7 : 30;
        $nextAllowed = Carbon::parse($lastAttempt->completed_at)->addDays($cooldownDays);

        if (Carbon::now()->lt($nextAllowed)) {
            return [
                'allowed' => false,
                'reason' => 'cooldown',
                'next_allowed_at' => $nextAllowed,
                'days_remaining' => Carbon::now()->diffInDays($nextAllowed),
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Start a new quiz attempt
     */
    public function startQuiz(User $user): SkillQuizAttempt
    {
        // Check for in-progress attempt
        $existing = SkillQuizAttempt::where('user_id', $user->id)
            ->where('status', SkillQuizAttempt::STATUS_IN_PROGRESS)
            ->first();

        if ($existing) {
            // Check if timed out
            $elapsed = Carbon::now()->diffInSeconds($existing->started_at);
            if ($elapsed >= self::TIMEOUT_SECONDS) {
                $this->autoSubmit($existing);
                // Continue to create new attempt
            } else {
                return $existing;
            }
        }

        return SkillQuizAttempt::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'started_at' => Carbon::now(),
            'status' => SkillQuizAttempt::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Get questions for attempt (randomized order)
     */
    public function getQuestions(): array
    {
        $domains = SkillDomain::active()->ordered()->with([
            'questions' => fn($q) => $q->active()->orderBy('order_in_domain')
        ])->get();

        $questions = [];
        foreach ($domains as $domain) {
            foreach ($domain->questions as $question) {
                $questions[] = [
                    'id' => $question->id,
                    'domain_key' => $domain->key,
                    'domain_name' => $domain->name_vi,
                    'question' => $question->question_vi,
                    'order_in_domain' => $question->order_in_domain,
                ];
            }
        }

        // Shuffle questions for anti-cheat
        shuffle($questions);

        return $questions;
    }

    /**
     * Record an answer
     */
    public function recordAnswer(
        SkillQuizAttempt $attempt,
        int $questionId,
        int $answerValue,
        int $timeSpentSeconds = 0
    ): SkillQuizAnswer {
        if ($answerValue < 0 || $answerValue > 3) {
            throw new \InvalidArgumentException('Answer value must be 0-3');
        }

        return SkillQuizAnswer::updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $questionId],
            [
                'answer_value' => $answerValue,
                'answered_at' => Carbon::now(),
                'time_spent_seconds' => $timeSpentSeconds,
            ]
        );
    }

    /**
     * Submit quiz and calculate results
     */
    public function submitQuiz(SkillQuizAttempt $attempt): array
    {
        return DB::transaction(function () use ($attempt) {
            $attempt->refresh();

            if ($attempt->status !== SkillQuizAttempt::STATUS_IN_PROGRESS) {
                throw new \RuntimeException('Quiz already submitted');
            }

            $endTime = Carbon::now();
            $duration = $endTime->diffInSeconds($attempt->started_at);

            // Step 1: Calculate domain scores
            $domainScores = $this->calculateDomainScores($attempt);

            // Step 2: Calculate weighted total
            $quizPercent = $this->calculateWeightedScore($domainScores);

            // Step 3: Convert to base ELO
            $baseElo = $this->quizToElo($quizPercent);

            // Step 4: Apply cross-validation
            $crossFlags = $this->validateCrossLogic($domainScores);
            $elo = $this->applyFlags($baseElo, $crossFlags);

            // Step 5: Apply time validation
            $timeResult = $this->validateQuizTime($duration);
            $elo = $this->applyFlags($elo, $timeResult['flags']);

            // Step 6: Apply ELO caps
            $elo = $this->applyEloCaps($elo, $domainScores);

            // Combine all flags
            $allFlags = array_merge($crossFlags, $timeResult['flags']);

            // Update attempt
            $attempt->update([
                'completed_at' => $endTime,
                'duration_seconds' => $duration,
                'status' => SkillQuizAttempt::STATUS_COMPLETED,
                'domain_scores' => $domainScores,
                'quiz_percent' => $quizPercent,
                'calculated_elo' => $baseElo,
                'final_elo' => $elo,
                'flags' => $allFlags,
                'is_provisional' => true,
            ]);

            // Update user
            $this->updateUserElo($attempt->user, $elo, $attempt);

            return [
                'final_elo' => $elo,
                'quiz_percent' => $quizPercent,
                'domain_scores' => $domainScores,
                'flags' => $allFlags,
                'duration' => $duration,
                'is_provisional' => true,
                'skill_level' => $this->eloToSkillLevel($elo),
            ];
        });
    }

    /**
     * Auto-submit timed out quiz
     */
    public function autoSubmit(SkillQuizAttempt $attempt): void
    {
        if ($attempt->status !== SkillQuizAttempt::STATUS_IN_PROGRESS) {
            return;
        }

        // Mark as abandoned if no answers
        if ($attempt->answers()->count() === 0) {
            $attempt->update([
                'status' => SkillQuizAttempt::STATUS_ABANDONED,
                'completed_at' => Carbon::now(),
            ]);
            return;
        }

        // Submit with current answers
        $this->submitQuiz($attempt);
    }

    /**
     * Calculate domain scores from answers
     */
    public function calculateDomainScores(SkillQuizAttempt $attempt): array
    {
        $answers = $attempt->answers()
            ->with('question.domain')
            ->get();

        $domainTotals = [];
        $domainCounts = [];

        foreach ($answers as $answer) {
            $domainKey = $answer->question->domain->key;
            $domainTotals[$domainKey] = ($domainTotals[$domainKey] ?? 0) + $answer->answer_value;
            $domainCounts[$domainKey] = ($domainCounts[$domainKey] ?? 0) + 1;
        }

        $scores = [];
        $domains = SkillDomain::active()->get()->keyBy('key');

        foreach ($domains as $key => $domain) {
            // Max score = 6 questions × 3 points = 18
            $maxScore = 18;
            $actualScore = $domainTotals[$key] ?? 0;
            $scores[$key] = round(($actualScore / $maxScore) * 100, 2);
        }

        return $scores;
    }

    /**
     * Calculate weighted score from domain scores
     */
    public function calculateWeightedScore(array $domainScores): float
    {
        $domains = SkillDomain::active()->get()->keyBy('key');
        $weightedSum = 0;

        foreach ($domains as $key => $domain) {
            $score = $domainScores[$key] ?? 0;
            $weightedSum += $score * $domain->weight;
        }

        return round($weightedSum, 2);
    }

    /**
     * Convert quiz percentage to ELO using linear interpolation
     */
    public function quizToElo(float $quizPercent): int
    {
        $clamped = max(self::MIN_PERCENT, min(self::MAX_PERCENT, $quizPercent));

        $elo = self::MIN_ELO + (self::MAX_ELO - self::MIN_ELO)
            * ($clamped - self::MIN_PERCENT)
            / (self::MAX_PERCENT - self::MIN_PERCENT);

        return (int) round($elo);
    }

    /**
     * Validate cross-domain logic
     */
    public function validateCrossLogic(array $domainScores): array
    {
        $flags = [];

        foreach (self::CROSS_VALIDATION_RULES as $rule) {
            $allConditionsMet = true;

            foreach ($rule['check'] as $domain => $condition) {
                [$operator, $value] = $condition;
                $score = $domainScores[$domain] ?? 0;

                $conditionMet = match ($operator) {
                    '>=' => $score >= $value,
                    '<' => $score < $value,
                    '>' => $score > $value,
                    '<=' => $score <= $value,
                    default => false,
                };

                if (!$conditionMet) {
                    $allConditionsMet = false;
                    break;
                }
            }

            if ($allConditionsMet) {
                $flags[] = [
                    'type' => $rule['type'],
                    'message' => $rule['message'],
                    'adjustment' => $rule['adjustment'],
                ];
            }
        }

        return $flags;
    }

    /**
     * Validate quiz completion time
     */
    public function validateQuizTime(int $durationSeconds): array
    {
        $flags = [];

        if ($durationSeconds < self::MIN_TIME_SECONDS) {
            $flags[] = [
                'type' => 'TOO_FAST',
                'message' => 'Hoan thanh qua nhanh - co the doan mo',
                'adjustment' => -100,
                'require_review' => true,
            ];
        }

        if ($durationSeconds > self::MAX_TIME_SECONDS) {
            $flags[] = [
                'type' => 'TOO_SLOW',
                'message' => 'Hoan thanh qua cham - co the tra cuu',
                'adjustment' => -50,
                'require_review' => false,
            ];
        }

        return ['duration' => $durationSeconds, 'flags' => $flags];
    }

    /**
     * Apply flag adjustments to ELO
     */
    public function applyFlags(int $baseElo, array $flags): int
    {
        $totalAdjustment = 0;

        foreach ($flags as $flag) {
            $totalAdjustment += $flag['adjustment'] ?? 0;
        }

        // Max reduction from flags: 150
        $totalAdjustment = max(-150, $totalAdjustment);

        return $baseElo + $totalAdjustment;
    }

    /**
     * Apply ELO caps based on domain scores
     */
    public function applyEloCaps(int $elo, array $domainScores): int
    {
        $cappedElo = $elo;

        foreach (self::ELO_CAPS as $domain => $cap) {
            $score = $domainScores[$domain] ?? 0;
            if ($score < $cap['threshold']) {
                $cappedElo = min($cappedElo, $cap['max_elo']);
            }
        }

        return $cappedElo;
    }

    /**
     * Map ELO to skill level string
     */
    public function eloToSkillLevel(int $elo): string
    {
        return match (true) {
            $elo < 800 => '2.0 - 2.5',
            $elo < 900 => '2.5',
            $elo < 1000 => '2.8 - 3.0',
            $elo < 1100 => '3.2 - 3.5',
            $elo < 1200 => '3.8 - 4.0',
            $elo < 1300 => '4.3 - 4.5',
            $elo < 1400 => '4.8 - 5.0',
            $elo < 1500 => '5.3 - 5.5',
            $elo < 1600 => '5.8 - 6.0',
            default => '6.0+',
        };
    }

    /**
     * Update user ELO after quiz
     */
    private function updateUserElo(User $user, int $newElo, SkillQuizAttempt $attempt): void
    {
        $user->update([
            'elo_rating' => $newElo,
            'last_skill_quiz_at' => $attempt->completed_at,
            'skill_quiz_count' => $user->skill_quiz_count + 1,
            'elo_is_provisional' => true,
        ]);

        // Update Elo rank
        $user->updateEloRank();

        // Trigger OPRS recalculation
        $this->oprsService->recalculateAfterMatch($user);
    }

    /**
     * Get quiz result summary
     */
    public function getResult(string $attemptId): ?array
    {
        $attempt = SkillQuizAttempt::with(['answers.question.domain', 'user'])
            ->find($attemptId);

        if (!$attempt || $attempt->status !== SkillQuizAttempt::STATUS_COMPLETED) {
            return null;
        }

        return [
            'attempt_id' => $attempt->id,
            'user_id' => $attempt->user_id,
            'started_at' => $attempt->started_at,
            'completed_at' => $attempt->completed_at,
            'duration_seconds' => $attempt->duration_seconds,
            'domain_scores' => $attempt->domain_scores,
            'quiz_percent' => $attempt->quiz_percent,
            'calculated_elo' => $attempt->calculated_elo,
            'final_elo' => $attempt->final_elo,
            'skill_level' => $this->eloToSkillLevel($attempt->final_elo),
            'flags' => $attempt->flags,
            'is_provisional' => $attempt->is_provisional,
            'recommendations' => $this->getRecommendations($attempt->domain_scores),
        ];
    }

    /**
     * Get improvement recommendations based on scores
     */
    public function getRecommendations(array $domainScores): array
    {
        $recommendations = [];
        $domains = SkillDomain::active()->get()->keyBy('key');

        foreach ($domainScores as $key => $score) {
            if ($score < 50) {
                $domain = $domains[$key] ?? null;
                if ($domain) {
                    $recommendations[] = [
                        'domain' => $domain->name_vi,
                        'score' => $score,
                        'priority' => 'high',
                        'message' => "Can tap trung cai thien {$domain->name_vi}",
                    ];
                }
            } elseif ($score < 70) {
                $domain = $domains[$key] ?? null;
                if ($domain) {
                    $recommendations[] = [
                        'domain' => $domain->name_vi,
                        'score' => $score,
                        'priority' => 'medium',
                        'message' => "Co the cai thien them {$domain->name_vi}",
                    ];
                }
            }
        }

        // Sort by score ascending (lowest first)
        usort($recommendations, fn($a, $b) => $a['score'] <=> $b['score']);

        return array_slice($recommendations, 0, 3);
    }
}
```

### Step 2: Update User Model

Add to `app/Models/User.php`:

```php
// Add to $fillable
'last_skill_quiz_at',
'skill_quiz_count',
'elo_is_provisional',

// Add to $casts
'last_skill_quiz_at' => 'datetime',
'elo_is_provisional' => 'boolean',

// Add relationship
public function skillQuizAttempts(): HasMany
{
    return $this->hasMany(SkillQuizAttempt::class);
}

public function latestSkillQuizAttempt(): HasOne
{
    return $this->hasOne(SkillQuizAttempt::class)
        ->where('status', SkillQuizAttempt::STATUS_COMPLETED)
        ->latest('completed_at');
}
```

### Step 3: Register Service

Add to `AppServiceProvider`:

```php
public function register(): void
{
    $this->app->singleton(SkillQuizService::class, function ($app) {
        return new SkillQuizService($app->make(OprsService::class));
    });
}
```

## Todo List

- [x] Create SkillQuizService class
- [x] Implement canTakeQuiz method
- [x] Implement startQuiz method
- [x] Implement getQuestions method
- [x] Implement recordAnswer method
- [x] Implement calculateDomainScores method
- [x] Implement calculateWeightedScore method
- [x] Implement quizToElo method
- [x] Implement validateCrossLogic method
- [x] Implement validateQuizTime method
- [x] Implement applyFlags method
- [x] Implement applyEloCaps method
- [x] Implement submitQuiz method
- [x] Implement updateUserElo method
- [x] Implement getResult method
- [x] Implement getRecommendations method
- [x] Update User model with new fields/relationships
- [x] Register service in AppServiceProvider
- [ ] Write unit tests for scoring logic (deferred to Phase 6)

## Success Criteria

- [x] Domain scores calculate correctly (0-100 scale)
- [x] Weighted total matches expected formula
- [x] ELO interpolation produces correct values
- [x] Cross-validation catches inconsistencies
- [x] Time flags applied correctly
- [x] ELO caps working as specified
- [x] User ELO updated after quiz
- [x] OPRS recalculates after ELO change

## Risk Assessment

| Risk | Mitigation |
|------|------------|
| Race condition on submit | DB transaction |
| Invalid answer values | Validation in recordAnswer |
| Negative ELO | Min ELO clamp |

## Next Steps

After Phase 2:
- Phase 3: API Endpoints
