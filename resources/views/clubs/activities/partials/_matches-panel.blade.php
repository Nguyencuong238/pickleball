{{-- Matches panel: rounds, matches, standings for non-competition activities --}}
{{-- Variables: $club, $activity, $isManagement --}}

<div class="matches-panel" id="matches-panel"
     data-club-id="{{ $club->id }}"
     data-activity-id="{{ $activity->id }}">

    {{-- Management actions --}}
    @if($isManagement)
    <div class="matches-actions">
        <button class="btn-matches btn-matches-primary" onclick="openGenerateModal()">
            🏆 Tạo trận đấu
        </button>
        <button class="btn-matches btn-matches-secondary" onclick="openCustomModal()">
            ➕ Thêm trận tuỳ chỉnh
        </button>
    </div>
    @endif

    {{-- Rounds container (JS populates) --}}
    <div id="matches-rounds-container" style="display:none"></div>

    {{-- Standings section --}}
    <div class="matches-section" id="standings-section" style="display:none">
        <h4 class="matches-heading">Bảng xếp hạng cá nhân</h4>
        <div id="matches-standings-table"></div>
    </div>

    {{-- Empty state --}}
    <div id="matches-empty-state">
        @if($isManagement)
            <p class="matches-empty">Chưa có trận đấu nào. Nhấn "Tạo trận đấu" để bắt đầu.</p>
        @else
            <p class="matches-empty">Chưa có trận đấu nào. Liên hệ ban tổ chức.</p>
        @endif
    </div>

    {{-- Confirmed participants data for custom match modal --}}
    @if($isManagement)
    <div id="confirmed-participants-data"
         data-participants='@json($activity->confirmedParticipants->map(fn($p) => ["id" => $p->user_id, "name" => $p->user->name ?? "N/A"]))'
         style="display:none"></div>
    @include('clubs.activities.partials._matches-generate-modal')
    @include('clubs.activities.partials._matches-custom-modal')
    @endif
</div>

@include('clubs.activities.partials._matches-styles')
@include('clubs.activities.partials._matches-scripts')
