# Phase 4: Responsive Design + Polish

## Overview
- **Priority**: P2
- **Status**: Completed
- **Effort**: 0.5h

Responsive breakpoints, edge case handling, final polish.

## Implementation Steps

1. **Responsive breakpoints**:
   - Desktop (>1200px): 4-column group grid, horizontal bracket
   - Tablet (768-1200px): 2-column group grid, horizontal bracket with scroll
   - Mobile (<768px): 1-column group grid, bracket rounds stacked or single-round view

2. **Edge cases**:
   - Tournament with no categories -> show text schedule fallback
   - Category with no groups -> show "Chua co lich thi dau"
   - Groups with no matches -> show empty state
   - Bracket with no matches -> hide bracket section
   - Mixed: some categories have groups, others don't

3. **Polish**:
   - Score color: green (#10B981) for higher score, red (#EF4444) for lower
   - Winner row in standings: subtle green background
   - Advanced athletes marker (dot or highlight) in standings
   - Smooth transitions for category tab switching
   - Print-friendly: bracket readable when printed

## Todo List
- [ ] Add responsive breakpoints for group grid
- [ ] Add responsive breakpoints for bracket
- [ ] Handle all empty states
- [ ] Polish score colors and winner highlighting
- [ ] Test on mobile viewport

## Success Criteria
- Readable on all screen sizes
- No broken layouts on edge cases
- Consistent with site's existing visual style
