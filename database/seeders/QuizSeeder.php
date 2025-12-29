<?php

namespace Database\Seeders;

use App\Models\Quiz;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quizzes = [
            [
                'title' => 'Kích thước sân Pickleball',
                'description' => 'Kiến thức cơ bản về kích thước sân pickleball',
                'question' => 'Kích thước sân pickleball chuẩn là bao nhiêu?',
                'options' => [
                    'a' => '20 x 44 feet',
                    'b' => '20 x 50 feet',
                    'c' => '25 x 55 feet',
                    'd' => '30 x 60 feet',
                ],
                'correct_answer' => 'a',
                'explanation' => 'Sân pickleball chuẩn quốc tế có kích thước 20 x 44 feet (tương đương 6,1 x 13,4 mét).',
                'category' => 'Luật chơi',
                'difficulty' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Điểm trong Pickleball',
                'description' => 'Quy tắc điểm số trong pickleball',
                'question' => 'Trong pickleball, chỉ đội nào được tính điểm?',
                'options' => [
                    'a' => 'Đội bên trái',
                    'b' => 'Đội bên phải',
                    'c' => 'Chỉ đội phục vụ',
                    'd' => 'Cả hai đội',
                ],
                'correct_answer' => 'c',
                'explanation' => 'Trong pickleball, chỉ đội phục vụ (serving team) mới được tính điểm khi đối phương mắc lỗi.',
                'category' => 'Luật chơi',
                'difficulty' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Vùng Kitchen',
                'description' => 'Vùng không volley trong pickleball',
                'question' => 'Vùng kitchen trong pickleball là gì?',
                'options' => [
                    'a' => 'Vùng ngoài sân',
                    'b' => 'Vùng 7 feet từ lưới ở mỗi bên',
                    'c' => 'Vùng phục vụ',
                    'd' => 'Vùng gần đường base line',
                ],
                'correct_answer' => 'b',
                'explanation' => 'Kitchen (hoặc no-volley zone) là vùng 7 feet từ lưới ở mỗi bên sân, nơi không được phép volley.',
                'category' => 'Luật chơi',
                'difficulty' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Độ cao lưới Pickleball',
                'description' => 'Độ cao của lưới trong pickleball',
                'question' => 'Lưới pickleball cao bao nhiêu ở giữa sân?',
                'options' => [
                    'a' => '30 inch',
                    'b' => '34 inch',
                    'c' => '36 inch',
                    'd' => '40 inch',
                ],
                'correct_answer' => 'b',
                'explanation' => 'Lưới pickleball cao 34 inch (86,4 cm) ở giữa sân và 36 inch (91,4 cm) ở hai đầu.',
                'category' => 'Luật chơi',
                'difficulty' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Quả bóng Pickleball',
                'description' => 'Thông tin về quả bóng pickleball',
                'question' => 'Quả bóng pickleball được làm từ chất liệu nào?',
                'options' => [
                    'a' => 'Cao su',
                    'b' => 'Nhựa polymer',
                    'c' => 'Da lông cừu',
                    'd' => 'Tơ lụa',
                ],
                'correct_answer' => 'b',
                'explanation' => 'Quả bóng pickleball được làm từ nhựa polymer (plastic), nhẹ hơn quả bóng tennis.',
                'category' => 'Dụng cụ',
                'difficulty' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Luật Serve',
                'description' => 'Quy tắc phục vụ trong pickleball',
                'question' => 'Serve trong pickleball phải được thực hiện từ độ cao nào?',
                'options' => [
                    'a' => 'Ở mức lưng',
                    'b' => 'Dưới mức hông',
                    'c' => 'Ở mức vai',
                    'd' => 'Ở độ cao bất kỳ',
                ],
                'correct_answer' => 'b',
                'explanation' => 'Serve phải được thực hiện từ dưới mức hông, với cơ thể quay 45 độ so với lưới.',
                'category' => 'Luật chơi',
                'difficulty' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Quy tắc hai lần bounce',
                'description' => 'Luật bounce sau khi serve',
                'question' => 'Quy tắc "hai lần bounce" trong pickleball là gì?',
                'options' => [
                    'a' => 'Bóng phải bounce 2 lần trên sân',
                    'b' => 'Serve phải bounce, nhận phải bounce trước khi volley',
                    'c' => 'Bóng phải hit 2 lần',
                    'd' => 'Người chơi phải bounce cầu 2 lần',
                ],
                'correct_answer' => 'b',
                'explanation' => 'Quy tắc hai lần bounce: Serve phải bounce trên sân đối phương, và đối phương phải để nó bounce trước khi volley.',
                'category' => 'Luật chơi',
                'difficulty' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Kích thước vợt Pickleball',
                'description' => 'Thông tin về kích thước vợt',
                'question' => 'Kích thước tối đa của vợt pickleball là bao nhiêu?',
                'options' => [
                    'a' => '15 x 8 inch',
                    'b' => '17 x 10 inch',
                    'c' => '18 x 11 inch',
                    'd' => '20 x 12 inch',
                ],
                'correct_answer' => 'b',
                'explanation' => 'Kích thước tối đa của vợt pickleball là 17 x 10 inch (43 x 25 cm), bao gồm cả handle.',
                'category' => 'Dụng cụ',
                'difficulty' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Điểm thắng',
                'description' => 'Cách tính điểm thắng trong pickleball',
                'question' => 'Để thắng một trận đơn trong pickleball, bạn cần bao nhiêu điểm?',
                'options' => [
                    'a' => '10 điểm',
                    'b' => '11 điểm',
                    'c' => '15 điểm',
                    'd' => '21 điểm',
                ],
                'correct_answer' => 'b',
                'explanation' => 'Để thắng một trận đơn hoặc đôi, bạn cần 11 điểm với ít nhất 2 điểm chênh lệch.',
                'category' => 'Luật chơi',
                'difficulty' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Non-Volley Zone',
                'description' => 'Vùng không được volley',
                'question' => 'Hành động nào là vi phạm trong vùng kitchen?',
                'options' => [
                    'a' => 'Đứng trong vùng kitchen',
                    'b' => 'Volley bóng trong vùng kitchen',
                    'c' => 'Tiếp cận lưới',
                    'd' => 'Cả A, B, C đều sai',
                ],
                'correct_answer' => 'd',
                'explanation' => 'Các hành động vi phạm kitchen bao gồm: volley bóng, cho bóng bounce trong vùng kitchen và sau đó volley, hoặc để cơ thể/vợt chạm kitchen.',
                'category' => 'Luật chơi',
                'difficulty' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($quizzes as $quiz) {
            Quiz::create($quiz);
        }
    }
}
