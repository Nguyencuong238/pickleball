@extends('layouts.homeyard')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/layout-sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-buttons-alerts.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-forms.css') }}?v=1.1">
@endsection

@section('content')
@include('clubs.activities.partials._form-styles')

<div class="main-content">
    <div class="top-header">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('clubs.activities.show', [$club, $activity]) }}" class="td-btn td-btn-ghost">
                &larr; Quay lại
            </a>
            <h2 class="page-title" style="margin: 0;">Chỉnh Sửa Hoạt Động</h2>
        </div>
    </div>

    <div class="form-card">
        @if($errors->any())
            <div class="td-alert td-alert-error">
                <strong>Vui lòng sửa các lỗi sau:</strong>
                <ul style="margin: 8px 0 0 16px; padding: 0;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Show type as read-only badge (type cannot change after creation) --}}
        @php
            $typeLabels = ['one_off' => 'Buổi chơi', 'recurring' => 'Lịch cố định', 'competition' => 'Giải đấu', 'open_play' => 'Chơi mở'];
        @endphp
        <span class="type-badge-static type-{{ $activity->type }}">
            {{ $typeLabels[$activity->type] ?? $activity->type }}
        </span>

        <form action="{{ route('clubs.activities.update', [$club, $activity]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title">Tên Hoạt Động <span style="color: red;">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $activity->title) }}" required
                    placeholder="VD: Giải đấu nội bộ, Buổi tập luyện...">
            </div>

            <div class="form-group">
                <label for="description">Mô Tả Chi Tiết</label>
                <textarea id="description" name="description" placeholder="Mô tả chi tiết về hoạt động này...">{{ old('description', $activity->description) }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="activity_date">Ngày & Giờ <span style="color: red;">*</span></label>
                    <input type="datetime-local" id="activity_date" name="activity_date"
                        value="{{ old('activity_date', $activity->activity_date->format('Y-m-d\TH:i')) }}" required>
                </div>
                <div class="form-group">
                    <label for="end_time">Giờ kết thúc</label>
                    <input type="time" id="end_time" name="end_time"
                        value="{{ old('end_time', $activity->end_time) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="location">Địa Điểm</label>
                    <input type="text" id="location" name="location"
                        value="{{ old('location', $activity->location) }}" placeholder="VD: Sân pickleball ABC...">
                </div>
                <div class="form-group">
                    <label for="max_participants">Số người tối đa <span style="color: red;">*</span></label>
                    <input type="number" id="max_participants" name="max_participants"
                        value="{{ old('max_participants', $activity->max_participants) }}" min="2" max="200" required>
                </div>
            </div>

            <div class="form-group">
                <label for="fee_gems">Phí tham gia (Gems)</label>
                @if($activity->isFeeEditable())
                    <input type="number" id="fee_gems" name="fee_gems"
                        value="{{ old('fee_gems', $activity->fee_gems) }}"
                        min="1" max="10000" placeholder="Để trống nếu miễn phí">
                @else
                    <input type="number" id="fee_gems" value="{{ $activity->fee_gems }}" disabled
                        style="background: #f3f4f6; cursor: not-allowed;">
                    <small style="color: #ef4444;">Không thể thay đổi phí khi đã có người đăng ký.</small>
                @endif
                <small style="color: #6b7280;">1 Gem = {{ number_format(config('gems.exchange_rate')) }} VND</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status">Trạng Thái <span style="color: red;">*</span></label>
                    <select id="status" name="status" required>
                        <option value="upcoming" {{ old('status', $activity->status) === 'upcoming' ? 'selected' : '' }}>Sắp tới</option>
                        <option value="completed" {{ old('status', $activity->status) === 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                        <option value="cancelled" {{ old('status', $activity->status) === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>
                <div></div>
            </div>

            @include('clubs.activities.partials._skill-level-fields', ['activity' => $activity])

            @if($activity->type === 'recurring')
                @include('clubs.activities.partials._recurring-fields', ['activity' => $activity])
            @endif

            @if($activity->type === 'competition')
                @include('clubs.activities.partials._competition-fields', ['activity' => $activity])
            @endif

            @if($activity->type === 'open_play')
                @include('clubs.activities.partials._open-play-fields', ['activity' => $activity])
            @endif

            <div class="btn-group">
                <button type="submit" class="btn-submit">Cập Nhật</button>
                <a href="{{ route('clubs.activities.show', [$club, $activity]) }}" class="btn-cancel">Quay Lại</a>
            </div>
        </form>

        {{-- Delete form separate (no nested form bug) --}}
        <form action="{{ route('clubs.activities.destroy', [$club, $activity]) }}" method="POST" style="margin-top: 15px;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-delete" style="width: 100%;"
                onclick="return confirm('Bạn chắc chắn muốn xóa hoạt động này?')">Xóa hoạt động</button>
        </form>
    </div>
</div>

@if($activity->type === 'recurring')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('recurring-fields').style.display = 'block';
});
</script>
@endif
@if($activity->type === 'competition')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('competition-fields').style.display = 'block';
});
</script>
@endif
@if($activity->type === 'open_play')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('open-play-fields').style.display = 'block';
});
</script>
@endif
@endsection
