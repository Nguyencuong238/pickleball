@extends('layouts.front')

@section('css')
@endsection

@section('content')
<section class="page-header">
        <div class="page-header-background"></div>
        <div class="container">
            <div class="breadcrumb">
                <a href="index.html">Trang chủ</a>
                <span class="separator">/</span>
                <span>Giải đấu</span>
            </div>
            <h1 class="page-title">Giải Đấu Pickleball</h1>
            <p class="page-description">Tìm và đăng ký tham gia các giải đấu Pickleball chuyên nghiệp và phong trào trên toàn quốc</p>
            
            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-box">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-content">
                        <div class="stat-number">48</div>
                        <div class="stat-label">Giải đấu năm nay</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <div class="stat-number">2,847</div>
                        <div class="stat-label">Vận động viên</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <div class="stat-number">5.2 tỷ</div>
                        <div class="stat-label">Tổng giải thưởng</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">📍</div>
                    <div class="stat-content">
                        <div class="stat-number">15</div>
                        <div class="stat-label">Tỉnh/Thành phố</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="tournaments-section section">
        <div class="container">
            <div class="tournaments-layout">
                <!-- Sidebar Filters -->
                <aside class="tournaments-sidebar">
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
                                    <line x1="1" y1="14" x2="7" y2="14"/>
                                    <line x1="9" y1="8" x2="15" y2="8"/>
                                    <line x1="17" y1="16" x2="23" y2="16"/>
                                </svg>
                                Bộ lọc
                            </h3>
                            <button class="filter-reset">Xóa bộ lọc</button>
                        </div>

                        <!-- Search -->
                        <div class="filter-group">
                            <label class="filter-label">Tìm kiếm</label>
                            <div class="search-input-wrapper">
                                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="m21 21-4.35-4.35"/>
                                </svg>
                                <input type="text" class="filter-search" placeholder="Tên giải đấu...">
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="filter-group">
                            <label class="filter-label">Trạng thái</label>
                            <div class="filter-options">
                                <label class="filter-checkbox">
                                    <input type="checkbox" checked>
                                    <span class="checkbox-custom"></span>
                                    <span>Đang mở đăng ký</span>
                                    <span class="filter-count">(12)</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox" checked>
                                    <span class="checkbox-custom"></span>
                                    <span>Sắp mở</span>
                                    <span class="filter-count">(8)</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>Đang diễn ra</span>
                                    <span class="filter-count">(3)</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>Đã kết thúc</span>
                                    <span class="filter-count">(25)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Location Filter -->
                        <div class="filter-group">
                            <label class="filter-label">Địa điểm</label>
                            <select class="filter-select">
                                <option value="">Tất cả khu vực</option>
                                <option value="hcm">TP. Hồ Chí Minh</option>
                                <option value="hn">Hà Nội</option>
                                <option value="dn">Đà Nẵng</option>
                                <option value="ct">Cần Thơ</option>
                                <option value="vt">Vũng Tàu</option>
                                <option value="nt">Nha Trang</option>
                                <option value="hp">Hải Phòng</option>
                            </select>
                        </div>

                        <!-- Level Filter -->
                        <div class="filter-group">
                            <label class="filter-label">Trình độ</label>
                            <div class="filter-options">
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>Beginner</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>Intermediate</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>Advanced</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>Professional</span>
                                </label>
                                <label class="filter-checkbox">
                                    <input type="checkbox">
                                    <span class="checkbox-custom"></span>
                                    <span>Open (Tất cả)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="filter-group">
                            <label class="filter-label">Thời gian</label>
                            <div class="date-range">
                                <input type="date" class="filter-date" placeholder="Từ ngày">
                                <span class="date-separator">-</span>
                                <input type="date" class="filter-date" placeholder="Đến ngày">
                            </div>
                        </div>

                        <!-- Prize Filter -->
                        <div class="filter-group">
                            <label class="filter-label">Giải thưởng</label>
                            <div class="filter-options">
                                <label class="filter-radio">
                                    <input type="radio" name="prize" value="" checked>
                                    <span class="radio-custom"></span>
                                    <span>Tất cả</span>
                                </label>
                                <label class="filter-radio">
                                    <input type="radio" name="prize" value="low">
                                    <span class="radio-custom"></span>
                                    <span>Dưới 100 triệu</span>
                                </label>
                                <label class="filter-radio">
                                    <input type="radio" name="prize" value="mid">
                                    <span class="radio-custom"></span>
                                    <span>100 - 300 triệu</span>
                                </label>
                                <label class="filter-radio">
                                    <input type="radio" name="prize" value="high">
                                    <span class="radio-custom"></span>
                                    <span>Trên 300 triệu</span>
                                </label>
                            </div>
                        </div>

                        <!-- Apply Filters Button -->
                        <button class="btn btn-primary btn-block filter-apply">
                            Áp dụng bộ lọc
                        </button>
                    </div>

                    <!-- Featured Banner -->
                    <div class="sidebar-banner">
                        <div class="banner-content">
                            <span class="banner-badge">🎉 Đặc biệt</span>
                            <h4 class="banner-title">Vietnam National Championship</h4>
                            <p class="banner-text">Giải vô địch quốc gia - Đăng ký sớm để nhận ưu đãi!</p>
                            <a href="tournament-detail.html" class="btn btn-white btn-sm">Xem chi tiết</a>
                        </div>
                    </div>
                </aside>

                <!-- Main Content Area -->
                <div class="tournaments-main">
                    <!-- Toolbar -->
                    <div class="tournaments-toolbar">
                        <div class="toolbar-left">
                            <h2 class="toolbar-title">Tìm thấy <span class="result-count">48</span> giải đấu</h2>
                        </div>
                        <div class="toolbar-right">
                            <div class="view-toggle">
                                <button class="view-btn active" data-view="grid" title="Grid view">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <rect x="3" y="3" width="7" height="7"/>
                                        <rect x="14" y="3" width="7" height="7"/>
                                        <rect x="14" y="14" width="7" height="7"/>
                                        <rect x="3" y="14" width="7" height="7"/>
                                    </svg>
                                </button>
                                <button class="view-btn" data-view="list" title="List view">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <line x1="8" y1="6" x2="21" y2="6"/>
                                        <line x1="8" y1="12" x2="21" y2="12"/>
                                        <line x1="8" y1="18" x2="21" y2="18"/>
                                        <line x1="3" y1="6" x2="3.01" y2="6"/>
                                        <line x1="3" y1="12" x2="3.01" y2="12"/>
                                        <line x1="3" y1="18" x2="3.01" y2="18"/>
                                    </svg>
                                </button>
                            </div>
                            <select class="sort-select">
                                <option value="date-asc">Ngày diễn ra (gần nhất)</option>
                                <option value="date-desc">Ngày diễn ra (xa nhất)</option>
                                <option value="prize-desc">Giải thưởng (cao nhất)</option>
                                <option value="prize-asc">Giải thưởng (thấp nhất)</option>
                                <option value="name-asc">Tên A-Z</option>
                                <option value="name-desc">Tên Z-A</option>
                            </select>
                        </div>
                    </div>

                    <!-- Active Filters Tags -->
                    <div class="active-filters">
                        <span class="filter-tag">
                            Đang mở đăng ký
                            <button class="tag-remove">&times;</button>
                        </span>
                        <span class="filter-tag">
                            Sắp mở
                            <button class="tag-remove">&times;</button>
                        </span>
                    </div>

                    <!-- Tournaments Grid -->
                    <div class="tournaments-grid" id="tournamentsGrid">
                        <!-- Tournament Card 1 -->
                        <div class="tournament-item">
                            <a href="tournament-detail.html" class="tournament-link">
                                <div class="tournament-image">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 250'%3E%3Cdefs%3E%3ClinearGradient id='t1' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%230099CC;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23t1)' width='400' height='250'/%3E%3Ctext x='200' y='125' font-family='Arial' font-size='28' fill='white' text-anchor='middle' dominant-baseline='middle' font-weight='bold'%3EHCM OPEN 2025%3C/text%3E%3C/svg%3E" alt="HCM Open 2025">
                                    <div class="tournament-badges">
                                        <span class="badge badge-featured">Featured</span>
                                        <span class="badge badge-status status-open">Đang mở</span>
                                    </div>
                                    <div class="tournament-overlay">
                                        <span class="overlay-text">Xem chi tiết →</span>
                                    </div>
                                </div>
                                <div class="tournament-content">
                                    <div class="tournament-date-badge">
                                        <div class="date-icon">📅</div>
                                        <div class="date-text">
                                            <span class="date-day">15-17</span>
                                            <span class="date-month">Tháng 12, 2025</span>
                                        </div>
                                    </div>
                                    <h3 class="tournament-title">HCM Pickleball Open 2025</h3>
                                    <p class="tournament-excerpt">Giải đấu mở rộng quy mô lớn nhất năm với tổng giá trị giải thưởng 500 triệu đồng</p>
                                    
                                    <div class="tournament-meta">
                                        <div class="meta-item">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                                <circle cx="12" cy="10" r="3"/>
                                            </svg>
                                            <span>Sân Rạch Chiếc, Q2</span>
                                        </div>
                                        <div class="meta-item">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                <circle cx="9" cy="7" r="4"/>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                            </svg>
                                            <span>128 VĐV</span>
                                        </div>
                                        <div class="meta-item">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                            <span>Open</span>
                                        </div>
                                    </div>

                                    <div class="tournament-footer">
                                        <div class="tournament-prize">
                                            <span class="prize-label">Giải thưởng</span>
                                            <span class="prize-amount">500.000.000 VNĐ</span>
                                        </div>
                                        <button class="btn btn-primary btn-sm" onclick="event.preventDefault(); alert('Đăng ký giải đấu!');">
                                            Đăng ký ngay
                                        </button>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Tournament Card 2 -->
                        <div class="tournament-item">
                            <a href="tournament-detail.html" class="tournament-link">
                                <div class="tournament-image">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 250'%3E%3Cdefs%3E%3ClinearGradient id='t2' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23FF6B6B;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%23FF8E53;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23t2)' width='400' height='250'/%3E%3Ctext x='200' y='125' font-family='Arial' font-size='24' fill='white' text-anchor='middle' dominant-baseline='middle' font-weight='bold'%3EHN MASTERS%3C/text%3E%3C/svg%3E" alt="Hà Nội Masters">
                                    <div class="tournament-badges">
                                        <span class="badge badge-status status-open">Đang mở</span>
                                    </div>
                                    <div class="tournament-overlay">
                                        <span class="overlay-text">Xem chi tiết →</span>
                                    </div>
                                </div>
                                <div class="tournament-content">
                                    <div class="tournament-date-badge">
                                        <div class="date-icon">📅</div>
                                        <div class="date-text">
                                            <span class="date-day">22-24</span>
                                            <span class="date-month">Tháng 12, 2025</span>
                                        </div>
                                    </div>
                                    <h3 class="tournament-title">Hà Nội Pickleball Masters</h3>
                                    <p class="tournament-excerpt">Giải đấu dành cho các tay vợt chuyên nghiệp hạng Masters trở lên</p>
                                    
                                    <div class="tournament-meta">
                                        <div class="meta-item">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                                <circle cx="12" cy="10" r="3"/>
                                            </svg>
                                            <span>Cung TDTT Quốc Gia</span>
                                        </div>
                                        <div class="meta-item">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                <circle cx="9" cy="7" r="4"/>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                            </svg>
                                            <span>64 VĐV</span>
                                        </div>
                                        <div class="meta-item">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                            <span>Professional</span>
                                        </div>
                                    </div>

                                    <div class="tournament-footer">
                                        <div class="tournament-prize">
                                            <span class="prize-label">Giải thưởng</span>
                                            <span class="prize-amount">300.000.000 VNĐ</span>
                                        </div>
                                        <button class="btn btn-primary btn-sm" onclick="event.preventDefault(); alert('Đăng ký giải đấu!');">
                                            Đăng ký ngay
                                        </button>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Tournament Card 3 -->
                        <div class="tournament-item">
                            <a href="tournament-detail.html" class="tournament-link">
                                <div class="tournament-image">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 250'%3E%3Cdefs%3E%3ClinearGradient id='t3' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%234ECDC4;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%2344A08D;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23t3)' width='400' height='250'/%3E%3Ctext x='200' y='125' font-family='Arial' font-size='24' fill='white' text-anchor='middle' dominant-baseline='middle' font-weight='bold'%3EDA NANG BEACH%3C/text%3E%3C/svg%3E" alt="Đà Nẵng Beach">
                                    <div class="tournament-badges">
                                        <span class="badge badge-status status-soon">Sắp mở</span>
                                    </div>
                                    <div class="tournament-overlay">
                                        <span class="overlay-text">Xem chi tiết →</span>
                                    </div>
                                </div>
                                <div class="tournament-content">
                                    <div class="tournament-date-badge">
                                        <div class="date-icon">📅</div>
                                        <div class="date-text">
                                            <span class="date-day">05-07</span>
                                            <span class="date-month">Tháng 1, 2026</span>
                                        </div>
                                    </div>
                                    <h3 class="tournament-title">Đà Nẵng Beach Pickleball</h3>
                                    <p class="tournament-excerpt">Giải đấu bãi biển độc đáo với không khí sôi động và giải thưởng hấp dẫn</p>
                                    
                                    <div class="tournament-meta">
                                        <div class="meta-item">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                                <circle cx="12" cy="10" r="3"/>
                                            </svg>
                                            <span>Bãi Biển Mỹ Khê</span>
                                        </div>
                                        <div class="meta-item">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                <circle cx="9" cy="7" r="4"/>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                            </svg>
                                            <span>96 VĐV</span>
                                        </div>
                                        <div class="meta-item">
                                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                            <span>Advanced</span>
                                        </div>
                                    </div>

                                    <div class="tournament-footer">
                                        <div class="tournament-prize">
                                            <span class="prize-label">Giải thưởng</span>
                                            <span class="prize-amount">200.000.000 VNĐ</span>
                                        </div>
                                        <button class="btn btn-outline btn-sm" onclick="event.preventDefault(); alert('Đăng ký sớm!');">
                                            Đăng ký sớm
                                        </button>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Add more tournament cards (4-12) with similar structure but different data -->
                        <!-- Tournament Card 4-12 would follow same pattern -->
                        
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
                            <button class="pagination-number">10</button>
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
                <h2 class="cta-title">Không tìm thấy giải đấu phù hợp?</h2>
                <p class="cta-description">Đăng ký nhận thông báo về các giải đấu mới và ưu đãi đặc biệt</p>
                <button class="btn btn-primary btn-lg">Đăng ký nhận thông báo</button>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script src="{{ asset('assets/js/tournaments.js') }}"></script>
@endsection
