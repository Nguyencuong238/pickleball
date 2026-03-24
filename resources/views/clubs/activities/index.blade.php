@extends('layouts.front')

@section('content')
@include('clubs.activities.partials._index-styles')

<div class="activities-container">
    <a href="{{ route('clubs.show', $club) }}" class="btn-back">← Quay lại câu lạc bộ</a>

    <div class="activities-header">
        <h2>📅 Hoạt Động - {{ $club->name }}</h2>
        @if($isManagement)
            <a href="{{ route('clubs.activities.create', $club) }}" class="btn-create-activity">➕ Thêm Hoạt Động</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($activities->count() > 0)
        @php
            $typeLabels = ['one_off' => 'Buổi chơi', 'recurring' => 'Lịch cố định', 'competition' => 'Giải đấu', 'open_play' => 'Chơi mở'];
            $statusLabels = ['upcoming' => 'Sắp tới', 'completed' => 'Đã hoàn thành', 'cancelled' => 'Đã hủy'];
        @endphp

        <div class="activities-list">
            @foreach($activities as $activity)
                <a href="{{ route('clubs.activities.show', [$club, $activity]) }}" class="activity-card type-{{ $activity->type }}">
                    <div class="activity-header">
                        <div class="activity-title-row">
                            <h3 class="activity-title">{{ $activity->title }}</h3>
                            <span class="type-badge type-{{ $activity->type }}">{{ $typeLabels[$activity->type] ?? $activity->type }}</span>
                        </div>
                        <span class="activity-status status-{{ $activity->status }}">
                            {{ $statusLabels[$activity->status] ?? $activity->status }}
                        </span>
                    </div>

                    <div class="activity-meta">
                        <div class="activity-meta-item">
                            📅 {{ $activity->activity_date->format('d/m/Y H:i') }}
                        </div>
                        @if($activity->location)
                            <div class="activity-meta-item">
                                📍 {{ $activity->location }}
                            </div>
                        @endif
                        @if($activity->max_participants)
                            <div class="activity-meta-item participant-count">
                                👥 {{ $activity->confirmed_participants_count ?? 0 }}/{{ $activity->max_participants }} người
                            </div>
                        @endif
                    </div>

                    @if($activity->description)
                        <div class="activity-description">
                            {{ Str::limit($activity->description, 150) }}
                        </div>
                    @endif
                </a>

                @if($isManagement)
                    <div class="activity-actions" style="margin-top: -10px; margin-bottom: 10px;">
                        <a href="{{ route('clubs.activities.edit', [$club, $activity]) }}" class="btn-action btn-edit">✏️ Chỉnh Sửa</a>
                        <form action="{{ route('clubs.activities.destroy', [$club, $activity]) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete" onclick="return confirm('Bạn chắc chắn muốn xóa hoạt động này?')">🗑️ Xóa</button>
                        </form>
                    </div>
                @endif
            @endforeach
        </div>

        <div style="margin-top: 30px;">
            {{ $activities->links() }}
        </div>
    @else
        <div class="empty-state">
            <h3>Chưa có hoạt động nào</h3>
            <p>Hãy tạo hoạt động đầu tiên cho câu lạc bộ/nhóm của bạn!</p>
            @if($isManagement)
                <a href="{{ route('clubs.activities.create', $club) }}" class="btn-create-activity" style="margin-top: 20px;">➕ Tạo Hoạt Động</a>
            @endif
        </div>
    @endif
</div>

@endsection
