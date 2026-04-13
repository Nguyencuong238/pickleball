# Phase 4 — Tests + manual verification

**Priority:** High
**Status:** pending
**Effort:** S
**Depends on:** phase-01, phase-02, phase-03

## Goal

Prevent regression. Prove fix on real tournament 237.

## Files

**Create:**
- `tests/Feature/Tournament/RankingsIdempotencyTest.php`

## Test Cases

### 1. Double-submit same match → idempotent
- Setup: tournament + 1 group + 3 pairs + full round-robin matches.
- Submit score for match A once → assert standings correct.
- Submit same score for match A again → assert standings **unchanged**.

### 2. Score update (change result)
- Submit match A as pair1 wins → assert.
- Submit match A as pair2 wins (edit) → assert new standings match the new result, not accumulated with old.

### 3. Recompute from dirty state
- Manually inflate a `group_standings` row (`matches_played = 99`).
- Call `recalculateGroupStandings` → assert row restored to real count.

### 4. `matches_drawn` preserved
- Submit a drawn match → assert `matches_drawn += 1`, `points` unchanged (or current behavior preserved).

## Manual Verification

1. Load `/tournament-manage/giai-pickleball-chao-mung-304-15-tranh-cup-five-star-lan-ii/rankings`.
2. Bảng B: all rows show `P = 4`.
3. Bảng A, C: no row has `P > 4` (max pairs per group).
4. Edit a completed match's score in admin UI, reload rankings → counts sane.

## Todo

- [ ] Write feature test with factory setup
- [ ] `php artisan test --filter=RankingsIdempotency`
- [ ] Manual smoke on tournament 237
- [ ] Screenshot before/after for PR description

## Success Criteria

- All 4 test cases pass.
- Manual verification confirms UI shows corrected values.
- No other tests broken.

## Notes

Existing test coverage for rankings may be zero. If no factory exists for `Tournament`/`Group`/`MatchModel`, create minimal ones inline in the test (or use direct `create` with hardcoded data) — keep scope tight.
