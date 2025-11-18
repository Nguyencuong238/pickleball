<?php

namespace Database\Seeders;

use App\Models\Tournament;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddGalleryToTournamentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $galleryData = [
            1 => [ // HCM Pickleball Open 2025
                ['url' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=500&h=400&fit=crop', 'title' => 'Thi đấu vòng 1'],
                ['url' => 'https://images.unsplash.com/photo-1552674605-5defe6aa44bb?w=500&h=400&fit=crop', 'title' => 'Cảnh quang chung'],
                ['url' => 'https://images.unsplash.com/photo-1565895917775-c271d75b63ce?w=500&h=400&fit=crop', 'title' => 'Vợt Pickleball'],
            ],
            2 => [ // Hà Nội Pickleball Masters
                ['url' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=500&h=400&fit=crop', 'title' => 'Bán kết'],
                ['url' => 'https://images.unsplash.com/photo-1552674605-5defe6aa44bb?w=500&h=400&fit=crop', 'title' => 'Chung kết'],
            ],
            3 => [ // Đà Nẵng Beach Pickleball
                ['url' => 'https://images.unsplash.com/photo-1565895917775-c271d75b63ce?w=500&h=400&fit=crop', 'title' => 'Bãi biển đẹp'],
                ['url' => 'https://images.unsplash.com/photo-1542438859-0f67e3a2e6f4?w=500&h=400&fit=crop', 'title' => 'Cảnh sân thi đấu'],
                ['url' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=500&h=400&fit=crop', 'title' => 'VĐV thi đấu'],
            ],
            5 => [ // Vũng Tàu Seaside Open
                ['url' => 'https://images.unsplash.com/photo-1552674605-5defe6aa44bb?w=500&h=400&fit=crop', 'title' => 'Resort sang trọng'],
            ],
            6 => [ // Vietnam National Championship
                ['url' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=500&h=400&fit=crop', 'title' => 'Sân chính'],
                ['url' => 'https://images.unsplash.com/photo-1565895917775-c271d75b63ce?w=500&h=400&fit=crop', 'title' => 'Vòng loại'],
                ['url' => 'https://images.unsplash.com/photo-1552674605-5defe6aa44bb?w=500&h=400&fit=crop', 'title' => 'Bán kết quốc gia'],
            ],
        ];

        foreach ($galleryData as $tournamentId => $gallery) {
            $tournament = Tournament::find($tournamentId);
            if ($tournament) {
                $tournament->update(['gallery' => json_encode($gallery)]);
                $this->command->info("Added gallery to tournament: {$tournament->name}");
            }
        }

        $this->command->info('Gallery data added to tournaments!');
    }
}
