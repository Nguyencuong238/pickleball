@extends('layouts.front')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tournament-detail.css') }}">
@endsection

@section('content')
    <section class="tournament-hero">
        <div class="hero-background">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1920 600'%3E%3Cdefs%3E%3ClinearGradient id='hero-grad' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%2300D9B5;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%230099CC;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Crect fill='url(%23hero-grad)' width='1920' height='600'/%3E%3Ctext x='960' y='300' font-family='Arial' font-size='72' fill='white' text-anchor='middle' dominant-baseline='middle' font-weight='bold'%3EHCM OPEN 2025%3C/text%3E%3C/svg%3E" alt="Tournament Banner">
            <div class="hero-overlay"></div>
        </div>
        
        <div class="container">
            <div class="breadcrumb">
                <a href="index.html">Trang chủ</a>
                <span class="separator">/</span>
                <a href="tournaments.html">Giải đấu</a>
                <span class="separator">/</span>
                <span>HCM Pickleball Open 2025</span>
            </div>
            
            <div class="hero-content">
                <div class="hero-badges">
                    <span class="hero-badge badge-featured">⭐ Featured</span>
                    <span class="hero-badge badge-open">✓ Đang mở đăng ký</span>
                </div>
                
                <h1 class="hero-title">HCM Pickleball Open 2025</h1>
                <p class="hero-subtitle">Giải đấu Pickleball mở rộng quy mô lớn nhất năm</p>
                
                <div class="hero-meta">
                    <div class="hero-meta-item">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <span>15-17 Tháng 12, 2025</span>
                    </div>
                    <div class="hero-meta-item">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span>Sân Rạch Chiếc Sport Complex, Quận 2, TP.HCM</span>
                    </div>
                    <div class="hero-meta-item">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span>128 vận động viên</span>
                    </div>
                </div>
                
                <div class="hero-actions">
                    <button class="btn btn-primary btn-lg">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <line x1="20" y1="8" x2="20" y2="14"/>
                            <line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                        Đăng ký tham gia
                    </button>
                    <button class="btn btn-secondary btn-lg">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
                            <line x1="4" y1="22" x2="4" y2="15"/>
                        </svg>
                        Lưu giải đấu
                    </button>
                    <button class="btn btn-white btn-lg">
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
        </div>
    </section>

    <!-- Tournament Stats Bar -->
    <section class="stats-bar">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-info">
                        <div class="stat-value">500.000.000 VNĐ</div>
                        <div class="stat-label">Tổng giải thưởng</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <div class="stat-value">128</div>
                        <div class="stat-label">Số vận động viên</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-info">
                        <div class="stat-value">8</div>
                        <div class="stat-label">Số sân thi đấu</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-info">
                        <div class="stat-value">3 ngày</div>
                        <div class="stat-label">Thời gian diễn ra</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-info">
                        <div class="stat-value">15 ngày</div>
                        <div class="stat-label">Còn lại để đăng ký</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="tournament-detail-section section">
        <div class="container">
            <div class="detail-layout">
                <!-- Main Content -->
                <div class="detail-main">
                    <!-- Tab Navigation -->
                    <div class="tab-navigation">
                        <button class="tab-btn active" data-tab="overview">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                            </svg>
                            Tổng quan
                        </button>
                        <button class="tab-btn" data-tab="schedule">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            Lịch thi đấu
                        </button>
                        <button class="tab-btn" data-tab="results">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                            Kết quả
                        </button>
                        <button class="tab-btn" data-tab="participants">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            Danh sách VĐV
                        </button>
                        <button class="tab-btn" data-tab="gallery">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                            Gallery
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Overview Tab -->
                        <div class="tab-pane active" id="overview">
                            <div class="content-card">
                                <h2 class="content-title">Giới thiệu giải đấu</h2>
                                <div class="content-text">
                                    <p>HCM Pickleball Open 2025 là giải đấu Pickleball mở rộng quy mô lớn nhất trong năm, được tổ chức tại Sân Rạch Chiếc Sport Complex với cơ sở vật chất hiện đại bậc nhất khu vực phía Nam.</p>
                                    
                                    <p>Giải đấu quy tụ 128 vận động viên hàng đầu từ khắp cả nước, thi đấu trong 3 ngày liên tục với tổng giá trị giải thưởng lên đến 500 triệu đồng. Đây là cơ hội tuyệt vời để các tay vợt thể hiện kỹ năng, giao lưu học hỏi và nâng cao trình độ chơi.</p>
                                    
                                    <h3>Điểm nổi bật:</h3>
                                    <ul>
                                        <li>✓ Tổng giải thưởng: 500.000.000 VNĐ</li>
                                        <li>✓ 8 sân thi đấu chuẩn quốc tế</li>
                                        <li>✓ Hệ thống livestream chuyên nghiệp</li>
                                        <li>✓ Trọng tài quốc tế được chứng nhận</li>
                                        <li>✓ Khu vực ẩm thực và giải trí phong phú</li>
                                        <li>✓ Miễn phí bãi đỗ xe cho VĐV và khán giả</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Format Section -->
                            <div class="content-card">
                                <h2 class="content-title">Thể thức thi đấu</h2>
                                <div class="format-grid">
                                    <div class="format-card">
                                        <div class="format-icon">🎯</div>
                                        <h4>Vòng loại</h4>
                                        <p>Round Robin - Đấu vòng tròn tính điểm</p>
                                    </div>
                                    <div class="format-card">
                                        <div class="format-icon">⚔️</div>
                                        <h4>Vòng đấu loại</h4>
                                        <p>Single Elimination - Thua 1 trận bị loại</p>
                                    </div>
                                    <div class="format-card">
                                        <div class="format-icon">🏆</div>
                                        <h4>Chung kết</h4>
                                        <p>Best of 3 - Đấu 3 set, thắng 2 set</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Categories Section -->
                            <div class="content-card">
                                <h2 class="content-title">Hạng đấu</h2>
                                <div class="categories-list">
                                    <div class="category-item">
                                        <div class="category-header">
                                            <h4>🥇 Open Singles (Đơn mở rộng)</h4>
                                            <span class="category-prize">200.000.000 VNĐ</span>
                                        </div>
                                        <p class="category-desc">Dành cho tất cả vận động viên, không phân biệt trình độ</p>
                                        <div class="category-meta">
                                            <span>128 VĐV</span>
                                            <span>•</span>
                                            <span>Open</span>
                                        </div>
                                    </div>
                                    
                                    <div class="category-item">
                                        <div class="category-header">
                                            <h4>🥈 Open Doubles (Đôi mở rộng)</h4>
                                            <span class="category-prize">150.000.000 VNĐ</span>
                                        </div>
                                        <p class="category-desc">Đấu đôi nam, nữ hoặc hỗn hợp</p>
                                        <div class="category-meta">
                                            <span>64 cặp</span>
                                            <span>•</span>
                                            <span>Open</span>
                                        </div>
                                    </div>
                                    
                                    <div class="category-item">
                                        <div class="category-header">
                                            <h4>🥉 Masters Singles 50+ (Đơn thạc sĩ)</h4>
                                            <span class="category-prize">100.000.000 VNĐ</span>
                                        </div>
                                        <p class="category-desc">Dành cho VĐV từ 50 tuổi trở lên</p>
                                        <div class="category-meta">
                                            <span>32 VĐV</span>
                                            <span>•</span>
                                            <span>50+</span>
                                        </div>
                                    </div>
                                    
                                    <div class="category-item">
                                        <div class="category-header">
                                            <h4>🏅 Mixed Doubles (Đôi hỗn hợp)</h4>
                                            <span class="category-prize">50.000.000 VNĐ</span>
                                        </div>
                                        <p class="category-desc">Mỗi đội gồm 1 nam và 1 nữ</p>
                                        <div class="category-meta">
                                            <span>32 cặp</span>
                                            <span>•</span>
                                            <span>Mixed</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rules Section -->
                            <div class="content-card">
                                <h2 class="content-title">Quy định thi đấu</h2>
                                <div class="rules-list">
                                    <div class="rule-item">
                                        <span class="rule-number">1</span>
                                        <div class="rule-content">
                                            <h4>Điều kiện tham gia</h4>
                                            <p>VĐV phải từ 18 tuổi trở lên, có sức khỏe tốt và có kinh nghiệm thi đấu Pickleball.</p>
                                        </div>
                                    </div>
                                    <div class="rule-item">
                                        <span class="rule-number">2</span>
                                        <div class="rule-content">
                                            <h4>Trang thiết bị</h4>
                                            <p>VĐV tự chuẩn bị vợt, BTC cung cấp bóng thi đấu chính thức. Trang phục thể thao phù hợp.</p>
                                        </div>
                                    </div>
                                    <div class="rule-item">
                                        <span class="rule-number">3</span>
                                        <div class="rule-content">
                                            <h4>Luật thi đấu</h4>
                                            <p>Áp dụng luật Pickleball quốc tế IFP (International Federation of Pickleball).</p>
                                        </div>
                                    </div>
                                    <div class="rule-item">
                                        <span class="rule-number">4</span>
                                        <div class="rule-content">
                                            <h4>Trọng tài</h4>
                                            <p>Quyết định của trọng tài là quyết định cuối cùng. Các trường hợp khiếu nại phải được gửi bằng văn bản.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline -->
                            <div class="content-card">
                                <h2 class="content-title">Timeline sự kiện</h2>
                                <div class="timeline">
                                    <div class="timeline-item completed">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-date">01/11/2025</div>
                                            <h4>Mở đăng ký</h4>
                                            <p>Bắt đầu nhận đăng ký tham gia giải đấu</p>
                                        </div>
                                    </div>
                                    <div class="timeline-item current">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-date">30/11/2025</div>
                                            <h4>Hạn chót đăng ký</h4>
                                            <p>Đóng đăng ký hoặc khi đủ số lượng VĐV</p>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-date">10/12/2025</div>
                                            <h4>Công bố bảng đấu</h4>
                                            <p>Công bố lịch thi đấu chính thức</p>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-date">15-17/12/2025</div>
                                            <h4>Diễn ra giải đấu</h4>
                                            <p>3 ngày thi đấu chính thức</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Tab -->
                        <div class="tab-pane" id="schedule">
                            <div class="content-card">
                                <h2 class="content-title">Lịch thi đấu chi tiết</h2>
                                <p class="text-muted">Lịch thi đấu sẽ được cập nhật sau khi đóng đăng ký (30/11/2025)</p>
                                
                                <!-- Schedule Preview -->
                                <div class="schedule-preview">
                                    <div class="day-schedule">
                                        <h3 class="day-title">Ngày 1 - 15/12/2025 (Thứ Sáu)</h3>
                                        <div class="schedule-table">
                                            <div class="schedule-row schedule-header">
                                                <div class="time-col">Giờ</div>
                                                <div class="court-col">Sân</div>
                                                <div class="match-col">Trận đấu</div>
                                                <div class="round-col">Vòng</div>
                                            </div>
                                            <div class="schedule-row">
                                                <div class="time-col">08:00</div>
                                                <div class="court-col">Court 1-8</div>
                                                <div class="match-col">Vòng loại - Open Singles</div>
                                                <div class="round-col"><span class="round-badge">Vòng loại</span></div>
                                            </div>
                                            <div class="schedule-row">
                                                <div class="time-col">13:00</div>
                                                <div class="court-col">Court 1-4</div>
                                                <div class="match-col">Vòng loại - Open Doubles</div>
                                                <div class="round-col"><span class="round-badge">Vòng loại</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Results Tab -->
                        <div class="tab-pane" id="results">
                            <div class="content-card">
                                <h2 class="content-title">Kết quả thi đấu</h2>
                                <p class="text-muted">Kết quả sẽ được cập nhật trong quá trình diễn ra giải đấu</p>
                            </div>
                        </div>

                        <!-- Participants Tab -->
                        <div class="tab-pane" id="participants">
                            <div class="content-card">
                                <h2 class="content-title">Danh sách vận động viên</h2>
                                <p class="text-muted">Danh sách VĐV sẽ được công bố sau khi đóng đăng ký (30/11/2025)</p>
                                <div class="participants-stats">
                                    <div class="participant-stat">
                                        <div class="stat-number">85/128</div>
                                        <div class="stat-label">Đã đăng ký</div>
                                    </div>
                                    <div class="participant-stat">
                                        <div class="stat-number">43</div>
                                        <div class="stat-label">Còn lại</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gallery Tab -->
                        <div class="tab-pane" id="gallery">
                            <div class="content-card">
                                <h2 class="content-title">Hình ảnh từ các mùa giải trước</h2>
                                <div class="gallery-grid">
                                    <!-- Gallery items would go here -->
                                    <div class="gallery-item">
                                        <div class="gallery-placeholder">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                                <polyline points="21 15 16 10 5 21"/>
                                            </svg>
                                            <p>HCM Open 2024 - Lễ khai mạc</p>
                                        </div>
                                    </div>
                                    <div class="gallery-item">
                                        <div class="gallery-placeholder">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                                <polyline points="21 15 16 10 5 21"/>
                                            </svg>
                                            <p>Trận chung kết 2024</p>
                                        </div>
                                    </div>
                                    <div class="gallery-item">
                                        <div class="gallery-placeholder">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                                <polyline points="21 15 16 10 5 21"/>
                                            </svg>
                                            <p>Nhà vô địch 2024</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <aside class="detail-sidebar">
                    <!-- Registration Card -->
                    <div class="sidebar-card registration-card">
                        <div class="card-header">
                            <h3>Đăng ký tham gia</h3>
                            <span class="urgency-badge">⏰ Còn 15 ngày</span>
                        </div>
                        
                        <div class="price-section">
                            <div class="price-item">
                                <span class="price-label">Lệ phí đăng ký</span>
                                <span class="price-value">500.000 VNĐ</span>
                            </div>
                            <div class="price-note">
                                * Giảm 20% khi đăng ký trước 20/11
                            </div>
                        </div>

                        <div class="registration-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 66%"></div>
                            </div>
                            <div class="progress-text">
                                <span>85/128 VĐV đã đăng ký</span>
                                <span>66%</span>
                            </div>
                        </div>

                        <button class="btn btn-primary btn-block btn-lg">
                            Đăng ký ngay
                        </button>
                        
                        <div class="registration-benefits">
                            <h4>Quyền lợi khi đăng ký:</h4>
                            <ul>
                                <li>✓ Bóng thi đấu chính thức</li>
                                <li>✓ Áo thi đấu cao cấp</li>
                                <li>✓ Bảo hiểm tai nạn</li>
                                <li>✓ Suất ăn 3 ngày</li>
                                <li>✓ Giấy chứng nhận tham dự</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Contact Card -->
                    <div class="sidebar-card contact-card">
                        <h3 class="card-title">Thông tin liên hệ</h3>
                        <div class="contact-list">
                            <div class="contact-item">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <div>
                                    <div class="contact-label">Email</div>
                                    <div class="contact-value">hcmopen@onepickleball.vn</div>
                                </div>
                            </div>
                            <div class="contact-item">
                                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                                </svg>
                                <div>
                                    <div class="contact-label">Hotline</div>
                                    <div class="contact-value">0901 234 567</div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-outline btn-block">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                            </svg>
                            Chat với BTC
                        </button>
                    </div>

                    <!-- Share Card -->
                    <div class="sidebar-card share-card">
                        <h3 class="card-title">Chia sẻ giải đấu</h3>
                        <div class="share-buttons">
                            <button class="share-btn facebook">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </button>
                            <button class="share-btn zalo">
                                <svg viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 14.79c-.28.4-.85.77-1.58.77-.16 0-.33-.02-.5-.06-1.72-.42-3.46-1.51-4.91-3.06-1.45-1.56-2.39-3.38-2.65-5.13-.03-.17-.04-.34-.04-.5 0-.73.34-1.33.71-1.64.37-.32.88-.51 1.42-.51.12 0 .24.01.35.03.61.09 1.15.64 1.42 1.44l.59 1.76c.14.43.11.89-.08 1.28-.18.39-.51.7-.9.86l-.28.11c.12.28.29.56.52.84.48.57 1.08 1.12 1.76 1.64.28.21.55.38.82.5l.11-.28c.16-.39.47-.72.86-.9.39-.19.85-.22 1.28-.08l1.76.59c.8.27 1.35.81 1.44 1.42.02.11.03.23.03.35 0 .54-.19 1.05-.51 1.42z"/>
                                </svg>
                            </button>
                            <button class="share-btn copy">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                    <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Related Tournaments -->
                    <div class="sidebar-card related-card">
                        <h3 class="card-title">Giải đấu liên quan</h3>
                        <div class="related-list">
                            <a href="#" class="related-item">
                                <div class="related-image">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 80'%3E%3Crect fill='%23FF6B6B' width='100' height='80'/%3E%3C/svg%3E" alt="">
                                </div>
                                <div class="related-content">
                                    <h4>Hà Nội Masters</h4>
                                    <p>22-24 Tháng 12</p>
                                </div>
                            </a>
                            <a href="#" class="related-item">
                                <div class="related-image">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 80'%3E%3Crect fill='%234ECDC4' width='100' height='80'/%3E%3C/svg%3E" alt="">
                                </div>
                                <div class="related-content">
                                    <h4>Đà Nẵng Beach</h4>
                                    <p>05-07 Tháng 1</p>
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
@endsection
