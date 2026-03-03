---
title: "Club Activity Matches Tab"
description: "Add lightweight match system (rounds, scoring, standings) to non-competition club activities"
status: complete
priority: P2
effort: 10h
branch: main
tags: [club, activity, matches, standings, blade, laravel]
created: 2026-03-03
---

# Club Activity Matches Tab

Lightweight casual match system for `one_off` and `recurring` activities. Competition activities keep their existing bracket system untouched.

## Phases

| # | Phase | Status | Effort |
|---|-------|--------|--------|
| 1 | [Schema + Models + Tab UI](./phase-01-schema-models-tab-ui.md) | complete | 2.5h |
| 2 | [Generate Matches](./phase-02-generate-matches.md) | complete | 3h |
| 3 | [Scoring + Standings](./phase-03-scoring-standings.md) | complete | 2.5h |
| 4 | [Custom Match](./phase-04-custom-match.md) | complete | 2h |

## Key Decisions
- New tables: `club_activity_match_rounds`, `club_activity_matches`, `club_activity_match_standings`
- Tab shown for `type !== 'competition'`, using existing hash-based tab system
- AJAX endpoints mirror `ClubCompetitionController` pattern
- Route prefix: `{activity}/matches` under existing `clubs.activities.` group
- No new JS framework — vanilla JS only

## Dependencies
- Phase 2 requires Phase 1 migrations + models
- Phase 3 requires Phase 2 match data
- Phase 4 can run parallel to Phase 3

## Validation Log

### Session 1 — 2026-03-03
**Trigger:** Initial plan validation before implementation
**Questions asked:** 7

#### Questions & Answers

1. **[Schema]** Plan uses 4 separate FK columns (player1_id..player4_id) on matches table. Alternative: junction table or JSON. Which approach?
   - Options: 4 FK columns | Junction table (match_players) | JSON column
   - **Answer:** 4 FK columns
   - **Rationale:** Simple, fast queries, no extra joins. Pickleball is always 1v1 or 2v2 — no flexibility needed.

2. **[MVP Scope]** Plan includes 3 match formats for MVP. Which formats needed now?
   - Options: All 3 formats | Rotating doubles only | Rotating doubles + singles
   - **Answer:** All 3 formats (rotating_doubles + fixed_doubles + singles_rr)
   - **Rationale:** Full feature parity desired. All 3 algorithms share polygon rotation base — incremental effort is small.

3. **[Permissions]** Who should be allowed to enter match scores?
   - Options: Management only | Any participant | Management + designated scorer
   - **Answer:** Management only
   - **Rationale:** Simpler, avoids disputes. Standard for club meetups where host manages the session.

4. **[Regeneration]** When re-generating matches, plan deletes ALL existing rounds+scores. Safeguard?
   - Options: Confirm dialog only | Block if scores exist | Generate additional rounds only
   - **Answer:** Confirm dialog only
   - **Rationale:** Simple JS confirm is sufficient. Users understand the destructive action.

5. **[Scoring]** Pickleball standard is play to 11, win by 2. Should score entry enforce rules?
   - Options: Free-form integers | Enforce pickleball rules | Preset options
   - **Answer:** Free-form integers (0-99)
   - **Rationale:** Flexible for timed games, house rules, different point systems.

6. **[Recurring]** Should Matches tab appear on recurring activity templates or only instances?
   - Options: All non-competition | Only instances, not templates | Only if activity has date
   - **Answer:** Only instances, not templates (defaulted to recommended)
   - **Rationale:** Parent templates don't have actual players. Only scheduled instances need match generation.

7. **[Finalization]** After all scores entered, should there be a 'session complete' action?
   - Options: No finalization needed | Session complete button | Auto-complete
   - **Answer:** No finalization needed
   - **Custom input:** "ban quyet dinh giup toi" — delegated to architect
   - **Rationale:** KISS principle. Each round completes independently. Standings always reflect latest data.

#### Confirmed Decisions
- Schema: 4 FK columns — simple and sufficient for pickleball
- Formats: all 3 (singles_rr, rotating_doubles, fixed_doubles)
- Scoring: management-only, free-form integers 0-99
- Regeneration: JS confirm dialog, then full delete + regenerate
- Recurring: tab hidden on parent/template activities (where `parent_activity_id IS NULL` and `type = 'recurring'`)
- Finalization: none — round-level completion is enough

#### Action Items
- [ ] Phase 1: Add condition to hide tab on recurring templates (`$activity->parent_activity_id !== null || $activity->type !== 'recurring'`)

#### Impact on Phases
- Phase 1: Tab visibility condition needs refinement — exclude recurring templates (parent activities)
- Phase 2: No changes — all 3 formats confirmed
- Phase 3: No changes — management-only scoring, free-form integers confirmed
- Phase 4: No changes
