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
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .quick-stat-box {
        background: var(--bg-light);
        padding: 1rem;
        border-radius: var(--radius-md);
        text-align: center;
    }

    .quick-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }

    .quick-stat-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
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
                            <a href="overview.html" class="breadcrumb-link">Trang chủ</a>
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

            <!-- Quick Stats -->
            <div class="quick-stats fade-in">
                <div class="quick-stat-box">
                    <div class="quick-stat-value">1,248</div>
                    <div class="quick-stat-label">Tổng VĐV</div>
                </div>
                <div class="quick-stat-box">
                    <div class="quick-stat-value">156</div>
                    <div class="quick-stat-label">VĐV Mới Tháng Này</div>
                </div>
                <div class="quick-stat-box">
                    <div class="quick-stat-value">892</div>
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
                        <label class="form-label">Nội dung</label>
                        <select class="form-select">
                            <option value="">Tất cả</option>
                            <option value="singles">Đơn Nam</option>
                            <option value="women">Đơn Nữ</option>
                            <option value="doubles">Đôi Nam</option>
                            <option value="women-doubles">Đôi Nữ</option>
                            <option value="mixed">Đôi Nam Nữ</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select">
                            <option value="">Tất cả</option>
                            <option value="active">Đang hoạt động</option>
                            <option value="inactive">Không hoạt động</option>
                            <option value="pending">Chờ duyệt</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary" style="width: 100%;">
                            🔍 Lọc
                        </button>
                    </div>
                </div>
            </div>

            <!-- Players Table -->
            <div class="card fade-in">
                <div class="card-header">
                    <h3 class="card-title">Danh Sách Vận Động Viên</h3>
                    <div class="card-actions">
                        <button class="btn btn-secondary btn-sm">📥 Xuất Excel</button>
                        <button class="btn btn-primary btn-sm" onclick="openAddPlayerModal()">
                            ➕ Thêm VĐV
                        </button>
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
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="rank-badge rank-gold">1</div>
                                    </td>
                                    <td>
                                        <div class="player-info">
                                            <div class="player-avatar">NA</div>
                                            <div class="player-details">
                                                <div class="player-name">Nguyễn Văn An</div>
                                                <div class="player-meta">📧 nguyenvanan@email.com • 📱 0901234567</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">Đơn Nam</span>
                                        <span class="badge badge-info">Đôi Nam</span>
                                    </td>
                                    <td>
                                        <div class="stats-mini">
                                            <div class="stat-mini-item">
                                                🏆 <span class="stat-mini-value">45</span>
                                            </div>
                                            <div class="stat-mini-item">
                                                🎯 <span class="stat-mini-value">78%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">Đang hoạt động</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết">👁️</button>
                                            <button class="btn btn-ghost btn-icon-sm" title="Chỉnh sửa">✏️</button>
                                            <button class="btn btn-ghost btn-icon-sm" title="Xóa">🗑️</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="rank-badge rank-silver">2</div>
                                    </td>
                                    <td>
                                        <div class="player-info">
                                            <div class="player-avatar">TL</div>
                                            <div class="player-details">
                                                <div class="player-name">Trần Thu Linh</div>
                                                <div class="player-meta">📧 tranthilinh@email.com • 📱 0912345678</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-danger">Đơn Nữ</span>
                                        <span class="badge badge-warning">Đôi Nam Nữ</span>
                                    </td>
                                    <td>
                                        <div class="stats-mini">
                                            <div class="stat-mini-item">
                                                🏆 <span class="stat-mini-value">38</span>
                                            </div>
                                            <div class="stat-mini-item">
                                                🎯 <span class="stat-mini-value">72%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">Đang hoạt động</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết">👁️</button>
                                            <button class="btn btn-ghost btn-icon-sm" title="Chỉnh sửa">✏️</button>
                                            <button class="btn btn-ghost btn-icon-sm" title="Xóa">🗑️</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="rank-badge rank-bronze">3</div>
                                    </td>
                                    <td>
                                        <div class="player-info">
                                            <div class="player-avatar">LH</div>
                                            <div class="player-details">
                                                <div class="player-name">Lê Minh Hoàng</div>
                                                <div class="player-meta">📧 leminhhoang@email.com • 📱 0923456789</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">Đơn Nam</span>
                                    </td>
                                    <td>
                                        <div class="stats-mini">
                                            <div class="stat-mini-item">
                                                🏆 <span class="stat-mini-value">32</span>
                                            </div>
                                            <div class="stat-mini-item">
                                                🎯 <span class="stat-mini-value">68%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">Đang hoạt động</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết">👁️</button>
                                            <button class="btn btn-ghost btn-icon-sm" title="Chỉnh sửa">✏️</button>
                                            <button class="btn btn-ghost btn-icon-sm" title="Xóa">🗑️</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="rank-badge rank-default">4</div>
                                    </td>
                                    <td>
                                        <div class="player-info">
                                            <div class="player-avatar">PH</div>
                                            <div class="player-details">
                                                <div class="player-name">Phạm Thu Hà</div>
                                                <div class="player-meta">📧 phamthuha@email.com • 📱 0934567890</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-danger">Đơn Nữ</span>
                                        <span class="badge badge-success">Đôi Nữ</span>
                                    </td>
                                    <td>
                                        <div class="stats-mini">
                                            <div class="stat-mini-item">
                                                🏆 <span class="stat-mini-value">28</span>
                                            </div>
                                            <div class="stat-mini-item">
                                                🎯 <span class="stat-mini-value">65%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning">Chờ duyệt</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết">👁️</button>
                                            <button class="btn btn-ghost btn-icon-sm" title="Chỉnh sửa">✏️</button>
                                            <button class="btn btn-ghost btn-icon-sm" title="Xóa">🗑️</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="rank-badge rank-default">5</div>
                                    </td>
                                    <td>
                                        <div class="player-info">
                                            <div class="player-avatar">ĐT</div>
                                            <div class="player-details">
                                                <div class="player-name">Đỗ Văn Toàn</div>
                                                <div class="player-meta">📧 dovantoan@email.com • 📱 0945678901</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">Đôi Nam</span>
                                        <span class="badge badge-warning">Đôi Nam Nữ</span>
                                    </td>
                                    <td>
                                        <div class="stats-mini">
                                            <div class="stat-mini-item">
                                                🏆 <span class="stat-mini-value">24</span>
                                            </div>
                                            <div class="stat-mini-item">
                                                🎯 <span class="stat-mini-value">61%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">Đang hoạt động</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết">👁️</button>
                                            <button class="btn btn-ghost btn-icon-sm" title="Chỉnh sửa">✏️</button>
                                            <button class="btn btn-ghost btn-icon-sm" title="Xóa">🗑️</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="rank-badge rank-default">6</div>
                                    </td>
                                    <td>
                                        <div class="player-info">
                                            <div class="player-avatar">VL</div>
                                            <div class="player-details">
                                                <div class="player-name">Vũ Thu Lan</div>
                                                <div class="player-meta">📧 vuthulan@email.com • 📱 0956789012</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-danger">Đơn Nữ</span>
                                    </td>
                                    <td>
                                        <div class="stats-mini">
                                            <div class="stat-mini-item">
                                                🏆 <span class="stat-mini-value">22</span>
                                            </div>
                                            <div class="stat-mini-item">
                                                🎯 <span class="stat-mini-value">59%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-gray">Không hoạt động</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-ghost btn-icon-sm" title="Xem chi tiết">👁️</button>
                                            <button class="btn btn-ghost btn-icon-sm" title="Chỉnh sửa">✏️</button>
                                            <button class="btn btn-ghost btn-icon-sm" title="Xóa">🗑️</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
    <div class="modal" id="addPlayerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Thêm Vận Động Viên Mới</h3>
                <button class="modal-close" onclick="closeAddPlayerModal()">×</button>
            </div>
            <form>
                <div class="form-group">
                    <label class="form-label">Họ và tên *</label>
                    <input type="text" class="form-input" placeholder="Nhập họ và tên" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-input" placeholder="example@email.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Số điện thoại *</label>
                    <input type="tel" class="form-input" placeholder="0901234567" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Ngày sinh</label>
                    <input type="date" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Nội dung thi đấu *</label>
                    <select class="form-select" required>
                        <option value="">Chọn nội dung</option>
                        <option value="singles">Đơn Nam</option>
                        <option value="women">Đơn Nữ</option>
                        <option value="doubles">Đôi Nam</option>
                        <option value="women-doubles">Đôi Nữ</option>
                        <option value="mixed">Đôi Nam Nữ</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Ghi chú</label>
                    <textarea class="form-textarea" placeholder="Thông tin thêm về vận động viên..."></textarea>
                </div>
            </form>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeAddPlayerModal()">Hủy</button>
                <button class="btn btn-primary">💾 Lưu</button>
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

        // Modal functions
        function openAddPlayerModal() {
            document.getElementById('addPlayerModal').classList.add('active');
        }

        function closeAddPlayerModal() {
            document.getElementById('addPlayerModal').classList.remove('active');
        }

        // Close modal on outside click
        document.getElementById('addPlayerModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddPlayerModal();
            }
        });

        // Load page
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Player Management Loaded');
        });
    </script>
@endsection
