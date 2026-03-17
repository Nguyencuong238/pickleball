{{-- Bracket Tree Partial --}}
@php
    $dataUrl     = route('tournament-manage.bracket.data',     $tournament);
    $generateUrl = route('tournament-manage.bracket.generate', $tournament);
    $categories  = $tournament->categories->map(fn($c) => [
        'category_id'   => $c->id,
        'category_name' => $c->category_name,
    ])->values()->toArray();
    $bracketData = $tournament->bracket_data ?? [];
@endphp

<div x-data="bracketManager({
    tournamentSlug: '{{ $tournament->slug }}',
    dataUrl:        '{{ $dataUrl }}',
    generateUrl:    '{{ $generateUrl }}',
    categories:     {{ Js::from($categories) }},
    bracketData:    {{ Js::from($bracketData) }},
    csrf:           document.querySelector('meta[name=csrf-token]').content,
})" x-init="init()" @keydown.escape.window="clearSwap()">

    {{-- Page header --}}
    <div class="td-card" style="margin-bottom:0;border-radius:10px 10px 0 0;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div>
                <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;margin:0;">Bracket</h2>
                <p style="font-size:0.82rem;color:#64748b;margin:4px 0 0;">
                    Nhánh loại trực tiếp theo nội dung
                </p>
            </div>
            <template x-if="hasBracket">
                <button class="td-btn td-btn-outline td-btn-sm"
                        @click="toggleEditMode()"
                        x-text="editMode ? 'Xong' : 'Chỉnh sửa bracket'"
                        style="white-space:nowrap;"></button>
            </template>
        </div>
    </div>

    {{-- Category tabs --}}
    <div class="td-card" style="border-radius:0;border-top:1px solid #f1f5f9;">
        <template x-if="categories.length === 0">
            <p style="font-size:0.85rem;color:#94a3b8;">Giải đấu chưa có nội dung thi đấu.</p>
        </template>
        <div class="match-category-tabs">
            <template x-for="cat in categories" :key="cat.category_id">
                <button class="match-category-tab"
                        :class="{ active: activeCategoryId === cat.category_id }"
                        @click="selectCategory(cat.category_id)"
                        x-text="cat.category_name"></button>
            </template>
        </div>
    </div>

    {{-- Bảng nhập tỉ số --}}
    <template x-if="scoreMatchId">
        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:50;display:flex;align-items:center;justify-content:center;"
             @keydown.escape.window="scoreMatchId = null">
            <div style="background:#fff;border-radius:10px;padding:24px;max-width:400px;width:90%;">
                <h3 style="font-size:1rem;font-weight:700;margin:0 0 16px;">Nhập tỉ số</h3>

                <template x-for="(set, idx) in scoreSets" :key="idx">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                        <span style="font-size:0.82rem;color:#64748b;min-width:40px;" x-text="'Set ' + (idx + 1)"></span>
                        <input type="number" min="0" x-model.number="set.s1"
                               style="width:60px;padding:6px;border:1px solid #e2e8f0;border-radius:4px;text-align:center;">
                        <span>-</span>
                        <input type="number" min="0" x-model.number="set.s2"
                               style="width:60px;padding:6px;border:1px solid #e2e8f0;border-radius:4px;text-align:center;">
                    </div>
                </template>

                <div style="display:flex;gap:8px;margin:8px 0 16px;">
                    <button class="td-btn td-btn-outline td-btn-sm" @click="addSet()" x-show="scoreSets.length < 5">+ Set</button>
                    <button class="td-btn td-btn-outline td-btn-sm" @click="removeSet()" x-show="scoreSets.length > 1">- Set</button>
                </div>

                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button class="td-btn" @click="scoreMatchId = null">Hủy</button>
                    <button class="td-btn td-btn-primary" @click="saveScore()" :disabled="scoreSaving">
                        <span x-show="!scoreSaving">Lưu tỉ số</span>
                        <span x-show="scoreSaving">Đang lưu...</span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal chỉnh sửa trận đấu --}}
    <template x-if="editMatchId">
        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:50;display:flex;align-items:center;justify-content:center;"
             @keydown.escape.window="editMatchId = null">
            <div style="background:#fff;border-radius:10px;padding:24px;max-width:480px;width:90%;">
                <h3 style="font-size:1rem;font-weight:700;margin:0 0 16px;">Chỉnh sửa trận đấu</h3>

                <template x-if="editLoading">
                    <p style="font-size:0.85rem;color:#64748b;">Đang tải...</p>
                </template>

                <template x-if="!editLoading">
                    <div>
                        <!-- VĐV 1 -->
                        <div style="margin-bottom:12px;">
                            <label style="font-size:0.82rem;color:#64748b;display:block;margin-bottom:4px;">VĐV 1</label>
                            <select x-model="editForm.athlete1_id"
                                    style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:0.85rem;">
                                <option value="">-- Chưa xác định --</option>
                                <template x-for="a in editEligibleAthletes" :key="a.id">
                                    <option :value="a.id" x-text="a.pair_name || a.name"
                                            :disabled="String(a.id) === String(editForm.athlete2_id)"></option>
                                </template>
                            </select>
                        </div>

                        <!-- VĐV 2 -->
                        <div style="margin-bottom:12px;">
                            <label style="font-size:0.82rem;color:#64748b;display:block;margin-bottom:4px;">VĐV 2</label>
                            <select x-model="editForm.athlete2_id"
                                    style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:0.85rem;">
                                <option value="">-- Chưa xác định --</option>
                                <template x-for="a in editEligibleAthletes" :key="a.id">
                                    <option :value="a.id" x-text="a.pair_name || a.name"
                                            :disabled="String(a.id) === String(editForm.athlete1_id)"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Giờ thi đấu -->
                        <div style="margin-bottom:12px;">
                            <label style="font-size:0.82rem;color:#64748b;display:block;margin-bottom:4px;">Giờ thi đấu</label>
                            <input type="time" x-model="editForm.match_time"
                                   style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:0.85rem;">
                        </div>

                        <!-- Số set (best of) -->
                        <div style="margin-bottom:12px;">
                            <label style="font-size:0.82rem;color:#64748b;display:block;margin-bottom:4px;">Số set (best of)</label>
                            <select x-model="editForm.best_of"
                                    style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:0.85rem;">
                                <option value="">-- Không đổi --</option>
                                <option value="1">1</option>
                                <option value="3">3</option>
                                <option value="5">5</option>
                            </select>
                        </div>

                        <!-- Ghi chú -->
                        <div style="margin-bottom:16px;">
                            <label style="font-size:0.82rem;color:#64748b;display:block;margin-bottom:4px;">Ghi chú</label>
                            <textarea x-model="editForm.notes" rows="2"
                                      style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:0.85rem;resize:vertical;"></textarea>
                        </div>

                        <!-- Cảnh báo cascade -->
                        <template x-if="editCascadeCount > 0">
                            <div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:6px;padding:10px;margin-bottom:12px;font-size:0.82rem;color:#92400e;">
                                <strong>Cảnh báo:</strong> Thay đổi VĐV sẽ ảnh hưởng
                                <span x-text="editCascadeCount"></span> trận đấu ở các vòng sau.
                                Dữ liệu VĐV ở các trận đó sẽ bị xóa.
                            </div>
                        </template>

                        <!-- Nút hành động -->
                        <div style="display:flex;gap:8px;justify-content:flex-end;">
                            <button class="td-btn" @click="editMatchId = null">Hủy</button>
                            <button class="td-btn td-btn-primary" @click="saveMatchEdit()" :disabled="editSaving">
                                <span x-show="!editSaving">Lưu</span>
                                <span x-show="editSaving">Đang lưu...</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- Content area --}}
    <template x-if="activeCategoryId">
        <div class="td-card" style="border-radius:0 0 10px 10px;border-top:1px solid #f1f5f9;">

            {{-- Loading state --}}
            <template x-if="loading">
                <p style="font-size:0.85rem;color:#64748b;padding:16px 0;">Đang tải...</p>
            </template>

            {{-- Generating state --}}
            <template x-if="generating">
                <p style="font-size:0.85rem;color:#64748b;padding:16px 0;">Đang tạo...</p>
            </template>

            {{-- No bracket: generate section --}}
            <template x-if="!loading && !generating && !hasBracket">
                <div style="padding:16px 0;">
                    <template x-if="bracketData['category_' + activeCategoryId + '_ready']">
                        <p style="font-size:0.85rem;color:#059669;font-weight:600;margin-bottom:12px;">
                            Vòng bảng đã hoàn tất! Sẵn sàng tạo bracket.
                        </p>
                    </template>
                    <template x-if="!bracketData['category_' + activeCategoryId + '_ready']">
                        <p style="font-size:0.85rem;color:#94a3b8;margin-bottom:12px;">
                            Vòng bảng chưa hoàn tất. Hoàn thành tất cả trận đấu vòng bảng trước.
                        </p>
                    </template>
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <label style="display:flex;align-items:center;gap:6px;font-size:0.85rem;color:#475569;cursor:pointer;">
                            <input type="checkbox" x-model="enableThirdPlace">
                           Tạo trận tranh hạng ba
                        </label>
                        <button class="td-btn td-btn-primary"
                                @click="generateBracket()"
                                :disabled="generating">
                            Tạo Bracket
                        </button>
                    </div>
                </div>
            </template>

            {{-- Bracket display --}}
            <template x-if="!loading && !generating && hasBracket">
                <div>
                    {{-- Mobile round navigation --}}
                    <div class="bracket-mobile-nav">
                        <button class="td-btn" @click="prevRound()" :disabled="currentRoundIdx === 0">Trước</button>
                        <span style="font-size:0.85rem;font-weight:600;color:#1e293b;" x-text="currentRoundName"></span>
                        <button class="td-btn" @click="nextRound()" :disabled="currentRoundIdx >= mainRounds.length - 1">Sau</button>
                    </div>

                    {{-- Horizontal bracket --}}
                    <div class="bracket-container">
                        <template x-for="(round, rIdx) in mainRounds" :key="round.number">
                            <div class="bracket-round"
                                 :class="{ 'bracket-round-active': currentRoundIdx === rIdx }">
                                <div class="bracket-round-header" x-text="round.name"></div>
                                <div class="bracket-round-matches">
                                    <template x-for="match in round.matches" :key="match.id">
                                        @include('home-yard.tournaments.partials._bracket-match')
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Third place match --}}
                    <template x-if="thirdPlaceMatch">
                        <div style="padding-top:16px;border-top:1px solid #f1f5f9;margin-top:8px;"
                             x-data="{ match: $data.thirdPlaceMatch }">
                            <div class="bracket-round-header" style="text-align:left;margin-bottom:8px;">
                                Tranh hạng ba
                            </div>
                            @include('home-yard.tournaments.partials._bracket-match')
                        </div>
                    </template>
                </div>
            </template>

        </div>
    </template>

</div>
