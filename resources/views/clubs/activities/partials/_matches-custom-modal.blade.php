{{-- Custom match modal (management only) --}}
<div class="matches-modal-overlay" id="custom-modal-overlay">
    <div class="matches-modal">
        <h3>➕ Thêm trận tuỳ chỉnh</h3>

        <label for="custom-match-type">Loại trận</label>
        <select id="custom-match-type" onchange="onMatchTypeChange()">
            <option value="doubles">Đánh đôi</option>
            <option value="singles">Đánh đơn</option>
        </select>

        <label>Chọn người chơi</label>
        <div class="player-grid" id="player-grid">
            <div>
                <label for="custom-player1">Team 1 - P1</label>
                <select id="custom-player1" class="player-select"></select>
            </div>
            <div>
                <label for="custom-player2">Team 1 - P2</label>
                <select id="custom-player2" class="player-select"></select>
            </div>
            <div>
                <label for="custom-player3">Team 2 - P1</label>
                <select id="custom-player3" class="player-select"></select>
            </div>
            <div>
                <label for="custom-player4">Team 2 - P2</label>
                <select id="custom-player4" class="player-select"></select>
            </div>
        </div>

        <label for="custom-court" style="margin-top:12px;">Sân (tuỳ chọn)</label>
        <input type="number" id="custom-court" min="1" max="20" placeholder="Số sân">

        <label for="custom-round">Vòng đấu</label>
        <select id="custom-round">
            <option value="">Vòng mới</option>
        </select>

        <div class="matches-modal-actions">
            <button class="btn-modal-cancel" onclick="closeCustomModal()">Huỷ</button>
            <button class="btn-matches btn-matches-primary" onclick="submitCustomMatch()" id="btn-custom-submit">
                Thêm trận đấu
            </button>
        </div>
    </div>
</div>
