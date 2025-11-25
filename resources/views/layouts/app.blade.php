<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - onePickleball</title>
    <link rel="icon" href="{{asset('assets/images/logo.jpeg')}}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #00D9B5;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            line-height: 1.6;
        }

        .sidebar {
            background-color: var(--sidebar-bg);
            color: #fff;
            min-height: 100vh;
            padding: 20px 0;
            position: fixed;
            width: 250px;
            left: 0;
            top: 0;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar .brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar .brand h4 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 700;
            font-size: clamp(1rem, 2vw, 1.5rem);
        }

        .sidebar .nav-link {
            color: #cbd5e1;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            margin: 0 10px;
            border-radius: 0.5rem;
            font-size: 0.95rem;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: var(--sidebar-hover);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }

        .main-content {
            margin-left: 250px;
            padding: clamp(15px, 3vw, 30px);
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }

        .topbar {
            background-color: #fff;
            padding: clamp(12px, 2vw, 20px) clamp(15px, 3vw, 30px);
            margin-bottom: clamp(20px, 3vw, 30px);
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .topbar h2 {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: 600;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background-color: #f1f5f9;
            border-radius: 9999px;
            cursor: pointer;
            min-width: fit-content;
        }

        .user-menu small {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            min-width: 36px;
            background-color: var(--primary-color);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #1e293b;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #00b8a0;
            border-color: #00b8a0;
        }

        .btn-danger {
            background-color: #ef4444;
            border-color: #ef4444;
            color: #fff;
        }

        .btn-danger:hover {
            background-color: #dc2626;
            border-color: #dc2626;
        }

        .sidebar-toggle {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            background: none;
            border: none;
            color: #1e293b;
            padding: 0;
            margin-right: 10px;
        }

        .sidebar-toggle:hover {
            color: var(--primary-color);
        }

        /* Tablet Responsiveness */
        @media (max-width: 1024px) {
            .sidebar {
                width: 220px;
            }

            .main-content {
                margin-left: 220px;
            }

            .sidebar .nav-link {
                padding: 10px 16px;
                font-size: 0.9rem;
            }

            .user-menu small {
                max-width: 100px;
            }
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                width: 260px;
                transform: translateX(-100%);
                position: fixed;
                z-index: 1050;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: clamp(12px, 2vw, 20px);
            }

            .topbar {
                padding: clamp(10px, 2vw, 15px);
                margin-bottom: clamp(15px, 2vw, 20px);
            }

            .topbar h2 {
                font-size: clamp(1.2rem, 4vw, 1.8rem);
            }

            .topbar-right {
                width: 100%;
                justify-content: flex-end;
            }

            .sidebar-toggle {
                display: inline-flex;
            }

            .user-menu {
                padding: 6px 12px;
            }

            .user-menu small {
                display: none;
            }

            .user-avatar {
                width: 32px;
                height: 32px;
                min-width: 32px;
            }
        }

        /* Small Mobile */
        @media (max-width: 480px) {
            .sidebar {
                width: 240px;
            }

            .main-content {
                padding: 10px;
            }

            .topbar {
                padding: 10px;
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar-right {
                width: 100%;
                justify-content: space-between;
            }

            .btn-primary,
            .btn-danger {
                font-size: 0.85rem;
                padding: 8px 12px;
            }

            h2 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="brand">
            <h4><i class="fas fa-badminton"></i> Bảng Điều Khiển</h4>
        </div>

        <nav>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i> Trang Chủ
            </a>

            <hr style="border-color: rgba(255,255,255,0.2); margin: 15px 0;">
            
            <p style="padding: 0 20px; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">Quản Lý</p>

            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> Người Dùng
            </a>

            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="fas fa-newspaper"></i> Danh mục
            </a>

            <a href="{{ route('admin.news.index') }}" class="nav-link {{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                <i class="fas fa-newspaper"></i> Tin Tức
            </a>

            <a href="{{ route('admin.pages.index') }}" class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i> Page
            </a>

            <a href="{{ route('admin.stadiums.index') }}" class="nav-link {{ request()->routeIs('admin.stadiums.*') ? 'active' : '' }}">
                <i class="fas fa-building"></i> Sân
            </a>

            <a href="{{ route('admin.tournaments.index') }}" class="nav-link {{ request()->routeIs('admin.tournaments.*') ? 'active' : '' }}">
                <i class="fas fa-trophy"></i> Giải Đấu
            </a>

            <hr style="border-color: rgba(255,255,255,0.1); margin: 15px 0;">

            <a href="{{ route('home') }}" class="nav-link" target="blank">
                <i class="fas fa-home"></i> Quay Lại Trang Chính
            </a>

            <form method="POST" action="{{ route('logout') }}" style="padding: 12px 20px; margin: 10px 0;">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm w-100">
                    <i class="fas fa-sign-out-alt"></i> Đăng Xuất
                </button>
            </form>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div>
                <i class="fas fa-bars sidebar-toggle" id="sidebarToggle"></i>
                <h2 class="d-inline-block ms-2">@yield('title', 'Bảng Điều Khiển')</h2>
            </div>
            <div class="topbar-right">
                <div class="user-menu">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div>
                        <small style="color: #666;">{{ auth()->user()->name }}</small>
                        <br>
                        <small style="color: #999; font-size: 0.8rem;">
                            @forelse(auth()->user()->roles as $role)
                                @if($role->name == 'admin')
                                    Quản Trị Viên
                                @elseif($role->name == 'home_yard')
                                    Chủ Sân
                                @elseif($role->name == 'user')
                                    Người Dùng
                                @else
                                    {{ ucfirst($role->name) }}
                                @endif
                            @empty
                                Không Có Vai Trò
                            @endforelse
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });

        // Sidebar toggle for mobile
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                sidebar.classList.toggle('collapsed');
                sidebar.classList.toggle('show');
            });

            // Close sidebar when clicking outside (mobile only)
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                        sidebar.classList.remove('show');
                        sidebar.classList.add('collapsed');
                    }
                }
            });

            // Close sidebar when window resizes
            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('collapsed', 'show');
                }
            });
        }
    </script>
</body>
</html>
