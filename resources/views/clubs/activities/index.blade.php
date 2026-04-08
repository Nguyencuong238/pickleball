@extends('layouts.homeyard')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/layout-sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-cards.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/tournament-dashboard/components-buttons-alerts.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/club-activity-index.css') }}">
@endsection

@section('content')
@php
    $typeLabels = ['one_off' => 'Buổi chơi', 'recurring' => 'Lịch cố định', 'competition' => 'Giải đấu', 'open_play' => 'Chơi mở'];
    $statusLabels = ['upcoming' => 'Sắp tới', 'completed' => 'Đã hoàn thành', 'cancelled' => 'Đã hủy'];
    $totalCount = $statusCounts->sum();
@endphp

<div class="main-content">
    {{-- Header --}}
    <div class="top-header">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('homeyard.clubs.index') }}" class="td-btn td-btn-ghost">
                &larr; Quay lại
            </a>
            <h2 class="page-title" style="margin: 0;">Hoạt Động - {{ $club->name }}</h2>
        </div>
        @if($isManagement)
            <a href="{{ route('clubs.activities.create', $club) }}" class="td-btn td-btn-primary">
                + Thêm Hoạt Động
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="td-alert td-alert-success">{{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="ca-idx-stats">
        <a href="{{ route('clubs.activities.index', [$club, 'type' => request('type'), 'sort' => request('sort')]) }}" class="ca-idx-stat {{ !request('status') ? 'active' : '' }}">
            <div class="ca-idx-stat-value">{{ $totalCount }}</div>
            <div class="ca-idx-stat-label">Tất cả</div>
        </a>
        <a href="{{ route('clubs.activities.index', array_merge([$club], request()->query(), ['status' => 'upcoming', 'page' => null])) }}" class="ca-idx-stat ca-idx-stat--upcoming {{ request('status') === 'upcoming' ? 'active' : '' }}">
            <div class="ca-idx-stat-value">{{ $statusCounts->get('upcoming', 0) }}</div>
            <div class="ca-idx-stat-label">Sắp tới</div>
        </a>
        <a href="{{ route('clubs.activities.index', array_merge([$club], request()->query(), ['status' => 'completed', 'page' => null])) }}" class="ca-idx-stat ca-idx-stat--completed {{ request('status') === 'completed' ? 'active' : '' }}">
            <div class="ca-idx-stat-value">{{ $statusCounts->get('completed', 0) }}</div>
            <div class="ca-idx-stat-label">Đã hoàn thành</div>
        </a>
        <a href="{{ route('clubs.activities.index', array_merge([$club], request()->query(), ['status' => 'cancelled', 'page' => null])) }}" class="ca-idx-stat ca-idx-stat--cancelled {{ request('status') === 'cancelled' ? 'active' : '' }}">
            <div class="ca-idx-stat-value">{{ $statusCounts->get('cancelled', 0) }}</div>
            <div class="ca-idx-stat-label">Đã hủy</div>
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('clubs.activities.index', $club) }}" class="ca-idx-filters">
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        <select name="type" class="ca-idx-filter-select" onchange="this.form.submit()">
            <option value="">Loại: Tất cả</option>
            @foreach($typeLabels as $value => $label)
                <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="sort" class="ca-idx-filter-select" onchange="this.form.submit()">
            <option value="date_desc" {{ request('sort', 'date_desc') === 'date_desc' ? 'selected' : '' }}>Mới nhất</option>
            <option value="date_asc" {{ request('sort') === 'date_asc' ? 'selected' : '' }}>Cũ nhất</option>
        </select>
        @if(request('type') || request('sort', 'date_desc') !== 'date_desc')
            <a href="{{ route('clubs.activities.index', array_merge([$club], ['status' => request('status')])) }}" class="ca-idx-filter-reset">Đặt lại</a>
        @endif
    </form>

    {{-- Table --}}
    @if($activities->count() > 0)
        <div class="ca-idx-table-wrap">
            <table class="ca-idx-table">
                <thead>
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Loại</th>
                        <th>Trạng thái</th>
                        <th>Ngày</th>
                        <th>Địa điểm</th>
                        <th>Người tham gia</th>
                        @if($isManagement)
                            <th>Thao tác</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                        <tr>
                            <td>
                                <a href="{{ route('clubs.activities.show', [$club, $activity]) }}" class="ca-idx-title-link">
                                    {{ $activity->title }}
                                </a>
                                @if($activity->fee_gems)
                                    <span style="display:inline-block;background:#fbbf24;color:#78350f;font-size:11px;padding:1px 6px;border-radius:4px;margin-left:6px;font-weight:600;">{{ $activity->fee_gems }} Gems</span>
                                @endif
                            </td>
                            <td>
                                <span class="ca-idx-type-badge type-{{ $activity->type }}">{{ $typeLabels[$activity->type] ?? $activity->type }}</span>
                            </td>
                            <td>
                                <span class="ca-idx-status ca-idx-status--{{ $activity->status }}">{{ $statusLabels[$activity->status] ?? $activity->status }}</span>
                            </td>
                            <td class="ca-idx-meta">{{ $activity->activity_date->format('d/m/Y H:i') }}</td>
                            <td class="ca-idx-meta">{{ $activity->location ?: '-' }}</td>
                            <td class="ca-idx-meta">
                                @if($activity->max_participants)
                                    {{ $activity->confirmed_participants_count ?? 0 }}/{{ $activity->max_participants }}
                                @else
                                    -
                                @endif
                            </td>
                            @if($isManagement)
                                <td>
                                    <div class="ca-idx-actions">
                                        <a href="{{ route('clubs.activities.edit', [$club, $activity]) }}" class="ca-idx-btn ca-idx-btn--edit">Chỉnh sửa</a>
                                        <form action="{{ route('clubs.activities.destroy', [$club, $activity]) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ca-idx-btn ca-idx-btn--delete" onclick="return confirm('Bạn chắc chắn muốn xóa hoạt động này?')">Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="ca-idx-pagination">
            {{ $activities->appends(request()->query())->links() }}
        </div>
    @else
        <div class="ca-idx-empty">
            <h3>Chưa có hoạt động nào</h3>
            <p>Hãy tạo hoạt động đầu tiên cho câu lạc bộ/nhóm của bạn!</p>
            @if($isManagement)
                <a href="{{ route('clubs.activities.create', $club) }}" class="td-btn td-btn-primary">+ Tạo Hoạt Động</a>
            @endif
        </div>
    @endif
</div>
@endsection
