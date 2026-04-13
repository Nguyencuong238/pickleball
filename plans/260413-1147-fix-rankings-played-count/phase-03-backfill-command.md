# Phase 3 — Backfill command

**Priority:** High
**Status:** pending
**Effort:** S
**Depends on:** phase-01

## Goal

Fix historical dirty data across all tournaments. Idempotent, safe to re-run.

## Files

**Create:**
- `app/Console/Commands/RecalculateTournamentStandings.php`

## Command Spec

Signature:
```
php artisan tournament:recalculate-standings {--tournament=} {--group=} {--dry-run}
```

Behavior:
- No args → iterate all groups across all tournaments.
- `--tournament=ID` → only groups of that tournament.
- `--group=ID` → only that single group.
- `--dry-run` → compute but do not save (log planned diffs).

Logic:
1. Resolve target groups.
2. For each group: snapshot current standings (id → matches_played/won/lost/points).
3. Call `TournamentStandingService::recalculateGroupStandings($groupId)`.
4. Reload, diff vs snapshot, log rows that changed (info level).
5. Also call `recalculateTournamentAthleteStats` for each distinct athlete in the group.
6. Output summary: groups processed, rows changed, errors.

Wrap each group in its own try/catch so one bad group doesn't halt the run.

## Todo

- [ ] Create command class
- [ ] Register in `app/Console/Kernel.php` if not auto-discovered (Laravel 10: auto)
- [ ] Dry-run test on tournament 237
- [ ] Real run on tournament 237, verify Bảng B athletes all = 4 matches_played
- [ ] Full run across all tournaments (after review of dry-run output)

## Success Criteria

- Dry-run reports specific row changes for tournament 237.
- After real run, DB query on group 34 shows `matches_played = 4` for all 5 rows.
- Re-running command is a no-op (diff = 0).

## Risks

- Running during live match updates could race. Mitigation: run during low-traffic window or lock `groups` rows. For now, document + run off-hours.
- Large tournaments count: check query count. Service already transactional per group.
