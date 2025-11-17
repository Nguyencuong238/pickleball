@extends('layouts.front')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tournament-detail.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/courts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/court-detail.css') }}">
@endsection

@section('content')
    <section class="court-hero">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.html">Trang chủ</a>
                <span class="separator">/</span>
                <a href="{{ route('courts') }}">Sân thi đấu</a>
                <span class="separator">/</span>
                <span>{{ $stadium->name }}</span>
            </div>
            
            <div class="court-hero-content">
                <div class="court-hero-left">
                    <div class="court-hero-badges">
                        @if($stadium->is_featured)
                        <span class="hero-badge badge-featured">⭐ Nổi bật</span>
                        @endif
                        @if($stadium->is_premium)
                        <span class="hero-badge badge-premium">👑 Premium</span>
                        @endif
                        @if($stadium->is_verified)
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
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>{{ $stadium->address }}</span>
                        </div>
                        <div class="hero-status">
                            <span class="status-dot status-open"></span>
                            @php
                                $openingHours = explode('-', $stadium->opening_hours);
                                $closingTime = trim($openingHours[1]);
                                $startTime = trim($openingHours[0]);
                                $currentTime = now()->format('H:i');
                                $isOpen = $currentTime >= $startTime && $currentTime <= $closingTime;  
                            @endphp
                            <span>@if($isOpen)Đang mở cửa @else Đã đóng cửa @endif • Đóng cửa lúc {{ $closingTime }}</span>
                        </div>
                    </div>

                    <div class="court-hero-actions">
                        <button class="btn btn-primary btn-lg" onclick="window.location.href='{{ route('booking') }}'">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            Đặt sân ngay
                        </button>
                        <button class="btn btn-secondary btn-lg favorite-toggle">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                            </svg>
                            Yêu thích
                        </button>
                        <button class="btn btn-secondary btn-lg">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="18" cy="5" r="3"/>
                                <circle cx="6" cy="12" r="3"/>
                                <circle cx="18" cy="19" r="3"/>
                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                            </svg>
                            Chia sẻ
                        </button>
                    </div>
                </div>

                <div class="court-hero-right">
                    <div class="price-card">
                        <div class="price-header">
                            <span class="price-label">Giá thuê</span>
                            <span class="price-range">150.000đ - 300.000đ</span>
                        </div>
                        <div class="price-note">mỗi giờ</div>
                        <div class="price-breakdown">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery-section">
        <div class="container">
            <div class="gallery-grid">
                <div class="gallery-main">
                    @php
                        $bannerUrl = $stadium->getFirstMediaUrl('banner') ?: asset('assets/images/court_default.svg');
                    @endphp
                    <img src="{{ $bannerUrl }}" alt="{{ $stadium->name }}">
                    <button class="gallery-view-all">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        Xem tất cả ảnh
                    </button>
                </div>
                <div class="gallery-thumbnails">
                    @forelse($stadium->getMedia('images') as $image)
                        <img src="{{ $image->getUrl() }}" alt="{{ $stadium->name }} - Gallery">
                    @empty
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect fill='%230088CC' width='400' height='300'/%3E%3Ctext x='200' y='160' font-family='Arial' font-size='24' fill='white' text-anchor='middle'%3EFacilities%3C/text%3E%3C/svg%3E" alt="Facilities">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect fill='%23FF6B6B' width='400' height='300'/%3E%3Ctext x='200' y='160' font-family='Arial' font-size='24' fill='white' text-anchor='middle'%3EAmenities%3C/text%3E%3C/svg%3E" alt="Amenities">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect fill='%23FFB84D' width='400' height='300'/%3E%3Ctext x='200' y='160' font-family='Arial' font-size='24' fill='white' text-anchor='middle'%3EParking%3C/text%3E%3C/svg%3E" alt="Parking">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect fill='%239D84B7' width='400' height='300'/%3E%3Ctext x='200' y='160' font-family='Arial' font-size='24' fill='white' text-anchor='middle'%3ERestrooms%3C/text%3E%3C/svg%3E" alt="Restrooms">
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="court-detail-section section">
        <div class="container">
            <div class="detail-layout">
                <!-- Main Content -->
                <div class="detail-main">
                    <!-- Tab Navigation -->
                    <div class="tab-navigation">
                        <button class="tab-btn active" data-tab="overview">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="16" x2="12" y2="12"/>
                                <line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                            Tổng quan
                        </button>
                        <button class="tab-btn" data-tab="facilities">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            </svg>
                            Tiện ích
                        </button>
                        <button class="tab-btn" data-tab="reviews">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            Đánh giá (128)
                        </button>
                        <button class="tab-btn" data-tab="rules">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
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
                                    <p>{{ $stadium->description ?? 'Sân pickleball chất lượng cao với đầy đủ tiện ích hiện đại.' }}</p>
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
                                        <div class="info-value">{{ $stadium->opening_hours ?? '05:00 - 23:00' }}</div>
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
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                            <circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        <p>Bản đồ Google Maps sẽ hiển thị tại đây</p>
                                    </div>
                                    <div class="location-details">
                                        <h4>Địa chỉ chi tiết</h4>
                                        <p>{{ $stadium->address }}</p>
                                        <button class="btn btn-outline">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                                <polyline points="15 3 21 3 21 9"/>
                                                <line x1="10" y1="14" x2="21" y2="3"/>
                                            </svg>
                                            Chỉ đường
                                        </button>
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
                                    @if($stadium->amenities)
                                        @foreach(is_array($stadium->amenities) ? $stadium->amenities : json_decode($stadium->amenities, true) as $amenity)
                                        <div class="facility-item">
                                            <div class="facility-icon">
                                                @if(strpos($amenity, '🚿') !== false)
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
                                            <h4>{{ str_replace(['�', '�️', '☕', '🏪', '❄️', '🎾', '📱', '🔒', '👨‍🏫', '📸', '🎵'], '', $amenity) }}</h4>
                                            <p>Dịch vụ chất lượng cao</p>
                                        </div>
                                        @endforeach
                                    @endif
                                    <div class="facility-item">
                                        <div class="facility-icon">🎵</div>
                                        <h4>Âm thanh</h4>
                                        <p>Hệ thống âm thanh chất lượng cao</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reviews Tab -->
                        <div class="tab-pane" id="reviews">
                            <div class="content-card">
                                <div class="reviews-header">
                                    <div>
                                        <h2 class="content-title">Đánh giá từ khách hàng</h2>
                                        <p class="reviews-summary">Dựa trên 128 đánh giá</p>
                                    </div>
                                    <button class="btn btn-primary">Viết đánh giá</button>
                                </div>

                                <!-- Rating Overview -->
                                <div class="rating-overview">
                                    <div class="rating-score">
                                        <div class="score-number">4.8</div>
                                        <div class="score-stars">⭐⭐⭐⭐⭐</div>
                                        <div class="score-label">Xuất sắc</div>
                                    </div>
                                    <div class="rating-breakdown">
                                        <div class="rating-row">
                                            <span class="rating-label">5 ⭐</span>
                                            <div class="rating-bar">
                                                <div class="rating-fill" style="width: 75%"></div>
                                            </div>
                                            <span class="rating-count">96</span>
                                        </div>
                                        <div class="rating-row">
                                            <span class="rating-label">4 ⭐</span>
                                            <div class="rating-bar">
                                                <div class="rating-fill" style="width: 20%"></div>
                                            </div>
                                            <span class="rating-count">26</span>
                                        </div>
                                        <div class="rating-row">
                                            <span class="rating-label">3 ⭐</span>
                                            <div class="rating-bar">
                                                <div class="rating-fill" style="width: 3%"></div>
                                            </div>
                                            <span class="rating-count">4</span>
                                        </div>
                                        <div class="rating-row">
                                            <span class="rating-label">2 ⭐</span>
                                            <div class="rating-bar">
                                                <div class="rating-fill" style="width: 1%"></div>
                                            </div>
                                            <span class="rating-count">1</span>
                                        </div>
                                        <div class="rating-row">
                                            <span class="rating-label">1 ⭐</span>
                                            <div class="rating-bar">
                                                <div class="rating-fill" style="width: 1%"></div>
                                            </div>
                                            <span class="rating-count">1</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reviews List -->
                                <div class="reviews-list">
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
                                            <p>Sân rất đẹp và sạch sẽ, nhân viên thân thiện. Mặt sân chất lượng tốt, phù hợp cho cả người mới và người chơi lâu năm. Sẽ quay lại chơi tiếp!</p>
                                        </div>
                                        <div class="review-helpful">
                                            <button class="helpful-btn">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                                                </svg>
                                                Hữu ích (12)
                                            </button>
                                        </div>
                                    </div>

                                    <div class="review-item">
                                        <div class="review-header">
                                            <div class="reviewer-info">
                                                <div class="reviewer-avatar">TH</div>
                                                <div>
                                                    <div class="reviewer-name">Trần Hoàng</div>
                                                    <div class="review-date">1 tháng trước</div>
                                                </div>
                                            </div>
                                            <div class="review-rating">⭐⭐⭐⭐⭐</div>
                                        </div>
                                        <div class="review-content">
                                            <p>Cơ sở vật chất hiện đại, giá cả hợp lý. Đặc biệt là có nhiều khung giờ linh hoạt, rất tiện cho người đi làm. Highly recommended!</p>
                                        </div>
                                        <div class="review-helpful">
                                            <button class="helpful-btn">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                                                </svg>
                                                Hữu ích (8)
                                            </button>
                                        </div>
                                    </div>

                                    <div class="review-item">
                                        <div class="review-header">
                                            <div class="reviewer-info">
                                                <div class="reviewer-avatar">LP</div>
                                                <div>
                                                    <div class="reviewer-name">Lê Phương</div>
                                                    <div class="review-date">1 tháng trước</div>
                                                </div>
                                            </div>
                                            <div class="review-rating">⭐⭐⭐⭐</div>
                                        </div>
                                        <div class="review-content">
                                            <p>Sân tốt, tiện ích đầy đủ. Chỉ có điều bãi đỗ xe hơi chật vào giờ cao điểm. Nhưng nhìn chung vẫn rất hài lòng.</p>
                                        </div>
                                        <div class="review-helpful">
                                            <button class="helpful-btn">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                                                </svg>
                                                Hữu ích (5)
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <button class="btn btn-outline btn-block load-more-reviews">Xem thêm đánh giá</button>
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
                                            <p>Khách hàng cần đặt sân trước ít nhất 2 giờ. Đặt qua website hoặc gọi điện để được hỗ trợ tốt nhất.</p>
                                        </div>
                                    </div>
                                    <div class="rule-item">
                                        <span class="rule-number">2</span>
                                        <div class="rule-content">
                                            <h4>Đúng giờ</h4>
                                            <p>Vui lòng có mặt đúng giờ đã đặt. Nếu đến muộn quá 15 phút mà không báo trước, sân có thể được chuyển cho khách khác.</p>
                                        </div>
                                    </div>
                                    <div class="rule-item">
                                        <span class="rule-number">3</span>
                                        <div class="rule-content">
                                            <h4>Trang phục</h4>
                                            <p>Mặc trang phục thể thao phù hợp. Giày thể thao với đế không để lại vết đen trên sân.</p>
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
                                            <p>Hủy sân trước 24 giờ được hoàn lại 100% tiền. Hủy trong vòng 24 giờ sẽ không được hoàn tiền.</p>
                                        </div>
                                    </div>
                                    <div class="rule-item">
                                        <span class="rule-number">6</span>
                                        <div class="rule-content">
                                            <h4>An toàn</h4>
                                            <p>Chủ động khởi động kỹ trước khi chơi. Báo ngay cho nhân viên nếu có tai nạn hoặc chấn thương.</p>
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
                                <select class="booking-input">
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
                                <select class="booking-input">
                                    <option value="1">1 giờ</option>
                                    <option value="2">2 giờ</option>
                                    <option value="3">3 giờ</option>
                                </select>
                            </div>
                            <button class="btn btn-primary btn-block" onclick="window.location.href='booking.html'">
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
                                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                                </svg>
                                <div>
                                    <div class="contact-label">Điện thoại</div>
                                    <div class="contact-value">0901 234 567</div>
                                </div>
                            </div>
                            <div class="contact-item">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <div>
                                    <div class="contact-label">Email</div>
                                    <div class="contact-value">rachchieccourt@gmail.com</div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-outline btn-block">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                            </svg>
                            Chat ngay
                        </button>
                    </div>

                    <!-- Related Courts -->
                    <div class="sidebar-card">
                        <h3 class="card-title">Sân gần đây</h3>
                        <div class="related-list">
                            <a href="#" class="related-item">
                                <div class="related-image">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 80'%3E%3Crect fill='%230088CC' width='100' height='80'/%3E%3C/svg%3E" alt="">
                                </div>
                                <div class="related-content">
                                    <h4>Thảo Điền Sports</h4>
                                    <p>⭐ 4.9 • 2.5 km</p>
                                </div>
                            </a>
                            <a href="#" class="related-item">
                                <div class="related-image">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 80'%3E%3Crect fill='%23FF6B6B' width='100' height='80'/%3E%3C/svg%3E" alt="">
                                </div>
                                <div class="related-content">
                                    <h4>An Phú Arena</h4>
                                    <p>⭐ 4.7 • 3.2 km</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script src="{{ asset('assets/js/tournament-detail.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
