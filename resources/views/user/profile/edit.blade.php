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

    .btn-info {
        background: #dbeafe;
        color: #0c4a6e;
    }

    .btn-info:hover {
        background: #bfdbfe;
    }

    .text-danger {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 5px;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: white;
        border-radius: 15px;
        padding: clamp(20px, 3vw, 40px);
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            transform: translateY(30px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        border-bottom: 1px solid #f3f4f6;
        padding-bottom: 15px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.3rem;
        color: #1f2937;
        font-weight: 700;
    }

    .modal-close {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.5rem;
        color: #6b7280;
        transition: color 0.3s;
    }

    .modal-close:hover {
        color: #1f2937;
    }

    .permission-item {
        background: #f9fafb;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 15px;
        display: flex;
        gap: 12px;
    }

    .permission-item input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .permission-info {
        flex: 1;
    }

    .permission-info label {
        display: block;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 5px;
        cursor: pointer;
    }

    .permission-info p {
        margin: 0;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .modal-footer {
        border-top: 1px solid #f3f4f6;
        padding-top: 20px;
        margin-top: 20px;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .modal-footer .btn {
        padding: 10px 20px;
        font-size: 0.9rem;
    }

    .permission-error {
        background: #fee2e2;
        color: #991b1b;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 15px;
        font-size: 0.9rem;
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

    .referral-section {
        background: linear-gradient(135deg, #e0f7f4 0%, #f0fffe 100%);
        border: 1px solid #a7f3d0;
        border-radius: 12px;
        padding: 20px;
    }

    .referral-link-container {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        flex-wrap: wrap;
    }

    .referral-link-input {
        flex: 1;
        min-width: 250px;
        padding: 12px 16px;
        border: 1px solid #6ee7b7;
        border-radius: 8px;
        background: white;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        word-break: break-all;
    }

    .btn-copy {
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
        white-space: nowrap;
    }

    .btn-copy:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 217, 181, 0.3);
    }

    .btn-copy.copied {
        background: #10b981;
    }

    .referral-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }

    .stat-box {
        background: white;
        padding: 15px;
        border-radius: 10px;
        text-align: center;
        border: 1px solid #d1d5db;
    }

    .stat-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #00D9B5;
    }

    .stat-label {
        color: #6b7280;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    .referral-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .referral-table thead {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
    }

    .referral-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem;
    }

    .referral-table td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .referral-table tbody tr:hover {
        background: #f3f4f6;
    }

    .referral-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .referral-badge.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .referral-badge.completed {
        background: #d1fae5;
        color: #065f46;
    }

    .referral-date {
        color: #9ca3af;
        font-size: 0.85rem;
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
    @if(session('success'))
        <div style="background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 16px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
            <svg style="width: 20px; height: 20px; flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

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

     {{-- Referral Section --}}
     <div class="profile-card">
         <h4>💼 Chia Sẻ Liên Kết Referral</h4>
         <div class="referral-section">
             @if($user->referral_code)
             <p style="margin: 0 0 10px 0; color: #065f46; font-weight: 500;">Chia sẻ link dưới đây để bạn bè có thể đăng ký qua bạn</p>
             <p style="margin: 0 0 15px 0; color: #6b7280; font-size: 0.85rem;">Mã của bạn: <strong style="color: #00D9B5;">{{ $user->referral_code }}</strong></p>
             
             <div class="referral-link-container">
                 <input type="text" id="referralLink" class="referral-link-input" readonly value="{{ url('/register?ref=' . $user->referral_code) }}">
                 <button type="button" class="btn-copy" onclick="copyReferralLink()">
                     <span id="copyText">📋 Copy Link</span>
                 </button>
             </div>

             <div style="margin-top: 15px; padding: 12px; background: #f0fffe; border: 1px solid #a7f3d0; border-radius: 8px; font-size: 0.9rem; color: #065f46;">
                 <strong>✓ Mã của bạn:</strong> {{ $user->referral_code }}<br>
                 <span style="font-size: 0.85rem;">Gửi cho bạn bè để họ biết ai giới thiệu họ</span>
             </div>
             @else
             <div style="padding: 15px; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 8px; color: #991b1b;">
                 <strong>⚠️ Lỗi:</strong> Mã referral chưa được tạo. Vui lòng liên hệ admin.
             </div>
             @endif
             
             @if($referralStats)
             <div class="referral-stats">
                 <div class="stat-box">
                     <div class="stat-number">{{ $referralStats['total'] }}</div>
                     <div class="stat-label">Tổng Lời Mời</div>
                 </div>
                 <div class="stat-box">
                     <div class="stat-number">{{ $referralStats['completed'] }}</div>
                     <div class="stat-label">Đã Hoàn Thành</div>
                 </div>
                 <div class="stat-box">
                     <div class="stat-number">{{ $referralStats['pending'] }}</div>
                     <div class="stat-label">Đang Chờ</div>
                 </div>
             </div>
             @endif

             {{-- Referral Details Table --}}
             @if($referralDetails && $referralDetails->count() > 0)
             <div style="margin-top: 25px;">
                 <h5 style="font-size: 1rem; font-weight: 600; color: #1f2937; margin-bottom: 15px;">Danh Sách Người Được Giới Thiệu</h5>
                 <div style="overflow-x: auto;">
                     <table class="referral-table">
                         <thead>
                             <tr>
                                 <th>Người Đăng Ký</th>
                                 <th>Email</th>
                                 <th>Ngày Đăng Ký</th>
                                 <th>Trạng Thái</th>
                             </tr>
                         </thead>
                         <tbody>
                             @foreach($referralDetails as $referral)
                             <tr>
                                 <td>
                                     <strong>{{ $referral->referredUser->name }}</strong>
                                 </td>
                                 <td>{{ $referral->referredUser->email }}</td>
                                 <td>
                                     <span class="referral-date">{{ $referral->referred_at->format('d/m/Y H:i') }}</span>
                                 </td>
                                 <td>
                                     <span class="referral-badge {{ $referral->status }}">
                                         {{ $referral->status === 'completed' ? '✓ Đã hoàn thành' : '⏳ Đang chờ' }}
                                     </span>
                                 </td>
                             </tr>
                             @endforeach
                         </tbody>
                     </table>
                 </div>
             </div>
             @else
             <div style="margin-top: 20px; padding: 15px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; color: #6b7280; text-align: center;">
                 Bạn chưa có ai được giới thiệu. Chia sẻ link của bạn để bắt đầu!
             </div>
             @endif
         </div>
     </div>

     {{-- Permission Request Section --}}
     <div class="profile-card">
         <h4>Đăng Ký Quyền</h4>
         <p style="color: #6b7280; margin-bottom: 20px;">Nâng cấp tài khoản của bạn bằng cách đăng ký để trở thành chủ sân, chủ giải hoặc trọng tài.</p>
         
         @if($permissionRequest)
             @if($permissionRequest->status === 'pending')
                 <button type="button" class="btn" style="background: #fbbf24; color: #92400e; cursor: not-allowed;" disabled>
                     ⏳ Chờ Xét Duyệt
                 </button>
             @elseif($permissionRequest->status === 'approved')
                 <button type="button" class="btn" style="background: #d1fae5; color: #065f46; cursor: not-allowed;" disabled>
                     ✓ Bạn Đã Đăng Ký Quyền
                 </button>
             @elseif($permissionRequest->status === 'rejected')
                 <button type="button" class="btn btn-info" onclick="openPermissionModal()">Đăng Ký Quyền Lại</button>
             @endif
         @else
             <button type="button" class="btn btn-info" onclick="openPermissionModal()">Đăng Ký Quyền</button>
         @endif
     </div>

     {{-- Permission Modal --}}
     <div id="permissionModal" class="modal">
         <div class="modal-content">
             <div class="modal-header">
                 <h3>Đăng Ký Quyền</h3>
                 <button type="button" class="modal-close" onclick="closePermissionModal()">&times;</button>
             </div>
             <div id="permissionError"></div>
             <form id="permissionForm" onsubmit="submitPermissionRequest(event)">
                 @csrf
                 <div class="permission-item">
                     <input type="checkbox" id="quản-lý-sân-giải" name="permissions" value="home_yard">
                     <div class="permission-info">
                         <label for="quản-lý-sân-giải">Quản Lý Sân & Giải Đấu</label>
                         <p>Quản lý và cho thuê sân quần vợt, tổ chức và quản lý các giải đấu</p>
                     </div>
                 </div>

                 <div class="permission-item">
                     <input type="checkbox" id="trọng-tài" name="permissions" value="referee">
                     <div class="permission-info">
                         <label for="trọng-tài">Trọng Tài</label>
                         <p>Phân công làm trọng tài cho các giải đấu</p>
                     </div>
                 </div>

                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" onclick="closePermissionModal()">Hủy</button>
                     <button type="submit" class="btn btn-primary">Yêu Cầu Duyệt</button>
                 </div>
             </form>
         </div>
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

     // Permission Modal Functions
     function openPermissionModal() {
         document.getElementById('permissionModal').classList.add('show');
         document.getElementById('permissionError').innerHTML = '';
     }

     function closePermissionModal() {
         document.getElementById('permissionModal').classList.remove('show');
         document.getElementById('permissionForm').reset();
         document.getElementById('permissionError').innerHTML = '';
     }

     // Close modal when clicking outside
     document.getElementById('permissionModal').addEventListener('click', function(e) {
         if (e.target === this) {
             closePermissionModal();
         }
     });

     function submitPermissionRequest(e) {
         e.preventDefault();

         // Get selected permissions
         const checkboxes = document.querySelectorAll('input[name="permissions"]:checked');
         if (checkboxes.length === 0) {
             const errorDiv = document.getElementById('permissionError');
             errorDiv.innerHTML = '<div class="permission-error">Vui lòng chọn ít nhất một quyền.</div>';
             return;
         }

         const permissions = Array.from(checkboxes).map(cb => cb.value);

         // Send request
         fetch('{{ route("user.permission-request.store") }}', {
             method: 'POST',
             headers: {
                 'Content-Type': 'application/json',
                 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
             },
             body: JSON.stringify({ permissions: permissions })
         })
         .then(response => {
             if (!response.ok) {
                 return response.json().then(data => {
                     throw new Error(data.message || 'Có lỗi xảy ra');
                 });
             }
             return response.json();
         })
         .then(data => {
             toastr.success('Yêu cầu cấp quyền đã được gửi. Vui lòng chờ admin duyệt.');
             closePermissionModal();
             setTimeout(() => {
                 location.reload();
             }, 1500);
         })
         .catch(error => {
             const errorDiv = document.getElementById('permissionError');
             errorDiv.innerHTML = '<div class="permission-error">' + error.message + '</div>';
         });
     }

     // Referral Link Copy Function
     function copyReferralLink() {
         const referralLink = document.getElementById('referralLink');
         const copyBtn = document.querySelector('.btn-copy');
         const copyText = document.getElementById('copyText');
         
         // Select text
         referralLink.select();
         referralLink.setSelectionRange(0, 99999);
         
         // Copy to clipboard
         navigator.clipboard.writeText(referralLink.value).then(() => {
             // Show feedback
             copyText.textContent = '✓ Đã Copy!';
             copyBtn.classList.add('copied');
             
             // Reset after 2 seconds
             setTimeout(() => {
                 copyText.textContent = '📋 Copy Link';
                 copyBtn.classList.remove('copied');
             }, 2000);
             
             toastr.success('Liên kết referral đã được sao chép!');
         }).catch(err => {
             toastr.error('Không thể sao chép liên kết');
         });
     }
</script>
@endsection
