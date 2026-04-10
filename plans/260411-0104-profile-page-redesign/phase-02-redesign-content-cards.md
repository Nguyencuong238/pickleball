# Phase 2: Redesign Content Cards

## Context
- [Research Report](../reports/researcher-260411-0104-profile-page-redesign.md)
- Current: `resources/views/front/ocr/profile.blade.php` (lines 385-520)
- OPRS components: `resources/views/components/oprs/score-card.blade.php`, `breakdown-chart.blade.php`

## Overview
- **Priority:** P1
- **Status:** Complete
- **Effort:** 1.5h

Merge two redundant OPRS cards into one unified card. Polish badges, elo history, and match history sections with consistent card design.

## Key Insights
- Current: 2 OPRS cards (score-card + breakdown-chart) show overlapping data
- Badges section uses emoji icons (not professional)
- Match history is basic list - needs cleaner layout
- All cards should have consistent border-radius, shadow, spacing

## Requirements

### Functional
- Unified OPRS card: score + breakdown bars + level progress in one card
- Badges: grid with styled icons (no emoji)
- Elo History: compact timeline
- Recent Matches: cleaner layout with outcome badges
- Win rate ring/circle as visual element

### Non-functional
- Consistent card design system (same radius, shadow, padding)
- Max 2 columns on desktop, 1 on mobile
- Scrollable sections for long lists (max-height)

## Architecture

```
Content Section Layout:
┌─────────────────────────┬─────────────────────────┐
│  OPRS Chi Tiet          │  Thanh Tich             │
│  ┌─────────────────┐    │  [Badge] [Badge] [Badge]│
│  │ Elo      ▓▓▓▓░░│    │                         │
│  │ Challenge ▓▓░░░░│    │  Tien Trinh:            │
│  │ Community ▓░░░░░│    │  [=======>   ] 7/10     │
│  └─────────────────┘    │                         │
│  Next: Advanced +175pts │                         │
├─────────────────────────┼─────────────────────────┤
│  Lich Su Elo            │  Tran Dau Gan Day       │
│  +25  Thang  12/03      │  A vs B  11-9  Thang    │
│  -12  Thua   10/03      │  A vs C  8-11  Thua     │
│  +8   Thang  08/03      │                         │
└─────────────────────────┴─────────────────────────┘
```

## Related Code Files

| File | Action | Description |
|------|--------|-------------|
| `resources/views/front/ocr/profile.blade.php` | Modify | Rewrite content section (lines 385-520) |
| `resources/views/components/oprs/score-card.blade.php` | Modify | Merge into unified OPRS card |
| `resources/views/components/oprs/breakdown-chart.blade.php` | Delete | Delete after merge (validated: not needed) |
<!-- Updated: Validation Session 1 - Changed action from Modify/Remove to Delete -->

## Implementation Steps

1. **Create unified OPRS component**
   - Merge score-card + breakdown-chart into single `score-card.blade.php`
   - Top: OPRS title + skill level badge
   - Middle: 3 breakdown rows with colored progress bars (green/blue/purple)
   - Each row: icon + name + weighted value + bar
   - Bottom: level progress bar with "Next level" info

2. **Redesign card base styles**
   - Border-radius: 16px
   - Box-shadow: `0 2px 12px rgba(0,0,0,0.06)`
   - Background: white
   - Card header: bold title with icon, no background (cleaner)
   - Card body: 1.25rem padding

3. **Redesign badges section**
   - Replace emoji with styled CSS circles (letter icon or SVG)
   - Badge icon: colored gradient circle (gold/silver/bronze based on type)
   - Badge name below icon, 0.75rem
   - Progress bars: thin (6px), brand green gradient

4. **Redesign Elo History**
   - Compact list with colored change badges (+25 green, -12 red)
   - Date on right, reason on left
   - Max-height: 250px with scroll

5. **Redesign Match History**
   - Full-width card (span both columns)
   - Each match: player names, score, outcome badge (Thang/Thua)
   - Cleaner spacing, consistent typography

6. **Remove breakdown-chart component call**
   - Remove `<x-oprs.breakdown-chart>` from profile.blade.php
   - All OPRS data now in unified `<x-oprs.score-card>`

## Todo List

- [ ] Merge OPRS components into unified score-card
- [ ] Update breakdown-chart.blade.php (remove or repurpose)
- [ ] Redesign card base CSS (consistent system)
- [ ] Redesign badges section (no emoji, styled icons)
- [ ] Redesign Elo history (compact timeline)
- [ ] Redesign match history (cleaner layout)
- [ ] Remove breakdown-chart component call from profile

## Success Criteria

- Single unified OPRS card (no redundancy)
- Consistent card design across all sections
- No emoji in badge icons
- Clean, scannable layout

## Risk Assessment

- **Medium risk:** Modifying shared OPRS components - check if used elsewhere
- Verify `breakdown-chart.blade.php` is not used on other pages before removing
