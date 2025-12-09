<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Referee Dashboard') - OnePickleball</title>
    <link rel="icon" href="{{ asset('assets/images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tournament-styles.css') }}">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- jQuery -->
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

        /* Match Table */
        .matches-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 0.75rem;
        }

        .matches-table thead tr {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .matches-table th {
            padding: 1rem 1.25rem;
            text-align: left;
            font-weight: 700;
            white-space: nowrap;
            font-size: 0.95rem;
        }

        .matches-table th:first-child {
            border-radius: var(--radius-lg) 0 0 var(--radius-lg);
        }

        .matches-table th:last-child {
            border-radius: 0 var(--radius-lg) var(--radius-lg) 0;
        }

        .matches-table tbody tr {
            background: var(--bg-white);
            transition: all var(--transition);
        }

        .matches-table tbody tr:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-md);
        }

        .matches-table td {
            padding: 1rem 1.25rem;
        }

        .matches-table tbody tr td:first-child {
            border-radius: var(--radius-lg) 0 0 var(--radius-lg);
        }

        .matches-table tbody tr td:last-child {
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

        .badge-scheduled {
            background: var(--bg-light);
            color: var(--text-secondary);
        }

        .badge-in-progress {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-completed {
            background: #D1FAE5;
            color: #065F46;
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

        /* Match Detail Card */
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
                    <span>[MENU]</span>
                </button>
            </div>

            <nav class="sidebar-nav" id="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Trong Tai</div>
                    <a href="{{ route('referee.dashboard') }}" class="nav-item {{ request()->routeIs('referee.dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">[DASHBOARD]</span>
                        <span class="nav-text">Tong quan</span>
                    </a>
                    <a href="{{ route('referee.matches.index') }}" class="nav-item {{ request()->routeIs('referee.matches.*') ? 'active' : '' }}">
                        <span class="nav-icon">[MATCH]</span>
                        <span class="nav-text">Tran dau</span>
                    </a>
                </div>

                @if(auth()->user()->hasRole('home_yard'))
                <div class="nav-section">
                    <div class="nav-section-title">Vai Tro Khac</div>
                    <a href="{{ route('homeyard.overview') }}" class="nav-item">
                        <span class="nav-icon">[STADIUM]</span>
                        <span class="nav-text">Home Yard</span>
                    </a>
                </div>
                @endif

                <div class="nav-section">
                    <div class="nav-section-title">He Thong</div>
                    <a href="{{ route('home') }}" class="nav-item">
                        <span class="nav-icon">[HOME]</span>
                        <span class="nav-text">Trang chu</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="#" class="nav-item btn-logout"
                            onclick="event.preventDefault();this.closest('form').submit();">
                            <span class="nav-icon">[LOGOUT]</span>
                            <span class="nav-text">Dang xuat</span>
                        </a>
                    </form>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-header">
                <div class="header-search">
                    <h2>@yield('header', 'Referee Dashboard')</h2>
                </div>
                <div class="header-user">
                    <div class="user-avatar">{{ auth()->user()->getInitials() }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">Trong tai</div>
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

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        // Configure Toastr
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
