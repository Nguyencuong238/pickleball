# Phase 5: Bracket Editing UI

## Context Links
- [Phase 1 - swapAthletes method](phase-01-backend-service.md)
- [Phase 2 - swap route](phase-02-controller-routes.md)
- [UX Research - click-to-swap pattern](/Users/thaopv/Desktop/php/pickleball/plans/reports/researcher-260313-1505-knockout-bracket-ux-research.md)

## Overview
- **Priority**: P2
- **Status**: completed
- **Description**: Click-to-swap UI for admin to rearrange athlete positions before matches start. Simpler than drag-drop, works on mobile.

## Requirements
- R1: Click athlete slot to select; click another slot to swap
- R2: Visual highlight on selected slot + affected matches preview
- R3: Only allow swaps on 'scheduled' matches (not started/completed)
- R4: Confirm before executing swap
- R5: Cancel selection with Escape key or cancel button
- R6: Re-render bracket after successful swap

## Architecture

### Click-to-Swap Flow
1. Admin clicks athlete slot → slot highlighted (blue border)
2. Admin clicks second athlete slot → confirmation prompt shown
3. On confirm → POST to bracket.swap → re-fetch bracket
4. On cancel → clear selection

### State in Alpine.js
```javascript
swapState: {
    active: false,
    matchId1: null,
    slot1: null,      // 'athlete1' or 'athlete2'
    athleteName1: '',
    matchId2: null,
    slot2: null,
    athleteName2: '',
},
```

## Related Code Files

### Files to Modify
- `public/assets/js/bracket-manager.js` -- add swap logic (~40 LOC)
- `resources/views/home-yard/tournaments/partials/_bracket-match.blade.php` -- add click handlers + selection styling
- `public/assets/css/tournament-dashboard/bracket-tree.css` -- add swap highlight styles (~20 LOC)

## Implementation Steps

### Step 1: Add swap methods to bracket-manager.js
```javascript
// Add to bracketManager object
swapState: { active: false, matchId1: null, slot1: null, name1: '', matchId2: null, slot2: null, name2: '' },
editMode: false,

toggleEditMode() { this.editMode = !this.editMode; this.clearSwap(); },

selectSlot(matchId, slot, athleteName, athleteId) {
    if (!this.editMode || !athleteId) return;

    // Find match status
    const match = this.findMatch(matchId);
    if (!match || match.status !== 'scheduled') return;

    if (!this.swapState.active) {
        this.swapState = { active: true, matchId1: matchId, slot1: slot, name1: athleteName };
    } else {
        if (this.swapState.matchId1 === matchId && this.swapState.slot1 === slot) {
            this.clearSwap(); return;
        }
        this.swapState.matchId2 = matchId;
        this.swapState.slot2 = slot;
        this.swapState.name2 = athleteName;
        this.confirmSwap();
    }
},

async confirmSwap() {
    const msg = `Doi vi tri ${this.swapState.name1} va ${this.swapState.name2}?`;
    if (!confirm(msg)) { this.clearSwap(); return; }

    try {
        const res = await fetch(config.swapUrl || config.generateUrl.replace('/generate', '/swap'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrf },
            body: JSON.stringify({
                match_id_1: this.swapState.matchId1, slot_1: this.swapState.slot1,
                match_id_2: this.swapState.matchId2, slot_2: this.swapState.slot2,
            }),
        });
        const json = await res.json();
        if (json.success) { await this.fetchBracket(); }
        else { alert(json.message); }
    } finally { this.clearSwap(); }
},

clearSwap() {
    this.swapState = { active: false, matchId1: null, slot1: null, name1: '', matchId2: null, slot2: null, name2: '' };
},

findMatch(matchId) {
    for (const round of this.rounds) {
        const m = round.matches.find(m => m.id === matchId);
        if (m) return m;
    }
    return null;
},
```

### Step 2: Update _bracket-match.blade.php
Add click handler + selection class to each athlete slot:
```blade
<div class="bracket-slot"
     :class="{
         'bracket-slot--winner': match.winner_id && match.winner_id === match.athlete1_id,
         'bracket-slot--selected': swapState.active && swapState.matchId1 === match.id && swapState.slot1 === 'athlete1',
         'bracket-slot--swappable': editMode && match.status === 'scheduled' && match.athlete1_id,
     }"
     @click="selectSlot(match.id, 'athlete1', match.athlete1_name, match.athlete1_id)">
```

Add edit mode toggle button in bracket header:
```blade
<button class="td-btn td-btn-outline td-btn-sm"
        @click="toggleEditMode()"
        x-text="editMode ? 'Xong' : 'Chinh sua bracket'">
</button>
```

### Step 3: CSS for swap states
```css
.bracket-slot--swappable { cursor: pointer; }
.bracket-slot--swappable:hover { background: #f0f9ff; border-left: 3px solid #0369a1; }
.bracket-slot--selected { background: #dbeafe; border-left: 3px solid #2563eb; font-weight: 600; }
```

## Todo List
- [ ] Add editMode toggle + swap state to bracket-manager.js
- [ ] Add selectSlot, confirmSwap, clearSwap methods
- [ ] Update _bracket-match.blade.php with click handlers + conditional classes
- [ ] Add edit mode toggle button to bracket header
- [ ] Add CSS styles for swappable/selected states
- [ ] Test: select two athletes → confirm → bracket re-renders with swapped positions
- [ ] Test: cannot swap completed/in_progress matches
- [ ] Test: Escape key clears selection

## Success Criteria
1. Edit mode toggle shows/hides swap capability
2. Clicking athlete slot highlights it
3. Clicking second slot shows confirmation
4. Swap executes and bracket re-renders with new positions
5. Non-scheduled matches cannot be selected for swap

## Risk Assessment
| Risk | Impact | Mitigation |
|------|--------|------------|
| Accidental swap | Medium | Confirmation dialog before executing |
| Mobile tap targets too small | Low | bracket-slot already 48px+ height |
