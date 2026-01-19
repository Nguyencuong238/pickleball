@extends('layouts.front')

@section('seo')
    <title>Điều khoản sử dụng - OnePickleball</title>
    <meta name="description" content="Điều khoản sử dụng của OnePickleball - Nền tảng kết nối cộng đồng Pickleball hàng đầu tại Việt Nam.">
@endsection

@section('css')
    <style>
        .terms-hero {
            background: linear-gradient(135deg, rgba(10, 162, 137, 0.1) 0%, rgba(0, 168, 150, 0.1) 100%);
            padding: 60px 0 40px;
        }
        .terms-hero-content {
            text-align: center;
        }
        .terms-hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }
        .terms-hero-description {
            font-size: 1.1rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }
        .terms-section {
            background: var(--bg-white);
            padding: 60px 0;
        }
        .terms-content {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius-lg);
            padding: 40px;
        }
        .terms-content h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 2rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary);
        }
        .terms-content h2:first-child {
            margin-top: 0;
        }
        .terms-content h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-dark);
            margin: 1.5rem 0 0.75rem;
        }
        .terms-content p {
            color: var(--text-muted);
            line-height: 1.8;
            margin-bottom: 1rem;
        }
        .terms-content ul {
            color: var(--text-muted);
            line-height: 1.8;
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }
        .terms-content ul li {
            margin-bottom: 0.5rem;
        }
        .terms-update {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: var(--radius-md);
        }
        @media (max-width: 768px) {
            .terms-hero-title {
                font-size: 1.75rem;
            }
            .terms-content {
                padding: 24px;
            }
            .terms-content h2 {
                font-size: 1.25rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <!-- Hero Section -->
        <section class="terms-hero">
            <div class="container">
                <div class="terms-hero-content">
                    <h1 class="terms-hero-title">Điều khoản sử dụng</h1>
                    <p class="terms-hero-description">Vui lòng đọc kỹ các điều khoản trước khi sử dụng dịch vụ của OnePickleball</p>
                </div>
            </div>
        </section>

        <!-- Terms Content -->
        <section class="terms-section">
            <div class="container">
                <div class="terms-content">
                    <div class="terms-update">
                        <strong>📅 Cập nhật lần cuối:</strong> Tháng 01/2025
                    </div>

                    <h2>1. Giới thiệu</h2>
                    <p>Chào mừng bạn đến với OnePickleball! Khi truy cập và sử dụng website onepickleball.vn, bạn đồng ý tuân thủ các điều khoản được nêu dưới đây. Nếu bạn không đồng ý, vui lòng không sử dụng dịch vụ của chúng tôi.</p>

                    <h2>2. Định nghĩa</h2>
                    <ul>
                        <li><strong>"Nền tảng"</strong>: Website onepickleball.vn và các dịch vụ liên quan</li>
                        <li><strong>"Người dùng"</strong>: Cá nhân đăng ký và sử dụng dịch vụ</li>
                        <li><strong>"Nội dung"</strong>: Thông tin, hình ảnh, video được đăng tải trên nền tảng</li>
                        <li><strong>"Dịch vụ"</strong>: Tất cả tính năng do OnePickleball cung cấp</li>
                    </ul>

                    <h2>3. Đăng ký tài khoản</h2>
                    <h3>3.1 Điều kiện đăng ký</h3>
                    <ul>
                        <li>Bạn phải từ 16 tuổi trở lên</li>
                        <li>Cung cấp thông tin chính xác, đầy đủ</li>
                        <li>Mỗi cá nhân chỉ được sở hữu một tài khoản</li>
                    </ul>
                    <h3>3.2 Bảo mật tài khoản</h3>
                    <p>Bạn chịu trách nhiệm bảo mật thông tin đăng nhập và tất cả hoạt động dưới tài khoản của mình. Thông báo ngay cho chúng tôi nếu phát hiện truy cập trái phép.</p>

                    <h2>4. Quy tắc sử dụng</h2>
                    <h3>4.1 Cấm</h3>
                    <ul>
                        <li>Đăng tải nội dung vi phạm pháp luật, đạo đức</li>
                        <li>Giả mạo, mạo danh người khác</li>
                        <li>Spam, quảng cáo không được phép</li>
                        <li>Can thiệp vào hệ thống, tấn công mạng</li>
                        <li>Gian lận trong thi đấu, xếp hạng</li>
                    </ul>
                    <h3>4.2 Trách nhiệm</h3>
                    <p>Người dùng chịu trách nhiệm về nội dung mình đăng tải. OnePickleball có quyền xóa nội dung vi phạm mà không cần báo trước.</p>

                    <h2>5. Đặt sân và giải đấu</h2>
                    <h3>5.1 Đặt sân</h3>
                    <ul>
                        <li>Thanh toán đúng hạn theo quy định</li>
                        <li>Hủy đặt trước 24h để được hoàn tiền (tùy chính sách từng sân)</li>
                        <li>Tuân thủ nội quy của sân thi đấu</li>
                    </ul>
                    <h3>5.2 Giải đấu</h3>
                    <ul>
                        <li>Đăng ký đúng thời hạn và nộp phí (nếu có)</li>
                        <li>Tuân thủ luật thi đấu và quyết định của trọng tài</li>
                        <li>Không được rút lui sau khi bốc thăm/xếp lịch (trừ trường hợp bất khả kháng)</li>
                    </ul>

                    <h2>6. Hệ thống điểm OPR</h2>
                    <p>Điểm OPR (OnePickleball Rating) được tính dựa trên:</p>
                    <ul>
                        <li>Kết quả thi đấu chính thức</li>
                        <li>Đánh giá trình độ (Skill Quiz)</li>
                        <li>Hoạt động cộng đồng</li>
                    </ul>
                    <p>OnePickleball có quyền điều chỉnh điểm nếu phát hiện gian lận hoặc sai sót.</p>

                    <h2>7. Quyền sở hữu trí tuệ</h2>
                    <p>Tất cả nội dung trên nền tảng (logo, thiết kế, phần mềm) thuộc sở hữu của OnePickleball. Người dùng không được sao chép, phân phối khi chưa có sự đồng ý.</p>

                    <h2>8. Giới hạn trách nhiệm</h2>
                    <p>OnePickleball không chịu trách nhiệm về:</p>
                    <ul>
                        <li>Thiệt hại do lỗi kỹ thuật, gián đoạn dịch vụ</li>
                        <li>Tranh chấp giữa người dùng</li>
                        <li>Chấn thương trong quá trình thi đấu</li>
                        <li>Mất mát tài sản tại địa điểm thi đấu</li>
                    </ul>

                    <h2>9. Chấm dứt tài khoản</h2>
                    <p>OnePickleball có quyền tạm khóa hoặc xóa vĩnh viễn tài khoản nếu người dùng vi phạm điều khoản. Người dùng có thể yêu cầu xóa tài khoản bất kỳ lúc nào.</p>

                    <h2>10. Thay đổi điều khoản</h2>
                    <p>Chúng tôi có thể cập nhật điều khoản bất kỳ lúc nào. Thay đổi sẽ được thông báo trên website. Việc tiếp tục sử dụng dịch vụ sau thay đổi đồng nghĩa với việc bạn chấp nhận điều khoản mới.</p>

                    <h2>11. Liên hệ</h2>
                    <p>Mọi thắc mắc về điều khoản sử dụng, vui lòng liên hệ:</p>
                    <ul>
                        <li><strong>📧 Email:</strong> hello@onepickleball.vn</li>
                        <li><strong>📞 Điện thoại:</strong> 0963 728 586</li>
                        <li><strong>📍 Địa chỉ:</strong> 125 Trần Thị Nơi, Phường Chánh Hưng, TP HCM</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>
@endsection
