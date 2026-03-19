{{-- Manual Draw Sub-partial: drag athletes into group slots --}}
<div class="td-card" style="border-radius:0;border-top:1px solid #f1f5f9;">

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
        <div class="td-card-title" style="margin:0;">Bốc thăm thủ công</div>
        <div style="display:flex;gap:8px;">
            <button class="td-btn td-btn-ghost" @click="view = 'seeding'">Hủy</button>
            <button class="td-btn td-btn-primary" :disabled="loading" @click="saveManualDraw()">
                <span x-show="loading" class="draw-spinner"></span>
                <span x-text="loading ? 'Đang lưu...' : 'Lưu kết quả'"></span>
            </button>
        </div>
    </div>

    <div class="draw-manual-layout">

        {{-- Unassigned panel --}}
        <div class="draw-unassigned-panel">
            <div class="draw-panel-title">
                Chưa phân bảng
                <span style="font-weight:500;color:#475569;" x-text="'(' + unassigned.length + ')'"></span>
            </div>
            <ul class="draw-unassigned-list" id="manual-unassigned">
                <template x-if="unassigned.length === 0">
                    <li class="draw-drop-hint">Tất cả đã được phân bảng</li>
                </template>
                <template x-for="athlete in unassigned" :key="athlete.id ?? athlete.pair_id">
                    <li class="draw-athlete-item"
                        :data-athlete-id="athlete.id ?? athlete.athlete1_id">
                        <span class="drag-handle">&#8942;&#8942;</span>
                        <span class="draw-athlete-name" x-text="displayName(athlete)"></span>
                    </li>
                </template>
            </ul>
        </div>

        {{-- Group slots --}}
        <div>
            <div class="draw-panel-title" style="margin-bottom:10px;">Các bảng đấu</div>
            <div class="draw-groups-grid" style="margin-top:0;">
                <template x-for="group in manualGroupList()" :key="group.id">
                    <div class="draw-group-card">
                        <div class="draw-group-header">
                            <span x-text="group.name"></span>
                            <span class="draw-group-count"
                                  x-text="group.athletes.length + '/' + group.max"></span>
                        </div>
                        <ul class="draw-group-athletes"
                            :id="'manual-group-' + group.id">
                            <template x-if="group.athletes.length === 0">
                                <li class="draw-drop-hint">Kéo VĐV vào đây</li>
                            </template>
                            <template x-for="athlete in group.athletes"
                                      :key="athlete.id ?? athlete.pair_id">
                                <li class="draw-group-athlete"
                                    :data-athlete-id="athlete.id ?? athlete.athlete1_id">
                                    <span class="drag-handle" style="color:#94a3b8;cursor:grab;">&#8942;&#8942;</span>
                                    <span style="flex:1;font-size:.82rem;" x-text="displayName(athlete)"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>
            </div>
        </div>

    </div>

    {{-- Validation hint --}}
    <template x-if="unassigned.length > 0">
        <div class="td-alert td-alert-error" style="margin-top:14px;">
            Còn <strong x-text="unassigned.length"></strong> VĐV chưa được phân bảng.
            Vui lòng phân bảng hết trước khi lưu.
        </div>
    </template>

</div>
