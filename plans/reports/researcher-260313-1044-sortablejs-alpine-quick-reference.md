# SortableJS + Alpine.js v3: Quick Reference & Code Patterns

**TL;DR:** Use `onAdd`/`onRemove` event handlers with `Alpine.raw()` to sync data. Read full report at `researcher-260313-1044-sortablejs-alpine-sync-patterns.md`.

---

## Problem in Current Code

```javascript
_onManualDrop(evt, targetKey) {
    this._syncManualFromDOM();  // ❌ Reads DOM after drop
}
```

**Issues:**
- Alpine may re-render during drag, breaking lookup tables
- Race condition: DOM state doesn't match data state
- Fragile: relies on DOM attribute parsing
- No tracking of source list

---

## Solution Pattern (Copy-Paste Ready)

### 1. Initialize Sortables with Event Handlers

```javascript
_initManualSortables() {
    this._manualSortables.forEach(s => s.destroy());
    this._manualSortables = [];

    const makeOptions = (listKey) => ({
        group: 'manual-draw',
        animation: 150,
        handle: '.drag-handle',
        onAdd: (evt) => this._onItemAdded(evt, listKey),
        onRemove: (evt) => this._onItemRemoved(evt, listKey),
        onUpdate: (evt) => this._onItemReordered(evt, listKey),
        onEnd: (evt) => this._onDragEnd(evt),
    });

    // Unassigned list
    const unassignedEl = document.getElementById('manual-unassigned');
    if (unassignedEl) {
        this._manualSortables.push(
            new Sortable(unassignedEl, makeOptions('unassigned'))
        );
    }

    // Group lists
    Object.keys(this.manualGroups).forEach(gid => {
        const el = document.getElementById('manual-group-' + gid);
        if (el) {
            this._manualSortables.push(
                new Sortable(el, makeOptions(gid))
            );
        }
    });
}
```

### 2. Handle Item Removal

```javascript
_onItemRemoved(evt, sourceListKey) {
    // Runs when item leaves a list
    const sourceIndex = evt.oldIndex;

    if (sourceListKey === 'unassigned') {
        const items = Alpine.raw(this.unassigned);
        items.splice(sourceIndex, 1);
    } else {
        const groupItems = Alpine.raw(this.manualGroups[sourceListKey].athletes);
        groupItems.splice(sourceIndex, 1);
    }
}
```

### 3. Handle Item Addition

```javascript
_onItemAdded(evt, targetListKey) {
    // Runs when item arrives in a list
    const targetIndex = evt.newIndex;
    const athleteId = parseInt(evt.item.dataset.athleteId);

    // Find athlete object
    const athlete = this._findAthleteById(athleteId);
    if (!athlete) return;

    // Insert into target
    if (targetListKey === 'unassigned') {
        const items = Alpine.raw(this.unassigned);
        items.splice(targetIndex, 0, athlete);
    } else {
        const groupItems = Alpine.raw(this.manualGroups[targetListKey].athletes);
        groupItems.splice(targetIndex, 0, athlete);
    }
}

_findAthleteById(id) {
    const found = this.unassigned.find(a => (a.id ?? a.athlete1_id) === id);
    if (found) return found;

    for (const group of Object.values(this.manualGroups)) {
        const found = group.athletes.find(a => (a.id ?? a.athlete1_id) === id);
        if (found) return found;
    }
    return null;
}
```

### 4. Handle Reordering Within Same List

```javascript
_onItemReordered(evt, listKey) {
    // Runs when item moves within same list
    const oldIndex = evt.oldIndex;
    const newIndex = evt.newIndex;

    if (oldIndex === newIndex) return;

    let items;
    if (listKey === 'unassigned') {
        items = Alpine.raw(this.unassigned);
    } else {
        items = Alpine.raw(this.manualGroups[listKey].athletes);
    }

    const [movedItem] = items.splice(oldIndex, 1);
    items.splice(newIndex, 0, movedItem);
}
```

### 5. Cleanup (Optional)

```javascript
_onDragEnd(evt) {
    // Runs after all handlers above
    // Use for visual feedback, validation, auto-save, etc.
    console.log('Drag complete');
}
```

---

## Critical: Why Alpine.raw()

Alpine.js v3 wraps objects in Proxy. Without `Alpine.raw()`:

```javascript
this.unassigned.splice(0, 1);  // ❌ Proxy intercepts, may not detect
```

With `Alpine.raw()`:

```javascript
const items = Alpine.raw(this.unassigned);
items.splice(0, 1);  // ✅ Direct mutation, Alpine detects and re-renders
```

---

## Why This Works (vs. Current Approach)

| Step | Current | Proposed |
|------|---------|----------|
| User drags item A | DOM moves, Alpine stale | DOM moves, Alpine stale |
| Drop completes | `onEnd` fires, reads DOM | `onAdd`/`onRemove` fire, update data |
| Data updated | Lookup table built from stale data | Data already correct |
| Alpine reconciles | Re-renders from stale data; double-swap | Re-renders from correct data; correct order |

**Result:** Proposed approach avoids the double-swap race condition.

---

## HTML (No Changes Needed)

