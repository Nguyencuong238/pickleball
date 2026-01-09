{{-- Members Tab Content --}}
<div class="members-tab" x-data="membersTab()">
    {{-- Header with Role Filter and Admin Actions --}}
    <div class="members-header sidebar-card">
        <div class="members-header-row">
            <h3 class="sidebar-card-title" style="margin-bottom: 0;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Thành viên (<span x-text="totalMembers"></span>)
            </h3>

            @if($canPost)
            <a href="{{ route('clubs.join-requests', $club) }}" class="btn btn-outline btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                Yêu cầu tham gia
                @php
                    $pendingCount = $club->joinRequests()->where('status', 'pending')->count();
                @endphp
                @if($pendingCount > 0)
                <span class="badge-count">{{ $pendingCount }}</span>
                @endif
            </a>
            @endif
        </div>

        {{-- Role Filter --}}
        <div class="members-filters">
            <button class="filter-btn" :class="{ 'active': roleFilter === '' }" @click="filterByRole('')">
                Tất cả
            </button>
            <button class="filter-btn" :class="{ 'active': roleFilter === 'creator' }" @click="filterByRole('creator')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                Chủ nhiệm
            </button>
            <button class="filter-btn" :class="{ 'active': roleFilter === 'admin' }" @click="filterByRole('admin')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                Admin
            </button>
            <button class="filter-btn" :class="{ 'active': roleFilter === 'moderator' }" @click="filterByRole('moderator')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
                Điều hành
            </button>
            <button class="filter-btn" :class="{ 'active': roleFilter === 'member' }" @click="filterByRole('member')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Thành viên
            </button>
        </div>
    </div>

    {{-- Members Grid --}}
    <div class="members-grid-full">
        <template x-if="loading">
            <div class="loading-spinner" style="grid-column: 1 / -1;">
                <svg class="spinner" viewBox="0 0 50 50">
                    <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                </svg>
                Đang tải...
            </div>
        </template>

        <template x-for="member in filteredMembers" :key="member.id">
            <div class="member-card sidebar-card">
                <div class="member-card-header">
                    <template x-if="member.avatar">
                        <img :src="'/storage/' + member.avatar" :alt="member.name" class="member-card-avatar">
                    </template>
                    <template x-if="!member.avatar">
                        <div class="member-card-avatar member-avatar-placeholder" x-text="member.name.charAt(0).toUpperCase()"></div>
                    </template>

                    <div class="member-card-info">
                        <span class="member-card-name" x-text="member.name"></span>
                        <span class="member-card-role" :class="member.pivot.role" x-text="getRoleText(member.pivot.role)"></span>
                    </div>

                    {{-- Admin Actions Dropdown --}}
                    @if($canPost)
                    <div class="member-actions-wrapper" x-data="{ menuOpen: false }">
                        <template x-if="member.id !== {{ Auth::id() ?? 'null' }} && member.pivot.role !== 'creator'">
                            <button class="member-menu-btn" @click="menuOpen = !menuOpen">
                                <svg viewBox="0 0 24 24" fill="currentColor" style="width: 20px; height: 20px;">
                                    <circle cx="12" cy="5" r="2"/>
                                    <circle cx="12" cy="12" r="2"/>
                                    <circle cx="12" cy="19" r="2"/>
                                </svg>
                            </button>
                        </template>
                        <div class="member-menu-dropdown" x-show="menuOpen" @click.away="menuOpen = false" x-cloak>
                            {{-- Promote/Demote Options --}}
                            <template x-if="member.pivot.role === 'member'">
                                <button class="menu-item" @click="updateRole(member, 'moderator'); menuOpen = false">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                                        <polyline points="18 15 12 9 6 15"/>
                                    </svg>
                                    Nâng cấp thành Điều hành
                                </button>
                            </template>
                            <template x-if="member.pivot.role === 'moderator'">
                                <div>
                                    <button class="menu-item" @click="updateRole(member, 'admin'); menuOpen = false">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                                            <polyline points="18 15 12 9 6 15"/>
                                        </svg>
                                        Nâng cấp thành Admin
                                    </button>
                                    <button class="menu-item" @click="updateRole(member, 'member'); menuOpen = false">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                                            <polyline points="6 9 12 15 18 9"/>
                                        </svg>
                                        Hạ cấp thành Thành viên
                                    </button>
                                </div>
                            </template>
                            <template x-if="member.pivot.role === 'admin'">
                                <button class="menu-item" @click="updateRole(member, 'moderator'); menuOpen = false">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                                        <polyline points="6 9 12 15 18 9"/>
                                    </svg>
                                    Hạ cấp thành Điều hành
                                </button>
                            </template>
                            {{-- Remove Member --}}
                            <button class="menu-item menu-item-danger" @click="removeMember(member); menuOpen = false">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="8.5" cy="7" r="4"/>
                                    <line x1="18" y1="8" x2="23" y2="13"/>
                                    <line x1="23" y1="8" x2="18" y2="13"/>
                                </svg>
                                Xóa khỏi CLB
                            </button>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="member-card-meta">
                    <span class="joined-date">Tham gia: <span x-text="formatDate(member.pivot.joined_at)"></span></span>
                </div>
            </div>
        </template>

        <template x-if="!loading && filteredMembers.length === 0">
            <div class="empty-state sidebar-card" style="grid-column: 1 / -1;">
                <div class="empty-state-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 48px; height: 48px;">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3>Không tìm thấy thành viên</h3>
                <p>Thử thay đổi bộ lọc</p>
            </div>
        </template>
    </div>
</div>
