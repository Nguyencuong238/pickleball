# SortableJS + Alpine.js v3 — Code Patterns & Examples

**Reference:** Full research at `/Users/thaopv/Desktop/php/pickleball/plans/reports/researcher-260313-1152-sortablejs-alpine-integration-patterns.md`

---

## Pattern 1: Single-List Reordering (Simplest)

### HTML
```html
<div x-data="sortableList()" class="list">
    <template x-for="item in items" :key="item.id">
        <div class="item" x-sort:item="item.id">
            <span>{{ item.name }}</span>
        </div>
    </template>
</div>
```

### JavaScript
```javascript
function sortableList() {
    return {
        items: [
            { id: 1, name: 'Item A' },
            { id: 2, name: 'Item B' },
            { id: 3, name: 'Item C' }
        ],

        init() {
            new Sortable(this.$el.querySelector('.list'), {
                animation: 150,
                onUpdate: (evt) => this.handleReorder(evt)
            });
        },

        handleReorder(evt) {
            const items = Alpine.raw(this.items);
            const [movedItem] = items.splice(evt.oldIndex, 1);
            items.splice(evt.newIndex, 0, movedItem);
            console.log('Reordered:', this.items);
        }
    };
}
```

**Key:** `onUpdate` fires only for reordering within same list. Use `Alpine.raw()` to mutate.

---

## Pattern 2: Cross-Container Drag (Multiple Connected Lists)

### HTML
```html
<div x-data="multiListSort()">
    <!-- Unassigned list -->
    <div class="list" data-list="unassigned">
        <h3>Unassigned</h3>
        <template x-for="item in unassigned" :key="item.id">
            <div class="item" :data-item-id="item.id">
                {{ item.name }}
            </div>
        </template>
    </div>

    <!-- Group lists -->
    <template x-for="(group, idx) in groups" :key="group.id">
        <div class="list" :data-list="`group-${group.id}`">
            <h3>{{ group.name }}</h3>
            <template x-for="item in group.items" :key="item.id">
                <div class="item" :data-item-id="item.id">
                    {{ item.name }}
                </div>
            </template>
        </div>
    </template>
</div>
```

### JavaScript
```javascript
function multiListSort() {
    return {
        unassigned: [
            { id: 1, name: 'Player A' },
            { id: 2, name: 'Player B' }
        ],
        groups: [
            { id: 'g1', name: 'Group 1', items: [{ id: 3, name: 'Player C' }] },
            { id: 'g2', name: 'Group 2', items: [] }
        ],
        itemMap: {}, // Cache for fast lookup

        init() {
            // Build lookup table
            this.buildItemMap();

            // Initialize all lists with same group
            const listElements = this.$el.querySelectorAll('.list');
            listElements.forEach(el => {
                new Sortable(el, {
                    group: 'shared-draw',
                    animation: 150,
                    onAdd: (evt) => this.handleAdd(evt),
                    onRemove: (evt) => this.handleRemove(evt),
                    onEnd: (evt) => this.handleDragEnd(evt)
                });
            });
        },

        buildItemMap() {
            // Quick lookup: itemId → { container, list, index }
            this.itemMap = {};

            // Unassigned items
            this.unassigned.forEach((item, idx) => {
                this.itemMap[item.id] = { container: 'unassigned', list: this.unassigned, index: idx };
            });

            // Group items
            this.groups.forEach(group => {
                group.items.forEach((item, idx) => {
                    this.itemMap[item.id] = { container: `group-${group.id}`, list: group.items, index: idx };
                });
            });
        },

        handleAdd(evt) {
            const itemId = parseInt(evt.item.dataset.itemId);
            const item = this.findItem(itemId);

            // Add to target container
            const targetList = this.getListFromElement(evt.to);
            if (targetList) {
                const arr = Alpine.raw(targetList);
                arr.splice(evt.newIndex, 0, item);
                console.log('Added item', itemId, 'to', evt.to.dataset.list, 'at position', evt.newIndex);
            }
        },

        handleRemove(evt) {
            const itemId = parseInt(evt.item.dataset.itemId);

            // Remove from source container
            const sourceList = this.getListFromElement(evt.from);
            if (sourceList) {
                const arr = Alpine.raw(sourceList);
                arr.splice(evt.oldIndex, 1);
                console.log('Removed item', itemId, 'from', evt.from.dataset.list);
            }
        },

        handleDragEnd(evt) {
            // Data model already updated by onAdd/onRemove
            // Safe place for async operations
            this.saveToDatabase();
        },

        findItem(id) {
            // Search all lists
            const found = this.unassigned.find(i => i.id === id);
            if (found) return found;

            for (let group of this.groups) {
                const item = group.items.find(i => i.id === id);
                if (item) return item;
            }
            return null;
        },

        getListFromElement(containerEl) {
            // Extract list from container element
            const listKey = containerEl.dataset.list;

            if (listKey === 'unassigned') {
                return this.unassigned;
            }

            const groupId = listKey.replace('group-', '');
            const group = this.groups.find(g => g.id === groupId);
            return group ? group.items : null;
        },

        saveToDatabase() {
            console.log('Saving order to database...');
            // POST to /api/draw/save with current state
        }
    };
}
```

