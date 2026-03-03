{{-- Participant list with card-row layout --}}
{{-- Variables: $confirmed (collection), $waitlisted (collection) --}}

@if($confirmed->count() > 0)
<div class="participant-section">
    <h4 class="participant-heading">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
        Đã xác nhận ({{ $confirmed->count() }})
    </h4>
    <div class="participant-card-list">
        @foreach($confirmed as $p)
        <div class="participant-card">
            @if($p->user->avatar ?? null)
                <img src="{{ storage_url($p->user->avatar) }}" alt="" class="participant-avatar-lg">
            @else
                <div class="participant-avatar-lg participant-avatar-lg-placeholder">
                    {{ strtoupper(substr($p->user->name ?? '?', 0, 1)) }}
                </div>
            @endif
            <div class="participant-info">
                <span class="participant-name">{{ $p->user->name ?? 'Unknown' }}</span>
                @if($p->user->oprs_level ?? null)
                    <span class="participant-opr">OPR {{ number_format($p->user->oprs_level, 1) }}</span>
                @endif
            </div>
            <div class="participant-status-icon confirmed">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($waitlisted->count() > 0)
<div class="participant-section">
    <h4 class="participant-heading waitlisted">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </svg>
        Danh sách chờ ({{ $waitlisted->count() }})
    </h4>
    <div class="participant-card-list">
        @foreach($waitlisted as $p)
        <div class="participant-card">
            @if($p->user->avatar ?? null)
                <img src="{{ storage_url($p->user->avatar) }}" alt="" class="participant-avatar-lg">
            @else
                <div class="participant-avatar-lg participant-avatar-lg-placeholder waitlisted">
                    {{ strtoupper(substr($p->user->name ?? '?', 0, 1)) }}
                </div>
            @endif
            <div class="participant-info">
                <span class="participant-name">{{ $p->user->name ?? 'Unknown' }}</span>
                @if($p->user->oprs_level ?? null)
                    <span class="participant-opr">OPR {{ number_format($p->user->oprs_level, 1) }}</span>
                @endif
            </div>
            <span class="waitlist-position">#{{ $p->waitlist_position }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($confirmed->count() === 0 && $waitlisted->count() === 0)
<div class="participant-empty-state">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="40" height="40">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
    </svg>
    <p class="empty-title">Chưa có người tham gia</p>
    <p class="empty-sub">Hãy là người đầu tiên đăng ký!</p>
</div>
@endif
