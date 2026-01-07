<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Xác Minh OPR') - OnePickleball</title>
    <link rel="icon" href="{{ asset('assets/images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tournament-styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-white);
            padding: 1.5rem;
            border-radius: var(--radius-xl);
            border: 2px solid var(--border-color);
            text-align: center;
            transition: all var(--transition);
        }

        .stat-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Request Table */
        .requests-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 0.75rem;
        }

        .requests-table thead tr {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .requests-table th {
            padding: 1rem 1.25rem;
            text-align: left;
            font-weight: 700;
            white-space: nowrap;
            font-size: 0.95rem;
        }

        .requests-table th:first-child {
            border-radius: var(--radius-lg) 0 0 var(--radius-lg);
        }

        .requests-table th:last-child {
            border-radius: 0 var(--radius-lg) var(--radius-lg) 0;
        }

        .requests-table tbody tr {
            background: var(--bg-white);
            transition: all var(--transition);
        }

        .requests-table tbody tr:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-md);
        }

        .requests-table td {
            padding: 1rem 1.25rem;
        }

        .requests-table tbody tr td:first-child {
            border-radius: var(--radius-lg) 0 0 var(--radius-lg);
        }

        .requests-table tbody tr td:last-child {
            border-radius: 0 var(--radius-lg) var(--radius-lg) 0;
        }

        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: var(--radius-md);
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-pending {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-approved, .badge-success {
            background: #D1FAE5;
            color: #065F46;
        }

        .badge-rejected, .badge-danger {
            background: #FEE2E2;
            color: #991B1B;
        }

        .badge-warning {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-info {
            background: #DBEAFE;
            color: #1E40AF;
        }

        /* Filter Form */
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: var(--bg-white);
            border-radius: var(--radius-xl);
            border: 2px solid var(--border-color);
        }

        .filter-form select,
        .filter-form input {
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 0.95rem;
            min-width: 150px;
        }

        .filter-form select:focus,
        .filter-form input:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        /* Detail Card */
        .detail-card {
            background: var(--bg-white);
            border-radius: var(--radius-xl);
            border: 2px solid var(--border-color);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .detail-card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.5rem;
        }

        .detail-card-header h3 {
            margin: 0;
            font-size: 1.25rem;
        }

        .detail-card-body {
            padding: 1.5rem;
        }

        .detail-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-weight: 600;
            color: var(--text-primary);
        }

        /* Alert */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
        }

        .alert-info {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .alert-success {
            background: #D1FAE5;
            color: #065F46;
        }

        .alert-danger {
            background: #FEE2E2;
            color: #991B1B;
        }

        /* User Avatar */
        .user-avatar-lg {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 2rem;
        }

        /* Media Gallery */
        .media-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }

        .media-item {
            border-radius: var(--radius-md);
            overflow: hidden;
            border: 2px solid var(--border-color);
        }

        .media-item img,
        .media-item video {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        /* Links List */
        .links-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .links-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .links-list li:last-child {
            border-bottom: none;
        }

        .links-list a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .links-list a:hover {
            text-decoration: underline;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn-approve {
            background: #059669;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
        }

        .btn-approve:hover {
            background: #047857;
        }

        .btn-reject {
            background: #DC2626;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
        }

        .btn-reject:hover {
            background: #B91C1C;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filter-form {
                flex-direction: column;
            }

            .filter-form select,
            .filter-form input {
                width: 100%;
            }

            .detail-row {
                grid-template-columns: 1fr;
            }
        }

        .btn-logout:hover {
            background: var(--status-danger);
            color: #fff;
        }

        .sidebar-brand {
            text-decoration: none;
        }

        .sidebar-header {
            justify-content: center;
            padding: 0.5rem;
        }

        .sidebar-toggle {
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar-header {
                padding: 1rem 1.5rem;
                justify-content: space-between;
            }

            .sidebar-toggle {
                display: block;
            }

            .sidebar-nav.active {
                max-height: calc(100vh - 115px);
                overflow: auto;
            }
        }
    </style>
    @yield('css')
</head>

<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="/" class="sidebar-brand">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="OnePickleball" width="74px">
                </a>
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <span>☰</span>
                </button>
            </div>

            <nav class="sidebar-nav" id="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Xác Minh OPR</div>
                    <a href="{{ route('verifier.dashboard') }}" class="nav-item {{ request()->routeIs('verifier.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">📊</span>
                        <span class="nav-text">Tổng quan</span>
                    </a>
                    <a href="{{ route('verifier.requests.index') }}" class="nav-item {{ request()->routeIs('verifier.requests.*') ? 'active' : '' }}">
                        <span class="nav-icon">📋</span>
                        <span class="nav-text">Yêu cầu xác minh</span>
                    </a>
                </div>

                @if(auth()->user()->hasRole('referee'))
                <div class="nav-section">
                    <div class="nav-section-title">Vai Trò Khác</div>
                    <a href="{{ route('referee.dashboard') }}" class="nav-item">
                        <span class="nav-icon">🏸</span>
                        <span class="nav-text">Trọng Tài</span>
                    </a>
                </div>
                @endif

                @if(auth()->user()->hasRole('home_yard'))
                <div class="nav-section">
                    <div class="nav-section-title">Vai Trò Khác</div>
                    <a href="{{ route('homeyard.overview') }}" class="nav-item">
                        <span class="nav-icon">🏟️</span>
                        <span class="nav-text">Home Yard</span>
                    </a>
                </div>
                @endif

                <div class="nav-section">
                    <div class="nav-section-title">Hệ Thống</div>
                    <a href="{{ route('home') }}" class="nav-item">
                        <span class="nav-icon">🏠</span>
                        <span class="nav-text">Trang chủ</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="#" class="nav-item btn-logout"
                            onclick="event.preventDefault();this.closest('form').submit();">
                            <span class="nav-icon">🚪</span>
                            <span class="nav-text">Đăng xuất</span>
                        </a>
                    </form>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-header">
                <div class="header-search">
                    <h2>@yield('header', 'Xác Minh OPR')</h2>
                </div>
                <div class="header-user">
                    <div class="user-avatar">{{ auth()->user()->getInitials() }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">
                            @php
                                $roles = auth()->user()->getRoleNames();
                                $roleLabels = [
                                    'referee' => 'Trọng tài',
                                    'instructor' => 'Giảng viên',
                                    'expert_host' => 'Chuyên gia',
                                    'admin' => 'Admin',
                                ];
                                $displayRole = collect($roleLabels)->filter(fn($label, $key) => $roles->contains($key))->first() ?? 'Verifier';
                            @endphp
                            {{ $displayRole }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-area">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        @if (session('success'))
            toastr.success('{{ session('success') }}');
        @endif

        @if (session('error'))
            toastr.error('{{ session('error') }}');
        @endif

        function toggleSidebar() {
            const sidebarNav = document.getElementById('sidebar-nav');
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
            sidebarNav.classList.toggle('active');
        }
    </script>
    @yield('js')
</body>

</html>
