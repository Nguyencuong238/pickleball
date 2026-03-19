---
title: "Tournament Management Complete Rewrite"
description: "Rewrite tournament flow - decompose God controller, new dashboard UI with Alpine.js, service layer extraction"
status: complete
priority: P1
effort: 24h
branch: main
tags: [refactor, frontend, backend, tournament]
created: 2026-03-12
---

# Tournament Management Complete Rewrite

## Overview

Complete rewrite of tournament management: decompose 5,173-line God controller into 5 focused controllers + 3 services. New dashboard UI with sidebar navigation, Alpine.js reactivity, mobile-first CSS. Covers core tournament operation flow: CRUD -> Athletes -> Draw -> Matches -> Rankings.

## Scope

**In:** CRUD, categories, athletes, draw/seeding, matches, scoring (set entry), rankings/standings
**Out:** Courts/bookings, referee screens, exports, OCR scoring

## Key Decisions

- Blade + Alpine.js (new addition) + custom CSS (no Tailwind)
- jQuery/Select2/Toastr kept for compatibility
- Existing DB schema & models unchanged (minor tweaks OK)
- No unit tests
- Controllers in `Front/Tournament/` namespace
- Services in `app/Services/Tournament/`

## Phases

| # | Phase | Status | Effort | Link |
|---|-------|--------|--------|------|
| 1 | Setup Alpine.js + Routes + Controller Stubs | Complete | 3h | [phase-01](./phase-01-setup-alpine-routes-controllers.md) |
| 2 | Service Layer Extraction | Complete | 5h | [phase-02-extract-service-layer.md](./phase-02-extract-service-layer.md) |
| 3 | Tournament Dashboard Layout + CRUD | Complete | 4h | [phase-03](./phase-03-dashboard-layout-crud.md) |
| 4 | Athletes Management UI | Complete | 3h | [phase-04](./phase-04-athletes-management.md) |
| 5 | Draw & Seeding UI | Complete | 4h | [phase-05](./phase-05-draw-seeding.md) |
| 6 | Matches & Scoring UI | Complete | 3h | [phase-06](./phase-06-matches-scoring.md) |
| 7 | Rankings & Standings UI | Complete | 2h | [phase-07](./phase-07-rankings-standings.md) |

## Dependencies

- Phase 1 (setup) unblocks all others
- Phase 2 (services) unblocks phases 5, 6, 7
- Phases 3-7 can partially overlap after phase 2
- Existing referee/booking/API controllers untouched

## Research

- [UX Research Report](../reports/researcher-260312-2011-tournament-ux-research.md)

## Validation Log

### Session 1 — 2026-03-12
**Trigger:** Initial plan creation validation
**Questions asked:** 7

#### Questions & Answers

1. **[Architecture]** The plan uses /tournament-manage prefix for new routes to avoid conflict with existing /tournaments. How do you want to handle the transition?
   - Options: New prefix permanently | New prefix then migrate | Replace old routes now
   - **Answer:** New prefix permanently
   - **Rationale:** `/tournament-manage` stays as permanent URL. Old `/tournaments` routes remain for public/API.

2. **[Scope]** The plan keeps old HomeYardTournamentController intact during transition. When should the old code be removed?
   - Options: Remove after all 7 phases | Remove per phase | Keep both permanently
   - **Answer:** Keep old code until new code verified correct
   - **Custom input:** "keep old code until we make sure new code correct"
   - **Rationale:** Safety-first approach. Old code serves as reference and fallback.

3. **[Architecture]** Alpine.js will be added via CDN. Should new views use Alpine.js exclusively or mix Alpine + jQuery?
   - Options: Alpine.js only for new views | Alpine + jQuery mix | jQuery only
   - **Answer:** Alpine.js only for new views
   - **Rationale:** Clean separation. New tournament views = Alpine. Existing views = jQuery.

4. **[Architecture]** Dashboard navigation: separate Blade views or Alpine-managed tabs?
   - Options: Separate Blade views | Alpine tabs single page | Hybrid
   - **Answer:** Keep plan as-is (Hybrid: Alpine tabs within Blade pages)
   - **Rationale:** Each section = separate URL for deep-linking, Alpine handles interactivity within.

5. **[Scope]** How should category management work in the new UI?
   - Options: Inline in create/edit form | Separate tab | Keep current approach
   - **Answer:** Inline in create/edit form
   - **Rationale:** Categories managed as dynamic rows in tournament form via Alpine.

6. **[Architecture]** How to handle mobile drag-and-drop for seeding?
   - Options: Tap-to-move pattern | Add SortableJS library | Desktop-only drag
   - **Answer:** Add SortableJS library
   - **Rationale:** SortableJS (~8KB) provides reliable touch drag-and-drop across all devices.

7. **[Architecture]** AJAX method for new Alpine views?
   - Options: fetch() + CSRF meta tag | axios | Alpine $fetch plugin
   - **Answer:** fetch() + CSRF meta tag
   - **Rationale:** Lightweight, no extra dependency. CSRF token from meta tag in headers.

#### Confirmed Decisions
- Route prefix: `/tournament-manage` (permanent)
- Old code: Keep until verified, then cleanup
- JS: Alpine.js only in new views, fetch() for AJAX
- Navigation: Hybrid (separate URLs + Alpine interactivity)
- Categories: Inline in tournament form
- Mobile drag: SortableJS library
- AJAX: fetch() + CSRF meta tag

#### Action Items
- [ ] Add SortableJS CDN to homeyard layout (Phase 1)
- [ ] Add CSRF meta tag to homeyard layout head (Phase 1)
- [ ] Update Phase 5 draw to use SortableJS instead of HTML5 Drag API

#### Impact on Phases
- Phase 1: Add SortableJS CDN + CSRF meta tag to layout setup
- Phase 3: Confirm categories inline in create/edit form (already planned)
- Phase 5: Replace HTML5 Drag API with SortableJS for seeding
