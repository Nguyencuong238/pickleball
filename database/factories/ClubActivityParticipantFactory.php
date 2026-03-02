<?php

namespace Database\Factories;

use App\Models\ClubActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClubActivityParticipant>
 */
class ClubActivityParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'club_activity_id' => ClubActivity::factory(),
            'user_id' => User::factory(),
            'status' => 'confirmed',
            'responded_at' => now(),
        ];
    }

    public function waitlisted(int $position = 1): static
    {
        return $this->state([
            'status' => 'waitlisted',
            'waitlist_position' => $position,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => 'cancelled',
            'waitlist_position' => null,
        ]);
    }
}
