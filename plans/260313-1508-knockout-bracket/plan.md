---
title: "Single Elimination Knockout Bracket"
description: "Auto-generate and manage single elimination brackets from group stage results per tournament category"
status: completed
priority: P1
effort: 16h
branch: main
tags: [tournament, bracket, knockout, single-elimination]
created: 2026-03-13
---

# Single Elimination Knockout Bracket

## Summary

Add per-category single elimination brackets that auto-generate from group stage advanced athletes, with seeding, bye handling, third-place match option, winner auto-advancement, and bracket editing.

## Architecture Overview

- **Service**: `KnockoutBracketService` -- bracket generation, seeding, advancement, swap
- **Controller**: `TournamentBracketController` -- CRUD endpoints for bracket ops
- **Migration**: `enable_third_place` boolean on tournaments table
- **Views**: Bracket display (Blade + CSS + Alpine.js), score entry, bracket editing
- **Integration**: Hook into `MatchScoreTrait::processScoreUpdate()` for auto-advance

## Phases

| # | Phase | Status | Effort | File |
|---|-------|--------|--------|------|
| 1 | Migration + KnockoutBracketService | completed | 3h | [phase-01](phase-01-backend-service.md) |
| 2 | Controller + Routes | completed | 2h | [phase-02](phase-02-controller-routes.md) |
| 3 | Bracket Display UI | completed | 4h | [phase-03](phase-03-bracket-display-ui.md) |
| 4 | Score Entry + Auto-Advancement | completed | 3h | [phase-04](phase-04-score-auto-advance.md) |
| 5 | Bracket Editing UI | completed | 2h | [phase-05](phase-05-bracket-editing.md) |
| 6 | Auto-Trigger Integration | completed | 2h | [phase-06](phase-06-auto-trigger.md) |

## Key Decisions

1. Reuse `matches` table -- knockout matches are MatchModel rows linked via `next_match_id`
2. Reuse `rounds` table with round_type = knockout/quarterfinal/semifinal/final/bronze
3. `bracket_position` stores integer for bracket tree position (1=final, 2-3=semi, 4-7=quarter, etc)
4. Flexbox CSS bracket (no JS lib), Alpine.js for interactivity
5. Click-to-swap for bracket editing (simpler than drag-drop, mobile-friendly)
6. Vietnamese UI text with diacritics

## Dependencies

- Existing: MatchModel, Round, Group, GroupStanding, TournamentStandingService
- Existing: MatchScoreTrait (hook point for auto-advance)
- Existing: tournament-manage routes prefix, dashboard layout

## Validation Log

### Session 1 — 2026-03-13
**Trigger:** Initial plan creation validation
**Questions asked:** 4

#### Questions & Answers

1. **[Architecture]** Plan gia dinh bracket_position dung heap-style integer (1=final, 2-3=semi, 4-7=quarter). OK?
   - Options: Heap integer (Recommended) | String format R{round}M{match}
   - **Answer:** Heap integer
   - **Rationale:** Simple tree traversal via parent=floor(N/2). Already used in plan.

2. **[Scope]** Bracket co can ho tro doubles (cap doi) khong?
   - Options: Ho tro ca singles & doubles (Recommended) | Chi singles truoc
   - **Answer:** Ho tro ca singles & doubles
   - **Rationale:** TournamentAthlete already has partner_id. Bracket uses athlete_id, doubles pair linked via partner. UI needs display both names.

3. **[UX]** Khi admin chua bat auto_bracket_generation, vong bang xong thi can notification khong?
   - Options: Badge tren sidebar + toast (Recommended) | Chi hien trong tab Bracket | Toast only
   - **Answer:** Badge tren sidebar + toast
   - **Rationale:** Admin needs clear signal that bracket is ready. Badge on sidebar + toast ensures visibility.

4. **[Standards]** Tat ca Vietnamese text trong code phai co dau dung khong?
   - Options: Co dau tat ca (Recommended) | Comments khong dau, user-facing co dau
   - **Answer:** Co dau tat ca
   - **Rationale:** Consistency. All Vietnamese text including code comments must have proper diacritics.

#### Confirmed Decisions
- bracket_position: heap-style integer encoding
- Doubles support: yes, from Phase 1
- Notification: sidebar badge + toast when groups complete
- Vietnamese: full diacritics everywhere

#### Action Items
- [ ] Phase 1: Add doubles pair display logic in BracketSeedingHelper
- [ ] Phase 1: Fix all Vietnamese text to have proper diacritics
- [ ] Phase 2: Add sidebar badge for bracket readiness
- [ ] Phase 6: Add toast notification when groups complete

#### Impact on Phases
- Phase 1: collectSeededAthletes() must handle doubles pairs (partner_id). getRoundName() must use diacritics.
- Phase 2: Sidebar partial needs badge indicator for bracket readiness
- Phase 3: Match cards must display doubles pair names (athlete + partner)
- Phase 6: Add toastr notification in MatchScoreTrait when category completes
