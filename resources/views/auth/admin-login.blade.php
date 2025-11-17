<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Quản Trị - Pickleball Booking</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #0B3B40 0%, #10CAB7 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .admin-login-wrapper {
            width: 100%;
            max-width: 450px;
        }

        .admin-header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        .admin-header h1 {
            font-size: 32px;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .admin-header p {
            font-size: 15px;
            opacity: 0.9;
        }

        .admin-login-box {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .form-group {
            margin-bottom: 24px;
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #0B3B40;
            font-size: 15px;
        }

        .form-group input {
            padding: 14px 16px;
            border: 1.5px solid #e0e7e6;
            border-radius: 10px;
            font-size: 15px;
            transition: border-color 0.3s;
            background: #f9fffe;
        }

        .form-group input:focus {
            outline: none;
            border-color: #10CAB7;
            background: white;
            box-shadow: 0 0 0 3px rgba(16, 202, 183, 0.1);
        }

        .form-group input::placeholder {
            color: #999;
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c33;
        }

        .btn-admin-login {
            background: linear-gradient(90deg, #0B3B40, #10CAB7);
            color: white;
            padding: 14px 20px;
            border-radius: 12px;
            width: 100%;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 8px;
        }

        .btn-admin-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(11, 59, 64, 0.3);
        }

        .btn-admin-login:active {
            transform: translateY(0);
        }

        .admin-footer {
            text-align: center;
            margin-top: 24px;
            color: #666;
            font-size: 14px;
        }

        .admin-footer a {
            color: #10CAB7;
            text-decoration: none;
            font-weight: 600;
        }

        .admin-footer a:hover {
            text-decoration: underline;
        }

        .security-badge {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #eee;
        }

        .lock-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <div class="admin-login-wrapper">
        <div class="admin-header">
            <h1>Bảng Điều Khiển Quản Trị</h1>
            <p>Hệ Thống Đặt Sân Pickleball</p>
        </div>

        <div class="admin-login-box">
            @if ($errors->any())
                <div class="error-message">
                    <strong>{{ $errors->first() }}</strong>
                </div>
            @endif

            @if (session('success'))
                <div class="error-message" style="background: #efe; color: #3c3; border-left-color: #3c3;">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="/admin/login">
                @csrf

                <div class="form-group">
                    <label for="email">Địa Chỉ Email</label>
                    <input 
                        type="email" 
                        id="email"
                        name="email" 
                        placeholder="admin@example.com"
                        value="{{ old('email') }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Mật Khẩu</label>
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        placeholder="••••••••"
                        required
                    >
                </div>

                <button type="submit" class="btn-admin-login">Đăng Nhập Quản Trị</button>

                <div class="admin-footer">
                    Không phải quản trị viên? <a href="/">Quay Lại Trang Chủ</a>
                </div>

                <div class="security-badge">
                    <div class="lock-icon">🔒</div>
                    Truy Cập Quản Trị An Toàn
                </div>
            </form>
        </div>
    </div>
</body>

</html>
