{{-- Tab navigation bar --}}
<div class="tab-navigation" role="tablist">
    <button class="tab-btn active" data-tab="detail" role="tab" aria-selected="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
        Chi tiết
    </button>
    <button class="tab-btn" data-tab="participants" role="tab" aria-selected="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        Người tham gia
        <span class="tab-count">{{ $activity->confirmed_participants_count ?? 0 }}</span>
    </button>
    @if($activity->type === 'competition')
    <button class="tab-btn" data-tab="competition" role="tab" aria-selected="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>
        </svg>
        Trận đấu
    </button>
    @endif
    {{-- Matches tab for non-competition activities (hide on recurring templates) --}}
    @if($activity->type !== 'competition' && !$activity->isRecurringTemplate())
    <button class="tab-btn" data-tab="matches" role="tab" aria-selected="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>
        </svg>
        Trận đấu
    </button>
    @endif
</div>
