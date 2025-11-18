@extends('layouts.front')

@section('content')
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .dashboard-container {
        padding: 40px 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .welcome-header {
        margin-bottom: 50px;
        animation: slideDown 0.5s ease;
    }

    .welcome-header h2 {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 10px;
    }

    .welcome-header p {
        color: #6b7280;
        font-size: 1rem;
    }

    .sidebar {
        display: grid;
        grid-template-columns: 1fr 2.5fr;
        gap: 30px;
        align-items: start;
    }

    .profile-section {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .profile-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .profile-card:hover {
        box-shadow: 0 12px 40px rgba(245, 158, 11, 0.15);
        transform: translateY(-5px);
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 700;
        box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
    }

    .profile-card h4 {
        font-size: 1.5rem;
        color: #1f2937;
        margin-bottom: 8px;
        font-weight: 700;
    }

    .profile-card p {
        color: #9ca3af;
        margin-bottom: 15px;
        font-size: 0.95rem;
    }

    .role-badge {
        display: inline-block;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .profile-card hr {
        border: none;
        border-top: 1px solid #e5e7eb;
        margin: 20px 0;
    }

    .edit-profile-btn {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
    }

    .edit-profile-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
        color: white;
    }

    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .stats-card h5 {
        font-size: 1rem;
        color: #6b7280;
        margin-bottom: 20px;
        font-weight: 600;
        padding-bottom: 15px;
        border-bottom: 1px solid #f3f4f6;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        color: #374151;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #f59e0b;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .menu-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .menu-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }

    .menu-card:hover::before {
        transform: scaleX(1);
    }

    .menu-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(245, 158, 11, 0.2);
    }

    .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
        font-size: 1.8rem;
        color: white;
        transition: all 0.3s ease;
    }

    .icon-box-amber {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .icon-box-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .icon-box-red {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .icon-box-green {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .icon-box-purple {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .icon-box-teal {
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
    }

    .menu-card:hover .icon-box {
        transform: scale(1.1) rotate(-5deg);
    }

    .menu-card h5 {
        font-size: 1.2rem;
        color: #1f2937;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .menu-card p {
        color: #9ca3af;
        font-size: 0.95rem;
        margin-bottom: 15px;
        line-height: 1.5;
    }

    .menu-btn {
        display: inline-block;
        padding: 10px 16px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .menu-btn:hover {
        transform: translateX(3px);
        color: white;
    }

    .activity-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-top: 30px;
    }

    .activity-card h5 {
        font-size: 1.2rem;
        color: #1f2937;
        margin-bottom: 20px;
        font-weight: 700;
        padding-bottom: 15px;
        border-bottom: 1px solid #f3f4f6;
    }

    .activity-empty {
        text-align: center;
        padding: 40px 20px;
        color: #9ca3af;
    }

    .activity-empty i {
        font-size: 3rem;
        color: #d1d5db;
        margin-bottom: 15px;
    }

    .athlete-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .athlete-item {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
    }

    .athlete-item:hover {
        background: #f3f4f6;
        border-color: #f59e0b;
    }

    .athlete-info {
        flex: 1;
    }

    .athlete-name {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 5px;
    }

    .athlete-tournament {
        font-size: 0.9rem;
        color: #9ca3af;
    }

    .athlete-actions {
        display: flex;
        gap: 10px;
    }

    .btn-approve {
        padding: 8px 14px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-approve:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .btn-reject {
        padding: 8px 14px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-reject:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 5px;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    @media (max-width: 1024px) {
        .sidebar {
            grid-template-columns: 1fr;
        }

        .menu-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 20px 15px;
        }

        .welcome-header h2 {
            font-size: 1.8rem;
        }

        .menu-grid {
            grid-template-columns: 1fr;
        }

        .profile-section {
            order: 2;
        }
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
<div class="dashboard-container">
    <div class="welcome-header">
        <h2>Bảng Điều Khiển Sân Nhà 🏟️</h2>
        <p>Quản lý các giải đấu, vận động viên, sân vận động và đặt sân của bạn một cách hiệu quả</p>
    </div>

    <div class="sidebar">
        <div class="profile-section">
            <div class="profile-card">
                <div class="profile-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <h4>{{ $user->name }}</h4>
                <p>{{ $user->email }}</p>
                <span class="role-badge">🏟️ Sân Nhà</span>
                <hr>
                <a href="#" class="edit-profile-btn">Chỉnh Sửa Hồ Sơ</a>
            </div>

            <div class="stats-card">
                <h5>📊 Thống Kê Nhanh</h5>
                <div class="stat-item">
                    <span>Sân Của Tôi</span>
                    <span class="stat-value">0</span>
                </div>
                <div class="stat-item">
                    <span>Vận Động Viên</span>
                    <span class="stat-value">0</span>
                </div>
                <div class="stat-item">
                    <span>Đặt Sân Hoạt Động</span>
                    <span class="stat-value">0</span>
                </div>
            </div>
        </div>

        <div>
            <div class="menu-grid">
                <!-- Tournament Information Management -->
                @if(auth()->user()->hasRole('admin'))
                    <div class="menu-card">
                        <div class="icon-box icon-box-teal">ℹ️</div>
                        <h5>Thông Tin Giải Đấu</h5>
                        <p>Quản lý tất cả các giải đấu trong hệ thống.</p>
                        <a href="{{ route('admin.tournaments.index') }}" class="menu-btn">Quản Lý →</a>
                    </div>
                @else
                    <div class="menu-card">
                        <div class="icon-box icon-box-teal">ℹ️</div>
                        <h5>Thông Tin Giải Đấu</h5>
                        <p>Quản lý các giải đấu mà bạn đã tạo.</p>
                        <a href="{{ route('homeyard.tournaments.index') }}" class="menu-btn">Quản Lý →</a>
                    </div>
                @endif

                <!-- Edit Tournament -->
                <div class="menu-card">
                    <div class="icon-box icon-box-blue">✏️</div>
                    <h5>Tạo Giải Đấu Mới</h5>
                    <p>Tạo giải đấu mới với quy tắc, lịch trình và cài đặt.</p>
                    <a href="{{ route('homeyard.tournaments.create') }}" class="menu-btn">Tạo →</a>
                </div>

                <!-- Manage Athletes -->
                <div class="menu-card">
                    <div class="icon-box icon-box-green">👥</div>
                    <h5>Quản Lý Vận Động Viên</h5>
                    <p>Duyệt và quản lý vận động viên trong các giải đấu của bạn.</p>
                    <a href="{{ route('homeyard.athletes.index') }}" class="menu-btn">Quản Lý →</a>
                </div>

                <!-- Manage Stadium -->
                <div class="menu-card">
                    <div class="icon-box icon-box-amber">🏢</div>
                    <h5>Quản Lý Sân</h5>
                    <p>Quản lý thông tin sân và tính khả dụng của bạn.</p>
                    <a href="#" class="menu-btn">Quản Lý →</a>
                </div>

                <!-- Manage Bookings -->
                <div class="menu-card">
                    <div class="icon-box icon-box-red">📅</div>
                    <h5>Quản Lý Đặt Sân</h5>
                    <p>Xem và quản lý tất cả các đặt sân sân và giải đấu.</p>
                    <a href="{{ route('booking') }}" class="menu-btn">Quản Lý →</a>
                </div>

                <!-- Update Stadium Information -->
                <div class="menu-card">
                    <div class="icon-box icon-box-purple">⚙️</div>
                    <h5>Cập Nhật Thông Tin Sân</h5>
                    <p>Cập nhật chi tiết sân, ảnh và cơ sở vật chất của bạn.</p>
                    <a href="{{ route('homeyard.stadiums.index') }}" class="menu-btn">Cập Nhật →</a>
                </div>
            </div>

            <!-- Pending Athletes Management -->
            <div class="activity-card">
                <h5>👥 Quản Lý Vận Động Viên</h5>
                @php
                    $pendingAthletes = \App\Models\TournamentAthlete::whereHas('tournament', function($q) {
                        $q->where('user_id', auth()->id());
                    })->where('status', 'pending')->with('tournament')->get();
                @endphp

                @if($pendingAthletes->count() > 0)
                    <div class="athlete-list">
                        @foreach($pendingAthletes as $athlete)
                            <div class="athlete-item">
                                <div class="athlete-info">
                                    <div class="athlete-name">{{ $athlete->athlete_name }}</div>
                                    <div class="athlete-tournament">
                                        <strong>Giải đấu:</strong> {{ $athlete->tournament->name }}
                                    </div>
                                    <div class="athlete-tournament">
                                        <strong>Email:</strong> {{ $athlete->email ?? 'N/A' }}
                                    </div>
                                    <div class="athlete-tournament">
                                        <strong>Điện thoại:</strong> {{ $athlete->phone ?? 'N/A' }}
                                    </div>
                                    <span class="status-badge status-pending">⏳ Đang Chờ Duyệt</span>
                                </div>
                                <div class="athlete-actions">
                                    <form action="{{ route('homeyard.athletes.approve', ['tournament' => $athlete->tournament_id, 'athlete' => $athlete->id]) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn-approve">✓ Duyệt</button>
                                    </form>
                                    <form action="{{ route('homeyard.athletes.reject', ['tournament' => $athlete->tournament_id, 'athlete' => $athlete->id]) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn-reject">✕ Từ Chối</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="activity-empty">
                        <i class="fas fa-check-circle"></i>
                        <p>Tất cả vận động viên đã được duyệt. Không có yêu cầu chờ xử lý!</p>
                    </div>
                @endif
            </div>

            <!-- Recent Bookings -->
            <div class="activity-card">
                <h5>📋 Đặt Sân Gần Đây</h5>
                <div class="activity-empty">
                    <i class="fas fa-calendar"></i>
                    <p>Chưa có đặt sân nào gần đây. Hãy bắt đầu quảng bá các sân của bạn!</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
