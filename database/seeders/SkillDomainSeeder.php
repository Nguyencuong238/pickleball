<?php

namespace Database\Seeders;

use App\Models\SkillDomain;
use Illuminate\Database\Seeder;

class SkillDomainSeeder extends Seeder
{
    /**
     * Seed skill domains for the skill assessment quiz.
     * 6 domains with specific weights totaling 1.0
     */
    public function run(): void
    {
        $domains = [
            [
                'key' => 'rules',
                'name' => 'Rules & Positioning',
                'name_vi' => 'Luật & Vị trí',
                'description' => 'Understanding of pickleball rules, scoring, positioning, and game fundamentals.',
                'weight' => 0.10,
                'anchor_min' => 2.0,
                'anchor_max' => 4.0,
                'order' => 1,
            ],
            [
                'key' => 'consistency',
                'name' => 'Consistency',
                'name_vi' => 'Độ ổn định',
                'description' => 'Ability to maintain consistent shots and reduce unforced errors throughout the game.',
                'weight' => 0.20,
                'anchor_min' => 2.5,
                'anchor_max' => 4.5,
                'order' => 2,
            ],
            [
                'key' => 'serve_return',
                'name' => 'Serve & Return',
                'name_vi' => 'Giao bóng & Trả giao',
                'description' => 'Quality of serve placement and depth of return shots to gain tactical advantage.',
                'weight' => 0.15,
                'anchor_min' => 3.0,
                'anchor_max' => 5.0,
                'order' => 3,
            ],
            [
                'key' => 'dink_net',
                'name' => 'Dink & Net Play',
                'name_vi' => 'Dink & Chơi lưới',
                'description' => 'Soft game skills including dink rallies, volley control, and net presence.',
                'weight' => 0.20,
                'anchor_min' => 3.5,
                'anchor_max' => 5.5,
                'order' => 4,
            ],
            [
                'key' => 'reset_defense',
                'name' => 'Reset & Defense',
                'name_vi' => 'Reset & Phòng thủ',
                'description' => 'Defensive skills including resets, blocks, and transitioning from defense to neutral.',
                'weight' => 0.20,
                'anchor_min' => 4.0,
                'anchor_max' => 5.5,
                'order' => 5,
            ],
            [
                'key' => 'tactics',
                'name' => 'Tactics & Partner Play',
                'name_vi' => 'Chiến thuật & Phối hợp',
                'description' => 'Strategic thinking, partner coordination, and in-game adjustments.',
                'weight' => 0.15,
                'anchor_min' => 4.0,
                'anchor_max' => 6.0,
                'order' => 6,
            ],
        ];

        foreach ($domains as $domain) {
            SkillDomain::updateOrCreate(
                ['key' => $domain['key']],
                $domain
            );
        }

        $this->command->info('[PICKLEBALL] Seeded 6 skill domains for assessment quiz');
    }
}
