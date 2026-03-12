# Plan: League Register Page - 3 Tab Layout

**Date**: 2026-03-12
**Status**: Complete
**Complexity**: Low

## Overview

Update `/leagues/{slug}/register` page to display 3 tabs:
1. **Thong tin & Dang ky** - Tournament info + registration form (existing content)
2. **Lich thi dau** - Match schedule (public read-only)
3. **Bang xep hang** - Standings (public read-only)

## Phases

| # | Phase | Status | File |
|---|-------|--------|------|
| 1 | Controller update + View tabs | Pending | [phase-01-implementation.md](./phase-01-implementation.md) |

## Key Decisions

- Reuse existing admin tab partials as reference, create simplified public versions (no edit buttons, no modals)
- Tab switching via URL hash + vanilla JS (consistent with existing league admin UI pattern)
- Eager-load relationships in controller to avoid N+1
- Single phase - straightforward UI task

## Files to Modify

- `app/Http/Controllers/Front/LeagueRegistrationController.php` - Eager-load rounds, matches, standings, teams
- `resources/views/front/leagues/register.blade.php` - Add tab navigation + tab content panels

## Validation Log

### Session 1 — 2026-03-12
**Trigger:** Initial plan validation
**Questions asked:** 3

#### Questions & Answers

1. **[UX]** Default active tab khi user vao page la tab nao?
   - Options: Thong tin & Dang ky | Lich thi dau | Tuy trang thai league
   - **Answer:** Thong tin & Dang ky
   - **Rationale:** Giu UX hien tai, user vao thay form dang ky truoc

2. **[Scope]** Tab Lich thi dau va Bang xep hang co hien thi khi chua co data khong?
   - Options: An tab khi chua co data | Luon hien ca 3 tab | An tab + badge count
   - **Answer:** Luon hien ca 3 tab
   - **Rationale:** Hien tat ca tab voi empty state message khi chua co data

3. **[Feature]** Co can hien thi chi tiet sub-game (MLP format) cho public view?
   - Options: Co, hien thi collapsible | Khong, chi hien tong diem
   - **Answer:** Co, hien thi collapsible
   - **Rationale:** Giong admin view nhung read-only

#### Confirmed Decisions
- Default tab: Thong tin & Dang ky (hash `#info`)
- Always show all 3 tabs with empty state when no data
- MLP sub-game details: collapsible read-only view

#### Action Items
- [x] Phase 1 already accounts for all decisions - no changes needed

#### Impact on Phases
- No phase changes required - plan already aligned with answers
