<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClubActivity>
 */
class ClubActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'type' => 'one_off',
            'created_by' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'activity_date' => now()->addDays(3),
            'location' => fake()->address(),
            'max_participants' => 10,
            'status' => 'upcoming',
            'auto_approve' => true,
        ];
    }

    public function recurring(int $dayOfWeek = 1): static
    {
        return $this->state([
            'type' => 'recurring',
            'recurrence_day' => $dayOfWeek,
            'parent_activity_id' => null,
        ]);
    }

    public function recurringChild(int $parentId): static
    {
        return $this->state([
            'type' => 'recurring',
            'parent_activity_id' => $parentId,
        ]);
    }

    public function competition(string $format = 'round_robin'): static
    {
        return $this->state([
            'type' => 'competition',
            'competition_config' => [
                'format' => $format,
                'points_for_win' => 3,
                'points_for_loss' => 0,
            ],
        ]);
    }

    public function withSkillRange(float $min, float $max): static
    {
        return $this->state([
            'min_skill_level' => $min,
            'max_skill_level' => $max,
        ]);
    }

    public function full(): static
    {
        return $this->state([
            'max_participants' => 2,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => 'cancelled',
        ]);
    }
}
