<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @hasSection('seo')
        @yield('seo')
    @else
        <title>OnePickleball - Nền tảng Pickleball hàng đầu tại Việt Nam</title>
        <meta name="description" content="Nền tảng kết nối cộng đồng Pickleball hàng đầu tại Việt Nam. Tìm sân, đăng ký giải đấu và kết nối với hàng ngàn tay vợt.">
        <meta name="keywords" content="pickleball, sân pickleball, giải đấu pickleball, cộng đồng pickleball">
        <meta name="author" content="OnePickleball">
        <meta property="og:title" content="OnePickleball - Nền tảng Pickleball hàng đầu tại Việt Nam">
        <meta property="og:description" content="Nền tảng kết nối cộng đồng Pickleball hàng đầu tại Việt Nam. Tìm sân, đăng ký giải đấu và kết nối với hàng ngàn tay vợt.">
        <meta property="og:image" content="{{ asset('assets/images/logo.png') }}">
        <meta property="og:url" content="{{ request()->url() }}">
        <meta property="og:type" content="website">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="OnePickleball">
        <meta name="twitter:description" content="Nền tảng kết nối cộng đồng Pickleball hàng đầu tại Việt Nam.">
        <meta name="twitter:image" content="{{ asset('assets/images/logo.png') }}">
        <meta name="canonical" content="{{ request()->url() }}">
    @endif
    

    <link rel="icon" href="{{asset('assets/images/logo.png')}}">

    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/booking.css') }}">
    <!-- Toastr CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- jQuery (Required for Toastr) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

     @yield('css')
