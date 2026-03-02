@extends('layouts.front')

@section('title', $activity->title . ' | ' . $club->name)

@section('content')
@include('clubs.activities.partials._show-styles')

<div class="activity-container">
    <a href="{{ route('clubs.activities.index', $club) }}" class="btn-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; vertical-align: middle; margin-right: 5px;">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
        Quay lại danh sách
    </a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="activity-detail-card">
        <div class="activity-detail-header">
            <h1 class="activity-detail-title">{{ $activity->title }}</h1>
            <div class="badge-row">
                @php
                    $typeLabels = ['one_off' => 'Buổi chơi', 'recurring' => 'Lịch cố định', 'competition' => 'Giải đấu'];
                @endphp
                <span class="type-badge type-{{ $activity->type }}">{{ $typeLabels[$activity->type] ?? $activity->type }}</span>
                <span class="activity-status status-{{ $activity->status }}">
                    @if($activity->status === 'upcoming') Sắp diễn ra
                    @elseif($activity->status === 'completed') Đã hoàn thành
                    @else Đã hủy
                    @endif
                </span>
            </div>
        </div>

        <div class="activity-detail-meta">
            <div class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <span><strong>Ngày giờ:</strong> {{ $activity->activity_date->format('d/m/Y') }} lúc {{ $activity->activity_date->format('H:i') }}
                    @if($activity->end_time) - {{ $activity->end_time }} @endif
                </span>
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

            @if($activity->max_participants)
            <div class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span><strong>Số người:</strong> {{ $activity->confirmed_participants_count ?? 0 }} / {{ $activity->max_participants }}</span>
            </div>
            @endif

            @if($activity->min_skill_level || $activity->max_skill_level)
            <div class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
                <span><strong>Trình độ OPR:</strong> {{ $activity->min_skill_level ?? '1.0' }} - {{ $activity->max_skill_level ?? '6.0' }}</span>
            </div>
            @endif

            @if($activity->type === 'recurring' && $activity->parent)
            <div class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                </svg>
                <span><strong>Lịch cố định:</strong> Buổi lặp lại của "{{ $activity->parent->title }}"</span>
            </div>
            @endif
        </div>

        @if($activity->description)
        <div class="activity-detail-description">
            <h4>Mô tả chi tiết</h4>
            <p>{!! nl2br(e($activity->description)) !!}</p>
        </div>
        @endif

        <div class="club-info-box">
            @if($club->image)
                <img src="{{ storage_url($club->image) }}" alt="{{ $club->name }}" class="club-avatar">
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

        {{-- Management actions --}}
        @if($isManagement)
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
                    </svg>
                    Xóa hoạt động
                </button>
            </form>
        </div>
        @endif
    </div>

    {{-- RSVP Panel - shown for ALL activity types --}}
    @include('clubs.activities.partials._rsvp-panel')

    {{-- Competition Panel - shown for competition type only --}}
    @if($activity->type === 'competition')
        @include('clubs.activities.partials._competition-panel')
    @endif
</div>

@endsection
