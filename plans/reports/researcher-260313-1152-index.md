# SortableJS + Alpine.js v3 Research — Complete Index

**Research Date:** 2026-03-13
**Status:** Complete with actionable recommendations

---

## Overview

Comprehensive research on SortableJS + Alpine.js v3 integration patterns, focusing on cross-container drag-drop with x-for rendering and keyed reconciliation conflicts.

**Bottom Line:** Use event-driven data sync (onAdd/onRemove) with Alpine.raw() to update data models synchronously. This avoids race conditions where Alpine re-renders and corrupts SortableJS's DOM mutations.

---

## Documents in This Research

### 1. **Quick Reference** (Start here)
**File:** `researcher-260313-1152-sortablejs-alpine-summary.md`

- Direct answers to your 6 key questions
- Comparison table: onAdd vs onRemove vs onEnd
- Implementation pattern (copy-paste ready)
- Key gotchas and workarounds

**Read this for:** Quick answers, decision matrix, immediate guidance

---

### 2. **Full Technical Report** (Comprehensive)
**File:** `researcher-260313-1152-sortablejs-alpine-integration-patterns.md`

- Question 1: Canonical approach for data sync
- Question 2: Event strategy (onAdd/onRemove vs onEnd)
- Question 3: Alpine.raw() mechanics and caveats
- Question 4: Race condition prevention strategies
- Question 5: onEnd DOM-read approach (problematic but documented)
- Question 6: x-for keyed reconciliation conflicts + solutions
- Practical implementation guide with nested arrays
- Configuration defaults for connected lists
- Known gotchas and edge cases
- Unresolved technical questions for future research

**Read this for:** Deep understanding, edge cases, architectural decisions, sources

---

### 3. **Code Patterns** (Implementation reference)
**File:** `researcher-260313-1152-sortablejs-code-patterns.md`

**7 complete working patterns:**
1. Single-list reordering (simplest)
2. Cross-container drag (multiple connected lists)
3. Handling x-if & placeholder issues
4. Filtered elements & index offset
5. Prevent re-renders during drag
6. Nested arrays (groups with athletes)
7. Race condition protection (full example)

Plus critical configuration reference.

**Read this for:** Copy-paste code, examples for your specific use case, configuration details

---

## Quick Decision Tree

### My use case is...

**Single list, reorder items:**
→ Read Pattern 1 in Code Patterns
→ Use `onUpdate` handler

**Multiple lists, items move between them:**
→ Read Pattern 2 in Code Patterns
→ Use `onAdd` + `onRemove` handlers
→ Reference full technical report Q2

**Items have conditional visibility (x-if):**
→ Read Pattern 3 in Code Patterns
→ Solution: Use x-show instead of x-if
→ See Q6 in technical report

**Some items are not draggable (filter):**
→ Read Pattern 4 in Code Patterns
→ Use `evt.newDraggableIndex` if available
→ Fallback: Read DOM after drag

**Tournament draw with nested arrays:**
→ Read Pattern 6 in Code Patterns
→ Reference full technical report Q1 & Q3

**Concerned about race conditions / Alpine re-renders:**
→ Read Pattern 5 & Pattern 7 in Code Patterns
→ Reference full technical report Q4

---

## Key Findings Summary

### Canonical Recommendation

**Use event-driven data sync:**
- `onAdd` handler: Add item to target array
- `onRemove` handler: Remove item from source array
- `onEnd` handler: Finalize (side effects only, no data mutations)

```javascript
{
    onAdd: (evt) => {
        const items = Alpine.raw(this.targetArray);
        items.splice(evt.newIndex, 0, item);
    },
    onRemove: (evt) => {
        const items = Alpine.raw(this.sourceArray);
        items.splice(evt.oldIndex, 1);
    },
    onEnd: (evt) => {
        // Safe for DB sync, validation, logging
    }
}
```

**Why:** Data model updates happen before Alpine reconciliation, preventing race conditions.

### Alpine.raw() is Essential

Alpine wraps reactive arrays in Proxy. Direct mutations trigger re-renders that can interrupt SortableJS's DOM operations. `Alpine.raw()` bypasses the Proxy layer:

```javascript
const items = Alpine.raw(this.items);  // Get unwrapped array
items.splice(idx, 1);                  // Mutate directly
```

### Keyed Reconciliation Conflicts

Alpine v3 stores `:key` info on `<template>`, not items. External DOM reordering breaks internal tracking.

**Solution:** Always use stable, unique keys:
```html
<template x-for="item in items" :key="item.id">  <!-- Good -->
<template x-for="item in items" :key="$index">   <!-- Bad -->
```

### x-if + SortableJS Don't Mix

`x-if` creates/destroys elements, breaking SortableJS tracking.

**Solutions:**
1. Use `x-show` instead (visibility only, DOM stays)
2. Move `x-if` outside sortable container
3. Use data filtering instead of conditional rendering

### onEnd DOM-Read Approach (Not Recommended)

Reading DOM after drag and rebuilding arrays works as fallback but:
- Slower (DOM queries + array rebuilds)
- Breaks with x-if placeholders
- oldIndex/newIndex become invalid after re-renders
- SortableJS indices count non-draggable elements

