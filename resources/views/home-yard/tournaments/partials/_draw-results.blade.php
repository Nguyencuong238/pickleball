{{-- Draw Results Sub-partial: group cards grid --}}
<div class="td-card" style="border-radius:0;border-top:1px solid #f1f5f9;">

    <div class="td-card-title" style="margin-bottom:14px;">
        Kết quả bốc thăm
        <span style="font-size:.78rem;font-weight:500;color:#64748b;" x-text="'(' + groups.length + ' bảng)'"></span>
    </div>

    <template x-if="groups.length === 0">
        <div class="draw-empty-state">
            <p>Chưa có kết quả bốc thăm.</p>
        </div>
    </template>

    <template x-if="groups.length > 0">
        <div class="draw-groups-grid">
            <template x-for="group in groups" :key="group.group_id">
                <div class="draw-group-card">
                    <div class="draw-group-header">
                        <span x-text="group.group_name"></span>
                        <span class="draw-group-count"
                              x-text="group.athletes.length + ' VĐV'"></span>
                    </div>
                    <ul class="draw-group-athletes">
                        <template x-if="group.athletes.length === 0">
                            <li style="color:#94a3b8;font-size:.8rem;padding:6px 8px;">Chưa có VĐV</li>
                        </template>
                        <template x-for="(athlete, idx) in group.athletes" :key="athlete.id">
                            <li class="draw-group-athlete">
                                <span class="draw-order-num" x-text="athlete.draw_order ?? idx + 1"></span>
                                <span style="flex:1;">
                                    <span x-text="athlete.name"></span>
                                    <template x-if="isDoubles && athlete.partner_name">
                                        <span style="color:#64748b;font-size:.78rem;"
                                              x-text="' / ' + athlete.partner_name"></span>
                                    </template>
                                </span>
                                <template x-if="athlete.seed_number">
                                    <span style="font-size:.72rem;color:#94a3b8;"
                                          x-text="'#' + athlete.seed_number"></span>
                                </template>
                            </li>
                        </template>
                    </ul>
                </div>
            </template>
        </div>
    </template>

</div>
