# Phase 2: Frontend Modal (Blade)

## Overview
- **Priority**: P0
- **Status**: pending
- Add edit match modal to `_bracket-tree.blade.php`

## Requirements
- R1: Modal with athlete1/athlete2 dropdowns + match properties
- R2: Dropdowns populated from eligible-athletes API
- R3: Show current assignments as default selected
- R4: Allow clearing an athlete (set to null / "Chua xac dinh")
- R5: Save triggers `updateMatch` endpoint, then re-fetch bracket

## Related Code Files

### Files to Modify
- `resources/views/home-yard/tournaments/partials/_bracket-tree.blade.php` - add modal template
- `resources/views/home-yard/tournaments/partials/_bracket-match.blade.php` - add edit button

### Files to Reference
- Score modal in `_bracket-tree.blade.php` (lines 55-86) - follow same pattern

## Implementation Steps

### Step 1: Add edit button to `_bracket-match.blade.php`
Add after the score button section, visible only in editMode:
```blade
{{-- Edit match button --}}
<template x-if="editMode">
    <div style="padding:4px 8px;text-align:center;border-top:1px solid #f1f5f9;">
        <button class="bracket-score-btn"
                @click.stop="openMatchEditor(match.id)"
                style="font-size:0.75rem;color:#6b21a8;cursor:pointer;background:none;border:none;">
            Chinh sua tran dau
        </button>
    </div>
</template>
```

### Step 2: Add modal to `_bracket-tree.blade.php`
After the score modal (line 86), add match editor modal:
```blade
{{-- Match editor modal --}}
<template x-if="editMatchId">
    <div style="position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:50;display:flex;align-items:center;justify-content:center;"
         @keydown.escape.window="editMatchId = null">
        <div style="background:#fff;border-radius:10px;padding:24px;max-width:480px;width:90%;">
            <h3 style="font-size:1rem;font-weight:700;margin:0 0 16px;">Chinh sua tran dau</h3>

            <!-- Loading -->
            <template x-if="editLoading">
                <p style="font-size:0.85rem;color:#64748b;">Dang tai...</p>
            </template>

            <template x-if="!editLoading">
                <div>
                    <!-- Athlete 1 -->
                    <div style="margin-bottom:12px;">
                        <label style="font-size:0.82rem;color:#64748b;display:block;margin-bottom:4px;">VDV 1</label>
                        <select x-model="editForm.athlete1_id"
                                style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:0.85rem;">
                            <option value="">-- Chua xac dinh --</option>
                            <template x-for="a in editEligibleAthletes" :key="a.id">
                                <option :value="a.id" x-text="a.pair_name || a.name"
                                        :disabled="String(a.id) === String(editForm.athlete2_id)"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Athlete 2 -->
                    <div style="margin-bottom:12px;">
                        <label style="font-size:0.82rem;color:#64748b;display:block;margin-bottom:4px;">VDV 2</label>
                        <select x-model="editForm.athlete2_id"
                                style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:0.85rem;">
                            <option value="">-- Chua xac dinh --</option>
                            <template x-for="a in editEligibleAthletes" :key="a.id">
                                <option :value="a.id" x-text="a.pair_name || a.name"
                                        :disabled="String(a.id) === String(editForm.athlete1_id)"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Match time -->
                    <div style="margin-bottom:12px;">
                        <label style="font-size:0.82rem;color:#64748b;display:block;margin-bottom:4px;">Gio thi dau</label>
                        <input type="time" x-model="editForm.match_time"
                               style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:0.85rem;">
                    </div>

                    <!-- Best of -->
                    <div style="margin-bottom:12px;">
                        <label style="font-size:0.82rem;color:#64748b;display:block;margin-bottom:4px;">So set (best of)</label>
                        <select x-model="editForm.best_of"
                                style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:0.85rem;">
                            <option value="">-- Khong doi --</option>
                            <option value="1">1</option>
                            <option value="3">3</option>
                            <option value="5">5</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div style="margin-bottom:16px;">
                        <label style="font-size:0.82rem;color:#64748b;display:block;margin-bottom:4px;">Ghi chu</label>
                        <textarea x-model="editForm.notes" rows="2"
                                  style="width:100%;padding:8px;border:1px solid #e2e8f0;border-radius:6px;font-size:0.85rem;resize:vertical;"></textarea>
                    </div>

                    <!-- Cascade warning -->
                    <!-- Updated: Validation Session 1 - Added cascade warning area -->
                    <template x-if="editCascadeCount > 0">
                        <div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:6px;padding:10px;margin-bottom:12px;font-size:0.82rem;color:#92400e;">
                            <strong>Canh bao:</strong> Thay doi VDV se anh huong
                            <span x-text="editCascadeCount"></span> tran dau o cac vong sau.
                            Du lieu VDV o cac tran do se bi xoa.
                        </div>
                    </template>

                    <!-- Actions -->
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button class="td-btn" @click="editMatchId = null">Huy</button>
                        <button class="td-btn td-btn-primary" @click="saveMatchEdit()" :disabled="editSaving">
                            <span x-show="!editSaving">Luu</span>
                            <span x-show="editSaving">Dang luu...</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
```

## Todo List
- [ ] Add edit button to `_bracket-match.blade.php` (visible in editMode)
- [ ] Add match editor modal to `_bracket-tree.blade.php`
- [ ] Athlete dropdowns disable option if already selected in the other dropdown
- [ ] Style consistent with existing score modal

## Success Criteria
1. Edit button visible only in editMode
2. Modal opens with current match data pre-filled
3. Dropdowns show eligible athletes from correct round pool
4. Cannot select same athlete for both slots
5. Modal closeable with Escape or cancel button
