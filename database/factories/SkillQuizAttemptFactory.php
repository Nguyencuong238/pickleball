<?php

namespace Database\Factories;

use App\Models\SkillQuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SkillQuizAttempt>
 */
class SkillQuizAttemptFactory extends Factory
{
    protected $model = SkillQuizAttempt::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'user_id' => User::factory(),
            'started_at' => now()->subMinutes(10),
            'completed_at' => null,
            'duration_seconds' => null,
            'status' => SkillQuizAttempt::STATUS_IN_PROGRESS,
            'domain_scores' => null,
            'quiz_percent' => null,
            'calculated_elo' => null,
            'final_elo' => null,
            'flags' => [],
            'is_provisional' => true,
        ];
    }

    /**
     * Indicate that the attempt is completed.
     */
    public function completed(): self
    {
        return $this->state(fn () => [
            'status' => SkillQuizAttempt::STATUS_COMPLETED,
            'completed_at' => now(),
            'duration_seconds' => 540,
            'domain_scores' => [
                'rules' => 70,
                'consistency' => 65,
                'serve_return' => 60,
                'dink_net' => 55,
                'reset_defense' => 50,
                'tactics' => 60,
            ],
            'quiz_percent' => 60,
            'calculated_elo' => 1120,
            'final_elo' => 1120,
        ]);
    }

    /**
     * Indicate that the attempt is abandoned.
     */
    public function abandoned(): self
    {
        return $this->state(fn () => [
            'status' => SkillQuizAttempt::STATUS_ABANDONED,
            'completed_at' => now(),
            'duration_seconds' => null,
        ]);
    }

    /**
     * Add flags to the attempt.
     */
    public function withFlags(array $flags): self
    {
        return $this->state(fn () => [
            'flags' => $flags,
        ]);
    }

    /**
     * Create a completed attempt with TOO_FAST flag.
     */
    public function tooFast(): self
    {
        return $this->completed()->state(fn () => [
            'duration_seconds' => 120,
            'flags' => [
                [
                    'type' => 'TOO_FAST',
                    'message' => 'Hoan thanh qua nhanh - co the doan mo',
                    'adjustment' => -100,
                    'require_review' => true,
                ],
            ],
        ]);
    }

    /**
     * Create a completed attempt with inconsistency flag.
     */
    public function inconsistent(): self
    {
        return $this->completed()->state(fn () => [
            'domain_scores' => [
                'rules' => 50,
                'consistency' => 40,
                'serve_return' => 50,
                'dink_net' => 50,
                'reset_defense' => 50,
                'tactics' => 80,
            ],
            'flags' => [
                [
                    'type' => 'INCONSISTENT',
                    'message' => 'Claim chien thuat cao nhung do on dinh thap',
                    'adjustment' => -80,
                ],
            ],
        ]);
    }
}
