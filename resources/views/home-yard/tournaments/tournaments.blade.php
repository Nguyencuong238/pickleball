@extends('layouts.homeyard')
<style>
    .toast {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 1.5rem 2rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xl);
        display: flex;
        align-items: center;
        gap: 1rem;
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
        max-width: 400px;
    }

    .toast.success {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .toast.error {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .toast-icon {
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .toast-message {
        flex: 1;
        font-weight: 500;
    }

    .toast-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.25rem;
        cursor: pointer;
        flex-shrink: 0;
        padding: 0;
        opacity: 0.8;
        transition: opacity var(--transition);
    }

    .toast-close:hover {
        opacity: 1;
    }

    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }

    .toast.hide {
        animation: slideOut 0.3s ease-out forwards;
    }
</style>
<style>
    /* Page-specific styles */
    .filter-bar {
        background: var(--bg-white);
        padding: 1.5rem;
        border-radius: var(--radius-xl);
        margin-bottom: 2rem;
        box-shadow: var(--shadow-md);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .filter-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .tournament-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .tournament-card {
        background: var(--bg-white);
        border-radius: var(--radius-xl);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        transition: all var(--transition);
        cursor: pointer;
    }

    .tournament-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-xl);
    }

    .tournament-header {
        height: 120px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .tournament-header::before {
        content: '🏆';
        position: absolute;
        font-size: 8rem;
        opacity: 0.1;
        right: -1rem;
        bottom: -2rem;
    }

    .tournament-status {
        position: absolute;
        top: 1rem;
        right: 1rem;
    }

    .tournament-title {
        color: white;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }

    .tournament-date {
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.875rem;
        position: relative;
        z-index: 1;
    }

    .tournament-body {
        padding: 1.5rem;
    }

    .tournament-meta {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .meta-item {
        display: flex;
        flex-direction: column;
    }

    .meta-label {
        font-size: 0.75rem;
        color: var(--text-light);
        margin-bottom: 0.25rem;
    }

    .meta-value {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .tournament-progress {
        margin-bottom: 1rem;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }

    .tournament-footer {
        display: flex;
        gap: 0.5rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }

    .view-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
        background: var(--bg-white);
        padding: 0.5rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
    }

    .view-tab {
        flex: 1;
        padding: 0.75rem 1.5rem;
        background: transparent;
        border: none;
        border-radius: var(--radius-md);
        cursor: pointer;
        font-weight: 600;
        color: var(--text-secondary);
        transition: all var(--transition);
    }

    .view-tab.active {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        box-shadow: var(--shadow-md);
    }

    .list-view .tournament-card {
        display: flex;
        flex-direction: row;
        align-items: center;
    }

    .list-view .tournament-header {
        width: 200px;
        height: auto;
        flex-shrink: 0;
    }

    .list-view .tournament-body {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 2rem;
    }

    .list-view .tournament-meta {
        display: flex;
        gap: 2rem;
        margin: 0;
    }

    .list-view .tournament-footer {
        border: none;
        padding: 0;
        margin-left: auto;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: var(--bg-white);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-md);
    }

    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .empty-description {
        color: var(--text-secondary);
        margin-bottom: 2rem;
    }

    .bulk-actions {
        background: var(--bg-white);
        padding: 1rem 1.5rem;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        margin-bottom: 2rem;
        display: none;
        align-items: center;
        gap: 1rem;
    }

    .bulk-actions.show {
        display: flex;
    }

    .bulk-info {
        flex: 1;
        font-weight: 600;
        color: var(--text-primary);
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 2rem;
    }

    .pagination-btn {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-white);
        cursor: pointer;
        transition: all var(--transition);
        font-weight: 600;
    }

    .pagination-btn:hover:not(.active):not(:disabled) {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .pagination-btn.active {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-color: transparent;
    }

    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .tournament-card input[type="checkbox"] {
        position: absolute;
        top: 1rem;
        left: 1rem;
        width: 20px;
        height: 20px;
        cursor: pointer;
        z-index: 2;
        accent-color: var(--primary-color);
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background: var(--bg-white);
        border-radius: var(--radius-xl);
        max-width: 600px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: var(--shadow-xl);
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 2px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--text-light);
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1.5rem;
        border-top: 2px solid var(--border-color);
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }
</style>
@section('content')
    <!-- Toast notification container -->
    <div id="toastContainer"></div>

    <main class="main-content" id="mainContent">
        <div class="container">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <h1>Quản Lý Giải Đấu</h1>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">
                            <a href="overview.html" class="breadcrumb-link">🏠 Dashboard</a>
                        </span>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item">Giải đấu</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <input type="text" class="search-input" placeholder="Tìm kiếm giải đấu..." id="searchInput">
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

            <!-- Stats Summary -->
            <div class="stats-grid fade-in">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-label">Tổng Giải Đấu</div>
                            <div class="stat-value">24</div>
                        </div>
                        <div class="stat-icon primary">🏆</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-label">Đang Diễn Ra</div>
                            <div class="stat-value">12</div>
                        </div>
                        <div class="stat-icon success">▶️</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-label">Sắp Tới</div>
                            <div class="stat-value">8</div>
                        </div>
                        <div class="stat-icon warning">📅</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-label">Đã Kết Thúc</div>
                            <div class="stat-value">4</div>
                        </div>
                        <div class="stat-icon danger">✅</div>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar fade-in">
                <div class="filter-grid">
                    <div class="form-group" style="margin: 0;">
                        <select class="form-select" id="statusFilter">
                            <option value="">Tất cả trạng thái</option>
                            <option value="ongoing">Đang diễn ra</option>
                            <option value="upcoming">Sắp tới</option>
                            <option value="completed">Đã kết thúc</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <select class="form-select" id="typeFilter">
                            <option value="">Tất cả loại giải</option>
                            <option value="single-men">Đơn nam</option>
                            <option value="single-women">Đơn nữ</option>
                            <option value="double-men">Đôi nam</option>
                            <option value="double-women">Đôi nữ</option>
                            <option value="double-mixed">Đôi nam nữ</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <select class="form-select" id="locationFilter">
                            <option value="">Tất cả địa điểm</option>
                            <option value="hcm">TP. Hồ Chí Minh</option>
                            <option value="hn">Hà Nội</option>
                            <option value="dn">Đà Nẵng</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <select class="form-select" id="sortFilter">
                            <option value="newest">Mới nhất</option>
                            <option value="oldest">Cũ nhất</option>
                            <option value="name-asc">Tên A-Z</option>
                            <option value="name-desc">Tên Z-A</option>
                            <option value="date-asc">Ngày tăng dần</option>
                            <option value="date-desc">Ngày giảm dần</option>
                        </select>
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-primary" onclick="openCreateModal()">
                        ➕ Tạo Giải Mới
                    </button>
                    <button class="btn btn-secondary" onclick="resetFilters()">
                        🔄 Đặt lại bộ lọc
                    </button>
                    <button class="btn btn-secondary">
                        📊 Xuất Excel
                    </button>
                    <button class="btn btn-ghost" onclick="toggleView()">
                        <span id="viewIcon">📋</span> Chuyển chế độ xem
                    </button>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div class="bulk-actions" id="bulkActions">
                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                <div class="bulk-info">
                    <span id="selectedCount">0</span> giải đấu được chọn
                </div>
                <button class="btn btn-secondary btn-sm">
                    📋 Nhân bản
                </button>
                <button class="btn btn-secondary btn-sm">
                    📦 Archive
                </button>
                <button class="btn btn-danger btn-sm">
                    🗑️ Xóa
                </button>
            </div>

            <!-- View Tabs -->
            <div class="view-tabs fade-in">
                <button class="view-tab active" onclick="filterByStatus('all')">
                    Tất cả (24)
                </button>
                <button class="view-tab" onclick="filterByStatus('ongoing')">
                    Đang diễn ra (12)
                </button>
                <button class="view-tab" onclick="filterByStatus('upcoming')">
                    Sắp tới (8)
                </button>
                <button class="view-tab" onclick="filterByStatus('completed')">
                    Đã kết thúc (4)
                </button>
            </div>

            <!-- Tournament Grid -->
            <div class="tournament-grid" id="tournamentGrid">
                <!-- Tournament Card 1 -->
                <div class="tournament-card fade-in">
                    <input type="checkbox" class="tournament-checkbox" onchange="updateBulkActions()">
                    <div class="tournament-header">
                        <span class="tournament-status">
                            <span class="badge badge-success">Đang diễn ra</span>
                        </span>
                        <h3 class="tournament-title">Giải Pickleball Mở Rộng TP.HCM 2025</h3>
                        <div class="tournament-date">📅 20-22/01/2025</div>
                    </div>
                    <div class="tournament-body">
                        <div class="tournament-meta">
                            <div class="meta-item">
                                <div class="meta-label">Vận động viên</div>
                                <div class="meta-value">64 VĐV</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Trận đấu</div>
                                <div class="meta-value">32/48</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Loại giải</div>
                                <div class="meta-value">Đơn nam</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Giải thưởng</div>
                                <div class="meta-value">₫50M</div>
                            </div>
                        </div>
                        <div class="tournament-progress">
                            <div class="progress-label">
                                <span>Tiến độ</span>
                                <span>67%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 67%;"></div>
                            </div>
                        </div>
                        <div class="tournament-footer">
                            <button class="btn btn-primary btn-sm" style="flex: 1;"
                                onclick="window.location.href='tournament-dashboard.html'">
                                👁️ Chi tiết
                            </button>
                            <button class="btn btn-secondary btn-sm">
                                ✏️
                            </button>
                            <button class="btn btn-secondary btn-sm">
                                ⋮
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tournament Card 2 -->
                <div class="tournament-card fade-in">
                    <input type="checkbox" class="tournament-checkbox" onchange="updateBulkActions()">
                    <div class="tournament-header">
                        <span class="tournament-status">
                            <span class="badge badge-warning">Sắp tới</span>
                        </span>
                        <h3 class="tournament-title">Cúp Pickleball Hà Nội 2025</h3>
                        <div class="tournament-date">📅 25-27/01/2025</div>
                    </div>
                    <div class="tournament-body">
                        <div class="tournament-meta">
                            <div class="meta-item">
                                <div class="meta-label">Vận động viên</div>
                                <div class="meta-value">48 VĐV</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Trận đấu</div>
                                <div class="meta-value">0/32</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Loại giải</div>
                                <div class="meta-value">Đơn nữ</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Giải thưởng</div>
                                <div class="meta-value">₫30M</div>
                            </div>
                        </div>
                        <div class="tournament-progress">
                            <div class="progress-label">
                                <span>Đăng ký</span>
                                <span>48/64 (75%)</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 75%;"></div>
                            </div>
                        </div>
                        <div class="tournament-footer">
                            <button class="btn btn-primary btn-sm" style="flex: 1;"
                                onclick="window.location.href='tournament-dashboard.html'">
                                👁️ Chi tiết
                            </button>
                            <button class="btn btn-secondary btn-sm">
                                ✏️
                            </button>
                            <button class="btn btn-secondary btn-sm">
                                ⋮
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tournament Card 3 -->
                <div class="tournament-card fade-in">
                    <input type="checkbox" class="tournament-checkbox" onchange="updateBulkActions()">
                    <div class="tournament-header">
                        <span class="tournament-status">
                            <span class="badge badge-success">Đang diễn ra</span>
                        </span>
                        <h3 class="tournament-title">Giải Đôi Nam Nữ Đà Nẵng</h3>
                        <div class="tournament-date">📅 22-24/01/2025</div>
                    </div>
                    <div class="tournament-body">
                        <div class="tournament-meta">
                            <div class="meta-item">
                                <div class="meta-label">Cặp đôi</div>
                                <div class="meta-value">32 cặp</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Trận đấu</div>
                                <div class="meta-value">12/24</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Loại giải</div>
                                <div class="meta-value">Đôi nam nữ</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Giải thưởng</div>
                                <div class="meta-value">₫40M</div>
                            </div>
                        </div>
                        <div class="tournament-progress">
                            <div class="progress-label">
                                <span>Tiến độ</span>
                                <span>50%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 50%;"></div>
                            </div>
                        </div>
                        <div class="tournament-footer">
                            <button class="btn btn-primary btn-sm" style="flex: 1;">
                                👁️ Chi tiết
                            </button>
                            <button class="btn btn-secondary btn-sm">
                                ✏️
                            </button>
                            <button class="btn btn-secondary btn-sm">
                                ⋮
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tournament Card 4 -->
                <div class="tournament-card fade-in">
                    <input type="checkbox" class="tournament-checkbox" onchange="updateBulkActions()">
                    <div class="tournament-header">
                        <span class="tournament-status">
                            <span class="badge badge-gray">Đã kết thúc</span>
                        </span>
                        <h3 class="tournament-title">Giải Nội Bộ Tháng 12</h3>
                        <div class="tournament-date">📅 15-18/12/2024</div>
                    </div>
                    <div class="tournament-body">
                        <div class="tournament-meta">
                            <div class="meta-item">
                                <div class="meta-label">Vận động viên</div>
                                <div class="meta-value">28 VĐV</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Trận đấu</div>
                                <div class="meta-value">14/14</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Loại giải</div>
                                <div class="meta-value">Đơn nam</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Giải thưởng</div>
                                <div class="meta-value">₫15M</div>
                            </div>
                        </div>
                        <div class="tournament-progress">
                            <div class="progress-label">
                                <span>Hoàn thành</span>
                                <span>100%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 100%;"></div>
                            </div>
                        </div>
                        <div class="tournament-footer">
                            <button class="btn btn-primary btn-sm" style="flex: 1;">
                                👁️ Chi tiết
                            </button>
                            <button class="btn btn-secondary btn-sm">
                                📊 Báo cáo
                            </button>
                            <button class="btn btn-secondary btn-sm">
                                ⋮
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add more tournament cards as needed -->
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <button class="pagination-btn" disabled>←</button>
                <button class="pagination-btn active">1</button>
                <button class="pagination-btn">2</button>
                <button class="pagination-btn">3</button>
                <button class="pagination-btn">4</button>
                <button class="pagination-btn">5</button>
                <button class="pagination-btn">→</button>
            </div>
        </div>
    </main>
    <div class="modal" id="createModal">
        <div class="modal-content">
            <form id="tournamentForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h3 class="modal-title">Tạo Giải Đấu Mới</h3>
                    <button type="button" class="modal-close" onclick="closeCreateModal()">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Tên giải đấu *</label>
                        <input type="text" class="form-input" name="name" placeholder="VD: Giải Pickleball Mở Rộng 2025" required>
                    </div>
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Ngày bắt đầu *</label>
                            <input type="date" class="form-input" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ngày kết thúc *</label>
                            <input type="date" class="form-input" name="end_date">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Địa điểm *</label>
                        <input type="text" class="form-input" name="location" placeholder="VD: Sân Pickleball Thảo Điền">
                    </div>
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Loại giải *</label>
                            <select class="form-select" name="competition_format">
                                <option value="">Chọn loại giải</option>
                                <option value="single">Đơn</option>
                                <option value="double">Đôi</option>
                                <option value="mixed">Đôi nam nữ</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Số VĐV tối đa</label>
                            <input type="number" class="form-input" name="max_participants" placeholder="64">
                        </div>
                    </div>
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Lệ phí giải đấu (VNĐ)</label>
                            <input type="number" class="form-input" name="price" placeholder="500000" step="0.01" min="0" max="99999999">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Giải thưởng (VNĐ)</label>
                            <input type="number" class="form-input" name="prizes" placeholder="50000000" step="0.01" min="0" max="99999999">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Thời hạn đăng ký</label>
                        <input type="datetime-local" class="form-input" name="registration_deadline">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-input" name="description" placeholder="Nhập mô tả giải đấu..." rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Quy định</label>
                        <textarea class="form-input" name="competition_rules" placeholder="Nhập quy định của giải đấu..." rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Quyền lợi khi tham gia</label>
                        <textarea class="form-input" name="registration_benefits" placeholder="Nhập quyền lợi khi tham gia..." rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ảnh</label>
                        <input type="file" class="form-input" id="imageInput" name="image" accept="image/*">
                        <div id="imagePreview" style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Banner</label>
                        <input type="file" class="form-input" id="bannerInput" name="banner" accept="image/*">
                        <div id="bannerPreview" style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;"></div>
                    </div>
                    <input type="hidden" name="status" value="1">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Tạo giải đấu</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('js')
    <script>
        // Toast notification function
        function showToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };
            
            toast.innerHTML = `
                <span class="toast-icon">${icons[type] || '✓'}</span>
                <span class="toast-message">${message}</span>
                <button class="toast-close" onclick="this.parentElement.classList.add('hide'); setTimeout(() => this.parentElement.remove(), 300)">×</button>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        // Check for flash success message
        @if(session('success'))
            showToast('{{ session('success') }}', 'success');
        @endif

        @if(session('error'))
            showToast('{{ session('error') }}', 'error');
        @endif

        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
        }

        // Toggle view (grid/list)
        let isGridView = true;

        function toggleView() {
            const grid = document.getElementById('tournamentGrid');
            const icon = document.getElementById('viewIcon');

            isGridView = !isGridView;

            if (isGridView) {
                grid.classList.remove('list-view');
                grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(350px, 1fr))';
                icon.textContent = '📋';
            } else {
                grid.classList.add('list-view');
                grid.style.gridTemplateColumns = '1fr';
                icon.textContent = '🔲';
            }
        }

        // Filter by status
        function filterByStatus(status) {
            const tabs = document.querySelectorAll('.view-tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');

            console.log('Filtering by:', status);
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('statusFilter').value = '';
            document.getElementById('typeFilter').value = '';
            document.getElementById('locationFilter').value = '';
            document.getElementById('sortFilter').value = 'newest';
            document.getElementById('searchInput').value = '';
        }

        // Bulk actions
        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.tournament-checkbox:checked');
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');

            if (checkboxes.length > 0) {
                bulkActions.classList.add('show');
                selectedCount.textContent = checkboxes.length;
            } else {
                bulkActions.classList.remove('show');
            }
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.tournament-checkbox');

            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });

            updateBulkActions();
        }

        // Modal functions
         function openCreateModal() {
             document.getElementById('createModal').classList.add('show');
         }
        
         function closeCreateModal() {
             document.getElementById('createModal').classList.remove('show');
             // Reset form after a short delay to avoid interfering with submission
             setTimeout(() => {
                 document.getElementById('tournamentForm').reset();
                 document.getElementById('imagePreview').innerHTML = '';
                 document.getElementById('bannerPreview').innerHTML = '';
             }, 500);
         }

        // Image preview
        const imageInput = document.getElementById('imageInput');
        const imagePreview = document.getElementById('imagePreview');

        imageInput?.addEventListener('change', function() {
            imagePreview.innerHTML = '';
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '120px';
                    img.style.height = '120px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = 'var(--radius-md)';
                    img.style.border = '2px solid var(--border-color)';
                    imagePreview.appendChild(img);
                };
                
                reader.readAsDataURL(file);
            }
        });

        // Banner preview
        const bannerInput = document.getElementById('bannerInput');
        const bannerPreview = document.getElementById('bannerPreview');

        bannerInput?.addEventListener('change', function() {
            bannerPreview.innerHTML = '';
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '120px';
                    img.style.height = '80px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = 'var(--radius-md)';
                    img.style.border = '2px solid var(--border-color)';
                    bannerPreview.appendChild(img);
                };
                
                reader.readAsDataURL(file);
            }
        });

        // Set form action - controller handles the redirect
         document.getElementById('tournamentForm')?.setAttribute('action', '{{ route("admin.tournaments.store") }}')

        // Initialize
        if (window.innerWidth <= 1024) {
            toggleSidebar();
        }

        window.addEventListener('resize', () => {
            if (window.innerWidth <= 1024) {
                document.getElementById('sidebar').classList.add('collapsed');
                document.getElementById('mainContent').classList.add('sidebar-collapsed');
            }
        });

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            const modal = document.getElementById('createModal');
            if (e.target === modal) {
                closeCreateModal();
            }
        });

        console.log('Tournaments Page Loaded');
    </script>
@endsection
