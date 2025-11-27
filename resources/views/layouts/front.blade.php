<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OnePickleball - Cộng Đồng Pickleball Việt Nam</title>
    <meta name="description" content="Nền tảng hàng đầu về Pickleball tại Việt Nam - Tin tức, giải đấu, sân thi đấu và cộng đồng">

    <link rel="icon" href="{{asset('assets/images/logo.png')}}">

    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tournaments.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/booking.css') }}">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .user-info {
            display: none;
        }

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
</style>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav container">
            <div class="nav-brand">
                <a href="/" class="sidebar-brand">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="OnePickleball" width="80px">
                </a>
            </div>
            
            <button class="mobile-menu-toggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <ul class="nav-menu">
                <li><a href="/" class="nav-link @if(request()->routeIs('home')) active @endif">Trang chủ</a></li>
                <li><a href="{{ route('tournaments') }}" class="nav-link @if(request()->routeIs('tournaments')) active @endif">Giải đấu</a></li>
                <li><a href="{{ route('courts') }}" class="nav-link @if(request()->routeIs('courts')) active @endif">Sân thi đấu</a></li>
                <li><a href="{{ route('social') }}" class="nav-link @if(request()->routeIs('social')) active @endif">Thi đấu Social</a></li>
                <li><a href="{{ route('news') }}" class="nav-link @if(request()->routeIs('news')) active @endif">Tin tức</a></li>
                <li><a href="#contact" class="nav-link">Liên hệ</a></li>
            </ul>
            
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
                        @if(auth()->check() && auth()->user()->hasRole('home_yard'))
                            <a href="{{ route('homeyard.overview') }}" class="nav-link">
                                <i class="icon-home"></i> Bảng điều khiển
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}" class="nav-link"
                                onclick="event.preventDefault();this.closest('form').submit();">
                                <i class="icon-switch2"></i> {{ __('Log Out') }}
                            </a>
                        </form>
                    </div>
                </div>
            @else
                <div class="nav-actions">
                    <a href="/login" class="btn btn-outline">Đăng nhập</a>
                    <a href="/register" class="btn btn-primary">Đăng ký</a>
                </div>
            @endauth

        </nav>
    </header>

    <!-- Hero Section -->
    @yield('content')

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-col footer-about">
                    <div class="footer-brand">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="OnePickleball" width="80px">
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
                        <li><a href="#">Liên hệ</a></li>
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
                            <span>123 Nguyễn Huệ, Q1, TP.HCM</span>
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <span>0901 234 567</span>
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <span>hello@onepickleball.vn</span>
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <span>T2-CN: 05:00 - 23:00</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p class="footer-copyright">© 2025 onePickleball.vn - All rights reserved</p>
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
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

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
