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

    .court-card.in_use::before {
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

    .court-card.in_use .court-icon {
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
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .modal-content::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
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

    /* Custom Pagination Styling */
    .custom-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 2rem;
        margin-bottom: 2rem;
        list-style: none;
        padding: 0;
        flex-wrap: wrap;
    }

    .custom-pagination li {
        display: flex;
        align-items: center;
    }

    .custom-pagination .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 44px;
        height: 44px;
        padding: 0.5rem 0.75rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--bg-white);
        color: var(--text-primary);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all var(--transition);
    }

    .custom-pagination a.page-link:hover {
        border-color: var(--primary-color);
        background: var(--primary-color);
        color: white;
        transform: translateY(-2px);
    }

    .custom-pagination li.active .page-link {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        border-color: transparent;
        color: white;
        font-weight: 700;
    }

    .custom-pagination li.disabled .page-link {
        background: var(--bg-light);
        color: var(--text-light);
        border-color: var(--border-color);
        cursor: not-allowed;
    }

    .pricing-tier {
        background: var(--bg-light);
        padding: 1rem;
        border-radius: var(--radius-md);
        margin-bottom: 1rem;
        border: 2px solid var(--border-color);
    }

    .pricing-tier-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto;
        gap: 0.75rem;
        align-items: end;
    }

    .pricing-tier-remove {
        padding: 0.5rem 0.75rem;
        background: var(--accent-red);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        cursor: pointer;
        font-weight: 600;
        transition: all var(--transition);
    }

    .pricing-tier-remove:hover {
        background: #c53030;
        transform: scale(1.05);
    }

    @media (max-width: 768px) {
        .pricing-tier-grid {
            grid-template-columns: 1fr;
        }
    }

    .pricing-display {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .pricing-item {
        background: var(--bg-light);
        padding: 0.75rem;
        border-radius: var(--radius-md);
        border-left: 4px solid var(--primary-color);
    }

    .pricing-time {
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
    }

    .pricing-price {
        color: var(--primary-color);
        font-weight: 700;
        font-size: 1rem;
    }

    .pricing-label {
        font-size: 0.7rem;
        color: var(--text-light);
        margin-bottom: 0.25rem;
    }

    .no-pricing {
        padding: 1rem;
        background: var(--bg-light);
        border-radius: var(--radius-md);
        text-align: center;
        color: var(--text-light);
        font-size: 0.875rem;
    }

    @media (max-width: 768px) {
        .pricing-display {
            grid-template-columns: 1fr;
        }
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
                            <a href="{{route('homeyard.overview')}}" class="breadcrumb-link">Dashboard</a>
                        </span>
                        <span class="breadcrumb-separator">›</span>
                        <span class="breadcrumb-item">Quản Lý Sân</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <span class="search-icon">🔍</span>
                        <input type="text" class="search-input" id="courtSearch" placeholder="Tìm kiếm sân...">
                    </div>
                    <div class="header-notifications">
                        <button class="notification-btn">
                            🔔
                            <span class="notification-badge">5</span>
                        </button>
                    </div>
                    <div class="header-user">
                        <div class="user-avatar">{{ auth()->user()->getInitials() }}</div>
                        <div class="user-info">
                            <div class="user-name">{{auth()->user()->name}}</div>
                            <div class="user-role">{{auth()->user()->getFirstRoleName()}}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            @php
                $allCourts = collect();
                foreach ($stadiums as $stadium) {
                    $allCourts = $allCourts->concat($stadium->courts);
                }
                $totalCourts = $allCourts->count();
                $availableCourts = $allCourts->where('status', 'available')->count();
                $occupiedCourts = $allCourts->where('status', 'in_use')->count();
                $maintenanceCourts = $allCourts->where('status', 'maintenance')->count();
            @endphp
            <div class="court-stats-overview fade-in">
                <div class="stat-overview-box">
                    <div class="stat-overview-icon info">🏟️</div>
                    <div class="stat-overview-content">
                        <div class="stat-overview-value">{{ $totalCourts }}</div>
                        <div class="stat-overview-label">Tổng Số Sân</div>
                    </div>
                </div>
                <div class="stat-overview-box">
                    <div class="stat-overview-icon success">✅</div>
                    <div class="stat-overview-content">
                        <div class="stat-overview-value">{{ $availableCourts }}</div>
                        <div class="stat-overview-label">Sân Sẵn Sàng</div>
                    </div>
                </div>
                <div class="stat-overview-box">
                    <div class="stat-overview-icon danger">🔴</div>
                    <div class="stat-overview-content">
                        <div class="stat-overview-value">{{ $occupiedCourts }}</div>
                        <div class="stat-overview-label">Sân Đang Sử Dụng</div>
                    </div>
                </div>
                <div class="stat-overview-box">
                    <div class="stat-overview-icon warning">⚠️</div>
                    <div class="stat-overview-content">
                        <div class="stat-overview-value">{{ $maintenanceCourts }}</div>
                        <div class="stat-overview-label">Đang Bảo Trì</div>
                    </div>
                </div>
            </div>

            <!-- Filter Tabs & Court List -->
            <div class="card fade-in">
                <div class="card-header">
                    <h3 class="card-title">Danh Sách Sân</h3>
                    <div class="card-actions">
                        {{-- <button class="btn btn-secondary btn-sm">📊 Báo Cáo</button> --}}
                        <button class="btn btn-primary btn-sm" onclick="openAddCourtModal()">
                            ➕ Thêm Sân
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="filter-tabs">
                        <button class="filter-tab active" onclick="filterCourts('all')">
                            Tất Cả ({{ $totalCourts }})
                        </button>
                        <button class="filter-tab" onclick="filterCourts('available')">
                            ✅ Sẵn Sàng ({{ $availableCourts }})
                        </button>
                        <button class="filter-tab" onclick="filterCourts('in_use')">
                            🔴 Đang Sử Dụng ({{ $occupiedCourts }})
                        </button>
                        <button class="filter-tab" onclick="filterCourts('maintenance')">
                            ⚠️ Bảo Trì ({{ $maintenanceCourts }})
                        </button>
                    </div>

                    <!-- Court Grid -->
                    <div class="court-grid" id="courtGrid">
                        @forelse($courts as $court)
                            <div class="court-card {{ $court->status }}">
                                <div class="court-header">
                                    <div class="court-number">
                                        <div class="court-icon">{{ $court->court_number ?? $court->id }}</div>
                                        <div>
                                            <div class="court-title">{{ $court->court_name }}</div>
                                            <div class="court-subtitle">
                                                {{ ucfirst(str_replace('-', ' ', $court->court_type)) }} •
                                                {{ ucfirst(str_replace('-', ' ', $court->surface_type)) }}</div>
                                        </div>
                                    </div>
                                    @if ($court->status === 'available')
                                        <span class="court-status available">✅ Sẵn Sàng</span>
                                    @elseif($court->status === 'in_use')
                                        <span class="court-status occupied">🔴 Đang Thi Đấu</span>
                                    @elseif($court->status === 'maintenance')
                                        <span class="court-status maintenance">⚠️ Bảo Trì</span>
                                    @endif
                                </div>

                                <div class="court-info">
                                    <div class="court-info-item">
                                        <div class="court-info-label">Loại Sân</div>
                                        <div class="court-info-value">{{ ucfirst($court->court_type) }}</div>
                                    </div>
                                    <div class="court-info-item">
                                        <div class="court-info-label">Kích Thước</div>
                                        <div class="court-info-value">{{$court->size}}</div>
                                    </div>
                                    <div class="court-info-item">
                                        <div class="court-info-label">Mặt Sân</div>
                                        <div class="court-info-value">
                                            {{ ucfirst(str_replace('-', ' ', $court->surface_type)) }}</div>
                                    </div>
                                    <div class="court-info-item">
                                        <div class="court-info-label">Sức Chứa</div>
                                        <div class="court-info-value">{{$court->capacity}} người</div>
                                    </div>
                                </div>

                                <div class="court-schedule">
                                    <div class="schedule-title">Mô Tả</div>
                                    <div style="padding: 0.5rem 0; font-size: 0.75rem; color: var(--text-secondary);">
                                        {{ $court->description ?: 'Không có mô tả' }}
                                    </div>
                                </div>

                                <div class="court-actions">
                                    <button class="btn btn-primary btn-sm" style="flex: 1;"
                                        onclick="openCourtDetails({{ $court->id }})">
                                        👁️ Chi Tiết
                                    </button>
                                    <button class="btn btn-secondary btn-sm" onclick="openEditCourt({{ $court->id }})">
                                        ✏️
                                    </button>
                                    <button class="btn btn-ghost btn-sm">
                                        ⚙️
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem 1rem;">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">🏟️</div>
                                <h3 style="color: var(--text-primary); margin-bottom: 0.5rem;">Chưa có sân nào</h3>
                                <p style="color: var(--text-light); margin-bottom: 1.5rem;">Bắt đầu bằng cách thêm sân đầu
                                    tiên của bạn</p>
                                <button class="btn btn-primary" onclick="openAddCourtModal()">➕ Thêm Sân Đầu Tiên</button>
                            </div>
                        @endforelse
                        </div>

                        <!-- Pagination -->
                        @if ($courts->hasPages())
                            <div style="display: flex; justify-content: center; padding: 0 1rem;">
                                {{ $courts->links('vendor.pagination.custom') }}
                            </div>
                        @endif
                        </div>
                        </div>
                        </div>
                        </main>

    <!-- Add Court Modal -->
    <div class="modal" id="addCourtModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Thêm Sân Mới</h3>
                <button class="modal-close" onclick="closeAddCourtModal()">×</button>
            </div>
            <form id="addCourtForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Tên sân *</label>
                    <input type="text" class="form-input" name="court_name" placeholder="Ví dụ: Sân 7" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Số hiệu sân</label>
                    <input type="text" class="form-input" name="court_number" placeholder="Ví dụ: A1, B2">
                </div>
                <div class="form-group">
                    <label class="form-label">Loại sân *</label>
                    <select class="form-select" name="court_type" required>
                        <option value="">Chọn loại sân</option>
                        <option value="indoor">Indoor</option>
                        <option value="outdoor">Outdoor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Mặt sân *</label>
                    <select class="form-select" name="surface_type" required>
                        <option value="">Chọn loại mặt sân</option>
                        <option value="acrylic">Acrylic</option>
                        <option value="polyurethane">Polyurethane</option>
                        <option value="concrete">Concrete</option>
                        <option value="sport-court">Sport Court</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Chọn cụm sân *</label>
                    <select class="form-select" name="stadium_id" required>
                        <option value="">Chọn cụm sân</option>
                        @foreach ($stadiums as $stadium)
                            <option value="{{ $stadium->id }}">{{ $stadium->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Kích thước</label>
                    <input type="text" class="form-input" name="size" placeholder="13.4m x 6.1m">
                </div>
                <div class="form-group">
                    <label class="form-label">Sức chứa (người) *</label>
                    <input type="number" class="form-input" name="capacity" placeholder="Ví dụ: 80" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Giá thuê (giờ) *</label>
                    <input type="number" class="form-input" name="rental_price" placeholder="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Ghi chú</label>
                    <textarea class="form-textarea" name="description" placeholder="Thông tin thêm về sân..."></textarea>
                </div>
                
                <!-- Pricing Section -->
                <div class="form-group">
                    <label class="form-label">Giá Thuê Theo Thời Gian (Tùy Chọn)</label>
                    <div id="addPricingContainer" style="margin-top: 1rem;">
                        <!-- Pricing items will be added here dynamically -->
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addPricingTierToAdd()" style="margin-top: 1rem;">➕ Thêm Giá Theo Thời Gian</button>
                </div>
            </form>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeAddCourtModal()">Hủy</button>
                <button class="btn btn-primary" onclick="submitAddCourtForm()">💾 Lưu</button>
            </div>
        </div>
    </div>

    <!-- Edit Court Modal -->
    <div class="modal" id="editCourtModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Chỉnh Sửa Sân</h3>
                <button class="modal-close" onclick="closeEditCourtModal()">×</button>
            </div>
            <form id="editCourtForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Tên sân *</label>
                    <input type="text" class="form-input" name="court_name" placeholder="Ví dụ: Sân 7" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Số hiệu sân</label>
                    <input type="text" class="form-input" name="court_number" placeholder="Ví dụ: A1, B2">
                </div>
                <div class="form-group">
                    <label class="form-label">Loại sân *</label>
                    <select class="form-select" name="court_type" required>
                        <option value="">Chọn loại sân</option>
                        <option value="indoor">Indoor</option>
                        <option value="outdoor">Outdoor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Mặt sân *</label>
                    <select class="form-select" name="surface_type" required>
                        <option value="">Chọn loại mặt sân</option>
                        <option value="acrylic">Acrylic</option>
                        <option value="polyurethane">Polyurethane</option>
                        <option value="concrete">Concrete</option>
                        <option value="sport-court">Sport Court</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Chọn cụm sân *</label>
                    <select class="form-select" name="stadium_id" required>
                        <option value="">Chọn cụm sân</option>
                        @foreach ($stadiums as $stadium)
                            <option value="{{ $stadium->id }}">{{ $stadium->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Kích thước</label>
                    <input type="text" class="form-input" name="size" placeholder="13.4m x 6.1m">
                </div>
                <div class="form-group">
                    <label class="form-label">Sức chứa (người) *</label>
                    <input type="number" class="form-input" name="capacity" placeholder="Ví dụ: 80" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Giá thuê (giờ) *</label>
                    <input type="number" class="form-input" name="rental_price" placeholder="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Ghi chú</label>
                    <textarea class="form-textarea" name="description" placeholder="Thông tin chi tiết về sân..."></textarea>
                </div>
                
                <!-- Pricing Section -->
                <div class="form-group">
                    <label class="form-label">Giá Thuê Theo Thời Gian (Tùy Chọn)</label>
                    <div id="pricingContainer" style="margin-top: 1rem;">
                        <!-- Pricing items will be added here dynamically -->
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addPricingTier()" style="margin-top: 1rem;">➕ Thêm Giá Theo Thời Gian</button>
                </div>
            </form>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeEditCourtModal()">Hủy</button>
                <button class="btn btn-primary" onclick="submitEditCourtForm()">💾 Cập Nhật</button>
            </div>
        </div>
    </div>

    <!-- Court Details Modal -->
    <div class="modal" id="courtDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="detailsCourtTitle">Chi Tiết Sân</h3>
                <button class="modal-close" onclick="closeCourtDetailsModal()">×</button>
            </div>
            <div id="courtDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeCourtDetailsModal()">Đóng</button>
                <button class="btn btn-primary" onclick="openEditCurrentCourt()">✏️ Chỉnh Sửa</button>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        let pricingTiers = [];
        let currentCourtId = null;

        // Format currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        }

        // Get court type label
        function getCourtTypeLabel(type) {
            const types = {
                'indoor': 'Indoor',
                'outdoor': 'Outdoor'
            };
            return types[type] || type;
        }

        // Get surface type label
        function getSurfaceTypeLabel(type) {
            const surfaces = {
                'acrylic': 'Acrylic',
                'polyurethane': 'Polyurethane',
                'concrete': 'Concrete',
                'sport-court': 'Sport Court'
            };
            return surfaces[type] || type;
        }

        // Open court details modal
        function openCourtDetails(courtId) {
            currentCourtId = courtId;
            const detailsContainer = document.getElementById('courtDetailsContent');
            detailsContainer.innerHTML = '<p style="text-align: center; padding: 2rem;">Đang tải...</p>';

            // Fetch court data
            Promise.all([
                fetch(`/homeyard/courts/${courtId}/edit`).then(r => r.json()),
                fetch(`/homeyard/courts/${courtId}/pricing`).then(r => r.json())
            ])
            .then(([courtData, pricingData]) => {
                const court = courtData.court;
                const pricing = pricingData.pricing || [];

                // Update title
                document.getElementById('detailsCourtTitle').textContent = `Chi Tiết Sân - ${court.court_name}`;

                // Build HTML content
                let html = `
                    <div class="form-group">
                        <label class="form-label">Thông Tin Cơ Bản</label>
                        <div class="court-info">
                            <div class="court-info-item">
                                <div class="court-info-label">Tên sân</div>
                                <div class="court-info-value">${court.court_name}</div>
                            </div>
                            <div class="court-info-item">
                                <div class="court-info-label">Số hiệu</div>
                                <div class="court-info-value">${court.court_number || 'N/A'}</div>
                            </div>
                            <div class="court-info-item">
                                <div class="court-info-label">Loại sân</div>
                                <div class="court-info-value">${getCourtTypeLabel(court.court_type)}</div>
                            </div>
                            <div class="court-info-item">
                                <div class="court-info-label">Mặt sân</div>
                                <div class="court-info-value">${getSurfaceTypeLabel(court.surface_type)}</div>
                            </div>
                            <div class="court-info-item">
                                <div class="court-info-label">Kích thước</div>
                                <div class="court-info-value">${court.size || 'N/A'}</div>
                            </div>
                            <div class="court-info-item">
                                <div class="court-info-label">Sức chứa</div>
                                <div class="court-info-value">${court.capacity || 'N/A'} người</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Giá Thuê</label>
                        <div class="court-info">
                            <div class="court-info-item">
                                <div class="court-info-label">Giá cơ bản</div>
                                <div class="court-info-value">${formatCurrency(court.rental_price)}/giờ</div>
                            </div>
                        </div>
                    </div>
                `;

                // Add pricing tiers if available
                if (pricing && pricing.length > 0) {
                    html += `
                        <div class="form-group">
                            <label class="form-label">Giá Theo Thời Gian</label>
                            <div class="pricing-display">
                    `;
                    
                    pricing.forEach(tier => {
                        html += `
                            <div class="pricing-item">
                                <div class="pricing-label">Thời gian</div>
                                <div class="pricing-time">${tier.start_time} - ${tier.end_time}</div>
                                <div class="pricing-label" style="margin-top: 0.5rem;">Giá</div>
                                <div class="pricing-price">${formatCurrency(tier.price_per_hour)}</div>
                            </div>
                        `;
                    });

                    html += `
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="form-group">
                            <label class="form-label">Giá Theo Thời Gian</label>
                            <div class="no-pricing">Chưa có giá theo thời gian được thiết lập</div>
                        </div>
                    `;
                }

                // Add description if available
                if (court.description) {
                    html += `
                        <div class="form-group">
                            <label class="form-label">Ghi Chú</label>
                            <div style="background: var(--bg-light); padding: 0.75rem; border-radius: var(--radius-md); color: var(--text-primary); font-size: 0.875rem;">
                                ${court.description}
                            </div>
                        </div>
                    `;
                }

                detailsContainer.innerHTML = html;
                document.getElementById('courtDetailsModal').classList.add('active');
            })
            .catch(error => {
                console.error('Error loading court details:', error);
                detailsContainer.innerHTML = '<p style="text-align: center; padding: 2rem; color: var(--accent-red);">Lỗi khi tải dữ liệu sân</p>';
                document.getElementById('courtDetailsModal').classList.add('active');
            });
        }

        // Open edit for current court
        function openEditCurrentCourt() {
            if (currentCourtId) {
                closeCourtDetailsModal();
                openEditCourt(currentCourtId);
            }
        }

        // Add new pricing tier for Edit modal
        function addPricingTier() {
            const container = document.getElementById('pricingContainer');
            const tierId = `tier_${Date.now()}`;
            
            const tierHtml = `
                <div class="pricing-tier" id="${tierId}">
                    <div class="pricing-tier-grid">
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-size: 0.7rem;">Từ (giờ)</label>
                            <input type="time" class="form-input pricing-start-time" data-tier-id="${tierId}" placeholder="08:00" required>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-size: 0.7rem;">Đến (giờ)</label>
                            <input type="time" class="form-input pricing-end-time" data-tier-id="${tierId}" placeholder="17:00" required>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-size: 0.7rem;">Giá (VND/giờ)</label>
                            <input type="number" class="form-input pricing-price" data-tier-id="${tierId}" placeholder="0" min="1" required>
                        </div>
                        <button type="button" class="pricing-tier-remove" onclick="removePricingTier('${tierId}')">✕</button>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', tierHtml);
        }

        // Add new pricing tier for Add modal
        function addPricingTierToAdd() {
            const container = document.getElementById('addPricingContainer');
            const tierId = `add_tier_${Date.now()}`;
            
            const tierHtml = `
                <div class="pricing-tier" id="${tierId}">
                    <div class="pricing-tier-grid">
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-size: 0.7rem;">Từ (giờ)</label>
                            <input type="time" class="form-input pricing-start-time" data-tier-id="${tierId}" placeholder="08:00" required>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-size: 0.7rem;">Đến (giờ)</label>
                            <input type="time" class="form-input pricing-end-time" data-tier-id="${tierId}" placeholder="17:00" required>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" style="font-size: 0.7rem;">Giá (VND/giờ)</label>
                            <input type="number" class="form-input pricing-price" data-tier-id="${tierId}" placeholder="0" min="1" required>
                        </div>
                        <button type="button" class="pricing-tier-remove" onclick="removePricingTierFromAdd('${tierId}')">✕</button>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', tierHtml);
        }

        // Remove pricing tier from Edit modal
        function removePricingTier(tierId) {
            const element = document.getElementById(tierId);
            if (element) {
                element.remove();
            }
        }

        // Remove pricing tier from Add modal
        function removePricingTierFromAdd(tierId) {
            const element = document.getElementById(tierId);
            if (element) {
                element.remove();
            }
        }

        // Get pricing tiers data from form
        function getPricingTiersData() {
            const tiers = [];
            const tierElements = document.querySelectorAll('.pricing-tier');
            
            tierElements.forEach(element => {
                const startTime = element.querySelector('.pricing-start-time').value;
                const endTime = element.querySelector('.pricing-end-time').value;
                const price = element.querySelector('.pricing-price').value;
                
                if (startTime && endTime && price) {
                    tiers.push({
                        start_time: startTime,
                        end_time: endTime,
                        price_per_hour: parseInt(price)
                    });
                }
            });
            
            return tiers;
        }

        // Load pricing tiers for a court
        function loadPricingTiers(courtId) {
            fetch(`/homeyard/courts/${courtId}/pricing`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('pricingContainer');
                    container.innerHTML = '';
                    
                    if (data.pricing && data.pricing.length > 0) {
                        data.pricing.forEach(price => {
                            const tierId = `tier_${price.id}`;
                            const tierHtml = `
                                <div class="pricing-tier" id="${tierId}">
                                    <div class="pricing-tier-grid">
                                        <div class="form-group" style="margin: 0;">
                                            <label class="form-label" style="font-size: 0.7rem;">Từ (giờ)</label>
                                            <input type="time" class="form-input pricing-start-time" data-tier-id="${tierId}" data-pricing-id="${price.id}" value="${price.start_time}" required>
                                        </div>
                                        <div class="form-group" style="margin: 0;">
                                            <label class="form-label" style="font-size: 0.7rem;">Đến (giờ)</label>
                                            <input type="time" class="form-input pricing-end-time" data-tier-id="${tierId}" data-pricing-id="${price.id}" value="${price.end_time}" required>
                                        </div>
                                        <div class="form-group" style="margin: 0;">
                                            <label class="form-label" style="font-size: 0.7rem;">Giá (VND/giờ)</label>
                                            <input type="number" class="form-input pricing-price" data-tier-id="${tierId}" data-pricing-id="${price.id}" value="${price.price_per_hour}" min="1" required>
                                        </div>
                                        <button type="button" class="pricing-tier-remove" onclick="removePricingTier('${tierId}')">✕</button>
                                    </div>
                                </div>
                            `;
                            container.insertAdjacentHTML('beforeend', tierHtml);
                        });
                    }
                })
                .catch(error => console.error('Error loading pricing tiers:', error));
        }

        // Search courts by name
        const courtSearchInput = document.getElementById('courtSearch');
        if (courtSearchInput) {
            courtSearchInput.addEventListener('input', function(e) {
                const searchQuery = e.target.value.toLowerCase().trim();
                const courtCards = document.querySelectorAll('.court-card');
                let visibleCount = 0;

                courtCards.forEach(card => {
                    const courtName = card.querySelector('.court-title').textContent.toLowerCase();
                    if (courtName.includes(searchQuery)) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Show/hide empty state
                const grid = document.getElementById('courtGrid');
                if (visibleCount === 0 && searchQuery !== '') {
                    if (!document.getElementById('noSearchResults')) {
                        const emptyDiv = document.createElement('div');
                        emptyDiv.id = 'noSearchResults';
                        emptyDiv.style.cssText = 'grid-column: 1 / -1; text-align: center; padding: 3rem 1rem;';
                        emptyDiv.innerHTML = `
                            <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                            <h3 style="color: var(--text-primary); margin-bottom: 0.5rem;">Không tìm thấy sân</h3>
                            <p style="color: var(--text-light);">Không có sân nào phù hợp với từ khóa "${searchQuery}"</p>
                        `;
                        grid.appendChild(emptyDiv);
                    }
                } else {
                    const noResults = document.getElementById('noSearchResults');
                    if (noResults) noResults.remove();
                }
            });
        }

        // Filter courts by status
        function filterCourts(status) {
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');

            const courtCards = document.querySelectorAll('.court-card');
            courtCards.forEach(card => {
                if (status === 'all') {
                    card.style.display = 'block';
                } else {
                    if (card.classList.contains(status)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        }

        // Modal functions
        function openAddCourtModal() {
            document.getElementById('addCourtModal').classList.add('active');
        }

        function closeAddCourtModal() {
            const modal = document.getElementById('addCourtModal');
            const form = document.getElementById('addCourtForm');
            
            modal.classList.remove('active');
            
            // Reset to add mode
            modal.querySelector('.modal-title').textContent = 'Thêm Sân Mới';
            delete form.dataset.courtId;
            form.reset();
            
            // Clear pricing tiers
            const pricingContainer = document.getElementById('addPricingContainer');
            if (pricingContainer) {
                pricingContainer.innerHTML = '';
            }
        }

        function closeCourtDetailsModal() {
            document.getElementById('courtDetailsModal').classList.remove('active');
            currentCourtId = null;
        }

        function openEditCourt(courtId) {
             // Populate form with court data and open modal
             const modal = document.getElementById('editCourtModal');
             const form = document.getElementById('editCourtForm');
             
             // Set edit mode flag
             form.dataset.courtId = courtId;
             
             // Fetch court data
             fetch(`/homeyard/courts/${courtId}/edit`)
                 .then(response => response.json())
                 .then(data => {
                     // Populate form fields
                     form.querySelector('input[name="court_name"]').value = data.court.court_name || '';
                     form.querySelector('input[name="court_number"]').value = data.court.court_number || '';
                     form.querySelector('select[name="court_type"]').value = data.court.court_type || '';
                     form.querySelector('select[name="surface_type"]').value = data.court.surface_type || '';
                     form.querySelector('select[name="stadium_id"]').value = data.court.stadium_id || '';
                     form.querySelector('input[name="capacity"]').value = data.court.capacity || '';
                     form.querySelector('input[name="rental_price"]').value = data.court.rental_price || '';
                     form.querySelector('input[name="size"]').value = data.court.size || '';
                     form.querySelector('textarea[name="description"]').value = data.court.description || '';
                     
                     // Load pricing tiers
                     loadPricingTiers(courtId);
                     
                     // Open modal
                     modal.classList.add('active');
                 })
                 .catch(error => {
                     console.error('Error loading court:', error);
                     toastr.error('Không thể tải dữ liệu sân');
                 });
         }

        function closeEditCourtModal() {
            const modal = document.getElementById('editCourtModal');
            const form = document.getElementById('editCourtForm');
            
            modal.classList.remove('active');
            
            // Reset form
            delete form.dataset.courtId;
            form.reset();
        }

        // Submit Add Court Form
        function submitAddCourtForm() {
            const form = document.getElementById('addCourtForm');
            const formData = new FormData(form);

            // Get pricing tiers from add form
            const pricingTiers = [];
            const tierElements = document.querySelectorAll('#addPricingContainer .pricing-tier');
            
            tierElements.forEach(element => {
                const startTime = element.querySelector('.pricing-start-time').value;
                const endTime = element.querySelector('.pricing-end-time').value;
                const price = element.querySelector('.pricing-price').value;
                
                if (startTime && endTime && price) {
                    pricingTiers.push({
                        start_time: startTime,
                        end_time: endTime,
                        price_per_hour: parseInt(price)
                    });
                }
            });
            
            // Add pricing tiers to form data
            formData.append('pricing_tiers', JSON.stringify(pricingTiers));

            fetch('{{ route('homeyard.courts.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Response Error:', response.status, text);
                            throw new Error(`HTTP ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        closeAddCourtModal();
                        form.reset();
                        location.reload();
                    } else {
                        toastr.error(data.message);
                    }
                })
                .catch(error => {
                    toastr.error(error.message);
                });
        }

        // Submit Edit Court Form
         function submitEditCourtForm() {
             const form = document.getElementById('editCourtForm');
             const formData = new FormData(form);
             const courtId = form.dataset.courtId;

             // Add method to formData for Laravel to recognize PUT request
             formData.append('_method', 'PUT');
             
             // Add pricing tiers
             const pricingTiers = getPricingTiersData();
             formData.append('pricing_tiers', JSON.stringify(pricingTiers));

             fetch(`/homeyard/courts/${courtId}`, {
                     method: 'POST',
                     headers: {
                         'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                         'Accept': 'application/json',
                     },
                     body: formData
                 })
                 .then(response => {
                     if (!response.ok) {
                         return response.text().then(text => {
                             console.error('Response Error:', response.status, text);
                             throw new Error(`HTTP ${response.status}: ${text}`);
                         });
                     }
                     return response.json();
                 })
                 .then(data => {
                     if (data.success) {
                         toastr.success(data.message);
                         closeEditCourtModal();
                         form.reset();
                         delete form.dataset.courtId;
                         location.reload();
                     } else {
                         toastr.error(data.message);
                     }
                 })
                 .catch(error => {
                     toastr.error(error.message);
                 });
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
