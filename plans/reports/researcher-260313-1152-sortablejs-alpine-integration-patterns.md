# SortableJS + Alpine.js v3 Integration Research
**Date:** 2026-03-13
**Focus:** Cross-container drag-drop with x-for, event syncing patterns, race conditions

---

## Executive Summary

Alpine.js v3 + SortableJS integration is reliable **when using event-driven data sync** (onAdd/onRemove/onUpdate) rather than onEnd-only or DOM-read approaches. The "onEnd DOM-read" pattern is documented as problematic but sometimes unavoidable. Official Alpine Sort plugin exists but has limitations with complex multi-list scenarios.

**Canonical recommendation:** Use `onAdd`/`onRemove` handlers on each container to update Alpine data model synchronously, with `Alpine.raw()` to bypass reactivity wrappers.

---

## Question 1: Canonical Approach for Data Sync

### What Works: Event-Driven Sync (Recommended)

Update Alpine's data model via `onAdd`/`onRemove`/`onUpdate` event handlers **before** SortableJS completes the drag operation. This prevents the race condition where Alpine re-renders and finds stale element positions.

**Pattern:**
```javascript
const sortConfig = {
    group: 'my-group',
    onAdd: (evt) => {
        // evt.item = HTMLElement, evt.to = target container
        // evt.newIndex = position in new container
        const items = Alpine.raw(this.targetArray);
        items.splice(evt.newIndex, 0, extractedItem);
    },
    onRemove: (evt) => {
        // evt.from = source container, evt.oldIndex = old position
        const items = Alpine.raw(this.sourceArray);
        items.splice(evt.oldIndex, 1);
    }
};
```

**Why:** SortableJS has already moved the DOM element by the time these handlers fire. Updating the data model synchronously in the handler keeps Alpine's reactivity in sync with DOM reality.

### What Doesn't Work Well

1. **Data model change alone (no DOM manipulation):** Triggers Alpine re-render which re-positions DOM elements, undoing SortableJS's work.
2. **Relying only on onEnd:** onEnd fires last, but by then Alpine may have re-rendered multiple times during the drag, causing indices to become invalid.

### Official Alpine Sort Plugin

Alpine provides `x-sort` directive (built on SortableJS). Handler signature:
```html
<ul x-sort="saveOrder">
    <li x-sort:item="item.id" x-for="item in items" :key="item.id">...</li>
</ul>
```

Handler receives: `(key, newPosition)` — key from `x-sort:item`, position as 0-based index.

**Limitations documented in issues:**
- Works for single-list reordering but has limitations with cross-list drags and nested arrays
- Some users report workarounds needed when using x-for with complex conditions
- Does not expose full SortableJS event object to custom handlers

---

## Question 2: onAdd/onRemove/onUpdate vs onEnd

### Event Order in Native SortableJS

When item moves from List A to List B:
1. `onRemove` fires on List A with `evt.oldIndex` (position in A)
2. `onAdd` fires on List B with `evt.newIndex` (position in B)
3. `onEnd` fires globally

**Critical insight:** onEnd fires **after** both lists have already updated. At this point, indices may have shifted if Alpine re-rendered.

### Recommended Event Strategy

| Scenario | Event | Reason |
|----------|-------|--------|
| Item enters new list | `onAdd` | Earliest point to add item to target array |
| Item leaves source list | `onRemove` | Earliest point to remove from source |
| Reorder within same list | `onUpdate` | Captures oldIndex → newIndex within same list |
| Cross-container finalization | `onEnd` | Safe for logging/API calls; data model already synced |

**Best practice:** Use `onAdd`/`onRemove` for data mutations. Use `onEnd` for side effects (database sync, logging). Never rely on `onEnd` indices for data model changes.

### Framework Wrapper Variations

Angular wrapper (ngx-sortablejs) reverses the event order: calls `onRemove` first, then `onAdd`. Provides `onAddOriginal` for native order. This shows frameworks differ in their interpretation—check your specific binding.

---

## Question 3: Alpine.raw() vs Reactive Splice

### The Problem

Alpine.js v3 wraps reactive data in a Proxy. When you do:
```javascript
this.items.splice(idx, 1);  // wrapped array
```

Alpine's reactivity detects the change AND triggers a re-render. During re-render, Alpine attempts to reconcile elements using `:key` bindings, causing it to reuse existing DOM elements that SortableJS just moved. Result: double-swap, corrupted visual order.

### The Solution: Alpine.raw()

```javascript
const items = Alpine.raw(this.items);  // Get unwrapped array
items.splice(idx, 1);                  // Mutate directly
// Alpine still detects the change (underlying array changed)
// But no re-render cycle occurs until next Alpine tick
```

