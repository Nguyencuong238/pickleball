# Phase 1: Redesign Hero + Stats Section

## Context
- [Research Report](../reports/researcher-260411-0104-profile-page-redesign.md)
- Current: `resources/views/front/ocr/profile.blade.php` (lines 307-383)

## Overview
- **Priority:** P1
- **Status:** Complete
- **Effort:** 2h

Redesign the hero section from a flat dark gradient to a modern, card-style athlete profile header. OPRS score becomes the primary visual element (48px+). Stats reorganized into compact pills.

## Key Insights
- Current hero crams 7 stats inline - causes overflow on mobile
- OPRS should be the hero number (like DUPR rating display)
- Brand colors: `#006646` (primary green), `#52c98c` (secondary green)
- Font: Inter (already loaded in layout)

## Requirements

### Functional
- Display: avatar, name, rank badge, verification status, OPRS (primary), global rank, Elo, match stats
- Authenticated owner sees action buttons (community link, verification)
- Non-owner sees "Thach Dau" button

### Non-functional
- Hero must look good in screenshot (shareable)
- Mobile-first: single column on <768px
- Load time: no additional JS, pure CSS

## Architecture

```
┌──────────────────────────────────────────────┐
│  Hero Section (gradient bg)                  │
│  ┌────────────────────────────────────────┐  │
│  │  [Avatar 100px]  Name                  │  │
│  │                  Rank Badge | Verified  │  │
│  │                                        │  │
│  │         ┌─────────────────┐            │  │
│  │         │    825 OPRS     │            │  │  <- Primary stat, 48px
│  │         │ #1 Toan Quoc    │            │  │
│  │         └─────────────────┘            │  │
│  │                                        │  │
│  │  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ │  │
│  │  │ 1178 │ │  1   │ │  1   │ │  0   │ │  │  <- 4 stat pills
│  │  │ Elo  │ │ Tran │ │Thang │ │ Thua │ │  │
│  │  └──────┘ └──────┘ └──────┘ └──────┘ │  │
│  │                                        │  │
│  │  [Chia Se]  [Thach Dau]               │  │  <- Action buttons
│  └────────────────────────────────────────┘  │
└──────────────────────────────────────────────┘
```

## Related Code Files

| File | Action | Description |
|------|--------|-------------|
| `resources/views/front/ocr/profile.blade.php` | Modify | Rewrite hero HTML+CSS (lines 4-383) |

## Implementation Steps

1. **Replace hero CSS** (lines 4-303)
   - New gradient: `linear-gradient(135deg, #006646 0%, #004d33 50%, #1a1a2e 100%)`
   - Centered layout with max-width container
   - Profile card with subtle glassmorphism: `background: rgba(255,255,255,0.08); backdrop-filter: blur(10px)`

2. **Restructure hero HTML** (lines 307-383)
   - Avatar: keep letter fallback, add subtle glow border
   - Name: 1.75rem, font-weight 700
   - Rank badge + verification: inline-flex row below name
   - OPRS primary display: centered, 3rem font, white, with "OPRS" label below
   - Global rank: `#N Toan Quoc` below OPRS
   - Stat pills: 4-item flex row (Elo, Tran Dau, Thang, Thua) - remove Win Rate (calculated, not needed)
   - Action buttons: "Chia Se" (share) + "Thach Dau" (challenge) / "Cong Dong" (community)

3. **Avatar enhancement**
   - Green gradient border: `border: 3px solid rgba(82, 201, 140, 0.6)`
   - Box shadow glow: `box-shadow: 0 0 20px rgba(82, 201, 140, 0.3)`

4. **Stat pills styling**
   - Background: `rgba(255,255,255,0.1)`
   - Border-radius: 12px
   - Padding: 0.75rem 1.25rem
   - Value: 1.5rem bold white
   - Label: 0.7rem uppercase, opacity 0.7

## Todo List

- [ ] Replace hero CSS with new modern styles
- [ ] Restructure hero HTML with centered OPRS display
- [ ] Style avatar with glow border
- [ ] Create stat pills layout (4 items)
- [ ] Add action buttons row (share + challenge/community)
- [ ] Test mobile responsiveness

## Success Criteria

- Hero section looks premium/professional
- OPRS score immediately visible as primary stat
- Clean on mobile (320px+) and desktop
- No JS required for hero section

## Risk Assessment

- **Low risk:** CSS-only changes, no backend modifications
- Existing Blade variables unchanged (`$user`, `$globalRank`, `$oprsBreakdown`)