**Key Points:**
- Use `onAdd` + `onRemove` to keep data in sync
- Build itemMap for O(1) item lookup
- `getListFromElement()` resolves container → actual array reference

---

## Pattern 3: Handling x-if & Placeholder Issues

### Problem HTML (DON'T DO THIS)
```html
<!-- FRAGILE: x-if + x-sort conflict -->
<template x-for="item in items" :key="item.id">
    <div x-if="item.visible" x-sort:item="item.id">
        {{ item.name }}
    </div>
</template>
```

### Solution 1: Use x-show Instead
```html
<!-- BETTER: x-show keeps DOM present -->
<template x-for="item in items" :key="item.id">
    <div x-show="item.visible" x-sort:item="item.id">
        {{ item.name }}
    </div>
</template>
```

### Solution 2: Move x-if Outside
```html
<div x-if="hasVisibleItems">
    <template x-for="item in items" :key="item.id">
        <div x-sort:item="item.id">
            {{ item.name }}
        </div>
    </template>
</div>
```

### Solution 3: Force Re-reconciliation (Expensive)
```javascript
function sortableWithFilter() {
    return {
        items: [...],
        filterActive: false,
        reconcileIteration: 0,

        init() {
            new Sortable(this.$el.querySelector('.list'), {
                onEnd: (evt) => {
                    // Increment to force key change on all items
                    this.reconcileIteration++;
                }
            });
        }
    };
}
```

```html
<!-- Key includes iteration counter to force re-render -->
<template x-for="item in items" :key="`${item.id}-${reconcileIteration}`">
    <div x-show="!filterActive || item.visible" x-sort:item="item.id">
        {{ item.name }}
    </div>
</template>
```

---

## Pattern 4: Filtered Elements & Index Offset

### Problem: Filter Option Changes Index Meaning

```javascript
const list = new Sortable(el, {
    draggable: '.draggable-item',  // Only these can be dragged
    onAdd: (evt) => {
        // BUG: evt.newIndex counts ALL elements, not just draggable ones!
        console.log('Index:', evt.newIndex); // May not match filtered array
    }
});
```

### Solution: Use newDraggableIndex (v1.13.0+)

```javascript
const list = new Sortable(el, {
    draggable: '.draggable-item',
    onAdd: (evt) => {
        // evt.newDraggableIndex only counts draggable elements
        const items = Alpine.raw(this.items);
        items.splice(evt.newDraggableIndex, 0, item);
    }
});
```

### Fallback: Read DOM After Drag

