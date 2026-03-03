{{-- Participants tab content --}}
<div class="participants-tab">
    <div class="participants-header">
        <h3>
            Người tham gia
            ({{ $activity->confirmed_participants_count ?? 0 }}@if($activity->max_participants) / {{ $activity->max_participants }}@endif)
        </h3>
        @if(($activity->waitlisted_participants_count ?? 0) > 0)
            <span class="waitlist-badge-sm">{{ $activity->waitlisted_participants_count }} đang chờ</span>
        @endif
    </div>

    {{-- Skill level notice --}}
    @if($activity->min_skill_level || $activity->max_skill_level)
        <div class="skill-notice-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            Trình độ OPR: {{ $activity->min_skill_level ?? '1.0' }} - {{ $activity->max_skill_level ?? '6.0' }}
        </div>
    @endif

    @include('clubs.activities.partials._participant-list', [
        'confirmed' => $activity->confirmedParticipants,
        'waitlisted' => $activity->waitlistedParticipants,
    ])

    {{-- RSVP button --}}
    @include('clubs.activities.partials._rsvp-button')
</div>
