<?php

namespace Database\Factories;

use App\Models\ClubActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClubCompetitionTeam>
 */
class ClubCompetitionTeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'club_activity_id' => ClubActivity::factory()->competition(),
            'name' => 'Team ' . fake()->lastName(),
            'captain_user_id' => User::factory(),
            'status' => 'active',
        ];
    }
}
