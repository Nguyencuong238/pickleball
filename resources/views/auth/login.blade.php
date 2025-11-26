<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập / Đăng ký - onePickleball.vn</title>
    <meta name="description" content="Đăng nhập hoặc đăng ký tài khoản onePickleball">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/styles-extended.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav container">
            <div class="nav-brand">
                <a href="/" class="sidebar-brand">
                    <img src="{{ asset('assets/images/logo.jpeg') }}" alt="OnePickleball" width="80px">
                </a>
            </div>
        </nav>
    </header>

    <!-- Auth Section -->
    <section class="auth-section">
        <div class="auth-background"></div>
        <div class="auth-container">
            <div class="auth-box">
                <!-- Auth Tabs -->
                <div class="auth-tabs">
                    <button class="auth-tab active" data-tab="login">Đăng nhập</button>
                    <button class="auth-tab" data-tab="register">Đăng ký</button>
                </div>

                <!-- Login Form -->
                <div class="auth-form-container" id="login-form">
                    <div class="auth-header">
                        <h1 class="auth-title">Chào mừng trở lại!</h1>
                        <p class="auth-description">Đăng nhập để tiếp tục với onePickleball</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger" style="padding: 12px; background: #fee; border: 1px solid #fcc; border-radius: 4px; color: #c00; margin-bottom: 15px;">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success" style="padding: 12px; background: #efe; border: 1px solid #cfc; border-radius: 4px; color: #080; margin-bottom: 15px;">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form class="auth-form" method="POST" action="{{ route('login.submit') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-input" name="email" placeholder="Nhập email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mật khẩu</label>
                            <div class="password-input-wrapper">
                                <input type="password" class="form-input" name="password" placeholder="Nhập mật khẩu" required>
                                <button type="button" class="password-toggle">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <span class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox" class="form-checkbox" name="remember">
                                <span>Ghi nhớ đăng nhập</span>
                            </label>
                            <a href="#" class="forgot-password">Quên mật khẩu?</a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg">Đăng nhập</button>
                    </form>

                    <div class="divider">
                        <span>hoặc</span>
                    </div>

                    <div class="social-login">
                        <a href="{{ route('auth.facebook') }}" class="social-btn facebook-btn">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                 <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                             </svg>
                             Facebook
                        </a>
                        <a href="{{ route('auth.google') }}" class="social-btn google-btn">
                             <svg viewBox="0 0 24 24" fill="currentColor">
                                 <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                 <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                 <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                 <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                             </svg>
                             Google
                        </a>
                        {{-- <button class="social-btn zalo-btn">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 14.79c-.28.4-.85.77-1.58.77-.16 0-.33-.02-.5-.06-1.72-.42-3.46-1.51-4.91-3.06-1.45-1.56-2.39-3.38-2.65-5.13-.03-.17-.04-.34-.04-.5 0-.73.34-1.33.71-1.64.37-.32.88-.51 1.42-.51.12 0 .24.01.35.03.61.09 1.15.64 1.42 1.44l.59 1.76c.14.43.11.89-.08 1.28-.18.39-.51.7-.9.86l-.28.11c.12.28.29.56.52.84.48.57 1.08 1.12 1.76 1.64.28.21.55.38.82.5l.11-.28c.16-.39.47-.72.86-.9.39-.19.85-.22 1.28-.08l1.76.59c.8.27 1.35.81 1.44 1.42.02.11.03.23.03.35 0 .54-.19 1.05-.51 1.42z"/>
                            </svg>
                            Zalo
                        </button> --}}
                    </div>
                </div>

                <!-- Register Form -->
                <div class="auth-form-container" id="register-form" style="display: none;">
                    <div class="auth-header">
                        <h1 class="auth-title">Tạo tài khoản mới</h1>
                        <p class="auth-description">Tham gia cộng đồng Pickleball lớn nhất Việt Nam</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger" style="padding: 12px; background: #fee; border: 1px solid #fcc; border-radius: 4px; color: #c00; margin-bottom: 15px;">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success" style="padding: 12px; background: #efe; border: 1px solid #cfc; border-radius: 4px; color: #080; margin-bottom: 15px;">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form class="auth-form" method="POST" action="{{ route('register.submit') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" class="form-input" name="name" placeholder="Nhập họ và tên đầy đủ" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-input" name="email" placeholder="Nhập địa chỉ email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-input" name="phone" placeholder="Nhập số điện thoại" value="{{ old('phone') }}">
                            @error('phone')
                                <span class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mật khẩu</label>
                            <div class="password-input-wrapper">
                                <input type="password" class="form-input" name="password" placeholder="Tạo mật khẩu" required>
                                <button type="button" class="password-toggle">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="strength-bar">
                                    <div class="strength-progress"></div>
                                </div>
                                <span class="strength-text">Mật khẩu mạnh</span>
                            </div>
                            @error('password')
                                <span class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Xác nhận mật khẩu</label>
                            <div class="password-input-wrapper">
                                <input type="password" class="form-input" name="password_confirmation" placeholder="Nhập lại mật khẩu" required>
                                <button type="button" class="password-toggle">
                                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Bạn là:</label>
                            <div style="display: flex; gap: 16px; margin-top: 8px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; flex: 1;">
                                    <input type="radio" name="role_type" value="user" checked required style="cursor: pointer;">
                                    <span>Người dùng</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; flex: 1;">
                                    <input type="radio" name="role_type" value="court_owner" required style="cursor: pointer;">
                                    <span>Chủ sân</span>
                                </label>
                            </div>
                            @error('role_type')
                                <span class="text-danger" style="color: #dc3545; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" class="form-checkbox" name="terms" required>
                                <span>Tôi đồng ý với <a href="#">Điều khoản dịch vụ</a> và <a href="#">Chính sách bảo mật</a></span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg">Đăng ký</button>
                    </form>

                    <div class="divider">
                        <span>hoặc</span>
                    </div>

                    <div class="social-login">
                         <button class="social-btn facebook-btn">
                             <svg viewBox="0 0 24 24" fill="currentColor">
                                 <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                             </svg>
                             Facebook
                         </button>
                         <a href="{{ route('auth.google') }}" class="social-btn google-btn">
                             <svg viewBox="0 0 24 24" fill="currentColor">
                                 <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                 <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                 <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                 <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                             </svg>
                             Google
                         </a>
                         <button class="social-btn zalo-btn">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 14.79c-.28.4-.85.77-1.58.77-.16 0-.33-.02-.5-.06-1.72-.42-3.46-1.51-4.91-3.06-1.45-1.56-2.39-3.38-2.65-5.13-.03-.17-.04-.34-.04-.5 0-.73.34-1.33.71-1.64.37-.32.88-.51 1.42-.51.12 0 .24.01.35.03.61.09 1.15.64 1.42 1.44l.59 1.76c.14.43.11.89-.08 1.28-.18.39-.51.7-.9.86l-.28.11c.12.28.29.56.52.84.48.57 1.08 1.12 1.76 1.64.28.21.55.38.82.5l.11-.28c.16-.39.47-.72.86-.9.39-.19.85-.22 1.28-.08l1.76.59c.8.27 1.35.81 1.44 1.42.02.11.03.23.03.35 0 .54-.19 1.05-.51 1.42z"/>
                            </svg>
                            Zalo
                        </button>
                    </div>
                </div>

                <!-- Auth Benefits -->
                <div class="auth-benefits">
                    <h3>Lợi ích khi tham gia</h3>
                    <div class="benefit-list">
                        <div class="benefit-item">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <span>Đăng ký giải đấu dễ dàng</span>
                        </div>
                        <div class="benefit-item">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <span>Tìm sân và đối thủ phù hợp</span>
                        </div>
                        <div class="benefit-item">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <span>Theo dõi thành tích cá nhân</span>
                        </div>
                        <div class="benefit-item">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            <span>Nhận thông báo ưu đãi đặc biệt</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Auth Side Info -->
            <div class="auth-side-info">
                <div class="auth-side-content">
                    <div class="side-icon">
                        <svg viewBox="0 0 120 120" fill="none">
                            <circle cx="60" cy="60" r="55" stroke="#00D9B5" stroke-width="3" fill="rgba(0,217,181,0.1)"/>
                            <circle cx="60" cy="60" r="40" fill="#00D9B5"/>
                            <path d="M40 60 L55 75 L80 45" stroke="white" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                        </svg>
                    </div>
                    <h2>Kết nối với cộng đồng Pickleball</h2>
                    <p>Tham gia 2,500+ vận động viên đam mê Pickleball tại Việt Nam</p>
                    
                    <div class="auth-stats">
                        <div class="stat-box">
                            <h3>2,500+</h3>
                            <p>Thành viên</p>
                        </div>
                        <div class="stat-box">
                            <h3>50+</h3>
                            <p>Sân thi đấu</p>
                        </div>
                        <div class="stat-box">
                            <h3>120+</h3>
                            <p>Giải đấu/năm</p>
                        </div>
                    </div>

                    
                </div>
            </div>
        </div>
    </section>

    <script>
        // Tab Switching
        const tabs = document.querySelectorAll('.auth-tab');
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                const tabName = tab.getAttribute('data-tab');
                if (tabName === 'login') {
                    loginForm.style.display = 'block';
                    registerForm.style.display = 'none';
                } else {
                    loginForm.style.display = 'none';
                    registerForm.style.display = 'block';
                }
            });
        });

        // Password Toggle
        const passwordToggles = document.querySelectorAll('.password-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                const input = toggle.previousElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                } else {
                    input.type = 'password';
                }
            });
        });
    </script>
</body>
</html>
