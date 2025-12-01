@extends('layouts.homeyard')
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
        border-radius: var(--radius-lg);
        max-width: 600px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: var(--shadow-xl);
    }

    .modal-header {
        padding: 20px 1.5rem;
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
        padding: 1rem 1.5rem;
        border-top: 2px solid var(--border-color);
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    @media (max-width: 768px) {
        .top-header {
            margin-top: 100px;
        }
    }
    @media (max-width: 480px) {
        .view-tabs {
            overflow-x: auto;
        }
        .view-tab {
            white-space: nowrap; 
        }
    }
</style>
@section('content')
    <main class="main-content" id="mainContent">
        <div class="container">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <h1>Quản Lý Giải Đấu</h1>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">
                            <a href="{{route('homeyard.overview')}}" class="breadcrumb-link">🏠 Dashboard</a>
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
                    <div class="header-user">
                        <div class="user-avatar">{{ auth()->user()->getInitials() }}</div>
                        <div class="user-info">
                            <div class="user-name">{{auth()->user()->name}}</div>
                            <div class="user-role">{{auth()->user()->getFirstRoleName()}}</div>
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
            <div class="bulk-actions" id="bulkActions">
                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                <div class="bulk-info">
                    <span id="selectedCount">0</span> giải đấu được chọn
                </div>
                <button class="btn btn-danger btn-sm" onclick="deleteBulkTournaments()">
                    🗑️ Xóa
                </button>
            </div>

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
                @forelse($tournaments as $item)
                    @php
                        $athleteCount = $item->athletes()->count();
                        $maxParticipants = $item->max_participants ?? 64;
                        $progress = $maxParticipants > 0 ? round(($athleteCount / $maxParticipants) * 100) : 0;

                        $now = now();
                        $startDate = strtotime($item->start_date);
                        $endDate = $item->end_date ? strtotime($item->end_date) : null;
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
                        if ($item->competition_format === 'single') {
                            $formatText = 'Đơn';
                        } elseif ($item->competition_format === 'double') {
                            $formatText = 'Đôi';
                        } elseif ($item->competition_format === 'mixed') {
                            $formatText = 'Đôi nam nữ';
                        } else {
                            $formatText = 'Không xác định';
                        }
                    @endphp

                    <div class="tournament-card fade-in" data-tournament-id="{{ $item->id }}" data-status="{{ $statusText }}" data-format="{{ $formatText }}"
                        data-location="{{ $item->location ?? 'N/A' }}" data-name="{{ $item->name }}"
                        data-date="{{ strtotime($item->start_date) }}">
                        <input type="checkbox" class="tournament-checkbox" onchange="updateBulkActions()">
                        <div class="tournament-header">
                            <span class="tournament-status">
                                <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                            </span>
                            <h3 class="tournament-title">{{ $item->name }}</h3>
                            <div class="tournament-date">📅 {{ date('d/m/Y', strtotime($item->start_date)) }}
                                @if ($item->end_date)
                                    - {{ date('d/m/Y', strtotime($item->end_date)) }}
                                @endif
                            </div>
                        </div>
                        <div class="tournament-body">
                            <div class="tournament-meta">
                                <div class="meta-item">
                                    <div class="meta-label">Vận động viên</div>
                                    <div class="meta-value">{{ $athleteCount }}/{{ $maxParticipants }} VĐV</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">Địa điểm</div>
                                    <div class="meta-value">{{ $item->location ?? 'N/A' }}</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">Loại giải</div>
                                    <div class="meta-value">{{ $formatText }}</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">Giải thưởng</div>
                                    <div class="meta-value">
                                        {{ $item->prizes ? number_format($item->prizes, 0, ',', '.') . ' ₫' : 'N/A' }}
                                    </div>
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
                                <button class="btn btn-primary btn-sm" style="flex: 1;"
                                    onclick="openTournamentModal({{ $item->id }})">
                                    👁️ Chi tiết
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="openEditModal({{ $item->id }})">
                                    ✏️
                                </button>
                                <button class="btn btn-secondary btn-sm"
                                    onclick="window.location.href='{{ route('homeyard.dashboard', $item->id) }}'">
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
                            <div class="empty-description">Bạn chưa tạo giải đấu nào. Hãy tạo giải đấu đầu tiên của bạn.
                            </div>
                            <button class="btn btn-primary" onclick="openCreateModal()">Tạo Giải Đấu Mới</button>
                        </div>
                    </div>
                @endforelse


            </div>

            <!-- Pagination -->
            <div class="pagination">
                @if ($tournaments->onFirstPage())
                    <button class="pagination-btn" disabled>←</button>
                @else
                    <a href="{{ $tournaments->previousPageUrl() }}" class="pagination-btn">←</a>
                @endif

                @foreach ($tournaments->getUrlRange(1, $tournaments->lastPage()) as $page => $url)
                    @if ($page == $tournaments->currentPage())
                        <button class="pagination-btn active" disabled>{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($tournaments->hasMorePages())
                    <a href="{{ $tournaments->nextPageUrl() }}" class="pagination-btn">→</a>
                @else
                    <button class="pagination-btn" disabled>→</button>
                @endif
            </div>
        </div>
    </main>

    <!-- Tournament Detail Modal -->
    <div class="modal" id="detailModal"></div>

    <!-- Tournament Edit Modal -->
    <div class="modal" id="editModal"></div>

    <div class="modal" id="createModal">
        <div class="modal-content">
            <form id="tournamentForm" method="POST" enctype="multipart/form-data"
                action="{{ route('homeyard.tournaments.store') }}">

                @csrf
                <div class="modal-header"
                    style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border-bottom: none;">
                    <h3 class="modal-title">Tạo Giải Đấu Mới</h3>
                    <button type="button" class="modal-close" style="color: white;" onclick="closeCreateModal()">×</button>
                </div>
                <div class="modal-body" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <div class="form-group">
                        <label class="form-label">Tên giải đấu *</label>
                        <input type="text" class="form-input" name="name"
                            placeholder="VD: Giải Pickleball Mở Rộng" required>
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
                        <input type="text" class="form-input" name="location"
                            placeholder="VD: Sân Pickleball Thảo Điền">
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
                            <input type="number" class="form-input" name="price" placeholder="500000">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Giải thưởng (VNĐ)</label>
                            <input type="number" class="form-input" name="prizes" placeholder="50000000">
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
                        <textarea class="form-input" name="registration_benefits" placeholder="Nhập quyền lợi khi tham gia..."
                            rows="3"></textarea>
                    </div>

                    @php
                        $tournament = new \App\Models\Tournament();
                    @endphp
                    <div class="form-group">
                        <label class="form-label">Banner</label>
                        @include('components.media-uploader', [
                            'model' => $tournament,
                            'collection' => 'banner',
                            'name' => 'banner',
                            'rules' => 'JPG, JPEG, SVG, PNG, WebP',
                            'maxItems' => 1,
                        ])
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hình ảnh</label>
                        @include('components.media-uploader', [
                            'model' => $tournament,
                            'collection' => 'gallery',
                            'name' => 'gallery',
                            'rules' => 'JPG, JPEG, SVG, PNG, WebP',
                        ])
                    </div>
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

                allTournaments.push({
                    element,
                    name,
                    status,
                    format,
                    location,
                    dateStr
                });
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

            switch (sortBy) {
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
            window.location.href = '{{ route('homeyard.tournaments.export') }}';
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

         function deleteBulkTournaments() {
             const checkboxes = document.querySelectorAll('.tournament-checkbox:checked');
             if (checkboxes.length === 0) {
                 alert('Vui lòng chọn ít nhất một giải đấu để xóa');
                 return;
             }

             const tournamentIds = [];
             checkboxes.forEach(checkbox => {
                 const card = checkbox.closest('.tournament-card');
                 if (card) {
                     const tournamentId = card.getAttribute('data-tournament-id');
                     if (tournamentId) {
                         tournamentIds.push(tournamentId);
                     }
                 }
             });

             if (tournamentIds.length === 0) {
                 alert('Không thể xác định ID của giải đấu');
                 return;
             }

             // Confirm deletion
             const message = `Bạn chắc chắn muốn xóa ${tournamentIds.length} giải đấu? Hành động này không thể được hoàn tác.`;
             if (!confirm(message)) {
                 return;
             }

             // Send delete request
             fetch('{{ route('homeyard.tournaments.bulk-delete') }}', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                     'Accept': 'application/json'
                 },
                 body: JSON.stringify({
                     ids: tournamentIds
                 })
             })
             .then(response => {
                 if (!response.ok) {
                     throw new Error(`HTTP error! status: ${response.status}`);
                 }
                 return response.json();
             })
             .then(data => {
                 if (data.success) {
                     alert('Xóa giải đấu thành công!');
                     // Reload page
                     window.location.reload();
                 } else {
                     alert(data.message || 'Đã xảy ra lỗi khi xóa giải đấu');
                 }
             })
             .catch(error => {
                 console.error('Error:', error);
                 alert('Đã xảy ra lỗi khi xóa giải đấu: ' + error.message);
             });
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
            }, 500);
        }


        // Initialize
        initializeFilters();

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

            // Fetch tournament details via AJAX
            fetch(`/homeyard/tournaments/${tournamentId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    modal.innerHTML = data.html;
                    modal.classList.add('show');
                });
        }

        function closeDetailModal() {
            const modal = document.getElementById('detailModal');
            modal.classList.remove('show');
        }

        // Tournament Edit Modal Functions
        function openEditModal(tournamentId) {
            const modal = document.getElementById('editModal');

            // Fetch tournament data for editing
            fetch(`/homeyard/tournaments/${tournamentId}/edit`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    modal.innerHTML = data.html;
                    modal.classList.add('show');
                });
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.remove('show');
        }
    </script>
@endsection
