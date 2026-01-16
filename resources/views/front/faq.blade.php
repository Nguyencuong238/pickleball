@extends('layouts.front')

@section('seo')
    <title>Câu hỏi thường gặp - OnePickleball</title>
    <meta name="description" content="Giải đáp những thắc mắc phổ biến về OnePickleball - Nền tảng kết nối cộng đồng Pickleball hàng đầu tại Việt Nam.">
@endsection

@section('css')
    <style>
        .faq-hero {
            background: linear-gradient(135deg, rgba(10, 162, 137, 0.1) 0%, rgba(0, 168, 150, 0.1) 100%);
            padding: 60px 0 40px;
        }
        .faq-hero-content {
            text-align: center;
        }
        .faq-hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }
        .faq-hero-description {
            font-size: 1.1rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

        /* FAQ Section Styles */
        .faq-section {
            background: var(--bg-white);
            padding: 60px 0;
        }
        .faq-list {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .faq-item {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }
        .faq-item:hover {
            box-shadow: var(--shadow-md);
        }
        .faq-item.active {
            border-color: var(--primary);
        }
        .faq-question {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 20px;
            cursor: pointer;
            gap: 16px;
            background: transparent;
            border: none;
            width: 100%;
            text-align: left;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
            transition: background 0.2s ease;
        }
        .faq-question:hover {
            background: #f9fafb;
        }
        .faq-question-text {
            flex: 1;
        }
        .faq-icon {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f3f4f6;
            color: var(--text-muted);
            transition: transform 0.3s ease, background 0.3s ease, color 0.3s ease;
        }
        .faq-item.active .faq-icon {
            background: var(--primary);
            color: #fff;
            transform: rotate(45deg);
        }
        .faq-answer {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 0.35s ease-out;
        }
        .faq-item.active .faq-answer {
            grid-template-rows: 1fr;
        }
        .faq-answer-inner {
            overflow: hidden;
        }
        .faq-answer-content {
            padding: 0 20px 20px 20px;
            color: var(--text-muted);
            line-height: 1.7;
            font-size: 15px;
        }
        @media (max-width: 768px) {
            .faq-hero-title {
                font-size: 1.75rem;
            }
            .faq-question {
                padding: 16px;
                font-size: 15px;
            }
            .faq-answer-content {
                padding: 0 16px 16px 16px;
                font-size: 14px;
            }
        }
        /* Accessibility: Reduce motion for users who prefer it */
        @media (prefers-reduced-motion: reduce) {
            .faq-answer {
                transition: none;
            }
            .faq-icon {
                transition: none;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <!-- Hero Section -->
        <section class="faq-hero">
            <div class="container">
                <div class="faq-hero-content">
                    <h1 class="faq-hero-title">Câu hỏi thường gặp</h1>
                    <p class="faq-hero-description">Giải đáp những thắc mắc phổ biến về OnePickleball và các tính năng của nền tảng</p>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq-section">
            <div class="container">
                <div class="faq-list">
                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span class="faq-question-text">Onepickleball là gì?</span>
                            <span class="faq-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-inner">
                                <div class="faq-answer-content">
                                    Onepickleball là nền tảng kết nối cộng đồng Pickleball hàng đầu tại Việt Nam. Tìm sân, đăng ký giải đấu, kết nối đối thủ và cập nhật tin tức mới nhất.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span class="faq-question-text">Sân thi đấu</span>
                            <span class="faq-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-inner">
                                <div class="faq-answer-content">
                                    Là nơi tìm kiếm và đặt sân Pickleball chất lượng cao với cơ sở vật chất hiện đại. Ngoài ra người dùng có thể tham gia, tìm đối thủ thi đấu Social và nâng cao kỹ năng Pickleball.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span class="faq-question-text">Giải đấu Pickleball</span>
                            <span class="faq-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-inner">
                                <div class="faq-answer-content">
                                    Là nơi hỗ trợ tìm và đăng ký tham gia các giải đấu Pickleball chuyên nghiệp và phong trào trên toàn quốc.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span class="faq-question-text">Trận đấu</span>
                            <span class="faq-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-inner">
                                <div class="faq-answer-content">
                                    Là nơi người đam mê Pickleball có thể theo dõi xem tất cả trận đấu đang diễn ra, sắp diễn ra và đã diễn ra.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span class="faq-question-text">Bảng Xếp Hạng OPRS (OnePickleball Rating Score)</span>
                            <span class="faq-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-inner">
                                <div class="faq-answer-content">
                                    Là hệ thống Bảng Xếp Hạng phân cấp OPR Level giúp người chơi dễ dàng nhận biết trình độ của bản thân và đối thủ, từ đó lựa chọn trận đấu phù hợp và theo dõi sự tiến bộ.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span class="faq-question-text">OnePickleball Championship Ranking (OCR)</span>
                            <span class="faq-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-inner">
                                <div class="faq-answer-content">
                                    Là hệ thống thi đấu xếp hạng mở, được OnePickleball công nhận chính thức. Đây là nền tảng cho phép vận động viên có thể thi đấu mọi lúc, mọi nơi và được tính điểm một cách minh bạch, công bằng.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span class="faq-question-text">Đánh giá trình độ</span>
                            <span class="faq-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-inner">
                                <div class="faq-answer-content">
                                    Là nơi VĐV khảo sát trình độ qua bộ câu hỏi trắc nghiệm được chia thành 6 bộ kỹ năng trong Pickleball để xác định ELO và trình độ.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span class="faq-question-text">Nhóm & Câu lạc bộ</span>
                            <span class="faq-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-inner">
                                <div class="faq-answer-content">
                                    Là nơi người dùng có thể tham gia, tạo nhóm hoặc CLB để giao lưu, sinh hoạt và triển khai các hoạt động, sự kiện để luyện tập và thi đấu nâng trình độ.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span class="faq-question-text">Giảng Viên</span>
                            <span class="faq-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-inner">
                                <div class="faq-answer-content">
                                    Là nơi hỗ trợ, tìm kiếm và đăng ký học Pickleball từ những giảng viên chuyên gia Pickleball chuyên nghiệp.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span class="faq-question-text">Trọng Tài</span>
                            <span class="faq-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-inner">
                                <div class="faq-answer-content">
                                    Là nơi hỗ trợ, tìm kiếm kết nối các trọng tài chuyên nghiệp có chuyên môn cao trong quá trình thi đấu chuyên nghiệp, cũng như là tổ chức các giải đấu Pickleball tại Việt Nam.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span class="faq-question-text">Community Hub</span>
                            <span class="faq-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-inner">
                                <div class="faq-answer-content">
                                    Người dùng thu thập điểm Onepickleball qua các nhiệm vụ social, tham gia giải đấu,... trên nền tảng onepickleball.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span class="faq-question-text">Tin Tức</span>
                            <span class="faq-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-inner">
                                <div class="faq-answer-content">
                                    Là nơi cập nhật tin tức, giải đấu sớm nhất, và những bài phân tích, đánh giá các trận nổi bật khách quan nhất từ đội ngũ Onepickleball.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item">
                        <button class="faq-question" onclick="toggleFaq(this)">
                            <span class="faq-question-text">Ví điểm Onepickleball</span>
                            <span class="faq-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </span>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-inner">
                                <div class="faq-answer-content">
                                    Là nơi Quản lý và theo dõi điểm Onepickleball, và điểm có thể dùng để đăng ký giải đấu, hoán đổi voucher và mua sản phẩm đặc biệt.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        // FAQ Accordion Toggle
        function toggleFaq(button) {
            const faqItem = button.closest('.faq-item');

            // Close all other FAQ items (accordion behavior)
            document.querySelectorAll('.faq-item.active').forEach(item => {
                if (item !== faqItem) {
                    item.classList.remove('active');
                }
            });

            // Toggle current item
            faqItem.classList.toggle('active');
        }
    </script>
@endsection
