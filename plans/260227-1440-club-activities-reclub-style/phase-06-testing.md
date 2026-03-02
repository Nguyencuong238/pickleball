# Phase 6: Testing

## Context Links
- ClubActivityService: `app/Services/ClubActivityService.php`
- ClubCompetitionService: `app/Services/ClubCompetitionService.php`
- Command: `app/Console/Commands/GenerateRecurringMeets.php`
- Controllers: ClubActivityController, ClubActivityParticipantController, ClubCompetitionController

## Overview
- **Priority:** P2
- **Status:** complete
- Unit tests for services (RSVP, competition)
- Feature tests for controller endpoints
- Command test for recurring generation
- 4 factories created + 5 test files with 25 tests all passing

## Related Code Files

### Files to CREATE:
- `tests/Feature/ClubActivityRsvpTest.php`
- `tests/Feature/ClubCompetitionTest.php`
- `tests/Feature/GenerateRecurringMeetsTest.php`
- `tests/Unit/ClubActivityServiceTest.php`
- `tests/Unit/ClubCompetitionServiceTest.php`

## Test Cases

### ClubActivityServiceTest (Unit)
1. `test_rsvp_confirms_when_spots_available` -- user joins, gets confirmed
2. `test_rsvp_waitlists_when_full` -- activity full, user goes to waitlist
3. `test_cancel_rsvp_promotes_waitlisted` -- cancel confirmed user, first waitlisted promoted
4. `test_rsvp_rejects_wrong_skill_level` -- user below min or above max skill level
5. `test_rsvp_prevents_duplicate` -- already-joined user gets error
6. `test_create_recurring_instance` -- template creates child with correct date/fields

### ClubCompetitionServiceTest (Unit)
7. `test_generate_round_robin_creates_correct_matches` -- N teams = N-1 rounds
8. `test_generate_round_robin_handles_odd_teams` -- bye handling
9. `test_save_match_score_updates_standings` -- score entry triggers standings recalc
10. `test_recalculate_standings_correct_ranking` -- points/rank computed correctly
11. `test_initialize_standings_creates_rows` -- standing per team

### ClubActivityRsvpTest (Feature)
12. `test_member_can_rsvp_to_activity` -- POST rsvp returns 200
13. `test_non_member_cannot_rsvp` -- 403 response
14. `test_member_can_cancel_rsvp` -- DELETE rsvp returns 200
15. `test_participants_endpoint_returns_correct_data` -- GET participants

### ClubCompetitionTest (Feature)
16. `test_management_can_add_team` -- POST team returns 200
17. `test_member_cannot_add_team` -- 403 response
18. `test_management_can_generate_schedule` -- POST generate returns 200
19. `test_management_can_save_score` -- PUT score returns 200
20. `test_standings_endpoint_returns_ranked_data` -- GET standings

### GenerateRecurringMeetsTest (Feature)
21. `test_command_creates_instances_for_correct_day` -- creates on matching day
22. `test_command_skips_existing_instances` -- idempotent
23. `test_command_ignores_cancelled_templates` -- only upcoming

## Todo List
- [x] Create test factories for ClubActivity, ClubActivityParticipant, ClubCompetitionTeam
- [x] Write ClubActivityServiceTest
- [x] Write ClubCompetitionServiceTest
- [x] Write ClubActivityRsvpTest
- [x] Write ClubCompetitionTest
- [x] Write GenerateRecurringMeetsTest
- [x] Run full test suite and verify all pass

## Success Criteria
- All 23 test cases pass
- No mocks for DB operations (use RefreshDatabase)
- Factory setup is reusable across tests
- Edge cases covered: full capacity, wrong skill level, duplicate RSVP, odd team count

## Risk Assessment
- **Factory dependencies**: ClubActivity factory needs Club factory with members
- **Test isolation**: Use RefreshDatabase trait
- **Slow tests**: Competition round-robin generation may be slow with many teams -- keep test data small
