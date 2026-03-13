# SortableJS + Alpine.js v3 — Quick Reference

**Research Date:** 2026-03-13
**Full Report:** `/Users/thaopv/Desktop/php/pickleball/plans/reports/researcher-260313-1152-sortablejs-alpine-integration-patterns.md`

---

## Answers to Your 6 Questions

### 1. Canonical Approach for Data Sync?

**Use event-driven sync: `onAdd`/`onRemove` handlers update Alpine data model synchronously.**

```javascript
{
    onAdd: (evt) => {
        const items = Alpine.raw(this.targetArray);
        items.splice(evt.newIndex, 0, extractedItem);
    },
    onRemove: (evt) => {
        const items = Alpine.raw(this.sourceArray);
        items.splice(evt.oldIndex, 1);
    }
}
```

SortableJS fires these handlers before re-renders occur. Data mutations in handlers stay in sync with DOM.

### 2. onAdd/onRemove vs onEnd?

| Event | Use For | Details |
|-------|---------|---------|
| `onAdd` | Add item to target array | Fires when item enters destination list |
| `onRemove` | Remove item from source array | Fires when item leaves source list |
| `onUpdate` | Reorder within same list | Fires when sort order changes within single list |
| `onEnd` | Side effects only | Logs, DB sync, validation — NOT data mutations |

**Order:** onRemove → onAdd → onEnd (synchronously)

**Why not onEnd alone:** By then Alpine may have re-rendered, making indices invalid.

### 3. Alpine.raw() vs Reactive Splice?

**`Alpine.raw()` prevents mid-drag re-renders that would corrupt SortableJS's DOM changes.**

```javascript
// Bad: triggers re-render during drag
this.items.splice(idx, 1);

// Good: mutates directly, prevents re-render interference
const items = Alpine.raw(this.items);
items.splice(idx, 1);
```

Alpine's Proxy wrapper detects the change eventually, but doesn't interrupt the drag operation.

**Caveat:** Only safe if the array is the exclusive data source for that component.

### 4. How to Avoid the Race Condition?

Three strategies:

1. **Update data in event handler only** (recommended)
   - No other component logic mutates the sorted array during drag
   - Use `Alpine.raw()` in handlers

2. **Disable other reactivity during drag**
   - Set flag `isDragging = true` on onStart
   - Block unrelated mutations/re-renders while dragging

3. **Read DOM after drag completes** (fallback)
   - Slower but works even if Alpine re-renders during drag
   - Must account for x-if conditions and filtered elements

### 5. Is "onEnd DOM-Read Approach" Recommended?

**No — documented as problematic, but used as fallback when event handlers fail.**

Pros:
- Works regardless of re-renders during drag
- Guarantees DOM ↔ data sync after operation

Cons:
- Breaks with x-if placeholders (indices don't match)
- oldIndex/newIndex become invalid after Alpine re-renders
- Performance cost (DOM queries + array rebuilding)
- SortableJS issue: indices count filtered-out elements

Use only when event-driven sync fails.

### 6. x-for Keyed Reconciliation Conflicts?

**Alpine v3 stores `:key` info on `<template>` element, not items. External reordering breaks internal tracking.**

Solutions (in order):

1. **Use stable unique keys** (most important)
   ```html
   <template x-for="item in items" :key="item.id">
       <!-- Good: key doesn't change -->
   </template>
   ```

2. **Never use array index as key**
   ```html
   <!-- Wrong -->
   <template x-for="item in items" :key="$index">
   ```

3. **Avoid x-if in sortable containers**
   - Use x-show instead (hidden but DOM present)
   - Move x-if outside sortable area
   - Accept that x-if + SortableJS conflict

4. **Force re-reconciliation after drag** (expensive)
   ```javascript
   onEnd: (evt) => {
       this.reconcileIteration++;
   }
   ```
   ```html
   <template x-for="item in items" :key="`${item.id}-${reconcileIteration}`">
   ```

---

## Implementation Pattern

```javascript
export default () => ({
    items: [...],
    isDragging: false,

    init() {
        new Sortable(this.$el.querySelector('.sortable-list'), {
            group: 'my-group',
            animation: 150,
            onAdd: (evt) => {
                const items = Alpine.raw(this.items);
                items.splice(evt.newIndex, 0, this.extractItem(evt.item));
            },
            onRemove: (evt) => {
                const items = Alpine.raw(this.items);
                items.splice(evt.oldIndex, 1);
            },
            onEnd: (evt) => {
                // Safe: data model already updated, do side effects
                this._saveToDatabase();
            }
        });
    },

    extractItem(element) {
        // Get item from DOM (data attribute or lookup by ID)
        return JSON.parse(element.dataset.item);
    }
});
```

**Template:**
```html
<div x-data="drawComponent()" class="sortable-list">
    <template x-for="item in items" :key="item.id">
        <div x-sort:item="item.id" :data-item="JSON.stringify(item)">
            {{ item.name }}
        </div>
    </template>
</div>
```

---

## Key Takeaways

- **Event-driven sync is canonical** — onAdd/onRemove to update data model, onEnd for side effects
- **Alpine.raw() is essential** — prevents mid-drag re-renders that corrupt DOM
- **Stable :key values are mandatory** — never use array indices
- **Avoid x-if in sortable areas** — conflicts with element tracking
- **onEnd DOM-read is fallback only** — slower, fragile, documented as problematic
- **Cross-container moves:** Use `onAdd` on target + `onRemove` on source simultaneously

---

## Gotchas

| Issue | Cause | Fix |
|-------|-------|-----|
| Elements shift position after drag | Using `:key="$index"` | Use `:key="item.id"` |
| Index mismatch with filtered lists | Indices count non-draggable elements | Use `evt.oldDraggableIndex` if available, or read DOM |
| x-if placeholders break sorting | Alpine creates/destroys elements | Use x-show or move x-if outside |
| Slow DOM-read approach | Querying and rebuilding arrays | Use event handlers instead |
| Alpine re-renders during drag | Unrelated mutations trigger reactivity | Use `Alpine.raw()` in handlers, set isDragging flag |

---

## Sources

- [Alpine.js Sort Plugin](https://alpinejs.dev/plugins/sort)
- [SortableJS GitHub](https://github.com/SortableJS/Sortable)
- [Alpine Discussion #1635](https://github.com/alpinejs/alpine/discussions/1635) — Regression with x-for
- [Alpine Discussion #4368](https://github.com/alpinejs/alpine/discussions/4368) — x-for corruption
- [Alpine Discussion #4157](https://github.com/alpinejs/alpine/discussions/4157) — Best practices
