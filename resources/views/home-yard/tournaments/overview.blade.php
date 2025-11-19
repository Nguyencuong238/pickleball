@extends('layouts.homeyard')

<style>
    /* Page-specific styles */
    .welcome-banner {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 2rem;
        border-radius: var(--radius-xl);
        margin-bottom: 2rem;
        box-shadow: var(--shadow-lg);
    }

    .welcome-banner h2 {
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
    }

    .welcome-banner p {
        opacity: 0.95;
        font-size: 1rem;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .quick-action-btn {
        background: var(--bg-white);
        border: 2px solid var(--border-color);
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        text-align: center;
        cursor: pointer;
        transition: all var(--transition);
        text-decoration: none;
        color: var(--text-primary);
    }

    .quick-action-btn:hover {
        border-color: var(--primary-color);
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }

    .quick-action-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .quick-action-title {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .recent-list {
        list-style: none;
    }

    .recent-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        transition: all var(--transition);
    }

    .recent-item:hover {
        background: var(--bg-light);
    }

    .recent-item:last-child {
        border-bottom: none;
    }

    .recent-item-info {
        flex: 1;
    }

    .recent-item-title {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .recent-item-meta {
        font-size: 0.75rem;
        color: var(--text-light);
    }

    .activity-timeline {
        position: relative;
        padding-left: 2rem;
    }

    .activity-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .activity-item::before {
        content: '';
        position: absolute;
        left: -2rem;
        top: 0.5rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--primary-color);
        border: 3px solid var(--bg-white);
        box-shadow: 0 0 0 2px var(--primary-color);
    }

    .activity-item::after {
        content: '';
        position: absolute;
        left: -1.5rem;
        top: 1.5rem;
        bottom: 0;
        width: 2px;
        background: var(--border-color);
    }

    .activity-item:last-child::after {
        display: none;
    }

    .activity-time {
        font-size: 0.75rem;
        color: var(--text-light);
        margin-bottom: 0.25rem;
    }

    .activity-content {
        color: var(--text-secondary);
        font-size: 0.875rem;
    }

    .chart-placeholder {
        height: 300px;
        background: var(--bg-light);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-light);
        font-size: 0.875rem;
    }

    .calendar-widget {
        background: var(--bg-light);
        padding: 1rem;
        border-radius: var(--radius-md);
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .calendar-title {
        font-weight: 700;
        color: var(--text-primary);
    }

    .calendar-nav {
        display: flex;
        gap: 0.5rem;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.5rem;
    }

    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        cursor: pointer;
        transition: all var(--transition);
    }

    .calendar-day:hover {
        background: var(--bg-white);
    }

    .calendar-day.has-event {
        background: rgba(0, 217, 181, 0.1);
        color: var(--primary-color);
        font-weight: 700;
    }

    .calendar-day.today {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        font-weight: 700;
    }
