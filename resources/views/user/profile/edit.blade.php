@extends('layouts.front')

@section('content')
<style>
    .profile-container {
        padding: clamp(20px, 3vw, 40px);
        max-width: 900px;
        margin: 0 auto;
    }

    .profile-header {
        margin-bottom: clamp(30px, 5vw, 50px);
    }

    .profile-header h2 {
        font-size: clamp(1.8rem, 5vw, 2.5rem);
        font-weight: 700;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 10px;
    }

    .profile-header p {
        color: #6b7280;
        font-size: clamp(0.9rem, 2vw, 1rem);
    }

    .profile-card {
        background: white;
        border-radius: 15px;
        padding: clamp(20px, 3vw, 30px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
    }

    .profile-card h4 {
        font-size: 1.2rem;
        color: #1f2937;
        margin-bottom: 20px;
        font-weight: 700;
        padding-bottom: 15px;
        border-bottom: 1px solid #f3f4f6;
    }

    .avatar-section {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .avatar-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 700;
        overflow: hidden;
        flex-shrink: 0;
    }

    .avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-size: 1rem;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #00D9B5;
        box-shadow: 0 0 0 3px rgba(0, 217, 181, 0.1);
    }

    .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 20px;
        padding-right: 40px;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        font-size: 0.95rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 217, 181, 0.3);
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        text-decoration: none;
        display: inline-block;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .btn-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-danger:hover {
        background: #fecaca;
    }

    .text-danger {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 5px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .file-input {
        display: none;
    }

    .file-label {
        display: inline-block;
        padding: 10px 20px;
        background: #f3f4f6;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: background 0.3s;
    }

    .file-label:hover {
        background: #e5e7eb;
    }

    .oauth-notice {
        background: #fef3c7;
        color: #92400e;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .avatar-section {
            flex-direction: column;
            align-items: flex-start;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .avatar-actions {
            width: 100%;
        }

        .avatar-actions .btn {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="profile-container">
    <div class="profile-header">
        <h2>Chỉnh Sửa Hồ Sơ</h2>
        <p>Cập nhật thông tin cá nhân của bạn</p>
    </div>

    {{-- Avatar Section --}}
    <div class="profile-card">
        <h4>Ảnh Đại Diện</h4>
        <form action="{{ route('user.profile.avatar') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="avatar-section">
                <div class="avatar-preview" id="avatar-preview">
                    @if($user->getAvatarUrl())
                        <img src="{{ $user->getAvatarUrl() }}" alt="Avatar">
                    @else
                        {{ $user->getInitials() }}
                    @endif
                </div>
                <div class="avatar-actions">
                    <input type="file" name="avatar" id="avatar-input" class="file-input" accept="image/jpeg,image/png,image/webp">
                    <label for="avatar-input" class="file-label">Chọn Ảnh Mới</label>
                    @if($user->getAvatarUrl())
                        <button type="submit" name="remove_avatar" value="1" class="btn btn-danger">Xóa Ảnh</button>
                    @endif
                    <button type="submit" class="btn btn-primary">Lưu Ảnh</button>
                </div>
            </div>
            @error('avatar')
                <p class="text-danger">{{ $message }}</p>
            @enderror
            <p style="color: #6b7280; font-size: 0.85rem; margin-top: 15px;">Chỉ chấp nhận file JPG, PNG, WebP. Kích thước tối đa 2MB.</p>
        </form>
    </div>

    {{-- Basic Info Section --}}
    <div class="profile-card">
        <h4>Thông Tin Cơ Bản</h4>
        <form action="{{ route('user.profile.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Họ và Tên *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required minlength="3">
                @error('name')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Địa Chỉ</label>
                <input type="text" name="location" class="form-control" value="{{ old('location', $user->location) }}" placeholder="Quận/Huyện, Phường/Xã...">
                @error('location')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Tỉnh/Thành Phố</label>
                <select name="province_id" class="form-control form-select">
                    <option value="">-- Chọn tỉnh/thành phố --</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province->id }}" {{ old('province_id', $user->province_id) == $province->id ? 'selected' : '' }}>
                            {{ $province->name }}
                        </option>
                    @endforeach
                </select>
                @error('province_id')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Lưu Thông Tin</button>
        </form>
    </div>

    {{-- Email Section --}}
    <div class="profile-card">
        <h4>Địa Chỉ Email</h4>
        @if(!$hasPassword)
            <div class="oauth-notice">
                Bạn đang đăng nhập bằng tài khoản xã hội. Vui lòng đặt mật khẩu trước khi thay đổi email.
            </div>
        @endif
        <form action="{{ route('user.profile.email') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required {{ !$hasPassword ? 'disabled' : '' }}>
                @error('email')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Mật Khẩu Hiện Tại (để xác nhận) *</label>
                <input type="password" name="current_password_email" class="form-control" placeholder="Nhập mật khẩu hiện tại" {{ !$hasPassword ? 'disabled' : '' }}>
                @error('current_password_email')
                    <p class="text-danger">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary" {{ !$hasPassword ? 'disabled' : '' }}>Cập Nhật Email</button>
        </form>
    </div>

    {{-- Password Section --}}
    <div class="profile-card">
        <h4>{{ $hasPassword ? 'Đổi Mật Khẩu' : 'Đặt Mật Khẩu' }}</h4>
        @if(!$hasPassword)
            <div class="oauth-notice">
                Bạn chưa có mật khẩu. Vui lòng đặt mật khẩu để bảo mật tài khoản.
            </div>
        @endif
        <form action="{{ route('user.profile.password') }}" method="POST">
            @csrf
            @method('PUT')
            @if($hasPassword)
                <div class="form-group">
                    <label class="form-label">Mật Khẩu Hiện Tại *</label>
                    <input type="password" name="current_password" class="form-control" placeholder="Nhập mật khẩu hiện tại">
                    @error('current_password')
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                </div>
            @endif
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Mật Khẩu Mới *</label>
                    <input type="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự">
                    @error('password')
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Xác Nhận Mật Khẩu Mới *</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu mới">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">{{ $hasPassword ? 'Đổi Mật Khẩu' : 'Đặt Mật Khẩu' }}</button>
        </form>
    </div>

</div>

@endsection

@section('js')
<script>
    // Avatar preview - XSS safe implementation
    document.getElementById('avatar-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                toastr.error('Kích thước file tối đa là 2MB.');
                this.value = '';
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                toastr.error('Chỉ chấp nhận file JPG, PNG, WebP.');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatar-preview');
                // XSS safe: create image element instead of using innerHTML
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Avatar';
                preview.innerHTML = '';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
