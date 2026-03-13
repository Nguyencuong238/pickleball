# Alpine.js v3 Nested Object Reactivity: Root Cause Analysis

## Executive Summary

The pattern `loadingSchedule: {}` + `loadingSchedule[match.id]` fails in templates because:
1. **Proxy tracking limitation**: Alpine/Vue use Proxies; accessing non-existent properties doesn't trigger reactivity awareness when the property hasn't been set yet
2. **Dynamic keys not pre-declared**: Keys added dynamically (`loadingSchedule[matchId] = true`) are not observed unless the property existed during initialization
3. **x-if + x-for interaction**: x-if inside x-for creates DOM mutation ordering issues that compound the reactivity problem

**Fix priority:** Use direct property initialization (not empty objects) or leverage Alpine's reactivity patterns correctly.

---

## Root Cause Analysis

### 1. The Core Issue: Empty Object Initialization

```javascript
// ❌ PROBLEMATIC
x-data="{
  loadingSchedule: {}  // Empty object - no keys declared
}"
```

When you initialize `loadingSchedule` as an empty object `{}`, Alpine/Vue doesn't know about potential keys like `loadingSchedule[match.id]`. Later, when you set:

```javascript
this.loadingSchedule[matchId] = true
```

The Proxy sees this as adding a **new property to an already-reactive object**. Vue/Alpine's Proxy-based reactivity **does eventually track these dynamically-added properties**, but there's a critical timing issue:

**Problem**: The template binding `:disabled="loadingSchedule[match.id]"` is evaluated **before** the property is assigned. The access happens during template compilation/binding setup, and if the property doesn't exist yet, the Proxy doesn't establish a dependency relationship properly.

### 2. Proxy Reactivity Caveat (Vue 3 Foundation)

Alpine.js uses Vue 3's reactivity engine, which relies on JavaScript Proxies. Per [Vue.js documentation](https://vuejs.org/guide/essentials/reactivity-fundamentals.html):

> "You need to ensure [properties] are all present in the object returned by the `data` function. Where necessary, use `null`, `undefined` or some other placeholder value."

While Vue 3 Proxies *can* track dynamically-added properties, **properties must be declared in the initial data object** for robust reactivity. Accessing a property that doesn't exist yet means the Proxy never establishes a "dependency" on that specific property key.

### 3. x-if Inside x-for Compounds the Problem

```html
<template x-for="match in matches">
  <template x-if="someCondition">
    <button :disabled="loadingSchedule[match.id]">Save</button>
  </template>
</template>
```

**Why this breaks:**
- **x-if removes/inserts DOM**: When `x-if` condition changes, Alpine removes the entire `<button>` from DOM
- **x-for re-renders on state change**: When you set `loadingSchedule[matchId] = true`, Alpine may re-render the `x-for`, triggering x-if evaluation again
- **DOM mutation order**: The sequence is unclear: does x-if toggle first or x-for re-render first?
- **Button state lost**: If the button is removed from DOM during the loading state update, the disabled binding never propagates

