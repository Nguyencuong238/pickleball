{{-- Single match row: collapsed summary + expanded score form --}}
<div class="match-row" :class="{ expanded: expandedMatchId === match.id }">

    {{-- Collapsed head (always visible) --}}
    <div class="match-row-head" @click="toggleExpand(match)">
        <span class="match-num" x-text="'#' + match.match_number"></span>

        <div class="match-athletes">
            <span class="athlete-name"
                  :class="{ winner: match.winner_id && match.winner_id === match.athlete1_id }"
                  x-text="match.athlete1_name"></span>
            <span class="vs">vs</span>
            <span class="athlete-name"
                  :class="{ winner: match.winner_id && match.winner_id === match.athlete2_id }"
                  x-text="match.athlete2_name"></span>
        </div>

        <span class="match-score-display"
              x-show="match.final_score"
              x-text="match.final_score"></span>

        <span class="match-status" :class="match.status" x-text="statusLabel(match.status)"></span>

        <span x-show="match.match_date"
              x-text="(match.match_date || '') + (match.match_time ? ' ' + match.match_time : '')"
              style="font-size:.75rem;color:#64748b;margin-left:6px;"></span>

        <span class="match-chevron" :class="{ open: expandedMatchId === match.id }">&#9660;</span>
    </div>

    {{-- Expanded score entry form --}}
    <div x-show="expandedMatchId === match.id" x-cloak>
            {{-- Schedule form --}}
            <div style="padding:10px 14px;border-bottom:1px solid #f1f5f9;">
                <div style="font-size:.78rem;font-weight:700;color:#475569;margin-bottom:8px;">Lịch thi đấu</div>
                <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                    <div>
                        <label style="display:block;font-size:.75rem;color:#64748b;margin-bottom:3px;">Ngày</label>
                        <input type="date" x-model="scheduleForms[match.id].date"
                               style="padding:5px 8px;border:1px solid #cbd5e1;border-radius:6px;font-size:.82rem;">
                    </div>
                    <div>
                        <label style="display:block;font-size:.75rem;color:#64748b;margin-bottom:3px;">Giờ</label>
                        <input type="time" x-model="scheduleForms[match.id].time"
                               style="padding:5px 8px;border:1px solid #cbd5e1;border-radius:6px;font-size:.82rem;">
                    </div>
                    <button class="td-btn td-btn-primary"
                            style="padding:5px 14px;font-size:.82rem;"
                            :disabled="loadingSchedule[match.id] ? true : false"
                            @click.stop="saveSchedule(match.id)">
                        <span x-show="loadingSchedule[match.id]" class="match-spinner"></span>
                        <span x-text="loadingSchedule[match.id] ? 'Đang lưu...' : 'Lưu lịch'"></span>
                    </button>
                </div>
            </div>

            <div class="match-score-form">
                <div style="font-size:0.8rem;font-weight:700;color:#475569;margin-bottom:10px;">
                    Nhập tỉ số (<span x-text="'Best of ' + match.best_of"></span>)
                </div>

                <div class="score-sets-grid">
                    <template x-for="(set, idx) in scoreFormFor(match.id)" :key="idx">
                        <div class="score-set-item">
                            <span class="score-set-label" x-text="'Set ' + (idx + 1)"></span>
                            <input
                                type="number" min="0" max="30"
                                class="score-input"
                                data-side="s1"
                                x-model="scoreForms[match.id][idx].s1"
                                placeholder="0"
                            >
                            <span class="score-sep">-</span>
                            <input
                                type="number" min="0" max="30"
                                class="score-input"
                                data-side="s2"
                                x-model="scoreForms[match.id][idx].s2"
                                placeholder="0"
                            >
                        </div>
                    </template>
                </div>

                <div class="score-form-actions">
                    <button class="td-btn td-btn-primary"
                            :disabled="loadingScore[match.id] ? true : false"
                            @click.stop="submitScore(match.id)">
                        <span x-show="loadingScore[match.id]" class="match-spinner"></span>
                        <span x-text="loadingScore[match.id] ? 'Đang lưu...' : 'Lưu tỉ số'"></span>
                    </button>
                    <button class="td-btn td-btn-ghost" @click.stop="expandedMatchId = null">
                        Hủy
                    </button>
                </div>
            </div>

            {{-- Delete area --}}
            <div class="match-delete-area">
                <template x-if="confirmDeleteId === match.id">
                    <div style="display:flex;gap:8px;align-items:center;font-size:0.82rem;color:#991b1b;">
                        <span>Xác nhận xóa?</span>
                        <button class="td-btn td-btn-danger"
                                style="padding:4px 10px;font-size:0.78rem;"
                                :disabled="loadingDelete[match.id] ? true : false"
                                @click.stop="confirmDelete(match.id)">
                            <span x-show="loadingDelete[match.id]" class="match-spinner"></span>
                            Xóa
                        </button>
                        <button class="td-btn td-btn-ghost"
                                style="padding:4px 10px;font-size:0.78rem;"
                                @click.stop="cancelDelete()">
                            Không
                        </button>
                    </div>
                </template>
                <template x-if="confirmDeleteId !== match.id">
                    <button class="td-btn td-btn-ghost"
                            style="padding:4px 10px;font-size:0.78rem;color:#ef4444;"
                            @click.stop="askDelete(match.id)">
                        Xóa trận đấu
                    </button>
                </template>
            </div>
    </div>

</div>
