@extends('layouts.front')

@section('content')
<style>
    .activities-container {
        padding: 40px 20px;
        max-width: 1000px;
        margin: 0 auto;
        margin-top: 100px;
    }

    .activities-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .activities-header h2 {
        font-size: 2rem;
        font-weight: 700;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
    }

    .btn-create-activity {
        padding: 12px 24px;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .btn-create-activity:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 217, 181, 0.3);
        color: white;
    }

    .activities-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .activity-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #00D9B5;
        transition: all 0.3s ease;
    }

    .activity-card:hover {
        box-shadow: 0 8px 30px rgba(0, 217, 181, 0.15);
        transform: translateX(5px);
    }

    .activity-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .activity-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
        word-break: break-word;
    }

    .activity-status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-upcoming {
        background: #dbeafe;
        color: #0284c7;
    }

    .status-completed {
        background: #dcfce7;
        color: #16a34a;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #b91c1c;
    }

    .activity-meta {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
        flex-wrap: wrap;
        color: #6b7280;
        font-size: 0.95rem;
    }

    .activity-meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .activity-description {
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    .activity-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-edit {
        background: #dbeafe;
        color: #0284c7;
    }

    .btn-edit:hover {
        background: #bfdbfe;
    }

    .btn-delete {
        background: #fee2e2;
        color: #b91c1c;
    }

    .btn-delete:hover {
        background: #fecaca;
    }

    .btn-back {
        background: #f3f4f6;
        color: #6b7280;
        padding: 12px 24px;
        margin-bottom: 30px;
        display: inline-block;
    }

    .btn-back:hover {
        background: #e5e7eb;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 3rem;
        color: #d1d5db;
        margin-bottom: 20px;
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #dcfce7;
        color: #16a34a;
        border-left: 4px solid #16a34a;
    }

    @media (max-width: 768px) {
        .activities-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .activities-header h2 {
            font-size: 1.5rem;
        }

        .activity-header {
            flex-direction: column;
        }

        .activity-actions {
            width: 100%;
        }

        .btn-action {
            flex: 1;
            text-align: center;
        }
    }
</style>

<div class="activities-container">
    <a href="{{ route('clubs.show', $club) }}" class="btn-back">← Quay lại câu lạc bộ</a>

    <div class="activities-header">
        <h2>📅 Hoạt Động - {{ $club->name }}</h2>
        @if(Auth::id() === $club->user_id)
            <a href="{{ route('clubs.activities.create', $club) }}" class="btn-create-activity">+ Thêm Hoạt Động</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if($activities->count() > 0)
        <div class="activities-list">
            @foreach($activities as $activity)
                <div class="activity-card">
                    <div class="activity-header">
                        <h3 class="activity-title">{{ $activity->title }}</h3>
                        <span class="activity-status status-{{ $activity->status }}">
                            @if($activity->status === 'upcoming')
                                📅 Sắp tới
                            @elseif($activity->status === 'completed')
                                ✓ Đã hoàn thành
                            @else
                                ✕ Đã hủy
                            @endif
                        </span>
                    </div>

                    <div class="activity-meta">
                        <div class="activity-meta-item">
                            🕐 {{ $activity->activity_date->format('d/m/Y H:i') }}
                        </div>
                        @if($activity->location)
                            <div class="activity-meta-item">
                                📍 {{ $activity->location }}
                            </div>
                        @endif
                    </div>

                    @if($activity->description)
                        <div class="activity-description">
                            {{ $activity->description }}
                        </div>
                    @endif

                    @if(Auth::id() === $club->user_id)
                        <div class="activity-actions">
                            <a href="{{ route('clubs.activities.edit', [$club, $activity]) }}" class="btn-action btn-edit">✏️ Chỉnh Sửa</a>
                            <form action="{{ route('clubs.activities.destroy', [$club, $activity]) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete" onclick="return confirm('Bạn chắc chắn muốn xóa hoạt động này?')">🗑️ Xóa</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div style="margin-top: 30px;">
            {{ $activities->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-calendar-alt"></i>
            <h3>Chưa có hoạt động nào</h3>
            <p>Hãy tạo hoạt động đầu tiên cho câu lạc bộ/nhóm của bạn!</p>
            @if(Auth::id() === $club->user_id)
                <a href="{{ route('clubs.activities.create', $club) }}" class="btn-create-activity" style="margin-top: 20px;">+ Tạo Hoạt Động</a>
            @endif
        </div>
    @endif
</div>

@endsection