Keep existing structure:
```html
<ul class="draw-unassigned-list" id="manual-unassigned">
  <template x-for="athlete in unassigned" :key="athlete.id ?? athlete.pair_id">
    <li class="draw-athlete-item" :data-athlete-id="athlete.id ?? athlete.athlete1_id">
      <span class="drag-handle">&#8942;&#8942;</span>
      <span x-text="displayName(athlete)"></span>
    </li>
  </template>
</ul>

<ul class="draw-group-athletes" :id="'manual-group-' + group.id">
  <template x-for="athlete in group.athletes" :key="athlete.id ?? athlete.pair_id">
    <li class="draw-group-athlete" :data-athlete-id="athlete.id ?? athlete.athlete1_id">
      <!-- content -->
    </li>
  </template>
</ul>
```

**Key attributes:**
- `id="manual-unassigned"` and `id="manual-group-{id}"` — used to initialize Sortables
- `data-athlete-id` — used to find athlete object during drop
- `:key` in x-for — must be stable (not index)

---

## Event Properties Available

When handlers fire, `evt` contains:

```javascript
evt.item        // HTMLElement being dragged
evt.from        // Source container (HTMLElement)
evt.to          // Target container (HTMLElement)
evt.oldIndex    // Index in source (0-based)
evt.newIndex    // Index in target (0-based)
```

**Firing order for cross-list drag:**
1. `onRemove` on source (evt.from = source, evt.oldIndex = old position)
2. `onAdd` on target (evt.to = target, evt.newIndex = new position)
3. `onEnd` (evt.from and evt.to both set)

**Within-list reorder:**
1. `onUpdate` (evt.from = evt.to, oldIndex → newIndex)
2. `onEnd`

---

## Testing Checklist

- [ ] Drag item from unassigned to group → appears in group data
- [ ] Drag item from group to unassigned → leaves group data, joins unassigned
- [ ] Reorder within unassigned list → order changes in data
- [ ] Reorder within group list → order changes in data
- [ ] Drag item between two groups → leaves group A data, joins group B data
- [ ] Data persists when Save is clicked
- [ ] No visual glitches (double-swap, flicker)
- [ ] Works on touch devices (SortableJS supports touch automatically)

---

## Edge Cases & Fixes

### Case 1: Athlete Already Exists in Target Group
**Problem:** No validation; athlete appears twice.

**Fix:** In `_onItemAdded`, check if athlete already in target list:
```javascript
_onItemAdded(evt, targetListKey) {
    const athleteId = parseInt(evt.item.dataset.athleteId);
    const athlete = this._findAthleteById(athleteId);
    if (!athlete) return;

    // Check if already in target
    let targetList;
    if (targetListKey === 'unassigned') {
        targetList = this.unassigned;
    } else {
        targetList = this.manualGroups[targetListKey]?.athletes || [];
    }

    if (targetList.some(a => (a.id ?? a.athlete1_id) === athleteId)) {
        // Already exists; cancel or move instead of duplicate
        return;
    }

    // Insert...
}
```

### Case 2: Group Full (Max Participants)
**Problem:** Allow drop but exceed max.

**Fix:** In `_onItemAdded`, check max before insert:
```javascript
if (targetListKey !== 'unassigned') {
    const group = this.manualGroups[targetListKey];
    if (group.athletes.length >= group.max) {
        this._showToast('Bảng đã đầy.', 'error');
        return;  // Don't add; Sortable reverts DOM
    }
}
```

### Case 3: Drag Cancelled
**Problem:** User cancels drag mid-way; data already updated.

**Fix:** Snapshot before drag, allow undo:
```javascript
_onDragStart(evt) {
    this._snapshot = {
        unassigned: JSON.parse(JSON.stringify(this.unassigned)),
        manualGroups: JSON.parse(JSON.stringify(this.manualGroups)),
    };
}

undoLastDrag() {
    if (this._snapshot) {
        this.unassigned = this._snapshot.unassigned;
        this.manualGroups = this._snapshot.manualGroups;
        this._snapshot = null;
    }
}
```

---

## Performance Notes

- **No performance penalty:** Event handlers are O(1), splice is O(n) for list size (unavoidable)
- **Memory:** Snapshot feature (undo) adds 2x memory copy; OK for tournament draws (typically < 200 athletes)
- **Re-renders:** Only affected lists re-render (Alpine's fine-grained reactivity)

---

## Troubleshooting

| Symptom | Cause | Fix |
|---------|-------|-----|
| Item disappears after drop | `_findAthleteById` returns null | Ensure `data-athlete-id` matches athlete ID |
| Item doesn't move | Event handler not firing | Check ID selector matches DOM ID |
| Double-swap (item flickers) | Alpine.raw() not used | Add `Alpine.raw()` wrapper |
| Drag disabled after drop | Sortable not re-initialized | Ensure `_nextTick()` before `_initManualSortables()` |
| Touch not working | Sortable needs focus | Sortable supports touch natively; no fix needed |

---

## Related Files

- **Full Research Report:** `/Users/thaopv/Desktop/php/pickleball/plans/reports/researcher-260313-1044-sortablejs-alpine-sync-patterns.md`
- **Memory (Quick Ref):** `/Users/thaopv/.claude/agent-memory/researcher/sortablejs-alpine-integration.md`
- **Implementation File:** `/Users/thaopv/Desktop/php/pickleball/public/assets/js/tournament-draw.js`
- **HTML Template:** `/Users/thaopv/Desktop/php/pickleball/resources/views/home-yard/tournaments/partials/_draw-manual.blade.php`

