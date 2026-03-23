# Bracket Match Editor

## Summary
Add edit button per bracket match. Click opens modal to reassign athletes (selecting from previous-round qualifiers) and edit match properties (court, time, notes, best_of).

## Current State
- Bracket tree: `_bracket-tree.blade.php` + `_bracket-match.blade.php`
- JS modules: `bracket-manager.js` (Alpine), `bracket-swap-editor.js` (slot swap), `bracket-score-entry.js` (score modal), `bracket-data-fetcher.js`
- Backend: `TournamentBracketController` (index, getData, generate, swap)
- Service: `KnockoutBracketQuery` (getBracketData, swapAthletes, formatMatch)
- Data format: rounds[] > matches[] > {id, athlete1, athlete2, status, winner_id, scores...}

## Approach
Extend existing edit mode. Add "edit match" button per match card (visible in editMode only). Opens a modal with:
1. Athlete1/Athlete2 dropdowns populated from eligible athletes (previous round qualifiers)
2. Match properties: match_time, best_of, notes (no court field)
3. Save via new backend endpoint `bracket.updateMatch`
4. Only scheduled/bye matches editable. Completed matches with scores must have scores removed first.
5. Cascade warning: when changing athlete in earlier round, warn about affected downstream matches before clearing.

## Athlete Selection Logic
- **Round N match**: can pick any athlete who qualified from round N-1 (or earlier)
- **First round (quarterfinal from groups)**: pick from `tournament_athletes` where `is_advanced = true` for that category
- Backend computes eligible list per round to prevent invalid selections

## Phases

| # | Phase | Status | Priority |
|---|-------|--------|----------|
| 1 | [Backend API](phase-01-backend-api.md) | pending | P0 |
| 2 | [Frontend Modal](phase-02-frontend-modal.md) | pending | P0 |
| 3 | [JS Logic](phase-03-js-match-editor.md) | pending | P0 |

## Dependencies
- Existing bracket CRUD working (confirmed)
- Edit mode toggle already exists

## Validation Log

### Session 1 — 2026-03-16
**Trigger:** Initial plan validation before implementation
**Questions asked:** 4

#### Questions & Answers

1. **[Scope]** Khi chinh sua tran dau da completed (co ti so), co cho phep thay doi VDV khong?
   - Options: Chi scheduled/bye | Cho phep tat ca tran | Completed nhung canh bao
   - **Answer:** Chi scheduled/bye
   - **Rationale:** Keeps data integrity. Matches with scores are final. Admin must clear scores first via existing score UI before editing athletes.

2. **[Cascade]** Khi thay doi VDV o vong truoc, cac tran vong sau da co VDV do thi xu ly the nao?
   - Options: Cascade clear | Khong cascade | Canh bao va hoi
   - **Answer:** Canh bao va hoi
   - **Rationale:** Shows affected downstream matches count, asks confirm before cascade clearing. Prevents accidental data loss while maintaining consistency.

3. **[UI]** Nut 'Chinh sua tran dau' chi hien khi bat edit mode hay luon hien?
   - Options: Chi trong edit mode | Luon hien thi
   - **Answer:** Chi trong edit mode
   - **Rationale:** Clean UI. Must toggle edit mode first. Consistent with existing swap behavior.

4. **[Scope]** Co can them truong court vao modal chinh sua khong?
   - Options: Bo court, giu don gian | Them court dropdown
   - **Answer:** Bo court, giu don gian
   - **Rationale:** Court managed separately. Reduces scope and avoids extra API call for court list.

#### Confirmed Decisions
- Edit scope: scheduled/bye only — avoid accidental score/winner corruption
- Cascade: warn + confirm before clearing downstream — balance safety and usability
- UI trigger: edit mode only — consistent with swap UX
- No court field — YAGNI, simpler modal

#### Action Items
- [ ] Remove `court_id` from backend validation and frontend modal
- [ ] Add cascade detection + warning logic to backend `updateMatch`
- [ ] Add cascade confirm dialog to frontend JS before save

#### Impact on Phases
- Phase 1: Remove court_id from validation/update. Add cascade detection method that counts affected downstream matches. Return affected_count in response for frontend to show warning.
- Phase 2: Remove court dropdown from modal. Add cascade warning text area.
- Phase 3: Add cascade confirm logic in saveMatchEdit() — if response returns affected_count > 0, show confirm dialog before final save.
