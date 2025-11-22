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
    
    .pagination a.pagination-btn {
        text-decoration: none;
        color: inherit;
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
                            <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                        <div class="stat-icon primary">🏆</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-label">Đang Diễn Ra</div>
                            <div class="stat-value">{{ $stats['ongoing'] ?? 0 }}</div>
                        </div>
                        <div class="stat-icon success">▶️</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-label">Sắp Tới</div>
                            <div class="stat-value">{{ $stats['upcoming'] ?? 0 }}</div>
                        </div>
                        <div class="stat-icon warning">📅</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-label">Đã Kết Thúc</div>
                            <div class="stat-value">{{ $stats['completed'] ?? 0 }}</div>
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
                            <option value="ongoing">Đang diễn ra</option>
                            <option value="upcoming">Sắp tới</option>
                            <option value="completed">Đã kết thúc</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                    </div>
                    {{-- <div class="form-group" style="margin: 0;">
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
                            <option value="hcm">TP. Hồ Chí Minh</option>
                            <option value="hn">Hà Nội</option>
                            <option value="dn">Đà Nẵng</option>
                            <option value="other">Khác</option>
                        </select>
                    </div> --}}
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
                    <button class="btn btn-secondary" onclick="exportToExcel()">
                         📊 Xuất Excel
                     </button>
                    <button class="btn btn-ghost" onclick="toggleView()">
                        <span id="viewIcon">📋</span> Chuyển chế độ xem
                    </button>
                </div>
            </div>

            <!-- Bulk Actions -->
            {{-- <div class="bulk-actions" id="bulkActions">
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
            </div> --}}

            <!-- View Tabs -->
            <div class="view-tabs fade-in">
                <button class="view-tab active" onclick="filterByStatus('all')">
                    Tất cả ({{ $stats['total'] ?? 0 }})
                </button>
                <button class="view-tab" onclick="filterByStatus('ongoing')">
                    Đang diễn ra ({{ $stats['ongoing'] ?? 0 }})
                </button>
                <button class="view-tab" onclick="filterByStatus('upcoming')">
                    Sắp tới ({{ $stats['upcoming'] ?? 0 }})
                </button>
                <button class="view-tab" onclick="filterByStatus('completed')">
                    Đã kết thúc ({{ $stats['completed'] ?? 0 }})
                </button>
            </div>

            <!-- Tournament Grid -->
            <div class="tournament-grid" id="tournamentGrid">
                @forelse($tournaments as $tournament)
                    @php
                        $athleteCount = $tournament->athletes()->count();
                        $maxParticipants = $tournament->max_participants ?? 64;
                        $progress = $maxParticipants > 0 ? round(($athleteCount / $maxParticipants) * 100) : 0;
                        
                        $now = now();
                        $startDate = strtotime($tournament->start_date);
                        $endDate = $tournament->end_date ? strtotime($tournament->end_date) : null;
                        $nowTime = $now->timestamp;
                        
                        // Determine status based on dates
                        if ($startDate > $nowTime) {
                            // Start date is in the future
                            $statusBadge = 'badge-warning';
                            $statusText = 'Sắp tới';
                            $isUpcoming = true;
                        } elseif ($startDate <= $nowTime && ($endDate === null || $endDate >= $nowTime)) {
                            // Started and either no end date or end date is in the future
                            $statusBadge = 'badge-success';
                            $statusText = 'Đang diễn ra';
                            $isOngoing = true;
                        } else {
                            // End date has passed
                            $statusBadge = 'badge-gray';
                            $statusText = 'Đã kết thúc';
                            $isCompleted = true;
                        }
                        
                        $formatText = '';
                        if ($tournament->competition_format === 'single') {
                            $formatText = 'Đơn';
                        } elseif ($tournament->competition_format === 'double') {
                            $formatText = 'Đôi';
                        } elseif ($tournament->competition_format === 'mixed') {
                            $formatText = 'Đôi nam nữ';
                        } else {
                            $formatText = 'Không xác định';
                        }
                    @endphp
                    
                    <div class="tournament-card fade-in" 
                         data-status="{{ $statusText }}" 
                         data-format="{{ $formatText }}"
                         data-location="{{ $tournament->location ?? 'N/A' }}"
                         data-name="{{ $tournament->name }}"
                         data-date="{{ strtotime($tournament->start_date) }}">
                         <div class="tournament-header">
                             <span class="tournament-status">
                                 <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                             </span>
                             <h3 class="tournament-title">{{ $tournament->name }}</h3>
                             <div class="tournament-date">📅 {{ date('d/m/Y', strtotime($tournament->start_date)) }}@if($tournament->end_date) - {{ date('d/m/Y', strtotime($tournament->end_date)) }}@endif</div>
                         </div>
                        <div class="tournament-body">
                            <div class="tournament-meta">
                                <div class="meta-item">
                                    <div class="meta-label">Vận động viên</div>
                                    <div class="meta-value">{{ $athleteCount }}/{{ $maxParticipants }} VĐV</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">Địa điểm</div>
                                    <div class="meta-value">{{ $tournament->location ?? 'N/A' }}</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">Loại giải</div>
                                    <div class="meta-value">{{ $formatText }}</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">Giải thưởng</div>
                                    <div class="meta-value">{{ $tournament->prizes ? number_format($tournament->prizes, 0, ',', '.') . ' ₫' : 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="tournament-progress">
                                <div class="progress-label">
                                    <span>Đăng ký</span>
                                    <span>{{ $athleteCount }}/{{ $maxParticipants }} ({{ $progress }}%)</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $progress }}%;"></div>
                                </div>
                            </div>
                            <div class="tournament-footer">
                                <button class="btn btn-primary btn-sm" style="flex: 1;" onclick="openTournamentModal({{ $tournament->id }})">
                                    👁️ Chi tiết
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="openEditModal({{ $tournament->id }})">
                                    ✏️
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="window.location.href='{{ route('homeyard.dashboard', $tournament->id) }}'">
                                   ⚙️
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1;">
                        <div class="empty-state">
                            <div class="empty-icon">📋</div>
                            <div class="empty-title">Không có giải đấu nào</div>
                            <div class="empty-description">Bạn chưa tạo giải đấu nào. Hãy tạo giải đấu đầu tiên của bạn.</div>
                            <button class="btn btn-primary" onclick="openCreateModal()">Tạo Giải Đấu Mới</button>
                        </div>
                    </div>
                @endforelse
                

            </div>

            <!-- Pagination -->
            <div class="pagination">
                @if($tournaments->onFirstPage())
                    <button class="pagination-btn" disabled>←</button>
                @else
                    <a href="{{ $tournaments->previousPageUrl() }}" class="pagination-btn">←</a>
                @endif
                
                @foreach($tournaments->getUrlRange(1, $tournaments->lastPage()) as $page => $url)
                    @if($page == $tournaments->currentPage())
                        <button class="pagination-btn active" disabled>{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                    @endif
                @endforeach
                
                @if($tournaments->hasMorePages())
                    <a href="{{ $tournaments->nextPageUrl() }}" class="pagination-btn">→</a>
                @else
                    <button class="pagination-btn" disabled>→</button>
                @endif
            </div>
        </div>
    </main>
    
    <!-- Tournament Detail Modal -->
    <div class="modal" id="detailModal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border-bottom: none;">
                <h3 class="modal-title" style="color: white; margin: 0;">Chi Tiết Giải Đấu</h3>
                <button type="button" class="modal-close" style="color: white;" onclick="closeDetailModal()">×</button>
            </div>
            <div class="modal-body" id="detailModalBody" style="max-height: 70vh; overflow-y: auto;">
                <div style="text-align: center; padding: 2rem;">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">⏳</div>
                    <p>Đang tải dữ liệu...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDetailModal()">Đóng</button>
                <button type="button" class="btn btn-primary" id="editTournamentBtn">✏️ Chỉnh sửa</button>
            </div>
        </div>
    </div>
    
    <!-- Tournament Edit Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content" style="max-width: 700px;">
            <form id="editTournamentForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border-bottom: none;">
                    <h3 class="modal-title" style="color: white; margin: 0;">Chỉnh Sửa Giải Đấu</h3>
                    <button type="button" class="modal-close" style="color: white;" onclick="closeEditModal()">×</button>
                </div>
                <div class="modal-body" id="editModalBody" style="max-height: 70vh; overflow-y: auto;">
                    <div style="text-align: center; padding: 2rem;">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">⏳</div>
                        <p>Đang tải biểu mẫu...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">💾 Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>
    
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

        // Store all tournaments data
         let allTournaments = [];

         // Initialize filter system
         function initializeFilters() {
             // Get all tournament cards with data attributes
             const cards = document.querySelectorAll('.tournament-card');
             
             cards.forEach(card => {
                 const name = card.getAttribute('data-name') || '';
                 const status = card.getAttribute('data-status') || '';
                 const format = card.getAttribute('data-format') || '';
                 const location = card.getAttribute('data-location') || '';
                 const dateStr = card.getAttribute('data-date') || '';
                 const element = card;
                 
                 allTournaments.push({ element, name, status, format, location, dateStr });
             });

             // Add event listeners to filters
             document.getElementById('statusFilter')?.addEventListener('change', applyFilters);
             document.getElementById('typeFilter')?.addEventListener('change', applyFilters);
             document.getElementById('locationFilter')?.addEventListener('change', applyFilters);
             document.getElementById('sortFilter')?.addEventListener('change', applyFilters);
             document.getElementById('searchInput')?.addEventListener('input', applyFilters);
         }

         // Apply all filters
         function applyFilters() {
             const statusFilter = document.getElementById('statusFilter')?.value || '';
             const typeFilter = document.getElementById('typeFilter')?.value || '';
             const locationFilter = document.getElementById('locationFilter')?.value || '';
             const sortBy = document.getElementById('sortFilter')?.value || 'newest';
             const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';

             let filtered = allTournaments.filter(tournament => {
                 // Status filter
                 if (statusFilter) {
                     const statusMap = {
                         'ongoing': 'Đang diễn ra',
                         'upcoming': 'Sắp tới',
                         'completed': 'Đã kết thúc',
                         'cancelled': 'Đã hủy'
                     };
                     const expectedStatus = statusMap[statusFilter] || statusFilter;
                     if (tournament.status !== expectedStatus) {
                         return false;
                     }
                 }

                 // Type filter (competition format)
                 if (typeFilter) {
                     const formatMap = {
                         'single-men': 'Đơn nam',
                         'single-women': 'Đơn nữ',
                         'double-men': 'Đôi nam',
                         'double-women': 'Đôi nữ',
                         'double-mixed': 'Đôi nam nữ',
                         'single': 'Đơn',
                         'double': 'Đôi',
                         'mixed': 'Đôi nam nữ'
                     };
                     const expectedFormat = formatMap[typeFilter] || typeFilter;
                     if (tournament.format !== expectedFormat) {
                         return false;
                     }
                 }

                 // Location filter
                 if (locationFilter) {
                     const locationMap = {
                         'hcm': 'TP. Hồ Chí Minh',
                         'hn': 'Hà Nội',
                         'dn': 'Đà Nẵng'
                     };
                     const expectedLocation = locationMap[locationFilter];
                     if (locationFilter === 'other') {
                         // 'other' means any location that is not in the predefined list
                         if (Object.values(locationMap).includes(tournament.location)) {
                             return false;
                         }
                     } else if (tournament.location !== expectedLocation) {
                         return false;
                     }
                 }

                 // Search filter
                 if (searchTerm && !tournament.name.toLowerCase().includes(searchTerm)) {
                     return false;
                 }

                 return true;
             });

             // Apply sorting
             filtered = sortTournaments(filtered, sortBy);

             // Update display
             updateTournamentDisplay(filtered);
         }

         // Sort tournaments
         function sortTournaments(tournaments, sortBy) {
             const sorted = [...tournaments];
             
             switch(sortBy) {
                 case 'newest':
                     // Mới nhất - sắp xếp theo date giảm dần
                     sorted.sort((a, b) => parseInt(b.dateStr) - parseInt(a.dateStr));
                     break;
                 case 'oldest':
                     // Cũ nhất - sắp xếp theo date tăng dần
                     sorted.sort((a, b) => parseInt(a.dateStr) - parseInt(b.dateStr));
                     break;
                 case 'name-asc':
                     sorted.sort((a, b) => a.name.localeCompare(b.name, 'vi'));
                     break;
                 case 'name-desc':
                     sorted.sort((a, b) => b.name.localeCompare(a.name, 'vi'));
                     break;
                 case 'date-asc':
                     // Sắp xếp theo ngày bắt đầu tăng dần
                     sorted.sort((a, b) => parseInt(a.dateStr) - parseInt(b.dateStr));
                     break;
                 case 'date-desc':
                     // Sắp xếp theo ngày bắt đầu giảm dần
                     sorted.sort((a, b) => parseInt(b.dateStr) - parseInt(a.dateStr));
                     break;
             }
             
             return sorted;
         }

         // Update tournament display
         function updateTournamentDisplay(filtered) {
             const grid = document.getElementById('tournamentGrid');
             
             // Clear grid
             grid.innerHTML = '';

             if (filtered.length === 0) {
                 grid.innerHTML = `
                     <div style="grid-column: 1 / -1;">
                         <div class="empty-state">
                             <div class="empty-icon">📋</div>
                             <div class="empty-title">Không tìm thấy giải đấu nào</div>
                             <div class="empty-description">Hãy thử thay đổi các bộ lọc của bạn</div>
                         </div>
                     </div>
                 `;
                 return;
             }

             // Re-add filtered elements with animation
             filtered.forEach(tournament => {
                 tournament.element.style.opacity = '0';
                 tournament.element.style.transform = 'translateY(20px)';
                 grid.appendChild(tournament.element);
                 
                 // Trigger animation
                 setTimeout(() => {
                     tournament.element.style.transition = 'all 0.3s ease-out';
                     tournament.element.style.opacity = '1';
                     tournament.element.style.transform = 'translateY(0)';
                 }, 10);
             });
         }

         // Filter by status from tabs
         function filterByStatus(status) {
             const tabs = document.querySelectorAll('.view-tab');
             tabs.forEach(tab => tab.classList.remove('active'));
             event.target.classList.add('active');

             document.getElementById('statusFilter').value = status === 'all' ? '' : status;
             applyFilters();
         }

         // Reset filters
         function resetFilters() {
             document.getElementById('statusFilter').value = '';
             document.getElementById('typeFilter').value = '';
             document.getElementById('locationFilter').value = '';
             document.getElementById('sortFilter').value = 'newest';
             document.getElementById('searchInput').value = '';
             
             const tabs = document.querySelectorAll('.view-tab');
             tabs.forEach(tab => tab.classList.remove('active'));
             tabs[0].classList.add('active');
             
             applyFilters();
         }

         // Export to Excel
         function exportToExcel() {
             // Gửi request tới server để export Excel (.xlsx)
             window.location.href = '{{ route("homeyard.tournaments.export") }}';
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
         document.getElementById('tournamentForm')?.setAttribute('action', '{{ route("homeyard.tournaments.store") }}')

        // Initialize
        initializeFilters();
        
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
             const createModal = document.getElementById('createModal');
             const detailModal = document.getElementById('detailModal');
             const editModal = document.getElementById('editModal');
             if (e.target === createModal) {
                 closeCreateModal();
             }
             if (e.target === detailModal) {
                 closeDetailModal();
             }
             if (e.target === editModal) {
                 closeEditModal();
             }
         });

         // Tournament Detail Modal Functions
         function openTournamentModal(tournamentId) {
             const modal = document.getElementById('detailModal');
             const body = document.getElementById('detailModalBody');
             
             // Fetch tournament details via AJAX
             fetch(`/homeyard/tournaments/${tournamentId}`, {
                 headers: {
                     'Accept': 'application/json',
                     'X-Requested-With': 'XMLHttpRequest'
                 }
             })
             .then(response => response.json())
             .then(data => {
                 displayTournamentDetails(data);
                 modal.classList.add('show');
             })
             .catch(error => {
                 console.error('Error:', error);
                 body.innerHTML = `
                     <div style="text-align: center; padding: 2rem; color: var(--text-danger);">
                         <p>Không thể tải chi tiết giải đấu</p>
                     </div>
                 `;
                 modal.classList.add('show');
             });
         }

         function displayTournamentDetails(tournament) {
             const body = document.getElementById('detailModalBody');
             const formatMap = {
                 'single': 'Đơn',
                 'double': 'Đôi',
                 'mixed': 'Đôi nam nữ'
             };
             
             const bannerImg = tournament.banner ? `<img src="${tournament.banner}" style="width: 100%; height: 250px; object-fit: cover; border-radius: 8px; margin-bottom: 1.5rem;">` : '';
             const imageSection = tournament.image ? `
                 <div style="margin-bottom: 1.5rem;">
                     <h4 style="margin: 0 0 0.75rem 0; color: var(--text-primary); font-weight: 700; font-size: 0.95rem;">Hình ảnh giải đấu</h4>
                     <img src="${tournament.image}" style="width: 100%; max-height: 300px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);">
                 </div>
             ` : '';
             
             const html = `
                 ${bannerImg}
                 <div style="padding: 0 0.5rem;">
                     <div style="margin-bottom: 1.5rem;">
                         <h3 style="margin: 0 0 1rem 0; color: var(--text-primary); font-size: 1.5rem; font-weight: 700;">${tournament.name}</h3>
                         <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                             <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                                 <div style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">Loại giải</div>
                                 <div style="color: var(--text-primary); font-weight: 600; font-size: 1rem;">${formatMap[tournament.competition_format] || 'Không xác định'}</div>
                             </div>
                             <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                                 <div style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">Số VĐV tối đa</div>
                                 <div style="color: var(--text-primary); font-weight: 600; font-size: 1rem;">${tournament.max_participants || 0} người</div>
                             </div>
                         </div>
                     </div>

                     <div style="margin-bottom: 1.5rem;">
                         <h4 style="margin: 0 0 0.75rem 0; color: var(--text-primary); font-weight: 700; font-size: 0.95rem;">📅 Lịch trình</h4>
                         <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                             <div style="padding: 0.75rem; background: #f0f4ff; border-left: 4px solid var(--primary-color); border-radius: 4px;">
                                 <div style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem;">Ngày bắt đầu</div>
                                 <div style="color: var(--text-primary); font-weight: 600;">${new Date(tournament.start_date).toLocaleDateString('vi-VN')}</div>
                             </div>
                             <div style="padding: 0.75rem; background: #f0f4ff; border-left: 4px solid var(--primary-color); border-radius: 4px;">
                                 <div style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem;">Ngày kết thúc</div>
                                 <div style="color: var(--text-primary); font-weight: 600;">${tournament.end_date ? new Date(tournament.end_date).toLocaleDateString('vi-VN') : 'Chưa xác định'}</div>
                             </div>
                         </div>
                     </div>

                     <div style="margin-bottom: 1.5rem;">
                         <h4 style="margin: 0 0 0.75rem 0; color: var(--text-primary); font-weight: 700; font-size: 0.95rem;">📍 Địa điểm & Giải thưởng</h4>
                         <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                             <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                                 <div style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">Địa điểm</div>
                                 <div style="color: var(--text-primary); font-weight: 600;">${tournament.location || 'N/A'}</div>
                             </div>
                             <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                                 <div style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">Giải thưởng</div>
                                 <div style="color: #10b981; font-weight: 700;">${tournament.prizes ? new Intl.NumberFormat('vi-VN', {style: 'currency', currency: 'VND'}).format(tournament.prizes) : 'N/A'}</div>
                             </div>
                             <div style="padding: 0.75rem; background: var(--bg-light); border-radius: 6px;">
                                 <div style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.5px;">Lệ phí đăng ký</div>
                                 <div style="color: var(--text-primary); font-weight: 600;">${tournament.price ? new Intl.NumberFormat('vi-VN', {style: 'currency', currency: 'VND'}).format(tournament.price) : 'Miễn phí'}</div>
                             </div>
                         </div>
                     </div>

                     ${imageSection}

                     <div style="margin-bottom: 1.5rem;">
                         <h4 style="margin: 0 0 0.75rem 0; color: var(--text-primary); font-weight: 700; font-size: 0.95rem;">📝 Mô tả</h4>
                         <div style="padding: 1rem; background: var(--bg-light); border-radius: 6px; color: var(--text-primary); line-height: 1.6; white-space: pre-wrap;">${tournament.description || 'Chưa có mô tả'}</div>
                     </div>

                     <div style="margin-bottom: 1.5rem;">
                         <h4 style="margin: 0 0 0.75rem 0; color: var(--text-primary); font-weight: 700; font-size: 0.95rem;">⚽ Quy định & Quyền lợi</h4>
                         <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                             <div style="padding: 0.75rem; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                                 <div style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Quy định thi đấu</div>
                                 <div style="color: var(--text-primary); line-height: 1.5; white-space: pre-wrap;">${tournament.competition_rules || 'Chưa có quy định'}</div>
                             </div>
                             ${tournament.registration_benefits ? `
                             <div style="padding: 0.75rem; background: #d4edda; border-left: 4px solid #10b981; border-radius: 4px;">
                                 <div style="color: var(--text-light); font-size: 0.75rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Quyền lợi khi tham gia</div>
                                 <div style="color: var(--text-primary); line-height: 1.5; white-space: pre-wrap;">${tournament.registration_benefits}</div>
                             </div>
                             ` : ''}
                         </div>
                     </div>
                 </div>
             `;
             
             document.getElementById('detailModalBody').innerHTML = html;
             
             // Update edit button to open edit modal
             document.getElementById('editTournamentBtn').onclick = function() {
                 closeDetailModal();
                 openEditModal(tournament.id);
             };
             }

         function closeDetailModal() {
             const modal = document.getElementById('detailModal');
             modal.classList.remove('show');
         }

         // Tournament Edit Modal Functions
         function openEditModal(tournamentId) {
             const modal = document.getElementById('editModal');
             const body = document.getElementById('editModalBody');
             
             // Fetch tournament data for editing
             fetch(`/homeyard/tournaments/${tournamentId}`, {
                 headers: {
                     'Accept': 'application/json',
                     'X-Requested-With': 'XMLHttpRequest'
                 }
             })
             .then(response => response.json())
             .then(data => {
                 displayEditForm(data);
                 modal.classList.add('show');
             })
             .catch(error => {
                 console.error('Error:', error);
                 body.innerHTML = `
                     <div style="text-align: center; padding: 2rem; color: var(--text-danger);">
                         <p>Không thể tải biểu mẫu chỉnh sửa</p>
                     </div>
                 `;
                 modal.classList.add('show');
             });
         }

         function displayEditForm(tournament) {
             const body = document.getElementById('editModalBody');
             const form = document.getElementById('editTournamentForm');
             
             // Helper function to format date for input type="date"
             const formatDateForInput = (dateStr) => {
                 if (!dateStr) return '';
                 const date = new Date(dateStr);
                 const year = date.getFullYear();
                 const month = String(date.getMonth() + 1).padStart(2, '0');
                 const day = String(date.getDate()).padStart(2, '0');
                 return `${year}-${month}-${day}`;
             };

             const html = `
                 <div style="padding: 0;">
                     <div class="form-group">
                         <label class="form-label">Tên giải đấu *</label>
                         <input type="text" class="form-input" id="editName" name="name" value="${tournament.name}" required>
                     </div>
                     <div class="grid grid-2">
                         <div class="form-group">
                             <label class="form-label">Ngày bắt đầu *</label>
                             <input type="date" class="form-input" id="editStartDate" name="start_date" value="${formatDateForInput(tournament.start_date)}" required>
                         </div>
                         <div class="form-group">
                             <label class="form-label">Ngày kết thúc</label>
                             <input type="date" class="form-input" id="editEndDate" name="end_date" value="${formatDateForInput(tournament.end_date)}">
                         </div>
                     </div>
                     <div class="form-group">
                         <label class="form-label">Địa điểm *</label>
                         <input type="text" class="form-input" id="editLocation" name="location" value="${tournament.location || ''}" placeholder="VD: Sân Pickleball Thảo Điền">
                     </div>
                     <div class="grid grid-2">
                         <div class="form-group">
                             <label class="form-label">Loại giải *</label>
                             <select class="form-select" id="editFormat" name="competition_format">
                                 <option value="">Chọn loại giải</option>
                                 <option value="single" ${tournament.competition_format === 'single' ? 'selected' : ''}>Đơn</option>
                                 <option value="double" ${tournament.competition_format === 'double' ? 'selected' : ''}>Đôi</option>
                                 <option value="mixed" ${tournament.competition_format === 'mixed' ? 'selected' : ''}>Đôi nam nữ</option>
                             </select>
                         </div>
                         <div class="form-group">
                             <label class="form-label">Số VĐV tối đa</label>
                             <input type="number" class="form-input" id="editMaxParticipants" name="max_participants" value="${tournament.max_participants || ''}" placeholder="64">
                         </div>
                     </div>
                     <div class="grid grid-2">
                         <div class="form-group">
                             <label class="form-label">Lệ phí giải đấu (VNĐ)</label>
                             <input type="number" class="form-input" id="editPrice" name="price" value="${tournament.price || ''}" placeholder="500000" step="0.01" min="0" max="99999999">
                         </div>
                         <div class="form-group">
                             <label class="form-label">Giải thưởng (VNĐ)</label>
                             <input type="number" class="form-input" id="editPrizes" name="prizes" value="${tournament.prizes || ''}" placeholder="50000000" step="0.01" min="0" max="99999999">
                         </div>
                     </div>
                     <div class="form-group">
                         <label class="form-label">Ảnh giải đấu</label>
                         <input type="file" class="form-input" id="editImageInput" name="image" accept="image/*">
                         ${tournament.image ? `
                             <div style="margin-top: 0.75rem;">
                                 <small style="color: var(--text-light);">Ảnh hiện tại:</small>
                                 <img src="${tournament.image}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-color); margin-top: 0.5rem;">
                             </div>
                         ` : ''}
                         <div id="editImagePreview" style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.75rem;"></div>
                     </div>
                     <div class="form-group">
                         <label class="form-label">Banner giải đấu</label>
                         <input type="file" class="form-input" id="editBannerInput" name="banner" accept="image/*">
                         ${tournament.banner ? `
                             <div style="margin-top: 0.75rem;">
                                 <small style="color: var(--text-light);">Banner hiện tại:</small>
                                 <img src="${tournament.banner}" style="width: 150px; height: 80px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-color); margin-top: 0.5rem;">
                             </div>
                         ` : ''}
                         <div id="editBannerPreview" style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.75rem;"></div>
                     </div>
                     <div class="form-group">
                         <label class="form-label">Mô tả</label>
                         <textarea class="form-input" id="editDescription" name="description" placeholder="Nhập mô tả giải đấu..." rows="3">${tournament.description || ''}</textarea>
                     </div>
                     <div class="form-group">
                         <label class="form-label">Quy định</label>
                         <textarea class="form-input" id="editRules" name="competition_rules" placeholder="Nhập quy định của giải đấu..." rows="3">${tournament.competition_rules || ''}</textarea>
                     </div>
                     <div class="form-group">
                         <label class="form-label">Quyền lợi khi tham gia</label>
                         <textarea class="form-input" id="editBenefits" name="registration_benefits" placeholder="Nhập quyền lợi khi tham gia..." rows="3">${tournament.registration_benefits || ''}</textarea>
                     </div>
                 </div>
             `;

             body.innerHTML = html;
             
             // Image preview handler
             const editImageInput = document.getElementById('editImageInput');
             const editImagePreview = document.getElementById('editImagePreview');
             
             if (editImageInput) {
                 editImageInput.addEventListener('change', function() {
                     editImagePreview.innerHTML = '';
                     const file = this.files[0];
                     
                     if (file) {
                         const reader = new FileReader();
                         
                         reader.onload = function(e) {
                             const img = document.createElement('img');
                             img.src = e.target.result;
                             img.style.width = '100px';
                             img.style.height = '100px';
                             img.style.objectFit = 'cover';
                             img.style.borderRadius = '6px';
                             img.style.border = '2px solid var(--border-color)';
                             editImagePreview.appendChild(img);
                         };
                         
                         reader.readAsDataURL(file);
                     }
                 });
             }
             
             // Banner preview handler
             const editBannerInput = document.getElementById('editBannerInput');
             const editBannerPreview = document.getElementById('editBannerPreview');
             
             if (editBannerInput) {
                 editBannerInput.addEventListener('change', function() {
                     editBannerPreview.innerHTML = '';
                     const file = this.files[0];
                     
                     if (file) {
                         const reader = new FileReader();
                         
                         reader.onload = function(e) {
                             const img = document.createElement('img');
                             img.src = e.target.result;
                             img.style.width = '150px';
                             img.style.height = '80px';
                             img.style.objectFit = 'cover';
                             img.style.borderRadius = '6px';
                             img.style.border = '2px solid var(--border-color)';
                             editBannerPreview.appendChild(img);
                         };
                         
                         reader.readAsDataURL(file);
                     }
                 });
             }
             
             // Update form action
             form.setAttribute('action', `/homeyard/tournaments/${tournament.id}`);
             
             // Handle form submission
             form.onsubmit = function(e) {
                 e.preventDefault();
                 const formData = new FormData(form);
                 
                 fetch(form.action, {
                     method: 'POST',
                     headers: {
                         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                         'X-Requested-With': 'XMLHttpRequest',
                     },
                     body: formData
                 })
                 .then(response => {
                     if (response.ok) {
                         showToast('Giải đấu đã được cập nhật thành công', 'success');
                         closeEditModal();
                         setTimeout(() => location.reload(), 1500);
                     } else {
                         showToast('Lỗi khi cập nhật giải đấu', 'error');
                     }
                 })
                 .catch(error => {
                     console.error('Error:', error);
                     showToast('Có lỗi xảy ra, vui lòng thử lại', 'error');
                 });
             };
         }

         function closeEditModal() {
             const modal = document.getElementById('editModal');
             modal.classList.remove('show');
         }

         console.log('Tournaments Page Loaded');
        </script>
        @endsection
