<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\CourtPricing;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourtPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all courts
        $courts = Court::all();

        foreach ($courts as $court) {
            // Morning time (5:00 - 12:00) - Giá bình thường
            CourtPricing::create([
                'court_id' => $court->id,
                'start_time' => '05:00:00',
                'end_time' => '12:00:00',
                'price_per_hour' => 150000,
                'days_of_week' => [0, 1, 2, 3, 4, 5, 6], // All days
                'is_active' => true,
                'description' => 'Buổi sáng (5:00 - 12:00)',
            ]);

            // Afternoon time (12:00 - 18:00) - Giá cao hơn
            CourtPricing::create([
                'court_id' => $court->id,
                'start_time' => '12:00:00',
                'end_time' => '18:00:00',
                'price_per_hour' => 200000,
                'days_of_week' => [0, 1, 2, 3, 4, 5, 6],
                'is_active' => true,
                'description' => 'Buổi chiều (12:00 - 18:00)',
            ]);

            // Evening time (18:00 - 23:00) - Giá cao nhất
            CourtPricing::create([
                'court_id' => $court->id,
                'start_time' => '18:00:00',
                'end_time' => '23:00:00',
                'price_per_hour' => 300000,
                'days_of_week' => [0, 1, 2, 3, 4, 5, 6],
                'is_active' => true,
                'description' => 'Buổi tối (18:00 - 23:00)',
            ]);
        }
    }
}
