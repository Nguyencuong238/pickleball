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
use RuntimeException;
use InvalidArgumentException;

class SkillQuizService
{
    // Time constants (seconds)
    public const MIN_TIME_SECONDS = 180;      // 3 min
    public const RECOMMENDED_MIN = 480;       // 8 min
    public const RECOMMENDED_MAX = 600;       // 10 min
    public const MAX_TIME_SECONDS = 900;      // 15 min
    public const TIMEOUT_SECONDS = 1800;      // 30 min

    // ELO constants
    public const MIN_ELO = 850;
    public const MAX_ELO = 1500;
    public const MIN_PERCENT = 25;
    public const MAX_PERCENT = 95;

    // Max answers per question
    public const MAX_ANSWER_VALUE = 3;
    public const QUESTIONS_PER_DOMAIN = 6;
    public const MAX_SCORE_PER_DOMAIN = 18; // 6 questions * 3 points

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
            'message' => 'Claim chiến thuật cao nhưng độ ổn định thấp',
            'adjustment' => -80,
        ],
        [
            'check' => ['reset_defense' => ['>=', 70], 'dink_net' => ['<', 50]],
            'type' => 'INCONSISTENT',
            'message' => 'Claim reset tốt nhưng net play yếu',
            'adjustment' => -60,
        ],
        [
            'check' => ['serve_return' => ['>=', 80], 'rules' => ['<', 60]],
            'type' => 'INCONSISTENT',
            'message' => 'Claim serve/return mạnh nhưng chưa nắm luật',
            'adjustment' => -50,
        ],
        [
            'check' => ['dink_net' => ['>=', 75], 'consistency' => ['<', 50]],
            'type' => 'INCONSISTENT',
            'message' => 'Claim dink tốt nhưng consistency kém',
            'adjustment' => -70,
        ],
    ];

    // Max total adjustment from flags
    public const MAX_FLAG_ADJUSTMENT = -150;

    private OprsService $oprsService;

    public function __construct(OprsService $oprsService)
    {
        $this->oprsService = $oprsService;
    }

    /**
     * Check if user can take skill quiz
     *
     * @return array{allowed: bool, reason: string|null, next_allowed_at?: Carbon, days_remaining?: int}
     */
    public function canTakeQuiz(User $user): array
    {
        // First time - always allowed
        if ($user->skill_quiz_count === 0) {
            return ['allowed' => true, 'reason' => null];
        }

        // 20+ matches - always allowed (calibrated)
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

        // Check for serious flags
        $hasSerious = collect($lastAttempt->flags ?? [])
            ->contains(fn($f) => in_array($f['type'] ?? '', ['TOO_FAST', 'INCONSISTENT']));

        $cooldownDays = $hasSerious ? 7 : 30;
        $nextAllowed = Carbon::parse($lastAttempt->completed_at)->addDays($cooldownDays);

        if (Carbon::now()->lt($nextAllowed)) {
            return [
                'allowed' => false,
                'reason' => 'cooldown',
                'next_allowed_at' => $nextAllowed,
                'days_remaining' => (int) Carbon::now()->diffInDays($nextAllowed, false),
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
     *
     * @return array<int, array{id: int, domain_key: string, domain_name: string, question: string, order_in_domain: int}>
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
     * Get questions grouped by domain (for sequential display)
     *
     * @return array<string, array{domain: array{key: string, name: string, name_vi: string, order: int}, questions: array}>
     */
    public function getQuestionsGroupedByDomain(): array
    {
        $domains = SkillDomain::active()->ordered()->with([
            'questions' => fn($q) => $q->active()->orderBy('order_in_domain')
        ])->get();

        $grouped = [];
        foreach ($domains as $domain) {
            $questions = [];
            foreach ($domain->questions as $question) {
                $questions[] = [
                    'id' => $question->id,
                    'question' => $question->question_vi,
                    'anchor_level' => $question->anchor_level,
                    'order_in_domain' => $question->order_in_domain,
                ];
            }

            $grouped[$domain->key] = [
                'domain' => [
                    'key' => $domain->key,
                    'name' => $domain->name,
                    'name_vi' => $domain->name_vi,
                    'order' => $domain->order,
                ],
                'questions' => $questions,
            ];
        }

        return $grouped;
    }

    /**
     * Record an answer
     *
     * @throws InvalidArgumentException
     */
    public function recordAnswer(
        SkillQuizAttempt $attempt,
        int $questionId,
        int $answerValue,
        int $timeSpentSeconds = 0
    ): SkillQuizAnswer {
        if ($answerValue < 0 || $answerValue > self::MAX_ANSWER_VALUE) {
            throw new InvalidArgumentException('Answer value must be 0-3');
        }

        if ($attempt->status !== SkillQuizAttempt::STATUS_IN_PROGRESS) {
            throw new RuntimeException('Cannot record answer on completed quiz');
        }

        // Verify question exists
        $question = SkillQuestion::find($questionId);
        if (!$question || !$question->is_active) {
            throw new InvalidArgumentException('Invalid question ID');
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
     * Record multiple answers at once (batch submit)
     *
     * @param array<int, array{question_id: int, answer_value: int, time_spent_seconds?: int}> $answers
     * @return int Number of answers recorded
     */
    public function recordAnswers(SkillQuizAttempt $attempt, array $answers): int
    {
        $count = 0;
        foreach ($answers as $answer) {
            $this->recordAnswer(
                $attempt,
                $answer['question_id'],
                $answer['answer_value'],
                $answer['time_spent_seconds'] ?? 0
            );
            $count++;
        }
        return $count;
    }

    /**
     * Submit quiz and calculate results
     *
     * @return array{final_elo: int, quiz_percent: float, domain_scores: array, flags: array, duration: int, is_provisional: bool, skill_level: string}
     * @throws RuntimeException
     */
    public function submitQuiz(SkillQuizAttempt $attempt): array
    {
        return DB::transaction(function () use ($attempt) {
            $attempt->refresh();

            if ($attempt->status !== SkillQuizAttempt::STATUS_IN_PROGRESS) {
                throw new RuntimeException('Quiz already submitted');
            }

            $endTime = Carbon::now();
            $duration = (int) $endTime->diffInSeconds($attempt->started_at);

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
     *
     * @return array<string, float>
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
            // Max score = 6 questions x 3 points = 18
            $actualScore = $domainTotals[$key] ?? 0;
            $scores[$key] = round(($actualScore / self::MAX_SCORE_PER_DOMAIN) * 100, 2);
        }

        return $scores;
    }

    /**
     * Calculate weighted score from domain scores
     */
    public function calculateWeightedScore(array $domainScores): float
    {
        $domains = SkillDomain::active()->get()->keyBy('key');
        $weightedSum = 0.0;

        foreach ($domains as $key => $domain) {
            $score = $domainScores[$key] ?? 0;
            $weightedSum += $score * (float) $domain->weight;
        }

        return round($weightedSum, 2);
    }

    /**
     * Convert quiz percentage to ELO using linear interpolation
     * Formula: ELO = 850 + (1500-850) * (score%-25) / (95-25)
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
     *
     * @return array<int, array{type: string, message: string, adjustment: int}>
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
     *
     * @return array{duration: int, flags: array}
     */
    public function validateQuizTime(int $durationSeconds): array
    {
        $flags = [];

        if ($durationSeconds < self::MIN_TIME_SECONDS) {
            $flags[] = [
                'type' => 'TOO_FAST',
                'message' => 'Hoàn thành quá nhanh - có thể đoán mò',
                'adjustment' => -100,
                'require_review' => true,
            ];
        }

        if ($durationSeconds > self::MAX_TIME_SECONDS) {
            $flags[] = [
                'type' => 'TOO_SLOW',
                'message' => 'Hoàn thành quá chậm - có thể tra cứu',
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

        // Max reduction from flags
        $totalAdjustment = max(self::MAX_FLAG_ADJUSTMENT, $totalAdjustment);

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

        // Trigger OPRS recalculation (updates opr_level)
        $this->oprsService->recalculateAfterSkillQuiz($user, $attempt->id);
    }

    /**
     * Get quiz result summary
     *
     * @return array|null
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
     *
     * @return array<int, array{domain: string, score: float, priority: string, message: string}>
     */
    public function getRecommendations(array $domainScores): array
    {
        $recommendations = [];
        $domains = SkillDomain::active()->get()->keyBy('key');

        foreach ($domainScores as $key => $score) {
            $domain = $domains[$key] ?? null;
            if (!$domain) {
                continue;
            }

            if ($score < 50) {
                $recommendations[] = [
                    'domain' => $domain->name_vi,
                    'domain_key' => $key,
                    'score' => $score,
                    'priority' => 'high',
                    'message' => "Cần tập trung cải thiện {$domain->name_vi}",
                ];
            } elseif ($score < 70) {
                $recommendations[] = [
                    'domain' => $domain->name_vi,
                    'domain_key' => $key,
                    'score' => $score,
                    'priority' => 'medium',
                    'message' => "Có thể cải thiện thêm {$domain->name_vi}",
                ];
            }
        }

        // Sort by score ascending (lowest first)
        usort($recommendations, fn($a, $b) => $a['score'] <=> $b['score']);

        return array_slice($recommendations, 0, 3);
    }

    /**
     * Get quiz attempt progress
     *
     * @return array{total_questions: int, answered_questions: int, progress_percent: float, elapsed_seconds: int, remaining_seconds: int}
     */
    public function getAttemptProgress(SkillQuizAttempt $attempt): array
    {
        $totalQuestions = SkillQuestion::active()->count();
        $answeredQuestions = $attempt->answers()->count();
        $elapsed = (int) Carbon::now()->diffInSeconds($attempt->started_at);
        $remaining = max(0, self::TIMEOUT_SECONDS - $elapsed);

        return [
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'progress_percent' => round(($answeredQuestions / $totalQuestions) * 100, 1),
            'elapsed_seconds' => $elapsed,
            'remaining_seconds' => $remaining,
        ];
    }

    /**
     * Get user's quiz history
     *
     * @return array<int, array>
     */
    public function getUserHistory(User $user, int $limit = 10): array
    {
        return SkillQuizAttempt::where('user_id', $user->id)
            ->where('status', SkillQuizAttempt::STATUS_COMPLETED)
            ->orderByDesc('completed_at')
            ->limit($limit)
            ->get()
            ->map(fn($attempt) => [
                'attempt_id' => $attempt->id,
                'completed_at' => $attempt->completed_at,
                'duration_seconds' => $attempt->duration_seconds,
                'quiz_percent' => $attempt->quiz_percent,
                'final_elo' => $attempt->final_elo,
                'skill_level' => $this->eloToSkillLevel($attempt->final_elo),
                'has_flags' => !empty($attempt->flags),
            ])
            ->toArray();
    }
}