</head>
<style>
    :root {
        --primary-color: #00D9B5;
        --secondary-color: #0099CC;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html {
        font-size: 16px;
        scroll-behavior: smooth;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        color: #1e293b;
        line-height: 1.6;
    }

    /* Container tổng */
    .user-dropdown-container {
        position: relative;
        display: inline-block;
        font-family: 'Inter', sans-serif;
    }

    /* Phần profile (avatar + tên + mũi tên) */
    .user-profile {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: clamp(0.3rem, 1vw, 0.6rem);
        background-color: #f1f5f9; /* xám nhạt */
        border-radius: 9999px; /* tròn */
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .user-profile:hover {
        background-color: #e2e8f0; /* hover nhẹ */
    }

    /* Avatar tròn, màu gradient / đơn giản */
    .user-avatar {
        width: clamp(32px, 5vw, 36px);
        height: clamp(32px, 5vw, 36px);
        min-width: 32px;
        background-color: #3b82f6; /* xanh dương */
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: clamp(0.85rem, 1.5vw, 1rem);
    }

    /* Tên user */
    .user-info {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 500;
        font-size: clamp(0.8rem, 1.5vw, 0.9rem);
        color: #1e293b; /* slate-800 */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100px;
    }

    /* Dropdown */
    .dropdown-info {
        position: absolute;
        top: 100%;
        right: 0;
        background-color: #fff;
        min-width: 180px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-radius: 0.5rem;
        overflow: hidden;
        margin-top: 0.5rem;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.25s ease;
        z-index: 50;
    }

    /* Khi container active mới show dropdown */
    .user-dropdown-container.active .dropdown-info {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    /* Link trong dropdown */
    .dropdown-info .nav-link {
        display: block;
        padding: clamp(0.4rem, 1vw, 0.5rem) clamp(0.75rem, 2vw, 1rem);
        color: #1e293b;
        text-decoration: none;
        font-size: clamp(0.8rem, 1.2vw, 0.875rem);
        transition: background 0.2s;
    }

    .dropdown-info .nav-link:hover {
        background-color: #f1f5f9;
    }

    /* Icon switch nhỏ xíu */
    .dropdown-info .icon-switch2 {
        margin-right: 0.5rem;
        vertical-align: middle;
    }
    .color-white {
        color: #fff;
    }
    .bg-white {
        background-color: #fff;
    }

    @media (max-width: 1024px) {
        .nav-menu li {
            width: 100%;
            box-shadow: 0 1px 2px 0 rgb(24 204 174 / 20%);
            padding-right: 1rem;
            padding-left: 1rem;
        }
        .nav-item.dropdown > .dropdown-toggle::after {
            display: none !important;
        }
        .login-register-btn {
            box-shadow: none !important;
            display: flex;
            gap: 10px;
            padding: 10px 1rem !important;
        }
        .arrow-btn {
            position: absolute;
            right: 1rem;
            top: 10px;
            color: var(--text-secondary);
        }
        .dropdown-menu li {
            padding: 0;
        }
        .dropdown-menu li:last-child {
            border: 0;
            box-shadow: none;
        }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .user-profile {
            padding: 0.3rem;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            min-width: 32px;
        }
    }

    @media (max-width: 480px) {
        .user-avatar {
            width: 28px;
            height: 28px;
            min-width: 28px;
            font-size: 0.8rem;
        }

        .dropdown-info {
            min-width: 150px;
        }
    }

    /* Dropdown menu styles */
    .nav-item.dropdown {
        position: relative;
    }

    /* Invisible safe area - keeps hover active while moving to dropdown */
    .nav-item.dropdown::before {
        content: '';
        position: absolute;
        top: 100%;
        left: -5px;
        right: -5px;
        height: 20px;
        z-index: 1000;
        pointer-events: auto;
    }

    .dropdown-toggle {
        display: flex;
        align-items: center;
    }

    .nav-link.dropdown-toggle::after {
        display: none !important;
    }

    .dropdown-menu {
        position: absolute;
        top: calc(100% + 20px);
        left: 0;
        background-color: #fff;
        min-width: 200px;
        list-style: none;
        padding: 0.5rem 0;
        margin: 0;
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        visibility: hidden;
        opacity: 0;
        transform: translateY(-5px);
        z-index: 1000;
        pointer-events: none;
        transition: all 0.2s ease;
    }

    .nav-item.dropdown:hover .dropdown-menu,
    .nav-item.dropdown:hover::before {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }

    .nav-item.dropdown:hover .dropdown-menu {
        pointer-events: auto;
    }

    .dropdown-item {
        display: block;
        padding: 0.75rem 1rem;
        color: #1e293b;
        text-decoration: none;
        font-size: 0.875rem;
    }

    .dropdown-item:hover {
        background-color: #f1f5f9;
    }
    .nav-right {
        display: flex;
        gap: 1rem;
    }

    .user-dropdown-container .nav-link {
        width: 200px;
    }

    /* ========== Points Page Utilities ========== */

    /* Grid System */
    .row {
        display: flex;
        flex-wrap: wrap;
        margin: -0.5rem;
    }
    .row.g-3 {
        margin: -0.75rem;
    }
    .row.g-3 > [class*="col"] {
        padding: 0.75rem;
    }
    [class*="col-"] {
        padding: 0.5rem;
        width: 100%;
    }
    @media (min-width: 768px) {
        .col-md-4 { width: 33.333%; }
        .col-md-6 { width: 50%; }
        .col-md-8 { width: 66.666%; }
    }
    @media (min-width: 992px) {
        .col-lg-4 { width: 33.333%; }
    }

    /* Flexbox */
    .d-flex { display: flex; }
    .flex-wrap { flex-wrap: wrap; }
    .justify-content-between { justify-content: space-between; }
    .align-items-center { align-items: center; }
    .align-items-start { align-items: flex-start; }
    .gap-2 { gap: 0.5rem; }
    .gap-3 { gap: 1rem; }

    /* Spacing */
    .py-4 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
    .p-2 { padding: 0.5rem; }
    .mb-0 { margin-bottom: 0; }
    .mb-1 { margin-bottom: 0.25rem; }
    .mb-2 { margin-bottom: 0.5rem; }
    .mb-3 { margin-bottom: 1rem; }
    .mb-4 { margin-bottom: 1.5rem; }
    .mt-1 { margin-top: 0.25rem; }
    .mt-2 { margin-top: 0.5rem; }
    .mt-4 { margin-top: 1.5rem; }
    .me-1 { margin-right: 0.25rem; }

    /* Card */
    .card {
        background: var(--bg-white);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        overflow: hidden;
    }
    .card-header {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
    }
    .card-body {
        padding: 1rem;
    }
    .card-title {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }
    .card-text {
        color: var(--text-secondary);
    }
    .h-100 { height: 100%; }

    /* Buttons - Additional variants */
    .btn-success {
        background: #28a745;
        color: white;
    }
    .btn-success:hover {
        background: #218838;
    }
    .btn-dark {
        background: var(--bg-dark);
        color: white;
    }
    .btn-dark:hover {
        background: #333;
    }
    .btn-outline-secondary {
        background: transparent;
        border: 2px solid #6c757d;
        color: #6c757d;
    }
    .btn-outline-secondary:hover {
        background: #6c757d;
        color: white;
    }
    .btn-outline-warning {
        background: transparent;
        border: 2px solid #ffc107;
        color: #856404;
    }
    .btn-outline-warning:hover {
        background: #ffc107;
        color: var(--bg-dark);
    }

    /* Badge */
    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        font-size: var(--font-size-xs);
        font-weight: 600;
        border-radius: 0.25rem;
        color: white;
    }
    .bg-success { background-color: #28a745; }
    .bg-secondary { background-color: #6c757d; }
    .bg-info { background-color: #17a2b8; }
    .bg-danger { background-color: #dc3545; }
    .bg-primary { background-color: var(--primary-color); }
    .bg-dark { background-color: var(--bg-dark); }
    .bg-transparent { background-color: transparent; }

    /* Text Utilities */
    .text-white { color: white; }
    .text-muted { color: var(--text-secondary); }
    .text-primary { color: var(--primary-color); }
    .text-center { text-align: center; }
    .text-end { text-align: right; }
    .text-danger { color: #dc3545; }
    .text-warning { color: #856404; }
    .text-dark { color: var(--bg-dark); }
    .small { font-size: var(--font-size-sm); }
    .fw-bold { font-weight: 700; }
    .opacity-75 { opacity: 0.75; }

    /* Typography */
    .h3 { font-size: 1.75rem; font-weight: 600; margin: 0; }
    .h4 { font-size: 1.5rem; font-weight: 600; margin: 0; }
    .h5 { font-size: 1.25rem; font-weight: 600; margin: 0; }
    .h6 { font-size: 1rem; font-weight: 600; margin: 0; }

    /* Forms */
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    .form-control, .form-select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        font-size: 1rem;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        background-color: var(--bg-white);
        transition: border-color var(--transition-fast);
    }
    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: var(--primary-color);
    }
    .form-control.is-invalid, .form-select.is-invalid {
        border-color: #dc3545;
    }
    .form-text {
        display: block;
        margin-top: 0.25rem;
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }
    .invalid-feedback {
        display: block;
        margin-top: 0.25rem;
        font-size: var(--font-size-sm);
        color: #dc3545;
    }

    /* Breadcrumb */
    .breadcrumb {
        display: flex;
        flex-wrap: wrap;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .breadcrumb-item {
        font-size: var(--font-size-sm);
    }
    .breadcrumb-item + .breadcrumb-item::before {
        content: "/";
        padding: 0 0.5rem;
        color: var(--text-secondary);
    }
    .breadcrumb-item a {
        color: var(--primary-color);
    }
    .breadcrumb-item.active {
        color: var(--text-secondary);
    }

    /* Border & Rounded */
    .border {
        border: 1px solid var(--border-color);
    }
    .rounded {
        border-radius: var(--radius-sm);
    }

    /* Page Content - offset for fixed header */
    .page-content {
        padding-top: 110px;
        min-height: calc(100vh - 200px);
    }
</style>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav container">
            
            <div class="nav-brand">
                <a href="/" class="sidebar-brand">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="OnePickleball" width="74px">
                </a>
            </div>
            
            <ul class="nav-menu" id="nav-menu">
                <li><a href="/" class="nav-link @if(request()->routeIs('home')) active @endif">Trang chủ</a></li>
                <li class="nav-item dropdown">
                    <a href="{{ route('courts') }}" class="nav-link dropdown-toggle @if(request()->routeIs('courts') || request()->routeIs('social')) active @endif">Sân thi đấu</a>
                    <span class="arrow-btn mobile-only">▼</span>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('courts') }}" class="dropdown-item">Danh sách sân</a></li>
                        <li><a href="{{ route('social') }}" class="dropdown-item">Lịch thi đấu Social</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a href="{{ route('tournaments') }}" class="nav-link dropdown-toggle @if(request()->routeIs('tournaments') || request()->routeIs('ocr.matches.list')) active @endif">Giải đấu</a>
                    <span class="arrow-btn mobile-only">▼</span>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('ocr.matches.list') }}" class="dropdown-item">Trận đấu</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle">Bảng xếp hạng</a>
                    <span class="arrow-btn mobile-only">▼</span>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('athlete-leaderboard', ['type' => 'athlete_international']) }}" class="dropdown-item">BXH VĐV Toàn Cầu</a></li>
                        <li><a href="{{ route('athlete-leaderboard', ['type' => 'athlete_vietnam']) }}" class="dropdown-item">BXH VĐV Việt Nam</a></li>
                        <li><a href="{{ route('athlete-leaderboard', ['type' => 'athlete']) }}" class="dropdown-item">BXH Cộng Đồng</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a href="{{ route('ocr.index') }}" class="nav-link dropdown-toggle @if(request()->is('ocr*') && !request()->routeIs('ocr.matches.list') && !request()->routeIs('ocr.ocr-matches')) active @endif">Điểm trình OPR</a>
                    <span class="arrow-btn mobile-only">▼</span>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('ocr.index') }}" class="dropdown-item">Tổng quan OPRS</a></li>
                        <li><a href="{{ route('ocr.leaderboard') }}" class="dropdown-item">Bảng xếp hạng OPRS</a></li>
                        <li><a href="{{ route('ocr.ocr-matches') }}" class="dropdown-item">Trận đấu OCR</a></li>
                        @auth
                            <li><a href="{{ route('ocr.profile.id', auth()->user()->id) }}" class="dropdown-item">Hồ sơ của tôi</a></li>
                            <li><a href="{{ route('ocr.matches.index') }}" class="dropdown-item">Trận đấu của tôi</a></li>
                            <!-- <li><a href="{{ route('ocr.challenges.index') }}" class="dropdown-item">Challenge Center</a></li> -->
                        @endauth
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle @if(request()->routeIs('academy.*') || request()->routeIs('ocr.community.index') || request()->routeIs('quiz.*')) active @endif">Cộng đồng</a>
                    <span class="arrow-btn mobile-only">▼</span>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('skill-quiz.index') }}" class="dropdown-item">Đánh giá trình độ</a></li>
                        <li><a href="{{ route('clubs.index') }}" class="dropdown-item">Nhóm & CLB</a></li>
                        <li><a href="{{ route('instructors') }}" class="dropdown-item">Giảng viên</a></li>
                        <li><a href="{{ route('academy.referees.index') }}" class="dropdown-item">Trọng tài</a></li>                       
                        <li><a href="{{ route('course') }}" class="dropdown-item">Video Pickleball</a></li>
                        @auth
                            <!-- <li><a href="{{ route('quiz.index') }}" class="dropdown-item">Quiz Pickleball</a></li> -->
                            <li><a href="{{ route('ocr.community.index') }}" class="dropdown-item">Community Hub</a></li>
                        @endauth
                    </ul>
                </li>
                <li><a href="{{ route('news') }}" class="nav-link @if(request()->routeIs('news')) active @endif">Tin tức</a></li>

                @guest
                    <li class="mobile-only login-register-btn">
                        <a href="/login" class="btn btn-outline" style="border-color: #57e9dc;" >Đăng nhập</a>
                        <a href="/register" class="btn btn-primary" >Đăng ký</a>
                    </li>
                @endguest
            </ul>
            
            <div class="nav-right">
                @auth
                    <div class="user-dropdown-container">
                        <div class="user-profile" onclick="this.parentElement.classList.toggle('active')">
                            @php
                                $auth = auth()->user();
                                $name = $auth->name;
                                // Nếu bạn không dùng Spatie, lấy role từ cột 'type' hoặc 'role'
                                $role = $auth->type ?? 'user';
                                // Lấy ký tự đầu của tên trực tiếp
                                $userAvatar = strtoupper(mb_substr(trim($name), 0, 1));
                            @endphp
                            <div class="user-avatar">{{ $userAvatar }}</div>
                            <div class="user-info">
                                <div class="user-name">{{ $name }}</div>
                            </div>
                            <svg width="20" height="20" fill="#475569" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="dropdown-info">
                            {{-- <a href="{{ route('user.points.index') }}" class="nav-link">
                                ⭐ Kiếm điểm
                            </a> --}}
                            <a href="{{ route('user.wallet.index') }}" class="nav-link">
                                💰 Ví điểm ({{ auth()->user()->getPoints() }})
                            </a>
                            <a href="{{ route('user.referral.index') }}" class="nav-link">
                                💼 Giới thiệu người dùng
                            </a>
                            <a href="{{ route('ocr.profile', auth()->user()) }}" class="nav-link">
                                Hồ sơ OPRS
                            </a>
                            <a href="{{ route('user.profile.edit') }}" class="nav-link">
                                Chỉnh sửa hồ sơ
                            </a>
                            @if(auth()->check())
                                @if(auth()->user()->hasRole('admin'))
                                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                                        <i class="icon-admin"></i> Bảng điều khiển Admin
                                    </a>
                                @endif
                                @if(auth()->user()->hasRole('home_yard'))
                                    <a href="{{ route('homeyard.overview') }}" class="nav-link">
                                        <i class="icon-home"></i> Bảng điều khiển
                                    </a>
                                @endif
                                @if(auth()->user()->hasRole('user'))
                                    <a href="{{ route('user.dashboard') }}" class="nav-link">
                                        <i class="icon-user"></i> Bảng điều khiển Người dùng
                                    </a>
                                @endif
                                @if(auth()->user()->hasRole('referee'))
                                    <a href="{{ route('referee.dashboard') }}" class="nav-link">
                                        <i class="icon-user"></i> Bảng điều khiển Trọng tài
                                    </a>
                                @endif
                                @if(auth()->user()->canVerifyElo())
                                    <a href="{{ route('verifier.dashboard') }}" class="nav-link">
                                        <i class="icon-user"></i> Xác thực tài khoản OPR
                                    </a>
                                @endif
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a href="{{ route('logout') }}" class="nav-link" style="color: red;"
                                    onclick="event.preventDefault();this.closest('form').submit();">
                                    <i class="icon-switch2"></i> Đăng xuất
                                </a>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="nav-actions">
                        <a href="/login" class="btn btn-outline" style="border-color: #57e9dc;width: 120px;">Đăng nhập</a>
                        <a href="/register" class="btn btn-primary" style="width: 100px">Đăng ký</a>
                    </div>
                @endauth

                <button class="mobile-menu-toggle" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </nav>
    </header>

    <!-- Gender Update Modal for existing users without gender -->
    @auth
        @if(!auth()->user()->gender)
            <div id="gender-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div style="background: #fff; border-radius: 16px; max-width: 420px; width: 90%; padding: 2rem; box-shadow: 0 20px 60px rgba(0,0,0,0.3); animation: modalFadeIn 0.3s ease;">
                    <div style="text-align: center; margin-bottom: 1.5rem;">
                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #00D9B5, #0099CC); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">Cập nhật thông tin</h2>
                        <p style="color: #64748b; font-size: 0.95rem;">Vui lòng cho chúng tôi biết giới tính của bạn để cá nhân hoá trải nghiệm</p>
                    </div>

                    <form id="gender-update-form" method="POST" action="{{ route('user.profile.gender') }}">
                        @csrf
                        @method('PUT')

                        <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                            <label style="flex: 1; cursor: pointer;">
                                <input type="radio" name="gender" value="male" required style="display: none;" id="gender-male">
                                <div class="gender-option" data-gender="male" style="border: 2px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1rem; text-align: center; transition: all 0.2s ease;">
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">&#9794;</div>
                                    <div style="font-weight: 600; color: #1e293b;">Nam</div>
                                </div>
                            </label>
                            <label style="flex: 1; cursor: pointer;">
                                <input type="radio" name="gender" value="female" required style="display: none;" id="gender-female">
                                <div class="gender-option" data-gender="female" style="border: 2px solid #e2e8f0; border-radius: 12px; padding: 1.25rem 1rem; text-align: center; transition: all 0.2s ease;">
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">&#9792;</div>
                                    <div style="font-weight: 600; color: #1e293b;">Nữ</div>
                                </div>
                            </label>
                        </div>

                        <button type="submit" id="gender-submit-btn" disabled style="width: 100%; padding: 0.875rem 1.5rem; background: linear-gradient(135deg, #00D9B5, #0099CC); color: #fff; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease; opacity: 0.6;">
                            Xác nhận
                        </button>
                    </form>

                    <p style="text-align: center; color: #94a3b8; font-size: 0.8rem; margin-top: 1rem;">Thông tin này giúp đánh giá điểm trình OPR chính xác hơn</p>
                </div>
            </div>

            <style>
                @keyframes modalFadeIn {
                    from { opacity: 0; transform: scale(0.95); }
                    to { opacity: 1; transform: scale(1); }
                }
                .gender-option:hover {
                    border-color: #00D9B5 !important;
                    background: #f0fdfa;
                }
                .gender-option.selected {
                    border-color: #00D9B5 !important;
                    background: linear-gradient(135deg, rgba(0, 217, 181, 0.1), rgba(0, 153, 204, 0.1));
                }
                #gender-submit-btn:not(:disabled):hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(0, 217, 181, 0.3);
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const genderOptions = document.querySelectorAll('.gender-option');
                    const submitBtn = document.getElementById('gender-submit-btn');
                    const form = document.getElementById('gender-update-form');

                    genderOptions.forEach(option => {
                        option.addEventListener('click', function() {
                            genderOptions.forEach(opt => opt.classList.remove('selected'));
                            this.classList.add('selected');

                            const gender = this.dataset.gender;
                            document.getElementById('gender-' + gender).checked = true;

                            submitBtn.disabled = false;
                            submitBtn.style.opacity = '1';
                        });
                    });

                    form.addEventListener('submit', function(e) {
                        e.preventDefault();

                        submitBtn.disabled = true;
                        submitBtn.innerHTML = 'Đang xử lý...';

                        const formData = new FormData(form);

                        fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('gender-modal-overlay').style.display = 'none';
                                toastr.success(data.message);
                            } else {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = 'Xác nhận';
                                toastr.error('Có lỗi xảy ra. Vui lòng thử lại.');
                            }
                        })
                        .catch(error => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = 'Xác nhận';
                            toastr.error('Có lỗi xảy ra. Vui lòng thử lại.');
                        });
                    });
                });
            </script>
        @endif
    @endauth

    <!-- Hero Section -->
    @yield('content')

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-col footer-about">
                    <div class="footer-brand">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="OnePickleball" width="74px">
                    </div>
                    <p class="footer-description">
                        Nền tảng kết nối cộng đồng Pickleball hàng đầu tại Việt Nam. Tìm sân, đăng ký giải đấu và kết nối với hàng ngàn tay vợt.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-link" aria-label="Facebook">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-link" aria-label="Instagram">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-link" aria-label="YouTube">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                        </a>
                        <a href="#" class="social-link" aria-label="Zalo">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 14.79c-.28.4-.85.77-1.58.77-.16 0-.33-.02-.5-.06-1.72-.42-3.46-1.51-4.91-3.06-1.45-1.56-2.39-3.38-2.65-5.13-.03-.17-.04-.34-.04-.5 0-.73.34-1.33.71-1.64.37-.32.88-.51 1.42-.51.12 0 .24.01.35.03.61.09 1.15.64 1.42 1.44l.59 1.76c.14.43.11.89-.08 1.28-.18.39-.51.7-.9.86l-.28.11c.12.28.29.56.52.84.48.57 1.08 1.12 1.76 1.64.28.21.55.38.82.5l.11-.28c.16-.39.47-.72.86-.9.39-.19.85-.22 1.28-.08l1.76.59c.8.27 1.35.81 1.44 1.42.02.11.03.23.03.35 0 .54-.19 1.05-.51 1.42z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h4 class="footer-title">Dịch vụ</h4>
                    <ul class="footer-links">
                        <li><a href="#tournaments">Giải đấu</a></li>
                        <li><a href="#courts">Sân thi đấu</a></li>
                        <li><a href="#social">Thi đấu Social</a></li>
                        <li><a href="#news">Tin tức</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4 class="footer-title">Hỗ trợ</h4>
                    <ul class="footer-links">
                        <li><a href="#">Về chúng tôi</a></li>
                        <li><a href="/news?category=huong-dan-nen-tang&search=">Hướng dẫn nền tảng</a></li>
                        <li><a href="#">Câu hỏi thường gặp</a></li>
                        <li><a href="#">Điều khoản sử dụng</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4 class="footer-title">Liên hệ</h4>
                    <ul class="footer-contact">
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>125 Trần Thị Nơi, Phường Chánh Hưng, TP HCM</span>
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <span>0963 728 586</span>
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <span>hello@onepickleball.vn</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p class="footer-copyright">© 2025 OnePickleball.vn - All rights reserved</p>
                <div class="footer-legal">
                    <a href="#">Chính sách bảo mật</a>
                    <span>•</span>
                    <a href="#">Điều khoản dịch vụ</a>
                </div>
            </div>
        </div>
    </footer>
    <script src="{{ asset('assets/js/script.js') }}"></script>
    
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        // Configure Toastr
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "3000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            // Close user dropdown
            const userDropdownContainer = document.querySelector('.user-dropdown-container');
            if (userDropdownContainer && userDropdownContainer.classList.contains('active')) {
                if (!userDropdownContainer.contains(event.target)) {
                    userDropdownContainer.classList.remove('active');
                }
            }

            // Close mobile menu
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const navMenu = document.getElementById('nav-menu');
            
            if (mobileMenuToggle && navMenu && mobileMenuToggle.classList.contains('active')) {
                if (!mobileMenuToggle.contains(event.target) && !navMenu.contains(event.target)) {
                    mobileMenuToggle.click();
                }
            }
        });

        // Display session messages when DOM is ready
         document.addEventListener('DOMContentLoaded', function() {
             @if(session('success'))
                 toastr.success('{{ session('success') }}');
             @endif

             @if(session('error'))
                 toastr.error('{{ session('error') }}');
             @endif

             @if(session('warning'))
                 toastr.warning('{{ session('warning') }}');
             @endif

             @if(session('info'))
                 toastr.info('{{ session('info') }}');
             @endif

             // Handle validation errors
             @if($errors->any())
                 @foreach($errors->all() as $error)
                     toastr.error('{{ $error }}');
                 @endforeach
             @endif
         });
    </script>
    
    @yield('js')
</body>
</html>
