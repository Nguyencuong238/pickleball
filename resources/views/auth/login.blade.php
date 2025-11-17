<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>

    <style>
        body {
            margin: 0;
            background: #F1FBFA;
            font-family: Arial, sans-serif;
        }

        .auth-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .auth-box {
            width: 100%;
            max-width: 420px;
            padding: 32px;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .auth-title {
            font-size: 28px;
            font-weight: 700;
            color: #0B3B40;
            text-align: center;
        }

        .auth-subtitle {
            text-align: center;
            color: #4f5d5e;
            margin-bottom: 24px;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 18px;
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 6px;
            font-weight: 600;
            color: #0B3B40;
        }

        .form-group input {
            padding: 12px 14px;
            border: 1px solid #d4e7e6;
            border-radius: 10px;
            font-size: 15px;
        }

        .form-group input:focus {
            border-color: #10CAB7;
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(90deg, #0AC5B9, #07A8A1);
            color: #fff;
            padding: 12px 16px;
            border-radius: 12px;
            width: 100%;
            border: none;
            font-size: 16px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        .auth-switch {
            margin-top: 18px;
            text-align: center;
        }

        .auth-switch a {
            color: #0AC5B9;
            font-weight: 600;
            text-decoration: none;
        }
    </style>
</head>

<body>
<div class="auth-container">
    <div class="auth-box">

        <h2 class="auth-title">Đăng nhập</h2>
        <p class="auth-subtitle">Chào mừng bạn quay trở lại!</p>

        @if ($errors->any())
            <p style="color:red; text-align:center; margin-bottom:10px;">
                {{ $errors->first() }}
            </p>
        @endif

        <form method="POST" action="/login">
            @csrf

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Nhập email">
            </div>

            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password" placeholder="Nhập mật khẩu">
            </div>

            <button class="btn-primary">Đăng nhập</button>

            <p class="auth-switch">
                Chưa có tài khoản?
                <a href="/register">Đăng ký ngay</a>
            </p>
        </form>

    </div>
</div>
</body>
</html>
