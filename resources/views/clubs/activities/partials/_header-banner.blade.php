{{-- Gradient header banner - Reclub-inspired --}}
<div class="activity-header-banner">
    <div class="header-top-bar">
        <a href="{{ route('clubs.activities.index', $club) }}" class="header-back-btn" title="Quay lại">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </a>
        <div class="header-datetime">
            {{ $activity->activity_date->format('H:i') }} &middot;
            {{ $activity->activity_date->isoFormat('dddd, D/M') }}
        </div>
        <div class="header-actions">
            <button class="header-action-btn" onclick="shareActivity()" title="Chia sẻ">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                </svg>
            </button>
            @if($isManagement)
            <div class="header-menu-wrapper">
                <button class="header-action-btn" onclick="toggleHeaderMenu()" title="Tùy chọn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/>
                    </svg>
                </button>
                <div class="header-dropdown" id="header-dropdown">
                    @if($activity->isOpenPlay())
                    <a href="#" class="dropdown-item" onclick="event.preventDefault(); generateQrCode();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                        </svg>
                        Lấy mã QR Check-in
                    </a>
                    <a href="{{ route('club.activity.dashboard', [$club->slug, $activity]) }}" class="dropdown-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/><circle cx="17.5" cy="17.5" r="3.5"/>
                        </svg>
                        Bảng điều khiển
                    </a>
                    @endif
                    <a href="{{ route('clubs.activities.edit', [$club, $activity]) }}" class="dropdown-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Chỉnh sửa
                    </a>
                    <form method="POST" action="{{ route('clubs.activities.destroy', [$club, $activity]) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="dropdown-item dropdown-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa hoạt động này?')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                            Xóa hoạt động
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="header-badge-row">
        <span class="header-type-badge type-{{ $activity->type }}">{{ $typeLabels[$activity->type] ?? $activity->type }}</span>
        <span class="header-status-badge status-{{ $activity->status }}">
            @if($activity->status === 'upcoming') Sắp diễn ra
            @elseif($activity->status === 'completed') Đã hoàn thành
            @else Đã hủy
            @endif
        </span>
    </div>

    <h1 class="header-title">{{ $activity->title }}</h1>
</div>
