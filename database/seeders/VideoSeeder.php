<?php

namespace Database\Seeders;

use App\Models\Video;
use App\Models\Category;
use App\Models\Instructor;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        $instructors = Instructor::all();

        if ($categories->isEmpty() || $instructors->isEmpty()) {
            $this->command->warn('Vui lòng chạy CategorySeeder và InstructorSeeder trước!');
            return;
        }

        $videos = [
            [
                'name' => 'Kỹ thuật cơ bản - Grip (Cách cầm vợt)',
                'description' => 'Hướng dẫn chi tiết cách cầm vợt pickleball đúng cách. Bao gồm các loại grip khác nhau: Eastern, Western, Continental và Continental grip cho forehand và backhand. Phần video này sẽ giúp bạn hiểu rõ tầm quan trọng của grip trong việc kiểm soát vợt và tăng hiệu suất chơi.',
                'video_link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'category_id' => 1,
                'instructor_id' => 1,
                'duration' => '12:45',
                'level' => 'Người mới',
                'views_count' => 1250,
                'rating' => 4.8,
                'rating_count' => 45,
                'image' => 'assets/images/videos/video1.jpg',
                'chapters' => json_encode([
                    ['title' => 'Giới thiệu', 'start_time' => '00:00', 'duration' => '1:30'],
                    ['title' => 'Eastern Grip', 'start_time' => '01:30', 'duration' => '3:45'],
                    ['title' => 'Western Grip', 'start_time' => '05:15', 'duration' => '3:30'],
                    ['title' => 'Continental Grip', 'start_time' => '08:45', 'duration' => '4:00'],
                ]),
            ],
            [
                'name' => 'Từng bước học Forehand Drive',
                'description' => 'Video hướng dẫn chi tiết về forehand drive - một trong những cú đánh quan trọng nhất trong pickleball. Từ từng động tác đến kỹ thuật nâng cao, tất cả được giải thích một cách dễ hiểu.',
                'video_link' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw',
                'category_id' => 1,
                'instructor_id' => 2,
                'duration' => '18:30',
                'level' => 'Trung cấp',
                'views_count' => 2840,
                'rating' => 4.9,
                'rating_count' => 78,
                'image' => 'assets/images/videos/video2.jpg',
                'chapters' => json_encode([
                    ['title' => 'Chuẩn bị', 'start_time' => '00:00', 'duration' => '2:00'],
                    ['title' => 'Stance và Position', 'start_time' => '02:00', 'duration' => '4:30'],
                    ['title' => 'Swing Motion', 'start_time' => '06:30', 'duration' => '5:00'],
                    ['title' => 'Follow Through', 'start_time' => '11:30', 'duration' => '3:00'],
                    ['title' => 'Luyện tập', 'start_time' => '14:30', 'duration' => '4:00'],
                ]),
            ],
            [
                'name' => 'Backhand Slice - Cú đánh cắt ngược',
                'description' => 'Học cách thực hiện backhand slice - một cú đánh quyết định trong các tình huống phòng thủ. Video này sẽ hướng dẫn bạn kỹ thuật, timing, và cách ứng dụng trong trận đấu.',
                'video_link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'category_id' => 1,
                'instructor_id' => 1,
                'duration' => '15:20',
                'level' => 'Trung cấp',
                'views_count' => 1650,
                'rating' => 4.7,
                'rating_count' => 52,
                'image' => 'assets/images/videos/video3.jpg',
                'chapters' => json_encode([
                    ['title' => 'Giới thiệu Slice', 'start_time' => '00:00', 'duration' => '2:00'],
                    ['title' => 'Grip cho Slice', 'start_time' => '02:00', 'duration' => '2:30'],
                    ['title' => 'Kỹ thuật cơ bản', 'start_time' => '04:30', 'duration' => '5:00'],
                    ['title' => 'Các biến thể', 'start_time' => '09:30', 'duration' => '4:00'],
                    ['title' => 'Luyện tập thực tế', 'start_time' => '13:30', 'duration' => '1:50'],
                ]),
            ],
            [
                'name' => 'Chiến lược phòng thủ tại Net',
                'description' => 'Chiến lược quan trọng để bảo vệ net trong pickleball. Học cách đứng vị trí, di chuyển, và đánh bóng hiệu quả tại vị trí net để giành lợi thế.',
                'video_link' => 'https://www.youtube.com/watch?v=ZbZSe6N_BXs',
                'category_id' => 2,
                'instructor_id' => 3,
                'duration' => '20:15',
                'level' => 'Nâng cao',
                'views_count' => 3200,
                'rating' => 4.6,
                'rating_count' => 85,
                'image' => 'assets/images/videos/video4.jpg',
                'chapters' => json_encode([
                    ['title' => 'Vị trí Net đúng', 'start_time' => '00:00', 'duration' => '3:00'],
                    ['title' => 'Chân vợt ở Net', 'start_time' => '03:00', 'duration' => '4:00'],
                    ['title' => 'Volley kỹ thuật', 'start_time' => '07:00', 'duration' => '5:30'],
                    ['title' => 'Phòng chống Dinks', 'start_time' => '12:30', 'duration' => '4:00'],
                    ['title' => 'Drills và Bài tập', 'start_time' => '16:30', 'duration' => '3:45'],
                ]),
            ],
            [
                'name' => 'The Dink Shot - Cú đánh nhẹ quyết định',
                'description' => 'Dink shot là kỹ thuật quan trọng nhất trong pickleball. Video này giải thích chi tiết cách thực hiện dink shot hoàn hảo và cách sử dụng nó trong trận đấu.',
                'video_link' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw',
                'category_id' => 1,
                'instructor_id' => 2,
                'duration' => '22:40',
                'level' => 'Trung cấp',
                'views_count' => 5420,
                'rating' => 5.0,
                'rating_count' => 156,
                'image' => 'assets/images/videos/video5.jpg',
                'chapters' => json_encode([
                    ['title' => 'Dink là gì', 'start_time' => '00:00', 'duration' => '2:30'],
                    ['title' => 'Forehand Dink', 'start_time' => '02:30', 'duration' => '6:00'],
                    ['title' => 'Backhand Dink', 'start_time' => '08:30', 'duration' => '6:00'],
                    ['title' => 'Dink Attacks', 'start_time' => '14:30', 'duration' => '5:30'],
                    ['title' => 'Luyện tập Dink', 'start_time' => '20:00', 'duration' => '2:40'],
                ]),
            ],
            [
                'name' => 'Thể lực và Linh hoạt cho Pickleball',
                'description' => 'Bài hướng dẫn về các bài tập thể lực, linh hoạt và sức bền quan trọng cho các vận động viên pickleball. Cải thiện hiệu suất trên sân.',
                'video_link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'category_id' => 3,
                'instructor_id' => 1,
                'duration' => '28:50',
                'level' => 'Tất cả cấp độ',
                'views_count' => 2150,
                'rating' => 4.8,
                'rating_count' => 68,
                'image' => 'assets/images/videos/video6.jpg',
                'chapters' => json_encode([
                    ['title' => 'Warm up', 'start_time' => '00:00', 'duration' => '5:00'],
                    ['title' => 'Bài tập chân', 'start_time' => '05:00', 'duration' => '8:00'],
                    ['title' => 'Bài tập tay cánh tay', 'start_time' => '13:00', 'duration' => '7:00'],
                    ['title' => 'Linh hoạt', 'start_time' => '20:00', 'duration' => '6:00'],
                    ['title' => 'Cool down', 'start_time' => '26:00', 'duration' => '2:50'],
                ]),
            ],
            [
                'name' => 'Luật chơi Pickleball - Hướng dẫn đầy đủ',
                'description' => 'Hướng dẫn chi tiết tất cả các luật chơi pickleball, từ cơ bản đến nâng cao. Phù hợp cho người mới bắt đầu và những người muốn hiểu rõ hơn về luật.',
                'video_link' => 'https://www.youtube.com/watch?v=ZbZSe6N_BXs',
                'category_id' => 4,
                'instructor_id' => 3,
                'duration' => '31:25',
                'level' => 'Người mới',
                'views_count' => 4820,
                'rating' => 4.9,
                'rating_count' => 124,
                'image' => 'assets/images/videos/video7.jpg',
                'chapters' => json_encode([
                    ['title' => 'Sân chơi và Dụng cụ', 'start_time' => '00:00', 'duration' => '4:00'],
                    ['title' => 'Điểm và Kết quả', 'start_time' => '04:00', 'duration' => '5:30'],
                    ['title' => 'Phục vụ', 'start_time' => '09:30', 'duration' => '6:00'],
                    ['title' => 'Luật cơ bản', 'start_time' => '15:30', 'duration' => '7:00'],
                    ['title' => 'Kitchen Rule (Non-volley zone)', 'start_time' => '22:30', 'duration' => '5:30'],
                    ['title' => 'Luật nâng cao', 'start_time' => '28:00', 'duration' => '3:25'],
                ]),
            ],
            [
                'name' => 'Chiến lược Service (Phục vụ) hiệu quả',
                'description' => 'Tìm hiểu các chiến lược phục vụ khác nhau để có lợi thế từ đầu điểm. Từ serve bình thường đến các serve kỹ thuật cao.',
                'video_link' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw',
                'category_id' => 2,
                'instructor_id' => 2,
                'duration' => '19:30',
                'level' => 'Trung cấp',
                'views_count' => 1980,
                'rating' => 4.7,
                'rating_count' => 54,
                'image' => 'assets/images/videos/video8.jpg',
                'chapters' => json_encode([
                    ['title' => 'Grip và Stance cho Serve', 'start_time' => '00:00', 'duration' => '3:00'],
                    ['title' => 'Underhand Serve', 'start_time' => '03:00', 'duration' => '4:30'],
                    ['title' => 'Power Serve', 'start_time' => '07:30', 'duration' => '5:00'],
                    ['title' => 'Serve Placement', 'start_time' => '12:30', 'duration' => '4:00'],
                    ['title' => 'Serve Strategy', 'start_time' => '16:30', 'duration' => '3:00'],
                ]),
            ],
            [
                'name' => 'Return of Serve - Cách chơi trả phục vụ',
                'description' => 'Chiến lược và kỹ thuật trả phục vụ hiệu quả. Học cách nhận serve mạnh và tạo cơ hội tấn công.',
                'video_link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'category_id' => 2,
                'instructor_id' => 1,
                'duration' => '17:45',
                'level' => 'Trung cấp',
                'views_count' => 1620,
                'rating' => 4.6,
                'rating_count' => 43,
                'image' => 'assets/images/videos/video9.jpg',
                'chapters' => json_encode([
                    ['title' => 'Vị trí và Sẵn sàng', 'start_time' => '00:00', 'duration' => '2:30'],
                    ['title' => 'Đọc Serve', 'start_time' => '02:30', 'duration' => '3:00'],
                    ['title' => 'Deep Return', 'start_time' => '05:30', 'duration' => '4:00'],
                    ['title' => 'Aggressive Return', 'start_time' => '09:30', 'duration' => '4:00'],
                    ['title' => 'Bài tập thực hành', 'start_time' => '13:30', 'duration' => '4:15'],
                ]),
            ],
            [
                'name' => 'Chiến lược Đôi và Doubles Strategy',
                'description' => 'Hướng dẫn chi tiết về chiến lược chơi đôi nam nữ trong pickleball. Giao tiếp, vị trí, và chiến lược tấn công cùng đối tác.',
                'video_link' => 'https://www.youtube.com/watch?v=ZbZSe6N_BXs',
                'category_id' => 2,
                'instructor_id' => 3,
                'duration' => '25:30',
                'level' => 'Nâng cao',
                'views_count' => 2890,
                'rating' => 4.8,
                'rating_count' => 72,
                'image' => 'assets/images/videos/video10.jpg',
                'chapters' => json_encode([
                    ['title' => 'Giao tiếp với đối tác', 'start_time' => '00:00', 'duration' => '3:30'],
                    ['title' => 'Vị trí Doubles', 'start_time' => '03:30', 'duration' => '4:30'],
                    ['title' => 'Serve và Return Doubles', 'start_time' => '08:00', 'duration' => '5:00'],
                    ['title' => 'Tấn công Doubles', 'start_time' => '13:00', 'duration' => '4:00'],
                    ['title' => 'Bắt bóng ở Net Doubles', 'start_time' => '17:00', 'duration' => '4:30'],
                    ['title' => 'Bài tập Doubles', 'start_time' => '21:30', 'duration' => '4:00'],
                ]),
            ],
        ];

        foreach ($videos as $video) {
            Video::create($video);
        }

        $this->command->info('✓ Tạo 10 video thành công!');
    }
}