**Why it works:** `Alpine.raw()` bypasses the Proxy layer. When you mutate the underlying array directly, Alpine's reactivity watcher detects the reference change, but the re-render doesn't interfere with ongoing SortableJS operations.

### Important Caveat

`Alpine.raw()` is safe **only if the array is the exclusive data source** for that component. If other directives read the same array or other parts of the component trigger re-renders, you risk inconsistency.

**Safer approach:** Use `Alpine.raw()` + immediately trigger a forced reconciliation:
```javascript
const items = Alpine.raw(this.items);
items.splice(idx, 1);
// Optionally: mark component as updated to resync Alpine's internal state
```

---

## Question 4: Race Condition - Alpine Re-Renders During Drag

### The Race Condition

1. User starts drag → SortableJS begins manipulating DOM
2. Alpine's reactivity detects a data change (from elsewhere, or from SortableJS sending a signal)
3. Alpine triggers a re-render using the old `:key` mapping
4. Alpine reuses existing DOM elements based on `:key`, repositioning them according to current data order
5. SortableJS's DOM mutations are partially or fully reverted
6. Visual order = corrupted

### How to Avoid

**Strategy 1: Update data model in event handler, not elsewhere**
```javascript
onAdd: (evt) => {
    // Update HERE, not in a separate reactive handler
    const items = Alpine.raw(this.items);
    items.splice(evt.newIndex, 0, item);
}
```

Only data mutations allowed are those from `onAdd`/`onRemove` handlers. No other parts of the component should mutate the same array during drag.

**Strategy 2: Disable reactivity during drag**
```javascript
onStart: (evt) => {
    this.isDragging = true;  // Could block other mutations
}
onEnd: (evt) => {
    this.isDragging = false;
}
```

Use this flag to prevent unrelated re-renders during active drags.

**Strategy 3: Use Alpine Sort Plugin**
The official plugin handles this internally. Not guaranteed to work with all edge cases, but recommended for simple use cases.

**Strategy 4: Reconcile After Drag**
After `onEnd`, read the DOM and rebuild the data model (discussed next).

---

## Question 5: "onEnd DOM-Read Approach" — Is It Recommended?

### What It Is

After drag completes (onEnd fires), read the final DOM order and rebuild arrays:
```javascript
onEnd: (evt) => {
    const domOrder = [];
    document.querySelectorAll('[x-sort:item]').forEach(el => {
        domOrder.push(el.dataset.itemId);
    });
    this.items = items.filter(i => domOrder.includes(i.id))
                      .sort((a, b) => domOrder.indexOf(a.id) - domOrder.indexOf(b.id));
}
```

### Pros
- Works even if Alpine re-rendered during drag
- Guarantees DOM ↔ data synchronization after operation completes
- No need for `Alpine.raw()` or event-specific handling

### Cons (Well-Documented Issues)

1. **Fragile with x-if placeholders:** If you use `x-if` to conditionally show elements, DOM indices won't match data indices. Must read `data-*` attributes or element IDs, not positions.

2. **oldIndex/newIndex become invalid:** If Alpine re-rendered, the indices provided by SortableJS no longer match the current DOM. Must ignore them and rebuild from DOM truth.

3. **Performance:** Querying and rebuilding arrays is slower than direct mutations.

4. **Index offset problems:** Known SortableJS issue: when `filter` or `draggable` selectors exclude elements, oldIndex/newIndex are calculated relative to **all DOM elements**, not just draggable ones. Reading DOM must account for this.

### Status in Community

**Not recommended as primary approach,** but widely used as fallback when event-driven sync fails. Some frameworks (Vue.Draggable, react-sortablejs) use it internally but expose it as an implementation detail, not a public pattern.

**When to use:** Complex nested structures, multiple filtering conditions, or when event handlers are insufficient.

---

## Question 6: Alpine.js v3 x-for Keyed Reconciliation Conflicts

### The Core Issue

Alpine v3 stores `:key` information on the `<template>` element, not on individual items. When SortableJS reorders elements externally:

1. Alpine's internal `_x_prevKeys` array becomes stale (no longer matches DOM order)
2. Next Alpine re-render uses old key mappings to "reuse" elements
3. Elements get repositioned incorrectly

