@extends('layouts.front')

@section('css')
@endsection

@section('content')
    <section class="hero" id="home">
        <div class="hero-background"></div>
        <div class="container">
            <div class="hero-content">
                <span class="hero-badge">Cộng đồng Pickleball #1 Việt Nam</span>
                <h1 class="hero-title">
                    Chào mừng đến với<br>
                    <span class="gradient-text">onePickleball</span>
                </h1>
                <p class="hero-description">
                    Nền tảng kết nối cộng đồng Pickleball hàng đầu tại Việt Nam. 
                    Tìm sân, đăng ký giải đấu, kết nối đối thủ và cập nhật tin tức mới nhất.
                </p>
                <div class="hero-actions">
                    <button class="btn btn-primary btn-lg">Tham gia ngay</button>
                    <button class="btn btn-secondary btn-lg">Tìm hiểu thêm</button>
                </div>
                
                <!-- Stats -->
                <div class="hero-stats">
                    <div class="stat-item">
                        <h3 class="stat-number">2,500+</h3>
                        <p class="stat-label">Thành viên</p>
                    </div>
                    <div class="stat-item">
                        <h3 class="stat-number">50+</h3>
                        <p class="stat-label">Sân thi đấu</p>
                    </div>
                    <div class="stat-item">
                        <h3 class="stat-number">120+</h3>
                        <p class="stat-label">Giải đấu/năm</p>
                    </div>
                    <div class="stat-item">
                        <h3 class="stat-number">300+</h3>
                        <p class="stat-label">Buổi Social</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Tournaments Section -->
    <section class="tournaments section" id="tournaments">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Giải đấu</span>
                <h2 class="section-title">Các giải đấu sắp diễn ra</h2>
                <p class="section-description">Đăng ký tham gia các giải đấu Pickleball chuyên nghiệp và phong trào</p>
            </div>
            
            <div class="tournaments-grid">
                <!-- Tournament 1 -->
                <div class="tournament-card">
                    <div class="tournament-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 250'%3E%3Cdefs%3E%3ClinearGradient id='g1' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%230099CC;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23g1)' width='400' height='250'/%3E%3Ctext x='200' y='125' font-family='Arial' font-size='24' fill='white' text-anchor='middle' dominant-baseline='middle'%3ETournament%3C/text%3E%3C/svg%3E" alt="HCM Open 2025">
                        <span class="tournament-status status-open">Đang mở</span>
                    </div>
                    <div class="tournament-content">
                        <div class="tournament-date">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <span>15-17 Tháng 12, 2025</span>
                        </div>
                        <h3 class="tournament-title">HCM Pickleball Open 2025</h3>
                        <p class="tournament-description">Giải đấu mở rộng quy mô lớn nhất năm với tổng giá trị giải thưởng 500 triệu đồng</p>
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
                                <span>128 vận động viên</span>
                            </div>
                        </div>
                        <div class="tournament-footer">
                            <span class="tournament-prize">🏆 500.000.000 VNĐ</span>
                            <button class="btn btn-primary btn-sm">Đăng ký ngay</button>
                        </div>
                    </div>
                </div>
                <!-- Tournament 2 -->
                <div class="tournament-card">
                    <div class="tournament-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 250'%3E%3Cdefs%3E%3ClinearGradient id='g2' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23FF6B6B;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%23FF8E53;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23g2)' width='400' height='250'/%3E%3Ctext x='200' y='125' font-family='Arial' font-size='24' fill='white' text-anchor='middle' dominant-baseline='middle'%3ETournament%3C/text%3E%3C/svg%3E" alt="Hà Nội Masters">
                        <span class="tournament-status status-open">Đang mở</span>
                    </div>
                    <div class="tournament-content">
                        <div class="tournament-date">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <span>22-24 Tháng 12, 2025</span>
                        </div>
                        <h3 class="tournament-title">Hà Nội Pickleball Masters</h3>
                        <p class="tournament-description">Giải đấu dành cho các tay vợt chuyên nghiệp hạng Masters trở lên</p>
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
                                <span>64 vận động viên</span>
                            </div>
                        </div>
                        <div class="tournament-footer">
                            <span class="tournament-prize">🏆 300.000.000 VNĐ</span>
                            <button class="btn btn-primary btn-sm">Đăng ký ngay</button>
                        </div>
                    </div>
                </div>
                <!-- Tournament 3 -->
                <div class="tournament-card">
                    <div class="tournament-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 250'%3E%3Cdefs%3E%3ClinearGradient id='g3' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%234ECDC4;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%2344A08D;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23g3)' width='400' height='250'/%3E%3Ctext x='200' y='125' font-family='Arial' font-size='24' fill='white' text-anchor='middle' dominant-baseline='middle'%3ETournament%3C/text%3E%3C/svg%3E" alt="Đà Nẵng Beach">
                        <span class="tournament-status status-soon">Sắp mở</span>
                    </div>
                    <div class="tournament-content">
                        <div class="tournament-date">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <span>05-07 Tháng 1, 2026</span>
                        </div>
                        <h3 class="tournament-title">Đà Nẵng Beach Pickleball</h3>
                        <p class="tournament-description">Giải đấu bãi biển độc đáo với không khí sôi động và giải thưởng hấp dẫn</p>
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
                                <span>96 vận động viên</span>
                            </div>
                        </div>
                        <div class="tournament-footer">
                            <span class="tournament-prize">🏆 200.000.000 VNĐ</span>
                            <button class="btn btn-outline btn-sm">Đăng ký sớm</button>
                        </div>
                    </div>
                </div>
                <!-- Tournament 4 -->
                <div class="tournament-card">
                    <div class="tournament-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 250'%3E%3Cdefs%3E%3ClinearGradient id='g4' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23A8E6CF;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%2356AB91;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23g4)' width='400' height='250'/%3E%3Ctext x='200' y='125' font-family='Arial' font-size='24' fill='white' text-anchor='middle' dominant-baseline='middle'%3ETournament%3C/text%3E%3C/svg%3E" alt="Cần Thơ Cup">
                        <span class="tournament-status status-soon">Sắp mở</span>
                    </div>
                    <div class="tournament-content">
                        <div class="tournament-date">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <span>12-14 Tháng 1, 2026</span>
                        </div>
                        <h3 class="tournament-title">Cần Thơ Mekong Cup</h3>
                        <p class="tournament-description">Giải đấu khu vực miền Tây Nam Bộ dành cho mọi trình độ</p>
                        <div class="tournament-meta">
                            <div class="meta-item">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <span>Sân TDTT CT</span>
                            </div>
                            <div class="meta-item">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                                <span>80 vận động viên</span>
                            </div>
                        </div>
                        <div class="tournament-footer">
                            <span class="tournament-prize">🏆 150.000.000 VNĐ</span>
                            <button class="btn btn-outline btn-sm">Đăng ký sớm</button>
                        </div>
                    </div>
                </div>
                <!-- Tournament 5 -->
                <div class="tournament-card">
                    <div class="tournament-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 250'%3E%3Cdefs%3E%3ClinearGradient id='g5' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23FFD93D;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%23F4A261;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23g5)' width='400' height='250'/%3E%3Ctext x='200' y='125' font-family='Arial' font-size='24' fill='white' text-anchor='middle' dominant-baseline='middle'%3ETournament%3C/text%3E%3C/svg%3E" alt="Vũng Tàu Open">
                        <span class="tournament-status status-soon">Sắp mở</span>
                    </div>
                    <div class="tournament-content">
                        <div class="tournament-date">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <span>19-21 Tháng 1, 2026</span>
                        </div>
                        <h3 class="tournament-title">Vũng Tàu Seaside Open</h3>
                        <p class="tournament-description">Kết hợp nghỉ dưỡng và thi đấu tại thành phố biển xinh đẹp</p>
                        <div class="tournament-meta">
                            <div class="meta-item">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <span>Resort Paradise</span>
                            </div>
                            <div class="meta-item">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                                <span>72 vận động viên</span>
                            </div>
                        </div>
                        <div class="tournament-footer">
                            <span class="tournament-prize">🏆 180.000.000 VNĐ</span>
                            <button class="btn btn-outline btn-sm">Đăng ký sớm</button>
                        </div>
                    </div>
                </div>
                <!-- Tournament 6 -->
                <div class="tournament-card">
                    <div class="tournament-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 250'%3E%3Cdefs%3E%3ClinearGradient id='g6' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%239D84B7;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%236C5B7B;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23g6)' width='400' height='250'/%3E%3Ctext x='200' y='125' font-family='Arial' font-size='24' fill='white' text-anchor='middle' dominant-baseline='middle'%3ETournament%3C/text%3E%3C/svg%3E" alt="National Championship">
                        <span class="tournament-status status-upcoming">Sắp tới</span>
                    </div>
                    <div class="tournament-content">
                        <div class="tournament-date">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <span>15-20 Tháng 2, 2026</span>
                        </div>
                        <h3 class="tournament-title">Vietnam National Championship</h3>
                        <p class="tournament-description">Giải vô địch quốc gia - Sân chơi lớn nhất trong năm</p>
                        <div class="tournament-meta">
                            <div class="meta-item">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <span>TBA - Hà Nội</span>
                            </div>
                            <div class="meta-item">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                                <span>256 vận động viên</span>
                            </div>
                        </div>
                        <div class="tournament-footer">
                            <span class="tournament-prize">🏆 1.000.000.000 VNĐ</span>
                            <button class="btn btn-outline btn-sm">Thông báo sớm</button>
                        </div>
                    </div>
                </div>
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
                <div class="court-card">
                    <div class="court-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 280'%3E%3Crect fill='%2300A86B' width='400' height='280'/%3E%3Cline x1='0' y1='140' x2='400' y2='140' stroke='white' stroke-width='4'/%3E%3Cline x1='200' y1='0' x2='200' y2='280' stroke='white' stroke-width='2'/%3E%3Crect x='50' y='50' width='300' height='180' fill='none' stroke='white' stroke-width='3'/%3E%3Ctext x='200' y='160' font-family='Arial' font-size='20' fill='white' text-anchor='middle'%3ECOURT%3C/text%3E%3C/svg%3E" alt="Sân Pickleball Rạch Chiếc">
                        <div class="court-overlay">
                            <button class="btn btn-white btn-sm">Xem chi tiết</button>
                        </div>
                    </div>
                    <div class="court-content">
                        <div class="court-header">
                            <h3 class="court-name">Pickleball Rạch Chiếc</h3>
                            <div class="court-rating">
                                <span class="rating-star">⭐</span>
                                <span class="rating-value">4.8</span>
                            </div>
                        </div>
                        <div class="court-location">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>123 Lương Định Của, Quận 2, TP.HCM</span>
                        </div>
                        <div class="court-features">
                            <span class="feature-tag">🏟️ 8 sân</span>
                            <span class="feature-tag">🚿 Phòng tắm</span>
                            <span class="feature-tag">🅿️ Bãi đỗ xe</span>
                            <span class="feature-tag">☕ Canteen</span>
                        </div>
                        <div class="court-info">
                            <div class="info-item">
                                <span class="info-label">Giờ mở cửa:</span>
                                <span class="info-value">05:00 - 23:00</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Giá thuê:</span>
                                <span class="info-value highlight">150.000đ - 300.000đ/giờ</span>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-block">Đặt sân ngay</button>
                    </div>
                </div>
                <!-- Court 2 -->
                <div class="court-card">
                    <div class="court-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 280'%3E%3Crect fill='%230088CC' width='400' height='280'/%3E%3Cline x1='0' y1='140' x2='400' y2='140' stroke='white' stroke-width='4'/%3E%3Cline x1='200' y1='0' x2='200' y2='280' stroke='white' stroke-width='2'/%3E%3Crect x='50' y='50' width='300' height='180' fill='none' stroke='white' stroke-width='3'/%3E%3Ctext x='200' y='160' font-family='Arial' font-size='20' fill='white' text-anchor='middle'%3ECOURT%3C/text%3E%3C/svg%3E" alt="Sân Pickleball Thảo Điền">
                        <div class="court-overlay">
                            <button class="btn btn-white btn-sm">Xem chi tiết</button>
                        </div>
                    </div>
                    <div class="court-content">
                        <div class="court-header">
                            <h3 class="court-name">Thảo Điền Sports Club</h3>
                            <div class="court-rating">
                                <span class="rating-star">⭐</span>
                                <span class="rating-value">4.9</span>
                            </div>
                        </div>
                        <div class="court-location">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>456 Xa Lộ Hà Nội, Quận Thủ Đức, TP.HCM</span>
                        </div>
                        <div class="court-features">
                            <span class="feature-tag">🏟️ 6 sân</span>
                            <span class="feature-tag">🚿 Phòng tắm VIP</span>
                            <span class="feature-tag">🅿️ Bãi đỗ xe</span>
                            <span class="feature-tag">🏋️ Gym</span>
                        </div>
                        <div class="court-info">
                            <div class="info-item">
                                <span class="info-label">Giờ mở cửa:</span>
                                <span class="info-value">06:00 - 22:00</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Giá thuê:</span>
                                <span class="info-value highlight">200.000đ - 400.000đ/giờ</span>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-block">Đặt sân ngay</button>
                    </div>
                </div>
                <!-- Court 3 -->
                <div class="court-card">
                    <div class="court-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 280'%3E%3Crect fill='%23FF6B6B' width='400' height='280'/%3E%3Cline x1='0' y1='140' x2='400' y2='140' stroke='white' stroke-width='4'/%3E%3Cline x1='200' y1='0' x2='200' y2='280' stroke='white' stroke-width='2'/%3E%3Crect x='50' y='50' width='300' height='180' fill='none' stroke='white' stroke-width='3'/%3E%3Ctext x='200' y='160' font-family='Arial' font-size='20' fill='white' text-anchor='middle'%3ECOURT%3C/text%3E%3C/svg%3E" alt="Sân Pickleball Cầu Giấy">
                        <div class="court-overlay">
                            <button class="btn btn-white btn-sm">Xem chi tiết</button>
                        </div>
                    </div>
                    <div class="court-content">
                        <div class="court-header">
                            <h3 class="court-name">Cầu Giấy Pickleball Arena</h3>
                            <div class="court-rating">
                                <span class="rating-star">⭐</span>
                                <span class="rating-value">4.7</span>
                            </div>
                        </div>
                        <div class="court-location">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>789 Trần Duy Hưng, Cầu Giấy, Hà Nội</span>
                        </div>
                        <div class="court-features">
                            <span class="feature-tag">🏟️ 10 sân</span>
                            <span class="feature-tag">🚿 Phòng tắm</span>
                            <span class="feature-tag">🅿️ Bãi đỗ xe</span>
                            <span class="feature-tag">🏪 Cửa hàng</span>
                        </div>
                        <div class="court-info">
                            <div class="info-item">
                                <span class="info-label">Giờ mở cửa:</span>
                                <span class="info-value">05:30 - 23:00</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Giá thuê:</span>
                                <span class="info-value highlight">120.000đ - 250.000đ/giờ</span>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-block">Đặt sân ngay</button>
                    </div>
                </div>
            </div>
            
            <div class="section-cta">
                <button class="btn btn-primary">Xem tất cả sân thi đấu</button>
            </div>
        </div>
    </section>
    <!-- Social Play Section -->
    <section class="social section" id="social">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Giờ đi đấu Social</span>
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
                    <p class="social-description">Buổi chơi dành cho người mới bắt đầu, môi trường thân thiện và hỗ trợ tối đa</p>
                    <div class="social-info">
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>Sân Rạch Chiếc, Q2, HCM</span>
                        </div>
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            <span>12/20 người đã đăng ký</span>
                        </div>
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
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
                    <p class="social-description">Đấu xoay vòng với nhiều đối thủ khác nhau, phù hợp trình độ trung bình</p>
                    <div class="social-info">
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>Thảo Điền Sports Club, Thủ Đức</span>
                        </div>
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            <span>18/24 người đã đăng ký</span>
                        </div>
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
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
                    <p class="social-description">Buổi chơi mức độ cao cho các tay vợt giỏi, thi đấu căng thẳng và chuyên nghiệp</p>
                    <div class="social-info">
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>Cầu Giấy Arena, Hà Nội</span>
                        </div>
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            <span>14/16 người đã đăng ký</span>
                        </div>
                        <div class="info-row">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
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
    </section>
    <!-- News Section -->
    <section class="news section section-alt" id="news">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Tin tức</span>
                <h2 class="section-title">Tin tức mới nhất</h2>
                <p class="section-description">Cập nhật tin tức, kiến thức và xu hướng Pickleball</p>
            </div>
            
            <div class="news-grid">
                <!-- News Article 1 -->
                <article class="news-card">
                    <div class="news-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 400'%3E%3Cdefs%3E%3ClinearGradient id='news1' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%230099CC;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23news1)' width='600' height='400'/%3E%3Ctext x='300' y='200' font-family='Arial' font-size='32' fill='white' text-anchor='middle' dominant-baseline='middle'%3ENEWS%3C/text%3E%3C/svg%3E" alt="Pickleball News">
                        <span class="news-category">Sự kiện</span>
                    </div>
                    <div class="news-content">
                        <div class="news-meta">
                            <span class="news-date">10 Tháng 12, 2025</span>
                            <span class="news-read-time">5 phút đọc</span>
                        </div>
                        <h3 class="news-title">HCM Pickleball Open 2025: Giải Đấu Lớn Nhất Năm Sắp Khởi Tranh</h3>
                        <p class="news-excerpt">
                            Với tổng giá trị giải thưởng lên đến 500 triệu đồng, HCM Pickleball Open 2025 hứa hẹn sẽ là sự kiện thể thao đáng chú ý nhất trong năm. Giải đấu sẽ quy tụ hơn 128 vận động viên...
                        </p>
                        <a href="#" class="news-link">
                            Đọc tiếp
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                                <polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </a>
                    </div>
                </article>
                <!-- News Article 2 -->
                <article class="news-card">
                    <div class="news-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 400'%3E%3Cdefs%3E%3ClinearGradient id='news2' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23FF6B6B;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%23FF8E53;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23news2)' width='600' height='400'/%3E%3Ctext x='300' y='200' font-family='Arial' font-size='32' fill='white' text-anchor='middle' dominant-baseline='middle'%3ENEWS%3C/text%3E%3C/svg%3E" alt="Pickleball Tips">
                        <span class="news-category">Kỹ thuật</span>
                    </div>
                    <div class="news-content">
                        <div class="news-meta">
                            <span class="news-date">08 Tháng 12, 2025</span>
                            <span class="news-read-time">7 phút đọc</span>
                        </div>
                        <h3 class="news-title">10 Kỹ Thuật Cơ Bản Giúp Bạn Cải Thiện Kỹ Năng Pickleball</h3>
                        <p class="news-excerpt">
                            Từ cách cầm vợt đúng cách đến các kỹ thuật di chuyển hiệu quả, cùng khám phá những bí quyết giúp bạn trở thành một tay vợt giỏi hơn...
                        </p>
                        <a href="#" class="news-link">
                            Đọc tiếp
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                                <polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </a>
                    </div>
                </article>
                <!-- News Article 3 -->
                <article class="news-card">
                    <div class="news-image">
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 400'%3E%3Cdefs%3E%3ClinearGradient id='news3' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%234ECDC4;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%2344A08D;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23news3)' width='600' height='400'/%3E%3Ctext x='300' y='200' font-family='Arial' font-size='32' fill='white' text-anchor='middle' dominant-baseline='middle'%3ENEWS%3C/text%3E%3C/svg%3E" alt="Pickleball Community">
                        <span class="news-category">Cộng đồng</span>
                    </div>
                    <div class="news-content">
                        <div class="news-meta">
                            <span class="news-date">05 Tháng 12, 2025</span>
                            <span class="news-read-time">4 phút đọc</span>
                        </div>
                        <h3 class="news-title">Cộng Đồng Pickleball Việt Nam Đạt Mốc 10.000 Thành Viên</h3>
                        <p class="news-excerpt">
                            Một cột mốc đáng tự hào khi cộng đồng Pickleball Việt Nam chính thức vượt con số 10.000 người chơi tích cực trên khắp cả nước...
                        </p>
                        <a href="#" class="news-link">
                            Đọc tiếp
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                                <polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </a>
                    </div>
                </article>
            
            </div>
            
            <div class="section-cta">
                <button class="btn btn-primary">Xem tất cả tin tức</button>
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
                    <input type="email" placeholder="Nhập email của bạn" class="cta-input">
                    <button class="btn btn-primary btn-lg">Đăng ký ngay</button>
                </div>
                <p class="cta-note">🎁 Tặng voucher 100.000đ cho 100 người đăng ký đầu tiên</p>
            </div>
        </div>
    </section>
@endsection
