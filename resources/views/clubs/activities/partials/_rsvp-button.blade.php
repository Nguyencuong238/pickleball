{{-- RSVP action button (reusable in detail + participants tab) --}}
{{-- Variables: $club, $activity, $isMember, $userParticipation, $userGemBalance --}}
@auth
    @if($isMember && $activity->status === 'upcoming')
    <div class="rsvp-button-wrap">
        @if(!$userParticipation || $userParticipation->status === 'cancelled')
            @if($activity->hasFee())
                <div style="text-align:center;margin-bottom:8px;font-size:13px;color:#6b7280;">
                    Số dư: <strong style="color:#059669;">{{ $userGemBalance ?? 0 }} Gems</strong>
                </div>
            @endif
            @if($activity->hasFee() && ($userGemBalance ?? 0) < $activity->fee_gems)
                <button class="btn-rsvp btn-join" disabled style="opacity:0.5;cursor:not-allowed;">
                    Không đủ Gems (cần {{ $activity->fee_gems }} Gems)
                </button>
                <a href="{{ route('user.gems.index') }}" style="display:block;text-align:center;margin-top:6px;color:#d97706;font-size:13px;">Nạp Gems</a>
            @else
                <button class="btn-rsvp btn-join" onclick="rsvpAction('join')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>
                    </svg>
                    @if($activity->hasFee())
                        Đăng ký ({{ $activity->fee_gems }} Gems)
                    @else
                        Đăng ký tham gia
                    @endif
                </button>
            @endif
        @elseif($userParticipation->status === 'confirmed')
            <div class="rsvp-status-inline confirmed">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Bạn đã đăng ký
                @if($activity->hasFee())
                    <span style="color:#059669;font-size:12px;margin-left:4px;">(đã thanh toán {{ $activity->fee_gems }} Gems)</span>
                @endif
            </div>
            @if($activity->hasFee() && $activity->activity_date > now())
                <button class="btn-rsvp btn-cancel-rsvp" onclick="rsvpAction('cancel')">Hủy đăng ký (hoàn {{ $activity->fee_gems }} Gems)</button>
            @else
                <button class="btn-rsvp btn-cancel-rsvp" onclick="rsvpAction('cancel')">Hủy đăng ký</button>
            @endif
        @elseif($userParticipation->status === 'waitlisted')
            <div class="rsvp-status-inline waitlisted">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                Danh sách chờ (#{{ $userParticipation->waitlist_position }})
            </div>
            @if($activity->hasFee())
                <p style="text-align:center;font-size:12px;color:#6b7280;margin:4px 0;">Sẽ bị trừ {{ $activity->fee_gems }} Gems khi có chỗ.</p>
            @endif
            <button class="btn-rsvp btn-cancel-rsvp" onclick="rsvpAction('cancel')">Hủy chờ</button>
        @endif
    </div>
    @endif
@else
    <div class="rsvp-button-wrap">
        <a href="{{ route('login') }}" class="btn-rsvp btn-join">Đăng nhập để tham gia</a>
    </div>
@endauth
