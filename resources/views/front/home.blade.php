@extends('layouts.front')

@section('css')
    <style>
        .hero {
            background: linear-gradient(135deg, rgba(10 162 137 0.3) 0%, rgba(0, 168, 150, 0.3) 100%);
        }
        .hero-background {
            background-image: url('{{ asset('assets/images/banner.jpeg') }}');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
        }
        .hero-title {
            color: #fff;
            line-height: 1.5;
        }
        .hero-description {
            color: #fff;
        }

        /* FAQ Section Styles */
        .faq-section {
            background: var(--bg-white);
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
    <section class="hero" id="home">
        <div class="hero-background"></div>
        <div class="container">
            <div class="hero-content">
                <span class="hero-badge">Cộng đồng Pickleball #1 Việt Nam</span>
                <h1 class="hero-title">
                    Chào mừng đến với<br>
                    <span class="gradient-text">OnePickleball</span>
                </h1>
                <p class="hero-description">
                    Nền tảng kết nối cộng đồng Pickleball hàng đầu tại Việt Nam.
                    Tìm sân, đăng ký giải đấu, kết nối đối thủ và cập nhật tin tức mới nhất.
                </p>
                <div class="hero-actions">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Tham gia ngay</a>
                    {{-- <button class="btn btn-secondary btn-lg">Tìm hiểu thêm</button> --}}
                </div>

                <!-- Stats -->
                {{-- <div class="hero-stats">
                    <div class="stat-item">
                        <h3 class="stat-number">{{ $totalMembers }}+</h3>
                        <p class="stat-label">Thành viên</p>
                    </div>
                    <div class="stat-item">
                        <h3 class="stat-number">{{ $totalStadiums }}+</h3>
                        <p class="stat-label">Sân thi đấu</p>
                    </div>
                    <div class="stat-item">
                        <h3 class="stat-number">{{ $totalTournaments }}+</h3>
                        <p class="stat-label">Giải đấu</p>
                    </div>
                    <div class="stat-item">
                        <h3 class="stat-number">{{ $totalSocial }}+</h3>
                        <p class="stat-label">Buổi Social</p>
                    </div>
                </div> --}}
            </div>
        </div>
    </section>

    <!-- Special Challenge Banner -->
    <div class="container mt-4">
        @include('front.partials._special_challenge_banner')
    </div>

    <!-- Tournaments Section -->
    <section class="tournaments section" id="tournaments">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Giải đấu</span>
                <h2 class="section-title">Các giải đấu sắp diễn ra</h2>
                <p class="section-description">Đăng ký tham gia các giải đấu Pickleball chuyên nghiệp và phong trào</p>
            </div>

            <div class="tournaments-grid">
                @forelse($upcomingTournaments as $tournament)
                    <div class="tournament-card">
                        <div class="tournament-image" onclick="window.location.href='{{ route('tournaments-detail', $tournament->slug) }}'" style="cursor: pointer;">
                            <img src="{{ $tournament->getFirstMediaUrl('banner') }}" alt="{{ $tournament->name }}" style="cursor: pointer;">
                            @php
                                $now = now();
                                $startDate = $tournament->start_date;
                                if ($now < $startDate) {
                                    $status = 'status-soon';
                                    $statusText = 'Sắp mở';
                                } else {
                                    $status = 'status-open';
                                    $statusText = 'Đang mở';
                                }
                            @endphp
                            <span class="tournament-status {{ $status }}">{{ $statusText }}</span>
                        </div>
                        <div class="tournament-content">
                            <div class="tournament-date">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                <span>{{ $tournament->start_date->format('d-m-Y') }}
                                    @if ($tournament->end_date != $tournament->start_date)
                                        đến {{ $tournament->end_date->format('d-m-Y') }}
                                    @endif
                                </span>
                            </div>
                            <h3 class="tournament-title"><a href="{{ route('tournaments-detail', $tournament->slug) }}">{{ $tournament->name }}</a></h3>
                            {{-- <p class="tournament-description">{{ Str::limit($tournament->description, 100) }}</p> --}}
                            <div class="tournament-meta">
                                <div class="meta-item">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    <span>{{ $tournament->location ?? 'Địa điểm TBD' }}</span>
                                </div>
                                <div class="meta-item">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    </svg>
                                    <span>{{ $tournament->athleteCount() }} vận động viên</span>
                                </div>
                            </div>
                            <div class="tournament-footer">
                                <span class="tournament-prize">
                                    @if ($tournament->prizes)
                                        🏆 {{ number_format($tournament->prizes, 0, ',', '.') }} VNĐ
                                    @else
                                        🏆 Giải thưởng hấp dẫn
                                    @endif
                                </span>
                                <a href="{{ route('tournaments-detail', $tournament->slug) }}"
                                    class="btn btn-primary btn-sm">
                                    @if ($tournament->is_watch == 1 || $tournament->start_date->isPast())
                                        Xem chi tiết
                                    @else
                                        Đăng ký ngay
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                        <p style="font-size: 18px; color: #666;">Hiện chưa có giải đấu nào sắp diễn ra</p>
                    </div>
                @endforelse
            </div>

            <div class="section-cta">
                <button class="btn btn-secondary">Xem tất cả giải đấu</button>
            </div>
        </div>
    </section>
    <!-- Courts Section -->
    <section class="courts section section-alt" id="courts">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Sân thi đấu</span>
                <h2 class="section-title">Tìm sân gần bạn</h2>
                <p class="section-description">Hệ thống sân pickleball chất lượng cao trên toàn quốc</p>
            </div>

            <div class="courts-grid">
                <!-- Court 1 -->
                @forelse ($featuredStadiums as $stadium)
                    @if($stadium && $stadium->id)
                    <div class="court-card">
                        <div class="court-image">
                            @php
                                $bannerUrl =
                                    $stadium->getFirstMediaUrl('banner') ?: asset('assets/images/court_default.svg');
                            @endphp
                            <img src="{{ $bannerUrl }}" alt="{{ $stadium->name }}">
                            <div class="court-overlay">
                                <a href="{{ route('courts-detail', $stadium) }}" class="btn btn-white btn-sm">Xem chi
                                    tiết</a>
                            </div>
                        </div>
                        <div class="court-content">
                            <div class="court-header">
                                <h3 class="court-name"><a href="{{ route('courts-detail', $stadium) }}">{{ $stadium->name }}</a></h3>
                                <div class="court-rating">
                                    <span class="rating-star">⭐</span>
                                    <span class="rating-value">4.8</span>
                                </div>
                            </div>
                            <div class="court-location">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <span>{{ $stadium->address }}</span>
                            </div>
                            <div class="court-features">
                                <span class="feature-tag">🏟️ {{ $stadium->courts->count() }} sân</span>
                                
                                @php
                                    $amenities = $stadium->amenities;
                                    // Decode if it's a JSON string
                                    if (is_string($amenities)) {
                                        $amenities = json_decode($amenities, true);
                                    }
                                    $amenities = is_array($amenities) ? array_slice($amenities, 0, 3) : [];
                                @endphp
                                
                                @forelse ($amenities as $amenity)
                                    <span class="feature-tag">✓ {{ $amenity }}</span>
                                @empty
                                @endforelse
                            </div>
                            <div class="court-info">
                                <div class="info-item">
                                    <span class="info-label">Giờ mở cửa:</span>
                                    <span class="info-value">{{ $stadium->opening_time . ' - ' . $stadium->closing_time }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Giá thuê:</span>
                                    <span class="info-value highlight">150.000đ - 300.000đ/giờ</span>
                                </div>
                            </div>
                            <a href="{{ route('courts-detail', $stadium) }}" class="btn btn-primary btn-block">Xem
                                chi tiết</a>
                        </div>
                    </div>
                    @endif
                @empty
                    <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                        <p style="font-size: 18px; color: #666;">Hiện chưa có sân thi đấu nào</p>
                    </div>
                @endforelse
            </div>

            <div class="section-cta">
                <a href="{{ route('courts') }}" class="btn btn-primary">Xem tất cả sân thi đấu</a>
            </div>
        </div>
    </section>

    <!-- Social Play Section -->
    {{-- <section class="social section" id="social">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Thi đấu Social</span>
                <h2 class="section-title">Tham gia cộng đồng</h2>
                <p class="section-description">Kết nối với các tay vợt cùng trình độ, giao lưu và phát triển kỹ năng</p>
            </div>

            <div class="social-grid">
                <!-- Social Event 1 -->
                <div class="social-card">
                    <div class="social-header">
                        <div class="social-day">
                            <span class="day-name">Thứ Hai</span>
                            <span class="day-date">18:00 - 21:00</span>
                        </div>
                        <span class="social-level level-beginner">Beginner</span>
                    </div>
                    <h3 class="social-title">Monday Social Play</h3>
                    <p class="social-description">Buổi chơi dành cho người mới bắt đầu, môi trường thân thiện và hỗ trợ tối
                        đa</p>
                    <div class="social-info">
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <span>Sân Rạch Chiếc, Q2, HCM</span>
                        </div>
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <span>12/20 người đã đăng ký</span>
                        </div>
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <line x1="12" y1="1" x2="12" y2="23" />
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                            <span class="price">50.000đ/người</span>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-block">Tham gia ngay</button>
                </div>
                <!-- Social Event 2 -->
                <div class="social-card">
                    <div class="social-header">
                        <div class="social-day">
                            <span class="day-name">Thứ Tư</span>
                            <span class="day-date">19:00 - 22:00</span>
                        </div>
                        <span class="social-level level-intermediate">Intermediate</span>
                    </div>
                    <h3 class="social-title">Wednesday Mix & Match</h3>
                    <p class="social-description">Đấu xoay vòng với nhiều đối thủ khác nhau, phù hợp trình độ trung bình
                    </p>
                    <div class="social-info">
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <span>Thảo Điền Sports Club, Thủ Đức</span>
                        </div>
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <span>18/24 người đã đăng ký</span>
                        </div>
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <line x1="12" y1="1" x2="12" y2="23" />
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                            <span class="price">80.000đ/người</span>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-block">Tham gia ngay</button>
                </div>
                <!-- Social Event 3 -->
                <div class="social-card">
                    <div class="social-header">
                        <div class="social-day">
                            <span class="day-name">Thứ Sáu</span>
                            <span class="day-date">18:30 - 21:30</span>
                        </div>
                        <span class="social-level level-advanced">Advanced</span>
                    </div>
                    <h3 class="social-title">Friday Night Showdown</h3>
                    <p class="social-description">Buổi chơi mức độ cao cho các tay vợt giỏi, thi đấu căng thẳng và chuyên
                        nghiệp</p>
                    <div class="social-info">
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <span>Cầu Giấy Arena, Hà Nội</span>
                        </div>
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <span>14/16 người đã đăng ký</span>
                        </div>
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <line x1="12" y1="1" x2="12" y2="23" />
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                            <span class="price">100.000đ/người</span>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-block">Tham gia ngay</button>
                </div>

            </div>

            <div class="section-cta">
                <button class="btn btn-secondary">Xem lịch đầy đủ</button>
            </div>
        </div>
    </section> --}}
    <!-- News Section -->
    <section class="news section section-alt" id="news">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Tin tức</span>
                <h2 class="section-title">Tin tức mới nhất</h2>
                <p class="section-description">Cập nhật tin tức, kiến thức và xu hướng Pickleball</p>
            </div>

            <div class="news-grid">
                <!-- News Articles -->
                @foreach ($latestNews as $news)
                    <article class="news-card">
                        <div class="news-image">
                            <img src="{{ storage_url($news->image) }}" alt="{{ $news->title }}">
                            <span class="news-category">{{ $news->category->name ?? 'Tin tức' }}</span>
                        </div>
                        <div class="news-content">
                            <div class="news-meta">
                                <span class="news-date">{{ $news->created_at->format('d \\T\\h\\á\\n\\g m, Y') }}</span>
                                <span class="news-read-time">1 phút đọc</span>
                            </div>
                            <h3 class="news-title">{{ $news->title }}</h3>
                            <p class="news-excerpt">
                                {!! Str::words(strip_tags($news->content), 20) !!}
                            </p>
                            <a href="{{ route('news.show', $news->id) }}" class="news-link">
                                Đọc tiếp
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                    <polyline points="12 5 19 12 12 19" />
                                </svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="section-cta">
                <a href="{{ route('news') }}" class="btn btn-primary">Xem tất cả tin tức</a>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section section" id="faq">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">FAQ</span>
                <h2 class="section-title">Câu hỏi thường gặp</h2>
                <p class="section-description">Giải đáp những thắc mắc phổ biến về OnePickleball</p>
            </div>

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

    <!-- CTA Section -->
    <section class="cta section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Sẵn sàng tham gia cộng đồng Pickleball?</h2>
                <p class="cta-description">
                    Đăng ký ngay để nhận thông tin về các giải đấu, sự kiện và ưu đãi đặc biệt dành riêng cho thành viên
                </p>
                <div class="cta-form">
                    <input type="email" id="ctaEmail" placeholder="Nhập email của bạn" class="cta-input">
                    <button class="btn btn-primary btn-lg" onclick="handleCtaRegister()">Đăng ký ngay</button>
                </div>
            </div>
        </div>
    </section>

    <script>
        const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};

        // FAQ Accordion Toggle
        function toggleFaq(button) {
            const faqItem = button.closest('.faq-item');
            const isActive = faqItem.classList.contains('active');

            // Close all other FAQ items (accordion behavior)
            document.querySelectorAll('.faq-item.active').forEach(item => {
                if (item !== faqItem) {
                    item.classList.remove('active');
                }
            });

            // Toggle current item
            faqItem.classList.toggle('active');
        }

        function handleCtaRegister() {
            if (isLoggedIn) {
                alert('Bạn đã có tài khoản và đã đăng nhập rồi');
                return;
            }

            const email = document.getElementById('ctaEmail').value.trim();
            
            if (!email) {
                alert('Vui lòng nhập email của bạn');
                return;
            }
            
            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Email không hợp lệ');
                return;
            }
            
            // Redirect to register page with email as query parameter
            window.location.href = '{{ route("register") }}?email=' + encodeURIComponent(email);
        }
        
        // Allow Enter key to trigger register
        document.getElementById('ctaEmail')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleCtaRegister();
            }
        });
    </script>
@endsection
