{{-- Empty state + Generate Matches button --}}
<div class="matches-empty-state">
    <h3>Chưa có trận đấu nào</h3>
    <p>Chọn thể thức thi đấu và tạo lịch thi đấu vòng bảng.</p>

    <div style="margin-bottom:12px;">
        <label style="font-size:0.85rem;font-weight:600;color:#334155;display:block;margin-bottom:6px;">
            Thể thức
        </label>
        <div style="display:flex;gap:8px;justify-content:center;">
            <button type="button"
                    class="td-btn"
                    :class="bestOf === 1 ? 'td-btn-primary' : 'td-btn-outline'"
                    @click="bestOf = 1">Best of 1</button>
            <button type="button"
                    class="td-btn"
                    :class="bestOf === 3 ? 'td-btn-primary' : 'td-btn-outline'"
                    @click="bestOf = 3">Best of 3</button>
        </div>
    </div>

    <button class="td-btn td-btn-primary"
            :disabled="loadingGenerate"
            @click="generateMatches()">
        <span x-show="loadingGenerate" class="match-spinner"></span>
        <span x-text="loadingGenerate ? 'Đang tạo...' : 'Tạo trận đấu'"></span>
    </button>
</div>
