@extends('layouts.front')

@php
$stadiumImage = $stadium->getFirstMediaUrl('banner') ?? asset('assets/images/court_default.svg');
@endphp

@section('seo')
    <title>{{ $stadium->name }} - Sân Pickleball | OnePickleball</title>
    <meta name="description" content="Sân {{ $stadium->name }} - {{ $stadium->address }}. Đặt sân Pickleball chất lượng cao. {{ $stadium->description ? substr($stadium->description, 0, 100) : '' }}">
    <meta name="keywords" content="{{ $stadium->name }}, sân pickleball, {{ $stadium->address }}, đặt sân online">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ route('courts-detail', $stadium) }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $stadium->name }} - Sân Pickleball">
    <meta property="og:description" content="Đặt sân Pickleball tại {{ $stadium->name }} - {{ $stadium->address }}">
    <meta property="og:image" content="{{ $stadiumImage }}">
    <meta property="og:url" content="{{ route('courts-detail', $stadium) }}">
    <meta property="og:site_name" content="OnePickleball">
    <meta property="og:locale" content="vi_VN">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $stadium->name }} - Sân Pickleball">
    <meta name="twitter:description" content="Đặt sân tại {{ $stadium->name }}">
    <meta name="twitter:image" content="{{ $stadiumImage }}">
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tournament-detail.css') }}?v=1.1">
    <link rel="stylesheet" href="{{ asset('assets/css/courts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/court-detail.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/gallery-lightbox.css') }}">
    <style>
        .hidden {
            display: none;
        }

        .detail-main {
            overflow-x: auto;
        }

        .tab-content {
            min-height: auto;
            overflow: hidden;
        }

        .gallery-main img {
            border-radius: 10px;
        }

        .gallery-grid {
            display: block;
        }

        .favorite-toggle.active svg {
            color: red;
            fill: red;
        }

        .page-header-background {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(0, 217, 181, 0.1) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(0, 153, 204, 0.1) 0%, transparent 50%);
            z-index: 0;
        }
        .container {
            position: relative;
            z-index: 1;
        }
    </style>
@endsection

