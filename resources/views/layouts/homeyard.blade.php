<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cấu Hình Giải Đấu - Hệ Thống Quản Lý Giải Đấu</title>
    <link rel="stylesheet" href="{{ asset('assets/css/tournament-styles.css') }}">
    <style>
        /* Page-specific styles */
        .tournament-header-banner {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: var(--radius-xl);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .tournament-header-banner::before {
            content: '🏆';
            position: absolute;
            font-size: 12rem;
            opacity: 0.1;
            right: -2rem;
            bottom: -3rem;
        }

        .tournament-header-content {
            position: relative;
            z-index: 1;
        }

        .tournament-header-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .tournament-header-meta {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .header-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            opacity: 0.95;
        }

        /* Tabs */
        .config-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            background: var(--bg-white);
            padding: 0.5rem;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            overflow-x: auto;
        }

        .config-tab {
            flex: 1;
            min-width: 150px;
            padding: 1rem 1.5rem;
            background: transparent;
            border: none;
            border-radius: var(--radius-lg);
            cursor: pointer;
            font-weight: 600;
            color: var(--text-secondary);
            transition: all var(--transition);
            white-space: nowrap;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .config-tab:hover {
            background: var(--bg-light);
        }

        .config-tab.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: var(--shadow-md);
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .tab-content.active {
            display: block;
        }

        /* Step Indicator */
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3rem;
            padding: 0 2rem;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 10%;
            right: 10%;
            height: 3px;
            background: var(--border-color);
            z-index: -1;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
            background: transparent;
            max-width: 200px;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--bg-white);
            border: 3px solid var(--border-color);
            color: var(--text-light);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-bottom: 0.75rem;
            transition: all var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .step.active .step-circle {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-color: transparent;
            box-shadow: var(--shadow-lg);
            transform: scale(1.15);
        }

        .step.completed .step-circle {
            background: var(--accent-green);
            color: white;
            border-color: transparent;
        }

        .step.completed .step-circle::after {
            content: '✓';
        }

        .step-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 600;
        }

        .step.active .step-label {
            color: var(--primary-color);
            font-weight: 700;
        }

        /* Content Items */
        .content-item {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
            transition: all var(--transition);
        }

        .content-item:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-md);
        }

        .content-item h4 {
            color: var(--primary-color);
            margin-bottom: 0.75rem;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .content-item p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        /* Item Lists */
        .item-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .item-card {
            background: var(--bg-white);
            padding: 1.25rem;
            border-radius: var(--radius-lg);
            border: 2px solid var(--border-color);
            text-align: center;
            cursor: pointer;
            transition: all var(--transition);
        }

        .item-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .item-card.selected {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-color: transparent;
            box-shadow: var(--shadow-md);
        }

        .item-card strong {
            display: block;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .item-card p {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        /* Bracket */
        .bracket-container {
            background: var(--bg-light);
            padding: 2rem;
            border-radius: var(--radius-xl);
            overflow-x: auto;
            margin-top: 2rem;
        }

        .bracket-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            min-width: 1000px;
        }

        .bracket-column h4 {
            text-align: center;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .bracket-match {
            background: var(--bg-white);
            padding: 1rem;
            border-radius: var(--radius-lg);
            border: 2px solid var(--border-color);
            margin-bottom: 1rem;
        }

        .bracket-player {
            padding: 0.875rem;
            background: var(--bg-light);
            border-radius: var(--radius-md);
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all var(--transition);
        }

        .bracket-player:hover {
            transform: translateX(3px);
        }

        .bracket-player:last-child {
            margin-bottom: 0;
        }

        .bracket-player.winner {
            background: linear-gradient(135deg, var(--accent-green), #22C55E);
            color: white;
            font-weight: 700;
        }

        /* Athlete List */
        .athlete-list {
            margin-top: 1.5rem;
        }

        .athlete-item {
            background: var(--bg-white);
            padding: 1.25rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 2px solid var(--border-color);
            transition: all var(--transition);
            flex-wrap: wrap;
            gap: 1rem;
        }

        .athlete-item:hover {
            border-color: var(--primary-color);
            transform: translateX(3px);
            box-shadow: var(--shadow-md);
        }

        .athlete-info {
            flex: 1;
            min-width: 250px;
        }

        .athlete-name {
            font-weight: 700;
            color: var(--text-primary);
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }

        .athlete-details {
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .athlete-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* Group Cards */
        .group-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .group-card {
            background: var(--bg-white);
            border-radius: var(--radius-xl);
            border: 2px solid var(--border-color);
            overflow: hidden;
            transition: all var(--transition);
        }

        .group-card:hover {
            border-color: var(--primary-color);
            box-shadow: var(--shadow-lg);
        }

        .group-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.25rem;
            text-align: center;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .group-players {
            list-style: none;
            padding: 1.25rem;
        }

        .group-players li {
            padding: 1rem;
            background: var(--bg-light);
            margin-bottom: 0.75rem;
            border-radius: var(--radius-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all var(--transition);
        }

        .group-players li:hover {
            background: rgba(0, 217, 181, 0.1);
            transform: translateX(3px);
        }

        .group-players li:last-child {
            margin-bottom: 0;
        }

        /* Match Items */
        .match-list {
            margin-top: 1.5rem;
        }

        .match-item {
            background: var(--bg-white);
            padding: 1.5rem;
            border-radius: var(--radius-xl);
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-color);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition);
        }

        .match-item:hover {
            box-shadow: var(--shadow-lg);
        }

        .match-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--bg-light);
            flex-wrap: wrap;
            gap: 1rem;
        }

        .match-info {
            flex: 1;
            min-width: 250px;
        }

        .match-title {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .match-details {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.8;
        }

        .match-players {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 1.5rem;
            align-items: center;
            margin-top: 1rem;
        }

        .player-side {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
        }

        .player-name {
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-primary);
            font-size: 1.05rem;
        }

        .score-input {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .score-input input {
            width: 70px;
            text-align: center;
            font-size: 1.3rem;
            font-weight: 700;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
        }

        .vs-divider {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.75rem;
        }

        /* Rankings Table */
        .rankings-table {
            width: 100%;
            margin-top: 1.5rem;
            border-collapse: separate;
            border-spacing: 0 0.75rem;
        }

        .rankings-table thead tr {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .rankings-table th {
            padding: 1rem 1.25rem;
            text-align: left;
            font-weight: 700;
            white-space: nowrap;
            font-size: 0.95rem;
        }

        .rankings-table th:first-child {
            border-radius: var(--radius-lg) 0 0 var(--radius-lg);
        }

        .rankings-table th:last-child {
            border-radius: 0 var(--radius-lg) var(--radius-lg) 0;
        }

        .rankings-table tbody tr {
            background: var(--bg-white);
            transition: all var(--transition);
        }

        .rankings-table tbody tr:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-md);
        }

        .rankings-table td {
            padding: 1rem 1.25rem;
            white-space: nowrap;
        }

        .rankings-table tbody tr td:first-child {
            border-radius: var(--radius-lg) 0 0 var(--radius-lg);
        }

        .rankings-table tbody tr td:last-child {
            border-radius: 0 var(--radius-lg) var(--radius-lg) 0;
        }

        .rank-medal {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-weight: 700;
            color: white;
            font-size: 1rem;
        }

        .rank-1 {
            background: linear-gradient(135deg, #FFD700, #FFA500);
        }

        .rank-2 {
            background: linear-gradient(135deg, #C0C0C0, #808080);
        }

        .rank-3 {
            background: linear-gradient(135deg, #CD7F32, #8B4513);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin: 1rem 0;
        }

        .checkbox-group input[type="checkbox"] {
            width: 22px;
            height: 22px;
            margin-right: 0.75rem;
            cursor: pointer;
            accent-color: var(--primary-color);
        }

        .checkbox-group label {
            cursor: pointer;
            user-select: none;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .tournament-header-title {
                font-size: 1.5rem;
            }

            .config-tabs {
                overflow-x: scroll;
            }

            .step-indicator {
                padding: 0 1rem;
            }

            .step-label {
                font-size: 0.75rem;
            }

            .bracket-grid {
                grid-template-columns: 1fr;
            }

            .match-players {
                grid-template-columns: 1fr;
            }

            .vs-divider {
                display: none;
            }

            .item-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .group-grid {
                grid-template-columns: 1fr;
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.6;
            }
        }

        .status-live {
            animation: pulse 1.5s infinite;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <div class="sidebar-logo">🏆</div>
                    <span class="sidebar-brand-text">TMS</span>
                </div>
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <span>☰</span>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Tổng Quan</div>
                    <a href="overview.html" class="nav-item">
                        <span class="nav-icon">📊</span>
                        <span class="nav-text">Tổng quan</span>
                    </a>
                    
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Quản Lý Giải Đâu</div>

                    <a href="tournaments.html" class="nav-item">
                        <span class="nav-icon">🏆</span>
                        <span class="nav-text">Giải đấu</span>
                        <span class="nav-badge">12</span>
                    </a>

                    <a href="tournament-dashboard.html" class="nav-item active">
                        <span class="nav-icon">⚙️</span>
                        <span class="nav-text">Cấu hình giải</span>
                    </a>
                   
                    <a href="#" class="nav-item">
                        <span class="nav-icon">🎾</span>
                        <span class="nav-text">Trận đấu</span>
                        <span class="nav-badge">45</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Quản lý Sân</div>
                   
                    <a href="#" class="nav-item">
                        <span class="nav-icon">👥</span>
                        <span class="nav-text">Danh sách sân</span>
                        <span class="nav-badge">10</span>
                    </a>
                    <a href="#" class="nav-item">
                        <span class="nav-icon">🏅</span>
                        <span class="nav-text">Quản lý đặt sân</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Vận động viên</div>
                   
                    <a href="#" class="nav-item">
                        <span class="nav-icon">👥</span>
                        <span class="nav-text">Vận động viên</span>
                        <span class="nav-badge">248</span>
                    </a>
                    <a href="#" class="nav-item">
                        <span class="nav-icon">🏅</span>
                        <span class="nav-text">Xếp hạng</span>
                    </a>
                   
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Hệ Thống</div>
                    <a href="#" class="nav-item">
                        <span class="nav-icon">⚙️</span>
                        <span class="nav-text">Cài đặt</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <div class="container">
                <!-- Top Header -->
                <header class="top-header">
                    <div class="header-left">
                        <h1>Cấu Hình Giải Đấu</h1>
                        <div class="breadcrumb">
                            <span class="breadcrumb-item">
                                <a href="overview.html" class="breadcrumb-link">🏠 Dashboard</a>
                            </span>
                            <span class="breadcrumb-separator">/</span>
                            <span class="breadcrumb-item">
                                <a href="tournaments.html" class="breadcrumb-link">Giải đấu</a>
                            </span>
                            <span class="breadcrumb-separator">/</span>
                            <span class="breadcrumb-item">Cấu hình</span>
                        </div>
                    </div>
                    <div class="header-right">
                        <button class="btn btn-success">💾 Lưu thay đổi</button>
                        <button class="btn btn-secondary">👁️ Xem trước</button>
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

                <!-- Tournament Header Banner -->
                <div class="tournament-header-banner fade-in">
                    <div class="tournament-header-content">
                        <h2 class="tournament-header-title">Giải Pickleball Mở Rộng TP.HCM 2025</h2>
                        <div class="tournament-header-meta">
                            <div class="header-meta-item">
                                <span>📅</span>
                                <span>20-22 Tháng 1, 2025</span>
                            </div>
                            <div class="header-meta-item">
                                <span>📍</span>
                                <span>Sân Pickleball Thảo Điền</span>
                            </div>
                            <div class="header-meta-item">
                                <span>👥</span>
                                <span>64 Vận động viên</span>
                            </div>
                            <div class="header-meta-item">
                                <span>💰</span>
                                <span>Giải thưởng: 50,000,000 VNĐ</span>
                            </div>
                            <div class="header-meta-item">
                                <span class="badge badge-success">Đang diễn ra</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Config Tabs -->
                <div class="config-tabs fade-in">
                    <button class="config-tab active" onclick="showConfigTab('config')">
                        ⚙️ Cấu hình giải đấu
                    </button>
                    <button class="config-tab" onclick="showConfigTab('athletes')">
                        👥 Quản lý VĐV
                    </button>
                    <button class="config-tab" onclick="showConfigTab('matches')">
                        🎾 Quản lý trận đấu
                    </button>
                    <button class="config-tab" onclick="showConfigTab('rankings')">
                        🏅 Bảng xếp hạng
                    </button>
                </div>

                <!-- TAB 1: CẤU HÌNH GIẢI ĐẤU -->
                <div id="config" class="tab-content active">
                    <!-- Step Indicator -->
                    <div class="step-indicator">
                        <div class="step completed">
                            <div class="step-circle">1</div>
                            <div class="step-label">Cấu hình cơ bản</div>
                        </div>
                        <div class="step completed">
                            <div class="step-circle">2</div>
                            <div class="step-label">Nội dung thi đấu</div>
                        </div>
                        <div class="step active">
                            <div class="step-circle">3</div>
                            <div class="step-label">Vòng đấu & Sân</div>
                        </div>
                        <div class="step">
                            <div class="step-circle">4</div>
                            <div class="step-label">Bảng đấu</div>
                        </div>
                    </div>

                    <!-- Step 1: Cấu hình cơ bản -->
                    <div class="card fade-in">
                        <div class="card-header">
                            <h3 class="card-title">📋 Thông tin giải đấu</h3>
                            <button class="btn btn-secondary btn-sm">✏️ Chỉnh sửa</button>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label class="form-label">Tên giải đấu *</label>
                                    <input type="text" class="form-input" value="Giải Pickleball Mở Rộng TP.HCM 2025" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Mã giải đấu *</label>
                                    <input type="text" class="form-input" value="PB-HCM-2025" required>
                                </div>
                            </div>

                            <div class="grid grid-3">
                                <div class="form-group">
                                    <label class="form-label">Ngày bắt đầu *</label>
                                    <input type="date" class="form-input" value="2025-01-20" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Ngày kết thúc *</label>
                                    <input type="date" class="form-input" value="2025-01-22" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Địa điểm tổ chức *</label>
                                    <input type="text" class="form-input" value="Sân Pickleball Thảo Điền" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Mô tả giải đấu</label>
                                <textarea class="form-textarea" rows="4">Giải đấu Pickleball quy mô lớn nhất TP.HCM năm 2025 với sự tham gia của các VĐV hàng đầu khu vực.</textarea>
                            </div>

                            <div class="grid grid-3">
                                <div class="form-group">
                                    <label class="form-label">Số lượng VĐV tối đa</label>
                                    <input type="number" class="form-input" value="64" min="4">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Loại giải đấu *</label>
                                    <select class="form-select" required>
                                        <option value="single" selected>Đơn nam</option>
                                        <option value="single-women">Đơn nữ</option>
                                        <option value="double">Đôi nam</option>
                                        <option value="double-women">Đôi nữ</option>
                                        <option value="double-mixed">Đôi nam nữ</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Hình thức thi đấu *</label>
                                    <select class="form-select" required>
                                        <option value="knockout" selected>Loại trực tiếp</option>
                                        <option value="round-robin">Vòng tròn</option>
                                        <option value="group-knockout">Bảng đấu + Knockout</option>
                                        <option value="swiss">Swiss System</option>
                                    </select>
                                </div>
                            </div>

                            <button class="btn btn-primary" onclick="nextStep(2)">Tiếp tục ➜</button>
                        </div>
                    </div>

                    <!-- Step 2: Nội dung thi đấu -->
                    <div class="card fade-in">
                        <div class="card-header">
                            <h3 class="card-title">🎯 Nội dung thi đấu</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                💡 Tạo các nội dung thi đấu khác nhau cho giải đấu
                            </div>

                            <h4 style="margin: 1.5rem 0 1rem 0; font-weight: 700;">Thêm nội dung mới</h4>
                            
                            <div class="grid grid-3">
                                <div class="form-group">
                                    <label class="form-label">Tên nội dung *</label>
                                    <input type="text" class="form-input" id="contentName" placeholder="VD: Nam đơn 18+">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Loại nội dung *</label>
                                    <select class="form-select" id="contentType">
                                        <option value="single-men">Đơn nam</option>
                                        <option value="single-women">Đơn nữ</option>
                                        <option value="double-men">Đôi nam</option>
                                        <option value="double-women">Đôi nữ</option>
                                        <option value="double-mixed">Đôi nam nữ</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Độ tuổi</label>
                                    <select class="form-select" id="contentAge">
                                        <option value="open">Mở rộng</option>
                                        <option value="u18">U18</option>
                                        <option value="18+" selected>18+</option>
                                        <option value="35+">35+</option>
                                        <option value="45+">45+</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label class="form-label">Số VĐV tối đa</label>
                                    <input type="number" class="form-input" id="contentMaxPlayers" placeholder="32" min="4">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Giải thưởng (VNĐ)</label>
                                    <input type="number" class="form-input" id="contentPrize" placeholder="5000000" min="0">
                                </div>
                            </div>

                            <button class="btn btn-success" onclick="addContent()">➕ Thêm nội dung</button>

                            <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Danh sách nội dung đã tạo</h4>
                            <div id="contentList">
                                <div class="content-item">
                                    <h4>Nam đơn 18+</h4>
                                    <p><strong>Loại:</strong> Đơn nam | <strong>Độ tuổi:</strong> 18+ | <strong>Số VĐV:</strong> 64 | <strong>Giải thưởng:</strong> 50,000,000 VNĐ</p>
                                    <button class="btn btn-secondary btn-sm">✏️ Chỉnh sửa</button>
                                    <button class="btn btn-danger btn-sm">🗑️ Xóa</button>
                                </div>
                            </div>

                            <button class="btn btn-primary" onclick="nextStep(3)">Tiếp tục ➜</button>
                        </div>
                    </div>

                    <!-- Step 3: Vòng đấu & Sân -->
                    <div class="card fade-in">
                        <div class="card-header">
                            <h3 class="card-title">🔄 Tạo vòng đấu</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                💡 Thiết lập các vòng đấu cho giải (Vòng bảng, Vòng 1/8, Tứ kết, Bán kết, Chung kết)
                            </div>

                            <h4 style="margin: 1.5rem 0 1rem 0; font-weight: 700;">Thêm vòng đấu mới</h4>
                            
                            <div class="grid grid-3">
                                <div class="form-group">
                                    <label class="form-label">Tên vòng đấu *</label>
                                    <input type="text" class="form-input" id="roundName" placeholder="VD: Vòng bảng">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Ngày thi đấu *</label>
                                    <input type="date" class="form-input" id="roundDate">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Giờ bắt đầu *</label>
                                    <input type="time" class="form-input" id="roundTime">
                                </div>
                            </div>

                            <button class="btn btn-success" onclick="addRound()">➕ Thêm vòng đấu</button>

                            <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Danh sách vòng đấu</h4>
                            <div class="item-grid">
                                <div class="item-card">
                                    <strong>Vòng bảng</strong>
                                    <p>20/01/2025 - 08:00</p>
                                </div>
                                <div class="item-card">
                                    <strong>Vòng 1/8</strong>
                                    <p>21/01/2025 - 08:00</p>
                                </div>
                                <div class="item-card">
                                    <strong>Tứ kết</strong>
                                    <p>21/01/2025 - 14:00</p>
                                </div>
                                <div class="item-card selected">
                                    <strong>Bán kết</strong>
                                    <p>22/01/2025 - 09:00</p>
                                </div>
                                <div class="item-card">
                                    <strong>Chung kết</strong>
                                    <p>22/01/2025 - 15:00</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card fade-in">
                        <div class="card-header">
                            <h3 class="card-title">🏟️ Chọn sân thi đấu</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                💡 Chọn các sân sẽ được sử dụng cho giải đấu
                            </div>

                            <h4 style="margin: 1.5rem 0 1rem 0; font-weight: 700;">Thêm sân mới</h4>
                            
                            <div class="grid grid-3">
                                <div class="form-group">
                                    <label class="form-label">Tên sân *</label>
                                    <input type="text" class="form-input" id="courtName" placeholder="VD: Sân số 1">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Loại sân *</label>
                                    <select class="form-select" id="courtType">
                                        <option value="indoor">Trong nhà</option>
                                        <option value="outdoor">Ngoài trời</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Trạng thái</label>
                                    <select class="form-select" id="courtStatus">
                                        <option value="available">Có thể sử dụng</option>
                                        <option value="maintenance">Bảo trì</option>
                                        <option value="reserved">Đã đặt</option>
                                    </select>
                                </div>
                            </div>

                            <button class="btn btn-success" onclick="addCourt()">➕ Thêm sân</button>

                            <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Danh sách sân đã chọn</h4>
                            <div class="item-grid">
                                <div class="item-card selected">
                                    <strong>Sân số 1</strong>
                                    <p>Trong nhà</p>
                                </div>
                                <div class="item-card selected">
                                    <strong>Sân số 2</strong>
                                    <p>Trong nhà</p>
                                </div>
                                <div class="item-card">
                                    <strong>Sân số 3</strong>
                                    <p>Ngoài trời</p>
                                </div>
                                <div class="item-card selected">
                                    <strong>Sân số 4</strong>
                                    <p>Trong nhà</p>
                                </div>
                            </div>

                            <button class="btn btn-primary" onclick="nextStep(4)">Tiếp tục ➜</button>
                        </div>
                    </div>

                    <!-- Step 4: Tạo bảng đấu -->
                    <div class="card fade-in">
                        <div class="card-header">
                            <h3 class="card-title">📊 Tạo bảng đấu</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success">
                                ✅ Tự động tạo bảng đấu dựa trên hình thức thi đấu và số lượng VĐV đã đăng ký
                            </div>

                            <div class="grid grid-3">
                                <div class="form-group">
                                    <label class="form-label">Chọn nội dung thi đấu *</label>
                                    <select class="form-select">
                                        <option value="">-- Chọn nội dung --</option>
                                        <option value="1" selected>Nam đơn 18+</option>
                                        <option value="2">Nữ đơn 18+</option>
                                        <option value="3">Đôi nam 35+</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Loại bảng đấu *</label>
                                    <select class="form-select">
                                        <option value="knockout" selected>Loại trực tiếp</option>
                                        <option value="round-robin">Vòng tròn</option>
                                        <option value="group">Chia bảng</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Số lượng bảng</label>
                                    <input type="number" class="form-input" value="4" min="1" max="16">
                                </div>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" id="autoSeed" checked>
                                <label for="autoSeed">Tự động xếp hạt giống dựa trên ranking</label>
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" id="balancedGroups">
                                <label for="balancedGroups">Cân bằng độ mạnh các bảng</label>
                            </div>

                            <button class="btn btn-success">🎲 Tạo bảng đấu tự động</button>
                            <button class="btn btn-primary">✏️ Tạo bảng đấu thủ công</button>

                            <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Xem trước bảng đấu</h4>
                            <div class="bracket-container">
                                <div class="bracket-grid">
                                    <div class="bracket-column">
                                        <h4>Vòng 1/8</h4>
                                        <div class="bracket-match">
                                            <div class="bracket-player winner">
                                                <span>Nguyễn Văn A</span>
                                                <span>11-7, 11-5</span>
                                            </div>
                                            <div class="bracket-player">
                                                <span>Trần Văn B</span>
                                                <span>7-11, 5-11</span>
                                            </div>
                                        </div>
                                        <div class="bracket-match">
                                            <div class="bracket-player">
                                                <span>Lê Văn C</span>
                                                <span>-</span>
                                            </div>
                                            <div class="bracket-player">
                                                <span>Phạm Văn D</span>
                                                <span>-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bracket-column">
                                        <h4>Tứ kết</h4>
                                        <div class="bracket-match">
                                            <div class="bracket-player">
                                                <span>Nguyễn Văn A</span>
                                                <span>-</span>
                                            </div>
                                            <div class="bracket-player">
                                                <span>Đang chờ</span>
                                                <span>-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bracket-column">
                                        <h4>Bán kết</h4>
                                        <div class="bracket-match">
                                            <div class="bracket-player">
                                                <span>Đang chờ</span>
                                                <span>-</span>
                                            </div>
                                            <div class="bracket-player">
                                                <span>Đang chờ</span>
                                                <span>-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bracket-column">
                                        <h4>Chung kết</h4>
                                        <div class="bracket-match">
                                            <div class="bracket-player">
                                                <span>Đang chờ</span>
                                                <span>-</span>
                                            </div>
                                            <div class="bracket-player">
                                                <span>Đang chờ</span>
                                                <span>-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-primary">💾 Lưu cấu hình</button>
                            <button class="btn btn-warning">🔄 Làm mới bảng đấu</button>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: QUẢN LÝ VĐV -->
                <div id="athletes" class="tab-content">
                    <div class="card fade-in">
                        <div class="card-header">
                            <h3 class="card-title">👥 Quản lý danh sách vận động viên</h3>
                            <div class="card-actions">
                                <button class="btn btn-primary btn-sm">➕ Thêm VĐV</button>
                                <button class="btn btn-success btn-sm">📊 Xuất Excel</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-card-header">
                                        <div>
                                            <div class="stat-label">Tổng VĐV đăng ký</div>
                                            <div class="stat-value">64</div>
                                        </div>
                                        <div class="stat-icon primary">👥</div>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-card-header">
                                        <div>
                                            <div class="stat-label">Đã xác nhận</div>
                                            <div class="stat-value">58</div>
                                        </div>
                                        <div class="stat-icon success">✅</div>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-card-header">
                                        <div>
                                            <div class="stat-label">Chờ xác nhận</div>
                                            <div class="stat-value">6</div>
                                        </div>
                                        <div class="stat-icon warning">⏳</div>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-card-header">
                                        <div>
                                            <div class="stat-label">Đã thanh toán</div>
                                            <div class="stat-value">52</div>
                                        </div>
                                        <div class="stat-icon success">💰</div>
                                    </div>
                                </div>
                            </div>

                            <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Danh sách VĐV</h4>
                            
                            <div class="athlete-list">
                                <div class="athlete-item">
                                    <div class="athlete-info">
                                        <div class="athlete-name">Nguyễn Văn An</div>
                                        <div class="athlete-details">
                                            📧 nguyenvanan@email.com | 📞 0901234567 | 🎯 Nam đơn 18+<br>
                                            <span class="badge badge-success">Đã xác nhận</span>
                                            <span class="badge badge-success">Đã thanh toán</span>
                                        </div>
                                    </div>
                                    <div class="athlete-actions">
                                        <button class="btn btn-secondary btn-sm">👁️ Chi tiết</button>
                                        <button class="btn btn-warning btn-sm">✏️</button>
                                        <button class="btn btn-danger btn-sm">🗑️</button>
                                    </div>
                                </div>

                                <div class="athlete-item">
                                    <div class="athlete-info">
                                        <div class="athlete-name">Trần Thị Bình</div>
                                        <div class="athlete-details">
                                            📧 tranthibinh@email.com | 📞 0912345678 | 🎯 Nam đơn 18+<br>
                                            <span class="badge badge-success">Đã xác nhận</span>
                                            <span class="badge badge-success">Đã thanh toán</span>
                                        </div>
                                    </div>
                                    <div class="athlete-actions">
                                        <button class="btn btn-secondary btn-sm">👁️ Chi tiết</button>
                                        <button class="btn btn-warning btn-sm">✏️</button>
                                        <button class="btn btn-danger btn-sm">🗑️</button>
                                    </div>
                                </div>

                                <div class="athlete-item">
                                    <div class="athlete-info">
                                        <div class="athlete-name">Lê Văn Cường</div>
                                        <div class="athlete-details">
                                            📧 levanc@email.com | 📞 0923456789 | 🎯 Nam đơn 18+<br>
                                            <span class="badge badge-warning">Chờ xác nhận</span>
                                            <span class="badge badge-danger">Chưa thanh toán</span>
                                        </div>
                                    </div>
                                    <div class="athlete-actions">
                                        <button class="btn btn-success btn-sm">✅ Xác nhận</button>
                                        <button class="btn btn-secondary btn-sm">👁️</button>
                                        <button class="btn btn-danger btn-sm">🗑️</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card fade-in">
                        <div class="card-header">
                            <h3 class="card-title">🎲 Bốc thăm chia bảng</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                ⚠️ Sau khi bốc thăm, bạn không thể thay đổi danh sách VĐV
                            </div>

                            <div class="grid grid-3">
                                <div class="form-group">
                                    <label class="form-label">Chọn nội dung thi đấu *</label>
                                    <select class="form-select">
                                        <option value="1" selected>Nam đơn 18+ (64 VĐV)</option>
                                        <option value="2">Nữ đơn 18+ (32 VĐV)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Số lượng bảng</label>
                                    <select class="form-select">
                                        <option value="2">2 bảng</option>
                                        <option value="4" selected>4 bảng</option>
                                        <option value="8">8 bảng</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phương thức</label>
                                    <select class="form-select">
                                        <option value="auto">Tự động (Random)</option>
                                        <option value="seeded" selected>Theo hạt giống</option>
                                        <option value="manual">Thủ công</option>
                                    </select>
                                </div>
                            </div>

                            <button class="btn btn-success">🎲 Bốc thăm tự động</button>
                            <button class="btn btn-primary">✏️ Chia bảng thủ công</button>
                            <button class="btn btn-warning">🔄 Bốc lại</button>

                            <h4 style="margin: 2rem 0 1rem 0; font-weight: 700;">Kết quả chia bảng</h4>
                            <div class="group-grid">
                                <div class="group-card">
                                    <div class="group-header">BẢNG A</div>
                                    <ul class="group-players">
                                        <li>
                                            <span>1. Nguyễn Văn An</span>
                                            <span class="badge badge-warning">⭐ #1</span>
                                        </li>
                                        <li><span>2. Trần Văn Bình</span></li>
                                        <li><span>3. Lê Văn Cường</span></li>
                                        <li><span>4. Phạm Văn Dũng</span></li>
                                    </ul>
                                </div>

                                <div class="group-card">
                                    <div class="group-header">BẢNG B</div>
                                    <ul class="group-players">
                                        <li>
                                            <span>1. Bùi Văn Khoa</span>
                                            <span class="badge badge-warning">⭐ #2</span>
                                        </li>
                                        <li><span>2. Đinh Văn Long</span></li>
                                        <li><span>3. Trương Văn Minh</span></li>
                                        <li><span>4. Lý Văn Nam</span></li>
                                    </ul>
                                </div>
                            </div>

                            <button class="btn btn-primary mt-2">💾 Lưu kết quả chia bảng</button>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: QUẢN LÝ TRẬN ĐẤU -->
                <div id="matches" class="tab-content">
                    <div class="card fade-in">
                        <div class="card-header">
                            <h3 class="card-title">🎾 Quản lý trận đấu</h3>
                            <div class="card-actions">
                                <button class="btn btn-primary btn-sm">➕ Tạo trận mới</button>
                                <button class="btn btn-success btn-sm">🔄 Tạo lịch tự động</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="match-list">
                                <!-- Match 1 - Completed -->
                                <div class="match-item">
                                    <div class="match-header">
                                        <div class="match-info">
                                            <div class="match-title">Trận 1 - Vòng bảng A</div>
                                            <div class="match-details">
                                                📅 20/01/2025 - 08:00 | 🏟️ Sân số 1 | 🎯 Nam đơn 18+<br>
                                                <span class="badge badge-success">Đã hoàn thành</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="match-players">
                                        <div class="player-side" style="background: linear-gradient(135deg, #4ADE80, #22C55E); color: white;">
                                            <div class="player-name">🏆 Nguyễn Văn An</div>
                                            <div style="font-size: 1.75rem; font-weight: 700; margin-top: 10px;">11 - 11</div>
                                        </div>
                                        <div class="vs-divider">VS</div>
                                        <div class="player-side">
                                            <div class="player-name">Trần Văn Bình</div>
                                            <div style="font-size: 1.75rem; font-weight: 700; margin-top: 10px;">7 - 5</div>
                                        </div>
                                    </div>
                                    <div style="margin-top: 1rem;">
                                        <button class="btn btn-secondary btn-sm">👁️ Chi tiết</button>
                                        <button class="btn btn-warning btn-sm">✏️ Sửa kết quả</button>
                                    </div>
                                </div>

                                <!-- Match 2 - Live -->
                                <div class="match-item" style="border-left-color: #FF6B6B;">
                                    <div class="match-header">
                                        <div class="match-info">
                                            <div class="match-title">Trận 2 - Vòng bảng A</div>
                                            <div class="match-details">
                                                📅 20/01/2025 - 09:00 | 🏟️ Sân số 2 | 🎯 Nam đơn 18+<br>
                                                <span class="badge badge-danger status-live">🔴 ĐANG DIỄN RA</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="match-players">
                                        <div class="player-side">
                                            <div class="player-name">Lê Văn Cường</div>
                                            <div class="score-input">
                                                <input type="number" value="9" min="0" max="30">
                                                <input type="number" value="11" min="0" max="30">
                                                <input type="number" value="8" min="0" max="30">
                                            </div>
                                        </div>
                                        <div class="vs-divider">VS</div>
                                        <div class="player-side">
                                            <div class="player-name">Phạm Văn Dũng</div>
                                            <div class="score-input">
                                                <input type="number" value="11" min="0" max="30">
                                                <input type="number" value="7" min="0" max="30">
                                                <input type="number" value="10" min="0" max="30">
                                            </div>
                                        </div>
                                    </div>
                                    <div style="margin-top: 1rem;">
                                        <button class="btn btn-success">💾 Lưu tỷ số</button>
                                        <button class="btn btn-primary">🏁 Kết thúc trận</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: BẢNG XẾP HẠNG -->
                <div id="rankings" class="tab-content">
                    <div class="card fade-in">
                        <div class="card-header">
                            <h3 class="card-title">🏅 Bảng xếp hạng giải đấu</h3>
                            <div class="card-actions">
                                <button class="btn btn-primary btn-sm">📊 Xuất báo cáo</button>
                                <button class="btn btn-success btn-sm">📄 In bảng</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <h4 style="margin: 0 0 1.5rem 0; font-weight: 700;">Nam đơn 18+ - Bảng xếp hạng chung</h4>
                            <div style="overflow-x: auto;">
                                <table class="rankings-table">
                                    <thead>
                                        <tr>
                                            <th>Hạng</th>
                                            <th>Vận động viên</th>
                                            <th>Bảng</th>
                                            <th>Trận</th>
                                            <th>Thắng</th>
                                            <th>Thua</th>
                                            <th>Tỷ lệ</th>
                                            <th>Điểm</th>
                                            <th>Set</th>
                                            <th>Hiệu số</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="rank-medal rank-1">1</span></td>
                                            <td><strong>Nguyễn Văn An</strong></td>
                                            <td>Bảng A</td>
                                            <td>5</td>
                                            <td>5</td>
                                            <td>0</td>
                                            <td>100%</td>
                                            <td><strong>15</strong></td>
                                            <td>10/0</td>
                                            <td>+110</td>
                                        </tr>
                                        <tr>
                                            <td><span class="rank-medal rank-2">2</span></td>
                                            <td><strong>Bùi Văn Khoa</strong></td>
                                            <td>Bảng B</td>
                                            <td>5</td>
                                            <td>4</td>
                                            <td>1</td>
                                            <td>80%</td>
                                            <td><strong>12</strong></td>
                                            <td>9/2</td>
                                            <td>+85</td>
                                        </tr>
                                        <tr>
                                            <td><span class="rank-medal rank-3">3</span></td>
                                            <td><strong>Ngô Văn Sơn</strong></td>
                                            <td>Bảng C</td>
                                            <td>5</td>
                                            <td>4</td>
                                            <td>1</td>
                                            <td>80%</td>
                                            <td><strong>12</strong></td>
                                            <td>8/3</td>
                                            <td>+72</td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>Hà Văn Chiến</td>
                                            <td>Bảng D</td>
                                            <td>5</td>
                                            <td>4</td>
                                            <td>1</td>
                                            <td>80%</td>
                                            <td>12</td>
                                            <td>8/3</td>
                                            <td>+68</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
        }

        // Show config tab
        function showConfigTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active from all buttons
            document.querySelectorAll('.config-tab').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            
            // Add active to clicked button
            event.target.classList.add('active');
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Step navigation
        function nextStep(stepNumber) {
            const steps = document.querySelectorAll('.step');
            steps.forEach((step, index) => {
                step.classList.remove('active', 'completed');
                if (index + 1 < stepNumber) {
                    step.classList.add('completed');
                } else if (index + 1 === stepNumber) {
                    step.classList.add('active');
                }
            });
            
            window.scrollTo({ top: 300, behavior: 'smooth' });
        }

        // Add content
        function addContent() {
            const name = document.getElementById('contentName').value;
            const type = document.getElementById('contentType').value;
            const age = document.getElementById('contentAge').value;
            const maxPlayers = document.getElementById('contentMaxPlayers').value;
            const prize = document.getElementById('contentPrize').value;

            if (!name || !maxPlayers) {
                alert('Vui lòng điền đầy đủ thông tin!');
                return;
            }

            const contentList = document.getElementById('contentList');
            const newContent = document.createElement('div');
            newContent.className = 'content-item';
            newContent.innerHTML = `
                <h4>${name}</h4>
                <p><strong>Loại:</strong> ${type} | <strong>Độ tuổi:</strong> ${age} | <strong>Số VĐV:</strong> ${maxPlayers} | <strong>Giải thưởng:</strong> ${parseInt(prize).toLocaleString('vi-VN')} VNĐ</p>
                <button class="btn btn-secondary btn-sm">✏️ Chỉnh sửa</button>
                <button class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">🗑️ Xóa</button>
            `;
            contentList.appendChild(newContent);

            // Clear
            document.getElementById('contentName').value = '';
            document.getElementById('contentMaxPlayers').value = '';
            document.getElementById('contentPrize').value = '';
        }

        // Add round
        function addRound() {
            const name = document.getElementById('roundName').value;
            const date = document.getElementById('roundDate').value;
            const time = document.getElementById('roundTime').value;

            if (!name || !date || !time) {
                alert('Vui lòng điền đầy đủ thông tin!');
                return;
            }

            alert('Đã thêm vòng đấu: ' + name);
            
            // Clear
            document.getElementById('roundName').value = '';
            document.getElementById('roundDate').value = '';
            document.getElementById('roundTime').value = '';
        }

        // Add court
        function addCourt() {
            const name = document.getElementById('courtName').value;

            if (!name) {
                alert('Vui lòng nhập tên sân!');
                return;
            }

            alert('Đã thêm sân: ' + name);
            
            // Clear
            document.getElementById('courtName').value = '';
        }

        // Item card selection
        document.addEventListener('click', (e) => {
            if (e.target.closest('.item-card')) {
                e.target.closest('.item-card').classList.toggle('selected');
            }
        });

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

        console.log('Tournament Config Dashboard v2 Loaded');
    </script>
</body>
</html>
