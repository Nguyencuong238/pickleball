@extends('layouts.front')

@section('content')
<style>
    .show-container {
        padding: 40px 20px;
        max-width: 1200px;
        margin: 0 auto;
        margin-top: 100px;
    }

    .show-header {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 30px;
        margin-bottom: 40px;
        align-items: start;
    }

    .club-image-large {
        width: 100%;
        height: 300px;
        border-radius: 15px;
        object-fit: cover;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 4rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .club-info {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .club-type-badge {
        display: inline-block;
        padding: 6px 16px;
        background: #f0f9ff;
        color: #0084ff;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .club-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 10px;
        word-break: break-word;
    }

    .club-creator-info {
        color: #6b7280;
        margin-bottom: 20px;
        font-size: 0.95rem;
    }

    .club-meta-info {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-bottom: 20px;
        padding: 20px 0;
        border-top: 1px solid #f3f4f6;
        border-bottom: 1px solid #f3f4f6;
    }

    .meta-item {
        text-align: center;
    }

    .meta-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #00D9B5;
    }

    .meta-label {
        color: #9ca3af;
        font-size: 0.85rem;
        margin-top: 5px;
    }

    .btn-actions {
        display: flex;
        gap: 10px;
    }

    .btn-action {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
        font-size: 0.9rem;
    }

    .btn-edit {
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 217, 181, 0.3);
    }

    .btn-back {
        background: #f3f4f6;
        color: #6b7280;
    }

    .btn-back:hover {
        background: #e5e7eb;
    }

    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
    }

    .section-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .section-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f3f4f6;
    }

    .description {
        color: #6b7280;
        line-height: 1.8;
        margin-bottom: 20px;
    }

    .section-label {
        font-weight: 600;
        color: #374151;
        margin-top: 15px;
        margin-bottom: 10px;
    }

    .provinces-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .province-badge {
        display: inline-block;
        padding: 6px 14px;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .members-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .member-item {
        display: flex;
        align-items: center;
        padding: 12px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .member-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        margin-right: 12px;
    }

    .member-info {
        flex: 1;
    }

    .member-name {
        font-weight: 600;
        color: #374151;
    }

    .member-role {
        font-size: 0.8rem;
        color: #9ca3af;
    }

    .activities-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .activity-item {
        padding: 15px;
        background: #f9fafb;
        border-left: 3px solid #00D9B5;
        border-radius: 8px;
    }

    .activity-title {
        font-weight: 600;
        color: #374151;
        margin-bottom: 5px;
    }

    .activity-date {
        font-size: 0.85rem;
        color: #9ca3af;
    }

    .empty-message {
        text-align: center;
        padding: 30px;
        color: #9ca3af;
    }

    @media (max-width: 1024px) {
        .show-header {
            grid-template-columns: 1fr;
        }

        .content-grid {
            grid-template-columns: 1fr;
        }

        .club-meta-info {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .club-title {
            font-size: 1.5rem;
        }

        .club-image-large {
            height: 250px;
            font-size: 3rem;
        }

        .section-card {
            padding: 20px;
        }

        .btn-actions {
            flex-direction: column;
        }

        .member-item {
            padding: 10px;
        }

        .member-avatar {
            width: 35px;
            height: 35px;
            font-size: 0.9rem;
        }
    }
</style>

<div class="show-container">
    <!-- Header -->
    <div class="show-header">
        <div class="club-image-large">
            @if($club->image)
                <img src="{{ asset('storage/' . $club->image) }}" alt="{{ $club->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 15px;">
            @else
                <span>{{ ucfirst(substr($club->type, 0, 1)) }}</span>
            @endif
        </div>

        <div class="club-info">
            <span class="club-type-badge">{{ $club->type == 'club' ? '🏪 Câu Lạc Bộ' : '👥 Nhóm' }}</span>
            <h1 class="club-title">{{ $club->name }}</h1>
            <div class="club-creator-info">
                👤 Tạo bởi: <strong>{{ $club->creator->name }}</strong>
                <br>📅 Thành lập: <strong>{{ $club->founded_date->format('d/m/Y') }}</strong>
            </div>

            <div class="club-meta-info">
                <div class="meta-item">
                    <div class="meta-value">{{ $club->members->count() }}</div>
                    <div class="meta-label">Thành viên</div>
                </div>
                <div class="meta-item">
                    <div class="meta-value">{{ $club->provinces->count() }}</div>
                    <div class="meta-label">Tỉnh</div>
                </div>
                <div class="meta-item">
                    <div class="meta-value">{{ $club->activities->count() }}</div>
                    <div class="meta-label">Hoạt động</div>
                </div>
            </div>

            <div class="btn-actions">
                @if(Auth::id() === $club->user_id)
                    <a href="{{ route('clubs.edit', $club) }}" class="btn-action btn-edit">✏️ Chỉnh Sửa</a>
                @endif
                <a href="{{ route('clubs.index') }}" class="btn-action btn-back">← Quay Lại</a>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="content-grid">
        <!-- Main Content -->
        <div>
            <!-- Mô Tả -->
            @if($club->description)
                <div class="section-card">
                    <h2 class="section-title">📝 Mô Tả</h2>
                    <div class="description">{{ $club->description }}</div>
                </div>
            @endif

            <!-- Mục Tiêu -->
            @if($club->objectives)
                <div class="section-card">
                    <h2 class="section-title">🎯 Mục Tiêu Hoạt Động</h2>
                    <div class="description">{{ $club->objectives }}</div>
                </div>
            @endif

            <!-- Các Hoạt Động -->
            <div class="section-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f3f4f6;">
                    <h2 class="section-title" style="margin: 0; border: none; padding: 0;">📅 Các Hoạt Động</h2>
                    @if(Auth::id() === $club->user_id)
                        <a href="{{ route('clubs.activities.index', $club) }}" style="padding: 8px 16px; background: #dbeafe; color: #0284c7; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.85rem;">
                            ➕ Xem & Quản Lý
                        </a>
                    @else
                        <a href="{{ route('clubs.activities.index', $club) }}" style="padding: 8px 16px; background: linear-gradient(135deg, #00D9B5 0%, #0db89d 100%); color: white; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.85rem;">
                            📋 Xem Tất Cả
                        </a>
                    @endif
                </div>
                @if($club->activities->count() > 0)
                    <div class="activities-list">
                        @foreach($club->activities->take(3) as $activity)
                            <div class="activity-item">
                                <div class="activity-title">{{ $activity->title }}</div>
                                <div class="activity-date">
                                    🕐 {{ $activity->activity_date->format('d/m/Y H:i') }}
                                    @if($activity->location)
                                        • 📍 {{ $activity->location }}
                                    @endif
                                </div>
                                @if($activity->description)
                                    <p style="font-size: 0.85rem; color: #6b7280; margin-top: 8px;">{{ Str::limit($activity->description, 100) }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @if($club->activities->count() > 3)
                        <div style="text-align: center; margin-top: 15px;">
                            <a href="{{ route('clubs.activities.index', $club) }}" style="color: #00D9B5; font-weight: 600; text-decoration: none;">
                                Xem tất cả {{ $club->activities->count() }} hoạt động →
                            </a>
                        </div>
                    @endif
                @else
                    <div class="empty-message">
                        <i class="fas fa-calendar-alt" style="font-size: 2rem; margin-bottom: 10px;"></i>
                        <p>Chưa có hoạt động nào</p>
                        @if(Auth::id() === $club->user_id)
                            <a href="{{ route('clubs.activities.create', $club) }}" style="color: #00D9B5; font-weight: 600; text-decoration: none; margin-top: 10px; display: inline-block;">
                                ➕ Tạo hoạt động đầu tiên
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Khu Vực Hoạt Động -->
            <div class="section-card">
                <h2 class="section-title">🗺️ Khu Vực Hoạt Động</h2>
                @if($club->provinces->count() > 0)
                    <div class="provinces-list">
                        @foreach($club->provinces as $province)
                            <span class="province-badge">{{ $province->name }}</span>
                        @endforeach
                    </div>
                @else
                    <div class="empty-message">Chưa chọn tỉnh</div>
                @endif
            </div>

            <!-- Thành Viên -->
            <div class="section-card">
                <h2 class="section-title">👥 Thành Viên ({{ $club->members->count() }})</h2>
                @if($club->members->count() > 0)
                    <div class="members-list">
                        @foreach($club->members as $member)
                            <div class="member-item">
                                <div class="member-avatar">{{ Str::upper(substr($member->name, 0, 1)) }}</div>
                                <div class="member-info">
                                    <div class="member-name">{{ $member->name }}</div>
                                    <div class="member-role">
                                        @if($member->pivot->role === 'creator')
                                            👑 Tạo lập viên
                                        @elseif($member->pivot->role === 'admin')
                                            🔧 Quản trị viên
                                        @else
                                            👤 Thành viên
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-message">Chưa có thành viên</div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
