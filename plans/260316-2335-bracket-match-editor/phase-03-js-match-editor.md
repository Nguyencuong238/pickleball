# Phase 3: JS Match Editor Mixin

## Overview
- **Priority**: P0
- **Status**: pending
- New Alpine.js mixin `bracket-match-editor.js` for match edit modal logic

## Requirements
- R1: `openMatchEditor(matchId)` - fetch eligible athletes, populate form with current data
- R2: `saveMatchEdit()` - PUT to update-match endpoint, re-fetch bracket on success
- R3: Include current match athletes in eligible list (they're already assigned to this match)
- R4: State: editMatchId, editForm, editEligibleAthletes, editLoading, editSaving

## Related Code Files

### Files to Create
- `public/assets/js/bracket-match-editor.js` (~80 LOC)

### Files to Modify
- `public/assets/js/bracket-manager.js` - add mixin spread + new state vars
- `resources/views/home-yard/tournaments/bracket.blade.php` - add script tag

### Files to Reference
- `public/assets/js/bracket-score-entry.js` - follow same mixin pattern
- `public/assets/js/bracket-swap-editor.js` - follow same mixin pattern

## Implementation Steps

### Step 1: Create `bracket-match-editor.js`
```javascript
/**
 * bracket-match-editor.js
 * Alpine.js mixin: match editing modal methods.
 */
function bracketMatchEditorMixin() {
    return {
        editMatchId: null,
        editEligibleAthletes: [],
        editLoading: false,
        editSaving: false,
        editCascadeCount: 0,
        editForm: {
            athlete1_id: '',
            athlete2_id: '',
            match_time: '',
            best_of: '',
            notes: '',
        },

        async openMatchEditor(matchId) {
            this.editMatchId = matchId;
            this.editLoading = true;
            this.editEligibleAthletes = [];

            // Pre-fill from current match data
            var match = this.findMatch(matchId);
            if (match) {
                this.editForm = {
                    athlete1_id: match.athlete1 ? String(match.athlete1.id) : '',
                    athlete2_id: match.athlete2 ? String(match.athlete2.id) : '',
                    match_time: match.match_time || '',
                    best_of: match.best_of ? String(match.best_of) : '',
                    notes: match.notes || '',
                };
            }

            try {
                var url = this.dataUrl.replace('/data', '/eligible-athletes') + '?match_id=' + matchId;
                var res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                var data = await res.json();
                if (data.success) {
                    // Include current athletes in list (they belong to this match)
                    var list = data.athletes || [];
                    if (match && match.athlete1 && !list.find(function(a) { return a.id === match.athlete1.id; })) {
                        list.unshift({ id: match.athlete1.id, name: match.athlete1.name, partner_name: match.athlete1.partner_name, pair_name: match.athlete1.pair_name || match.athlete1.name, seed: match.athlete1.seed });
                    }
                    if (match && match.athlete2 && !list.find(function(a) { return a.id === match.athlete2.id; })) {
                        list.unshift({ id: match.athlete2.id, name: match.athlete2.name, partner_name: match.athlete2.partner_name, pair_name: match.athlete2.pair_name || match.athlete2.name, seed: match.athlete2.seed });
                    }
                    this.editEligibleAthletes = list;
                }
            } catch (e) {
                console.error('Failed to load eligible athletes', e);
            } finally {
                this.editLoading = false;
            }
        },

        async saveMatchEdit() {
            this.editSaving = true;
            try {
                var url = this.dataUrl.replace('/data', '/update-match');
                var body = { match_id: this.editMatchId };

                if (this.editForm.athlete1_id !== undefined) body.athlete1_id = this.editForm.athlete1_id || null;
                if (this.editForm.athlete2_id !== undefined) body.athlete2_id = this.editForm.athlete2_id || null;
                if (this.editForm.match_time) body.match_time = this.editForm.match_time;
                if (this.editForm.best_of) body.best_of = parseInt(this.editForm.best_of);
                if (this.editForm.notes !== undefined) body.notes = this.editForm.notes;

                // First call: check cascade (without confirm_cascade)
                // If response has affected_count > 0, ask confirm then resend with confirm_cascade=true
                // Updated: Validation Session 1 - Added cascade confirm logic

                var res = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(body),
                });
                var data = await res.json();
                if (data.success) {
                    this.editMatchId = null;
                    await this.fetchBracket();
                } else {
                    alert(data.message || 'Luu that bai');
                }
            } catch (e) {
                alert('Loi ket noi');
                console.error(e);
            } finally {
                this.editSaving = false;
            }
        },
    };
}
```

### Step 2: Update bracket-manager.js
Add to return object:
```javascript
...bracketMatchEditorMixin(),
```

And add initial state vars:
```javascript
editMatchId: null,
editEligibleAthletes: [],
editLoading: false,
editSaving: false,
editForm: { athlete1_id: '', athlete2_id: '', match_time: '', best_of: '', notes: '' },
```

### Step 3: Add script tag to bracket.blade.php
```blade
<script src="{{ asset('assets/js/bracket-match-editor.js') }}?v=1.0"></script>
```
Before bracket-manager.js.

### Step 4: Update formatMatch in KnockoutBracketQuery
Add `match_time`, `best_of`, `notes` to the returned data so the form can pre-fill:
```php
'match_time' => $match->match_time ? Carbon::parse($match->match_time)->format('H:i') : null,
'best_of'    => $match->best_of,
'notes'      => $match->notes,
```

## Todo List
- [ ] Create `bracket-match-editor.js` mixin
- [ ] Add mixin spread to `bracket-manager.js`
- [ ] Add script tag to `bracket.blade.php`
- [ ] Update `formatMatch()` to include match_time, best_of, notes
- [ ] Test: open modal -> loads eligible athletes
- [ ] Test: select different athletes -> save -> bracket updates
- [ ] Test: clear athlete -> save -> becomes bye
- [ ] Test: add athlete to bye match -> save -> reverts to scheduled

## Success Criteria
1. Modal pre-fills with current match data
2. Eligible athletes list correct per round logic
3. Save updates athletes and properties
4. Bracket auto-refreshes after save
5. Bye detection works correctly after edit

## Risk Assessment
| Risk | Impact | Mitigation |
|------|--------|------------|
| findMatch not finding match in bronze round | Low | findMatch already searches all rounds |
| editForm.athlete_id type mismatch (int vs string) | Low | Use String() conversion for comparisons |
