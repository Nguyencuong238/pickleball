{{-- RSVP panel: join/cancel button, spots counter, participant avatars --}}
{{-- Variables: $club, $activity, $isMember, $userParticipation, $isManagement --}}
<div class="rsvp-panel" id="rsvp-panel">
    <h3 class="panel-title">👥 Tham Gia</h3>

    <div class="rsvp-stats">
        <span class="spots-count" id="spots-count">
            {{ $activity->confirmed_participants_count ?? $activity->confirmedParticipants->count() }} / {{ $activity->max_participants ?? '--' }} người
        </span>
        @if(($activity->waitlisted_participants_count ?? $activity->waitlistedParticipants->count()) > 0)
            <span class="waitlist-badge">
                {{ $activity->waitlisted_participants_count ?? $activity->waitlistedParticipants->count() }} đang chờ
            </span>
        @endif
    </div>

    {{-- Skill level notice --}}
    @if($activity->min_skill_level || $activity->max_skill_level)
        <div class="skill-notice">
            ⭐ Trình độ OPR:
            {{ $activity->min_skill_level ?? '1.0' }} - {{ $activity->max_skill_level ?? '6.0' }}
        </div>
    @endif

    {{-- RSVP button for members --}}
    @auth
        @if($isMember && $activity->status === 'upcoming')
            <div class="rsvp-actions" id="rsvp-actions">
                @if(!$userParticipation || $userParticipation->status === 'cancelled')
                    <button class="btn-rsvp btn-join" onclick="rsvpAction('join')">
                        Đăng ký tham gia
                    </button>
                @elseif($userParticipation->status === 'confirmed')
                    <div class="rsvp-status confirmed">✅ Bạn đã đăng ký</div>
                    <button class="btn-rsvp btn-cancel-rsvp" onclick="rsvpAction('cancel')">
                        Hủy đăng ký
                    </button>
                @elseif($userParticipation->status === 'waitlisted')
                    <div class="rsvp-status waitlisted">
                        ⏳ Danh sách chờ (#{{ $userParticipation->waitlist_position }})
                    </div>
                    <button class="btn-rsvp btn-cancel-rsvp" onclick="rsvpAction('cancel')">
                        Hủy đăng ký
                    </button>
                @endif
            </div>
        @endif
    @endauth

    {{-- Participant list --}}
    @include('clubs.activities.partials._participant-list', [
        'confirmed' => $activity->confirmedParticipants,
        'waitlisted' => $activity->waitlistedParticipants,
    ])
</div>

<style>
.rsvp-panel {
    background: #f8fafc;
    border-radius: 12px;
    padding: 24px;
    margin-top: 24px;
}
.panel-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 16px 0;
}
.rsvp-stats {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.spots-count {
    font-weight: 600;
    color: #374151;
}
.waitlist-badge {
    background: #fef3c7;
    color: #92400e;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}
.skill-notice {
    background: #ede9fe;
    color: #6d28d9;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 0.85rem;
    margin-bottom: 16px;
}
.rsvp-actions {
    margin-bottom: 16px;
}
.btn-rsvp {
    padding: 10px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}
.btn-join {
    background: linear-gradient(135deg, #00D9B5, #0db89d);
    color: white;
}
.btn-join:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,217,181,0.3);
}
.btn-cancel-rsvp {
    background: #fee2e2;
    color: #b91c1c;
    margin-top: 8px;
}
.btn-cancel-rsvp:hover {
    background: #fecaca;
}
.rsvp-status {
    padding: 8px 14px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 8px;
}
.rsvp-status.confirmed {
    background: #dcfce7;
    color: #16a34a;
}
.rsvp-status.waitlisted {
    background: #fef3c7;
    color: #92400e;
}
.rsvp-message {
    padding: 10px 14px;
    border-radius: 8px;
    margin-bottom: 12px;
    font-size: 0.9rem;
    display: none;
}
.rsvp-message.success {
    background: #dcfce7;
    color: #16a34a;
}
.rsvp-message.error {
    background: #fee2e2;
    color: #b91c1c;
}
</style>

<script>
var _rsvpBusy = false;
function rsvpAction(action) {
    if (_rsvpBusy) return;
    _rsvpBusy = true;
    var btns = document.querySelectorAll('.btn-rsvp');
    btns.forEach(function(b) { b.disabled = true; });

    var clubSlug = '{{ $club->slug }}';
    var activityId = '{{ $activity->id }}';
    var url = '/clubs/' + clubSlug + '/activities/' + activityId + '/rsvp';
    var method = action === 'join' ? 'POST' : 'DELETE';

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra');
            _rsvpBusy = false;
            btns.forEach(function(b) { b.disabled = false; });
        }
    })
    .catch(function() {
        alert('Không thể kết nối. Vui lòng thử lại.');
        _rsvpBusy = false;
        btns.forEach(function(b) { b.disabled = false; });
    });
}
</script>
