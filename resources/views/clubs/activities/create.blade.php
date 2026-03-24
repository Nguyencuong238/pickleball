@extends('layouts.front')

@section('content')
@include('clubs.activities.partials._form-styles')

<div class="activity-form-container">
    <div class="form-header">
        <h2>➕ Thêm Hoạt Động Mới</h2>
        <p>Tạo hoạt động cho {{ $club->name }}</p>
    </div>

    <div class="form-card">
        @if($errors->any())
            <div class="error-message">
                <strong>Vui lòng sửa các lỗi sau:</strong>
                <ul class="error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('clubs.activities.store', $club) }}" method="POST">
            @csrf

            @include('clubs.activities.partials._type-selector', ['activity' => (object)['type' => null]])

            <div class="form-group">
                <label for="title">Tên Hoạt Động <span style="color: red;">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                    placeholder="VD: Giải đấu nội bộ, Buổi tập luyện...">
            </div>

            <div class="form-group">
                <label for="description">Mô Tả Chi Tiết</label>
                <textarea id="description" name="description" placeholder="Mô tả chi tiết về hoạt động này...">{{ old('description') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="activity_date">Ngày & Giờ <span style="color: red;">*</span></label>
                    <input type="datetime-local" id="activity_date" name="activity_date"
                        value="{{ old('activity_date') }}" required>
                </div>
                <div class="form-group">
                    <label for="end_time">Giờ kết thúc</label>
                    <input type="time" id="end_time" name="end_time" value="{{ old('end_time') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="location">Địa Điểm</label>
                    <input type="text" id="location" name="location" value="{{ old('location') }}"
                        placeholder="VD: Sân pickleball ABC...">
                </div>
                <div class="form-group">
                    <label for="max_participants">Số người tối đa <span style="color: red;">*</span></label>
                    <input type="number" id="max_participants" name="max_participants"
                        value="{{ old('max_participants', 20) }}" min="2" max="200" placeholder="VD: 20" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status">Trạng Thái <span style="color: red;">*</span></label>
                    <select id="status" name="status" required>
                        <option value="upcoming" {{ old('status', 'upcoming') === 'upcoming' ? 'selected' : '' }}>Sắp tới</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                        <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>
                <div></div>
            </div>

            @include('clubs.activities.partials._skill-level-fields', ['activity' => (object)['min_skill_level' => null, 'max_skill_level' => null]])
            @include('clubs.activities.partials._recurring-fields', ['activity' => (object)['recurrence_day' => null, 'auto_approve' => false]])
            @include('clubs.activities.partials._competition-fields', ['activity' => (object)['competition_config' => null]])
            @include('clubs.activities.partials._open-play-fields', ['activity' => (object)['courts_count' => 3, 'rotation_mode' => 'oprs_based', 'oprs_weight' => 0.50, 'avg_match_duration' => 15]])

            <div class="btn-group">
                <button type="submit" class="btn-submit">✅ Tạo Hoạt Động</button>
                <a href="{{ route('clubs.activities.index', $club) }}" class="btn-cancel">← Quay Lại</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var typeCards = document.querySelectorAll('.type-card');
    var typeInput = document.getElementById('type-input');
    var recurringFields = document.getElementById('recurring-fields');
    var competitionFields = document.getElementById('competition-fields');
    var openPlayFields = document.getElementById('open-play-fields');

    function updateTypeDisplay(type) {
        typeCards.forEach(function(c) {
            c.classList.toggle('active', c.dataset.type === type);
        });
        typeInput.value = type;
        recurringFields.style.display = type === 'recurring' ? 'block' : 'none';
        competitionFields.style.display = type === 'competition' ? 'block' : 'none';
        if (openPlayFields) openPlayFields.style.display = type === 'open_play' ? 'block' : 'none';
    }

    typeCards.forEach(function(card) {
        card.addEventListener('click', function() {
            updateTypeDisplay(this.dataset.type);
        });
    });

    updateTypeDisplay(typeInput.value || 'one_off');
});
</script>
@endsection