```javascript
const list = new Sortable(el, {
    draggable: '.draggable-item',
    onEnd: (evt) => {
        // Read DOM order directly (accounts for all filters)
        const newOrder = [];
        this.$el.querySelectorAll('.draggable-item').forEach(el => {
            newOrder.push(parseInt(el.dataset.itemId));
        });

        // Rebuild array from DOM order
        this.items = newOrder
            .map(id => this.findItem(id))
            .filter(Boolean);
    }
});
```

---

## Pattern 5: Prevent Re-Renders During Drag

### Flag-Based Prevention

```javascript
function protectedSort() {
    return {
        items: [...],
        isDragging: false,

        init() {
            new Sortable(this.$el, {
                onStart: (evt) => {
                    this.isDragging = true;
                },
                onAdd: (evt) => {
                    if (this.isDragging) {
                        this.handleAdd(evt);
                    }
                },
                onEnd: (evt) => {
                    this.isDragging = false;
                    // Safe now to do other operations
                    this.saveToDatabase();
                }
            });
        },

        handleAdd(evt) {
            const items = Alpine.raw(this.items);
            items.splice(evt.newIndex, 0, item);
        },

        // Other mutations use isDragging check
        addNewItem() {
            if (this.isDragging) return; // Skip if drag in progress
            const items = Alpine.raw(this.items);
            items.push(newItem);
        }
    };
}
```

---

## Pattern 6: Nested Arrays (Groups with Athletes)

### Full Example: Tournament Draw Setup

```javascript
function tournamentDraw() {
    return {
        unassigned: [{ id: 1, name: 'Alice' }, ...],
        manualGroups: {
            group1: { id: 'g1', name: 'Group 1', athletes: [{ id: 5, name: 'Bob' }] },
            group2: { id: 'g2', name: 'Group 2', athletes: [] }
        },

        init() {
            // Initialize unassigned list
            new Sortable(this.$el.querySelector('[data-list="unassigned"]'), {
                group: 'draw-move',
                onAdd: (evt) => this.onAthleteAdded(evt, 'unassigned'),
                onRemove: (evt) => this.onAthleteRemoved(evt, 'unassigned')
            });

            // Initialize group lists
            Object.keys(this.manualGroups).forEach(groupId => {
                new Sortable(
                    this.$el.querySelector(`[data-list="group-${groupId}"]`),
                    {
                        group: 'draw-move',
                        onAdd: (evt) => this.onAthleteAdded(evt, groupId),
                        onRemove: (evt) => this.onAthleteRemoved(evt, groupId)
                    }
                );
            });
        },

        onAthleteAdded(evt, targetGroupId) {
            const athlete = this.extractAthleteFromElement(evt.item);

            if (targetGroupId === 'unassigned') {
                const arr = Alpine.raw(this.unassigned);
                arr.splice(evt.newIndex, 0, athlete);
            } else {
                const group = this.manualGroups[targetGroupId];
                const arr = Alpine.raw(group.athletes);
                arr.splice(evt.newIndex, 0, athlete);
            }
        },

        onAthleteRemoved(evt, sourceGroupId) {
            if (sourceGroupId === 'unassigned') {
                const arr = Alpine.raw(this.unassigned);
                arr.splice(evt.oldIndex, 1);
            } else {
                const group = this.manualGroups[sourceGroupId];
                const arr = Alpine.raw(group.athletes);
                arr.splice(evt.oldIndex, 1);
            }
        },

        extractAthleteFromElement(el) {
            return JSON.parse(el.dataset.athlete);
        }
    };
}
```

