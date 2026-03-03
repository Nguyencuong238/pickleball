{{-- RSVP action button (reusable in detail + participants tab) --}}
{{-- Variables: $club, $activity, $isMember, $userParticipation --}}
@auth
    @if($isMember && $activity->status === 'upcoming')
    <div class="rsvp-button-wrap">
        @if(!$userParticipation || $userParticipation->status === 'cancelled')
            <button class="btn-rsvp btn-join" onclick="rsvpAction('join')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>
                </svg>
                Đăng ký tham gia
            </button>
        @elseif($userParticipation->status === 'confirmed')
            <div class="rsvp-status-inline confirmed">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Bạn đã đăng ký
            </div>
            <button class="btn-rsvp btn-cancel-rsvp" onclick="rsvpAction('cancel')">Hủy đăng ký</button>
        @elseif($userParticipation->status === 'waitlisted')
            <div class="rsvp-status-inline waitlisted">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                Danh sách chờ (#{{ $userParticipation->waitlist_position }})
            </div>
            <button class="btn-rsvp btn-cancel-rsvp" onclick="rsvpAction('cancel')">Hủy đăng ký</button>
        @endif
    </div>
    @endif
@else
    <div class="rsvp-button-wrap">
        <a href="{{ route('login') }}" class="btn-rsvp btn-join">Đăng nhập để tham gia</a>
    </div>
@endauth
