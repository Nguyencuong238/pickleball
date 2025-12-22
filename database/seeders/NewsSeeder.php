<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\Category;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create categories
        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }

        $newsData = [
            [
                'title' => 'Giải Pickleball Mở Rộng TP.HCM 2025 Chính Thức Khởi Động',
                'slug' => 'giai-pickleball-mo-rong-tphcm-2025-chinh-thuc-khoi-dong',
                'content' => '<p>Giải Pickleball Mở Rộng TP.HCM 2025 chính thức được khởi động với sự tham gia của hơn 200 vận động viên từ khắp các tỉnh thành. Đây là giải đấu lớn nhất từ trước đến nay với quy mô chưa từng thấy.</p>
<p>Giải đấu sẽ diễn ra trong 3 ngày liên tiếp tại Sân Pickleball Thảo Điền, một trong những sân thi đấu hiện đại nhất Việt Nam. Các vận động viên sẽ thi đấu trong các hạng mục: Nam đơn, Nữ đơn, Đôi nam nữ và Tập thể.</p>',
                'image' => 'news_images/sample1.jpg',
                'category_id' => $categories->first()->id,
                'author' => 'Admin User',
                'is_featured' => true,
            ],
            [
                'title' => 'Những Kỹ Thuật Cơ Bản Mà Bạn Cần Biết Để Bắt Đầu Chơi Pickleball',
                'slug' => 'nhung-ky-thuat-co-ban-can-biet-de-bat-dau-choi-pickleball',
                'content' => '<p>Pickleball là một môn thể thao tương đối dễ tiếp cận, nhưng để chơi tốt vẫn cần phải nắm vững một số kỹ thuật cơ bản. Bài viết này sẽ hướng dẫn bạn những kỹ thuật quan trọng nhất.</p>
<p><strong>1. Cách Cầm Vợt:</strong> Cầm vợt bằng cách bắt chặt phần tay cầm, jari trỏ đặt dưới để kiểm soát tốt hơn.</p>
<p><strong>2. Tư Thế Đứng:</strong> Đứng với hai chân rộng bằng vai, tạo thế cân bằng tốt.</p>
<p><strong>3. Cú Đánh Forehand:</strong> Sử dụng cơ bắp bàn tay và cổ tay để tạo lực.</p>',
                'image' => 'news_images/sample2.jpg',
                'category_id' => $categories->where('slug', 'ky-thuat-co-ban')->first()->id ?? $categories->first()->id,
                'author' => 'Admin User',
                'is_featured' => false,
            ],
            [
                'title' => 'Nguyễn Văn An Lên Ngôi Vô Địch Giải Pickleball Thành Phố',
                'slug' => 'nguyen-van-an-len-ngoi-vo-dich-giai-pickleball-thanh-pho',
                'content' => '<p>Nguyễn Văn An, 28 tuổi, vừa lên ngôi vô địch giải Pickleball Thành Phố sau khi giành chiến thắng 2-0 trước đối thủ Trần Văn Bình trong trận chung kết. Đây là lần thứ hai An giành chức vô địch trong năm 2025.</p>
<p>Sau trận đấu, An chia sẻ: "Tôi rất vui khi có thể bảo vệ thành công cúp vô địch. Điều này khích lệ tôi để tiếp tục cải thiện kỹ năng và chuẩn bị cho những giải đấu sắp tới."</p>',
                'image' => 'news_images/sample3.jpg',
                'category_id' => $categories->where('slug', 'chien-luoc-thi-dau')->first()->id ?? $categories->first()->id,
                'author' => 'Admin User',
                'is_featured' => true,
            ],
            [
                'title' => 'Hướng Dẫn Chọn Vợt Pickleball Phù Hợp Cho Bạn',
                'slug' => 'huong-dan-chon-vot-pickleball-phu-hop-cho-ban',
                'content' => '<p>Việc chọn vợt phù hợp là rất quan trọng để cải thiện hiệu suất thi đấu. Dưới đây là hướng dẫn chi tiết giúp bạn chọn được vợt tốt nhất cho mình.</p>
<p><strong>Trọng Lượng Vợt:</strong> Vợt nhẹ (dưới 200g) phù hợp với người mới bắt đầu, trong khi vợt nặng hơn phù hợp với người chơi nâng cao.</p>
<p><strong>Chất Liệu:</strong> Vợt gỗ, composite hay graphite đều có ưu và nhược điểm riêng.</p>',
                'image' => 'news_images/sample4.jpg',
                'category_id' => $categories->first()->id,
                'author' => 'Admin User',
                'is_featured' => false,
            ],
            [
                'title' => 'Các Sân Pickleball Mới Khai Trương Tại TP.HCM',
                'slug' => 'cac-san-pickleball-moi-khai-truong-tai-tphcm',
                'content' => '<p>Trong 6 tháng qua, TP.HCM đã chứng kiến sự ra đời của 5 sân pickleball mới, đem lại cơ hội chơi thể thao này cho nhiều người dân hơn.</p>
<p>Các sân này được trang bị những tiện nghi hiện đại, phục vụ tốt cho cộng đồng pickleball. Giá thuê sân cũng khá hợp lý, từ 150.000 - 300.000 đồng/giờ.</p>',
                'image' => 'news_images/sample5.jpg',
                'category_id' => $categories->first()->id,
                'author' => 'Admin User',
                'is_featured' => false,
            ],
            [
                'title' => 'Bảo Vệ Sức Khỏe Khi Chơi Pickleball - Lời Khuyên Từ Chuyên Gia',
                'slug' => 'bao-ve-suc-khoe-khi-choi-pickleball-loi-khuyen-tu-chuyen-gia',
                'content' => '<p>Chơi pickleball là cách tuyệt vời để cải thiện sức khỏe, nhưng cũng cần phải chú ý đến an toàn. Dưới đây là những lời khuyên từ các chuyên gia.</p>
<p><strong>Khởi Động Trước Thi Đấu:</strong> Luôn khởi động kỹ trong 10-15 phút trước khi thi đấu để tránh chấn thương.</p>
<p><strong>Uống Nước Thường Xuyên:</strong> Uống nước bù lại lỗ hổng mất nước do vận động.</p>',
                'image' => 'news_images/sample6.jpg',
                'category_id' => $categories->where('slug', 'tu-van-suc-khoe')->first()->id ?? $categories->first()->id,
                'author' => 'Admin User',
                'is_featured' => false,
            ],
            [
                'title' => 'Luật Chơi Pickleball Chi Tiết - Mọi Thứ Bạn Cần Biết',
                'slug' => 'luat-choi-pickleball-chi-tiet-moi-thu-ban-can-biet',
                'content' => '<p>Hiểu rõ luật chơi pickleball là điều cần thiết cho bất kỳ người chơi nào. Bài viết này sẽ giải thích chi tiết những luật cơ bản nhất.</p>
<p>Sân pickleball có kích thước 20 ft x 44 ft (6.1m x 13.4m), tương tự như sân cầu lông nhưng nhỏ hơn. Net có chiều cao 36 inches ở hai đầu và 34 inches ở giữa.</p>',
                'image' => 'news_images/sample7.jpg',
                'category_id' => $categories->where('slug', 'luat-choi')->first()->id ?? $categories->first()->id,
                'author' => 'Admin User',
                'is_featured' => false,
            ],
            [
                'title' => 'Trần Thị Bình: Từ Người Mới Đến Á Quân Quốc Gia',
                'slug' => 'tran-thi-binh-tu-nguoi-moi-den-a-quan-quoc-gia',
                'content' => '<p>Câu chuyện của Trần Thị Bình là một minh chứng cho sức mạnh của sự kiên trì. Chỉ sau 2 năm bắt đầu chơi pickleball, cô đã trở thành á quân quốc gia.</p>
<p>"Tôi không bao giờ tưởng tượng rằng mình có thể đạt được thành tích như vậy. Thành công này đến từ sự yêu thích môn thể thao này và những nỗ lực không ngừng," Bình chia sẻ.</p>',
                'image' => 'news_images/sample8.jpg',
                'category_id' => $categories->first()->id,
                'author' => 'Admin User',
                'is_featured' => true,
            ],
        ];

        foreach ($newsData as $news) {
            News::create($news);
        }

        $this->command->info('✓ Tạo ' . count($newsData) . ' bài viết tin tức thành công!');
    }
}
