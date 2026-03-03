{{-- Detail tab content - Reclub-inspired sections --}}
@php
    // Google Calendar URL
    $calStart = $activity->activity_date->format('Ymd\THis');
    $calEnd = $activity->end_time
        ? $activity->activity_date->copy()->setTimeFromTimeString($activity->end_time)->format('Ymd\THis')
        : $activity->activity_date->copy()->addHour()->format('Ymd\THis');
    $calUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
        . '&text=' . urlencode($activity->title)
        . '&dates=' . $calStart . '/' . $calEnd
        . '&location=' . urlencode($activity->location ?? '')
        . '&details=' . urlencode($activity->description ?? '')
        . '&ctz=Asia/Ho_Chi_Minh';
@endphp

<div class="detail-tab">
    {{-- Organizer section --}}
    <div class="detail-section organizer-section">
        <div class="organizer-avatar-wrap">
            @if($club->image)
                <img src="{{ storage_url($club->image) }}" alt="{{ $club->name }}" class="organizer-avatar">
            @else
                <div class="organizer-avatar-placeholder">
                    {{ strtoupper(substr($club->name, 0, 2)) }}
                </div>
            @endif
        </div>
        <div class="organizer-info">
            <strong>{{ $activity->creator->name ?? $club->name }}</strong>
            <span class="organizer-sub">
                {{ $typeLabels[$activity->type] ?? $activity->type }}
                @if($activity->type === 'recurring' && $activity->parent)
                    &middot; Lặp lại từ "{{ $activity->parent->title }}"
                @endif
            </span>
        </div>
        <a href="{{ route('clubs.activities.index', $club) }}" class="btn-outline-sm">Xem lịch</a>
    </div>

    {{-- Participant preview --}}
    <div class="detail-section participant-preview" onclick="switchTab('participants')" role="button" tabindex="0">
        <div class="section-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div class="section-content">
            <div class="avatar-row">
                @forelse($activity->confirmedParticipants->take(5) as $p)
                    @if($p->user->avatar ?? null)
                        <img src="{{ storage_url($p->user->avatar) }}" alt="{{ $p->user->name ?? '' }}" class="avatar-sm">
                    @else
                        <div class="avatar-sm avatar-sm-placeholder">
                            {{ strtoupper(substr($p->user->name ?? '?', 0, 1)) }}
                        </div>
                    @endif
                @empty
                    <span class="text-muted">Chưa có người tham gia</span>
                @endforelse
                @if(($activity->confirmed_participants_count ?? 0) > 5)
                    <span class="avatar-more">+{{ $activity->confirmed_participants_count - 5 }}</span>
                @endif
            </div>
            <span class="section-label">
                {{ $activity->confirmed_participants_count ?? 0 }}
                @if($activity->max_participants) / {{ $activity->max_participants }} @endif
                người tham gia
            </span>
        </div>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="chevron-right">
            <polyline points="9 18 15 12 9 6"/>
        </svg>
    </div>

    {{-- DateTime --}}
    <div class="detail-section">
        <div class="section-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="section-content">
            <strong>{{ $activity->activity_date->isoFormat('dddd, D [tháng] M') }}</strong>
            <span>
                Vào lúc {{ $activity->activity_date->format('H:i') }}
                @if($activity->end_time)
                    - {{ $activity->end_time }}
                @endif
            </span>
            <a href="{{ $calUrl }}" target="_blank" rel="noopener" class="section-action-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Thêm vào lịch
            </a>
        </div>
    </div>

    {{-- Location --}}
    @if($activity->location)
    <div class="detail-section">
        <div class="section-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                <circle cx="12" cy="10" r="3"/>
            </svg>
        </div>
        <div class="section-content">
            <strong>{{ $activity->location }}</strong>
            <a href="https://maps.google.com/?q={{ urlencode($activity->location) }}" target="_blank" rel="noopener" class="section-action-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                    <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                </svg>
                Hiển thị trong bản đồ
            </a>
        </div>
    </div>
    @endif

    {{-- Skill level --}}
    @if($activity->min_skill_level || $activity->max_skill_level)
    <div class="detail-section">
        <div class="section-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
        </div>
        <div class="section-content">
            <strong>Trình độ OPR</strong>
            <span>{{ $activity->min_skill_level ?? '1.0' }} - {{ $activity->max_skill_level ?? '6.0' }}</span>
        </div>
    </div>
    @endif

    {{-- Activity type --}}
    <div class="detail-section">
        <div class="section-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                <line x1="7" y1="7" x2="7.01" y2="7"/>
            </svg>
        </div>
        <div class="section-content">
            <strong>Loại hình</strong>
            <span class="type-badge-inline type-{{ $activity->type }}">{{ $typeLabels[$activity->type] ?? $activity->type }}</span>
        </div>
    </div>

    {{-- Description --}}
    @if($activity->description)
    <div class="detail-description">
        <h4>Mô tả chi tiết</h4>
        <p>{!! nl2br(e($activity->description)) !!}</p>
    </div>
    @endif

    {{-- Club info --}}
    <div class="detail-club-info">
        @if($club->image)
            <img src="{{ storage_url($club->image) }}" alt="{{ $club->name }}" class="club-avatar-sm">
        @else
            <div class="club-avatar-sm club-avatar-sm-placeholder">
                {{ strtoupper(substr($club->name, 0, 2)) }}
            </div>
        @endif
        <div>
            <span class="text-muted">Hoạt động của</span>
            <a href="{{ route('clubs.show', $club) }}">{{ $club->name }}</a>
        </div>
    </div>

    {{-- RSVP button --}}
    @include('clubs.activities.partials._rsvp-button')
</div>