### HTML
```html
<div x-data="tournamentDraw()" @x-init="init()">
    <!-- Unassigned -->
    <div data-list="unassigned" class="sortable-list">
        <h3>Unassigned</h3>
        <template x-for="athlete in unassigned" :key="athlete.id">
            <div class="athlete" :data-athlete="JSON.stringify(athlete)">
                {{ athlete.name }}
            </div>
        </template>
    </div>

    <!-- Groups -->
    <template x-for="(group, groupId) in manualGroups" :key="group.id">
        <div :data-list="`group-${groupId}`" class="sortable-list">
            <h3>{{ group.name }}</h3>
            <template x-for="athlete in group.athletes" :key="athlete.id">
                <div class="athlete" :data-athlete="JSON.stringify(athlete)">
                    {{ athlete.name }}
                </div>
            </template>
        </div>
    </template>
</div>
```

---

## Pattern 7: Race Condition Protection (Full Example)

```javascript
function robustSort() {
    return {
        items: [...],
        isDragging: false,
        dragStartTime: null,

        init() {
            new Sortable(this.$el, {
                group: 'robust',
                ghostClass: 'sortable-ghost',
                animation: 150,

                onStart: (evt) => {
                    this.isDragging = true;
                    this.dragStartTime = Date.now();
                    // Optionally: disable other reactive features
                },

                onAdd: (evt) => this.syncAdd(evt),
                onRemove: (evt) => this.syncRemove(evt),

                onEnd: (evt) => {
                    const dragDuration = Date.now() - this.dragStartTime;
                    this.isDragging = false;

                    // Validate final state
                    if (this.validateState()) {
                        this.persist();
                    } else {
                        this.showError('Invalid sort state');
                        location.reload(); // Fallback
                    }
                }
            });
        },

        syncAdd(evt) {
            if (!this.isDragging) return;

            const items = Alpine.raw(this.items);
            const item = this.findItem(evt.item.id);
            items.splice(evt.newIndex, 0, item);
        },

        syncRemove(evt) {
            if (!this.isDragging) return;

            const items = Alpine.raw(this.items);
            items.splice(evt.oldIndex, 1);
        },

        validateState() {
            // Check for duplicates, missing items, etc.
            const ids = this.items.map(i => i.id);
            return new Set(ids).size === ids.length;
        },

        persist() {
            // POST final order to server
            fetch('/api/sort-order', {
                method: 'POST',
                body: JSON.stringify(this.items)
            });
        }
    };
}
```

---

## Critical Configuration

```javascript
const config = {
    // Data sync
    group: 'my-group',              // Must match on all connected lists
    animation: 150,                  // Reorder animation (ms)
    ghostClass: 'sortable-ghost',   // CSS class for drag placeholder
    dragClass: 'sortable-drag',      // CSS class for dragged element

    // Drag behavior
    handle: '.drag-handle',          // Optional: drag via specific element
    draggable: '.item',              // Optional: only these are draggable
    filter: '.no-drag',              // Optional: these cannot be dragged
    preventOnFilter: true,           // Call preventDefault if filter matched

    // Container behavior
    fallbackOnBody: false,           // Set true for nested/scrollable parents
    swapThreshold: 0.65,             // Swap threshold (0-1)
    scrollSpeed: 10,                 // Auto-scroll speed
    scrollSensitivity: 100,          // Distance from edge to trigger scroll

    // Event handlers
    onStart: (evt) => {},            // Drag started
    onAdd: (evt) => {},              // Item added to this list
    onRemove: (evt) => {},           // Item removed from this list
    onUpdate: (evt) => {},           // Item reordered within list
    onSort: (evt) => {},             // Any change (add/remove/update)
    onEnd: (evt) => {},              // Drag completed

    // DON'T override these (breaks Alpine plugin integration):
    // onMove, setData, unmount, etc.
};
```

---

## Summary

- **Single list:** Use `onUpdate` with `Alpine.raw()`
- **Multi-list:** Use `onAdd` + `onRemove` on each container
- **Always:** Use stable `:key` values (never indices)
- **Avoid:** x-if in sortable containers (use x-show)
- **Side effects:** Use `onEnd`, not `onAdd`/`onRemove`
- **Protection:** Set `isDragging` flag to block concurrent mutations
- **Validation:** Check state in `onEnd` before persisting
