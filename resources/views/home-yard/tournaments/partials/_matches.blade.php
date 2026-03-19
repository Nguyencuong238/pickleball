{{-- Matches & Scoring Partial --}}
@php
    $indexUrl       = route('tournament-manage.matches.index', $tournament);
    $createGrpUrl   = route('tournament-manage.matches.createForGroups', $tournament);
    $scheduleUrl    = route('tournament-manage.matches.updateSchedule', [$tournament, '__ID__']);
@endphp

<div x-data="matchManager({
    tournamentId:    {{ $tournament->id }},
    indexUrl:        '{{ $indexUrl }}',
    createGroupsUrl: '{{ $createGrpUrl }}',
    scheduleUrl:     '{{ $scheduleUrl }}',
    csrf:            document.querySelector('meta[name=csrf-token]').content,
})" x-init="init()" @destroy.window="destroy()">

    {{-- Page header --}}
    <div class="td-card" style="margin-bottom:0;border-radius:10px 10px 0 0;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div>
                <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;margin:0;">Trận đấu</h2>
                <p style="font-size:0.82rem;color:#64748b;margin:4px 0 0;">
                    Quản lý lịch thi đấu và cập nhật tỉ số
                </p>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <span x-show="stats.total > 0"
                      style="font-size:0.8rem;color:#64748b;"
                      x-text="`${stats.completed}/${stats.total} trận hoàn thành`"></span>
            </div>
        </div>
    </div>

    {{-- Category tabs --}}
    <div class="td-card" style="border-radius:0;border-top:1px solid #f1f5f9">
        <template x-if="categories.length === 0">
            <p style="font-size:0.85rem;color:#94a3b8;">Giải đấu chưa có nội dung thi đấu.</p>
        </template>
        <div class="match-category-tabs">
            <template x-for="cat in categories" :key="cat.category_id">
                <button class="match-category-tab"
                        :class="{ active: activeCategoryId === cat.category_id }"
                        @click="selectCategory(cat.category_id)"
                        x-text="cat.category_name"></button>
            </template>
        </div>
    </div>

    {{-- Stats bar + filter --}}
    <template x-if="activeCategoryId">
        <div>
            <div class="td-card" style="border-radius:0;border-top:1px solid #f1f5f9;">

                {{-- Stats summary --}}
                <div class="match-stats-bar" x-show="stats.total > 0">
                    <span class="stat-item">
                        <span class="stat-dot completed"></span>
                        <span x-text="stats.completed + ' hoàn thành'"></span>
                    </span>
                    <span class="stat-item">
                        <span class="stat-dot progress"></span>
                        <span x-text="stats.in_progress + ' đang đấu'"></span>
                    </span>
                    <span class="stat-item">
                        <span class="stat-dot scheduled"></span>
                        <span x-text="stats.scheduled + ' chờ'"></span>
                    </span>
                    <div class="match-progress-bar" x-show="stats.total > 0">
                        <div class="match-progress-fill" :style="`width:${progressPct}%`"></div>
                    </div>
                    <span style="font-size:0.78rem;font-weight:700;" x-text="progressPct + '%'"></span>
                </div>

                {{-- Filter tabs --}}
                <div class="match-filter-tabs">
                    <template x-for="f in ['all','scheduled','in_progress','completed']" :key="f">
                        <button class="match-filter-tab"
                                :class="{ active: activeFilter === f }"
                                @click="setFilter(f)"
                                x-text="{ all:'Tất cả', scheduled:'Chưa đấu', in_progress:'Đang đấu', completed:'Hoàn thành' }[f]">
                        </button>
                    </template>
                </div>

                {{-- Generate matches button --}}
                <template x-if="!hasMatches">
                    @include('home-yard.tournaments.partials._matches-empty-generate')
                </template>
            </div>

            {{-- Match list --}}
            <template x-if="hasMatches">
                <div class="td-card" style="border-radius:0 0 10px 10px;border-top:1px solid #f1f5f9;">
                    <template x-for="group in filteredGroups()" :key="group.group_id">
                        <div style="margin-bottom:20px;">
                            <div class="match-group-header" x-text="group.group_name"></div>

                            <template x-if="group.matches.length === 0">
                                <p style="font-size:0.82rem;color:#94a3b8;padding:8px 0;">
                                    Không có trận đấu nào.
                                </p>
                            </template>

                            <template x-for="match in group.matches" :key="match.id">
                                @include('home-yard.tournaments.partials._matches-row')
                            </template>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </template>

</div>
