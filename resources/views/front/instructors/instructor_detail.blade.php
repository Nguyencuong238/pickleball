@extends('layouts.front')
@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles-extended.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles-coaches.css') }}">
@endsection

@section('content')
    <section class="breadcrumb-section">
        <div class="container">
            <nav class="breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
                <a href="{{ route('instructors') }}">Giảng viên</a>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
                <span>{{ $instructor->name }}</span>
            </nav>
        </div>
    </section>

    <!-- Coach Profile Header -->
    <section class="coach-profile-header">
        <div class="container">
            <div class="profile-header-wrapper">
                <!-- Coach Avatar & Basic Info -->
                <div class="coach-avatar-section">
                    <div class="coach-avatar-large">
                        @if ($instructor->image)
                            <img src="{{ asset('storage/' . $instructor->image) }}" alt="{{ $instructor->name }}">
                        @else
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 400'%3E%3Cdefs%3E%3ClinearGradient id='gProfile' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%230099CC;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23gProfile)' width='400' height='400'/%3E%3Ccircle cx='200' cy='150' r='80' fill='rgba(255,255,255,0.3)'/%3E%3Cellipse cx='200' cy='320' rx='100' ry='80' fill='rgba(255,255,255,0.3)'/%3E%3C/svg%3E"
                                alt="{{ $instructor->name }}">
                        @endif
                        <div class="online-status">
                            <span class="status-dot"></span>
                            Online
                        </div>
                    </div>
                    <div class="coach-badges-vertical">
                        <span class="badge-item verified">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" />
                            </svg>
                            Đã xác minh
                        </span>
                        <span class="badge-item certified">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 2L15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2z" />
                            </svg>
                            Có chứng chỉ
                        </span>
                    </div>
                </div>

                <!-- Coach Info -->
                <div class="coach-info-section">
                    <div class="coach-header-top">
                        <h1 class="coach-profile-name">{{ $instructor->name }}</h1>
                        <div class="coach-rating-large">
                            <div class="stars">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                    </svg>
                                @endfor
                            </div>
                            <span class="rating-number">{{ number_format($instructor->rating ?? 5, 1) }}</span>
                            <span class="rating-count">({{ $instructor->reviews_count ?? 0 }} đánh giá)</span>
                        </div>
                    </div>

                    <p class="coach-tagline">{{ $instructor->bio ?? 'Huấn luyện viên Pickleball chuyên nghiệp' }}</p>

                    <div class="coach-quick-info">
                        <div class="info-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                            </svg>
                            <span><strong>{{ $instructor->experience_years ?? 0 }}</strong> năm kinh nghiệm</span>
                        </div>
                        <div class="info-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <span>{{ $instructor->province->name ?? 'Hà Nội' }}</span>
                        </div>
                        <div class="info-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <span><strong>{{ $instructor->student_count ?? 0 }}</strong> học viên</span>
                        </div>
                    </div>

                    <div class="coach-specialty-tags">
                        @php
                            $specialties = json_decode($instructor->specialties ?? '[]', true);
                        @endphp
                        @forelse($specialties as $specialty)
                            <span class="specialty-tag">{{ $specialty }}</span>
                        @empty
                            <span class="specialty-tag">Dạy 1-1</span>
                            <span class="specialty-tag">Dạy nhóm</span>
                        @endforelse
                    </div>

                    <div class="coach-actions-header">
                        <button class="btn btn-primary btn-lg">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                            Liên hệ ngay
                        </button>
                        <button class="btn btn-secondary btn-lg">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            Đặt lịch học
                        </button>
                        <button class="btn btn-outline btn-icon btn-favorite" title="Yêu thích">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                            </svg>
                        </button>
                        <button class="btn btn-outline btn-icon" title="Chia sẻ">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="18" cy="5" r="3" />
                                <circle cx="6" cy="12" r="3" />
                                <circle cx="18" cy="19" r="3" />
                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="coach-stats-sidebar">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value">{{ $instructor->student_count ?? 0 }}</span>
                            <span class="stat-label">Học viên</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value">{{ $instructor->reviews_count ?? 0 }}</span>
                            <span class="stat-label">Đánh giá</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value">{{ $instructor->total_hours ?? 0 }}+</span>
                            <span class="stat-label">Giờ dạy</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Coach Detail Content -->
    <section class="coach-detail-content section">
        <div class="container">
            <div class="detail-layout">
                <!-- Main Content -->
                <div class="detail-main">
                    <!-- About Section -->
                    <div class="detail-card">
                        <h2 class="detail-card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Giới thiệu
                        </h2>
                        <div class="about-content">
                            {!! nl2br($instructor->description ?? 'Giảng viên Pickleball chuyên nghiệp') !!}
                        </div>
                    </div>

                    <!-- Teaching Experience -->
                    <div class="detail-card">
                        <h2 class="detail-card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                            </svg>
                            Kinh nghiệm giảng dạy
                        </h2>
                        <div class="experience-timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4>Huấn luyện viên chính - CLB Pickleball Saigon Elite</h4>
                                    <span class="timeline-date">2021 - Hiện tại</span>
                                    <p>Phụ trách đào tạo các lớp nâng cao và huấn luyện đội tuyển thi đấu của câu lạc bộ.
                                    </p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4>Giảng viên - Trung tâm Thể thao Rạch Chiếc</h4>
                                    <span class="timeline-date">2018 - 2021</span>
                                    <p>Giảng dạy các lớp Pickleball từ cơ bản đến nâng cao cho người lớn và thiếu niên.</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4>Trợ lý huấn luyện viên - CLB Pickleball District 2</h4>
                                    <span class="timeline-date">2017 - 2018</span>
                                    <p>Hỗ trợ huấn luyện viên chính trong các buổi tập và tổ chức các giải đấu nội bộ.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Certifications -->
                    <div class="detail-card">
                        <h2 class="detail-card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="7" />
                                <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88" />
                            </svg>
                            Chứng chỉ & Thành tích
                        </h2>
                        <div class="certifications-grid">
                            <div class="cert-item">
                                <div class="cert-icon">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2L15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2z" />
                                    </svg>
                                </div>
                                <div class="cert-info">
                                    <h4>IPTPA Certified Coach</h4>
                                    <p>International Pickleball Teaching Professional Association</p>
                                    <span class="cert-year">2020</span>
                                </div>
                            </div>
                            <div class="cert-item">
                                <div class="cert-icon">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2L15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91-1.01L12 2z" />
                                    </svg>
                                </div>
                                <div class="cert-info">
                                    <h4>Chứng chỉ HLV Pickleball Việt Nam</h4>
                                    <p>Liên đoàn Pickleball Việt Nam</p>
                                    <span class="cert-year">2019</span>
                                </div>
                            </div>
                            <div class="cert-item">
                                <div class="cert-icon trophy">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M8 21h8m-4-4v4M6 4h12m-6 8a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm-8 0h2a2 2 0 0 0 2-2V4H2v6a2 2 0 0 0 2 2zm16 0h2a2 2 0 0 0 2-2V4h-4v6a2 2 0 0 0 2 2z" />
                                    </svg>
                                </div>
                                <div class="cert-info">
                                    <h4>Vô địch Doubles - HCM Open 2023</h4>
                                    <p>Giải đấu Pickleball TP.HCM mở rộng</p>
                                    <span class="cert-year">2023</span>
                                </div>
                            </div>
                            <div class="cert-item">
                                <div class="cert-icon trophy">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M8 21h8m-4-4v4M6 4h12m-6 8a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm-8 0h2a2 2 0 0 0 2-2V4H2v6a2 2 0 0 0 2 2zm16 0h2a2 2 0 0 0 2-2V4h-4v6a2 2 0 0 0 2 2z" />
                                    </svg>
                                </div>
                                <div class="cert-info">
                                    <h4>Á quân Singles - Vietnam National 2022</h4>
                                    <p>Giải Vô địch Quốc gia Pickleball</p>
                                    <span class="cert-year">2022</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Teaching Methods -->
                    <div class="detail-card">
                        <h2 class="detail-card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                            </svg>
                            Phương pháp giảng dạy
                        </h2>
                        <div class="methods-grid">
                            <div class="method-item">
                                <div class="method-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <path d="M12 16v-4M12 8h.01" />
                                    </svg>
                                </div>
                                <h4>Cá nhân hóa</h4>
                                <p>Thiết kế chương trình phù hợp với trình độ và mục tiêu của từng học viên</p>
                            </div>
                            <div class="method-item">
                                <div class="method-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path
                                            d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                                    </svg>
                                </div>
                                <h4>Thực hành nhiều</h4>
                                <p>70% thời gian thực hành, 30% lý thuyết để tối đa hóa kỹ năng thực chiến</p>
                            </div>
                            <div class="method-item">
                                <div class="method-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path
                                            d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5" />
                                        <path d="M9 18h6" />
                                        <path d="M10 22h4" />
                                    </svg>
                                </div>
                                <h4>Phân tích video</h4>
                                <p>Quay và phân tích kỹ thuật để học viên nhận ra điểm cần cải thiện</p>
                            </div>
                            <div class="method-item">
                                <div class="method-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    </svg>
                                </div>
                                <h4>Thi đấu thực tế</h4>
                                <p>Tổ chức các trận đấu tập để học viên áp dụng kiến thức vào thực tế</p>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews -->
                    <div class="detail-card">
                        <h2 class="detail-card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon
                                    points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                            Đánh giá từ học viên
                            <span class="review-count">(89 đánh giá)</span>
                        </h2>

                        <!-- Rating Summary -->
                        <div class="rating-summary">
                            <div class="rating-overview">
                                <div class="rating-big">4.9</div>
                                <div class="rating-stars">
                                    <div class="stars">
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                        <svg viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                        </svg>
                                    </div>
                                    <span>89 đánh giá</span>
                                </div>
                            </div>
                            <div class="rating-bars">
                                <div class="rating-bar-item">
                                    <span>5 sao</span>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: 85%"></div>
                                    </div>
                                    <span>76</span>
                                </div>
                                <div class="rating-bar-item">
                                    <span>4 sao</span>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: 10%"></div>
                                    </div>
                                    <span>9</span>
                                </div>
                                <div class="rating-bar-item">
                                    <span>3 sao</span>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: 3%"></div>
                                    </div>
                                    <span>3</span>
                                </div>
                                <div class="rating-bar-item">
                                    <span>2 sao</span>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: 1%"></div>
                                    </div>
                                    <span>1</span>
                                </div>
                                <div class="rating-bar-item">
                                    <span>1 sao</span>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: 0%"></div>
                                    </div>
                                    <span>0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Reviews List -->
                        <div class="reviews-list">
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-avatar">
                                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='50' fill='%23FF8E53'/%3E%3Ctext x='50' y='55' font-size='40' text-anchor='middle' fill='white' dominant-baseline='middle'%3ETM%3C/text%3E%3C/svg%3E"
                                            alt="Trần Minh">
                                    </div>
                                    <div class="reviewer-info">
                                        <h4>Trần Minh</h4>
                                        <div class="review-meta">
                                            <div class="stars small">
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                            </div>
                                            <span class="review-date">2 tuần trước</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="review-content">Coach Hùng dạy rất tận tâm và dễ hiểu. Sau 3 tháng học, kỹ thuật
                                    serve của tôi đã cải thiện rõ rệt. Rất recommend cho những ai muốn học Pickleball một
                                    cách bài bản!</p>
                                <div class="review-tags">
                                    <span>Tận tâm</span>
                                    <span>Dễ hiểu</span>
                                    <span>Kiên nhẫn</span>
                                </div>
                            </div>

                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-avatar">
                                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='50' fill='%239D84B7'/%3E%3Ctext x='50' y='55' font-size='40' text-anchor='middle' fill='white' dominant-baseline='middle'%3ELH%3C/text%3E%3C/svg%3E"
                                            alt="Lê Hoàng">
                                    </div>
                                    <div class="reviewer-info">
                                        <h4>Lê Hoàng</h4>
                                        <div class="review-meta">
                                            <div class="stars small">
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                            </div>
                                            <span class="review-date">1 tháng trước</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="review-content">Mình đã tham gia lớp nâng cao của coach Hùng được 6 tháng. Phương
                                    pháp giảng dạy rất khoa học, có phân tích video giúp mình nhìn ra lỗi sai rất nhanh. Giờ
                                    mình đã tự tin tham gia các giải đấu phong trào.</p>
                                <div class="review-tags">
                                    <span>Chuyên nghiệp</span>
                                    <span>Khoa học</span>
                                </div>
                            </div>

                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-avatar">
                                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='50' fill='%2300D9B5'/%3E%3Ctext x='50' y='55' font-size='40' text-anchor='middle' fill='white' dominant-baseline='middle'%3ENL%3C/text%3E%3C/svg%3E"
                                            alt="Ngọc Linh">
                                    </div>
                                    <div class="reviewer-info">
                                        <h4>Ngọc Linh</h4>
                                        <div class="review-meta">
                                            <div class="stars small">
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                                <svg viewBox="0 0 24 24" fill="currentColor">
                                                    <path
                                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                                </svg>
                                            </div>
                                            <span class="review-date">2 tháng trước</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="review-content">Ban đầu em rất ngại vì chưa biết gì về Pickleball, nhưng coach
                                    Hùng rất kiên nhẫn và tạo môi trường thoải mái. Giờ em đã có thể chơi cùng bạn bè và
                                    thậm chí tham gia social hàng tuần!</p>
                                <div class="review-tags">
                                    <span>Kiên nhẫn</span>
                                    <span>Thân thiện</span>
                                    <span>Vui vẻ</span>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-outline btn-block">Xem tất cả đánh giá</button>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="detail-sidebar">
                    <!-- Booking Card -->
                    <div class="sidebar-card booking-card">
                        <h3 class="sidebar-card-title">Đăng ký học</h3>
                        <div class="price-display">
                            <span class="price-label">Học phí từ</span>
                            <span
                                class="price-value">{{ number_format($instructor->price_per_session ?? 500000, 0, ',', '.') }}đ</span>
                            <span class="price-unit">/ buổi</span>
                        </div>
                        <div class="package-options">
                            <label class="package-option">
                                <input type="radio" name="package" value="single" checked>
                                <div class="package-content">
                                    <span class="package-name">Buổi lẻ</span>
                                    <span class="package-price">500.000đ/buổi</span>
                                </div>
                            </label>
                            <label class="package-option">
                                <input type="radio" name="package" value="4sessions">
                                <div class="package-content">
                                    <span class="package-name">Gói 4 buổi</span>
                                    <span class="package-price">1.800.000đ <small>(-10%)</small></span>
                                </div>
                            </label>
                            <label class="package-option popular">
                                <input type="radio" name="package" value="8sessions">
                                <div class="package-content">
                                    <span class="package-name">Gói 8 buổi</span>
                                    <span class="package-price">3.200.000đ <small>(-20%)</small></span>
                                </div>
                                <span class="popular-badge">Phổ biến</span>
                            </label>
                            <label class="package-option">
                                <input type="radio" name="package" value="group">
                                <div class="package-content">
                                    <span class="package-name">Lớp nhóm (4-6 người)</span>
                                    <span class="package-price">250.000đ/buổi/người</span>
                                </div>
                            </label>
                        </div>
                        <button class="btn btn-primary btn-lg btn-block">Đặt lịch ngay</button>
                        <p class="booking-note">Liên hệ để được tư vấn và sắp xếp lịch phù hợp</p>
                    </div>

                    <!-- Contact Card -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-card-title">Liên hệ</h3>
                        <div class="contact-methods">
                            @if ($instructor->phone)
                                <a href="tel:{{ $instructor->phone }}" class="contact-method">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path
                                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                    </svg>
                                    <span>{{ $instructor->phone }}</span>
                                </a>
                            @endif
                            @if ($instructor->zalo)
                                <a href="#" class="contact-method zalo">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 14.79c-.28.4-.85.77-1.58.77-.16 0-.33-.02-.5-.06-1.72-.42-3.46-1.51-4.91-3.06-1.45-1.56-2.39-3.38-2.65-5.13-.03-.17-.04-.34-.04-.5 0-.73.34-1.33.71-1.64.37-.32.88-.51 1.42-.51.12 0 .24.01.35.03.61.09 1.15.64 1.42 1.44l.59 1.76c.14.43.11.89-.08 1.28-.18.39-.51.7-.9.86l-.28.11c.12.28.29.56.52.84.48.57 1.08 1.12 1.76 1.64.28.21.55.38.82.5l.11-.28c.16-.39.47-.72.86-.9.39-.19.85-.22 1.28-.08l1.76.59c.8.27 1.35.81 1.44 1.42.02.11.03.23.03.35 0 .54-.19 1.05-.51 1.42z" />
                                    </svg>
                                    <span>Zalo: {{ $instructor->zalo }}</span>
                                </a>
                            @endif
                            @if ($instructor->email)
                                <a href="mailto:{{ $instructor->email }}" class="contact-method">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path
                                            d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                        <polyline points="22,6 12,13 2,6" />
                                    </svg>
                                    <span>{{ $instructor->email }}</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Location Card -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-card-title">Khu vực dạy</h3>
                        <div class="location-list">
                            <div class="location-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <div class="location-info">
                                    <strong>Quận 2, TP. HCM</strong>
                                    <span>Sân Rạch Chiếc, Sân An Phú</span>
                                </div>
                            </div>
                            <div class="location-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <div class="location-info">
                                    <strong>Thủ Đức, TP. HCM</strong>
                                    <span>Sân Thủ Đức Sports</span>
                                </div>
                            </div>
                            <div class="location-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <div class="location-info">
                                    <strong>Quận 7, TP. HCM</strong>
                                    <span>Sân Phú Mỹ Hưng</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Card -->
                    <div class="sidebar-card">
                        <h3 class="sidebar-card-title">Lịch dạy</h3>
                        <div class="schedule-list">
                            <div class="schedule-item">
                                <span class="day">Thứ 2 - Thứ 6</span>
                                <span class="time">06:00 - 08:00, 17:00 - 21:00</span>
                            </div>
                            <div class="schedule-item">
                                <span class="day">Thứ 7 - Chủ nhật</span>
                                <span class="time">06:00 - 21:00</span>
                            </div>
                        </div>
                        <p class="schedule-note">* Lịch có thể thay đổi, vui lòng liên hệ trước</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Similar Coaches -->
    <section class="similar-coaches section section-alt">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Giảng viên tương tự</h2>
                <p class="section-description">Khám phá thêm các huấn luyện viên khác trong khu vực của bạn</p>
            </div>
            <div class="coaches-grid small">
                @forelse($similarInstructors as $similar)
                    <div class="coach-card">
                        <div class="coach-image">
                            @if ($similar->image)
                                <img src="{{ asset('storage/' . $similar->image) }}" alt="{{ $similar->name }}">
                            @else
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Cdefs%3E%3ClinearGradient id='gs1' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23FF8E53;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%23FE6B8B;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23gs1)' width='300' height='300'/%3E%3Ccircle cx='150' cy='120' r='60' fill='rgba(255,255,255,0.3)'/%3E%3Cellipse cx='150' cy='250' rx='80' ry='60' fill='rgba(255,255,255,0.3)'/%3E%3C/svg%3E"
                                    alt="{{ $similar->name }}">
                            @endif
                            <div class="coach-rating-badge">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                </svg>
                                <span>{{ number_format($similar->rating ?? 5, 1) }}</span>
                            </div>
                        </div>
                        <div class="coach-content">
                            <h3 class="coach-name"><a
                                    href="{{ route('instructors.detail', $similar->id) }}">{{ $similar->name }}</a></h3>
                            <div class="coach-experience">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                                </svg>
                                <span>{{ $similar->experience_years ?? 0 }} năm kinh nghiệm</span>
                            </div>
                            <div class="coach-location">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <span>{{ $similar->province->name ?? 'Hà Nội' }}</span>
                            </div>
                            <div class="coach-actions">
                                <a href="{{ route('instructors.detail', $similar->id) }}"
                                    class="btn btn-primary btn-sm">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p>Không có giảng viên tương tự</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script>
        // Favorite button toggle
        const favoriteBtn = document.querySelector('.btn-favorite');
        favoriteBtn?.addEventListener('click', () => {
            favoriteBtn.classList.toggle('active');
        });

        // Package selection
        const packageOptions = document.querySelectorAll('.package-option input');
        packageOptions.forEach(option => {
            option.addEventListener('change', () => {
                document.querySelectorAll('.package-option').forEach(p => p.classList.remove('selected'));
                option.closest('.package-option').classList.add('selected');
            });
        });
    </script>
@endsection
