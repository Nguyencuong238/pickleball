{{-- Bracket Match Card --}}
<div class="bracket-match"
     :class="{
         'bracket-match--bye':  match.status === 'bye',
         'bracket-match--live': match.status === 'in_progress'
     }">

    {{-- LIVE badge --}}
    <template x-if="match.status === 'in_progress'">
        <span class="bracket-match-status">LIVE</span>
    </template>

    {{-- Athlete 1 --}}
    <div class="bracket-slot"
         :class="{
             'bracket-slot--winner':   match.winner_id && match.athlete1 && match.winner_id === match.athlete1.id,
             'bracket-slot--swappable': editMode && (match.status === 'scheduled' || (match.status === 'completed' && (!match.athlete1 || !match.athlete2))),
             'bracket-slot--selected':  swapState.active && swapState.matchId1 === match.id && swapState.slot1 === 'athlete1'
         }"
         @click="selectSlot(match.id, 'athlete1', match.athlete1 ? match.athlete1.name : '', match.athlete1 ? match.athlete1.id : null)">
        <span x-text="match.athlete1
            ? (match.athlete1.partner_name ? match.athlete1.name + ' / ' + match.athlete1.partner_name : match.athlete1.name)
            : 'Chưa xác định'">
        </span>
        <span x-show="match.athlete1_score !== null && match.athlete1_score !== undefined"
              style="font-weight:700;min-width:18px;text-align:right;"
              x-text="match.athlete1_score"></span>
    </div>

    {{-- Athlete 2 --}}
    <div class="bracket-slot"
         :class="{
             'bracket-slot--winner':   match.winner_id && match.athlete2 && match.winner_id === match.athlete2.id,
             'bracket-slot--swappable': editMode && (match.status === 'scheduled' || (match.status === 'completed' && (!match.athlete1 || !match.athlete2))),
             'bracket-slot--selected':  swapState.active && swapState.matchId1 === match.id && swapState.slot1 === 'athlete2'
         }"
         @click="selectSlot(match.id, 'athlete2', match.athlete2 ? match.athlete2.name : '', match.athlete2 ? match.athlete2.id : null)">
        <span x-text="match.athlete2
            ? (match.athlete2.partner_name ? match.athlete2.name + ' / ' + match.athlete2.partner_name : match.athlete2.name)
            : 'Chưa xác định'">
        </span>
        <span x-show="match.athlete2_score !== null && match.athlete2_score !== undefined"
              style="font-weight:700;min-width:18px;text-align:right;"
              x-text="match.athlete2_score"></span>
    </div>

    {{-- Nút nhập tỉ số --}}
    <template x-if="(match.status === 'scheduled' || match.status === 'completed' || match.status === 'in_progress') && match.athlete1 && match.athlete2">
        <div style="padding:4px 8px;text-align:center;border-top:1px solid #f1f5f9;">
            <button class="bracket-score-btn"
                    @click="openScore(match.id)"
                    style="font-size:0.75rem;color:#0369a1;cursor:pointer;background:none;border:none;">
                <span x-text="match.status === 'completed' ? 'S\u1EEDa t\u1EC9 s\u1ED1' : 'Nh\u1EADp t\u1EC9 s\u1ED1'"></span>
            </button>
        </div>
    </template>

</div>
