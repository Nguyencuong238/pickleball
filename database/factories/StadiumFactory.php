<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stadium>
 */
class StadiumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Pickleball',
            'description' => fake()->paragraph(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->email(),
            'website' => fake()->url(),
            'courts_count' => fake()->numberBetween(1, 15),
            'image' => null,
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'opening_hours' => '05:00 - 23:00',
            'amenities' => json_encode([
                '🚿 Phòng tắm',
                '🅿️ Bãi đỗ xe',
                '☕ Canteen',
            ]),
            'status' => 'active',
            'is_featured' => fake()->boolean(30), // 30% là nổi bật
            'is_premium' => fake()->boolean(20), // 20% là premium
        ];
    }

    /**
     * Indicate that the stadium is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the stadium is premium.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => true,
        ]);
    }

    /**
     * Indicate that the stadium is both featured and premium.
     */
    public function featuredPremium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'is_premium' => true,
        ]);
    }

    /**
     * Indicate that the stadium is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
