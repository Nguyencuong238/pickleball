@extends('layouts.homeyard')

@section('content')
<main class="main-content" id="mainContent">
    <div class="container">
        <!-- Header Section -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <div>
                <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 10px;">📋 Danh sách Nhóm/CLB</h1>
                <p style="color: #6b7280; font-size: 0.95rem;">Quản lý các nhóm hoặc câu lạc bộ của bạn</p>
            </div>
            <a href="{{ route('homeyard.clubs.create') }}" class="btn btn-add">
                <span>➕</span>
                <span>Tạo Nhóm/CLB Mới</span>
            </a>
        </div>

        <!-- Joined Clubs Section -->
        @if ($joinedClubs->count() > 0)
            <div style="margin-bottom: 40px;">
                <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 20px;">👥 CLB/Nhóm đã tham gia</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
                    @foreach ($joinedClubs as $joinedClub)
                        @php
                            $memberRole = $joinedClub->members->where('id', Auth::id())->first()?->pivot->role;
                            $roleLabels = [
                                'creator' => 'Người sáng lập',
                                'admin' => 'Quản trị viên',
                                'moderator' => 'Điều phối viên',
                                'member' => 'Thành viên',
                            ];
                            $roleColors = [
                                'creator' => '#dc2626',
                                'admin' => '#ea580c',
                                'moderator' => '#2563eb',
                                'member' => '#16a34a',
                            ];
                        @endphp
                        <a href="{{ route('clubs.show', $joinedClub) }}" class="joined-club-card" style="text-decoration: none; color: inherit;">
                            <div class="joined-club-banner">
                                @if ($joinedClub->banner)
                                    <img src="{{ storage_url($joinedClub->banner) }}" alt="{{ $joinedClub->name }}">
                                @else
                                    <div class="joined-banner-placeholder">
                                        {{ $joinedClub->type === 'club' ? '🏆' : '👥' }}
                                    </div>
                                @endif
                            </div>
                            <div class="joined-club-body">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    @if ($joinedClub->image)
                                        <img src="{{ storage_url($joinedClub->image) }}" alt="{{ $joinedClub->name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                    @else
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.9rem;">
                                            {{ $joinedClub->type === 'club' ? '🏆' : '👥' }}
                                        </div>
                                    @endif
                                    <div style="flex: 1; min-width: 0;">
                                        <h4 style="font-size: 1rem; font-weight: 600; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $joinedClub->name }}</h4>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                                            <span style="font-size: 0.75rem; padding: 2px 8px; border-radius: 9999px; color: white; background: {{ $roleColors[$memberRole] ?? '#6b7280' }}; font-weight: 500;">
                                                {{ $roleLabels[$memberRole] ?? $memberRole }}
                                            </span>
                                            <span style="font-size: 0.8rem; color: #6b7280;">{{ $joinedClub->members->count() }} thành viên</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <hr style="border: none; border-top: 1px solid #e5e7eb; margin-bottom: 30px;">
        @endif

        <!-- My Clubs List -->
        @if ($clubs->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; margin-bottom: 40px;">
                @foreach ($clubs as $club)
                    <div class="club-card">
                        <!-- Club Banner -->
                        <div class="club-banner">
                            @if ($club->banner)
                                <img src="{{ storage_url($club->banner) }}" alt="{{ $club->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <div class="banner-placeholder">
                                    @if ($club->type === 'club')
                                        <span>🏆</span>
                                    @else
                                        <span>👥</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Club Logo & Info -->
                        <div class="club-info">
                            @if ($club->image)
                                <img src="{{ storage_url($club->image) }}" alt="{{ $club->name }}" class="club-logo" style="width: 60px; height: 60px; object-fit: cover; border-radius: 50%;">
                            @else
                                <div class="logo-placeholder">
                                    @if ($club->type === 'club')
                                        <span>🏆</span>
                                    @else
                                        <span>👥</span>
                                    @endif
                                </div>
                            @endif

                            <div class="club-details">
                                <h3 class="club-name">
                                    <a href="{{ route('clubs.show', $club) }}" target="blank" style="text-decoration: none; color: inherit; transition: color 0.3s;">
                                        {{ $club->name }}
                                    </a>
                                </h3>
                            </div>
                        </div>

                        <!-- Club Content -->
                        <div class="club-content">
                            @if ($club->description)
                                <p class="club-description">{{ Str::words($club->description, 20, '...') }}</p>
                            @endif

                            <div class="club-meta">
                                <div class="meta-item">
                                    <span class="meta-label">Loại hình:</span>
                                    <span class="meta-value">
                                        <span class="badge bg-primary" style="font-size: 0.75rem; padding: 4px 8px;">{{ $club->type === 'club' ? 'Câu lạc bộ' : 'Nhóm' }}</span>
                                    </span>
                                </div>

                                <div class="meta-item">
                                    <span class="meta-label">Thành viên:</span>
                                    <span class="meta-value">
                                        <span class="badge bg-info" style="font-size: 0.75rem; padding: 4px 8px;">{{ $club->members->count() }} thành viên</span>
                                    </span>
                                </div>

                                @if ($club->founded_date)
                                    <div class="meta-item">
                                        <span class="meta-label">Ngày thành lập:</span>
                                        <span class="meta-value">{{ $club->founded_date->format('d/m/Y') }}</span>
                                    </div>
                                @endif

                                @if ($club->provinces->count() > 0)
                                    <div class="meta-item">
                                        <span class="meta-label">Khu vực hoạt động:</span>
                                        <span class="meta-value">{{ $club->provinces->count() }} tỉnh/TP</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="club-actions">
                            <a href="{{ route('homeyard.clubs.edit', $club) }}" class="btn btn-sm btn-primary">
                                <span>✏️</span> Chỉnh sửa
                            </a>
                            <form action="{{ route('homeyard.clubs.destroy', $club) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">
                                    <span>🗑️</span> Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if ($clubs->hasPages())
                <div style="display: flex; justify-content: center; margin-top: 40px;">
                    {{ $clubs->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div style="text-align: center; padding: 60px 20px; background: #f9fafb; border-radius: var(--radius-xl);">
                <div style="font-size: 4rem; margin-bottom: 20px;">👥</div>
                <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 10px;">Chưa có Nhóm/CLB nào</h3>
                <p style="color: #6b7280; margin-bottom: 30px;">Hãy tạo nhóm hoặc câu lạc bộ đầu tiên của bạn</p>
                <a href="{{ route('homeyard.clubs.create') }}" class="btn btn-primary">
                    <span>➕</span> Tạo Nhóm/CLB
                </a>
            </div>
        @endif
    </div>
</main>

<style>
    .club-card {
        background: white;
        border-radius: var(--radius-xl);
        overflow: hidden;
        box-shadow: var(--shadow-md);
        transition: all var(--transition);
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .club-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .club-banner {
        width: 100%;
        height: 150px;
        overflow: hidden;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }

    .club-banner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .banner-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }

    .club-info {
        padding: 20px 20px 0 20px;
        display: flex;
        gap: 15px;
        align-items: flex-start;
        margin-top: -30px;
        position: relative;
        z-index: 1;
    }

    .club-logo {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: var(--shadow-md);
        flex-shrink: 0;
    }

    .logo-placeholder {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: var(--shadow-md);
        background: var(--bg-light);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .club-details {
        flex: 1;
    }

    .club-name {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 12px 0 8px 0;
        color: var(--text-dark);
    }

    .club-type,
    .club-members {
        font-size: 0.85rem;
        margin: 0;
    }

    .club-content {
        padding: 15px 20px;
        flex: 1;
    }

    .club-description {
        font-size: 0.9rem;
        color: var(--text-secondary);
        margin-bottom: 15px;
        line-height: 1.5;
    }

    .club-meta {
        font-size: 0.85rem;
        color: var(--text-light);
    }

    .meta-item {
        display: flex;
        margin-bottom: 8px;
    }

    .meta-label {
        font-weight: 600;
        margin-right: 8px;
        color: var(--text-secondary);
    }

    .meta-value {
        color: var(--text-light);
    }

    .club-actions {
        padding: 15px 20px;
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 10px;
    }

    .club-actions .btn {
        flex: 1;
        padding: 8px 12px;
        font-size: 0.85rem;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: var(--bg-light);
        border-radius: var(--radius-xl);
    }

    .empty-icon {
        font-size: 4rem;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: var(--text-secondary);
        margin-bottom: 30px;
    }

    .joined-club-card {
        background: white;
        border-radius: var(--radius-lg, 12px);
        overflow: hidden;
        box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.1));
        transition: all 0.2s ease;
        display: block;
    }

    .joined-club-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md, 0 4px 12px rgba(0,0,0,0.15));
    }

    .joined-club-banner {
        width: 100%;
        height: 80px;
        overflow: hidden;
        background: linear-gradient(135deg, var(--primary-color, #3b82f6), var(--secondary-color, #8b5cf6));
    }

    .joined-club-banner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .joined-banner-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .joined-club-body {
        padding: 12px 16px;
    }

    @media (max-width: 768px) {
        .club-actions .btn {
            padding: 6px 8px;
            font-size: 0.75rem;
        }
    }
</style>
@endsection
