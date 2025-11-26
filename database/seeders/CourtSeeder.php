<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\Stadium;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourtSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a stadium first
        $stadium = Stadium::create([
            'name' => 'Sân Pickleball Rạch Chiếc',
            'address' => '123 Rạch Chiếc, Tân Bình, TP.HCM',
            'description' => 'Sân pickleball chuyên nghiệp',
            'phone' => '0901234567',
        ]);

        Court::create([
            'court_name' => 'Sân Pickleball 1',
            'stadium_id' => $stadium->id,
            'court_number' => '1',
            'court_type' => 'indoor',
            'surface_type' => 'Acrylic',
            'status' => 'available',
            'capacity' => 4,
            'rental_price' => 150000,
            'is_active' => true,
        ]);

        Court::create([
            'court_name' => 'Sân Pickleball 2',
            'stadium_id' => $stadium->id,
            'court_number' => '2',
            'court_type' => 'indoor',
            'surface_type' => 'Acrylic',
            'status' => 'available',
            'capacity' => 4,
            'rental_price' => 150000,
            'is_active' => true,
        ]);

        Court::create([
            'court_name' => 'Sân Pickleball 3',
            'stadium_id' => $stadium->id,
            'court_number' => '3',
            'court_type' => 'indoor',
            'surface_type' => 'Acrylic',
            'status' => 'available',
            'capacity' => 4,
            'rental_price' => 200000,
            'is_active' => true,
        ]);
    }
}
