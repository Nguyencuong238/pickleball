{{-- Participant list with avatars --}}
{{-- Variables: $confirmed (collection), $waitlisted (collection) --}}

@if($confirmed->count() > 0)
<div class="participant-section">
    <h4 class="participant-heading">Đã xác nhận ({{ $confirmed->count() }})</h4>
    <div class="participant-grid">
        @foreach($confirmed as $p)
            <div class="participant-item" title="{{ $p->user->name ?? 'Unknown' }}">
                @if($p->user->avatar ?? null)
                    <img src="{{ storage_url($p->user->avatar) }}" alt="" class="participant-avatar">
                @else
                    <div class="participant-avatar-placeholder">
                        {{ strtoupper(substr($p->user->name ?? '?', 0, 1)) }}
                    </div>
                @endif
                <span class="participant-name">{{ $p->user->name ?? 'Unknown' }}</span>
            </div>
        @endforeach
    </div>
</div>
@endif

@if($waitlisted->count() > 0)
<div class="participant-section">
    <h4 class="participant-heading">Danh sách chờ ({{ $waitlisted->count() }})</h4>
    <div class="participant-grid">
        @foreach($waitlisted as $p)
            <div class="participant-item waitlisted" title="#{{ $p->waitlist_position }} - {{ $p->user->name ?? 'Unknown' }}">
                @if($p->user->avatar ?? null)
                    <img src="{{ storage_url($p->user->avatar) }}" alt="" class="participant-avatar">
                @else
                    <div class="participant-avatar-placeholder waitlisted">
                        {{ strtoupper(substr($p->user->name ?? '?', 0, 1)) }}
                    </div>
                @endif
                <span class="participant-name">{{ $p->user->name ?? 'Unknown' }}</span>
            </div>
        @endforeach
    </div>
</div>
@endif

<style>
.participant-section {
    margin-top: 16px;
}
.participant-heading {
    font-size: 0.85rem;
    font-weight: 600;
    color: #6b7280;
    margin: 0 0 10px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.participant-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.participant-item {
    display: flex;
    align-items: center;
    gap: 8px;
    background: white;
    padding: 6px 12px;
    border-radius: 20px;
    border: 1px solid #e5e7eb;
    font-size: 0.85rem;
}
.participant-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
}
.participant-avatar-placeholder {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00D9B5, #0099CC);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.75rem;
    font-weight: 700;
}
.participant-avatar-placeholder.waitlisted {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}
.participant-name {
    color: #374151;
    font-weight: 500;
}
</style>
