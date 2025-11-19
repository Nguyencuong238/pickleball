@extends('layouts.homeyard')
<style>
    /* Page-specific styles */
    .match-card {
        background: var(--bg-white);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin-bottom: 1rem;
        border: 2px solid var(--border-color);
        transition: all var(--transition);
    }

    .match-card:hover {
        border-color: var(--primary-color);
        box-shadow: var(--shadow-md);
    }

    .match-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
    }

    .match-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .match-id {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .match-time {
        font-size: 0.75rem;
        color: var(--text-light);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .match-body {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 1.5rem;
        align-items: center;
    }

    .player-side {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .player-card-mini {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: var(--bg-light);
        border-radius: var(--radius-md);
    }

    .player-card-mini.winner {
        background: rgba(74, 222, 128, 0.1);
        border: 2px solid var(--accent-green);
    }

    .player-avatar-sm {
        width: 32px;
        height: 32px;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.75rem;
    }

    .player-name-sm {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
    }

    .match-score {
        text-align: center;
    }

    .score-display {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .score-sets {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .match-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 2px solid var(--border-color);
    }

    .match-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }

    .match-meta-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .court-badge {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .match-actions {
        display: flex;
        gap: 0.5rem;
    }

    .tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid var(--border-color);
        overflow-x: auto;
    }

    .tab {
        padding: 0.75rem 1.5rem;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all var(--transition);
        font-weight: 600;
        white-space: nowrap;
    }

    .tab:hover {
        color: var(--primary-color);
    }

    .tab.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .schedule-grid {
        display: grid;
        gap: 1rem;
    }

    .live-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        background: var(--accent-red);
        color: white;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 700;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    .live-dot {
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        animation: blink 1.5s infinite;
    }

    @keyframes blink {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0;
        }
    }

    .calendar-view {
        background: var(--bg-white);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
    }

    .calendar-header-full {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .calendar-title-full {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .calendar-nav-full {
        display: flex;
        gap: 0.5rem;
    }

    .calendar-grid-full {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1rem;
    }

    .calendar-day-full {
        aspect-ratio: 1;
        padding: 0.75rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        cursor: pointer;
        transition: all var(--transition);
    }

    .calendar-day-full:hover {
        border-color: var(--primary-color);
        box-shadow: var(--shadow-md);
    }

    .calendar-day-full.has-matches {
        background: rgba(0, 217, 181, 0.05);
        border-color: var(--primary-color);
    }

    .calendar-day-full.today {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-color: transparent;
    }

    .day-number {
        font-weight: 700;
        font-size: 1.125rem;
    }

    .day-matches {
        font-size: 0.625rem;
        color: var(--text-light);
    }

    .calendar-day-full.today .day-matches {
        color: rgba(255, 255, 255, 0.9);
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-mini-card {
        background: var(--bg-light);
        padding: 1rem;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-mini-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stat-mini-icon.success {
        background: rgba(74, 222, 128, 0.1);
    }

    .stat-mini-icon.warning {
        background: rgba(255, 211, 61, 0.1);
    }

    .stat-mini-icon.danger {
        background: rgba(255, 107, 107, 0.1);
    }

    .stat-mini-icon.info {
        background: rgba(0, 153, 204, 0.1);
    }

    .stat-mini-content {
        flex: 1;
    }

    .stat-mini-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-bottom: 0.25rem;
    }

    .stat-mini-value-lg {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
    }
</style>
@section('content')
    <main class="main-content" id="mainContent">
        <div class="container">
            <!-- Header -->
            <div class="top-header">
                <div class="header-left">
                    <h1>Quản Lý Trận Đấu</h1>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">
                            <a href="overview.html" class="breadcrumb-link">Trang chủ</a>
                        </span>
                        <span class="breadcrumb-separator">›</span>
                        <span class="breadcrumb-item">Trận Đấu</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <span class="search-icon">🔍</span>
                        <input type="text" class="search-input" placeholder="Tìm kiếm trận đấu...">
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

            <!-- Stats Row -->
            <div class="stats-row fade-in">
                <div class="stat-mini-card">
                    <div class="stat-mini-icon success">✅</div>
                    <div class="stat-mini-content">
                        <div class="stat-mini-label">Đã hoàn thành</div>
                        <div class="stat-mini-value-lg">147</div>
                    </div>
                </div>
                <div class="stat-mini-card">
                    <div class="stat-mini-icon danger">🔴</div>
                    <div class="stat-mini-content">
                        <div class="stat-mini-label">Đang diễn ra</div>
                        <div class="stat-mini-value-lg">12</div>
                    </div>
                </div>
                <div class="stat-mini-card">
                    <div class="stat-mini-icon warning">⏰</div>
                    <div class="stat-mini-content">
                        <div class="stat-mini-label">Sắp tới</div>
                        <div class="stat-mini-value-lg">33</div>
                    </div>
                </div>
                <div class="stat-mini-card">
                    <div class="stat-mini-icon info">📊</div>
                    <div class="stat-mini-content">
                        <div class="stat-mini-label">Tổng cộng</div>
                        <div class="stat-mini-value-lg">192</div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="card fade-in">
                <div class="card-body">
                    <div class="tabs">
                        <button class="tab active" onclick="switchTab('live')">🔴 Trực Tiếp</button>
                        <button class="tab" onclick="switchTab('upcoming')">⏰ Sắp Tới</button>
                        <button class="tab" onclick="switchTab('completed')">✅ Đã Kết Thúc</button>
                        <button class="tab" onclick="switchTab('calendar')">📅 Lịch Thi Đấu</button>
                    </div>

                    <!-- Live Matches Tab -->
                    <div class="tab-content active" id="live">
                        <div class="schedule-grid">
                            <div class="match-card">
                                <div class="match-header">
                                    <div class="match-info">
                                        <span class="match-id">Trận #A-001</span>
                                        <span class="live-indicator">
                                            <span class="live-dot"></span>
                                            LIVE
                                        </span>
                                    </div>
                                    <div class="match-time">
                                        🕐 Bắt đầu: 14:30 • ⏱️ Set 2
                                    </div>
                                </div>
                                <div class="match-body">
                                    <div class="player-side">
                                        <div class="player-card-mini">
                                            <div class="player-avatar-sm">NA</div>
                                            <div class="player-name-sm">Nguyễn Văn An</div>
                                        </div>
                                    </div>
                                    <div class="match-score">
                                        <div class="score-display">11 - 8</div>
                                        <div class="score-sets">Set 1: 11-9 • Set 2: Đang chơi</div>
                                    </div>
                                    <div class="player-side">
                                        <div class="player-card-mini">
                                            <div class="player-avatar-sm">LH</div>
                                            <div class="player-name-sm">Lê Minh Hoàng</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="match-footer">
                                    <div class="match-meta">
                                        <span class="match-meta-item">🏆 Vòng 1/4</span>
                                        <span class="match-meta-item">👤 Đơn Nam</span>
                                        <span class="court-badge">Sân 1</span>
                                    </div>
                                    <div class="match-actions">
                                        <button class="btn btn-primary btn-sm">📊 Cập nhật điểm</button>
                                        <button class="btn btn-ghost btn-sm">👁️ Chi tiết</button>
                                    </div>
                                </div>
                            </div>

                            <div class="match-card">
                                <div class="match-header">
                                    <div class="match-info">
                                        <span class="match-id">Trận #A-002</span>
                                        <span class="live-indicator">
                                            <span class="live-dot"></span>
                                            LIVE
                                        </span>
                                    </div>
                                    <div class="match-time">
                                        🕐 Bắt đầu: 14:45 • ⏱️ Set 1
                                    </div>
                                </div>
                                <div class="match-body">
                                    <div class="player-side">
                                        <div class="player-card-mini winner">
                                            <div class="player-avatar-sm">TL</div>
                                            <div class="player-name-sm">Trần Thu Linh</div>
                                        </div>
                                    </div>
                                    <div class="match-score">
                                        <div class="score-display">9 - 5</div>
                                        <div class="score-sets">Set 1: Đang chơi</div>
                                    </div>
                                    <div class="player-side">
                                        <div class="player-card-mini">
                                            <div class="player-avatar-sm">PH</div>
                                            <div class="player-name-sm">Phạm Thu Hà</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="match-footer">
                                    <div class="match-meta">
                                        <span class="match-meta-item">🏆 Vòng bảng</span>
                                        <span class="match-meta-item">👤 Đơn Nữ</span>
                                        <span class="court-badge">Sân 2</span>
                                    </div>
                                    <div class="match-actions">
                                        <button class="btn btn-primary btn-sm">📊 Cập nhật điểm</button>
                                        <button class="btn btn-ghost btn-sm">👁️ Chi tiết</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Matches Tab -->
                    <div class="tab-content" id="upcoming">
                        <div class="schedule-grid">
                            <div class="match-card">
                                <div class="match-header">
                                    <div class="match-info">
                                        <span class="match-id">Trận #A-003</span>
                                        <span class="badge badge-warning">Sắp tới</span>
                                    </div>
                                    <div class="match-time">
                                        🕐 15:30 • 📅 20/01/2025
                                    </div>
                                </div>
                                <div class="match-body">
                                    <div class="player-side">
                                        <div class="player-card-mini">
                                            <div class="player-avatar-sm">ĐT</div>
                                            <div class="player-name-sm">Đỗ Văn Toàn</div>
                                        </div>
                                    </div>
                                    <div class="match-score">
                                        <div class="score-display" style="font-size: 1.5rem; color: var(--text-light);">VS
                                        </div>
                                    </div>
                                    <div class="player-side">
                                        <div class="player-card-mini">
                                            <div class="player-avatar-sm">VL</div>
                                            <div class="player-name-sm">Vũ Thu Lan</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="match-footer">
                                    <div class="match-meta">
                                        <span class="match-meta-item">🏆 Vòng 1/8</span>
                                        <span class="match-meta-item">👥 Đôi Nam Nữ</span>
                                        <span class="court-badge">Sân 3</span>
                                    </div>
                                    <div class="match-actions">
                                        <button class="btn btn-secondary btn-sm">✏️ Chỉnh sửa</button>
                                        <button class="btn btn-ghost btn-sm">👁️ Chi tiết</button>
                                    </div>
                                </div>
                            </div>

                            <div class="match-card">
                                <div class="match-header">
                                    <div class="match-info">
                                        <span class="match-id">Trận #A-004</span>
                                        <span class="badge badge-warning">Sắp tới</span>
                                    </div>
                                    <div class="match-time">
                                        🕐 16:00 • 📅 20/01/2025
                                    </div>
                                </div>
                                <div class="match-body">
                                    <div class="player-side">
                                        <div class="player-card-mini">
                                            <div class="player-avatar-sm">HK</div>
                                            <div class="player-name-sm">Hoàng Văn Khoa</div>
                                        </div>
                                    </div>
                                    <div class="match-score">
                                        <div class="score-display" style="font-size: 1.5rem; color: var(--text-light);">VS
                                        </div>
                                    </div>
                                    <div class="player-side">
                                        <div class="player-card-mini">
                                            <div class="player-avatar-sm">MT</div>
                                            <div class="player-name-sm">Mai Thanh Tùng</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="match-footer">
                                    <div class="match-meta">
                                        <span class="match-meta-item">🏆 Vòng bảng</span>
                                        <span class="match-meta-item">👤 Đơn Nam</span>
                                        <span class="court-badge">Sân 1</span>
                                    </div>
                                    <div class="match-actions">
                                        <button class="btn btn-secondary btn-sm">✏️ Chỉnh sửa</button>
                                        <button class="btn btn-ghost btn-sm">👁️ Chi tiết</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Matches Tab -->
                    <div class="tab-content" id="completed">
                        <div class="schedule-grid">
                            <div class="match-card">
                                <div class="match-header">
                                    <div class="match-info">
                                        <span class="match-id">Trận #A-099</span>
                                        <span class="badge badge-success">Đã kết thúc</span>
                                    </div>
                                    <div class="match-time">
                                        🕐 14:00 • 📅 20/01/2025
                                    </div>
                                </div>
                                <div class="match-body">
                                    <div class="player-side">
                                        <div class="player-card-mini winner">
                                            <div class="player-avatar-sm">NA</div>
                                            <div class="player-name-sm">Nguyễn Văn An</div>
                                        </div>
                                    </div>
                                    <div class="match-score">
                                        <div class="score-display">2 - 0</div>
                                        <div class="score-sets">Set 1: 11-7 • Set 2: 11-5</div>
                                    </div>
                                    <div class="player-side">
                                        <div class="player-card-mini">
                                            <div class="player-avatar-sm">TQ</div>
                                            <div class="player-name-sm">Trịnh Quang</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="match-footer">
                                    <div class="match-meta">
                                        <span class="match-meta-item">🏆 Vòng bảng</span>
                                        <span class="match-meta-item">👤 Đơn Nam</span>
                                        <span class="court-badge">Sân 1</span>
                                    </div>
                                    <div class="match-actions">
                                        <button class="btn btn-ghost btn-sm">👁️ Chi tiết</button>
                                        <button class="btn btn-ghost btn-sm">📊 Thống kê</button>
                                    </div>
                                </div>
                            </div>

                            <div class="match-card">
                                <div class="match-header">
                                    <div class="match-info">
                                        <span class="match-id">Trận #A-098</span>
                                        <span class="badge badge-success">Đã kết thúc</span>
                                    </div>
                                    <div class="match-time">
                                        🕐 13:30 • 📅 20/01/2025
                                    </div>
                                </div>
                                <div class="match-body">
                                    <div class="player-side">
                                        <div class="player-card-mini">
                                            <div class="player-avatar-sm">PH</div>
                                            <div class="player-name-sm">Phạm Thu Hà</div>
                                        </div>
                                    </div>
                                    <div class="match-score">
                                        <div class="score-display">1 - 2</div>
                                        <div class="score-sets">Set 1: 11-8 • Set 2: 9-11 • Set 3: 7-11</div>
                                    </div>
                                    <div class="player-side">
                                        <div class="player-card-mini winner">
                                            <div class="player-avatar-sm">TL</div>
                                            <div class="player-name-sm">Trần Thu Linh</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="match-footer">
                                    <div class="match-meta">
                                        <span class="match-meta-item">🏆 Chung kết</span>
                                        <span class="match-meta-item">👤 Đơn Nữ</span>
                                        <span class="court-badge">Sân 2</span>
                                    </div>
                                    <div class="match-actions">
                                        <button class="btn btn-ghost btn-sm">👁️ Chi tiết</button>
                                        <button class="btn btn-ghost btn-sm">📊 Thống kê</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Tab -->
                    <div class="tab-content" id="calendar">
                        <div class="calendar-view">
                            <div class="calendar-header-full">
                                <h3 class="calendar-title-full">Tháng 1, 2025</h3>
                                <div class="calendar-nav-full">
                                    <button class="btn btn-secondary btn-sm">‹ Tháng trước</button>
                                    <button class="btn btn-secondary btn-sm">Tháng sau ›</button>
                                </div>
                            </div>
                            <div class="calendar-grid-full">
                                <div class="calendar-day-full" style="opacity: 0.5;">
                                    <div class="day-number">29</div>
                                </div>
                                <div class="calendar-day-full" style="opacity: 0.5;">
                                    <div class="day-number">30</div>
                                </div>
                                <div class="calendar-day-full" style="opacity: 0.5;">
                                    <div class="day-number">31</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">1</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">2</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">3</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">4</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">5</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">6</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">7</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">8</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">9</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">10</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">11</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">12</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">13</div>
                                    <div class="day-matches">8 trận</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">14</div>
                                    <div class="day-matches">12 trận</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">15</div>
                                    <div class="day-matches">10 trận</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">16</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">17</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">18</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">19</div>
                                    <div class="day-matches">6 trận</div>
                                </div>
                                <div class="calendar-day-full today has-matches">
                                    <div class="day-number">20</div>
                                    <div class="day-matches">15 trận</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">21</div>
                                    <div class="day-matches">14 trận</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">22</div>
                                    <div class="day-matches">11 trận</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">23</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">24</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">25</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">26</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">27</div>
                                    <div class="day-matches">9 trận</div>
                                </div>
                                <div class="calendar-day-full has-matches">
                                    <div class="day-number">28</div>
                                    <div class="day-matches">7 trận</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">29</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">30</div>
                                </div>
                                <div class="calendar-day-full">
                                    <div class="day-number">31</div>
                                </div>
                            </div>
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

        // Tab switching
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab content
            document.getElementById(tabName).classList.add('active');

            // Add active class to clicked tab
            event.target.classList.add('active');
        }

        // Load page
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Match Management Loaded');
        });
    </script>
@endsection
