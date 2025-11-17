@extends('layouts.front')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/courts.css') }}">
@endsection

@section('content')
    <section class="page-header">
        <div class="page-header-background"></div>
        <div class="container">
            <div class="breadcrumb">
                <a href="index.html">Trang chủ</a>
                <span class="separator">/</span>
                <span>Sân thi đấu</span>
            </div>
            <h1 class="page-title">Sân Pickleball Toàn Quốc</h1>
            <p class="page-description">Tìm kiếm và đặt sân Pickleball chất lượng cao với cơ sở vật chất hiện đại</p>
            
            <!-- Search Bar -->
            <div class="main-search-bar">
                <div class="search-input-group">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" class="main-search-input" placeholder="Tìm kiếm sân theo tên, địa chỉ...">
                </div>
                <div class="search-location-group">
                    <svg class="location-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <select class="location-select">
                        <option value="">Tất cả khu vực</option>
                        <option value="hcm">TP. Hồ Chí Minh</option>
                        <option value="hn">Hà Nội</option>
                        <option value="dn">Đà Nẵng</option>
                        <option value="ct">Cần Thơ</option>
                        <option value="vt">Vũng Tàu</option>
                    </select>
                </div>
                <button class="btn btn-primary btn-lg search-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    Tìm kiếm
                </button>
            </div>

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-box">
                    <div class="stat-icon">🏟️</div>
                    <div class="stat-content">
                        <div class="stat-number">52</div>
                        <div class="stat-label">Sân thi đấu</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">📍</div>
                    <div class="stat-content">
                        <div class="stat-number">15</div>
                        <div class="stat-label">Tỉnh/Thành phố</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-content">
                        <div class="stat-number">4.7</div>
                        <div class="stat-label">Đánh giá trung bình</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-content">
                        <div class="stat-number">320+</div>
                        <div class="stat-label">Sân đơn lẻ</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="courts-section section">
        <div class="container">
            <!-- View Toggle Bar -->
            <div class="view-toggle-bar">
                <div class="toggle-left">
                    <button class="view-mode-btn active" data-view="grid">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                        </svg>
                        Grid
                    </button>
                    <button class="view-mode-btn" data-view="list">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <line x1="8" y1="6" x2="21" y2="6"/>
                            <line x1="8" y1="12" x2="21" y2="12"/>
                            <line x1="8" y1="18" x2="21" y2="18"/>
                            <line x1="3" y1="6" x2="3.01" y2="6"/>
                            <line x1="3" y1="12" x2="3.01" y2="12"/>
                            <line x1="3" y1="18" x2="3.01" y2="18"/>
                        </svg>
                        List
                    </button>
                    <button class="view-mode-btn" data-view="map">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        Map
                    </button>
                </div>
                <div class="toggle-right">
                    <span class="result-text">Tìm thấy <strong>52 sân</strong></span>
                    <button class="filter-mobile-btn btn btn-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <line x1="4" y1="21" x2="4" y2="14"/>
                            <line x1="4" y1="10" x2="4" y2="3"/>
                            <line x1="12" y1="21" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12" y2="3"/>
                            <line x1="20" y1="21" x2="20" y2="16"/>
                            <line x1="20" y1="12" x2="20" y2="3"/>
                        </svg>
                        Bộ lọc
                    </button>
                </div>
            </div>

            <div class="courts-layout">
                <!-- Sidebar Filters -->
                <aside class="courts-sidebar">
                    <div class="filter-card">
                        <div class="filter-header">
                            <h3 class="filter-title">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <line x1="4" y1="21" x2="4" y2="14"/>
                                    <line x1="4" y1="10" x2="4" y2="3"/>
                                    <line x1="12" y1="21" x2="12" y2="12"/>
                                    <line x1="12" y1="8" x2="12" y2="3"/>
                                    <line x1="20" y1="21" x2="20" y2="16"/>
                                    <line x1="20" y1="12" x2="20" y2="3"/>
                                </svg>
                                Bộ lọc
                            </h3>
                            <button class="filter-reset">Xóa bộ lọc</button>
                        </div>

                        <!-- Price Range Filter -->
                        <div class="filter-group">
                            <label class="filter-label">Giá thuê (VNĐ/giờ)</label>
                            <div class="price-range-inputs">
                                <input type="number" class="price-input" placeholder="Từ" min="0">
                                <span>-</span>
                                <input type="number" class="price-input" placeholder="Đến" min="0">
                            </div>
                            <div class="price-slider">
                                <input type="range" min="0" max="500000" step="10000" value="0" class="range-min">
                                <input type="range" min="0" max="500000" step="10000" value="500000" class="range-max">
                            </div>
                        </div>

                        <!-- Rating Filter -->
                        <div class="filter-group">
                            <label class="filter-label">Đánh giá</label>
                            <div class="filter-options">
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span class="rating-stars">⭐⭐⭐⭐⭐ 5.0</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span class="rating-stars">⭐⭐⭐⭐ 4.0+</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span class="rating-stars">⭐⭐⭐ 3.0+</span>
                                </label>
                            </div>
                        </div>

                        <!-- Facilities Filter -->
                        <div class="filter-group">
                            <label class="filter-label">Tiện ích</label>
                            <div class="filter-options">
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>🅿️ Bãi đỗ xe</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>🚿 Phòng tắm</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>☕ Canteen</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>🏪 Cửa hàng</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>❄️ Điều hòa</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>🎾 Cho thuê vợt</span>
                                </label>
                            </div>
                        </div>

                        <!-- Number of Courts Filter -->
                        <div class="filter-group">
                            <label class="filter-label">Số lượng sân</label>
                            <div class="filter-options">
                                <label class="filter-radio">
                                    <input type="radio" name="courts" value="" checked>
                                    <span class="radio-custom"></span>
                                    <span>Tất cả</span>
                                </label>
                                <label class="filter-radio">
                                    <input type="radio" name="courts" value="1-3">
                                    <span class="radio-custom"></span>
                                    <span>1-3 sân</span>
                                </label>
                                <label class="filter-radio">
                                    <input type="radio" name="courts" value="4-6">
                                    <span class="radio-custom"></span>
                                    <span>4-6 sân</span>
                                </label>
                                <label class="filter-radio">
                                    <input type="radio" name="courts" value="7+">
                                    <span class="radio-custom"></span>
                                    <span>7+ sân</span>
                                </label>
                            </div>
                        </div>

                        <!-- Opening Hours Filter -->
                        <div class="filter-group">
                            <label class="filter-label">Giờ mở cửa</label>
                            <div class="filter-options">
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>Mở cửa sớm (trước 7h)</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>Mở cửa muộn (sau 22h)</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>24/7</span>
                                </label>
                            </div>
                        </div>

                        <button class="btn btn-primary btn-block filter-apply">
                            Áp dụng bộ lọc
                        </button>
                    </div>
                </aside>

                <!-- Main Content Area -->
                <div class="courts-main">
                    <!-- Grid View -->
                    <div class="courts-grid active" id="courtsGrid">
                        <!-- Court Card 1 -->
                        <div class="court-card">
                            <a href="court-detail.html" class="court-link">
                                <div class="court-image">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 280'%3E%3Crect fill='%2300A86B' width='400' height='280'/%3E%3Cline x1='0' y1='140' x2='400' y2='140' stroke='white' stroke-width='4'/%3E%3Cline x1='200' y1='0' x2='200' y2='280' stroke='white' stroke-width='2'/%3E%3Crect x='50' y='50' width='300' height='180' fill='none' stroke='white' stroke-width='3'/%3E%3Ctext x='200' y='160' font-family='Arial' font-size='20' fill='white' text-anchor='middle'%3ECOURT%3C/text%3E%3C/svg%3E" alt="Pickleball Rạch Chiếc">
                                    <div class="court-badges">
                                        <span class="badge badge-featured">⭐ Nổi bật</span>
                                        <span class="badge badge-available">✓ Còn chỗ</span>
                                    </div>
                                    <button class="favorite-btn" onclick="event.preventDefault();">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="court-content">
                                    <div class="court-header">
                                        <div>
                                            <h3 class="court-name">Pickleball Rạch Chiếc</h3>
                                            <div class="court-location">
                                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                                    <circle cx="12" cy="10" r="3"/>
                                                </svg>
                                                <span>Quận 2, TP.HCM</span>
                                            </div>
                                        </div>
                                        <div class="court-rating">
                                            <span class="rating-star">⭐</span>
                                            <span class="rating-value">4.8</span>
                                            <span class="rating-count">(128)</span>
                                        </div>
                                    </div>

                                    <div class="court-features">
                                        <span class="feature-tag">🏟️ 8 sân</span>
                                        <span class="feature-tag">🚿 Phòng tắm</span>
                                        <span class="feature-tag">🅿️ Bãi đỗ xe</span>
                                        <span class="feature-tag">☕ Canteen</span>
                                    </div>

                                    <div class="court-info">
                                        <div class="info-row">
                                            <span class="info-label">Giờ mở cửa:</span>
                                            <span class="info-value">05:00 - 23:00</span>
                                        </div>
                                        <div class="info-row price-row">
                                            <span class="info-label">Giá thuê:</span>
                                            <span class="price-value">150.000đ - 300.000đ/giờ</span>
                                        </div>
                                    </div>

                                    <button class="btn btn-primary btn-block" onclick="event.preventDefault(); window.location.href='booking.html';">
                                        Đặt sân ngay
                                    </button>
                                </div>
                            </a>
                        </div>

                        <!-- Court Card 2 -->
                        <div class="court-card">
                            <a href="court-detail.html" class="court-link">
                                <div class="court-image">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 280'%3E%3Crect fill='%230088CC' width='400' height='280'/%3E%3Cline x1='0' y1='140' x2='400' y2='140' stroke='white' stroke-width='4'/%3E%3Cline x1='200' y1='0' x2='200' y2='280' stroke='white' stroke-width='2'/%3E%3Crect x='50' y='50' width='300' height='180' fill='none' stroke='white' stroke-width='3'/%3E%3Ctext x='200' y='160' font-family='Arial' font-size='20' fill='white' text-anchor='middle'%3ECOURT%3C/text%3E%3C/svg%3E" alt="Thảo Điền Sports Club">
                                    <div class="court-badges">
                                        <span class="badge badge-premium">👑 Premium</span>
                                    </div>
                                    <button class="favorite-btn" onclick="event.preventDefault();">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="court-content">
                                    <div class="court-header">
                                        <div>
                                            <h3 class="court-name">Thảo Điền Sports Club</h3>
                                            <div class="court-location">
                                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                                    <circle cx="12" cy="10" r="3"/>
                                                </svg>
                                                <span>Thủ Đức, TP.HCM</span>
                                            </div>
                                        </div>
                                        <div class="court-rating">
                                            <span class="rating-star">⭐</span>
                                            <span class="rating-value">4.9</span>
                                            <span class="rating-count">(95)</span>
                                        </div>
                                    </div>

                                    <div class="court-features">
                                        <span class="feature-tag">🏟️ 6 sân</span>
                                        <span class="feature-tag">🚿 Phòng tắm VIP</span>
                                        <span class="feature-tag">🅿️ Bãi đỗ xe</span>
                                        <span class="feature-tag">🏋️ Gym</span>
                                    </div>

                                    <div class="court-info">
                                        <div class="info-row">
                                            <span class="info-label">Giờ mở cửa:</span>
                                            <span class="info-value">06:00 - 22:00</span>
                                        </div>
                                        <div class="info-row price-row">
                                            <span class="info-label">Giá thuê:</span>
                                            <span class="price-value">200.000đ - 400.000đ/giờ</span>
                                        </div>
                                    </div>

                                    <button class="btn btn-primary btn-block" onclick="event.preventDefault(); window.location.href='booking.html';">
                                        Đặt sân ngay
                                    </button>
                                </div>
                            </a>
                        </div>

                        <!-- Court Card 3 -->
                        <div class="court-card">
                            <a href="court-detail.html" class="court-link">
                                <div class="court-image">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 280'%3E%3Crect fill='%23FF6B6B' width='400' height='280'/%3E%3Cline x1='0' y1='140' x2='400' y2='140' stroke='white' stroke-width='4'/%3E%3Cline x1='200' y1='0' x2='200' y2='280' stroke='white' stroke-width='2'/%3E%3Crect x='50' y='50' width='300' height='180' fill='none' stroke='white' stroke-width='3'/%3E%3Ctext x='200' y='160' font-family='Arial' font-size='20' fill='white' text-anchor='middle'%3ECOURT%3C/text%3E%3C/svg%3E" alt="Cầu Giấy Arena">
                                    <div class="court-badges">
                                        <span class="badge badge-available">✓ Còn chỗ</span>
                                    </div>
                                    <button class="favorite-btn" onclick="event.preventDefault();">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="court-content">
                                    <div class="court-header">
                                        <div>
                                            <h3 class="court-name">Cầu Giấy Pickleball Arena</h3>
                                            <div class="court-location">
                                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                                    <circle cx="12" cy="10" r="3"/>
                                                </svg>
                                                <span>Cầu Giấy, Hà Nội</span>
                                            </div>
                                        </div>
                                        <div class="court-rating">
                                            <span class="rating-star">⭐</span>
                                            <span class="rating-value">4.7</span>
                                            <span class="rating-count">(74)</span>
                                        </div>
                                    </div>

                                    <div class="court-features">
                                        <span class="feature-tag">🏟️ 10 sân</span>
                                        <span class="feature-tag">🚿 Phòng tắm</span>
                                        <span class="feature-tag">🅿️ Bãi đỗ xe</span>
                                        <span class="feature-tag">🏪 Cửa hàng</span>
                                    </div>

                                    <div class="court-info">
                                        <div class="info-row">
                                            <span class="info-label">Giờ mở cửa:</span>
                                            <span class="info-value">05:30 - 23:00</span>
                                        </div>
                                        <div class="info-row price-row">
                                            <span class="info-label">Giá thuê:</span>
                                            <span class="price-value">120.000đ - 250.000đ/giờ</span>
                                        </div>
                                    </div>

                                    <button class="btn btn-primary btn-block" onclick="event.preventDefault(); window.location.href='booking.html';">
                                        Đặt sân ngay
                                    </button>
                                </div>
                            </a>
                        </div>

                        <!-- Add more court cards here (4-12) -->
                    </div>

                    <!-- Map View -->
                    <div class="courts-map" id="courtsMap">
                        <div class="map-container">
                            <div class="map-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <p>Bản đồ sẽ được tích hợp Google Maps API</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination">
                        <button class="pagination-btn pagination-prev" disabled>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="15 18 9 12 15 6"/>
                            </svg>
                            Trước
                        </button>
                        <div class="pagination-numbers">
                            <button class="pagination-number active">1</button>
                            <button class="pagination-number">2</button>
                            <button class="pagination-number">3</button>
                            <button class="pagination-number">4</button>
                            <span class="pagination-dots">...</span>
                            <button class="pagination-number">8</button>
                        </div>
                        <button class="pagination-btn pagination-next">
                            Sau
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-banner section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Bạn là chủ sân Pickleball?</h2>
                <p class="cta-description">Đăng ký trở thành đối tác và tiếp cận hàng ngàn người chơi</p>
                <button class="btn btn-white btn-lg">Đăng ký làm đối tác</button>
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script src="{{ asset('assets/js/courts.js') }}"></script>
@endsection

