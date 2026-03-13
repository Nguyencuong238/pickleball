@extends('layouts.homeyard')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/layout-sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-buttons-alerts.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-forms.css') }}">
@endsection

@section('content')
<div class="main-content">
    <div class="top-header">
        <a href="{{ route('tournament-manage.tournaments.index') }}" class="td-btn td-btn-ghost">
            &larr; Quay lại
        </a>
        <h2 class="page-title">Tạo giải đấu mới</h2>
    </div>

    @if($errors->any())
        <div class="td-alert td-alert-error">
            <strong>Lỗi xác thực:</strong>
            <ul style="margin: 8px 0 0 16px; padding: 0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data="tournamentForm([])" @submit.prevent="validateForm() && $el.submit()">
    <form method="POST" action="{{ route('tournament-manage.tournaments.store') }}"
          enctype="multipart/form-data" @submit.prevent="validateForm() && $el.submit()">
        @csrf

        {{-- Basic info --}}
        <div class="td-form-section">
            <div class="td-section-title">Thông tin cơ bản</div>
            <div class="td-form-grid-2">
                <div class="td-form-group">
                    <label class="td-label">Tên giải đấu <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" class="td-input"
                           value="{{ old('name') }}" required>
                </div>
                <div class="td-form-group">
                    <label class="td-label">Trạng thái</label>
                    <select name="status" class="td-select">
                        <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Không hoạt động</option>
                    </select>
                </div>
                <div class="td-form-group">
                    <label class="td-label">Câu lạc bộ</label>
                    <select name="club_id" class="td-select">
                        <option value="">-- Không thuộc CLB nào --</option>
                        @foreach($clubs as $club)
                            <option value="{{ $club->id }}"
                                {{ old('club_id') == $club->id ? 'selected' : '' }}>
                                {{ $club->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="td-form-group">
                    <label class="td-label">Ngày bắt đầu <span style="color:#ef4444">*</span></label>
                    <input type="date" name="start_date" class="td-input"
                           value="{{ old('start_date') }}" required>
                </div>
                <div class="td-form-group">
                    <label class="td-label">Ngày kết thúc</label>
                    <input type="date" name="end_date" class="td-input"
                           value="{{ old('end_date') }}">
                </div>
                <div class="td-form-group">
                    <label class="td-label">Hạn đăng ký</label>
                    <input type="datetime-local" name="registration_deadline" class="td-input"
                           value="{{ old('registration_deadline') }}">
                </div>
                <div class="td-form-group">
                    <label class="td-label">Địa điểm</label>
                    <input type="text" name="location" class="td-input"
                           value="{{ old('location') }}">
                </div>
            </div>
            <div class="td-form-group">
                <label class="td-label">Mô tả</label>
                <textarea name="description" class="td-textarea" rows="3">{{ old('description') }}</textarea>
            </div>
        </div>

        {{-- Format & registration --}}
        <div class="td-form-section">
            <div class="td-section-title">Hình thức &amp; đăng ký</div>
            <div class="td-form-grid-2">
                <div class="td-form-group">
                    <label class="td-label">Hình thức thi đấu</label>
                    <select name="competition_format" class="td-select">
                        <option value="">-- Chọn --</option>
                        <option value="single"  {{ old('competition_format') === 'single'  ? 'selected' : '' }}>Đơn</option>
                        <option value="double"  {{ old('competition_format') === 'double'  ? 'selected' : '' }}>Đôi</option>
                        <option value="mixed"   {{ old('competition_format') === 'mixed'   ? 'selected' : '' }}>Hỗn hợp</option>
                    </select>
                </div>
                <div class="td-form-group">
                    <label class="td-label">Hạng giải</label>
                    <select name="tournament_rank" class="td-select">
                        <option value="">-- Chọn --</option>
                        <option value="beginner"     {{ old('tournament_rank') === 'beginner'     ? 'selected' : '' }}>Sơ cấp</option>
                        <option value="intermediate" {{ old('tournament_rank') === 'intermediate' ? 'selected' : '' }}>Trung cấp</option>
                        <option value="advanced"     {{ old('tournament_rank') === 'advanced'     ? 'selected' : '' }}>Cao cấp</option>
                        <option value="professional" {{ old('tournament_rank') === 'professional' ? 'selected' : '' }}>Chuyên nghiệp</option>
                    </select>
                </div>
                <div class="td-form-group">
                    <label class="td-label">Số VĐV tối đa <span style="color:#ef4444">*</span></label>
                    <input type="number" name="max_participants" class="td-input"
                           value="{{ old('max_participants', 32) }}" min="2" required>
                </div>
                <div class="td-form-group">
                    <label class="td-label">Lệ phí đăng ký (VNĐ)</label>
                    <input type="number" name="price" class="td-input"
                           value="{{ old('price', 0) }}" min="0" step="1000">
                </div>
                <div class="td-form-group">
                    <label class="td-label">Giải thưởng (VNĐ)</label>
                    <input type="number" name="prizes" class="td-input"
                           value="{{ old('prizes', 0) }}" min="0" step="1000">
                </div>
            </div>
        </div>

        {{-- Rules & benefits --}}
        <div class="td-form-section">
            <div class="td-section-title">Luật thi đấu &amp; quyền lợi</div>
            <div class="td-form-group">
                <label class="td-label">Luật thi đấu</label>
                <textarea name="rules" class="td-textarea" rows="4">{{ old('rules') }}</textarea>
            </div>
            <div class="td-form-group">
                <label class="td-label">Quyền lợi đăng ký</label>
                <textarea name="registration_benefits" class="td-textarea" rows="3">{{ old('registration_benefits') }}</textarea>
            </div>
        </div>

        {{-- Contact info --}}
        <div class="td-form-section">
            <div class="td-section-title">Thông tin liên hệ</div>
            <div class="td-form-grid-2">
                <div class="td-form-group">
                    <label class="td-label">Email tổ chức</label>
                    <input type="email" name="organizer_email" class="td-input"
                           value="{{ old('organizer_email') }}">
                </div>
                <div class="td-form-group">
                    <label class="td-label">Hotline tổ chức</label>
                    <input type="tel" name="organizer_hotline" class="td-input"
                           value="{{ old('organizer_hotline') }}">
                </div>
            </div>
            <div class="td-form-group">
                <label class="td-label">Thông tin mạng xã hội</label>
                <textarea name="social_information" class="td-textarea" rows="2">{{ old('social_information') }}</textarea>
            </div>
        </div>

        {{-- Categories --}}
        @include('home-yard.tournaments.partials._category-editor')

        {{-- Banner --}}
        <div class="td-form-section">
            <div class="td-section-title">Ảnh bìa</div>
            <div class="td-upload-area" @click="$refs.bannerInput.click()">
                <p style="color:#64748b; margin:0; font-size:0.875rem;">
                    Bấm để chọn ảnh bìa
                </p>
                <input type="file" name="banner" accept="image/*" style="display:none;"
                       x-ref="bannerInput" @change="handleBannerChange($event)">
            </div>
            <img x-show="bannerPreview" :src="bannerPreview" class="td-img-preview" alt="Preview">
        </div>

        {{-- Submit --}}
        <div style="display:flex; gap:12px; justify-content:flex-end; padding-bottom:20px;">
            <a href="{{ route('tournament-manage.tournaments.index') }}" class="td-btn td-btn-ghost">
                Hủy
            </a>
            <button type="submit" class="td-btn td-btn-primary">Tạo giải đấu</button>
        </div>
    </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="{{ asset('assets/js/tournament-dashboard.js') }}"></script>
@endsection
