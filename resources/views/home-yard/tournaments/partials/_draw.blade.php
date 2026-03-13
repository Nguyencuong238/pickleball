{{-- Draw & Seeding Partial --}}
@php
    $categoriesJson = json_encode($categories);
@endphp

<div x-data="drawManager({
    tournamentId:  {{ $tournament->id }},
    categories:    {{ $categoriesJson }},
    executeUrl:    '{{ route('tournament-manage.draw.execute', $tournament) }}',
    resultsUrl:    '{{ route('tournament-manage.draw.results', $tournament) }}',
    resetUrl:      '{{ route('tournament-manage.draw.reset', $tournament) }}',
    manualUrl:     '{{ route('tournament-manage.draw.manual', $tournament) }}',
    manualSaveUrl: '{{ route('tournament-manage.draw.manualSave', $tournament) }}',
    groupIndexUrl: '{{ route('tournament-manage.groups.index', $tournament) }}',
    groupSetupUrl: '{{ route('tournament-manage.groups.setup', $tournament) }}',
    csrf:          document.querySelector('meta[name=csrf-token]').content,
})">

    {{-- Page header --}}
    <div class="td-card" style="margin-bottom:0;border-radius:10px 10px 0 0;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div>
                <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;margin:0;">Bốc thăm / Chia bảng</h2>
                <p style="font-size:0.82rem;color:#64748b;margin:4px 0 0;">
                    Chọn nội dung và phương thức bốc thăm
                </p>
            </div>
            <span class="draw-status-badge" :class="isDrawn ? 'drawn' : 'not-drawn'"
                  x-text="isDrawn ? 'Đã bốc thăm' : 'Chưa bốc thăm'"></span>
        </div>
    </div>

    {{-- Category tabs --}}
    <div class="td-card" style="border-radius:0;border-top:1px solid #f1f5f9">
        @if(count($categories) === 0)
            <div class="draw-empty-state">
                <p>Giải đấu chưa có nội dung thi đấu.</p>
            </div>
        @else
            <div class="draw-category-tabs">
                <template x-for="cat in categories" :key="cat.id">
                    <button class="draw-category-tab"
                            :class="{ active: activeCategoryId === cat.id }"
                            @click="selectCategory(cat.id)">
                        <span x-text="cat.category_name"></span>
                        <span x-show="cat.is_drawn" style="margin-left:5px;color:inherit;opacity:.7;">(xong)</span>
                    </button>
                </template>
            </div>
        @endif
    </div>

    {{-- Main panel (only when a category is selected) --}}
    <template x-if="activeCategoryId">
        <div>
            {{-- Group setup panel (shown when not yet drawn) --}}
            <template x-if="!isDrawn">
                <div class="td-card" style="border-radius:0;border-top:1px solid #f1f5f9;">
                    <div style="font-size:.82rem;font-weight:700;color:#64748b;margin-bottom:10px;">
                        Cấu hình bảng đấu
                    </div>
                    <div style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;">
                        <div>
                            <label style="display:block;font-size:.78rem;color:#64748b;margin-bottom:4px;">Số bảng</label>
                            <input type="number" min="1" max="16"
                                   x-model.number="groupCount"
                                   style="width:90px;padding:6px 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;">
                        </div>
                        <div>
                            <label style="display:block;font-size:.78rem;color:#64748b;margin-bottom:4px;">Max VĐV/bảng</label>
                            <input type="number" min="2" max="100"
                                   x-model.number="groupMaxParticipants"
                                   style="width:110px;padding:6px 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;">
                        </div>
                        <div>
                            <label style="display:block;font-size:.78rem;color:#64748b;margin-bottom:4px;">Đi tiếp/bảng</label>
                            <input type="number" min="1" max="8"
                                   x-model.number="advancingCount"
                                   style="width:90px;padding:6px 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:.85rem;">
                        </div>
                        <button class="td-btn td-btn-primary"
                                :disabled="groupSetupLoading"
                                @click="setupGroups()">
                            <span x-show="groupSetupLoading" class="draw-spinner"></span>
                            <span x-text="groupSetupLoading ? 'Đang tạo...' : 'Cấu hình bảng'"></span>
                        </button>
                    </div>
                    {{-- Current groups list --}}
                    <template x-if="configuredGroups.length > 0">
                        <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:8px;">
                            <template x-for="g in configuredGroups" :key="g.id">
                                <div style="padding:4px 12px;background:#f1f5f9;border-radius:20px;font-size:.8rem;color:#334155;">
                                    <span x-text="g.group_name"></span>
                                    <span style="color:#94a3b8;margin-left:4px;" x-text="'(max ' + g.max_participants + ')'"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="configuredGroups.length === 0">
                        <div style="margin-top:10px;font-size:.8rem;color:#f59e0b;">
                            Chưa có bảng nào. Cấu hình trước khi bốc thăm.
                        </div>
                    </template>
                </div>
            </template>

            {{-- Controls bar --}}
            <div class="td-card" style="border-radius:0;border-top:1px solid #f1f5f9;">
                <template x-if="!isDrawn || view === 'seeding'">
                    <div>
                        <div style="font-size:.82rem;font-weight:700;color:#64748b;margin-bottom:8px;">
                            Phương thức bốc thăm
                        </div>
                        <div class="draw-method-group">
                            <label class="draw-method-option" :class="{ selected: drawMethod === 'random' }">
                                <input type="radio" name="draw_method" value="random" x-model="drawMethod">
                                Xáo trộn ngẫu nhiên
                            </label>
                            <label class="draw-method-option" :class="{ selected: drawMethod === 'seeded' }">
                                <input type="radio" name="draw_method" value="seeded" x-model="drawMethod">
                                Theo hạt giống (rắn)
                            </label>
                            <label class="draw-method-option" :class="{ selected: drawMethod === 'manual' }">
                                <input type="radio" name="draw_method" value="manual" x-model="drawMethod">
                                Thủ công
                            </label>
                        </div>
                        <div class="draw-controls">
                            <button class="td-btn td-btn-primary"
                                    :disabled="loading"
                                    @click="drawMethod === 'manual' ? openManualDraw() : executeDraw()">
                                <span x-show="loading" class="draw-spinner"></span>
                                <span x-text="loading ? 'Đang xử lý...' : 'Thực hiện bốc thăm'"></span>
                            </button>
                        </div>
                    </div>
                </template>
                <template x-if="isDrawn && view === 'results'">
                    <div class="draw-controls">
                        <button class="td-btn td-btn-ghost" @click="showSeedingFromResults()">
                            Xem lại thứ tự hạt giống
                        </button>
                        <button class="td-btn td-btn-danger" :disabled="loading" @click="resetDraw()">
                            <span x-show="loading" class="draw-spinner" style="border-top-color:#991b1b;"></span>
                            Reset bốc thăm
                        </button>
                    </div>
                </template>
            </div>

            {{-- Seeding view --}}
            <template x-if="view === 'seeding'">
                @include('home-yard.tournaments.partials._draw-seeding')
            </template>

            {{-- Results view --}}
            <template x-if="view === 'results'">
                @include('home-yard.tournaments.partials._draw-results')
            </template>

            {{-- Manual draw view --}}
            <template x-if="view === 'manual'">
                @include('home-yard.tournaments.partials._draw-manual')
            </template>
        </div>
    </template>

</div>
