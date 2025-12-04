@extends('layouts.front')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/courts.css') }}">
    <style>
        .price-input {
            width: 50%;
        }
        .address-truncate {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
    </style>
@endsection

@section('content')
    <section class="page-header">
        <div class="page-header-background"></div>
        <div class="container">
            <div class="breadcrumb">
                <a href="/">Trang chủ</a>
                <span class="separator">/</span>
                <span>Sân thi đấu</span>
            </div>
            <h1 class="page-title">Sân Pickleball Toàn Quốc</h1>
            <p class="page-description">Tìm kiếm và đặt sân Pickleball chất lượng cao với cơ sở vật chất hiện đại</p>

            <!-- Search Bar -->
            <form method="GET" action="{{ route('courts') }}" class="main-search-bar">
                <div class="search-input-group">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    <input type="text" name="search" class="main-search-input"
                        placeholder="Tìm kiếm sân theo tên, địa chỉ..." value="{{ request('search') }}">
                </div>
                <div class="search-location-group">
                    <svg class="location-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                    <select name="location" class="location-select">
                        <option value="">Tất cả khu vực</option>
                        @forelse($provinces as $p)
                            <option value="{{ $p->id }}"
                                {{ request('location') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @empty
                        @endforelse
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-lg search-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    Tìm kiếm
                </button>
            </form>

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-box">
                    <div class="stat-icon">🏟️</div>
                    <div class="stat-content">
                        <div class="stat-number">{{ $totalStadiums }}</div>
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
                        <div class="stat-number">{{ $totalCourts }}+</div>
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
            {{-- <div class="view-toggle-bar">
                <div class="toggle-left">
                    <button class="view-mode-btn active" data-view="grid">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="3" width="7" height="7" />
                            <rect x="14" y="3" width="7" height="7" />
                            <rect x="14" y="14" width="7" height="7" />
                            <rect x="3" y="14" width="7" height="7" />
                        </svg>
                        Grid
                    </button>
                    <button class="view-mode-btn" data-view="list">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <line x1="8" y1="6" x2="21" y2="6" />
                            <line x1="8" y1="12" x2="21" y2="12" />
                            <line x1="8" y1="18" x2="21" y2="18" />
                            <line x1="3" y1="6" x2="3.01" y2="6" />
                            <line x1="3" y1="12" x2="3.01" y2="12" />
                            <line x1="3" y1="18" x2="3.01" y2="18" />
                        </svg>
                        List
                    </button>
                    <button class="view-mode-btn" data-view="map">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        Map
                    </button>
                </div>
                <div class="toggle-right">
                    <span class="result-text">
                        Tìm thấy <strong>{{ $totalStadiums }} sân</strong>
                    </span>
                    <button type="button" class="filter-mobile-btn btn btn-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <line x1="4" y1="21" x2="4" y2="14" />
                            <line x1="4" y1="10" x2="4" y2="3" />
                            <line x1="12" y1="21" x2="12" y2="12" />
                            <line x1="12" y1="8" x2="12" y2="3" />
                            <line x1="20" y1="21" x2="20" y2="16" />
                            <line x1="20" y1="12" x2="20" y2="3" />
                        </svg>
                        Bộ lọc
                    </button>
                </div>
            </div> --}}

            <div class="courts-layout">
                <!-- Sidebar Filters -->
                <aside class="courts-sidebar">
                    <form id="filterForm" method="GET" action="{{ route('courts') }}" class="filter-card">
                        <div class="filter-header">
                            <h3 class="filter-title">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <line x1="4" y1="21" x2="4" y2="14" />
                                    <line x1="4" y1="10" x2="4" y2="3" />
                                    <line x1="12" y1="21" x2="12" y2="12" />
                                    <line x1="12" y1="8" x2="12" y2="3" />
                                    <line x1="20" y1="21" x2="20" y2="16" />
                                    <line x1="20" y1="12" x2="20" y2="3" />
                                </svg>
                                Bộ lọc
                            </h3>
                            <a href="{{ route('courts') }}" type="button" class="filter-reset">Xóa bộ lọc</a>
                        </div>

                        <!-- Price Range Filter -->
                        <div class="filter-group">
                            <label class="filter-label">Giá thuê (nghìn VNĐ/giờ)</label>
                            <div class="price-range-inputs">
                                <input type="number" name="price_min" class="price-input" placeholder="Từ"
                                    min="0" value="{{ request('price_min') }}">
                                <span>-</span>
                                <input type="number" name="price_max" class="price-input" placeholder="Đến"
                                    min="0" value="{{ request('price_max') }}">
                            </div>
                        </div>

                        <!-- Rating Filter -->
                        <div class="filter-group">
                            <label class="filter-label">Đánh giá</label>
                            <div class="filter-options">
                                <label class="filter-radio">
                                    <input type="radio" name="rating" value=""
                                        {{ !request('rating') ? 'checked' : '' }}>
                                    <span class="radio-custom"></span>
                                    <span>Tất cả</span>
                                </label>
                                <label class="filter-radio">
                                    <input type="radio" name="rating" value="5"
                                        {{ request('rating') == '5' ? 'checked' : '' }}>
                                    <span class="radio-custom"></span>
                                    <span class="rating-stars">⭐⭐⭐⭐⭐ 5.0</span>
                                </label>
                                <label class="filter-radio">
                                    <input type="radio" name="rating" value="4"
                                        {{ request('rating') == '4' ? 'checked' : '' }}>
                                    <span class="radio-custom"></span>
                                    <span class="rating-stars">⭐⭐⭐⭐ 4.0+</span>
                                </label>
                                <label class="filter-radio">
                                    <input type="radio" name="rating" value="3"
                                        {{ request('rating') == '3' ? 'checked' : '' }}>
                                    <span class="radio-custom"></span>
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
                                    <input type="radio" name="courts_range" value=""
                                        {{ !request('courts_range') ? 'checked' : '' }}>
                                    <span class="radio-custom"></span>
                                    <span>Tất cả</span>
                                </label>
                                <label class="filter-radio">
                                    <input type="radio" name="courts_range" value="1-3"
                                        {{ request('courts_range') == '1-3' ? 'checked' : '' }}>
                                    <span class="radio-custom"></span>
                                    <span>1-3 sân</span>
                                </label>
                                <label class="filter-radio">
                                    <input type="radio" name="courts_range" value="4-6"
                                        {{ request('courts_range') == '4-6' ? 'checked' : '' }}>
                                    <span class="radio-custom"></span>
                                    <span>4-6 sân</span>
                                </label>
                                <label class="filter-radio">
                                    <input type="radio" name="courts_range" value="7+"
                                        {{ request('courts_range') == '7+' ? 'checked' : '' }}>
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
                    </form>
                </aside>

                <!-- Main Content Area -->
                <div class="courts-main">
                    <!-- Grid View -->
                    <div class="courts-grid active" id="courtsGrid">
                        @forelse($stadiums as $stadium)
                            <!-- Court Card -->
                            <div class="court-card">
                                <a href="{{ route('courts-detail', $stadium->id) }}" class="court-link">
                                    <div class="court-image">
                                        @php
                                            $bannerUrl =
                                                $stadium->getFirstMediaUrl('banner') ?:
                                                ($stadium->image
                                                    ? asset('storage/' . $stadium->image)
                                                    : asset('assets/images/court_default.svg'));
                                        @endphp
                                        <img src="{{ $bannerUrl }}" alt="{{ $stadium->name }}">
                                        <div class="court-badges">
                                            @if ($stadium->is_featured)
                                                <span class="badge badge-featured">⭐ Nổi bật</span>
                                            @endif
                                            @if ($stadium->is_premium)
                                                <span class="badge badge-premium">👑 Premium</span>
                                            @else
                                                <span class="badge badge-available">✓ Còn chỗ</span>
                                            @endif
                                        </div>
                                        <button type="button" class="favorite-btn @if(in_array($stadium->id, $userFavorites)) active @endif" data-stadium-id="{{ $stadium->id }}"
                                            data-favorited="{{ in_array($stadium->id, $userFavorites) ? 'true' : 'false' }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path
                                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="court-content">
                                        <div class="court-header">
                                            <div>
                                                <h3 class="court-name">{{ $stadium->name }}</h3>
                                                
                                            </div>
                                            <div class="court-rating">
                                                <span class="rating-star">⭐</span>
                                                <span class="rating-value">4.8</span>
                                                <span class="rating-count">(128)</span>
                                            </div>
                                        </div>
                                        <div class="court-location">
                                                    <svg class="icon" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor">
                                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                                        <circle cx="12" cy="10" r="3" />
                                                    </svg>
                                                    <span style="flex:1">{{ $stadium->address }}</span>
                                                </div>

                                        <div class="court-features">
                                             <span class="feature-tag">🏟️ {{ $stadium->courts->count() }} sân</span>

                                             @foreach ((is_array($stadium->amenities) ? $stadium->amenities : json_decode($stadium->amenities, true)??[]) as $amenity)
                                                 <span class="feature-tag">{{ $amenity }}</span>
                                             @endforeach
                                         </div>

                                        <div class="court-info">
                                            <div class="info-row">
                                                <span class="info-label">Giờ mở cửa:</span>
                                                <span class="info-value">{{ $stadium->opening_hours ?? 'Liên hệ' }}</span>
                                            </div>
                                            <div class="info-row price-row">
                                                 <span class="info-label">SĐT:</span>
                                                 <span class="price-value address-truncate" title="{{ $stadium->phone ?? 'Không có' }}">{{ Str::limit($stadium->phone, 40, '...') ?? 'Không có' }}</span>
                                             </div>
                                        </div>

                                        <button class="btn btn-primary btn-block"
                                            onclick="event.preventDefault(); window.location.href='{{ route('booking', $stadium) }}';">
                                            Đặt sân ngay
                                        </button>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                                <p style="font-size: 18px; color: #666;">Hiện không có sân nào khả dụng</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Map View -->
                    <div class="courts-map" id="courtsMap">
                        <div class="map-container">
                            <div class="map-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                                <p>Bản đồ sẽ được tích hợp Google Maps API</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if ($stadiums->hasPages())
                        {{ $stadiums->links('pagination.custom') }}
                    @endif
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
    <script>
        // ==================== Favorites Feature ====================
        document.addEventListener('DOMContentLoaded', function() {
            const favoriteButtons = document.querySelectorAll('.favorite-btn');
            favoriteButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleFavorite(this);
                });
            });
        });

        async function toggleFavorite(btn) {
            const stadiumId = btn.getAttribute('data-stadium-id');
            
            try {
                btn.disabled = true;
                btn.classList.add('loading');

                const response = await fetch('/stadiums/' + stadiumId + '/toggle-favorite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    btn.classList.toggle('active');
                    const message = data.favorited ? 'Đã thêm vào yêu thích' : 'Đã xóa khỏi yêu thích';
                    showToast(message, 'success');
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            } catch (error) {
                showToast('Lỗi: ' + error.message, 'error');
            } finally {
                btn.disabled = false;
                btn.classList.remove('loading');
            }
        }

        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            toast.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                padding: 15px 20px;
                background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
                color: white;
                border-radius: 4px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                z-index: 10000;
                animation: slideIn 0.3s ease;
            `;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

    </script>
@endsection
