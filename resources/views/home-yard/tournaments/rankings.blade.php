@extends('layouts.homeyard')
<style>
    /* Page-specific styles */
    .ranking-filters {
        background: var(--bg-white);
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-sm);
    }

    .filter-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 0.75rem 1.5rem;
        border: 2px solid var(--border-color);
        background: var(--bg-white);
        border-radius: var(--radius-full);
        cursor: pointer;
        transition: all var(--transition);
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .filter-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .filter-btn.active {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-color: transparent;
    }

    .podium {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
        align-items: end;
    }

    .podium-item {
        background: var(--bg-white);
        border-radius: var(--radius-xl);
        padding: 2rem;
        text-align: center;
        position: relative;
        transition: all var(--transition);
    }

    .podium-item:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .podium-item.first {
        order: 2;
        padding-top: 3rem;
        box-shadow: var(--shadow-xl);
    }

    .podium-item.second {
        order: 1;
    }

    .podium-item.third {
        order: 3;
    }

    .podium-medal {
        width: 80px;
        height: 80px;
        margin: 0 auto 1rem;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 700;
        color: white;
    }

    .podium-item.first .podium-medal {
        background: linear-gradient(135deg, #FFD700, #FFA500);
        width: 100px;
        height: 100px;
        font-size: 3rem;
    }

    .podium-item.second .podium-medal {
        background: linear-gradient(135deg, #C0C0C0, #808080);
    }

    .podium-item.third .podium-medal {
        background: linear-gradient(135deg, #CD7F32, #8B4513);
    }

    .podium-avatar {
        width: 80px;
        height: 80px;
        margin: 0 auto 1rem;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.5rem;
        border: 4px solid white;
        box-shadow: var(--shadow-md);
    }

    .podium-item.first .podium-avatar {
        width: 100px;
        height: 100px;
        font-size: 2rem;
    }

    .podium-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .podium-item.first .podium-name {
        font-size: 1.5rem;
    }

    .podium-stats {
        display: flex;
        justify-content: center;
        gap: 1.5rem;
        margin-top: 1rem;
    }

    .podium-stat {
        text-align: center;
    }

    .podium-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .podium-stat-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .ranking-row {
        background: var(--bg-white);
        padding: 1.25rem 1.5rem;
        border-radius: var(--radius-lg);
        margin-bottom: 0.75rem;
        display: grid;
        grid-template-columns: 60px 1fr auto auto;
        align-items: center;
        gap: 1.5rem;
        transition: all var(--transition);
        border: 2px solid transparent;
    }

    .ranking-row:hover {
        border-color: var(--primary-color);
        box-shadow: var(--shadow-md);
        transform: translateX(5px);
    }

    .rank-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        text-align: center;
    }

    .rank-change {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        margin-left: 0.5rem;
    }

    .rank-change.up {
        background: rgba(74, 222, 128, 0.1);
        color: var(--accent-green);
    }

    .rank-change.down {
        background: rgba(255, 107, 107, 0.1);
        color: var(--accent-red);
    }

    .rank-change.same {
        background: var(--bg-light);
        color: var(--text-light);
    }

    .player-ranking-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .player-avatar-rank {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1rem;
    }

    .player-details-rank {
        flex: 1;
    }

    .player-name-rank {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }

    .player-meta-rank {
        font-size: 0.75rem;
        color: var(--text-light);
        display: flex;
        gap: 1rem;
    }

    .stats-row-rank {
        display: flex;
        gap: 2rem;
        font-size: 0.875rem;
    }

    .stat-item-rank {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
    }

    .stat-value-rank {
        font-weight: 700;
        font-size: 1.25rem;
        color: var(--text-primary);
    }

    .stat-label-rank {
        color: var(--text-secondary);
        font-size: 0.75rem;
    }

    .ranking-actions {
        display: flex;
        gap: 0.5rem;
    }

    .trophy-icon {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }

    .leaderboard-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 1.5rem 2rem;
        border-radius: var(--radius-lg);
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .leaderboard-title {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .leaderboard-subtitle {
        font-size: 0.875rem;
        opacity: 0.9;
        margin-top: 0.25rem;
    }

    .points-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1rem;
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: 1.25rem;
        backdrop-filter: blur(10px);
    }

    .stats-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-overview-card {
        background: var(--bg-white);
        padding: 1.25rem;
        border-radius: var(--radius-lg);
        text-align: center;
        box-shadow: var(--shadow-sm);
    }

    .stat-overview-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .stat-overview-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .stat-overview-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .performance-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .performance-badge.excellent {
        background: rgba(74, 222, 128, 0.1);
        color: var(--accent-green);
    }

    .performance-badge.good {
        background: rgba(0, 217, 181, 0.1);
        color: var(--primary-color);
    }

    .performance-badge.average {
        background: rgba(255, 211, 61, 0.1);
        color: #ca8a04;
    }
</style>
@section('content')
    <main class="main-content" id="mainContent">
        <div class="container">
            <!-- Header -->
            <div class="top-header">
                <div class="header-left">
                    <h1>Bảng Xếp Hạng</h1>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">
                            <a href="overview.html" class="breadcrumb-link">Trang chủ</a>
                        </span>
                        <span class="breadcrumb-separator">›</span>
                        <span class="breadcrumb-item">Bảng Xếp Hạng</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <span class="search-icon">🔍</span>
                        <input type="text" class="search-input" placeholder="Tìm kiếm VĐV...">
                    </div>
                    <div class="header-notifications">
                        <button class="notification-btn">
                            🔔
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
            </div>

            <!-- Stats Overview -->
            <div class="stats-overview fade-in">
                <div class="stat-overview-card">
                    <div class="stat-overview-icon">👥</div>
                    <div class="stat-overview-value">1,248</div>
                    <div class="stat-overview-label">Tổng VĐV</div>
                </div>
                <div class="stat-overview-card">
                    <div class="stat-overview-icon">🏆</div>
                    <div class="stat-overview-value">892</div>
                    <div class="stat-overview-label">VĐV Xếp Hạng</div>
                </div>
                <div class="stat-overview-card">
                    <div class="stat-overview-icon">🎯</div>
                    <div class="stat-overview-value">2,456</div>
                    <div class="stat-overview-label">Trận Đã Thi Đấu</div>
                </div>
                <div class="stat-overview-card">
                    <div class="stat-overview-icon">📊</div>
                    <div class="stat-overview-value">68%</div>
                    <div class="stat-overview-label">Tỷ Lệ Thắng TB</div>
                </div>
            </div>

            <!-- Ranking Filters -->
            <div class="ranking-filters fade-in">
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterRanking('all')">🏆 Tổng Hợp</button>
                    <button class="filter-btn" onclick="filterRanking('singles')">👤 Đơn Nam</button>
                    <button class="filter-btn" onclick="filterRanking('women')">👤 Đơn Nữ</button>
                    <button class="filter-btn" onclick="filterRanking('doubles')">👥 Đôi Nam</button>
                    <button class="filter-btn" onclick="filterRanking('women-doubles')">👥 Đôi Nữ</button>
                    <button class="filter-btn" onclick="filterRanking('mixed')">👫 Đôi Nam Nữ</button>
                </div>
            </div>

            <!-- Podium -->
            <div class="podium fade-in">
                <div class="podium-item first">
                    <div class="podium-medal">🥇</div>
                    <div class="podium-avatar">NA</div>
                    <div class="podium-name">Nguyễn Văn An</div>
                    <span class="badge badge-primary">Đơn Nam</span>
                    <div class="podium-stats">
                        <div class="podium-stat">
                            <div class="podium-stat-value">2,450</div>
                            <div class="podium-stat-label">Điểm</div>
                        </div>
                        <div class="podium-stat">
                            <div class="podium-stat-value">45</div>
                            <div class="podium-stat-label">Thắng</div>
                        </div>
                        <div class="podium-stat">
                            <div class="podium-stat-value">78%</div>
                            <div class="podium-stat-label">Tỷ lệ</div>
                        </div>
                    </div>
                </div>

                <div class="podium-item second">
                    <div class="podium-medal">🥈</div>
                    <div class="podium-avatar">TL</div>
                    <div class="podium-name">Trần Thu Linh</div>
                    <span class="badge badge-danger">Đơn Nữ</span>
                    <div class="podium-stats">
                        <div class="podium-stat">
                            <div class="podium-stat-value">2,180</div>
                            <div class="podium-stat-label">Điểm</div>
                        </div>
                        <div class="podium-stat">
                            <div class="podium-stat-value">38</div>
                            <div class="podium-stat-label">Thắng</div>
                        </div>
                        <div class="podium-stat">
                            <div class="podium-stat-value">72%</div>
                            <div class="podium-stat-label">Tỷ lệ</div>
                        </div>
                    </div>
                </div>

                <div class="podium-item third">
                    <div class="podium-medal">🥉</div>
                    <div class="podium-avatar">LH</div>
                    <div class="podium-name">Lê Minh Hoàng</div>
                    <span class="badge badge-primary">Đơn Nam</span>
                    <div class="podium-stats">
                        <div class="podium-stat">
                            <div class="podium-stat-value">2,050</div>
                            <div class="podium-stat-label">Điểm</div>
                        </div>
                        <div class="podium-stat">
                            <div class="podium-stat-value">32</div>
                            <div class="podium-stat-label">Thắng</div>
                        </div>
                        <div class="podium-stat">
                            <div class="podium-stat-value">68%</div>
                            <div class="podium-stat-label">Tỷ lệ</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leaderboard -->
            <div class="card fade-in">
                <div class="leaderboard-header">
                    <div>
                        <div class="leaderboard-title">🏆 Bảng Xếp Hạng Tổng Hợp</div>
                        <div class="leaderboard-subtitle">Cập nhật lần cuối: 20/01/2025 - 14:30</div>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-secondary btn-sm"
                            style="background: rgba(255,255,255,0.2); border: none; color: white;">
                            📥 Xuất Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Ranking Rows -->
                    <div class="ranking-row">
                        <div>
                            <span class="rank-number">4</span>
                            <span class="rank-change up">↑2</span>
                        </div>
                        <div class="player-ranking-info">
                            <div class="player-avatar-rank">PH</div>
                            <div class="player-details-rank">
                                <div class="player-name-rank">Phạm Thu Hà</div>
                                <div class="player-meta-rank">
                                    <span>📧 phamthuha@email.com</span>
                                    <span>📱 0934567890</span>
                                    <span class="performance-badge excellent">🔥 Xuất sắc</span>
                                </div>
                            </div>
                        </div>
                        <div class="stats-row-rank">
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">1,920</div>
                                <div class="stat-label-rank">Điểm</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">28</div>
                                <div class="stat-label-rank">Thắng</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">15</div>
                                <div class="stat-label-rank">Thua</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">65%</div>
                                <div class="stat-label-rank">Tỷ lệ</div>
                            </div>
                        </div>
                        <div class="ranking-actions">
                            <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết">👁️</button>
                            <button class="btn btn-ghost btn-icon-sm" title="Thống kê">📊</button>
                        </div>
                    </div>

                    <div class="ranking-row">
                        <div>
                            <span class="rank-number">5</span>
                            <span class="rank-change same">—</span>
                        </div>
                        <div class="player-ranking-info">
                            <div class="player-avatar-rank">ĐT</div>
                            <div class="player-details-rank">
                                <div class="player-name-rank">Đỗ Văn Toàn</div>
                                <div class="player-meta-rank">
                                    <span>📧 dovantoan@email.com</span>
                                    <span>📱 0945678901</span>
                                    <span class="performance-badge good">⚡ Tốt</span>
                                </div>
                            </div>
                        </div>
                        <div class="stats-row-rank">
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">1,850</div>
                                <div class="stat-label-rank">Điểm</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">24</div>
                                <div class="stat-label-rank">Thắng</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">15</div>
                                <div class="stat-label-rank">Thua</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">61%</div>
                                <div class="stat-label-rank">Tỷ lệ</div>
                            </div>
                        </div>
                        <div class="ranking-actions">
                            <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết">👁️</button>
                            <button class="btn btn-ghost btn-icon-sm" title="Thống kê">📊</button>
                        </div>
                    </div>

                    <div class="ranking-row">
                        <div>
                            <span class="rank-number">6</span>
                            <span class="rank-change down">↓1</span>
                        </div>
                        <div class="player-ranking-info">
                            <div class="player-avatar-rank">VL</div>
                            <div class="player-details-rank">
                                <div class="player-name-rank">Vũ Thu Lan</div>
                                <div class="player-meta-rank">
                                    <span>📧 vuthulan@email.com</span>
                                    <span>📱 0956789012</span>
                                    <span class="performance-badge good">⚡ Tốt</span>
                                </div>
                            </div>
                        </div>
                        <div class="stats-row-rank">
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">1,780</div>
                                <div class="stat-label-rank">Điểm</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">22</div>
                                <div class="stat-label-rank">Thắng</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">15</div>
                                <div class="stat-label-rank">Thua</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">59%</div>
                                <div class="stat-label-rank">Tỷ lệ</div>
                            </div>
                        </div>
                        <div class="ranking-actions">
                            <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết">👁️</button>
                            <button class="btn btn-ghost btn-icon-sm" title="Thống kê">📊</button>
                        </div>
                    </div>

                    <div class="ranking-row">
                        <div>
                            <span class="rank-number">7</span>
                            <span class="rank-change up">↑3</span>
                        </div>
                        <div class="player-ranking-info">
                            <div class="player-avatar-rank">HK</div>
                            <div class="player-details-rank">
                                <div class="player-name-rank">Hoàng Văn Khoa</div>
                                <div class="player-meta-rank">
                                    <span>📧 hoangvankoa@email.com</span>
                                    <span>📱 0967890123</span>
                                    <span class="performance-badge average">📈 Trung bình</span>
                                </div>
                            </div>
                        </div>
                        <div class="stats-row-rank">
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">1,650</div>
                                <div class="stat-label-rank">Điểm</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">19</div>
                                <div class="stat-label-rank">Thắng</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">16</div>
                                <div class="stat-label-rank">Thua</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">54%</div>
                                <div class="stat-label-rank">Tỷ lệ</div>
                            </div>
                        </div>
                        <div class="ranking-actions">
                            <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết">👁️</button>
                            <button class="btn btn-ghost btn-icon-sm" title="Thống kê">📊</button>
                        </div>
                    </div>

                    <div class="ranking-row">
                        <div>
                            <span class="rank-number">8</span>
                            <span class="rank-change same">—</span>
                        </div>
                        <div class="player-ranking-info">
                            <div class="player-avatar-rank">MT</div>
                            <div class="player-details-rank">
                                <div class="player-name-rank">Mai Thanh Tùng</div>
                                <div class="player-meta-rank">
                                    <span>📧 maithanhtung@email.com</span>
                                    <span>📱 0978901234</span>
                                    <span class="performance-badge average">📈 Trung bình</span>
                                </div>
                            </div>
                        </div>
                        <div class="stats-row-rank">
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">1,580</div>
                                <div class="stat-label-rank">Điểm</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">17</div>
                                <div class="stat-label-rank">Thắng</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">16</div>
                                <div class="stat-label-rank">Thua</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">51%</div>
                                <div class="stat-label-rank">Tỷ lệ</div>
                            </div>
                        </div>
                        <div class="ranking-actions">
                            <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết">👁️</button>
                            <button class="btn btn-ghost btn-icon-sm" title="Thống kê">📊</button>
                        </div>
                    </div>

                    <div class="ranking-row">
                        <div>
                            <span class="rank-number">9</span>
                            <span class="rank-change down">↓2</span>
                        </div>
                        <div class="player-ranking-info">
                            <div class="player-avatar-rank">NM</div>
                            <div class="player-details-rank">
                                <div class="player-name-rank">Ngô Thị Mai</div>
                                <div class="player-meta-rank">
                                    <span>📧 ngothimai@email.com</span>
                                    <span>📱 0989012345</span>
                                    <span class="performance-badge average">📈 Trung bình</span>
                                </div>
                            </div>
                        </div>
                        <div class="stats-row-rank">
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">1,520</div>
                                <div class="stat-label-rank">Điểm</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">16</div>
                                <div class="stat-label-rank">Thắng</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">17</div>
                                <div class="stat-label-rank">Thua</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">48%</div>
                                <div class="stat-label-rank">Tỷ lệ</div>
                            </div>
                        </div>
                        <div class="ranking-actions">
                            <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết">👁️</button>
                            <button class="btn btn-ghost btn-icon-sm" title="Thống kê">📊</button>
                        </div>
                    </div>

                    <div class="ranking-row">
                        <div>
                            <span class="rank-number">10</span>
                            <span class="rank-change up">↑1</span>
                        </div>
                        <div class="player-ranking-info">
                            <div class="player-avatar-rank">BQ</div>
                            <div class="player-details-rank">
                                <div class="player-name-rank">Bùi Quang</div>
                                <div class="player-meta-rank">
                                    <span>📧 buiquang@email.com</span>
                                    <span>📱 0990123456</span>
                                    <span class="performance-badge good">⚡ Tốt</span>
                                </div>
                            </div>
                        </div>
                        <div class="stats-row-rank">
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">1,480</div>
                                <div class="stat-label-rank">Điểm</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">15</div>
                                <div class="stat-label-rank">Thắng</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">16</div>
                                <div class="stat-label-rank">Thua</div>
                            </div>
                            <div class="stat-item-rank">
                                <div class="stat-value-rank">48%</div>
                                <div class="stat-label-rank">Tỷ lệ</div>
                            </div>
                        </div>
                        <div class="ranking-actions">
                            <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết">👁️</button>
                            <button class="btn btn-ghost btn-icon-sm" title="Thống kê">📊</button>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="pagination">
                        <button class="pagination-btn" disabled>‹ Trước</button>
                        <button class="pagination-btn active">1</button>
                        <button class="pagination-btn">2</button>
                        <button class="pagination-btn">3</button>
                        <button class="pagination-btn">4</button>
                        <button class="pagination-btn">5</button>
                        <button class="pagination-btn">Sau ›</button>
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

        // Filter ranking
        function filterRanking(category) {
            // Remove active class from all buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Add active class to clicked button
            event.target.classList.add('active');

            // In a real app, this would filter the ranking data
            console.log('Filtering by:', category);
        }

        // Load page
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Rankings Loaded');
        });
    </script>
@endsection