@section('content')
    <section class="court-hero">
        <div class="page-header-background"></div>
        <div class="container">
            <div class="breadcrumb">
                <a href="/">Trang chủ</a>
                <span class="separator">/</span>
                <a href="{{ route('courts') }}">Sân thi đấu</a>
                <span class="separator">/</span>
                <span>{{ $stadium->name }}</span>
            </div>

            <div class="court-hero-content">
                <div class="court-hero-left">
                    <div class="court-hero-badges">
                        @if ($stadium->is_featured)
                            <span class="hero-badge badge-featured">⭐ Nổi bật</span>
                        @endif
                        @if ($stadium->is_premium)
                            <span class="hero-badge badge-premium">👑 Premium</span>
                        @endif
                        @if ($stadium->is_verified)
                            <span class="hero-badge badge-verified">✓ Đã xác minh</span>
                        @endif
                    </div>

                    <h1 class="court-hero-title">{{ $stadium->name }}</h1>

                    <div class="court-hero-meta">
                        <div class="hero-rating">
                            <span class="rating-star">⭐</span>
                            <span class="rating-value">4.8</span>
                            <span class="rating-count">(128 đánh giá)</span>
                        </div>
                        <div class="hero-location">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <span>{{ $stadium->address }}</span>
                        </div>
                        <div class="hero-status">
                            <span class="status-dot status-open"></span>
                            @php
                                $currentTime = now()->format('H:i');
                                $isOpen = $currentTime >= $stadium->start_time && $currentTime <= $stadium->closing_time;
                            @endphp
                            <span>
                                @if ($isOpen)
                                    Đang mở cửa
                                @else
                                    Đã đóng cửa
                                @endif • Đóng cửa lúc {{ $stadium->closing_time }}
                            </span>
                        </div>
                    </div>

                    <div class="court-hero-actions">
                        <button class="btn btn-primary btn-lg"
                            onclick="window.location.href='{{ route('booking', $stadium) }}'">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            Đặt sân ngay
                        </button>
                        <button
                            class="btn btn-secondary btn-lg favorite-toggle @if ($stadium->is_favorited) active @endif"
                            onclick="toggleFavoriteStadium({{ $stadium->id }})">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path
                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                            </svg>
                            <span class="favorite-text">
                                @if ($stadium->is_favorited)
                                    Đã yêu thích
                                @else
                                    Yêu thích
                                @endif
                            </span>
                        </button>
                        <a target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-lg"
                            href="https://www.facebook.com/sharer/sharer.php?u={{url()->current()}}">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="18" cy="5" r="3" />
                                <circle cx="6" cy="12" r="3" />
                                <circle cx="18" cy="19" r="3" />
                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                            </svg>
                            Chia sẻ
                        </a>
                    </div>
                </div>

                <div class="court-hero-right">
                    <div class="price-card">
                        <div class="price-header">
                            <span class="price-label">Giá thuê mỗi giờ</span>
                            @if ($allPrices['min_price'] == 0 || $allPrices['max_price'] == 0)
                                <div class="price-range">Liên hệ</div>
                            @else
                                <div class="price-range">{{ number_format($allPrices['min_price'], 0, ',', '.') }}đ -
                                    {{ number_format($allPrices['max_price'], 0, ',', '.') }}đ</div>
                            @endif
                        </div>
                        {{-- <div class="price-note">mỗi giờ</div> --}}
                        {{-- <div class="price-breakdown">
                            <div class="price-item">
                                <span>Giờ sáng (5h-11h)</span>
                                <span class="price">150.000đ</span>
                            </div>
                            <div class="price-item">
                                <span>Giờ chiều (11h-17h)</span>
                                <span class="price">200.000đ</span>
                            </div>
                            <div class="price-item">
                                <span>Giờ tối (17h-23h)</span>
                                <span class="price">300.000đ</span>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </section>



    <!-- Gallery Lightbox Modal -->
    <div id="galleryLightbox" class="gallery-lightbox">
        <div class="lightbox-container">
            <button class="lightbox-close" onclick="closeLightbox()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>

            <button class="lightbox-nav prev" onclick="prevImage()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
            </button>

            <img id="lightboxImage" class="lightbox-image" src="" alt="">

            <button class="lightbox-nav next" onclick="nextImage()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </button>

            <div class="lightbox-counter">
                <span id="currentIndex">1</span> / <span id="totalImages">1</span>
            </div>

            <div class="lightbox-thumbnails" id="lightboxThumbnails"></div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="court-detail-section section">
        <div class="container">
            <div class="detail-layout">
                <!-- Main Content -->
                <div class="detail-main">
                    <!-- Gallery Section -->
                    @if ($stadium->hasMedia('gallery') || $stadium->hasMedia('banner'))
                        <section class="gallery-section">
                            <div class="container">
                                <div class="gallery-grid">
                                    <div class="gallery-main">
                                        @php
                                            $imageUrl = $stadium->getFirstMediaUrl('banner');
                                        @endphp
                                        <img src="{{ $imageUrl }}" alt="{{ $stadium->name }}">
                                        <button class="gallery-view-all" onclick="openGalleryLightbox()">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <rect x="3" y="3" width="18" height="18" rx="2"
                                                    ry="2" />
                                                <circle cx="8.5" cy="8.5" r="1.5" />
                                                <polyline points="21 15 16 10 5 21" />
                                            </svg>
                                            Xem tất cả ảnh
                                        </button>
                                    </div>
                                    <div class="gallery-thumbnails hidden">
                                        @foreach ($stadium->getMedia('gallery') as $image)
                                            {{-- @continue($loop->index == 0) --}}
                                            <img src="{{ $image->getUrl() }}" alt="{{ $stadium->name }} - Gallery"
                                                class="gallery-thumb" onclick="openGalleryLightbox({{ $loop->index }})">
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </section>
                    @endif

                    <!-- Tab Navigation -->
                    <div class="tab-navigation">
                        <button class="tab-btn active" data-tab="overview">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="16" x2="12" y2="12" />
                                <line x1="12" y1="8" x2="12.01" y2="8" />
                            </svg>
                            Tổng quan
                        </button>
                        <button class="tab-btn" data-tab="facilities">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                            </svg>
                            Tiện ích
                        </button>
                        <button class="tab-btn" data-tab="reviews">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                            Đánh giá ({{ $stadium->reviews->count() }})
                        </button>
                        <button class="tab-btn" data-tab="rules">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                            Quy định
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Overview Tab -->
                        <div class="tab-pane active" id="overview">
                            <div class="content-card">
                                <h2 class="content-title">Giới thiệu</h2>
                                <div class="content-text">
                                    <p>{{ $stadium->description ?? 'Sân pickleball chất lượng cao với đầy đủ tiện ích hiện đại.' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Quick Info Grid -->
                            <div class="quick-info-grid">
                                <div class="info-box">
                                    <div class="info-icon">🏟️</div>
                                    <div class="info-content">
                                        <div class="info-label">Số sân</div>
                                        <div class="info-value">{{ $stadium->courts_count }} sân</div>
                                    </div>
                                </div>
                                <div class="info-box">
                                    <div class="info-icon">⏰</div>
                                    <div class="info-content">
                                        <div class="info-label">Giờ mở cửa</div>
                                        <div class="info-value">{{ $stadium->opening_time . ' - ' . $stadium->closing_time }}</div>
                                    </div>
                                </div>
                                <div class="info-box">
                                    <div class="info-icon">🎾</div>
                                    <div class="info-content">
                                        <div class="info-label">Mặt sân</div>
                                        <div class="info-value">Acrylic chuyên dụng</div>
                                    </div>
                                </div>
                                <div class="info-box">
                                    <div class="info-icon">📞</div>
                                    <div class="info-content">
                                        <div class="info-label">Liên hệ</div>
                                        <div class="info-value">{{ $stadium->phone ?? '--' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Location Map -->
                            <div class="content-card">
                                <h2 class="content-title">Vị trí</h2>
                                <div class="location-map">
                                    <div class="map-placeholder">
                                        {!! $stadium->maps_address !!}
                                    </div>
                                    <div class="location-details">
                                        <h4>Địa chỉ chi tiết</h4>
                                        <p>{{ $stadium->address }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Facilities Tab -->
                        <div class="tab-pane" id="facilities">
                            <div class="content-card">
                                <h2 class="content-title">Tiện ích & Dịch vụ</h2>
                                <div class="facilities-grid">
                                    <div class="facility-item">
                                        <div class="facility-icon">🏟️</div>
                                        <h4>{{ $stadium->courts_count }} Sân thi đấu</h4>
                                        <p>Sân chuẩn quốc tế với mặt sân Acrylic chuyên dụng</p>
                                    </div>
                                    @if ($stadium->amenities)
                                        @foreach (is_array($stadium->amenities) ? $stadium->amenities : json_decode($stadium->amenities, true) as $amenity)
                                            <div class="facility-item">
                                                <div class="facility-icon">
                                                    @if (strpos($amenity, '🚿') !== false)
                                                        🚿
                                                    @elseif(strpos($amenity, '🅿️') !== false)
                                                        🅿️
                                                    @elseif(strpos($amenity, '☕') !== false)
                                                        ☕
                                                    @elseif(strpos($amenity, '🏪') !== false)
                                                        🏪
                                                    @elseif(strpos($amenity, '❄️') !== false)
                                                        ❄️
                                                    @elseif(strpos($amenity, '🎾') !== false)
                                                        🎾
                                                    @elseif(strpos($amenity, '📱') !== false)
                                                        📱
                                                    @elseif(strpos($amenity, '🔒') !== false)
                                                        🔒
                                                    @elseif(strpos($amenity, '👨‍🏫') !== false)
                                                        👨‍🏫
                                                    @elseif(strpos($amenity, '📸') !== false)
                                                        📸
                                                    @elseif(strpos($amenity, '🎵') !== false)
                                                        🎵
                                                    @else
                                                        ✓
                                                    @endif
                                                </div>
                                                <h4>{{ str_replace(['�', '�️', '☕', '🏪', '❄️', '🎾', '📱', '🔒', '👨‍🏫', '📸', '🎵'], '', $amenity) }}
                                                </h4>
                                                <p>Dịch vụ chất lượng cao</p>
                                            </div>
                                        @endforeach
                                    @endif

                                </div>
                            </div>
                        </div>

                        <!-- Reviews Tab -->
                        <div class="tab-pane" id="reviews">
                            <div class="content-card">
                                <!-- Review Form (Only for logged in users) -->
                                @auth
                                    <div class="review-form-container" id="reviewFormContainer" style="display: none;">
                                        <h3 class="form-title">Viết đánh giá của bạn</h3>
                                        <form id="reviewForm" class="review-form">
                                            <div class="form-group">
                                                <label class="form-label">Đánh giá (1-5 sao)</label>
                                                <div class="star-rating" id="starRating">
                                                    <span class="star" data-value="1">★</span>
                                                    <span class="star" data-value="2">★</span>
                                                    <span class="star" data-value="3">★</span>
                                                    <span class="star" data-value="4">★</span>
                                                    <span class="star" data-value="5">★</span>
                                                </div>
                                                <input type="hidden" id="ratingInput" name="rating" value="">
                                                <span class="rating-text">Vui lòng chọn mức đánh giá</span>
                                            </div>

                                            <div class="form-group">
                                                <label for="commentInput" class="form-label">Nhận xét (Tùy chọn)</label>
                                                <textarea id="commentInput" name="comment" class="form-input" rows="4"
                                                    placeholder="Chia sẻ trải nghiệm của bạn tại sân..."></textarea>
                                                <small class="form-hint"><span id="charCount">0</span>/1000 ký tự</small>
                                            </div>

                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                                                <button type="button" class="btn btn-outline"
                                                    onclick="toggleReviewForm()">Hủy</button>
                                            </div>
                                        </form>
                                    </div>
                                @endauth

                                <div class="reviews-header">
                                    <div>
                                        <h2 class="content-title">Đánh giá từ khách hàng</h2>
                                        <p class="reviews-summary">Dựa trên <span id="totalReviewCount">0</span> đánh giá
                                        </p>
                                    </div>
                                    @auth
                                        <button class="btn btn-primary" id="writeReviewBtn" onclick="toggleReviewForm()">Viết
                                            đánh giá</button>
                                    @endauth
                                    @guest
                                        <a href="{{ route('login') }}" class="btn btn-primary">Đăng nhập để đánh giá</a>
                                    @endguest
                                </div>

                                <!-- Rating Overview -->
                                <div class="rating-overview" id="ratingOverview">
                                    <div class="rating-score">
                                        <div class="score-number" id="avgRating">0.0</div>
                                        <div class="score-stars" id="scoreStars">☆☆☆☆☆</div>
                                        <div class="score-label" id="scoreLabel">Chưa có đánh giá</div>
                                    </div>
                                    <div class="rating-breakdown" id="ratingBreakdown">
                                        <!-- Dynamically populated -->
                                    </div>
                                </div>

                                <!-- Filter/Sort Controls -->
                                <!-- <div class="reviews-controls">
                                        <select id="sortReviews" class="form-input">
                                            <option value="recent">Mới nhất</option>
                                            <option value="helpful">Hữu ích nhất</option>
                                            <option value="rating_high">Đánh giá cao nhất</option>
                                            <option value="rating_low">Đánh giá thấp nhất</option>
                                        </select>
                                    </div> -->

                                <!-- Reviews List -->
                                <div class="reviews-list" id="reviewsList">
                                    <div class="review-item">
                                        <div class="review-header">
                                            <div class="reviewer-info">
                                                <div class="reviewer-avatar">NT</div>
                                                <div>
                                                    <div class="reviewer-name">Nguyễn Văn Tuấn</div>
                                                    <div class="review-date">2 tuần trước</div>
                                                </div>
                                            </div>
                                            <div class="review-rating">⭐⭐⭐⭐⭐</div>
                                        </div>
                                        <div class="review-content">
                                            <p>Sân rất đẹp và sạch sẽ, nhân viên thân thiện. Mặt sân chất lượng tốt, phù hợp
                                                cho cả người mới và người chơi lâu năm. Sẽ quay lại chơi tiếp!</p>
                                        </div>
                                        <div class="review-helpful">
                                            <button class="helpful-btn">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path
                                                        d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3">
                                                    </path>
                                                </svg>
                                                Hữu ích (12)
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <button class="btn btn-outline btn-block load-more-reviews" id="loadMoreBtn"
                                    style="display: none;">Xem thêm đánh giá</button>
                            </div>
                        </div>

                        <!-- Rules Tab -->
                        <div class="tab-pane" id="rules">
                            <div class="content-card">
                                <h2 class="content-title">Quy định sân</h2>
                                <div class="rules-list">
                                    <div class="rule-item">
                                        <span class="rule-number">1</span>
                                        <div class="rule-content">
                                            <h4>Đặt sân trước</h4>
                                            <p>Khách hàng cần đặt sân trước ít nhất 2 giờ. Đặt qua website hoặc gọi điện để
                                                được hỗ trợ tốt nhất.</p>
                                        </div>
                                    </div>
                                    <div class="rule-item">
                                        <span class="rule-number">2</span>
                                        <div class="rule-content">
                                            <h4>Đúng giờ</h4>
                                            <p>Vui lòng có mặt đúng giờ đã đặt. Nếu đến muộn quá 15 phút mà không báo trước,
                                                sân có thể được chuyển cho khách khác.</p>
                                        </div>
                                    </div>
                                    <div class="rule-item">
                                        <span class="rule-number">3</span>
                                        <div class="rule-content">
                                            <h4>Trang phục</h4>
                                            <p>Mặc trang phục thể thao phù hợp. Giày thể thao với đế không để lại vết đen
                                                trên sân.</p>
                                        </div>
                                    </div>
                                    <div class="rule-item">
                                        <span class="rule-number">4</span>
                                        <div class="rule-content">
                                            <h4>Giữ gìn vệ sinh</h4>
                                            <p>Không mang đồ ăn, thức uống vào sân. Sử dụng khu vực canteen để dùng bữa.</p>
                                        </div>
                                    </div>
                                    <div class="rule-item">
                                        <span class="rule-number">5</span>
                                        <div class="rule-content">
                                            <h4>Hủy sân</h4>
                                            <p>Hủy sân trước 24 giờ được hoàn lại 100% tiền. Hủy trong vòng 24 giờ sẽ không
                                                được hoàn tiền.</p>
                                        </div>
                                    </div>
                                    <div class="rule-item">
                                        <span class="rule-number">6</span>
                                        <div class="rule-content">
                                            <h4>An toàn</h4>
                                            <p>Chủ động khởi động kỹ trước khi chơi. Báo ngay cho nhân viên nếu có tai nạn
                                                hoặc chấn thương.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <aside class="detail-sidebar">
                    <!-- Booking Calendar -->
                    <div class="sidebar-card">
                        <h3 class="card-title">Đặt sân nhanh</h3>
                        <div class="quick-booking">
                            <div class="booking-date">
                                <label>Chọn ngày</label>
                                <input type="date" class="booking-input" min="2025-11-12">
                            </div>
                            <div class="booking-time">
                                <label>Chọn giờ</label>
                                <select class="booking-input bg-white">
                                    <option value="">Chọn giờ</option>
                                    <option value="05:00">05:00 - 06:00</option>
                                    <option value="06:00">06:00 - 07:00</option>
                                    <option value="07:00">07:00 - 08:00</option>
                                    <option value="18:00">18:00 - 19:00</option>
                                    <option value="19:00">19:00 - 20:00</option>
                                    <option value="20:00">20:00 - 21:00</option>
                                </select>
                            </div>
                            <div class="booking-duration">
                                <label>Thời gian</label>
                                <select class="booking-input bg-white">
                                    <option value="1">1 giờ</option>
                                    <option value="2">2 giờ</option>
                                    <option value="3">3 giờ</option>
                                </select>
                            </div>
                            <button class="btn btn-primary btn-block"
                                onclick="window.location.href='{{ route('booking', $stadium) }}'">
                                Tiếp tục đặt sân
                            </button>
                        </div>
                    </div>

                    <!-- Contact Card -->
                    <div class="sidebar-card">
                        <h3 class="card-title">Liên hệ</h3>
                        <div class="contact-list">
                            <div class="contact-item">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path
                                        d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z" />
                                </svg>
                                <div>
                                    <div class="contact-label">Điện thoại</div>
                                    <div class="contact-value">{{ $stadium->phone }}</div>
                                </div>
                            </div>
                            <div class="contact-item">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <div>
                                    <div class="contact-label">Email</div>
                                    <div class="contact-value">{{ $stadium->email }}</div>
                                </div>
                            </div>
                        </div>
                        <a href="https://zalo.me/{{ $stadium->phone }}" target="_blank"
                            class="btn btn-outline btn-block">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                            </svg>
                            Chat ngay
                        </a>
                    </div>

                    <!-- Related Courts -->
                    <div class="sidebar-card">
                        <h3 class="card-title">Sân gần đây</h3>
                        <div class="related-list">
                            @foreach ($relatedStadiums as $item)
                                <a href="{{ route('courts-detail', $item) }}" class="related-item">
                                    <div class="related-image">
                                        <img src="{{ $item->getFirstMediaUrl('banner') }}" alt="{{ $item->name }}">
                                    </div>
                                    <div class="related-content">
                                        <h4>{{ $item->name }}</h4>
                                        <p>⭐ 4.9 • 2.5 km</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script src="{{ asset('assets/js/tournament-detail.js') }}?v=1.1"></script>
    <script>
        // ==================== Reviews Feature ====================
        let reviewsCurrentPage = 1;
        let reviewsPerPage = 10;
        let currentSortOption = 'recent';
        const STADIUM_ID = {{ $stadium->id }};

        document.addEventListener('DOMContentLoaded', function() {
            loadReviewsSummary();
            loadReviews();
            setupReviewFormHandlers();
            setupReviewSortHandler();
        });

        // ==================== Review Form Handlers ====================
        function toggleReviewForm() {
            const formContainer = document.getElementById('reviewFormContainer');
            if (formContainer) {
                formContainer.style.display = formContainer.style.display === 'none' ? 'block' : 'none';
                if (formContainer.style.display === 'block') {
                    formContainer.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }
        }

        function setupReviewFormHandlers() {
            const starRating = document.getElementById('starRating');
            if (!starRating) return;

            const ratingInput = document.getElementById('ratingInput');
            const ratingText = document.querySelector('.rating-text');
            const commentInput = document.getElementById('commentInput');
            const charCount = document.getElementById('charCount');
            const reviewForm = document.getElementById('reviewForm');

            // Star rating selection
            starRating.querySelectorAll('.star').forEach(star => {
                star.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    ratingInput.value = value;

                    starRating.querySelectorAll('.star').forEach((s, idx) => {
                        s.classList.toggle('active', idx < value);
                    });

                    ratingText.textContent = `Bạn đánh giá: ${value} sao`;
                    ratingText.style.color = '#FFB800';
                });

                star.addEventListener('mouseover', function() {
                    const value = this.getAttribute('data-value');
                    starRating.querySelectorAll('.star').forEach((s, idx) => {
                        s.style.opacity = idx < value ? '1' : '0.3';
                    });
                });
            });

            starRating.addEventListener('mouseleave', function() {
                starRating.querySelectorAll('.star').forEach(s => {
                    s.style.opacity = '';
                });
            });

            // Character counter
            if (commentInput) {
                commentInput.addEventListener('input', function() {
                    charCount.textContent = this.value.length;
                });
            }

            // Form submission
            if (reviewForm) {
                reviewForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitReview();
                });
            }
        }

        async function submitReview() {
            const rating = document.getElementById('ratingInput').value;
            const comment = document.getElementById('commentInput').value;
            const submitBtn = document.querySelector('#reviewForm button[type="submit"]');

            if (!rating) {
                toastr.error('Vui lòng chọn mức đánh giá');
                return;
            }

            if (comment.length > 1000) {
                toastr.error('Nhận xét không được vượt quá 1000 ký tự');
                return;
            }

            try {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Đang gửi...';

                const formData = new FormData();
                formData.append('rating', parseInt(rating));
                formData.append('comment', comment || null);
                formData.append('stadium_id', STADIUM_ID);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch('/reviews/store', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Lỗi khi gửi đánh giá');
                }

                toastr.success('Đánh giá của bạn đã được gửi thành công!');

                // Reset form
                document.getElementById('reviewForm').reset();
                document.getElementById('ratingInput').value = '';
                document.querySelectorAll('#starRating .star').forEach(s => s.classList.remove('active'));
                document.getElementById('charCount').textContent = '0';

                toggleReviewForm();
                reviewsCurrentPage = 1;
                loadReviewsSummary();
                loadReviews();

            } catch (error) {
                toastr.error(error.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Gửi đánh giá';
            }
        }

        // ==================== Reviews Display ====================
        async function loadReviewsSummary() {
            try {
                const response = await fetch(`/reviews/summary/${STADIUM_ID}`);
                const data = await response.json();
                if (!response.ok) throw new Error(data.message);
                updateRatingSummary(data.data);
            } catch (error) {
                console.error('Error loading reviews summary:', error);
            }
        }

        function updateRatingSummary(summary) {
            const avgRating = summary.average_rating || 0;
            const totalReviews = summary.total_reviews || 0;
            const distribution = summary.distribution || {};

            document.getElementById('avgRating').textContent = avgRating.toFixed(1);
            document.getElementById('totalReviewCount').textContent = totalReviews;

            const fullStars = Math.round(avgRating);
            const stars = '⭐'.repeat(fullStars) + '☆'.repeat(5 - fullStars);
            document.getElementById('scoreStars').textContent = stars;

            let label = 'Chưa có đánh giá';
            if (avgRating >= 4.5) label = 'Xuất sắc';
            else if (avgRating >= 4) label = 'Rất tốt';
            else if (avgRating >= 3) label = 'Tốt';
            else if (avgRating >= 2) label = 'Trung bình';
            else if (avgRating > 0) label = 'Tệ';

            document.getElementById('scoreLabel').textContent = label;

            const breakdownHtml = [5, 4, 3, 2, 1].map(rating => {
                const count = distribution[rating] || 0;
                const percent = totalReviews > 0 ? (count / totalReviews) * 100 : 0;
                return `
                    <div class="rating-row">
                        <span class="rating-label">${rating} ⭐</span>
                        <div class="rating-bar">
                            <div class="rating-fill" style="width: ${percent}%"></div>
                        </div>
                        <span class="rating-count">${count}</span>
                    </div>
                `;
            }).join('');

            document.getElementById('ratingBreakdown').innerHTML = breakdownHtml;
        }

        async function loadReviews() {
            try {
                const params = new URLSearchParams({
                    page: reviewsCurrentPage,
                    per_page: reviewsPerPage,
                    sort: currentSortOption,
                    filter: 'verified'
                });

                const response = await fetch(`/reviews/stadium/${STADIUM_ID}?${params}`);
                const data = await response.json();
                if (!response.ok) throw new Error(data.message);

                displayReviews(data.data);

                const loadMoreBtn = document.getElementById('loadMoreBtn');
                if (loadMoreBtn) {
                    loadMoreBtn.style.display = data.pagination?.has_next_page ? 'block' : 'none';
                }

            } catch (error) {
                console.error('Error loading reviews:', error);
                const reviewsList = document.getElementById('reviewsList');
                if (reviewsList) {
                    reviewsList.innerHTML = `
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <p>Không thể tải đánh giá</p>
                        </div>
                    `;
                }
            }
        }

        function displayReviews(reviews) {
            const reviewsList = document.getElementById('reviewsList');

            if (!reviews || reviews.length === 0) {
                if (reviewsCurrentPage === 1) {
                    reviewsList.innerHTML = `
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <p>Chưa có đánh giá nào</p>
                        </div>
                    `;
                }
                return;
            }

            const reviewsHtml = reviews.map(review => createReviewElement(review)).join('');

            if (reviewsCurrentPage === 1) {
                reviewsList.innerHTML = reviewsHtml;
            } else {
                reviewsList.innerHTML += reviewsHtml;
            }
        }

        function createReviewElement(review) {
            // Parse created_at timestamp
            let formattedDate = 'vừa xong';
            try {
                // Handle both ISO string and timestamp formats
                const timestamp = review.created_at.match(/^\d+$/) ?
                    parseInt(review.created_at) * 1000 :
                    new Date(review.created_at).getTime();
                const date = new Date(timestamp);
                formattedDate = formatTimeAgo(date);
            } catch (e) {
                console.error('Error parsing date:', review.created_at);
            }

            const stars = '⭐'.repeat(review.rating) + '☆'.repeat(5 - review.rating);

            const nameParts = (review.user?.name || 'A').split(' ');
            const initials = nameParts.map(p => p[0]).join('').toUpperCase().substring(0, 2);

            let actionButtons = '';
            if (review.is_owner) {
                actionButtons = `
                    <div class="review-actions">
                        <button class="action-btn delete-btn" onclick="deleteReview(${review.id})" title="Xóa">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                        </button>
                    </div>
                `;
            }

            return `
                <div class="review-item" data-review-id="${review.id}">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">${initials}</div>
                            <div>
                                <div class="reviewer-name">${review.user?.name || 'Ẩn danh'}</div>
                                <div class="review-date">${formattedDate}</div>
                                
                            </div>
                        </div>
                        <div class="review-rating">${stars}</div>
                    </div>
                    <div class="review-content">
                        <p>${escapeHtml(review.comment || '(Không có nhận xét)')}</p>
                    </div>
                    <div class="review-footer">
                        <button class="helpful-btn" onclick="markHelpful(${review.id})">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                            </svg>
                            Hữu ích <span class="helpful-count">${review.helpful_count || 0}</span>
                        </button>
                        ${actionButtons}
                    </div>
                </div>
            `;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatTimeAgo(date) {
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);

            if (seconds < 60) return 'vừa xong';
            if (seconds < 3600) return `${Math.floor(seconds / 60)} phút trước`;
            if (seconds < 86400) return `${Math.floor(seconds / 3600)} giờ trước`;
            if (seconds < 604800) return `${Math.floor(seconds / 86400)} ngày trước`;
            if (seconds < 2592000) return `${Math.floor(seconds / 604800)} tuần trước`;
            return `${Math.floor(seconds / 2592000)} tháng trước`;
        }

        function setupReviewSortHandler() {
            const sortSelect = document.getElementById('sortReviews');
            if (sortSelect) {
                sortSelect.addEventListener('change', function(e) {
                    currentSortOption = e.target.value;
                    reviewsCurrentPage = 1;
                    loadReviews();
                });
            }
        }

        // ==================== Review Actions ====================
        async function markHelpful(reviewId) {
            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch(`/reviews/${reviewId}/helpful`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) throw new Error(data.message);

                const reviewItem = document.querySelector(`[data-review-id="${reviewId}"]`);
                if (reviewItem) {
                    const helpfulCount = reviewItem.querySelector('.helpful-count');
                    if (helpfulCount) {
                        helpfulCount.textContent = data.data?.helpful_count || 0;
                    }
                }

                toastr.success('Cảm ơn phản hồi của bạn!');

            } catch (error) {
                toastr.error(error.message);
            }
        }

        async function deleteReview(reviewId) {
            if (!confirm('Bạn chắc chắn muốn xóa đánh giá này?')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('_method', 'DELETE');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch(`/reviews/${reviewId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) throw new Error(data.message);

                toastr.success('Đánh giá đã được xóa');

                const reviewItem = document.querySelector(`[data-review-id="${reviewId}"]`);
                if (reviewItem) {
                    reviewItem.style.opacity = '0';
                    reviewItem.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => reviewItem.remove(), 300);
                }

                loadReviewsSummary();

            } catch (error) {
                toastr.error(error.message);
            }
        }

        // Load more reviews
        document.addEventListener('DOMContentLoaded', function() {
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function() {
                    reviewsCurrentPage++;
                    loadReviews();
                });
            }
        });

        // Add animations
        if (!document.querySelector('style[data-review-animations]')) {
            const style = document.createElement('style');
            style.setAttribute('data-review-animations', 'true');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(400px); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(400px); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
    </script>

    <script>
        // Gallery Lightbox JavaScript
        let currentImageIndex = 0;
        let galleryImages = [];

        function initGalleryLightbox() {
            // Collect all gallery images
            const galleryThumbs = document.querySelectorAll('.gallery-thumbnails .gallery-thumb');
            const bannerImg = document.querySelector('.gallery-main img');

            // Start with banner image
            if (bannerImg && bannerImg.src !== '{{ asset('assets/images/court_default.svg') }}') {
                galleryImages.push(bannerImg.src);
            }

            // Add thumbnail images
            galleryThumbs.forEach(thumb => {
                galleryImages.push(thumb.src);
            });

            // Update total images count
            document.getElementById('totalImages').textContent = galleryImages.length;

            // Populate lightbox thumbnails
            const lightboxThumbnails = document.getElementById('lightboxThumbnails');
            galleryImages.forEach((img, index) => {
                const thumbDiv = document.createElement('div');
                thumbDiv.className = 'lightbox-thumbnail' + (index === 0 ? ' active' : '');
                thumbDiv.innerHTML = `<img src="${img}" alt="Image ${index + 1}">`;
                thumbDiv.addEventListener('click', () => showImage(index));
                lightboxThumbnails.appendChild(thumbDiv);
            });
        }

        function openGalleryLightbox(startIndex = 0) {
            if (galleryImages.length === 0) {
                initGalleryLightbox();
            }

            currentImageIndex = startIndex;
            showImage(startIndex);
            document.getElementById('galleryLightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('galleryLightbox').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function showImage(index) {
            if (galleryImages.length === 0) return;

            currentImageIndex = (index + galleryImages.length) % galleryImages.length;

            // Update image
            document.getElementById('lightboxImage').src = galleryImages[currentImageIndex];

            // Update counter
            document.getElementById('currentIndex').textContent = currentImageIndex + 1;

            // Update thumbnail active state
            document.querySelectorAll('.lightbox-thumbnail').forEach((thumb, idx) => {
                thumb.classList.toggle('active', idx === currentImageIndex);
            });

            // Scroll thumbnail into view
            const activeThumbnail = document.querySelectorAll('.lightbox-thumbnail')[currentImageIndex];
            if (activeThumbnail) {
                activeThumbnail.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'center'
                });
            }
        }

        function nextImage() {
            showImage(currentImageIndex + 1);
        }

        function prevImage() {
            showImage(currentImageIndex - 1);
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            const lightbox = document.getElementById('galleryLightbox');
            if (lightbox.classList.contains('active')) {
                if (e.key === 'ArrowRight') nextImage();
                if (e.key === 'ArrowLeft') prevImage();
                if (e.key === 'Escape') closeLightbox();
            }
        });

        // Close on background click
        document.getElementById('galleryLightbox').addEventListener('click', (e) => {
            if (e.target.id === 'galleryLightbox') {
                closeLightbox();
            }
        });

        // Toggle favorite stadium
        async function toggleFavoriteStadium(stadiumId) {
            try {
                const response = await fetch(`/stadiums/${stadiumId}/toggle-favorite`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 401) {
                        window.location.href = '/login';
                        return;
                    }
                    throw new Error(data.message || 'Lỗi khi cập nhật yêu thích');
                }

                const button = document.querySelector('.favorite-toggle');
                const isFavorited = data.favorited || false;

                if (isFavorited) {
                    button.classList.add('active');
                    button.querySelector('.favorite-text').textContent = 'Đã yêu thích';
                    toastr.success(data.message || 'Đã thêm vào yêu thích');
                } else {
                    button.classList.remove('active');
                    button.querySelector('.favorite-text').textContent = 'Yêu thích';
                    toastr.success(data.message || 'Đã xóa khỏi yêu thích');
                }

            } catch (error) {
                toastr.error(error.message);
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initGalleryLightbox();

            // Tab switching functionality
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabPanes = document.querySelectorAll('.tab-pane');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');

                    // Remove active class from all buttons and panes
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabPanes.forEach(pane => pane.classList.remove('active'));

                    // Add active class to clicked button and corresponding pane
                    this.classList.add('active');
                    const targetPane = document.getElementById(tabName);
                    if (targetPane) {
                        targetPane.classList.add('active');
                    }
                });
            });
        });
    </script>
@endsection
