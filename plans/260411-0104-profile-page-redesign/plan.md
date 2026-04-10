---
title: "Redesign OCR Player Profile Page"
description: "Redesign shareable player profile at /ocr/profile/{slug} for professional look and social sharing"
status: complete
priority: P1
effort: 6h
branch: main
tags: [frontend, ui-ux, feature]
created: 2026-04-11
---

# Redesign OCR Player Profile Page

## Overview

Redesign `/ocr/profile/{slug}` to look professional when shared on Zalo/iMessage/Facebook. Current page is functional but looks like a dashboard, not a shareable athlete card.

## Research

- [Research Report](../reports/researcher-260411-0104-profile-page-redesign.md)

## Phases

| # | Phase | Status | Effort | Link |
|---|-------|--------|--------|------|
| 1 | Redesign Hero + Stats Section | Complete | 2h | [phase-01](./phase-01-redesign-hero-stats.md) |
| 2 | Redesign Content Cards | Complete | 1.5h | [phase-02-redesign-content-cards.md](./phase-02-redesign-content-cards.md) |
| 3 | Add OG Meta Tags + Share Button | Complete | 1.5h | [phase-03](./phase-03-og-meta-share.md) |
| 4 | Mobile Responsive Polish | Complete | 1h | [phase-04](./phase-04-mobile-responsive.md) |

## Dependencies

- Existing `OprsService`, `BadgeService`, `EloService` - no changes needed
- Layout `front.blade.php` already supports `@section('seo')` for OG tags
- OPRS components (`score-card.blade.php`, `breakdown-chart.blade.php`) will be redesigned inline

## Key Decisions

- Keep Blade templates (no Vue/React migration)
- Use existing brand colors: `--primary-color: #006646`, `--secondary-color: #52c98c`
- Inline CSS in blade (consistent with current pattern)
- No OG image generation in Phase 1 (use static branded template)

## Validation Log

### Session 1 -- 2026-04-11
**Trigger:** Initial plan creation validation
**Questions asked:** 4

#### Questions & Answers

1. **[Architecture]** Hero layout: avatar + name position on desktop - centered or left-aligned?
   - Options: Centered (Recommended) | Left-aligned (current style)
   - **Answer:** Centered
   - **Rationale:** Centered layout matches FIFA/DUPR card aesthetic, better for shareable screenshots

2. **[Tradeoff]** OG image khi share link: dung anh chung (static) hay tao anh rieng cho tung nguoi choi (dynamic)?
   - Options: Static image (Recommended) | Dynamic per-player image
   - **Answer:** Static image
   - **Rationale:** Simpler implementation, title+description still show player-specific data. Dynamic can be added later.

3. **[Scope]** Win Rate (Ty Le Thang) co giu lai trong stat pills khong?
   - Options: Bo Win Rate (Recommended) | Giu Win Rate 5 pills | Thay Elo bang Win Rate
   - **Answer:** Giu plan (Bo Win Rate) - 4 pills: Elo, Tran Dau, Thang, Thua
   - **Rationale:** Cleaner layout, Win Rate is derivable from Thang/Tran

4. **[Architecture]** breakdown-chart.blade.php - sau khi merge vao score-card thi xu ly sao?
   - Options: Xoa file (Recommended) | Giu lai nhung khong dung
   - **Answer:** Xoa file
   - **Rationale:** Keep codebase clean. All OPRS data displayed in unified score-card.

#### Confirmed Decisions
- Hero: Centered layout with avatar above name above OPRS
- OG: Static branded image (no per-player generation)
- Stats: 4 pills (Elo, Tran Dau, Thang, Thua) - no Win Rate
- Component: Delete breakdown-chart.blade.php after merge

#### Action Items
- [ ] Verify breakdown-chart.blade.php not used on other pages before deletion

#### Impact on Phases
- Phase 1: Confirmed centered layout (no change needed, already planned)
- Phase 2: Confirm deletion of breakdown-chart.blade.php (update action from "Remove" to "Delete")