**Reference**: [Alpine.js x-if documentation](https://alpinejs.dev/directives/if) does NOT support x-transition, and combining with x-for creates unpredictable DOM lifecycles.

### 4. Why :disabled Binding Fails

The `:disabled="loadingSchedule[match.id]"` binding doesn't work as expected because:

1. **Initial access returns `undefined`**: When the template first evaluates `loadingSchedule[match.id]`, the key doesn't exist → returns `undefined`
2. **Reactivity not established**: The Proxy doesn't register a dependency on `loadingSchedule.{dynamic-key}` because that key was never declared
3. **Later assignment ignored**: When you do `loadingSchedule[matchId] = true`, Alpine doesn't know that `loadingSchedule[match.id]` changed in templates that already evaluated it

Additionally, the interaction with x-if mutations means the button element might be removed/re-added before the binding update propagates.

---

## Recommended Fix Patterns

### Pattern 1: Pre-Declare All Keys (RECOMMENDED)

**Why**: Explicit declaration ensures Proxy tracks the property from the start.

```javascript
x-data="{
  loadingSchedule: Object.fromEntries(
    matches.map(m => [m.id, false])  // Declare all keys upfront
  )
}"
```

**Pros:**
- Proxy establishes dependencies immediately
- Binding `:disabled="loadingSchedule[match.id]"` works reliably
- Simple, predictable

**Cons:**
- Must know all match IDs upfront
- Less dynamic

---

### Pattern 2: Direct Property Assignment (BEST for Per-Item State)

**Why**: Use computed properties or dedicated state instead of nested objects.

Instead of:
```javascript
loadingSchedule: { [matchId]: true }
```

Use:
```javascript
loadingStates: new Map()  // or: loadingById = {}
```

Then:
```javascript
loadingStates.set(matchId, true)
// or
loadingById[matchId] = true
```

And in template:
```html
:disabled="loadingStates.has(match.id) && loadingStates.get(match.id)"
```

**Pros:**
- Clearer intent (a map of states)
- Works with dynamic IDs
- Easier to debug

**Cons:**
- Slightly more verbose

---

### Pattern 3: Use Spread Syntax with Reassignment

```javascript
// When setting state, create new object (forces Proxy update)
this.loadingSchedule = {
  ...this.loadingSchedule,
  [matchId]: true
}
```

**Why it works**: Creating a new object reference forces Alpine to re-evaluate all dependent expressions, even if Proxy tracking was incomplete.

**Cons:**
- Inefficient (copies entire object)
- Works around the issue rather than fixing it

---

### Pattern 4: Replace x-if with x-show (CRITICAL FIX)

**Current pattern:**
```html
<template x-if="someCondition">
  <button :disabled="loadingSchedule[match.id]">Save</button>
</template>
```

**Better pattern:**
```html
<button x-show="someCondition" :disabled="loadingSchedule[match.id]">Save</button>
```

**Why this fixes it:**
- `x-show` uses CSS (`display: none`) instead of removing from DOM
- Button stays in DOM → binding stays active → reactivity works
- No x-if/x-for interaction issues

**Reference**: [Alpine.js x-show is recommended over x-if for toggling](https://alpinejs.dev/directives/show) when you need persistent bindings.

---

### Pattern 5: Initialize with $watch (For Complex Logic)

```javascript
x-data="{
  matches: [],
  loadingSchedule: {},
  init() {
    this.$watch('matches', (newMatches) => {
      // Ensure loadingSchedule has keys for all matches
      newMatches.forEach(m => {
        if (!(m.id in this.loadingSchedule)) {
          this.loadingSchedule[m.id] = false
        }
      })
    }, { immediate: true })
  }
}"
```

**Pros:**
- Works with dynamic match lists
- Proactive initialization ensures Proxy tracking

**Cons:**
- More boilerplate
- Only needed for very dynamic scenarios

---

## Summary Table

| Pattern | Works? | Notes |
|---------|--------|-------|
| `loadingSchedule: {}` + dynamic keys | ❌ No | Reactivity not established for missing keys |
| Pre-declared keys | ✅ Yes | Best if IDs known upfront |
| Map-based state | ✅ Yes | Cleaner, more explicit |
| Spread reassignment | ⚠️ Works | Inefficient, workaround not fix |
| x-show instead of x-if | ✅ Yes | **Critical fix for x-for interaction** |
| $watch initialization | ✅ Yes | For dynamic scenarios |

---

## Implementation Priority

1. **Immediate**: Replace `x-if` with `x-show` to eliminate DOM lifecycle issues
2. **Short-term**: Pre-declare all keys in `loadingSchedule` (or use Map pattern)
3. **Validation**: Test `:disabled` binding with browser DevTools (inspect if attribute actually changes)

---

## Key Alpine.js Best Practices for Per-Item Loading States

- **Declare all reactive properties upfront**, even if initially `null` or `undefined`
- **Avoid dynamic key objects** for frequently-accessed properties; use Maps or pre-declared keys
- **Prefer `x-show` over `x-if`** for toggling elements with active bindings
- **Use `Alpine.raw()` only when directly mutating arrays** (e.g., splice operations)
- **Test Proxy tracking**: Bind to the property in template *before* the JS assigns it

---

## Sources

- [Vue.js Reactivity Fundamentals](https://vuejs.org/guide/essentials/reactivity-fundamentals.html) — Proxy requirements, property declaration
- [Alpine.js Advanced Reactivity](https://alpinejs.dev/advanced/reactivity) — Proxy-based tracking
- [Alpine.js x-if Directive](https://alpinejs.dev/directives/if) — DOM insertion/removal behavior
- [Alpine.js x-show Directive](https://alpinejs.dev/directives/show) — CSS-based toggling alternative
- [GitHub Discussion: Alpine.js Nested Object Properties](https://github.com/alpinejs/alpine/discussions/3428)
- [Non-Reactive Data in Alpine.js](https://sebastiandedeyne.com/non-reactive-data-in-alpine-js/) — Dynamic properties caveats
