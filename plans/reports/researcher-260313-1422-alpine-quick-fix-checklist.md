# Alpine.js Nested Object Reactivity: Quick Fix Checklist

## Problem Symptoms Checklist

- [ ] Button inside `x-if` inside `x-for` appears disabled but never changes
- [ ] `:disabled="loadingSchedule[match.id]"` binding doesn't update
- [ ] `x-text="loadingSchedule[match.id] ? 'loading...' : 'Save'"` doesn't reflect state change
- [ ] State updates in JS console (verified with `Alpine.raw(component).loadingSchedule`) but template doesn't reflect it
- [ ] Issue only happens inside `x-for` loops, not in static elements

**Diagnosis**: Proxy reactivity not established for dynamically-keyed object properties.

---

## One-Line Fix (Try First)

Replace `x-if` with `x-show`:

```html
<!-- ❌ Before -->
<template x-if="showButton">
  <button :disabled="loadingSchedule[match.id]">Save</button>
</template>

<!-- ✅ After -->
<button x-show="showButton" :disabled="loadingSchedule[match.id]">Save</button>
```

**Why**: `x-show` keeps button in DOM → binding stays active → reactivity works.

If that alone doesn't work, proceed to the next fix.

---

## Fix #2: Pre-Declare All Keys

```javascript
// ❌ Current
x-data="{
  loadingSchedule: {}
}"

// ✅ Fixed
x-data="{
  loadingSchedule: Object.fromEntries(
    matches.map(m => [m.id, false])
  )
}"
```

**Why**: Proxy establishes dependencies when keys exist in initial data.

---

## Fix #3: Use Map Instead of Object

```javascript
// ❌ Object with dynamic keys
loadingSchedule: {}
// In handler: this.loadingSchedule[matchId] = true

// ✅ Map (clearer intent + works)
loadingStates: new Map()
// In handler: this.loadingStates.set(matchId, true)
```

Template:
```html
:disabled="loadingStates.get(match.id) ?? false"
```

---

## Fix #4: Force Reactivity with Spread

Last resort (works but inefficient):

```javascript
// When setting state:
this.loadingSchedule = {
  ...this.loadingSchedule,
  [matchId]: true
}
```

---

## Testing

After applying fix, verify in browser console:

```javascript
// Element should have disabled attribute
document.querySelector('button').disabled

// Alpine state should be true
Alpine.store() or component.$data.loadingSchedule[matchId]

// Manually trigger change and watch UI update
Alpine.raw(component).loadingSchedule[matchId] = false
```

---

## Prevention Rules

1. Always declare reactive properties in initial `x-data`, even if `null`
2. Prefer `x-show` over `x-if` for elements with live bindings
3. For per-item state in loops, use Maps or pre-declared keys
4. Test bindings work *before* state assignment, not after

---

## Related Files

- **Full analysis**: `researcher-260313-1422-alpine-nested-object-reactivity.md`
- **SortableJS patterns**: `researcher-260313-1152-sortablejs-alpine-integration-patterns.md` (similar reactivity principles)
