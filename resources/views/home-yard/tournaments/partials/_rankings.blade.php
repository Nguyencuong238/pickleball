{{-- Rankings & Standings Partial --}}
@php
    $categoryRankingsUrl = route('tournament-manage.rankings.category', [$tournament, '__CATEGORY__']);
    $rankOverrideUrl     = route('tournament-manage.rankings.rankOverrides', [$tournament, '__GROUP__']);
    $tournamentStatus    = $tournament->tournament_stage ?? 'registration';
    $defaultCategoryId   = $categories->first()?->id ?? 'null';
@endphp

<div x-data="rankingsManager({
    tournamentId:        {{ $tournament->id }},
    categoryRankingsUrl: '{{ $categoryRankingsUrl }}',
    rankOverrideUrl:     '{{ $rankOverrideUrl }}',
    tournamentStatus:    '{{ $tournamentStatus }}',
    defaultCategoryId:   {{ $defaultCategoryId }},
})" x-init="init()" @destroy.window="destroy()">

    {{-- Page header --}}
    <div class="td-card" style="margin-bottom:0;border-radius:10px 10px 0 0;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div>
                <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;margin:0;">Xếp hạng</h2>
                <p style="font-size:0.82rem;color:#64748b;margin:4px 0 0;">Bảng xếp hạng theo nội dung và nhóm thi đấu</p>
            </div>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <div class="rk-view-toggle">
                    <button class="rk-view-btn" :class="{ active: activeView === 'by_group' }"
                            @click="activeView = 'by_group'">Theo bang</button>
                    <button class="rk-view-btn" :class="{ active: activeView === 'overall' }"
                            @click="activeView = 'overall'">Tong hop</button>
                </div>
                <span class="rk-last-updated" x-show="lastUpdatedAt" x-text="lastUpdatedLabel"></span>
            </div>
        </div>
    </div>

    {{-- Category tabs --}}
    <div class="td-card" style="border-radius:0;border-top:1px solid #f1f5f9">
        @if($categories->isEmpty())
            <p style="font-size:0.85rem;color:#94a3b8;">Giải đấu chưa có nội dung thi đấu.</p>
        @else
            <div class="rk-category-tabs">
                @foreach($categories as $cat)
                    <button class="rk-category-tab"
                            :class="{ active: activeCategoryId === {{ $cat->id }} }"
                            @click="selectCategory({{ $cat->id }})">{{ $cat->category_name }}</button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Loading --}}
    <template x-if="loading">
        <div class="td-card" style="border-radius:0;border-top:1px solid #f1f5f9;text-align:center;padding:32px;">
            <span style="font-size:0.85rem;color:#94a3b8;">Đang tải dữ liệu...</span>
        </div>
    </template>

    {{-- Empty --}}
    <template x-if="!loading && groups.length === 0">
        <div class="td-card" style="border-radius:0 0 10px 10px;border-top:1px solid #f1f5f9;">
            <div class="rk-empty">Chưa có dữ liệu xếp hạng cho nội dung này.</div>
        </div>
    </template>

    {{-- By-group view --}}
    <template x-if="!loading && groups.length > 0 && activeView === 'by_group'">
        <div class="td-card" style="border-radius:0 0 10px 10px;border-top:1px solid #f1f5f9;">
            <template x-for="group in groups" :key="group.group_id">
                <div>
                    @include('home-yard.tournaments.partials._rankings-group-table')
                </div>
            </template>

            {{-- Legend --}}
            <div class="rk-legend">
                <div class="rk-legend-item">
                    <div class="rk-legend-advancing"></div>
                    <span>VĐV đi tiếp vòng sau</span>
                </div>
                <div class="rk-legend-item">
                    <span style="border-bottom:2px dashed var(--primary-color);width:20px;display:inline-block;"></span>
                    <span>Ranh giới đi tiếp</span>
                </div>
                <div class="rk-legend-item">
                    <span>P=trận, W=thắng, L=thua, SW=set thắng, SL=set thua, SD=hiệu số set</span>
                </div>
            </div>
        </div>
    </template>

    {{-- Overall view --}}
    <template x-if="!loading && groups.length > 0 && activeView === 'overall'">
        <div class="td-card" style="border-radius:0 0 10px 10px;border-top:1px solid #f1f5f9;">
            <div class="rk-table-wrapper">
                <table class="rk-standings-table">
                    <thead>
                        <tr>
                            <th class="num">#</th>
                            <th>VDV</th>
                            <th class="num">P</th>
                            <th class="num">W</th>
                            <th class="num">L</th>
                            <th class="num">SW</th>
                            <th class="num">SL</th>
                            <th class="num">SD</th>
                            <th class="num">Điểm</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in overallStandings" :key="row.athlete_id">
                            <tr :class="{ 'advancing': row.is_advanced }">
                                <td class="num">
                                    <span class="rk-rank-num" :class="getRankClass(row.rank)" x-text="row.rank"></span>
                                </td>
                                <td><span class="rk-athlete-name" x-text="row.athlete_name"></span></td>
                                <td class="num" x-text="row.played"></td>
                                <td class="num" x-text="row.won"></td>
                                <td class="num" x-text="row.lost"></td>
                                <td class="num" x-text="row.sets_won"></td>
                                <td class="num" x-text="row.sets_lost"></td>
                                <td class="num" x-text="row.set_diff"></td>
                                <td class="num"><span class="rk-pts" x-text="row.points"></span></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </template>

</div>
