{{-- Generate matches modal (management only) --}}
<div class="matches-modal-overlay" id="generate-modal-overlay">
    <div class="matches-modal">
        <h3>🏆 Tạo trận đấu</h3>
        <p style="font-size:0.85rem; color:#6b7280; margin-bottom:16px;">
            {{ $activity->confirmed_participants_count ?? 0 }} người xác nhận tham gia
        </p>

        <label for="match-format">Thể thức</label>
        <select id="match-format">
            <option value="rotating_doubles">Đánh đôi luân phiên</option>
            <option value="fixed_doubles">Đánh đôi cố định</option>
            <option value="singles_rr">Đánh đơn vòng tròn</option>
        </select>

        <label for="court-count">Số sân</label>
        <input type="number" id="court-count" min="1" max="10" value="2">

        <div class="matches-modal-actions">
            <button class="btn-modal-cancel" onclick="closeGenerateModal()">Huỷ</button>
            <button class="btn-matches btn-matches-primary" onclick="generateMatches()" id="btn-generate">
                Tạo trận đấu
            </button>
        </div>
    </div>
</div>
