@extends('layouts.front')

@section('title', $activity->title . ' | ' . $club->name)

@section('content')
<style>
    .activity-container {
        padding: 40px 20px;
        max-width: 800px;
        margin: 0 auto;
        margin-top: 100px;
    }

    .btn-back {
        background: #f3f4f6;
        color: #6b7280;
        padding: 12px 24px;
        margin-bottom: 30px;
        display: inline-block;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .activity-detail-card {
        background: white;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border-top: 4px solid #00D9B5;
    }

    .activity-detail-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .activity-detail-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
        line-height: 1.3;
    }

    .activity-status {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
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

    .activity-detail-meta {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-bottom: 30px;
        padding-bottom: 30px;
        border-bottom: 1px solid #e5e7eb;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #6b7280;
        font-size: 1rem;
    }

    .meta-item svg {
        width: 20px;
        height: 20px;
        color: #00D9B5;
    }

    .meta-item strong {
        color: #374151;
    }

    .activity-detail-description {
        color: #4b5563;
        line-height: 1.8;
        font-size: 1.05rem;
    }

    .activity-detail-description h4 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 15px;
    }

    .club-info-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        margin-top: 30px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .club-avatar {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        object-fit: cover;
    }

    .club-avatar-placeholder {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        background: linear-gradient(135deg, #00D9B5, #0099CC);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.2rem;
    }

    .club-info-text {
        flex: 1;
    }

    .club-info-text p {
        margin: 0;
        font-size: 0.9rem;
        color: #6b7280;
    }

    .club-info-text a {
        font-weight: 600;
        color: #1f2937;
        text-decoration: none;
    }

    .club-info-text a:hover {
        color: #00D9B5;
    }

    .activity-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 30px;
        border-top: 1px solid #e5e7eb;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
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
        .activity-container {
            padding: 20px 15px;
            margin-top: 80px;
        }

        .activity-detail-card {
            padding: 25px;
        }

        .activity-detail-title {
            font-size: 1.4rem;
        }

        .activity-detail-header {
            flex-direction: column;
        }

        .activity-actions {
            flex-direction: column;
        }

        .btn-action {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="activity-container">
    <a href="{{ route('clubs.activities.index', $club) }}" class="btn-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; vertical-align: middle; margin-right: 5px;">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
        Quay lại danh sách
    </a>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="activity-detail-card">
        <div class="activity-detail-header">
            <h1 class="activity-detail-title">{{ $activity->title }}</h1>
            <span class="activity-status status-{{ $activity->status }}">
                @if($activity->status === 'upcoming')
                    Sắp diễn ra
                @elseif($activity->status === 'completed')
                    Đã hoàn thành
                @else
                    Đã hủy
                @endif
            </span>
        </div>

        <div class="activity-detail-meta">
            <div class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <span><strong>Ngày giờ:</strong> {{ $activity->activity_date->format('d/m/Y') }} lúc {{ $activity->activity_date->format('H:i') }}</span>
            </div>

            @if($activity->location)
            <div class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                <span><strong>Địa điểm:</strong> {{ $activity->location }}</span>
            </div>
            @endif

            <div class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <span><strong>Tạo lúc:</strong> {{ $activity->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        @if($activity->description)
        <div class="activity-detail-description">
            <h4>Mô tả chi tiết</h4>
            <p>{!! nl2br(e($activity->description)) !!}</p>
        </div>
        @endif

        <div class="club-info-box">
            @if($club->image)
                <img src="{{ asset('storage/' . $club->image) }}" alt="{{ $club->name }}" class="club-avatar">
            @else
                <div class="club-avatar-placeholder">
                    {{ strtoupper(substr($club->name, 0, 2)) }}
                </div>
            @endif
            <div class="club-info-text">
                <p>Hoạt động của</p>
                <a href="{{ route('clubs.show', $club) }}">{{ $club->name }}</a>
            </div>
        </div>

        @if(Auth::id() === $club->user_id)
        <div class="activity-actions">
            <a href="{{ route('clubs.activities.edit', [$club, $activity]) }}" class="btn-action btn-edit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Chỉnh sửa
            </a>
            <form action="{{ route('clubs.activities.destroy', [$club, $activity]) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa hoạt động này?')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        <line x1="10" y1="11" x2="10" y2="17"/>
                        <line x1="14" y1="11" x2="14" y2="17"/>
                    </svg>
                    Xóa hoạt động
                </button>
            </form>
        </div>
        @endif
    </div>
</div>

@endsection
