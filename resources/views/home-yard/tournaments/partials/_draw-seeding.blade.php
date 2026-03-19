{{-- Draw Seeding Sub-partial: athlete list with drag-to-reorder --}}
<div class="td-card" style="border-radius:0;border-top:1px solid #f1f5f9;">

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:14px;">
        <div class="td-card-title" style="margin:0;">
            Thứ tự hạt giống
            <span style="font-size:.78rem;font-weight:500;color:#64748b;" x-text="'(' + athletes.length + ' VĐV)'"></span>
        </div>
        <button class="td-btn td-btn-ghost" style="font-size:.82rem;" @click="shuffleAthletes()">
            Xáo trộn ngẫu nhiên
        </button>
    </div>

    {{-- Empty state when no athletes loaded yet (category just selected) --}}
    <template x-if="athletes.length === 0">
        <div class="draw-empty-state">
            <p>
                <template x-if="activeCategory && activeCategory.approved_count === 0">
                    <span>Chưa có vận động viên được duyệt trong nội dung này.</span>
                </template>
                <template x-if="activeCategory && activeCategory.approved_count > 0">
                    <span>
                        Có <strong x-text="activeCategory.approved_count"></strong> VĐV được duyệt.
                        Thứ tự sẽ được xác định khi thực hiện bốc thăm.
                    </span>
                </template>
            </p>
        </div>
    </template>

    {{-- Athlete list (populated after loadResults or after draw) --}}
    <template x-if="athletes.length > 0">
        <ul class="draw-athlete-list" id="draw-seeding-list">
            <template x-for="(athlete, idx) in athletes" :key="athlete.id ?? athlete.pair_id">
                <li class="draw-athlete-item">
                    <span class="drag-handle" title="Kéo để sắp xếp">&#8942;&#8942;</span>
                    <span class="draw-seed-number" x-text="idx + 1"></span>
                    <span class="draw-athlete-name" x-text="displayName(athlete)"></span>
                    <template x-if="isDoubles && athlete.athlete2_name">
                        <span class="draw-athlete-partner" x-text="'/ ' + athlete.athlete2_name"></span>
                    </template>
                </li>
            </template>
        </ul>
    </template>

    {{-- Info about groups --}}
    <div style="margin-top:12px;padding:10px 12px;background:#f8fafc;border-radius:8px;font-size:.82rem;color:#64748b;">
        <template x-if="activeCategory && activeCategory.group_count > 0">
            <span>
                Sẽ chia vào <strong x-text="activeCategory.group_count"></strong> bảng.
            </span>
        </template>
        <template x-if="activeCategory && activeCategory.group_count === 0">
            <span style="color:#ef4444;">
                Chưa có bảng đấu. Vui lòng tạo bảng trước khi bốc thăm.
            </span>
        </template>
    </div>

</div>
