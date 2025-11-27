<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Video;
use App\Models\Category;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            return;
        }

        $videos = [
            [
                'name' => 'Hướng dẫn các cú đánh cơ bản trong Pickleball',
                'description' => 'Học các cú đánh cơ bản như forehand, backhand, volley và serve trong Pickleball. Video này phù hợp cho những người mới bắt đầu.',
                'video_link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'category_id' => $categories->random()->id,
            ],
            [
                'name' => 'Kỹ thuật Serve trong Pickleball',
                'description' => 'Video chi tiết về cách thực hiện cú serve chính xác và hiệu quả trong Pickleball. Bao gồm nhiều loại serve khác nhau.',
                'video_link' => 'https://www.youtube.com/watch?v=9bZkp7q19f0',
                'category_id' => $categories->random()->id,
            ],
            [
                'name' => 'Chiến thuật Volley trong Pickleball',
                'description' => 'Tìm hiểu về các kỹ thuật volley nâng cao để cải thiện trò chơi gần lưới. Bao gồm các tình huống thực tế trong trận đấu.',
                'video_link' => 'https://www.youtube.com/watch?v=JZqdUF2mkQc',
                'category_id' => $categories->random()->id,
            ],
            [
                'name' => 'Cải thiện độ chính xác cơn gục (Dink) trong Pickleball',
                'description' => 'Hướng dẫn chi tiết về cách thực hiện cơn gục chính xác. Đây là một trong những kỹ năng quan trọng nhất trong Pickleball.',
                'video_link' => 'https://www.youtube.com/watch?v=2Tww2K4ibvE',
                'category_id' => $categories->random()->id,
            ],
            [
                'name' => 'Chiến thuật chơi đôi trong Pickleball',
                'description' => 'Tìm hiểu các chiến thuật để chơi đôi hiệu quả. Bao gồm vị trí, giao tiếp và sự phối hợp giữa các cầu thủ.',
                'video_link' => 'https://www.youtube.com/watch?v=nfWlot6GZaPo',
                'category_id' => $categories->random()->id,
            ],
            [
                'name' => 'Bài tập nâng cao kỹ năng Pickleball',
                'description' => 'Một series các bài tập giúp cải thiện tốc độ, sức mạnh và sự linh hoạt trong Pickleball. Phù hợp cho những người chơi trung cấp.',
                'video_link' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
                'category_id' => $categories->random()->id,
            ],
            [
                'name' => 'Luật chơi Pickleball - Những điều bạn cần biết',
                'description' => 'Giải thích chi tiết các quy tắc và luật chơi trong Pickleball. Bao gồm scoring, faults, và những tình huống đặc biệt.',
                'video_link' => 'https://www.youtube.com/watch?v=7CvURxQBXw4',
                'category_id' => $categories->random()->id,
            ],
            [
                'name' => 'Lựa chọn vợt Pickleball phù hợp với bạn',
                'description' => 'Hướng dẫn cách chọn vợt Pickleball phù hợp với mức độ kỹ năng và phong cách chơi của bạn. So sánh các loại vợt khác nhau.',
                'video_link' => 'https://www.youtube.com/watch?v=EwlvM36CH8U',
                'category_id' => $categories->random()->id,
            ],
            [
                'name' => 'Tập luyện thể chất cho Pickleball',
                'description' => 'Các bài tập thể dục để chuẩn bị cho Pickleball. Tăng cường sức khỏe, linh hoạt và phòng chống chấn thương.',
                'video_link' => 'https://www.youtube.com/watch?v=oI_X10bqQJM',
                'category_id' => $categories->random()->id,
            ],
            [
                'name' => 'Phân tích trận đấu Pickleball chuyên nghiệp',
                'description' => 'Xem và phân tích các trận đấu Pickleball chuyên nghiệp. Học hỏi từ những cầu thủ hàng đầu thế giới.',
                'video_link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'category_id' => $categories->random()->id,
            ],
        ];

        foreach ($videos as $video) {
            Video::create($video);
        }
    }
}
