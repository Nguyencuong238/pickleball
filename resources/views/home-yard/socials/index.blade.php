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

    .social-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .social-card {
        background: var(--bg-white);
        border-radius: var(--radius-xl);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        transition: all var(--transition);
        cursor: pointer;
    }

    .social-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-xl);
    }

    .social-header {
        height: 120px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .social-header::before {
        content: '🏆';
        position: absolute;
        font-size: 8rem;
        opacity: 0.1;
        right: -1rem;
        bottom: -2rem;
    }

    .social-status {
        position: absolute;
        top: 1rem;
        right: 1rem;
    }

    .social-title {
        color: white;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }

    .social-date {
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.875rem;
        position: relative;
        z-index: 1;
    }

    .social-body {
        padding: 1.5rem;
    }

    .social-meta {
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

    .social-progress {
        margin-bottom: 1rem;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }

    .social-footer {
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

    .list-view .social-card {
        display: flex;
        flex-direction: row;
        align-items: center;
    }

    .list-view .social-header {
        width: 200px;
        height: auto;
        flex-shrink: 0;
    }

    .list-view .social-body {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 2rem;
    }

    .list-view .social-meta {
        display: flex;
        gap: 2rem;
        margin: 0;
    }

    .list-view .social-footer {
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

    .social-card input[type="checkbox"] {
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
</style>
@section('content')
    <main class="main-content" id="mainContent">
        <div class="container">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <h1>Quản Lý Thi Đấu Social</h1>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">
                            <a href="{{route('homeyard.overview')}}" class="breadcrumb-link">🏠 Dashboard</a>
                        </span>
                        <span class="breadcrumb-separator">/</span>
                        <span class="breadcrumb-item">Lịch thi đấu</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <input type="text" class="search-input" placeholder="Tìm kiếm Lịch thi đấu..." id="searchInput">
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

            <!-- Filter Bar -->
            <div class="filter-actions" style="margin-bottom: 2rem;">
                <button class="btn btn-primary" onclick="openCreateModal()">
                    ➕ Tạo Lịch Thi Đấu Mới
                </button>
            </div>

            <!-- Bulk Actions -->
            <div class="bulk-actions" id="bulkActions">
                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                <div class="bulk-info">
                    <span id="selectedCount">0</span> lịch thi đấu được chọn
                </div>
                <button class="btn btn-danger btn-sm" onclick="deleteSelected()">
                    🗑️ Xóa
                </button>
            </div>

            <!-- Social Grid -->
            <div class="social-grid" id="socialGrid">
                @forelse($socials as $item)
                    @php
                        $participantCount = 0;
                        $maxParticipants = $item->max_participants ?? 64;
                        $progress = $maxParticipants > 0 ? round(($participantCount / $maxParticipants) * 100) : 0;

                        $itemDate = strtotime($item->date);
                    @endphp

                    <div class="social-card fade-in" data-location="{{ $item->stadium->name ?? '--' }}"
                        data-name="{{ $item->name }}" data-date="{{ $itemDate }}" data-social-id="{{ $item->id }}">
                        <input type="checkbox" class="tournament-checkbox" onchange="updateBulkActions()">
                        <div class="social-header">
                            <h3 class="social-title">{{ $item->name }}</h3>
                            <div class="social-date">
                                @if ($item->days_of_week && count($item->days_of_week) > 0)
                                    @php
                                        $daysMap = [
                                            '2' => 'T2',
                                            '3' => 'T3',
                                            '4' => 'T4',
                                            '5' => 'T5',
                                            '6' => 'T6',
                                            '7' => 'T7',
                                            '1' => 'CN',
                                        ];
                                        $dayLabels = array_map(fn($d) => $daysMap[$d], $item->days_of_week);
                                    @endphp
                                    <div>📅 {{ implode(', ', $dayLabels) }} </div>
                                @endif
                                ⏰ {{ substr($item->start_time, 0, 5) }} - {{ substr($item->end_time, 0, 5) }}
                            </div>
                        </div>
                        <div class="social-body">
                            <div class="social-meta">
                                <div class="meta-item">
                                    <div class="meta-label">Người tham gia</div>
                                    <div class="meta-value">{{ $participantCount }}/{{ $maxParticipants }} người</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">Sân</div>
                                    <div class="meta-value">{{ $item->stadium->name ?? '--' }}</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">Đối tượng</div>
                                    @php
                                        $levels = [
                                            'beginner' => 'Người mới',
                                            'intermediate' => 'Trung cấp',
                                            'advanced' => 'Nâng cao',
                                        ];
                                    @endphp
                                    <div class="meta-value">{{ $levels[$item->object] ?? '--' }}</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-label">Phí tham gia</div>
                                    <div class="meta-value">
                                        {{ $item->fee ? number_format($item->fee, 0, ',', '.') . ' ₫' : 'Miễn phí' }}
                                    </div>
                                </div>
                            </div>
                            <div class="social-progress">
                                <div class="progress-label">
                                    <span>Đăng ký</span>
                                    <span>{{ $participantCount }}/{{ $maxParticipants }} ({{ $progress }}%)</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $progress }}%;"></div>
                                </div>
                            </div>
                            <div class="social-footer">
                                <button class="btn btn-primary btn-sm" style="flex: 1;"
                                    onclick="openSocialModal({{ $item->id }})">
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
                            <div class="empty-title">Không có lịch đấu nào</div>
                            <div class="empty-description">Bạn chưa tạo lịch đấu nào. Hãy tạo lịch đấu đầu tiên của bạn.
                            </div>
                            <button class="btn btn-primary" onclick="openCreateModal()">Tạo Lịch Thi Đấu Mới</button>
                        </div>
                    </div>
                @endforelse


            </div>

            <!-- Pagination -->
            <div class="pagination">
                @if ($socials->onFirstPage())
                    <button class="pagination-btn" disabled>←</button>
                @else
                    <a href="{{ $socials->previousPageUrl() }}" class="pagination-btn">←</a>
                @endif

                @foreach ($socials->getUrlRange(1, $socials->lastPage()) as $page => $url)
                    @if ($page == $socials->currentPage())
                        <button class="pagination-btn active" disabled>{{ $page }}</button>
                    @else
                        <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($socials->hasMorePages())
                    <a href="{{ $socials->nextPageUrl() }}" class="pagination-btn">→</a>
                @else
                    <button class="pagination-btn" disabled>→</button>
                @endif
            </div>
        </div>
    </main>

    <!-- Social Detail Modal -->
    <div class="modal" id="detailModal"></div>

    <!-- Social Edit Modal -->
    <div class="modal" id="editModal"></div>

    <div class="modal" id="createModal">
        <div class="modal-content">
            <form id="socialForm" method="POST" enctype="multipart/form-data"
                action="{{ route('homeyard.socials.store') }}">

                @csrf
                <div class="modal-header"
                    style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border-bottom: none;">
                    <h3 class="modal-title">Tạo Lịch Đấu Mới</h3>
                    <button type="button" class="modal-close" style="color: white;"
                        onclick="closeCreateModal()">×</button>
                </div>
                <div class="modal-body" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                    <div class="form-group">
                        <label class="form-label">Tên *</label>
                        <input type="text" class="form-input" name="name"
                            placeholder="VD: Giải Pickleball Mở Rộng" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sân *</label>
                        <select class="form-select" name="stadium_id" required>
                            <option value="">Chọn sân</option>
                            @if (isset($stadiums))
                                @foreach ($stadiums as $stadium)
                                    <option value="{{ $stadium->id }}">{{ $stadium->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Giờ bắt đầu *</label>
                            <input type="time" class="form-input" name="start_time" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Giờ kết thúc *</label>
                            <input type="time" class="form-input" name="end_time" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ngày trong tuần</label>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" class="day-checkbox selectAllDays" value="all"
                                    style="cursor: pointer;">
                                <span>Chọn tất cả</span>
                            </label>
                            <span style="width: 100%; height: 1px; background: var(--border-color);"></span>
                            @php
                                $days = [
                                    '2' => 'Thứ 2',
                                    '3' => 'Thứ 3',
                                    '4' => 'Thứ 4',
                                    '5' => 'Thứ 5',
                                    '6' => 'Thứ 6',
                                    '7' => 'Thứ 7',
                                    '1' => 'Chủ nhật',
                                ];
                            @endphp
                            @foreach ($days as $dayNum => $dayName)
                                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                    <input type="checkbox" class="day-checkbox" name="days_of_week[]"
                                        value="{{ $dayNum }}" style="cursor: pointer;">
                                    <span>{{ $dayName }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-2">
                        <div class="form-group">
                            <label class="form-label">Đối tượng *</label>
                            @php
                                $levels = [
                                    'beginner' => 'Người mới',
                                    'intermediate' => 'Trung cấp',
                                    'advanced' => 'Nâng cao',
                                ];
                            @endphp
                            <select class="form-select" name="object">
                                <option value="">Chọn</option>
                                @foreach ($levels as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Số người tối đa</label>
                            <input type="number" class="form-input" name="max_participants" placeholder="64"
                                min="1">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phí tham gia (VNĐ)</label>
                        <input type="number" class="form-input" name="fee" placeholder="0" min="0"
                            step="0.01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-input" name="description" placeholder="Nhập mô tả sự kiện..." rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Tạo lịch đấu</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('js')
    <script>
        // Modal functions
        function openCreateModal() {
            document.getElementById('createModal').classList.add('show');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.remove('show');
            // Reset form after a short delay to avoid interfering with submission
            setTimeout(() => {
                document.getElementById('socialForm').reset();
            }, 500);
        }

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

        // Social Detail Modal Functions
        function openSocialModal(socialId) {
            const modal = document.getElementById('detailModal');

            // Fetch social details via AJAX
            fetch(`/homeyard/socials/${socialId}`, {
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

        // Attach select all days listener
        function attachSelectAllDaysListener() {
            const selectAllCheckboxes = document.querySelectorAll('.selectAllDays');
            selectAllCheckboxes.forEach(checkbox => {
                // Remove existing listeners by cloning
                const newCheckbox = checkbox.cloneNode(true);
                checkbox.parentNode.replaceChild(newCheckbox, checkbox);

                // Attach new listener
                newCheckbox.addEventListener('change', function() {
                    const form = this.closest('form');
                    const dayCheckboxes = form.querySelectorAll('input[name="days_of_week[]"]');
                    dayCheckboxes.forEach(cb => {
                        cb.checked = this.checked;
                    });
                });
            });
        }

        // Social Edit Modal Functions
        function openEditModal(socialId) {
            const modal = document.getElementById('editModal');

            // Fetch social data for editing
            fetch(`/homeyard/socials/${socialId}/edit`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    modal.innerHTML = data.html;
                    modal.classList.add('show');
                    attachSelectAllDaysListener();
                });
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.remove('show');
        }

        // Handle form submission with AJAX for create
        document.getElementById('socialForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const actionUrl = this.getAttribute('action');
            
            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    closeCreateModal();
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    toastr.error(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                toastr.error('Có lỗi xảy ra. Vui lòng thử lại.');
            });
        });

        // Handle form submission with AJAX for edit modal
        document.addEventListener('submit', function(e) {
            if (e.target.id === 'editSocialForm') {
                e.preventDefault();
                
                const form = e.target;
                const formData = new FormData(form);
                const actionUrl = form.getAttribute('action');
                
                fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        closeEditModal();
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Có lỗi xảy ra. Vui lòng thử lại.');
                });
            }
        });


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

        function deleteSelected() {
            const checkboxes = document.querySelectorAll('.tournament-checkbox:checked');
            if (checkboxes.length === 0) {
                toastr.warning('Vui lòng chọn ít nhất một lịch thi đấu để xóa');
                return;
            }

            if (!confirm(`Bạn có chắc chắn muốn xóa ${checkboxes.length} lịch thi đấu này?`)) {
                return;
            }

            const socialIds = Array.from(checkboxes).map(cb => {
                return cb.closest('.social-card').dataset.socialId;
            });

            fetch('{{ route("homeyard.socials.bulkDelete") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    ids: socialIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Có lỗi xảy ra. Vui lòng thử lại.');
            });
        }

        // Handle select all days for create modal (initial page load)
        attachSelectAllDaysListener();
        </script>
        @endsection
