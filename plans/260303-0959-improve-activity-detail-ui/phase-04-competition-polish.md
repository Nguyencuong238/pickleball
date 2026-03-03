# Phase 4: Competition Tab & Polish

**Priority**: Medium
**Status**: Pending
**Effort**: ~1h

## Context

- [Plan overview](plan.md)
- Current competition panel: `resources/views/clubs/activities/partials/_competition-panel.blade.php`

## Overview

Wrap existing competition panel as tab content. Polish overall UI: transitions, mobile sticky CTA, URL hash routing, final responsive testing.

## Requirements

### Functional
- Competition panel displayed as "Tran dau" tab content
- URL hash routing (#detail, #participants, #competition)
- Deep linking: visiting URL with hash auto-selects tab
- Sticky CTA button on mobile (RSVP join)
- Flash messages (success/error) styled consistently

### Non-functional
- Smooth fade transition between tabs
- No FOUC (flash of unstyled content) on page load
- Print-friendly (detail tab only)

## Related Code Files

### Modify
- `resources/views/clubs/activities/partials/_competition-panel.blade.php` - Wrap in tab content div
- `resources/views/clubs/activities/partials/_tab-scripts.blade.php` - Add hash routing
- `resources/views/clubs/activities/partials/_show-styles.blade.php` - Final polish CSS

## Implementation Steps

### 1. Competition tab wrapper

Wrap existing `_competition-panel.blade.php` content in tab-compatible div. No content changes needed - just ensure it works within tab context.

### 2. URL hash routing

In `_tab-scripts.blade.php`:
```javascript
// Read hash on page load
var hash = window.location.hash.substring(1);
if (hash && document.getElementById('tab-' + hash)) {
    switchTab(hash);
}
// Update hash on tab switch
function switchTab(tabName) {
    // ... existing logic
    history.replaceState(null, null, '#' + tabName);
}
```

### 3. Sticky mobile CTA

```css
@media (max-width: 768px) {
    .sticky-cta {
        position: fixed; bottom: 0; left: 0; right: 0;
        padding: 12px 20px;
        background: white;
        border-top: 1px solid #e5e7eb;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        z-index: 100;
    }
    .activity-container { padding-bottom: 80px; }
}
```

### 4. Tab transitions

```css
.tab-content {
    display: none;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.tab-content.active {
    display: block;
    opacity: 1;
}
```

### 5. Final polish

- Consistent border-radius on all cards
- Hover states on interactive elements
- Focus styles for accessibility
- Alert/flash message styling within new layout
- Management dropdown animation

## Todo

- [ ] Wrap competition panel as tab content
- [ ] Implement URL hash routing
- [ ] Add sticky CTA on mobile
- [ ] Tab fade transitions
- [ ] Test all 3 activity types: one_off, recurring, competition
- [ ] Test logged-in vs guest vs management views
- [ ] Cross-browser test (Safari, Chrome, Firefox)
- [ ] Mobile responsive final check

## Success Criteria

- All tabs work for all activity types
- Competition tab only shows for competition type
- URL hash updates on tab switch
- Direct URL with hash loads correct tab
- Sticky CTA on mobile doesn't overlap content
- No visual regressions on existing functionality
- RSVP join/cancel still works correctly
- Competition team/match/score management still works

## Risk Assessment

- **Competition scripts**: Existing JS in `_competition-scripts.blade.php` uses DOM selectors that might break if wrapped differently. Test thoroughly.
- **RSVP reload**: Current RSVP does `location.reload()`. After reload, need to preserve active tab via hash.