</style>
@section('content')
    <main class="main-content" id="mainContent">
        <div class="container">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <h1>Tổng Quan Hệ Thống</h1>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">
                            <span>🏠</span>
                            <span>Dashboard</span>
                        </span>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item">Tổng quan</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <input type="text" class="search-input" placeholder="Tìm kiếm...">
                        <span class="search-icon">🔍</span>
                    </div>
                    <div class="header-notifications">
                        <button class="notification-btn">
                            <span>🔔</span>
                            <span class="notification-badge">5</span>
                        </button>
                    </div>
                    <div class="header-user">
                        <div class="user-avatar">AD</div>
                        <div class="user-info">
                            <div class="user-name">Admin User</div>
                            <div class="user-role">Quản trị viên</div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Welcome Banner -->
            <div class="welcome-banner fade-in">
                <h2>👋 Xin chào, Admin!</h2>
                <p>Chào mừng trở lại với hệ thống quản lý giải đấu Pickleball. Hôm nay bạn có 3 giải đấu đang diễn ra và 45
                    trận đấu cần quản lý.</p>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions fade-in">
                <a href="tournaments.html?action=create" class="quick-action-btn">
                    <div class="quick-action-icon">➕</div>
                    <div class="quick-action-title">Tạo Giải Mới</div>
                </a>
                <a href="#" class="quick-action-btn">
                    <div class="quick-action-icon">✅</div>
                    <div class="quick-action-title">Duyệt VĐV</div>
                </a>
                <a href="#" class="quick-action-btn">
                    <div class="quick-action-icon">🎾</div>
                    <div class="quick-action-title">Cập Nhật Kết Quả</div>
                </a>
                <a href="#" class="quick-action-btn">
                    <div class="quick-action-icon">📊</div>
                    <div class="quick-action-title">Xem Báo Cáo</div>
                </a>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid fade-in">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-label">Tổng Giải Đấu</div>
                            <div class="stat-value">24</div>
                        </div>
                        <div class="stat-icon primary">🏆</div>
                    </div>
                    <div class="stat-trend up">
                        <span>↗</span>
                        <span>12% vs tháng trước</span>
                    </div>
                    <div class="stat-footer">
                        12 đang diễn ra • 8 sắp tới • 4 đã kết thúc
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-label">Vận Động Viên</div>
                            <div class="stat-value">1,248</div>
                        </div>
                        <div class="stat-icon success">👥</div>
                    </div>
                    <div class="stat-trend up">
                        <span>↗</span>
                        <span>8% vs tháng trước</span>
                    </div>
                    <div class="stat-footer">
                        156 VĐV mới tháng này
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-label">Trận Đấu Hôm Nay</div>
                            <div class="stat-value">45</div>
                        </div>
                        <div class="stat-icon warning">🎾</div>
                    </div>
                    <div class="stat-trend">
                        <span>→</span>
                        <span>12 đang diễn ra</span>
                    </div>
                    <div class="stat-footer">
                        33 trận còn lại • 2 trận delay
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-label">Doanh Thu Tháng</div>
                            <div class="stat-value">₫458M</div>
                        </div>
                        <div class="stat-icon danger">💰</div>
                    </div>
                    <div class="stat-trend up">
                        <span>↗</span>
                        <span>24% vs tháng trước</span>
                    </div>
                    <div class="stat-footer">
                        Mục tiêu: ₫500M (92%)
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-3" style="grid-template-columns: 2fr 1fr;">
                <!-- Recent Tournaments -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">Giải Đấu Gần Đây</h3>
                        <div class="card-actions">
                            <a href="tournaments.html" class="btn btn-ghost btn-sm">Xem tất cả →</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="recent-list">
                            <li class="recent-item">
                                <div class="recent-item-info">
                                    <div class="recent-item-title">🏆 Giải Pickleball Mở Rộng TP.HCM 2025</div>
                                    <div class="recent-item-meta">64 VĐV • 32 trận • Bắt đầu 20/01/2025</div>
                                </div>
                                <span class="badge badge-success">Đang diễn ra</span>
                            </li>
                            <li class="recent-item">
                                <div class="recent-item-info">
                                    <div class="recent-item-title">🏆 Cúp Pickleball Hà Nội 2025</div>
                                    <div class="recent-item-meta">48 VĐV • 24 trận • Bắt đầu 22/01/2025</div>
                                </div>
                                <span class="badge badge-warning">Sắp tới</span>
                            </li>
                            <li class="recent-item">
                                <div class="recent-item-info">
                                    <div class="recent-item-title">🏆 Giải Đôi Nam Nữ Đà Nẵng</div>
                                    <div class="recent-item-meta">32 cặp • 16 trận • Bắt đầu 25/01/2025</div>
                                </div>
                                <span class="badge badge-warning">Sắp tới</span>
                            </li>
                            <li class="recent-item">
                                <div class="recent-item-info">
                                    <div class="recent-item-title">🏆 Giải Nội Bộ Tháng 12</div>
                                    <div class="recent-item-meta">28 VĐV • 14 trận • Kết thúc 18/12/2024</div>
                                </div>
                                <span class="badge badge-gray">Đã kết thúc</span>
                            </li>
                            <li class="recent-item">
                                <div class="recent-item-info">
                                    <div class="recent-item-title">🏆 Cúp Mùa Đông 2024</div>
                                    <div class="recent-item-meta">56 VĐV • 28 trận • Kết thúc 15/12/2024</div>
                                </div>
                                <span class="badge badge-gray">Đã kết thúc</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">Hoạt Động Gần Đây</h3>
                    </div>
                    <div class="card-body">
                        <div class="activity-timeline">
                            <div class="activity-item">
                                <div class="activity-time">5 phút trước</div>
                                <div class="activity-content">
                                    <strong>Nguyễn Văn An</strong> đã check-in cho trận đấu Vòng 1/8
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-time">12 phút trước</div>
                                <div class="activity-content">
                                    Trận đấu <strong>Bảng A - Trận 5</strong> đã hoàn thành
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-time">25 phút trước</div>
                                <div class="activity-content">
                                    <strong>15 VĐV mới</strong> đã đăng ký Giải Pickleball TP.HCM
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-time">1 giờ trước</div>
                                <div class="activity-content">
                                    Admin đã duyệt <strong>23 đơn đăng ký</strong> VĐV
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-time">2 giờ trước</div>
                                <div class="activity-content">
                                    Đã tạo giải đấu mới: <strong>Cúp Mùa Xuân 2025</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-2 mt-3">
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">Thống Kê Giải Đấu Theo Tháng</h3>
                        <select class="form-select" style="width: auto;">
                            <option>2025</option>
                            <option>2024</option>
                        </select>
                    </div>
                    <div class="card-body">
                        <div class="chart-placeholder">
                            📊 Biểu đồ cột - Số lượng giải đấu theo tháng
                        </div>
                    </div>
                </div>

                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">Phân Bổ Vận Động Viên</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-placeholder">
                            🥧 Biểu đồ tròn - VĐV theo nội dung thi đấu
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </main>
@endsection
@section('js')
    <script>
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
        }

        // Mobile menu toggle
        if (window.innerWidth <= 1024) {
            toggleSidebar();
        }

        window.addEventListener('resize', () => {
            if (window.innerWidth <= 1024) {
                document.getElementById('sidebar').classList.add('collapsed');
                document.getElementById('mainContent').classList.add('sidebar-collapsed');
            }
        });

        // Animate stats on load
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Overview Dashboard Loaded');
        });
    </script>
@endsection
