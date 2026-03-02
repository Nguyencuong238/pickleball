{{-- Competition panel: teams, matches, standings, score entry --}}
{{-- Variables: $club, $activity, $isManagement --}}
@php
    $config = $activity->competition_config ?? [];
    $format = $config['format'] ?? 'round_robin';
    $formatLabels = [
        'round_robin' => 'Vòng tròn',
        'pool_play' => 'Bảng đấu + Loại trực tiếp',
        'single_elimination' => 'Loại trực tiếp',
    ];
@endphp

<div class="competition-panel" id="competition-panel">
    <h3 class="panel-title">🏆 Giải đấu - {{ $formatLabels[$format] ?? $format }}</h3>

    {{-- Team Management (management only) --}}
    @if($isManagement)
    <div class="comp-section">
        <h4 class="comp-heading">Quản lý đội</h4>
        <div id="team-list"></div>
        <div style="margin-top: 12px; display: flex; gap: 8px;">
            <input type="text" id="new-team-name" placeholder="Tên đội mới..."
                style="flex:1; padding:10px; border:1px solid #d1d5db; border-radius:8px; font-size:0.9rem;">
            <button class="btn-comp btn-add-team" onclick="addTeam()">
                ➕ Thêm đội
            </button>
        </div>
        <div style="margin-top: 16px; display: flex; gap: 8px; align-items: center;">
            <select id="schedule-format" style="padding:10px; border:1px solid #d1d5db; border-radius:8px; font-size:0.9rem;">
                <option value="round_robin" {{ $format === 'round_robin' ? 'selected' : '' }}>Vòng tròn</option>
                <option value="pool_play" {{ $format === 'pool_play' ? 'selected' : '' }}>Bảng đấu + Playoff</option>
                <option value="single_elimination" {{ $format === 'single_elimination' ? 'selected' : '' }}>Loại trực tiếp</option>
            </select>
            <button class="btn-comp btn-generate" onclick="generateSchedule()">
                ▶️ Tạo lịch thi đấu
            </button>
        </div>
    </div>
    @endif

    <div class="comp-section">
        <h4 class="comp-heading">Lịch thi đấu</h4>
        <div id="matches-list"><p class="comp-empty">Đang tải...</p></div>
    </div>

    <div class="comp-section">
        <h4 class="comp-heading">Bảng xếp hạng</h4>
        <div id="standings-table"><p class="comp-empty">Đang tải...</p></div>
    </div>
</div>

@include('clubs.activities.partials._competition-styles')
@include('clubs.activities.partials._competition-scripts')
