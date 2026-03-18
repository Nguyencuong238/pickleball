@extends('layouts.homeyard')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/layout-sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-buttons-alerts.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-forms.css') }}">
@endsection

@section('content')
<div class="main-content">
    <div class="top-header">
        <a href="{{ route('tournament-manage.tournaments.show', $tournament) }}" class="td-btn td-btn-ghost">
            &larr; Quay lại
        </a>
        <h2 class="page-title">Chỉnh sửa: {{ $tournament->name }}</h2>
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

    @php
        $existingCats = $tournament->categories->map(fn($c) => [
            'id'               => $c->id,
            'category_name'    => $c->category_name,
            'category_type'    => $c->category_type,
            'age_group'        => $c->age_group ?? '',
            'max_participants' => $c->max_participants,
            'prize_money'      => $c->prize_money ?? 0,
        ])->values()->toArray();
    @endphp

    <div x-data="tournamentForm({{ json_encode($existingCats) }})">
    <form method="POST" action="{{ route('tournament-manage.tournaments.update', $tournament) }}"
          enctype="multipart/form-data" @submit.prevent="validateForm() && $el.submit()">
        @csrf
        @method('PUT')

        {{-- Thong tin co ban --}}
        <div class="td-form-section">
            <div class="td-section-title">Thông tin cơ bản</div>
            <div class="td-form-grid-2">
                <div class="td-form-group">
                    <label class="td-label">Tên giải đấu <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" class="td-input"
                           value="{{ old('name', $tournament->name) }}" required>
                </div>
                <div class="td-form-group">
                    <label class="td-label">Trạng thái</label>
                    <select name="status" class="td-select">
                        <option value="1" {{ old('status', $tournament->status ? '1' : '0') == '1' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="0" {{ old('status', $tournament->status ? '1' : '0') == '0' ? 'selected' : '' }}>Không hoạt động</option>
                    </select>
                </div>
                <div class="td-form-group">
                    <label class="td-label">Câu lạc bộ</label>
                    <select name="club_id" class="td-select">
                        <option value="">-- Không thuộc CLB nào --</option>
                        @foreach($clubs as $club)
                            <option value="{{ $club->id }}"
                                {{ old('club_id', $tournament->club_id) == $club->id ? 'selected' : '' }}>
                                {{ $club->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="td-form-group">
                    <label class="td-label">Ngày bắt đầu <span style="color:#ef4444">*</span></label>
                    <input type="date" name="start_date" class="td-input"
                           value="{{ old('start_date', $tournament->start_date->format('Y-m-d')) }}" required>
                </div>
                <div class="td-form-group">
                    <label class="td-label">Ngày kết thúc</label>
                    <input type="date" name="end_date" class="td-input"
                           value="{{ old('end_date', $tournament->end_date?->format('Y-m-d')) }}">
                </div>
                <div class="td-form-group">
                    <label class="td-label">Hạn đăng ký</label>
                    <input type="datetime-local" name="registration_deadline" class="td-input"
                           value="{{ old('registration_deadline', $tournament->registration_deadline?->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="td-form-group">
                    <label class="td-label">Địa điểm</label>
                    <input type="text" name="location" class="td-input"
                           value="{{ old('location', $tournament->location) }}">
                </div>
            </div>
            <div class="td-form-group">
                <label class="td-label">Mô tả</label>
                <textarea name="description" class="td-textarea" rows="3">{{ old('description', $tournament->description) }}</textarea>
            </div>
        </div>

        {{-- Hình thức & đăng ký --}}
        <div class="td-form-section">
            <div class="td-section-title">Hình thức &amp; đăng ký</div>
            <div class="td-form-grid-2">
                <div class="td-form-group">
                    <label class="td-label">Hình thức thi đấu</label>
                    <select name="competition_format" class="td-select">
                        <option value="">-- Chọn --</option>
                        @foreach(['single' => 'Đơn', 'double' => 'Đôi', 'mixed' => 'Hỗn hợp'] as $val => $label)
                            <option value="{{ $val }}"
                                {{ old('competition_format', $tournament->competition_format) === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="td-form-group">
                    <label class="td-label">Hạng giải</label>
                    <select name="tournament_rank" class="td-select">
                        <option value="">-- Chọn --</option>
                        @foreach(['beginner' => 'Sơ cấp', 'intermediate' => 'Trung cấp', 'advanced' => 'Cao cấp', 'professional' => 'Chuyên nghiệp'] as $val => $label)
                            <option value="{{ $val }}"
                                {{ old('tournament_rank', $tournament->tournament_rank) === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="td-form-group">
                    <label class="td-label">Số VĐV tối đa <span style="color:#ef4444">*</span></label>
                    <input type="number" name="max_participants" class="td-input"
                           value="{{ old('max_participants', $tournament->max_participants) }}" min="2" required>
                </div>
                <div class="td-form-group">
                    <label class="td-label">Lệ phí đăng ký (VNĐ)</label>
                    <input type="number" name="price" class="td-input"
                           value="{{ old('price', $tournament->price) }}" min="0" step="1000">
                </div>
                <div class="td-form-group">
                    <label class="td-label">Giải thưởng (VNĐ)</label>
                    <input type="number" name="prizes" class="td-input"
                           value="{{ old('prizes', $tournament->prizes) }}" min="0" step="1000">
                </div>
            </div>
        </div>

        {{-- Luật thi đấu & quyền lợi --}}
        <div class="td-form-section">
            <div class="td-section-title">Luật thi đấu &amp; quyền lợi</div>
            <div class="td-form-group">
                <label class="td-label">Luật thi đấu</label>
                <textarea name="competition_rules" class="td-textarea" rows="4">{{ old('competition_rules', $tournament->competition_rules) }}</textarea>
            </div>
            <div class="td-form-group">
                <label class="td-label">Timeline sự kiện</label>
                <textarea name="event_timeline" class="td-textarea" rows="4">{{ old('event_timeline', $tournament->event_timeline) }}</textarea>
            </div>
            <div class="td-form-group">
                <label class="td-label">Quyền lợi đăng ký</label>
                <textarea name="registration_benefits" class="td-textarea" rows="3">{{ old('registration_benefits', $tournament->registration_benefits) }}</textarea>
            </div>
        </div>

        {{-- Thông tin liên hệ --}}
        <div class="td-form-section">
            <div class="td-section-title">Thông tin liên hệ</div>
            <div class="td-form-grid-2">
                <div class="td-form-group">
                    <label class="td-label">Email tổ chức</label>
                    <input type="email" name="organizer_email" class="td-input"
                           value="{{ old('organizer_email', $tournament->organizer_email) }}">
                </div>
                <div class="td-form-group">
                    <label class="td-label">Hotline tổ chức</label>
                    <input type="tel" name="organizer_hotline" class="td-input"
                           value="{{ old('organizer_hotline', $tournament->organizer_hotline) }}">
                </div>
            </div>
            <div class="td-form-group">
                <label class="td-label">Thông tin mạng xã hội</label>
                <textarea name="social_information" class="td-textarea" rows="2">{{ old('social_information', $tournament->social_information) }}</textarea>
            </div>
        </div>

        {{-- Hạng mục thi đấu --}}
        @include('home-yard.tournaments.partials._category-editor')

        {{-- Ảnh bìa --}}
        <div class="td-form-section">
            <div class="td-section-title">Ảnh bìa</div>
            @if($tournament->getFirstMediaUrl('banner'))
                <img src="{{ $tournament->getFirstMediaUrl('banner') }}"
                     class="td-img-preview" alt="Ảnh bìa hiện tại"
                     style="margin-bottom:12px; display:block;">
                <p style="font-size:0.8rem; color:#64748b; margin-bottom:8px;">
                    Chọn ảnh mới để thay thế ảnh hiện tại
                </p>
            @endif
            <div class="td-upload-area" @click="$refs.bannerInput.click()">
                <p style="color:#64748b; margin:0; font-size:0.875rem;">Bấm để chọn ảnh bìa</p>
                <input type="file" name="banner" accept="image/*" style="display:none;"
                       x-ref="bannerInput" @change="handleBannerChange($event)">
            </div>
            <img x-show="bannerPreview" :src="bannerPreview" class="td-img-preview" alt="Preview">
        </div>

        {{-- Submit --}}
        <div style="display:flex; gap:12px; justify-content:flex-end; padding-bottom:20px;">
            <a href="{{ route('tournament-manage.tournaments.show', $tournament) }}" class="td-btn td-btn-ghost">
                Hủy
            </a>
            <button type="submit" class="td-btn td-btn-primary">Lưu thay đổi</button>
        </div>
    </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="{{ asset('assets/js/tournament-dashboard.js') }}"></script>
@endsection
