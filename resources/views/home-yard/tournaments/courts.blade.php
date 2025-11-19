@extends('layouts.homeyard')
<style>
    /* Page-specific styles */
    .court-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .court-card {
        background: var(--bg-white);
        border-radius: var(--radius-xl);
        padding: 1.5rem;
        border: 2px solid var(--border-color);
        transition: all var(--transition);
        position: relative;
        overflow: hidden;
    }

    .court-card:hover {
        border-color: var(--primary-color);
        box-shadow: var(--shadow-lg);
        transform: translateY(-5px);
    }

    .court-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }

    .court-card.available::before {
        background: linear-gradient(135deg, var(--accent-green), #2dd4bf);
    }

    .court-card.occupied::before {
        background: linear-gradient(135deg, var(--accent-red), #f97316);
    }

    .court-card.maintenance::before {
        background: linear-gradient(135deg, var(--accent-yellow), #f59e0b);
    }

    .court-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .court-number {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .court-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .court-card.available .court-icon {
        background: linear-gradient(135deg, var(--accent-green), #2dd4bf);
    }

    .court-card.occupied .court-icon {
        background: linear-gradient(135deg, var(--accent-red), #f97316);
    }

    .court-card.maintenance .court-icon {
        background: linear-gradient(135deg, var(--accent-yellow), #f59e0b);
    }

    .court-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .court-subtitle {
        font-size: 0.75rem;
        color: var(--text-light);
    }

    .court-status {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .court-status.available {
        background: rgba(74, 222, 128, 0.1);
        color: var(--accent-green);
    }

    .court-status.occupied {
        background: rgba(255, 107, 107, 0.1);
        color: var(--accent-red);
    }

    .court-status.maintenance {
        background: rgba(255, 211, 61, 0.1);
        color: #ca8a04;
    }

    .court-info {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin: 1.5rem 0;
    }

    .court-info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .court-info-label {
        font-size: 0.75rem;
        color: var(--text-light);
    }

    .court-info-value {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .court-schedule {
        background: var(--bg-light);
        padding: 1rem;
        border-radius: var(--radius-md);
        margin: 1rem 0;
    }

    .schedule-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
    }

    .schedule-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.75rem;
    }

    .schedule-item:last-child {
        border-bottom: none;
    }

    .schedule-time {
        color: var(--text-secondary);
    }

    .schedule-event {
        font-weight: 600;
        color: var(--text-primary);
    }

    .court-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .court-stats-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-overview-box {
        background: var(--bg-white);
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        border: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-overview-icon {
        width: 56px;
        height: 56px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    .stat-overview-icon.success {
        background: rgba(74, 222, 128, 0.1);
    }

    .stat-overview-icon.danger {
        background: rgba(255, 107, 107, 0.1);
    }

    .stat-overview-icon.warning {
        background: rgba(255, 211, 61, 0.1);
    }

    .stat-overview-icon.info {
        background: rgba(0, 153, 204, 0.1);
    }

    .stat-overview-content {
        flex: 1;
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

    .filter-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
    }

    .filter-tab {
        padding: 0.75rem 1.5rem;
        background: var(--bg-white);
        border: 2px solid var(--border-color);
        border-radius: var(--radius-full);
        cursor: pointer;
        transition: all var(--transition);
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-secondary);
        white-space: nowrap;
    }

    .filter-tab:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .filter-tab.active {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-color: transparent;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: var(--bg-white);
        border-radius: var(--radius-xl);
        padding: 2rem;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: var(--shadow-xl);
        animation: fadeIn 0.3s ease;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
    }

    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--text-light);
        transition: color var(--transition);
    }

    .modal-close:hover {
        color: var(--accent-red);
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 2px solid var(--border-color);
    }

    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .timeline-item::before {
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

    .timeline-item::after {
        content: '';
        position: absolute;
        left: -1.5rem;
        top: 1.5rem;
        bottom: 0;
        width: 2px;
        background: var(--border-color);
    }

    .timeline-item:last-child::after {
        display: none;
    }

    .timeline-time {
        font-size: 0.75rem;
        color: var(--text-light);
        margin-bottom: 0.25rem;
    }

    .timeline-content {
        color: var(--text-secondary);
        font-size: 0.875rem;
    }
</style>
@section('content')
    <main class="main-content" id="mainContent">
        <div class="container">
            <!-- Header -->
            <div class="top-header">
                <div class="header-left">
                    <h1>Quản Lý Sân Thi Đấu</h1>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">
                            <a href="overview.html" class="breadcrumb-link">Trang chủ</a>
                        </span>
                        <span class="breadcrumb-separator">›</span>
                        <span class="breadcrumb-item">Quản Lý Sân</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <span class="search-icon">🔍</span>
                        <input type="text" class="search-input" placeholder="Tìm kiếm sân...">
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
            <div class="court-stats-overview fade-in">
                <div class="stat-overview-box">
                    <div class="stat-overview-icon info">🏟️</div>
                    <div class="stat-overview-content">
                        <div class="stat-overview-value">12</div>
                        <div class="stat-overview-label">Tổng Số Sân</div>
                    </div>
                </div>
                <div class="stat-overview-box">
                    <div class="stat-overview-icon success">✅</div>
                    <div class="stat-overview-content">
                        <div class="stat-overview-value">8</div>
                        <div class="stat-overview-label">Sân Sẵn Sàng</div>
                    </div>
                </div>
                <div class="stat-overview-box">
                    <div class="stat-overview-icon danger">🔴</div>
                    <div class="stat-overview-content">
                        <div class="stat-overview-value">3</div>
                        <div class="stat-overview-label">Sân Đang Sử Dụng</div>
                    </div>
                </div>
                <div class="stat-overview-box">
                    <div class="stat-overview-icon warning">⚠️</div>
                    <div class="stat-overview-content">
                        <div class="stat-overview-value">1</div>
                        <div class="stat-overview-label">Đang Bảo Trì</div>
                    </div>
                </div>
            </div>

            <!-- Filter Tabs & Court List -->
            <div class="card fade-in">
                <div class="card-header">
                    <h3 class="card-title">Danh Sách Sân</h3>
                    <div class="card-actions">
                        <button class="btn btn-secondary btn-sm">📊 Báo Cáo</button>
                        <button class="btn btn-primary btn-sm" onclick="openAddCourtModal()">
                            ➕ Thêm Sân
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="filter-tabs">
                        <button class="filter-tab active" onclick="filterCourts('all')">
                            Tất Cả (12)
                        </button>
                        <button class="filter-tab" onclick="filterCourts('available')">
                            ✅ Sẵn Sàng (8)
                        </button>
                        <button class="filter-tab" onclick="filterCourts('occupied')">
                            🔴 Đang Sử Dụng (3)
                        </button>
                        <button class="filter-tab" onclick="filterCourts('maintenance')">
                            ⚠️ Bảo Trì (1)
                        </button>
                    </div>

                    <!-- Court Grid -->
                    <div class="court-grid">
                        <!-- Court 1 - Available -->
                        <div class="court-card available">
                            <div class="court-header">
                                <div class="court-number">
                                    <div class="court-icon">1</div>
                                    <div>
                                        <div class="court-title">Sân 1</div>
                                        <div class="court-subtitle">Indoor • Standard</div>
                                    </div>
                                </div>
                                <span class="court-status available">✅ Sẵn Sàng</span>
                            </div>

                            <div class="court-info">
                                <div class="court-info-item">
                                    <div class="court-info-label">Loại Sân</div>
                                    <div class="court-info-value">Indoor</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Kích Thước</div>
                                    <div class="court-info-value">13.4m x 6.1m</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Mặt Sân</div>
                                    <div class="court-info-value">Acrylic</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Sức Chứa</div>
                                    <div class="court-info-value">50 người</div>
                                </div>
                            </div>

                            <div class="court-schedule">
                                <div class="schedule-title">Lịch Hôm Nay</div>
                                <div class="schedule-item">
                                    <span class="schedule-time">15:00 - 16:30</span>
                                    <span class="schedule-event">Trận #A-005</span>
                                </div>
                                <div class="schedule-item">
                                    <span class="schedule-time">17:00 - 18:30</span>
                                    <span class="schedule-event">Trận #A-012</span>
                                </div>
                            </div>

                            <div class="court-actions">
                                <button class="btn btn-primary btn-sm" style="flex: 1;" onclick="openCourtDetails(1)">
                                    👁️ Chi Tiết
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="openEditCourt(1)">
                                    ✏️
                                </button>
                                <button class="btn btn-ghost btn-sm">
                                    ⚙️
                                </button>
                            </div>
                        </div>

                        <!-- Court 2 - Occupied -->
                        <div class="court-card occupied">
                            <div class="court-header">
                                <div class="court-number">
                                    <div class="court-icon">2</div>
                                    <div>
                                        <div class="court-title">Sân 2</div>
                                        <div class="court-subtitle">Indoor • Premium</div>
                                    </div>
                                </div>
                                <span class="court-status occupied">🔴 Đang Thi Đấu</span>
                            </div>

                            <div class="court-info">
                                <div class="court-info-item">
                                    <div class="court-info-label">Loại Sân</div>
                                    <div class="court-info-value">Indoor</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Kích Thước</div>
                                    <div class="court-info-value">13.4m x 6.1m</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Mặt Sân</div>
                                    <div class="court-info-value">Polyurethane</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Sức Chứa</div>
                                    <div class="court-info-value">80 người</div>
                                </div>
                            </div>

                            <div class="court-schedule">
                                <div class="schedule-title">Trận Đang Diễn Ra</div>
                                <div class="schedule-item">
                                    <span class="schedule-time">14:30 - 16:00</span>
                                    <span class="schedule-event">Trận #A-001 • Set 2</span>
                                </div>
                                <div style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-light);">
                                    ⏱️ Còn khoảng 45 phút
                                </div>
                            </div>

                            <div class="court-actions">
                                <button class="btn btn-primary btn-sm" style="flex: 1;" onclick="openCourtDetails(2)">
                                    👁️ Chi Tiết
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="openEditCourt(2)">
                                    ✏️
                                </button>
                                <button class="btn btn-ghost btn-sm">
                                    ⚙️
                                </button>
                            </div>
                        </div>

                        <!-- Court 3 - Available -->
                        <div class="court-card available">
                            <div class="court-header">
                                <div class="court-number">
                                    <div class="court-icon">3</div>
                                    <div>
                                        <div class="court-title">Sân 3</div>
                                        <div class="court-subtitle">Outdoor • Standard</div>
                                    </div>
                                </div>
                                <span class="court-status available">✅ Sẵn Sàng</span>
                            </div>

                            <div class="court-info">
                                <div class="court-info-item">
                                    <div class="court-info-label">Loại Sân</div>
                                    <div class="court-info-value">Outdoor</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Kích Thước</div>
                                    <div class="court-info-value">13.4m x 6.1m</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Mặt Sân</div>
                                    <div class="court-info-value">Concrete</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Sức Chứa</div>
                                    <div class="court-info-value">40 người</div>
                                </div>
                            </div>

                            <div class="court-schedule">
                                <div class="schedule-title">Lịch Hôm Nay</div>
                                <div class="schedule-item">
                                    <span class="schedule-time">16:00 - 17:30</span>
                                    <span class="schedule-event">Trận #B-008</span>
                                </div>
                            </div>

                            <div class="court-actions">
                                <button class="btn btn-primary btn-sm" style="flex: 1;" onclick="openCourtDetails(3)">
                                    👁️ Chi Tiết
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="openEditCourt(3)">
                                    ✏️
                                </button>
                                <button class="btn btn-ghost btn-sm">
                                    ⚙️
                                </button>
                            </div>
                        </div>

                        <!-- Court 4 - Maintenance -->
                        <div class="court-card maintenance">
                            <div class="court-header">
                                <div class="court-number">
                                    <div class="court-icon">4</div>
                                    <div>
                                        <div class="court-title">Sân 4</div>
                                        <div class="court-subtitle">Indoor • Standard</div>
                                    </div>
                                </div>
                                <span class="court-status maintenance">⚠️ Bảo Trì</span>
                            </div>

                            <div class="court-info">
                                <div class="court-info-item">
                                    <div class="court-info-label">Loại Sân</div>
                                    <div class="court-info-value">Indoor</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Kích Thước</div>
                                    <div class="court-info-value">13.4m x 6.1m</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Mặt Sân</div>
                                    <div class="court-info-value">Acrylic</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Dự Kiến</div>
                                    <div class="court-info-value">22/01/2025</div>
                                </div>
                            </div>

                            <div class="court-schedule">
                                <div class="schedule-title">Lý Do Bảo Trì</div>
                                <div style="font-size: 0.875rem; color: var(--text-secondary); padding: 0.5rem 0;">
                                    Thay thế mặt sân và sửa chữa lưới
                                </div>
                            </div>

                            <div class="court-actions">
                                <button class="btn btn-primary btn-sm" style="flex: 1;" onclick="openCourtDetails(4)">
                                    👁️ Chi Tiết
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="openEditCourt(4)">
                                    ✏️
                                </button>
                                <button class="btn btn-ghost btn-sm">
                                    ⚙️
                                </button>
                            </div>
                        </div>

                        <!-- Court 5 - Occupied -->
                        <div class="court-card occupied">
                            <div class="court-header">
                                <div class="court-number">
                                    <div class="court-icon">5</div>
                                    <div>
                                        <div class="court-title">Sân 5</div>
                                        <div class="court-subtitle">Indoor • Standard</div>
                                    </div>
                                </div>
                                <span class="court-status occupied">🔴 Đang Thi Đấu</span>
                            </div>

                            <div class="court-info">
                                <div class="court-info-item">
                                    <div class="court-info-label">Loại Sân</div>
                                    <div class="court-info-value">Indoor</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Kích Thước</div>
                                    <div class="court-info-value">13.4m x 6.1m</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Mặt Sân</div>
                                    <div class="court-info-value">Acrylic</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Sức Chứa</div>
                                    <div class="court-info-value">50 người</div>
                                </div>
                            </div>

                            <div class="court-schedule">
                                <div class="schedule-title">Trận Đang Diễn Ra</div>
                                <div class="schedule-item">
                                    <span class="schedule-time">14:45 - 16:15</span>
                                    <span class="schedule-event">Trận #A-002 • Set 1</span>
                                </div>
                                <div style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-light);">
                                    ⏱️ Còn khoảng 1 giờ
                                </div>
                            </div>

                            <div class="court-actions">
                                <button class="btn btn-primary btn-sm" style="flex: 1;" onclick="openCourtDetails(5)">
                                    👁️ Chi Tiết
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="openEditCourt(5)">
                                    ✏️
                                </button>
                                <button class="btn btn-ghost btn-sm">
                                    ⚙️
                                </button>
                            </div>
                        </div>

                        <!-- Court 6 - Available -->
                        <div class="court-card available">
                            <div class="court-header">
                                <div class="court-number">
                                    <div class="court-icon">6</div>
                                    <div>
                                        <div class="court-title">Sân 6</div>
                                        <div class="court-subtitle">Outdoor • Premium</div>
                                    </div>
                                </div>
                                <span class="court-status available">✅ Sẵn Sàng</span>
                            </div>

                            <div class="court-info">
                                <div class="court-info-item">
                                    <div class="court-info-label">Loại Sân</div>
                                    <div class="court-info-value">Outdoor</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Kích Thước</div>
                                    <div class="court-info-value">13.4m x 6.1m</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Mặt Sân</div>
                                    <div class="court-info-value">Sport Court</div>
                                </div>
                                <div class="court-info-item">
                                    <div class="court-info-label">Sức Chứa</div>
                                    <div class="court-info-value">60 người</div>
                                </div>
                            </div>

                            <div class="court-schedule">
                                <div class="schedule-title">Lịch Hôm Nay</div>
                                <div
                                    style="text-align: center; padding: 1rem 0; color: var(--text-light); font-size: 0.875rem;">
                                    Không có lịch thi đấu
                                </div>
                            </div>

                            <div class="court-actions">
                                <button class="btn btn-primary btn-sm" style="flex: 1;" onclick="openCourtDetails(6)">
                                    👁️ Chi Tiết
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="openEditCourt(6)">
                                    ✏️
                                </button>
                                <button class="btn btn-ghost btn-sm">
                                    ⚙️
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div class="modal" id="addCourtModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Thêm Sân Mới</h3>
                <button class="modal-close" onclick="closeAddCourtModal()">×</button>
            </div>
            <form>
                <div class="form-group">
                    <label class="form-label">Tên sân *</label>
                    <input type="text" class="form-input" placeholder="Ví dụ: Sân 7" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Loại sân *</label>
                    <select class="form-select" required>
                        <option value="">Chọn loại sân</option>
                        <option value="indoor-standard">Indoor - Standard</option>
                        <option value="indoor-premium">Indoor - Premium</option>
                        <option value="outdoor-standard">Outdoor - Standard</option>
                        <option value="outdoor-premium">Outdoor - Premium</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Mặt sân *</label>
                    <select class="form-select" required>
                        <option value="">Chọn loại mặt sân</option>
                        <option value="acrylic">Acrylic</option>
                        <option value="polyurethane">Polyurethane</option>
                        <option value="concrete">Concrete</option>
                        <option value="sport-court">Sport Court</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Kích thước</label>
                    <input type="text" class="form-input" placeholder="13.4m x 6.1m" value="13.4m x 6.1m">
                </div>
                <div class="form-group">
                    <label class="form-label">Sức chứa (người)</label>
                    <input type="number" class="form-input" placeholder="50">
                </div>
                <div class="form-group">
                    <label class="form-label">Ghi chú</label>
                    <textarea class="form-textarea" placeholder="Thông tin thêm về sân..."></textarea>
                </div>
            </form>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeAddCourtModal()">Hủy</button>
                <button class="btn btn-primary">💾 Lưu</button>
            </div>
        </div>
    </div>

    <!-- Court Details Modal -->
    <div class="modal" id="courtDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Chi Tiết Sân 1</h3>
                <button class="modal-close" onclick="closeCourtDetailsModal()">×</button>
            </div>
            <div>
                <div class="form-group">
                    <label class="form-label">Thông Tin Cơ Bản</label>
                    <div class="court-info">
                        <div class="court-info-item">
                            <div class="court-info-label">Trạng thái</div>
                            <div class="court-info-value">✅ Sẵn Sàng</div>
                        </div>
                        <div class="court-info-item">
                            <div class="court-info-label">Loại sân</div>
                            <div class="court-info-value">Indoor - Standard</div>
                        </div>
                        <div class="court-info-item">
                            <div class="court-info-label">Mặt sân</div>
                            <div class="court-info-value">Acrylic</div>
                        </div>
                        <div class="court-info-item">
                            <div class="court-info-label">Kích thước</div>
                            <div class="court-info-value">13.4m x 6.1m</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Lịch Sử Hoạt Động</label>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-time">14:30 - Hôm nay</div>
                            <div class="timeline-content">Trận #A-001 đã kết thúc</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-time">12:00 - Hôm nay</div>
                            <div class="timeline-content">Bắt đầu trận #A-001</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-time">10:30 - Hôm nay</div>
                            <div class="timeline-content">Hoàn thành bảo trì định kỳ</div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-time">19/01/2025</div>
                            <div class="timeline-content">Tổ chức 8 trận đấu</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Thống Kê Sử Dụng</label>
                    <div class="court-info">
                        <div class="court-info-item">
                            <div class="court-info-label">Tổng trận tháng này</div>
                            <div class="court-info-value">45 trận</div>
                        </div>
                        <div class="court-info-item">
                            <div class="court-info-label">Tỷ lệ sử dụng</div>
                            <div class="court-info-value">82%</div>
                        </div>
                        <div class="court-info-item">
                            <div class="court-info-label">Bảo trì gần nhất</div>
                            <div class="court-info-value">10/01/2025</div>
                        </div>
                        <div class="court-info-item">
                            <div class="court-info-label">Đánh giá</div>
                            <div class="court-info-value">⭐ 4.8/5</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeCourtDetailsModal()">Đóng</button>
                <button class="btn btn-primary" onclick="openEditCourt(1)">✏️ Chỉnh Sửa</button>
            </div>
        </div>
    </div>
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

        // Filter courts
        function filterCourts(status) {
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
            console.log('Filtering courts by:', status);
        }

        // Modal functions
        function openAddCourtModal() {
            document.getElementById('addCourtModal').classList.add('active');
        }

        function closeAddCourtModal() {
            document.getElementById('addCourtModal').classList.remove('active');
        }

        function openCourtDetails(courtId) {
            document.getElementById('courtDetailsModal').classList.add('active');
        }

        function closeCourtDetailsModal() {
            document.getElementById('courtDetailsModal').classList.remove('active');
        }

        function openEditCourt(courtId) {
            console.log('Edit court:', courtId);
        }

        // Close modal on outside click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });

        // Load page
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Court Management Loaded');
        });
    </script>
@endsection
