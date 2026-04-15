{{--
    Rankings group standings table partial.
    Required Alpine scope: group (object with group_id, group_name, advancing_count, standings[])
    Parent must be inside rankingsManager x-data scope.
--}}
<div class="rk-group-block">
    <div class="rk-group-title">
        <span x-text="group.group_name"></span>
        <span class="advancing-badge"
              x-show="group.advancing_count > 0"
              x-text="group.advancing_count + ' VĐV đi tiếp'"></span>
    </div>

    <template x-if="group.standings.length === 0">
        <div class="rk-empty">Chưa có dữ liệu.</div>
    </template>

    <template x-if="group.standings.length > 0">
        <div class="rk-table-wrapper">
            <table class="rk-standings-table">
                <thead>
                    <tr>
                        <th class="num"
                            :class="{ 'sorted-asc': isSorted(group.group_id,'rank') && getSortDir(group.group_id,'rank')==='asc', 'sorted-desc': isSorted(group.group_id,'rank') && getSortDir(group.group_id,'rank')==='desc' }"
                            @click="sortGroup(group.group_id,'rank')">#
                            <span class="sort-arrow" x-text="isSorted(group.group_id,'rank') ? getSortArrow(group.group_id,'rank') : '&#9660;'"></span>
                        </th>
                        <th :class="{ 'sorted-asc': isSorted(group.group_id,'athlete_name') && getSortDir(group.group_id,'athlete_name')==='asc', 'sorted-desc': isSorted(group.group_id,'athlete_name') && getSortDir(group.group_id,'athlete_name')==='desc' }"
                            @click="sortGroup(group.group_id,'athlete_name')">VDV
                            <span class="sort-arrow" x-text="isSorted(group.group_id,'athlete_name') ? getSortArrow(group.group_id,'athlete_name') : '&#9660;'"></span>
                        </th>
                        @foreach([['played','P'],['won','W'],['lost','L'],['sets_won','SW'],['sets_lost','SL'],['set_diff','SD'],['games_scored','GS'],['points','Điểm']] as [$field, $label])
                        <th class="num"
                            :class="{ 'sorted-asc': isSorted(group.group_id,'{{ $field }}') && getSortDir(group.group_id,'{{ $field }}')==='asc', 'sorted-desc': isSorted(group.group_id,'{{ $field }}') && getSortDir(group.group_id,'{{ $field }}')==='desc' }"
                            @click="sortGroup(group.group_id,'{{ $field }}')">{{ $label }}
                            <span class="sort-arrow" x-text="isSorted(group.group_id,'{{ $field }}') ? getSortArrow(group.group_id,'{{ $field }}') : '&#9660;'"></span>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, idx) in group.standings" :key="row.athlete_id">
                        <tr :class="{
                                'advancing': row.is_advanced,
                                'advance-separator': isLastAdvancing(group, idx),
                                'tied-row': row.is_tied
                            }"
                            :style="row.is_tied ? 'background:#fff7ed;' : ''">
                            <td class="num">
                                <div class="rk-rank-cell" style="justify-content:center;">
                                    <span class="rk-rank-num" :class="getRankClass(row.rank)" x-text="row.rank"></span>
                                    <span class="rk-advance-dot" x-show="row.is_advanced"></span>
                                    <template x-if="row.is_tied">
                                        <input type="number" min="1"
                                               class="rank-override-input"
                                               :data-group-id="group.group_id"
                                               :data-athlete-id="row.athlete_id"
                                               :value="row.manual_rank_override ?? row.rank"
                                               style="width:46px;margin-left:6px;font-size:0.78rem;padding:2px 4px;border:1px solid #f97316;border-radius:4px;">
                                    </template>
                                </div>
                            </td>
                            <td><span class="rk-athlete-name" x-text="row.athlete_name"></span></td>
                            <td class="num" x-text="row.played"></td>
                            <td class="num" x-text="row.won"></td>
                            <td class="num" x-text="row.lost"></td>
                            <td class="num" x-text="row.sets_won"></td>
                            <td class="num" x-text="row.sets_lost"></td>
                            <td class="num" x-text="row.set_diff"></td>
                            <td class="num">
                                <span class="rk-games-scored" x-text="row.games_scored"></span><span class="rk-games-sep">-</span><span class="rk-games-conceded" x-text="row.games_conceded"></span>
                            </td>
                            <td class="num"><span class="rk-pts" x-text="row.points"></span></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div x-show="groupHasTied(group)"
                 style="margin-top:8px;display:flex;align-items:center;justify-content:flex-end;gap:8px;">
                <span style="font-size:0.78rem;color:#b45309;">Có VĐV còn tied — nhập thứ tự bốc thăm:</span>
                <button type="button"
                        @click="saveOverrides(group.group_id)"
                        :disabled="overrideSaving"
                        style="padding:5px 12px;font-size:0.78rem;border-radius:6px;background:#f97316;color:#fff;border:none;cursor:pointer;">
                    <span x-show="!overrideSaving">Lưu thứ tự bốc thăm</span>
                    <span x-show="overrideSaving">Đang lưu...</span>
                </button>
            </div>
            <div x-show="overrideError" x-text="overrideError"
                 style="margin-top:6px;font-size:0.78rem;color:#dc2626;text-align:right;"></div>
        </div>
    </template>
</div>
