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
        padding: clamp(20px, 3vw, 40px);
        max-width: 1400px;
        margin: 0 auto;
    }

    .welcome-header {
        margin-bottom: clamp(30px, 5vw, 50px);
        animation: slideDown 0.5s ease;
    }

    .welcome-header h2 {
        font-size: clamp(1.8rem, 5vw, 2.5rem);
        font-weight: 700;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 10px;
        word-wrap: break-word;
    }

    .welcome-header p {
        color: #6b7280;
        font-size: clamp(0.9rem, 2vw, 1rem);
        line-height: 1.5;
    }

    .sidebar {
        display: grid;
        grid-template-columns: 1fr 2.5fr;
        gap: clamp(20px, 3vw, 30px);
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
        padding: clamp(20px, 3vw, 30px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .profile-card:hover {
        box-shadow: 0 12px 40px rgba(0, 217, 181, 0.15);
        transform: translateY(-5px);
    }

    .profile-avatar {
        width: clamp(80px, 15vw, 100px);
        height: clamp(80px, 15vw, 100px);
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(2rem, 5vw, 2.5rem);
        font-weight: 700;
        box-shadow: 0 4px 20px rgba(0, 217, 181, 0.3);
    }

    .profile-card h4 {
        font-size: clamp(1.2rem, 3vw, 1.5rem);
        color: #1f2937;
        margin-bottom: 8px;
        font-weight: 700;
        word-break: break-word;
    }

    .profile-card p {
        color: #9ca3af;
        margin-bottom: 15px;
        font-size: clamp(0.85rem, 2vw, 0.95rem);
        word-break: break-word;
    }

    .role-badge {
        display: inline-block;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
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
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
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
        box-shadow: 0 8px 20px rgba(0, 217, 181, 0.3);
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
        color: #00D9B5;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: clamp(15px, 3vw, 20px);
    }

    .menu-card {
        background: white;
        border-radius: 15px;
        padding: clamp(20px, 3vw, 25px);
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
        background: linear-gradient(90deg, #00D9B5 0%, #0db89d 100%);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }

    .menu-card:hover::before {
        transform: scaleX(1);
    }

    .menu-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0, 217, 181, 0.2);
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

    .icon-box-teal {
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
    }

    .icon-box-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .icon-box-amber {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .icon-box-green {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .menu-card:hover .icon-box {
        transform: scale(1.1) rotate(-5deg);
    }

    .menu-card h5 {
        font-size: clamp(1rem, 2.5vw, 1.2rem);
        color: #1f2937;
        margin-bottom: 10px;
        font-weight: 700;
        word-break: break-word;
    }

    .menu-card p {
        color: #9ca3af;
        font-size: clamp(0.85rem, 2vw, 0.95rem);
        margin-bottom: 15px;
        line-height: 1.5;
    }

    .menu-btn {
        display: inline-block;
        padding: clamp(8px, 1vw, 10px) clamp(12px, 2vw, 16px);
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: clamp(0.8rem, 1.5vw, 0.9rem);
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
        padding: clamp(20px, 3vw, 30px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-top: clamp(20px, 3vw, 30px);
    }

    .activity-card h5 {
        font-size: clamp(1rem, 2.5vw, 1.2rem);
        color: #1f2937;
        margin-bottom: 20px;
        font-weight: 700;
        padding-bottom: 15px;
        border-bottom: 1px solid #f3f4f6;
        word-break: break-word;
    }

    .activity-empty {
        text-align: center;
        padding: clamp(30px, 5vw, 40px) clamp(15px, 3vw, 20px);
        color: #9ca3af;
    }

    .activity-empty i {
        font-size: clamp(2.5rem, 5vw, 3rem);
        color: #d1d5db;
        margin-bottom: 15px;
    }

    @media (max-width: 1024px) {
        .sidebar {
            grid-template-columns: 1fr;
        }

        .profile-section {
            flex-direction: row;
            gap: clamp(15px, 3vw, 20px);
        }

        .profile-card,
        .stats-card {
            flex: 1;
            min-width: 280px;
        }
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: clamp(15px, 2vw, 20px);
        }

        .welcome-header h2 {
            font-size: clamp(1.5rem, 4vw, 1.8rem);
        }

        .sidebar {
            grid-template-columns: 1fr;
        }

        .menu-grid {
            grid-template-columns: 1fr;
        }

        .profile-section {
            flex-direction: column;
            gap: clamp(15px, 3vw, 20px);
        }

        .profile-card,
        .stats-card {
            width: 100%;
        }

        .icon-box {
            font-size: clamp(1.5rem, 3vw, 1.8rem);
        }
    }

    @media (max-width: 480px) {
        .dashboard-container {
            padding: 10px;
        }

        .welcome-header h2 {
            font-size: 1.3rem;
        }

        .welcome-header p {
            font-size: 0.85rem;
        }

        .profile-card,
        .stats-card,
        .menu-card,
        .activity-card {
            padding: 15px;
        }

        .profile-avatar {
            width: 70px;
            height: 70px;
            font-size: 1.8rem;
        }

        .menu-card h5 {
            font-size: 1rem;
        }

        .menu-btn {
            width: 100%;
            text-align: center;
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

<div class="dashboard-container" style="margin-top: 100px">
    <div class="welcome-header">
        <h2>Chào mừng quay trở lại, {{ $user->name }}! 👋</h2>
        <p>Sẵn sàng khám phá các giải đấu mới và đặt trận đấu tiếp theo của bạn?</p>
    </div>

    <div class="sidebar">
        <div class="profile-section">
            <div class="profile-card">
                <div class="profile-avatar">
                    @if($user->getAvatarUrl())
                        <img src="{{ $user->getAvatarUrl() }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    @else
                        {{ $user->getInitials() }}
                    @endif
                </div>
                <h4>{{ $user->name }}</h4>
                <p>{{ $user->email }}</p>
                <span class="role-badge">👤 Người Dùng Thường</span>
                <hr>
                <a href="{{ route('user.profile.edit') }}" class="edit-profile-btn">Chỉnh sửa hồ sơ</a>
            </div>

            <div class="stats-card">
                <h5>📊 Thống Kê Nhanh</h5>
                <div class="stat-item">
                    <span>Giải Đấu Đã Tham Gia</span>
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
                <!-- Join Tournaments -->
                <div class="menu-card">
                    <div class="icon-box icon-box-teal">🏆</div>
                    <h5>Tham Gia Giải Đấu</h5>
                    <p>Duyệt và tham gia các giải đấu pickleball hấp dẫn trong khu vực của bạn.</p>
                    <a href="{{ route('tournaments') }}" class="menu-btn">Xem Giải Đấu →</a>
                </div>

                <!-- My Profile -->
                <div class="menu-card">
                    <div class="icon-box icon-box-teal">👤</div>
                    <h5>Hồ sơ của tôi</h5>
                    <p>Xem va quản lý thông tin hồ sơ và  cài đặt của bạn.</p>
                    <a href="{{ route('user.profile.edit') }}" class="menu-btn">Chỉnh sửa hồ sơ</a>
                </div>

                <!-- My Bookings -->
                {{-- <div class="menu-card">
                    <div class="icon-box icon-box-amber">📅</div>
                    <h5>Đặt Sân Của Tôi</h5>
                    <p>Xem và quản lý các đặt sân giải đấu và sân của bạn.</p>
                    <a href="{{ route('user.bookings') }}" class="menu-btn">Xem Đặt Sân →</a>
                </div> --}}

                <!-- Courts -->
                <div class="menu-card">
                    <div class="icon-box icon-box-green">📍</div>
                    <h5>Tìm Sân</h5>
                    <p>Duyệt các sân pickleball có sẵn gần bạn.</p>
                    <a href="{{ route('courts') }}" class="menu-btn">Xem Sân →</a>
                </div>

                <!-- Clubs & Groups -->
                <div class="menu-card">
                    <div class="icon-box icon-box-blue">👥</div>
                    <h5>Câu Lạc Bộ & Nhóm</h5>
                    <p>Tạo hoặc tham gia câu lạc bộ và nhóm pickleball.</p>
                    <a href="{{ route('clubs.index') }}" class="menu-btn">Xem Câu Lạc Bộ →</a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="activity-card">
                <h5>📋 Hoạt Động Gần Đây</h5>
                <div class="activity-empty">
                    <i class="fas fa-inbox"></i>
                    <p>Chưa có hoạt động nào. Hãy bắt đầu bằng cách tham gia một giải đấu!</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
