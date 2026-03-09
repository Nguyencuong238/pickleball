# Phase 1: Backend - Auto Generate Teams Service

## Priority: High
## Status: Not Started

## Overview
Add `autoGenerateTeams()` method to `LeagueService` that takes pool players and distributes them into new teams.

## Key Insights
- Pool players available via `LeagueRegistrationService::getAvailablePool()` - returns approved registrations with unassigned players
- Each `LeagueRegistrationPlayer` has `user_id`, `gender`, `skill_level`, `name`
- `skill_level` is free-text (e.g. "3.5", "4.0") - parse as float for sorting
- Teams created via existing `LeagueService::addTeam()` and player added via `addPlayer()`
- Team size do admin nhap (default: traditional=2, mlp=4)

## Related Files
- `app/Services/LeagueService.php` (modify)
- `app/Services/LeagueRegistrationService.php` (read - getAvailablePool)
- `app/Models/LeagueRegistrationPlayer.php` (read)

## Implementation Steps

### 1. Add `autoGenerateTeams()` to LeagueService

```php
/**
 * Tu dong tao doi tu pool VDV da duyet
 * @param string $mode 'skill_ranked' | 'random'
 */
public function autoGenerateTeams(League $league, string $mode = 'random', int $playersPerTeam = 2): array
```

**Logic:**
1. Get all unassigned approved players (flatten from registrations)
2. Validate: must have >= `playersPerTeam` players (at least 1 team)
3. `playersPerTeam` from request param (default: traditional=2, mlp=4)
4. Apply pairing strategy:

**Skill-ranked mode (`skill_ranked`):**
- Sort players by `skill_level` DESC (parse as float, null = 0)
- For 2-player teams: pair index 0 with N-1, index 1 with N-2, etc. (strongest with weakest)
- For 4-player teams: similar snake-draft pattern

**Random mode (`random`):**
- Shuffle players collection randomly
- Chunk into groups of `playersPerTeam`

5. For each group, create team:
   - Name: "Doi {number}" (auto-increment from existing team count + 1)
   - Create LeagueTeam via `addTeam()`
   - Add each player via `addPlayer()`
   - First player = captain

6. Handle odd players (leftover < playersPerTeam):
   - Skip them (leave in pool for manual assignment)

7. Return array of created teams

### 2. Wrap in DB::transaction

Entire operation atomic - if any step fails, rollback all.

## Todo
- [ ] Add `autoGenerateTeams()` method to LeagueService
- [ ] Validate league status (draft/registration only)
- [ ] Validate max_teams limit
- [ ] Handle skill_level parsing (float, null safety)
- [ ] Handle leftover players gracefully

## Success Criteria
- Teams created with correct player assignments
- Skill-ranked: strongest paired with weakest
- Random: shuffled distribution
- Leftover players remain in pool
- Transaction safety
