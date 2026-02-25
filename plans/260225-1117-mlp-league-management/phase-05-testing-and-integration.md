# Phase 5: Testing & Integration

## Context Links
- [Plan Overview](./plan.md)
- [Code Standards - Testing](../../docs/code-standards.md#testing-standards)
- All phase files in this plan
- Reference: `tests/` directory structure

## Overview
- **Priority**: P2
- **Status**: pending
- **Description**: Write unit tests for services, feature tests for controllers, and verify integration with existing system

## Key Insights
- Existing test structure: `tests/Feature/`, `tests/Unit/`
- Test naming: `snake_case` method names with `/** @test */` annotation
- Use `RefreshDatabase` trait for DB tests
- Factory pattern for test data: `User::factory()->create()`
- Need to create League factory for testing

## Requirements

### Functional
- Unit tests for all 3 service classes
- Feature tests for league CRUD, team management, match scoring
- Integration test: verify tournament system unaffected

### Non-functional
- All tests pass independently and in suite
- No mocking of core logic (test real behavior)
- Factories for test data generation

## Related Code Files

| File | Action |
|------|--------|
| `database/factories/LeagueFactory.php` | create |
| `database/factories/LeagueTeamFactory.php` | create |
| `tests/Unit/Services/LeagueServiceTest.php` | create |
| `tests/Unit/Services/LeagueScheduleServiceTest.php` | create |
| `tests/Unit/Services/LeagueStandingsServiceTest.php` | create |
| `tests/Feature/League/LeagueCrudTest.php` | create |
| `tests/Feature/League/LeagueTeamTest.php` | create |
| `tests/Feature/League/LeagueMatchTest.php` | create |
| `tests/Feature/League/LeagueApiTest.php` | create |

## Implementation Steps

### Factories

1. Create `database/factories/LeagueFactory.php`:
   ```php
   League::factory()->definition():
   - user_id => User::factory()
   - name => fake()->words(3, true) . ' League'
   - slug => Str::slug(name)
   - status => 'draft'
   - config => ['match_format' => ['WD','MD','MXD'], 'max_teams' => 8, 'points_for_win' => 3, 'points_for_loss' => 0]
   - start_date => now()->addDays(7)
   - end_date => now()->addDays(60)
   ```

2. Create `database/factories/LeagueTeamFactory.php`:
   ```php
   LeagueTeam::factory()->definition():
   - league_id => League::factory()
   - name => fake()->company() . ' Team'
   - status => 'active'
   - captain_user_id => User::factory()
   ```

### Unit Tests - LeagueServiceTest (~80 lines)

3. Create `tests/Unit/Services/LeagueServiceTest.php`:
   - `it_creates_league_with_defaults`: verify slug, default config values
   - `it_generates_unique_slug`: create two leagues with same name
   - `it_validates_status_transitions`: draft->registration OK, active->draft FAIL
   - `it_prevents_deletion_of_active_league`: status=active, expect exception
   - `it_enforces_max_teams_limit`: add teams up to max, expect error on next
   - `it_prevents_duplicate_player_in_league`: same user on two teams

### Unit Tests - LeagueScheduleServiceTest (~80 lines)

4. Create `tests/Unit/Services/LeagueScheduleServiceTest.php`:
   - `it_generates_correct_rounds_for_4_teams`: expect 3 rounds, 6 matches total
   - `it_generates_correct_rounds_for_5_teams`: expect 5 rounds (odd), each team plays 4 matches
   - `it_ensures_each_team_plays_every_other_team`: verify all pairs exist
   - `it_creates_games_per_match_from_config`: 3 game types = 3 games per match
   - `it_requires_minimum_2_teams`: expect exception with 1 team
   - `it_clears_existing_schedule`: generate, clear, verify rounds deleted

### Unit Tests - LeagueStandingsServiceTest (~80 lines)

5. Create `tests/Unit/Services/LeagueStandingsServiceTest.php`:
   - `it_initializes_standings_for_all_teams`: verify one standing per team, all zeros
   - `it_recalculates_after_match_result`: submit score, verify standings updated
   - `it_determines_correct_winner`: home wins, away wins, draw scenarios
   - `it_calculates_points_from_config`: custom points_for_win=2, verify
   - `it_ranks_by_points_then_game_diff`: teams with same points, different game diff
   - `it_handles_game_by_game_scoring`: save individual game scores, verify match winner

### Feature Tests - LeagueCrudTest (~100 lines)

6. Create `tests/Feature/League/LeagueCrudTest.php`:
   - `it_displays_league_index_for_authenticated_user`
   - `it_creates_league_with_valid_data`
   - `it_validates_required_fields_on_create`
   - `it_shows_league_dashboard_to_owner`
   - `it_returns_403_for_non_owner`
   - `it_updates_league`
   - `it_deletes_draft_league`
   - `it_changes_league_status`

### Feature Tests - LeagueTeamTest (~70 lines)

7. Create `tests/Feature/League/LeagueTeamTest.php`:
   - `it_adds_team_to_league`
   - `it_removes_team_from_draft_league`
   - `it_adds_player_to_team`
   - `it_prevents_duplicate_player_across_teams`
   - `it_removes_player_from_team`

### Feature Tests - LeagueMatchTest (~70 lines)

8. Create `tests/Feature/League/LeagueMatchTest.php`:
   - `it_displays_match_list_for_league`
   - `it_updates_match_score`
   - `it_updates_game_score_and_determines_winner`
   - `it_recalculates_standings_after_score_update`

<!-- Updated: Validation Session 1 - Add API endpoint tests -->

### Feature Tests - LeagueApiTest (~60 lines)

9. Create `tests/Feature/League/LeagueApiTest.php`:
   - `it_returns_leagues_for_authenticated_user`: GET /api/leagues with Sanctum token
   - `it_returns_401_for_unauthenticated_league_list`: GET /api/leagues without token
   - `it_returns_league_details_publicly`: GET /api/leagues/{slug} no auth
   - `it_returns_standings_publicly`: GET /api/leagues/{slug}/standings no auth
   - `it_returns_schedule_publicly`: GET /api/leagues/{slug}/schedule no auth

### Integration Verification

9. Run existing tournament tests to verify no regressions:
   ```bash
   php artisan test --filter=Tournament
   ```

10. Verify migration compatibility:
    ```bash
    php artisan migrate:fresh --seed
    php artisan test
    ```

11. Add sidebar link and verify Home Yard navigation:
    - Leagues link appears in sidebar
    - Active state highlights correctly
    - No visual regressions on tournament pages

## Todo List
- [ ] Create LeagueFactory
- [ ] Create LeagueTeamFactory
- [ ] Write LeagueServiceTest (6 tests)
- [ ] Write LeagueScheduleServiceTest (6 tests)
- [ ] Write LeagueStandingsServiceTest (6 tests)
- [ ] Write LeagueCrudTest (8 tests)
- [ ] Write LeagueTeamTest (5 tests)
- [ ] Write LeagueMatchTest (4 tests)
- [ ] Run full test suite, verify 0 failures
- [ ] Verify tournament system unaffected
- [ ] Verify migrations work with fresh + seed

## Success Criteria
- All 40 tests pass
- No existing tests broken
- `php artisan migrate:fresh --seed` succeeds
- Full test suite: `php artisan test` passes
- Sidebar link visible and functional

## Risk Assessment
- **Test database state**: Use `RefreshDatabase` trait to ensure clean state between tests
- **Factory dependencies**: LeagueTeam factory depends on League factory; ensure correct wiring
- **Existing test conflicts**: New migrations may break seeder. Verify seeder still works.

## Security Considerations
- Feature tests verify 403 responses for unauthorized access
- Test that non-owners cannot modify leagues/teams/matches
- Verify CSRF protection active on form submissions