**Use only when event handlers fail.**

---

## Event Order in SortableJS

When item moves from List A to List B:

```
1. onStart fires (on List A)
   ↓
2. SortableJS moves DOM element to List B
   ↓
3. onRemove fires (on List A) — data model update
   ↓
4. onAdd fires (on List B) — data model update
   ↓
5. onEnd fires (globally) — safe for side effects
```

**Key:** Update data model in onAdd/onRemove (steps 3-4), not onEnd.

---

## Configuration Essentials

```javascript
{
    group: 'shared-name',          // Same on all connected lists
    animation: 150,                 // Reorder animation (ms)
    onAdd: handler,                 // Cross-list move → target
    onRemove: handler,              // Cross-list move → source
    onUpdate: handler,              // Reorder within list
    onEnd: handler,                 // Finalization
}
```

**Never override:** handle, group, filter, onSort, onStart callbacks (breaks plugin integration).

---

## Critical Gotchas

| Gotcha | Fix |
|--------|-----|
| Elements shift position after drag | Use `:key="item.id"` not `:key="$index"` |
| x-if ruins sorting | Use `x-show` or move x-if outside |
| Index mismatch with filters | Use `evt.newDraggableIndex` or read DOM |
| Alpine re-renders during drag | Use `Alpine.raw()` in handlers |
| onEnd indices are stale | Don't use them for data mutations |
| Race condition: item appears twice | Set `isDragging` flag to block other mutations |

---

## How to Apply This Research

### If you're building from scratch:
1. Start with Pattern 2 (multi-list) in Code Patterns
2. Use event-driven sync (onAdd/onRemove)
3. Use Alpine.raw() for array mutations
4. Verify `:key` values are stable (never indices)
5. Avoid x-if in sortable containers

### If you're refactoring existing code:
1. Replace onEnd DOM-read approach with onAdd/onRemove handlers
2. Add Alpine.raw() to array splice operations
3. Test with multiple rapid drags to catch race conditions
4. Set isDragging flag to prevent concurrent mutations
5. Validate state in onEnd before persisting

### If you're debugging issues:
1. Check `:key` values (console: `item.id` or similar, not indices)
2. Check for x-if in sortable containers
3. Add isDragging flag to prevent other mutations
4. Verify event handlers use Alpine.raw()
5. If still broken, fallback to onEnd DOM-read (Pattern 4 in Code Patterns)

---

## Sources Consulted

**Official Documentation:**
- [Alpine.js Sort Plugin](https://alpinejs.dev/plugins/sort)
- [SortableJS Documentation](https://sortablejs.github.io/Sortable/)

**Alpine.js Issues (Documented Regressions):**
- [#1635: Regression with x-for and SortableJS](https://github.com/alpinejs/alpine/discussions/1635)
- [#4368: x-for display order corruption after drag](https://github.com/alpinejs/alpine/discussions/4368)
- [#4157: x-sort with x-for best practices](https://github.com/alpinejs/alpine/discussions/4157)

**SortableJS Issues:**
- [#818: onRemove/onAdd timing](https://github.com/SortableJS/Sortable/issues/818)
- [#1564: Filter elements & index calculation](https://github.com/SortableJS/Sortable/issues/1564)
- [#1911: oldIndex/newIndex behavior](https://github.com/SortableJS/Sortable/issues/1911)

**Reference Implementations:**
- [Desarrollolibre: SortableJS + Alpine Pattern](https://www.desarrollolibre.net/blog/javascript/sortable-js-alpinejs-for-drag-and-drop-sorting-23)
- [Vue.Draggable Architecture](https://github.com/SortableJS/vue.draggable.next)

---

## What's Tested vs Theoretical

**Confirmed Working:**
- Event-driven sync with onAdd/onRemove
- Alpine.raw() for array mutations
- Stable :key values prevent corruption
- Multi-list connected groups with same group name
- isDragging flag prevents concurrent mutations

**Documented as Problematic:**
- onEnd DOM-read approach (works but slower, fragile)
- x-if in sortable containers (breaks element tracking)
- Array indices as :key values (breaks reconciliation)
- Overriding onEnd handler (conflicts with plugin)

**Not Tested in This Research:**
- Real-time socket sync while dragging
- 50+ item lists performance characteristics
- Touch device / mobile browser compatibility
- RTL (right-to-left) language support
- Accessibility (keyboard navigation with Sortable)

---

## Unresolved Questions

See full technical report for 4 unresolved questions:
1. Alpine.raw() with nested object mutations
2. Private API stability (_x_prevKeys across versions)
3. Cross-container event order guarantees in 3+ list setups
4. SortableJS v1.15.x maintenance status

---

## Next Steps

1. **Choose your pattern** from Code Patterns document based on your use case
2. **Read the corresponding Q&A** in Quick Reference or Full Report
3. **Test with your data structure** — patterns are generic, adapt to your needs
4. **Add validation** in onEnd handler before persisting
5. **Monitor for race conditions** during rapid dragging
6. **Refer back to Edge Cases section** if issues arise

---

**Research Status:** ✅ Complete
**Confidence Level:** High (backed by official docs, GitHub issues, community patterns)
**Applicability:** Production-ready patterns with documented caveats