**Documented regressions:**
- [Alpine discussion #1635](https://github.com/alpinejs/alpine/discussions/1635): "elements are redrawn with a shift of one place"
- [Alpine discussion #4368](https://github.com/alpinejs/alpine/discussions/4368): "template x-for not display order correctly after drag"

### Solutions (In Order of Reliability)

**Solution 1: Use Stable, Unique Keys (Most Important)**

```html
<template x-for="item in items" :key="item.id">
    <div x-sort:item="item.id">{{ item.name }}</div>
</template>
```

**Never use array index as key:**
```html
<!-- WRONG -->
<template x-for="item in items" :key="$index">
```

Why: When array is reordered, `:key="$index"` changes meaning. Alpine can't distinguish between "item moved" and "item content changed."

**Solution 2: Avoid x-if in Sortable Containers**

If you must use `x-if`:
```html
<!-- FRAGILE -->
<template x-for="item in items" :key="item.id">
    <div x-if="item.visible" x-sort:item="item.id">...</div>
</template>
```

The `x-if` creates/destroys DOM elements, breaking SortableJS's element tracking. Alternatives:
- Use `x-show` (visibility only, DOM stays) instead of `x-if`
- Keep x-if outside the sortable container
- Accept that x-if + sortable don't work well together

**Solution 3: Force Re-reconciliation After Drag**

Increment a counter to change all keys:
```javascript
onEnd: (evt) => {
    this.reconcileIteration++;
}
```

```html
<template x-for="item in items" :key="`${item.id}-${reconcileIteration}`">
```

This forces Alpine to treat all elements as "new" and re-render from scratch. Expensive but reliable.

**Solution 4: Manual Key Synchronization (Undocumented)**

Some advanced users manually reset `_x_prevKeys`:
```javascript
onEnd: (evt) => {
    // Dangerous: accessing private Alpine API
    const template = document.querySelector('template[x-for]');
    template._x_prevKeys = [];  // Force recalculation
}
```

**Not recommended:** Uses private API, breaks on Alpine updates.

### Key Insight from Alpine Maintainers

The stated expectation is: **external DOM manipulations (like SortableJS) should not be used with x-for without stable keys.** The framework reserves the right to optimize key reconciliation, and external DOM libraries are outside Alpine's control.

---

## Practical Implementation Guide

### Recommended Pattern for Your Use Case

Based on research, for cross-container drag with x-for + multiple lists:

```javascript
// Alpine.js component
export default () => ({
    items: [...],
    unassigned: [...],
    manualGroups: {
        group1: { athletes: [...] },
        group2: { athletes: [...] }
    },

    init() {
        this._initSortables();
    },

    _initSortables() {
        const makeConfig = (listKey) => ({
            group: 'manual-draw',
            animation: 150,
            onAdd: (evt) => this._onAdd(evt, listKey),
            onRemove: (evt) => this._onRemove(evt, listKey),
            onEnd: (evt) => this._onDragEnd(evt),
        });

        // Initialize both lists with same group
        new Sortable(this.$el.querySelector('[data-list="unassigned"]'),
                     makeConfig('unassigned'));

        Object.keys(this.manualGroups).forEach(key => {
            new Sortable(this.$el.querySelector(`[data-list="group-${key}"]`),
                         makeConfig(key));
        });
    },

    _onAdd(evt, targetListKey) {
        // Extract item from evt.item before DOM changes
        const item = this._getItemFromElement(evt.item);

        const targetArray = targetListKey === 'unassigned'
            ? this.unassigned
            : this.manualGroups[targetListKey].athletes;

        // Use Alpine.raw() to mutate without triggering re-render during drag
        const arr = Alpine.raw(targetArray);
        arr.splice(evt.newIndex, 0, item);
    },

    _onRemove(evt, sourceListKey) {
        const sourceArray = sourceListKey === 'unassigned'
            ? this.unassigned
            : this.manualGroups[sourceListKey].athletes;

        const arr = Alpine.raw(sourceArray);
        arr.splice(evt.oldIndex, 1);
    },

    _onDragEnd(evt) {
        // Safe place for async operations (database sync, validation, etc.)
        this._saveToDatabase();
    },

    _getItemFromElement(el) {
        // Implement based on how you store item reference in DOM
        // Option 1: data attribute
        return JSON.parse(el.dataset.item);
        // Option 2: extract from component state by ID
        // const id = el.dataset.itemId;
        // return this.allItems.find(i => i.id === id);
    }
});
```

**Template:**
```html
<div x-data="drawComponent()">
    <!-- Unassigned list -->
    <div data-list="unassigned" class="sortable-list">
        <template x-for="item in unassigned" :key="item.id">
            <div x-sort:item="item.id" :data-item="JSON.stringify(item)">
                {{ item.name }}
            </div>
        </template>
    </div>

    <!-- Grouped lists -->
    <template x-for="(group, groupKey) in manualGroups" :key="groupKey">
        <div :data-list="`group-${groupKey}`" class="sortable-list">
            <template x-for="item in group.athletes" :key="item.id">
                <div x-sort:item="item.id" :data-item="JSON.stringify(item)">
                    {{ item.name }}
                </div>
            </template>
        </div>
    </template>
</div>
```

### Configuration Defaults for Connected Lists

```javascript
{
    group: 'shared-group-name',      // Must match on all connected lists
    animation: 150,                   // Visual reorder animation (ms)
    fallbackOnBody: false,            // Set true if parent is scrollable
    swapThreshold: 0.65,              // Avoid jitter with closely-packed items
    ghostClass: 'sortable-ghost',     // CSS class for drag preview
    dragClass: 'sortable-drag',       // CSS class for dragged element

    // Data sync
    onAdd: handler,                   // Required for cross-list moves
    onRemove: handler,                // Required for cross-list moves
    onUpdate: handler,                // Optional: reorder within same list

    // Don't override these (breaks plugin):
    handle: undefined,                // Unless you define specific drag handles
    filter: undefined,                // Unless some items shouldn't be draggable
    onEnd: undefined,                 // Use for side effects only
}
```

---

## Known Gotchas & Edge Cases

### x-if with x-sort
**Don't combine them directly.** The combination breaks SortableJS element tracking. Workarounds:
1. Move x-if outside the sortable container
2. Use x-show instead (hidden but DOM present)
3. Use data model filtering + stable keys instead of x-if

### Placeholder/Ghost Visibility
SortableJS creates a ghost element during drag. Alpine may accidentally re-render during drag and disrupt it. Mitigate:
1. Ensure handlers use `Alpine.raw()` — prevents mid-drag re-renders
2. Set `animation: 0` during initial testing to isolate issues
3. Use CSS `pointer-events: none` on placeholder if it interferes with drop zones

### Filtered Elements & Index Mismatch
If your list uses `filter` or `draggable` selectors:
- oldIndex/newIndex are relative to ALL elements, not just draggable ones
- Use `evt.oldDraggableIndex` / `evt.newDraggableIndex` if available (SortableJS 1.13.0+)
- If not available, read DOM position instead of trusting event indices

### Nested/Scrollable Containers
Set `fallbackOnBody: true` if parent has overflow scrolling. Also consider:
```javascript
{
    fallbackOnBody: true,
    fallbackTolerance: 0,        // Pixel tolerance for drag zones
    scrollSpeed: 10,              // Auto-scroll speed
    scrollSensitivity: 100,       // Distance from edge to trigger scroll
}
```

---

## Sources

- [Alpine.js Sort Plugin Docs](https://alpinejs.dev/plugins/sort)
- [SortableJS GitHub Repo](https://github.com/SortableJS/Sortable)
- [Alpine Issue #1635: Regression with x-for + SortableJS](https://github.com/alpinejs/alpine/discussions/1635)
- [Alpine Issue #4368: x-for display order after drag](https://github.com/alpinejs/alpine/discussions/4368)
- [Alpine Issue #4157: x-sort with x-for best practices](https://github.com/alpinejs/alpine/discussions/4157)
- [Desarrollolibre: SortableJS Alpine Integration Pattern](https://www.desarrollolibre.net/blog/javascript/sortable-js-alpinejs-for-drag-and-drop-sorting-23)
- [Vue.Draggable Architecture Reference](https://github.com/SortableJS/vue.draggable.next)
- [SortableJS Issue #1564: Filter elements index calculation](https://github.com/SortableJS/Sortable/issues/1564)
- [SortableJS Issue #818: onRemove/onAdd timing](https://github.com/SortableJS/Sortable/issues/818)

---

## Unresolved Questions

1. **Alpine.raw() edge cases with nested objects:** Does modifying a property inside a raw array trigger reactivity? (e.g., `Alpine.raw(this.items)[0].name = "new"` — does Alpine detect it?)
   - Current assumption: Yes, because the outer array reference changed
   - Should verify with Alpine source code or test

2. **Private API risk:** How stable is `_x_prevKeys` across Alpine versions? If using manual key sync workaround, what's the upgrade risk?
   - No official docs; only workaround, not supported

3. **Cross-container onAdd/onRemove timing guarantees:** In a 3+ container setup, if item moves through multiple containers rapidly, is event order guaranteed consistent?
   - Likely yes (synchronous), but no explicit documentation found

4. **SortableJS v1.15.x end-of-life:** Current version is 1.15.x. Is there active maintenance? Any planned breaking changes that would affect Alpine integration?
   - Last checked: Sep 2025; appears stable
   - No major breaking changes since v1.13.0 (2021)
