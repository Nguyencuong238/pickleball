{{-- Sidebar - Management team, members grid, upcoming events --}}
<div class="sidebar-column">
    {{-- Activity Areas Card --}}
    @if($club->provinces->count() > 0)
    <div class="sidebar-card">
        <h3 class="sidebar-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                <circle cx="12" cy="10" r="3"/>
            </svg>
            Khu vực hoạt động
        </h3>
        <div class="activity-areas">
            @foreach($club->provinces->take(3) as $index => $province)
            <div class="area-item">
                <div class="area-icon {{ $index === 0 ? 'primary' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>
                <div class="area-info">
                    <h4>{{ $province->name }}</h4>
                    @if($index === 0)
                    <span class="area-tag main">Khu vực chính</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @if($club->provinces->count() > 3)
        <a href="#" class="sidebar-link">Xem tất cả {{ $club->provinces->count() }} khu vực</a>
        @endif
    </div>
    @endif

    {{-- Management Team Card --}}
    @if($managementTeam->count() > 0)
    <div class="sidebar-card">
        <h3 class="sidebar-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            Ban điều hành
        </h3>
        <div class="management-team">
            @foreach($managementTeam as $member)
            <div class="team-member">
                @if($member->avatar)
                    <img src="{{ asset('storage/' . $member->avatar) }}" alt="{{ $member->name }}" class="member-avatar">
                @else
                    <div class="member-avatar member-avatar-placeholder">{{ strtoupper(substr($member->name, 0, 1)) }}</div>
                @endif
                <div class="member-info">
                    <span class="member-name">{{ $member->name }}</span>
                    <span class="member-role {{ $member->pivot->role === 'creator' ? 'president' : ($member->pivot->role === 'admin' ? 'admin' : 'moderator') }}">
                        @if($member->pivot->role === 'creator')
                            Chủ nhiệm CLB
                        @elseif($member->pivot->role === 'admin')
                            Quản trị viên
                        @else
                            Điều hành viên
                        @endif
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Members Card --}}
    <div class="sidebar-card">
        <div class="sidebar-card-header">
            <h3 class="sidebar-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Thành viên
            </h3>
            <span class="member-count">{{ $club->members->count() }} thành viên</span>
        </div>
        <div class="members-grid">
            @foreach($club->members->take(8) as $member)
            <a href="#" class="member-avatar-item" title="{{ $member->name }}">
                @if($member->avatar)
                    <img src="{{ asset('storage/' . $member->avatar) }}" alt="{{ $member->name }}">
                @else
                    <div class="member-avatar-placeholder-grid">{{ strtoupper(substr($member->name, 0, 1)) }}</div>
                @endif
            </a>
            @endforeach
            @if($club->members->count() > 8)
            <a href="#" class="member-avatar-item more" title="Xem thêm">
                <span>+{{ $club->members->count() - 8 }}</span>
            </a>
            @endif
        </div>
        <a href="#" class="sidebar-link">Xem tất cả thành viên</a>
    </div>

    {{-- Upcoming Events Card --}}
    @if($club->activities->count() > 0)
    <div class="sidebar-card">
        <h3 class="sidebar-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Sự kiện sắp tới
        </h3>
        <div class="upcoming-events">
            @foreach($club->activities->where('activity_date', '>=', now())->sortBy('activity_date')->take(3) as $activity)
            <a href="{{ route('clubs.activities.show', [$club, $activity]) }}" class="event-item">
                <div class="event-date">
                    <span class="day">{{ $activity->activity_date->format('d') }}</span>
                    <span class="month">Th{{ $activity->activity_date->format('n') }}</span>
                </div>
                <div class="event-info">
                    <h4>{{ $activity->title }}</h4>
                    @if($activity->location)
                    <p>{{ $activity->location }}</p>
                    @endif
                    @if($activity->participants_count > 0)
                    <span class="event-interested">{{ $activity->participants_count }} người quan tâm</span>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
        <a href="{{ route('clubs.activities.index', $club) }}" class="sidebar-link">Xem tất cả sự kiện</a>
    </div>
    @endif
</div>
