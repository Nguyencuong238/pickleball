<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Kỹ thuật cơ bản',
                'slug' => 'ky-thuat-co-ban',
                'description' => 'Hướng dẫn những kỹ thuật cơ bản nhất của pickleball dành cho người mới bắt đầu',
                'icon' => 'fas fa-book',
            ],
            [
                'name' => 'Chiến lược thi đấu',
                'slug' => 'chien-luoc-thi-dau',
                'description' => 'Chiến lược và tactics nâng cao để giành chiến thắng trong các trận đấu',
                'icon' => 'fas fa-chess',
            ],
            [
                'name' => 'Tập luyện thể lực',
                'slug' => 'tap-luyen-the-luc',
                'description' => 'Các bài tập thể lực và sức bền để cải thiện hiệu suất trong pickleball',
                'icon' => 'fas fa-dumbbell',
            ],
            [
                'name' => 'Luật chơi',
                'slug' => 'luat-choi',
                'description' => 'Hướng dẫn đầy đủ các luật chơi pickleball quốc tế',
                'icon' => 'fas fa-scroll',
            ],
            [
                'name' => 'Tư vấn sức khỏe',
                'slug' => 'tu-van-suc-khoe',
                'description' => 'Tư vấn về sức khỏe, chấn thương và phục hồi',
                'icon' => 'fas fa-heart',
            ],
            [
                'name' => 'Phát triển kỹ năng',
                'slug' => 'phat-trien-ky-nang',
                'description' => 'Phát triển các kỹ năng nâng cao trong pickleball',
                'icon' => 'fas fa-chart-line',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('✓ Tạo ' . count($categories) . ' danh mục khóa học thành công!');
    }
}
