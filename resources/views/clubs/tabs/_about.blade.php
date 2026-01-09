{{-- About Tab Content --}}
<div class="about-tab">
    {{-- Club Info Card --}}
    <div class="sidebar-card">
        <h3 class="sidebar-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            Thông tin CLB
        </h3>

        {{-- Description --}}
        @if($club->description)
        <div class="about-section">
            <h4 class="about-label">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; vertical-align: middle; margin-right: 4px;">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
                Giới thiệu
            </h4>
            <p class="about-content">{{ $club->description }}</p>
        </div>
        @endif

        {{-- Objectives --}}
        @if($club->objectives)
        <div class="about-section">
            <h4 class="about-label">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; vertical-align: middle; margin-right: 4px;">
                    <circle cx="12" cy="12" r="10"/>
                    <circle cx="12" cy="12" r="6"/>
                    <circle cx="12" cy="12" r="2"/>
                </svg>
                Mục tiêu
            </h4>
            <p class="about-content">{{ $club->objectives }}</p>
        </div>
        @endif

        {{-- Details Grid --}}
        <div class="about-details-grid">
            {{-- Type --}}
            <div class="about-detail-item">
                <span class="detail-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
                        <line x1="4" y1="22" x2="4" y2="15"/>
                    </svg>
                </span>
                <div class="detail-info">
                    <span class="detail-label">Loại hình</span>
                    <span class="detail-value">{{ $club->type === 'club' ? 'Câu lạc bộ' : 'Nhóm' }}</span>
                </div>
            </div>

            {{-- Founded Date --}}
            @if($club->founded_date)
            <div class="about-detail-item">
                <span class="detail-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </span>
                <div class="detail-info">
                    <span class="detail-label">Ngày thành lập</span>
                    <span class="detail-value">{{ $club->founded_date->format('d/m/Y') }}</span>
                </div>
            </div>
            @endif

            {{-- Members Count --}}
            <div class="about-detail-item">
                <span class="detail-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </span>
                <div class="detail-info">
                    <span class="detail-label">Thành viên</span>
                    <span class="detail-value">{{ $club->members->count() }} người</span>
                </div>
            </div>

            {{-- Events Count --}}
            <div class="about-detail-item">
                <span class="detail-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                        <path d="M8 14h.01"/>
                        <path d="M12 14h.01"/>
                        <path d="M16 14h.01"/>
                    </svg>
                </span>
                <div class="detail-info">
                    <span class="detail-label">Sự kiện</span>
                    <span class="detail-value">{{ $club->activities->count() }} hoạt động</span>
                </div>
            </div>
        </div>
    </div>

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
        <div class="provinces-list">
            @foreach($club->provinces as $index => $province)
            <div class="province-item">
                <span class="province-icon {{ $index === 0 ? 'primary' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                </span>
                <span class="province-name">{{ $province->name }}</span>
                @if($index === 0)
                <span class="area-tag main">Khu vực chính</span>
                @endif
            </div>
            @endforeach
        </div>
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
        <div class="management-team-list">
            @foreach($managementTeam as $member)
            <div class="team-member-card">
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
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
