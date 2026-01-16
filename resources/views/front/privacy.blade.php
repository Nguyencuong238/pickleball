@extends('layouts.front')

@section('seo')
    <title>Chính sách bảo mật - OnePickleball</title>
    <meta name="description" content="Chính sách bảo mật của OnePickleball - Cam kết bảo vệ thông tin cá nhân của người dùng.">
@endsection

@section('css')
    <style>
        .privacy-hero {
            background: linear-gradient(135deg, rgba(10, 162, 137, 0.1) 0%, rgba(0, 168, 150, 0.1) 100%);
            padding: 60px 0 40px;
        }
        .privacy-hero-content {
            text-align: center;
        }
        .privacy-hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }
        .privacy-hero-description {
            font-size: 1.1rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }
        .privacy-section {
            background: var(--bg-white);
            padding: 60px 0;
        }
        .privacy-content {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius-lg);
            padding: 40px;
        }
        .privacy-content h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 2rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary);
        }
        .privacy-content h2:first-child {
            margin-top: 0;
        }
        .privacy-content h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-dark);
            margin: 1.5rem 0 0.75rem;
        }
        .privacy-content p {
            color: var(--text-muted);
            line-height: 1.8;
            margin-bottom: 1rem;
        }
        .privacy-content ul {
            color: var(--text-muted);
            line-height: 1.8;
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }
        .privacy-content ul li {
            margin-bottom: 0.5rem;
        }
        .privacy-update {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: var(--radius-md);
        }
        .privacy-highlight {
            background: linear-gradient(135deg, rgba(0, 217, 181, 0.1) 0%, rgba(0, 153, 204, 0.1) 100%);
            border-left: 4px solid var(--primary);
            padding: 1rem 1.5rem;
            margin: 1.5rem 0;
            border-radius: 0 var(--radius-md) var(--radius-md) 0;
        }
        .privacy-highlight p {
            margin-bottom: 0;
            font-weight: 500;
        }
        @media (max-width: 768px) {
            .privacy-hero-title {
                font-size: 1.75rem;
            }
            .privacy-content {
                padding: 24px;
            }
            .privacy-content h2 {
                font-size: 1.25rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <!-- Hero Section -->
        <section class="privacy-hero">
            <div class="container">
                <div class="privacy-hero-content">
                    <h1 class="privacy-hero-title">Chính sách bảo mật</h1>
                    <p class="privacy-hero-description">Cam kết bảo vệ quyền riêng tư và thông tin cá nhân của bạn</p>
                </div>
            </div>
        </section>

        <!-- Privacy Content -->
        <section class="privacy-section">
            <div class="container">
                <div class="privacy-content">
                    <div class="privacy-update">
                        <strong>📅 Cập nhật lần cuối:</strong> Tháng 01/2025
                    </div>

                    <div class="privacy-highlight">
                        <p>🛡️ OnePickleball cam kết bảo vệ thông tin cá nhân của bạn theo quy định pháp luật Việt Nam.</p>
                    </div>

                    <h2>1. Thông tin chúng tôi thu thập</h2>
                    <h3>1.1 Thông tin bạn cung cấp</h3>
                    <ul>
                        <li><strong>Thông tin tài khoản:</strong> Họ tên, email, số điện thoại, mật khẩu</li>
                        <li><strong>Thông tin cá nhân:</strong> Ngày sinh, giới tính, địa chỉ, ảnh đại diện</li>
                        <li><strong>Thông tin thi đấu:</strong> Trình độ, kết quả, điểm OPR</li>
                        <li><strong>Thông tin thanh toán:</strong> Khi đặt sân hoặc đăng ký giải đấu</li>
                    </ul>
                    <h3>1.2 Thông tin tự động thu thập</h3>
                    <ul>
                        <li>Địa chỉ IP, loại trình duyệt, thiết bị</li>
                        <li>Thời gian truy cập, trang đã xem</li>
                        <li>Cookies và dữ liệu phiên làm việc</li>
                    </ul>

                    <h2>2. Mục đích sử dụng thông tin</h2>
                    <p>Chúng tôi sử dụng thông tin của bạn để:</p>
                    <ul>
                        <li>Cung cấp và quản lý tài khoản của bạn</li>
                        <li>Xử lý đặt sân, đăng ký giải đấu</li>
                        <li>Tính toán và cập nhật điểm OPR</li>
                        <li>Gửi thông báo về giải đấu, sự kiện</li>
                        <li>Cải thiện trải nghiệm người dùng</li>
                        <li>Hỗ trợ khách hàng</li>
                        <li>Phát hiện và ngăn chặn gian lận</li>
                    </ul>

                    <h2>3. Chia sẻ thông tin</h2>
                    <h3>3.1 Thông tin công khai</h3>
                    <ul>
                        <li>Tên, ảnh đại diện trong bảng xếp hạng</li>
                        <li>Kết quả thi đấu, điểm OPR</li>
                        <li>Thông tin CLB/nhóm bạn tham gia</li>
                    </ul>
                    <h3>3.2 Chia sẻ với bên thứ ba</h3>
                    <p>Chúng tôi <strong>không bán</strong> thông tin cá nhân. Chỉ chia sẻ khi:</p>
                    <ul>
                        <li>Có sự đồng ý của bạn</li>
                        <li>Với đối tác sân/giải đấu để xử lý đặt chỗ</li>
                        <li>Theo yêu cầu của cơ quan pháp luật</li>
                        <li>Với nhà cung cấp dịch vụ (hosting, thanh toán) theo hợp đồng bảo mật</li>
                    </ul>

                    <h2>4. Bảo mật thông tin</h2>
                    <p>Chúng tôi áp dụng các biện pháp bảo mật:</p>
                    <ul>
                        <li>Mã hóa SSL cho tất cả kết nối</li>
                        <li>Mã hóa mật khẩu bằng thuật toán bcrypt</li>
                        <li>Giới hạn quyền truy cập dữ liệu</li>
                        <li>Sao lưu dữ liệu định kỳ</li>
                        <li>Giám sát và phát hiện xâm nhập</li>
                    </ul>

                    <h2>5. Quyền của bạn</h2>
                    <p>Bạn có quyền:</p>
                    <ul>
                        <li><strong>Truy cập:</strong> Xem thông tin cá nhân của mình</li>
                        <li><strong>Chỉnh sửa:</strong> Cập nhật thông tin không chính xác</li>
                        <li><strong>Xóa:</strong> Yêu cầu xóa tài khoản và dữ liệu</li>
                        <li><strong>Hạn chế:</strong> Từ chối nhận email marketing</li>
                        <li><strong>Di chuyển:</strong> Yêu cầu xuất dữ liệu cá nhân</li>
                    </ul>
                    <p>Để thực hiện các quyền trên, liên hệ: hello@onepickleball.vn</p>

                    <h2>6. Cookies</h2>
                    <h3>6.1 Cookies chúng tôi sử dụng</h3>
                    <ul>
                        <li><strong>Cookies cần thiết:</strong> Đăng nhập, giỏ hàng, bảo mật</li>
                        <li><strong>Cookies phân tích:</strong> Thống kê lượng truy cập (Google Analytics)</li>
                        <li><strong>Cookies chức năng:</strong> Ghi nhớ tùy chọn của bạn</li>
                    </ul>
                    <h3>6.2 Quản lý cookies</h3>
                    <p>Bạn có thể tắt cookies trong cài đặt trình duyệt. Tuy nhiên, một số tính năng có thể không hoạt động đúng.</p>

                    <h2>7. Lưu trữ dữ liệu</h2>
                    <ul>
                        <li>Dữ liệu được lưu trữ tại máy chủ ở Việt Nam</li>
                        <li>Thời gian lưu trữ: Trong suốt thời gian bạn sử dụng dịch vụ</li>
                        <li>Sau khi xóa tài khoản: Dữ liệu được xóa trong 30 ngày (trừ dữ liệu pháp lý cần lưu)</li>
                    </ul>

                    <h2>8. Trẻ em</h2>
                    <p>Dịch vụ dành cho người từ 16 tuổi trở lên. Chúng tôi không cố ý thu thập thông tin từ trẻ em dưới 16 tuổi. Nếu phát hiện, chúng tôi sẽ xóa ngay.</p>

                    <h2>9. Thay đổi chính sách</h2>
                    <p>Chúng tôi có thể cập nhật chính sách này. Thay đổi quan trọng sẽ được thông báo qua:</p>
                    <ul>
                        <li>Email đến tài khoản đã đăng ký</li>
                        <li>Thông báo trên website</li>
                    </ul>

                    <h2>10. Liên hệ</h2>
                    <p>Mọi câu hỏi về chính sách bảo mật, vui lòng liên hệ:</p>
                    <ul>
                        <li><strong>📧 Email:</strong> hello@onepickleball.vn</li>
                        <li><strong>📞 Điện thoại:</strong> 0963 728 586</li>
                        <li><strong>📍 Địa chỉ:</strong> 125 Trần Thị Nơi, Phường Chánh Hưng, TP HCM</li>
                    </ul>

                    <div class="privacy-highlight">
                        <p>✅ Bằng việc sử dụng OnePickleball, bạn đồng ý với chính sách bảo mật này.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
