@extends('layouts.homeyard')
<style>
    /* Page-specific styles */
    .filter-bar {
        background: var(--bg-white);
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        margin-bottom: 1.5rem;
        box-shadow: var(--shadow-sm);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .player-avatar {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-full);
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.875rem;
    }

    .player-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .player-details {
        flex: 1;
    }

    .player-name {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.125rem;
    }

    .player-meta {
        font-size: 0.75rem;
        color: var(--text-light);
    }

    .stats-mini {
        display: flex;
        gap: 1rem;
        font-size: 0.75rem;
    }

    .stat-mini-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        color: var(--text-secondary);
    }

    .stat-mini-value {
        font-weight: 700;
        color: var(--text-primary);
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .btn-icon-sm {
        width: 32px;
        height: 32px;
        padding: 0;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
    }

    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: var(--radius-md);
        font-weight: 700;
        font-size: 0.875rem;
    }

    .rank-gold {
        background: linear-gradient(135deg, #FFD700, #FFA500);
        color: white;
    }

    .rank-silver {
        background: linear-gradient(135deg, #C0C0C0, #808080);
        color: white;
    }

    .rank-bronze {
        background: linear-gradient(135deg, #CD7F32, #8B4513);
        color: white;
    }

    .rank-default {
        background: var(--bg-light);
        color: var(--text-secondary);
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1.5rem;
    }

    .pagination-btn {
        padding: 0.5rem 1rem;
        border: 2px solid var(--border-color);
        background: var(--bg-white);
        border-radius: var(--radius-md);
        cursor: pointer;
        transition: all var(--transition);
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .pagination-btn:hover:not(:disabled) {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pagination-btn.active {
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

    .quick-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .quick-stat-box {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all var(--transition);
        position: relative;
        overflow: hidden;
    }

    .quick-stat-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%);
        pointer-events: none;
    }

    .quick-stat-box:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transform: translateY(-4px);
    }

    .quick-stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }

    .quick-stat-label {
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.9);
        position: relative;
        z-index: 1;
        font-weight: 500;
    }

    /* Category Badge Colors */
    .badge-category {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        font-weight: 600;
        color: white;
        text-align: center;
    }

    .badge-single-men {
        background: linear-gradient(135deg, #3B82F6, #2563EB);
    }

    .badge-single-women {
        background: linear-gradient(135deg, #EC4899, #DB2777);
    }

    .badge-double-men {
        background: linear-gradient(135deg, #8B5CF6, #7C3AED);
    }

    .badge-double-women {
        background: linear-gradient(135deg, #F59E0B, #D97706);
    }

    .badge-double-mixed {
        background: linear-gradient(135deg, #10B981, #059669);
    }
    @media (max-width: 768px) {
        .top-header {
            margin-top: 100px;
        }
    }
</style>
@section('content')
    <main class="main-content" id="mainContent">
        <div class="container">
            <!-- Header -->
            <div class="top-header">
                <div class="header-left">
                    <h1>Quản Lý Vận Động Viên</h1>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item">
                            <a href="{{route('homeyard.overview')}}" class="breadcrumb-link">Dashboard</a>
                        </span>
                        <span class="breadcrumb-separator">›</span>
                        <span class="breadcrumb-item">Vận động Viên</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <span class="search-icon">🔍</span>
                        <input type="text" class="search-input" placeholder="Tìm kiếm VĐV...">
                    </div>
                    {{-- <div class="header-notifications">
                        <button class="notification-btn">
                            🔔
                            <span class="notification-badge">5</span>
                        </button>
                    </div> --}}
                    <div class="header-user">
                        <div class="user-avatar">{{ auth()->user()->getInitials() }}</div>
                        <div class="user-info">
                            <div class="user-name">{{auth()->user()->name}}</div>
                            <div class="user-role">{{auth()->user()->getFirstRoleName()}}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="quick-stats fade-in">
                <div class="quick-stat-box">
                    <div class="quick-stat-value" id="totalAthletes">0</div>
                    <div class="quick-stat-label">Tổng VĐV</div>
                </div>
                <div class="quick-stat-box">
                    <div class="quick-stat-value" id="pendingAthletes">0</div>
                    <div class="quick-stat-label">VĐV Chờ Duyệt</div>
                </div>
                <div class="quick-stat-box">
                    <div class="quick-stat-value" id="activeAthletes">0</div>
                    <div class="quick-stat-label">VĐV Đang Hoạt Động</div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar fade-in">
                <div class="filter-grid">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Tìm kiếm</label>
                        <input type="text" class="form-input" placeholder="Tên, email, số điện thoại...">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Giải đấu</label>
                        <select class="form-select" id="filterTournament">
                            <option value="">Tất cả</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Nội dung</label>
                        <select class="form-select" id="filterCategory">
                            <option value="">Tất cả</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" id="filterStatus">
                            <option value="">Tất cả</option>
                            <option value="approved">Duyệt rồi</option>
                            <option value="pending">Chờ duyệt</option>
                            <option value="rejected">Bị từ chối</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">&nbsp;</label>
                        {{-- <button class="btn btn-primary" style="width: 100%;">
                            🔍 Lọc
                        </button> --}}
                    </div>
                </div>
            </div>

            <!-- Players Table -->
            <div class="card fade-in">
                <div class="card-header" style="flex-wrap: wrap;gap:1rem;">
                    <h3 class="card-title" style="white-space: nowrap;">Danh Sách Vận Động Viên</h3>
                    <div class="card-actions">
                        <button class="btn btn-secondary btn-sm" onclick="exportToExcel()">📥 Xuất Excel</button>
                    </div>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Hạng</th>
                                    <th>Vận Động Viên</th>
                                    <th>Nội dung</th>
                                    <th>Thống kê</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="athletesTableBody">
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 2rem;">
                                        <div style="color: var(--text-secondary);">Đang tải dữ liệu...</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="pagination" id="paginationContainer">
                        <button class="pagination-btn" disabled>‹ Trước</button>
                        <button class="pagination-btn active">1</button>
                        <button class="pagination-btn">Sau ›</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div class="modal" id="addPlayerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Thêm Vận Động Viên Mới</h3>
                <button class="modal-close" onclick="closeAddPlayerModal()">×</button>
            </div>
            <form id="addPlayerForm" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Giải Đấu *</label>
                    <select name="tournament_id" class="form-select" required>
                        <option value="">-- Chọn Giải Đấu --</option>
                    </select>
                    <small style="color: #9ca3af;">Bạn cần chọn giải đấu để thêm VĐV</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Nội dung thi đấu *</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">-- Chọn Nội Dung --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Họ và tên *</label>
                    <input type="text" name="athlete_name" class="form-input" placeholder="Nhập họ và tên" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-input" placeholder="example@email.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Số điện thoại *</label>
                    <input type="tel" name="phone" class="form-input" placeholder="0901234567" required>
                </div>
            </form>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeAddPlayerModal()">Hủy</button>
                <button class="btn btn-primary" onclick="submitAddPlayerForm()">💾 Lưu</button>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        const categoryTypeMap = {
            'single_men': 'Đơn Nam',
            'single_women': 'Đơn Nữ',
            'double_men': 'Đôi Nam',
            'double_women': 'Đôi Nữ',
            'double_mixed': 'Đôi Nam Nữ'
        };

        const statusMap = {
            'approved': { label: 'Duyệt rồi', className: 'badge-success' },
            'pending': { label: 'Chờ duyệt', className: 'badge-warning' },
            'rejected': { label: 'Bị từ chối', className: 'badge-danger' }
        };

        let allTournaments = [];
        let allAthletes = [];
        let currentPage = 1;
        let totalPages = 1;
        let paginationData = null;

        // Get initials from athlete name
        function getInitials(name) {
            return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
        }

        // Get badge class based on position
        function getRankBadgeClass(position) {
            if (!position || position > 3) return 'rank-default';
            if (position === 1) return 'rank-gold';
            if (position === 2) return 'rank-silver';
            if (position === 3) return 'rank-bronze';
            return 'rank-default';
        }

        // Get category badge class based on category type
        function getCategoryBadgeClass(categoryType) {
            const categoryMap = {
                'single_men': 'badge-single-men',
                'single_women': 'badge-single-women',
                'double_men': 'badge-double-men',
                'double_women': 'badge-double-women',
                'double_mixed': 'badge-double-mixed'
            };
            return categoryMap[categoryType] || 'badge-primary';
        }

        // Render pagination
        function renderPagination(pagination) {
            const container = document.getElementById('paginationContainer');
            
            if (!pagination) {
                container.innerHTML = '<button class="pagination-btn" disabled>‹ Trước</button><button class="pagination-btn active">1</button><button class="pagination-btn" disabled>Sau ›</button>';
                return;
            }
            
            currentPage = pagination.current_page;
            totalPages = pagination.last_page;
            
            
            let html = '';
            
            // Previous button
            if (currentPage > 1) {
                html += `<button class="pagination-btn" onclick="goToPage(${currentPage - 1})">‹ Trước</button>`;
            } else {
                html += `<button class="pagination-btn" disabled>‹ Trước</button>`;
            }
            
            // Page numbers
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);
            
            if (startPage > 1) {
                html += `<button class="pagination-btn" onclick="goToPage(1)">1</button>`;
                if (startPage > 2) {
                    html += `<button class="pagination-btn" disabled>...</button>`;
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                if (i === currentPage) {
                    html += `<button class="pagination-btn active">${i}</button>`;
                } else {
                    html += `<button class="pagination-btn" onclick="goToPage(${i})">${i}</button>`;
                }
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<button class="pagination-btn" disabled>...</button>`;
                }
                html += `<button class="pagination-btn" onclick="goToPage(${totalPages})">${totalPages}</button>`;
            }
            
            // Next button
            if (currentPage < totalPages) {
                html += `<button class="pagination-btn" onclick="goToPage(${currentPage + 1})">Sau ›</button>`;
            } else {
                html += `<button class="pagination-btn" disabled>Sau ›</button>`;
            }
            
            container.innerHTML = html;
        }

        function goToPage(page) {
            currentPage = page;
            loadAthletes();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Render athletes table
        function renderAthletesTable(athletes) {
            const tbody = document.getElementById('athletesTableBody');
            
            if (!athletes || athletes.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;"><div style="color: var(--text-secondary);">Không có vận động viên nào</div></td></tr>';
                return;
            }

            tbody.innerHTML = athletes.map((athlete, index) => {
                const initials = getInitials(athlete.athlete_name);
                const rankClass = getRankBadgeClass(athlete.position);
                const status = statusMap[athlete.status] || { label: 'Không xác định', className: 'badge-gray' };
                const categoryType = categoryTypeMap[athlete.category_type] || athlete.category_name;

                return `
                    <tr>
                        <td>
                            <div class="rank-badge ${rankClass}">${athlete.position || '-'}</div>
                        </td>
                        <td>
                            <div class="player-info">
                                <div class="player-avatar">${initials}</div>
                                <div class="player-details">
                                    <div class="player-name">${athlete.athlete_name}</div>
                                    <div class="player-meta">📧 ${athlete.email} • 📱 ${athlete.phone}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                             <span class="badge-category ${getCategoryBadgeClass(athlete.category_type)}">${categoryType}</span>
                         </td>
                        <td>
                            <div class="stats-mini">
                                <div class="stat-mini-item">
                                    🏆 <span class="stat-mini-value">${athlete.statistics.matches_won}/${athlete.statistics.matches_played}</span>
                                </div>
                                <div class="stat-mini-item">
                                    🎯 <span class="stat-mini-value">${athlete.statistics.win_rate}%</span>
                                </div>
                                <div class="stat-mini-item">
                                    📊 <span class="stat-mini-value">${athlete.statistics.total_points}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge ${status.className}">${status.label}</span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết" onclick="viewAthleteDetails(${athlete.id})">👁️</button>
                                <button class="btn btn-ghost btn-icon-sm" title="Xóa" onclick="deleteAthlete(${athlete.id})">🗑️</button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Load athletes data
         function loadAthletes() {
             // Lấy giá trị search từ cả header search và filter bar
             const headerSearchValue = document.querySelector('.search-input')?.value || '';
             const filterSearchValue = document.querySelector('input.form-input').value;
             const searchValue = headerSearchValue || filterSearchValue;
             
             const tournamentId = document.getElementById('filterTournament').value;
             const categoryId = document.getElementById('filterCategory').value;
             const status = document.getElementById('filterStatus').value;

            const params = new URLSearchParams();
            if (searchValue) params.append('search', searchValue);
            if (tournamentId) params.append('tournament_id', tournamentId);
            if (categoryId) params.append('category_id', categoryId);
            if (status) params.append('status', status);
            params.append('page', currentPage);

            console.log('Loading athletes with params:', Object.fromEntries(params));
            fetch(`/homeyard/athlete-management/list?${params.toString()}`)
                .then(res => {
                    console.log('Response status:', res.status);
                    return res.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        allAthletes = data.data;
                        renderAthletesTable(data.data);
                        renderPagination(data.pagination);
                        
                        // Update stats
                        document.getElementById('totalAthletes').textContent = data.summary.total_athletes;
                        document.getElementById('pendingAthletes').textContent = data.summary.pending_approval;
                        document.getElementById('activeAthletes').textContent = data.summary.active_athletes;
                    } else {
                        console.error('API error:', data);
                    }
                })
                .catch(err => {
                    console.error('Error loading athletes:', err);
                    renderAthletesTable([]);
                });
        }

        // Load tournaments for filter
        function loadTournamentsForFilter() {
            console.log('Loading tournaments...');
            fetch('/homeyard/athlete-management/tournaments')
                .then(res => {
                    console.log('Tournaments response status:', res.status);
                    return res.json();
                })
                .then(data => {
                    console.log('Tournaments data:', data);
                    if (data.success && data.tournaments) {
                        allTournaments = data.tournaments;
                        const select = document.getElementById('filterTournament');
                        const options = [{ id: '', name: 'Tất cả' }, ...allTournaments];
                        
                        select.innerHTML = options.map(t => 
                            `<option value="${t.id}">${t.name}</option>`
                        ).join('');
                        console.log('Tournaments loaded:', allTournaments.length);
                    } else {
                        console.warn('Invalid tournaments response:', data);
                    }
                })
                .catch(err => {
                    console.error('Error loading tournaments:', err);
                });
        }

        // Load categories when tournament changes
        function loadCategoriesForFilter(tournamentId) {
            const select = document.getElementById('filterCategory');
            
            if (!tournamentId) {
                select.innerHTML = '<option value="">Tất cả</option>';
                return;
            }

            fetch(`/homeyard/tournament-categories/${tournamentId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.categories) {
                        select.innerHTML = '<option value="">Tất cả</option>' + 
                            data.categories.map(c => 
                                `<option value="${c.id}">${c.category_name}</option>`
                            ).join('');
                    }
                })
                .catch(err => console.error('Error loading categories:', err));
        }

        // Modal functions
        function openAddPlayerModal() {
            document.getElementById('addPlayerModal').classList.add('active');
            loadTournamentsForModal();
        }
        
        function closeAddPlayerModal() {
            document.getElementById('addPlayerModal').classList.remove('active');
        }
        
        function submitAddPlayerForm() {
            const form = document.getElementById('addPlayerForm');
            const tournamentId = form.tournament_id.value;
            
            if (!tournamentId) {
                alert('Vui lòng chọn giải đấu');
                return;
            }
            
            const formData = new FormData(form);
            const actionUrl = `/homeyard/tournaments/${tournamentId}/athletes/add`;
            
            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Thêm vận động viên thành công');
                    closeAddPlayerModal();
                    loadAthletes();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không xác định'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Có lỗi xảy ra khi thêm vận động viên');
            });
        }
         
        function loadTournamentsForModal() {
            fetch('/homeyard/my-tournaments')
                .then(res => res.json())
                .then(data => {
                    const select = document.querySelector('select[name="tournament_id"]');
                    select.innerHTML = '<option value="">-- Chọn Giải Đấu --</option>';
                    
                    if (data.tournaments && data.tournaments.length > 0) {
                        data.tournaments.forEach(tournament => {
                            const option = document.createElement('option');
                            option.value = tournament.id;
                            option.textContent = tournament.name;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(err => console.error('Error loading tournaments:', err));
        }
         
        function loadCategoriesForModal(tournamentId) {
            if (!tournamentId) {
                document.querySelector('select[name="category_id"]').innerHTML = '<option value="">-- Chọn Nội Dung --</option>';
                return;
            }
            
            fetch(`/homeyard/tournament-categories/${tournamentId}`)
                .then(res => res.json())
                .then(data => {
                    const select = document.querySelector('select[name="category_id"]');
                    select.innerHTML = '<option value="">-- Chọn Nội Dung --</option>';
                    
                    if (data.categories && data.categories.length > 0) {
                        data.categories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.category_name;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(err => console.error('Error loading categories:', err));
        }

        function viewAthleteDetails(athleteId) {
            const athlete = allAthletes.find(a => a.id === athleteId);
            if (athlete) {
                alert(`Chi tiết VĐV: ${athlete.athlete_name}\n\nTrận đấu: ${athlete.statistics.matches_played}\nThắng: ${athlete.statistics.matches_won}\nTỷ lệ thắng: ${athlete.statistics.win_rate}%\nĐiểm: ${athlete.statistics.total_points}`);
            }
        }

        function editAthlete(athleteId) {
            const athlete = allAthletes.find(a => a.id === athleteId);
            if (!athlete) return;
            
            // TODO: Mở modal chỉnh sửa với dữ liệu VĐV
            alert(`Chỉnh sửa VĐV: ${athlete.athlete_name}\n\nID: ${athlete.id}`);
        }

        function deleteAthlete(athleteId) {
            const athlete = allAthletes.find(a => a.id === athleteId);
            if (!athlete) return;
            
            if (!confirm(`Bạn có chắc muốn xóa vận động viên "${athlete.athlete_name}"?`)) {
                return;
            }
            
            fetch(`/homeyard/athlete-management/athlete/${athleteId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Xóa vận động viên thành công');
                    loadAthletes();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không xác định'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Có lỗi xảy ra khi xóa vận động viên');
            });
        }

        function formatPhoneNumber(phone) {
            if (!phone) return '';
            // Loại bỏ tất cả ký tự không phải số
            const cleaned = phone.replace(/\D/g, '');
            // Format: 0XX XXXX XXXX
            if (cleaned.length === 10) {
                return cleaned.replace(/(\d{3})(\d{4})(\d{3})/, '$1 $2 $3');
            }
            return phone;
        }

        function exportToExcel() {
            const searchValue = document.querySelector('.search-input')?.value || '';
            const filterSearchValue = document.querySelector('input.form-input').value;
            const searchTerm = searchValue || filterSearchValue;
            
            const tournamentId = document.getElementById('filterTournament').value;
            const categoryId = document.getElementById('filterCategory').value;
            const status = document.getElementById('filterStatus').value;

            const params = new URLSearchParams();
            if (searchTerm) params.append('search', searchTerm);
            if (tournamentId) params.append('tournament_id', tournamentId);
            if (categoryId) params.append('category_id', categoryId);
            if (status) params.append('status', status);
            // Yêu cầu tất cả dữ liệu bằng cách set limit cao
            params.append('page', 1);

            // Fetch tất cả dữ liệu
            fetch(`/homeyard/athlete-management/list?${params.toString()}&limit=1000`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.data || data.data.length === 0) {
                    alert('Không có dữ liệu để xuất');
                    return;
                }

                // Chuẩn bị dữ liệu
                const athletes = data.data;
                const allData = [];
                
                // Lấy toàn bộ pages nếu cần
                if (data.pagination && data.pagination.last_page > 1) {
                    const fetchAllPages = [];
                    for (let i = 2; i <= data.pagination.last_page; i++) {
                        const newParams = new URLSearchParams(params);
                        newParams.set('page', i);
                        fetchAllPages.push(
                            fetch(`/homeyard/athlete-management/list?${newParams.toString()}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            }).then(r => r.json())
                        );
                    }
                    
                    Promise.all(fetchAllPages).then(results => {
                        let allAthletes = [...athletes];
                        results.forEach(result => {
                            if (result.success && result.data) {
                                allAthletes = [...allAthletes, ...result.data];
                            }
                        });
                        processExport(allAthletes);
                    });
                } else {
                    processExport(athletes);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Lỗi khi xuất dữ liệu');
            });

            function processExport(athletes) {
                const excelData = athletes.map(athlete => ({
                    'Tên VĐV': athlete.athlete_name,
                    'Email': athlete.email,
                    'Số điện thoại': formatPhoneNumber(athlete.phone),
                    'Giải đấu': athlete.tournament_name,
                    'Nội dung': athlete.category_name,
                    'Hạng': athlete.position || '-',
                    'Trạng thái': athlete.status === 'approved' ? 'Duyệt rồi' : athlete.status === 'pending' ? 'Chờ duyệt' : 'Bị từ chối',
                    'Trận đấu': athlete.statistics.matches_played,
                    'Thắng': athlete.statistics.matches_won,
                    'Tỷ lệ thắng (%)': athlete.statistics.win_rate,
                    'Điểm': athlete.statistics.total_points
                }));

                // Tạo CSV content
                const headers = Object.keys(excelData[0]);
                const csv = [
                    headers.join(','),
                    ...excelData.map(row => 
                        headers.map(header => {
                            const value = row[header];
                            // Escape quotes và wrap in quotes nếu chứa comma
                            return typeof value === 'string' && value.includes(',') 
                                ? `"${value.replace(/"/g, '""')}"` 
                                : value;
                        }).join(',')
                    )
                ].join('\n');

                // Download file
                const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                const now = new Date();
                const filename = `Danh_sach_VDV_${now.getDate()}_${now.getMonth() + 1}_${now.getFullYear()}.csv`;
                
                link.setAttribute('href', url);
                link.setAttribute('download', filename);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                alert(`Xuất thành công ${athletes.length} vận động viên`);
            }
        }
         
        // Event listeners
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Page loaded, calling loadTournamentsForFilter()...');
            loadTournamentsForFilter();
            console.log('Page loaded, calling loadAthletes()...');
            loadAthletes();
            
            // Filter change listeners
            document.getElementById('filterTournament').addEventListener('change', (e) => {
                currentPage = 1;
                loadCategoriesForFilter(e.target.value);
                loadAthletes();
            });
            
            document.getElementById('filterCategory').addEventListener('change', () => {
                currentPage = 1;
                loadAthletes();
            });
            
            document.getElementById('filterStatus').addEventListener('change', () => {
                currentPage = 1;
                loadAthletes();
            });
            
            document.querySelector('input.form-input').addEventListener('input', () => {
                currentPage = 1;
                loadAthletes();
            });

            // Add event listener for header search input
            document.querySelector('.search-input').addEventListener('input', () => {
                currentPage = 1;
                loadAthletes();
            });

            // Tournament selection in modal
            const tournamentSelect = document.querySelector('select[name="tournament_id"]');
            if (tournamentSelect) {
                tournamentSelect.addEventListener('change', (e) => {
                    loadCategoriesForModal(e.target.value);
                });
            }
        
            // Close modal on outside click
            document.getElementById('addPlayerModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAddPlayerModal();
                }
            });
        });
    </script>
@endsection
