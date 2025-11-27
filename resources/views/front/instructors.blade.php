@extends('layouts.front')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles-extended.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles-coaches.css') }}">
@endsection

@section('content')
<section class="page-header">
        <div class="page-header-background"></div>
        <div class="container">
            <div class="page-header-content">
                <span class="section-badge">Giảng Viên</span>
                <h1 class="page-title">Tìm <span class="gradient-text">Giảng Viên</span> Pickleball</h1>
                <p class="page-description">Kết nối với các huấn luyện viên chuyên nghiệp, nhiều năm kinh nghiệm, sẵn sàng đồng hành cùng bạn trên hành trình chinh phục Pickleball</p>
            </div>
        </div>
    </section>

    <!-- Filter Section -->
    <section class="coaches-filter">
        <div class="container">
            <div class="filter-wrapper">
                <!-- Search -->
                <div class="filter-search-box">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="text" class="search-input" placeholder="Tìm kiếm giảng viên...">
                </div>

                <!-- Location Filter -->
                <div class="filter-group">
                    <label class="filter-label">Tỉnh/Thành phố</label>
                    <select class="filter-select">
                        <option value="">Tất cả</option>
                        <option value="hcm">TP. Hồ Chí Minh</option>
                        <option value="hn">Hà Nội</option>
                        <option value="dn">Đà Nẵng</option>
                        <option value="bd">Bình Dương</option>
                        <option value="hp">Hải Phòng</option>
                        <option value="ct">Cần Thơ</option>
                    </select>
                </div>

                <!-- District Filter -->
                <div class="filter-group">
                    <label class="filter-label">Quận/Huyện</label>
                    <select class="filter-select">
                        <option value="">Tất cả</option>
                        <option value="q1">Quận 1</option>
                        <option value="q2">Quận 2</option>
                        <option value="q3">Quận 3</option>
                        <option value="q7">Quận 7</option>
                        <option value="td">Thủ Đức</option>
                        <option value="bt">Bình Thạnh</option>
                    </select>
                </div>

                <!-- Experience Filter -->
                <div class="filter-group">
                    <label class="filter-label">Kinh nghiệm</label>
                    <select class="filter-select">
                        <option value="">Tất cả</option>
                        <option value="1-3">1-3 năm</option>
                        <option value="3-5">3-5 năm</option>
                        <option value="5+">Trên 5 năm</option>
                    </select>
                </div>

                <!-- Sort -->
                <div class="filter-group">
                    <label class="filter-label">Sắp xếp</label>
                    <select class="filter-select">
                        <option value="rating">Đánh giá cao nhất</option>
                        <option value="experience">Nhiều kinh nghiệm</option>
                        <option value="students">Nhiều học viên</option>
                        <option value="newest">Mới nhất</option>
                    </select>
                </div>
            </div>

            <!-- Quick Filter Tags -->
            <div class="filter-tags">
                <button class="filter-tag active">Tất cả</button>
                <button class="filter-tag">⭐ Được đánh giá cao</button>
                <button class="filter-tag">🏆 Có chứng chỉ</button>
                <button class="filter-tag">👨‍👩‍👧‍👦 Dạy nhóm</button>
                <button class="filter-tag">🎯 Dạy 1-1</button>
                <button class="filter-tag">🌟 Giảng viên mới</button>
            </div>
        </div>
    </section>

    <!-- Coaches Grid -->
    <section class="coaches-section section">
        <div class="container">
            <!-- Results Info -->
            <div class="results-info">
                <p class="results-count">Tìm thấy <strong>24</strong> giảng viên</p>
                <div class="view-toggle">
                    <button class="view-btn active" data-view="grid" title="Xem dạng lưới">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                        </svg>
                    </button>
                    <button class="view-btn" data-view="list" title="Xem dạng danh sách">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="4" width="18" height="4"/>
                            <rect x="3" y="10" width="18" height="4"/>
                            <rect x="3" y="16" width="18" height="4"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Coaches Grid -->
            <div class="coaches-grid">
                <!-- Coach Card 1 -->
                <div class="coach-card">
                    <div class="coach-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Cdefs%3E%3ClinearGradient id='g1' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%230099CC;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23g1)' width='300' height='300'/%3E%3Ccircle cx='150' cy='120' r='60' fill='rgba(255,255,255,0.3)'/%3E%3Cellipse cx='150' cy='250' rx='80' ry='60' fill='rgba(255,255,255,0.3)'/%3E%3C/svg%3E" alt="Nguyễn Văn Hùng">
                        <span class="coach-badge verified">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                            </svg>
                            Đã xác minh
                        </span>
                        <div class="coach-rating-badge">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <span>4.9</span>
                        </div>
                    </div>
                    <div class="coach-content">
                        <h3 class="coach-name">
                            <a href="coach-detail.html">Nguyễn Văn Hùng</a>
                        </h3>
                        <div class="coach-experience">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                            </svg>
                            <span>8 năm kinh nghiệm</span>
                        </div>
                        <div class="coach-location">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>Quận 2, TP. Hồ Chí Minh</span>
                        </div>
                        <div class="coach-tags">
                            <span class="tag">Dạy 1-1</span>
                            <span class="tag">Dạy nhóm</span>
                            <span class="tag">Nâng cao</span>
                        </div>
                        <div class="coach-stats">
                            <div class="stat">
                                <strong>156</strong>
                                <span>Học viên</span>
                            </div>
                            <div class="stat">
                                <strong>89</strong>
                                <span>Đánh giá</span>
                            </div>
                        </div>
                        <div class="coach-actions">
                            <a href="coach-detail.html" class="btn btn-primary btn-sm">Xem chi tiết</a>
                            <button class="btn btn-outline btn-sm btn-favorite" title="Yêu thích">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Coach Card 2 -->
                <div class="coach-card">
                    <div class="coach-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Cdefs%3E%3ClinearGradient id='g2' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23FF8E53;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%23FE6B8B;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23g2)' width='300' height='300'/%3E%3Ccircle cx='150' cy='120' r='60' fill='rgba(255,255,255,0.3)'/%3E%3Cellipse cx='150' cy='250' rx='80' ry='60' fill='rgba(255,255,255,0.3)'/%3E%3C/svg%3E" alt="Trần Thị Mai">
                        <span class="coach-badge verified">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                            </svg>
                            Đã xác minh
                        </span>
                        <div class="coach-rating-badge">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <span>4.8</span>
                        </div>
                    </div>
                    <div class="coach-content">
                        <h3 class="coach-name">
                            <a href="coach-detail.html">Trần Thị Mai</a>
                        </h3>
                        <div class="coach-experience">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                            </svg>
                            <span>5 năm kinh nghiệm</span>
                        </div>
                        <div class="coach-location">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>Quận 7, TP. Hồ Chí Minh</span>
                        </div>
                        <div class="coach-tags">
                            <span class="tag">Người mới</span>
                            <span class="tag">Trung cấp</span>
                            <span class="tag">Nữ giới</span>
                        </div>
                        <div class="coach-stats">
                            <div class="stat">
                                <strong>98</strong>
                                <span>Học viên</span>
                            </div>
                            <div class="stat">
                                <strong>67</strong>
                                <span>Đánh giá</span>
                            </div>
                        </div>
                        <div class="coach-actions">
                            <a href="coach-detail.html" class="btn btn-primary btn-sm">Xem chi tiết</a>
                            <button class="btn btn-outline btn-sm btn-favorite" title="Yêu thích">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Coach Card 3 -->
                <div class="coach-card">
                    <div class="coach-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Cdefs%3E%3ClinearGradient id='g3' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%239D84B7;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%236C63FF;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23g3)' width='300' height='300'/%3E%3Ccircle cx='150' cy='120' r='60' fill='rgba(255,255,255,0.3)'/%3E%3Cellipse cx='150' cy='250' rx='80' ry='60' fill='rgba(255,255,255,0.3)'/%3E%3C/svg%3E" alt="Lê Minh Tuấn">
                        <span class="coach-badge pro">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2L15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2z"/>
                            </svg>
                            Pro Coach
                        </span>
                        <div class="coach-rating-badge">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <span>5.0</span>
                        </div>
                    </div>
                    <div class="coach-content">
                        <h3 class="coach-name">
                            <a href="coach-detail.html">Lê Minh Tuấn</a>
                        </h3>
                        <div class="coach-experience">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                            </svg>
                            <span>10 năm kinh nghiệm</span>
                        </div>
                        <div class="coach-location">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>Thủ Đức, TP. Hồ Chí Minh</span>
                        </div>
                        <div class="coach-tags">
                            <span class="tag">Chuyên nghiệp</span>
                            <span class="tag">Thi đấu</span>
                            <span class="tag">VĐV</span>
                        </div>
                        <div class="coach-stats">
                            <div class="stat">
                                <strong>234</strong>
                                <span>Học viên</span>
                            </div>
                            <div class="stat">
                                <strong>145</strong>
                                <span>Đánh giá</span>
                            </div>
                        </div>
                        <div class="coach-actions">
                            <a href="coach-detail.html" class="btn btn-primary btn-sm">Xem chi tiết</a>
                            <button class="btn btn-outline btn-sm btn-favorite" title="Yêu thích">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Coach Card 4 -->
                <div class="coach-card">
                    <div class="coach-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Cdefs%3E%3ClinearGradient id='g4' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23FFD93D;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%23FF8E53;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23g4)' width='300' height='300'/%3E%3Ccircle cx='150' cy='120' r='60' fill='rgba(255,255,255,0.3)'/%3E%3Cellipse cx='150' cy='250' rx='80' ry='60' fill='rgba(255,255,255,0.3)'/%3E%3C/svg%3E" alt="Phạm Hoàng Nam">
                        <span class="coach-badge new">Mới</span>
                        <div class="coach-rating-badge">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <span>4.7</span>
                        </div>
                    </div>
                    <div class="coach-content">
                        <h3 class="coach-name">
                            <a href="coach-detail.html">Phạm Hoàng Nam</a>
                        </h3>
                        <div class="coach-experience">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                            </svg>
                            <span>3 năm kinh nghiệm</span>
                        </div>
                        <div class="coach-location">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>Bình Thạnh, TP. Hồ Chí Minh</span>
                        </div>
                        <div class="coach-tags">
                            <span class="tag">Người mới</span>
                            <span class="tag">Trẻ em</span>
                            <span class="tag">Cuối tuần</span>
                        </div>
                        <div class="coach-stats">
                            <div class="stat">
                                <strong>45</strong>
                                <span>Học viên</span>
                            </div>
                            <div class="stat">
                                <strong>32</strong>
                                <span>Đánh giá</span>
                            </div>
                        </div>
                        <div class="coach-actions">
                            <a href="coach-detail.html" class="btn btn-primary btn-sm">Xem chi tiết</a>
                            <button class="btn btn-outline btn-sm btn-favorite" title="Yêu thích">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Coach Card 5 -->
                <div class="coach-card">
                    <div class="coach-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Cdefs%3E%3ClinearGradient id='g5' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300B89A;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23g5)' width='300' height='300'/%3E%3Ccircle cx='150' cy='120' r='60' fill='rgba(255,255,255,0.3)'/%3E%3Cellipse cx='150' cy='250' rx='80' ry='60' fill='rgba(255,255,255,0.3)'/%3E%3C/svg%3E" alt="Võ Thị Hồng">
                        <span class="coach-badge verified">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                            </svg>
                            Đã xác minh
                        </span>
                        <div class="coach-rating-badge">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <span>4.9</span>
                        </div>
                    </div>
                    <div class="coach-content">
                        <h3 class="coach-name">
                            <a href="coach-detail.html">Võ Thị Hồng</a>
                        </h3>
                        <div class="coach-experience">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                            </svg>
                            <span>6 năm kinh nghiệm</span>
                        </div>
                        <div class="coach-location">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>Cầu Giấy, Hà Nội</span>
                        </div>
                        <div class="coach-tags">
                            <span class="tag">Trung cấp</span>
                            <span class="tag">Nâng cao</span>
                            <span class="tag">Online</span>
                        </div>
                        <div class="coach-stats">
                            <div class="stat">
                                <strong>112</strong>
                                <span>Học viên</span>
                            </div>
                            <div class="stat">
                                <strong>78</strong>
                                <span>Đánh giá</span>
                            </div>
                        </div>
                        <div class="coach-actions">
                            <a href="coach-detail.html" class="btn btn-primary btn-sm">Xem chi tiết</a>
                            <button class="btn btn-outline btn-sm btn-favorite" title="Yêu thích">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Coach Card 6 -->
                <div class="coach-card">
                    <div class="coach-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 300'%3E%3Cdefs%3E%3ClinearGradient id='g6' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%230099CC;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23g6)' width='300' height='300'/%3E%3Ccircle cx='150' cy='120' r='60' fill='rgba(255,255,255,0.3)'/%3E%3Cellipse cx='150' cy='250' rx='80' ry='60' fill='rgba(255,255,255,0.3)'/%3E%3C/svg%3E" alt="Đặng Quốc Việt">
                        <span class="coach-badge verified">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                            </svg>
                            Đã xác minh
                        </span>
                        <div class="coach-rating-badge">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <span>4.6</span>
                        </div>
                    </div>
                    <div class="coach-content">
                        <h3 class="coach-name">
                            <a href="coach-detail.html">Đặng Quốc Việt</a>
                        </h3>
                        <div class="coach-experience">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                            </svg>
                            <span>4 năm kinh nghiệm</span>
                        </div>
                        <div class="coach-location">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>Hải Châu, Đà Nẵng</span>
                        </div>
                        <div class="coach-tags">
                            <span class="tag">Dạy 1-1</span>
                            <span class="tag">Kỹ thuật</span>
                            <span class="tag">Doubles</span>
                        </div>
                        <div class="coach-stats">
                            <div class="stat">
                                <strong>67</strong>
                                <span>Học viên</span>
                            </div>
                            <div class="stat">
                                <strong>45</strong>
                                <span>Đánh giá</span>
                            </div>
                        </div>
                        <div class="coach-actions">
                            <a href="coach-detail.html" class="btn btn-primary btn-sm">Xem chi tiết</a>
                            <button class="btn btn-outline btn-sm btn-favorite" title="Yêu thích">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <button class="pagination-btn" disabled>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </button>
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn">2</button>
                <button class="pagination-btn">3</button>
                <span class="pagination-dots">...</span>
                <button class="pagination-btn">8</button>
                <button class="pagination-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <!-- Become a Coach CTA -->
    <section class="become-coach-cta">
        <div class="container">
            <div class="cta-card">
                <div class="cta-content">
                    <h2 class="cta-title">Bạn là huấn luyện viên Pickleball?</h2>
                    <p class="cta-description">Đăng ký trở thành giảng viên trên onePickleball.vn và kết nối với hàng ngàn học viên tiềm năng trên toàn quốc.</p>
                    <div class="cta-features">
                        <div class="cta-feature">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <span>Miễn phí đăng ký</span>
                        </div>
                        <div class="cta-feature">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            <span>Tiếp cận học viên</span>
                        </div>
                        <div class="cta-feature">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                            <span>Thu nhập linh hoạt</span>
                        </div>
                    </div>
                    <button class="btn btn-white btn-lg">Đăng ký ngay</button>
                </div>
                <div class="cta-image">
                    <svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="ctaGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:rgba(255,255,255,0.2)"/>
                                <stop offset="100%" style="stop-color:rgba(255,255,255,0.05)"/>
                            </linearGradient>
                        </defs>
                        <circle cx="200" cy="150" r="120" fill="url(#ctaGrad)"/>
                        <circle cx="200" cy="150" r="80" fill="rgba(255,255,255,0.15)"/>
                        <path d="M160 150 L240 150 M200 110 L200 190" stroke="rgba(255,255,255,0.5)" stroke-width="8" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>
@endsection
