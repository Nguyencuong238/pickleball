# Phase 3: Backend - Controller & Route

## Priority: High
## Status: Not Started

## Overview
Add controller endpoint and route for auto team generation.

## Related Files
- `app/Http/Controllers/Front/LeagueTeamController.php` (modify)
- `routes/web.php` (modify)

## Implementation Steps

### 1. Add route in web.php
Inside the homeyard leagues group:
```php
POST /homeyard/leagues/{league}/teams/auto-generate → LeagueTeamController@autoGenerate
// name: homeyard.leagues.teams.auto-generate
```

Place BEFORE the `{team}` param routes to avoid route conflicts.

### 2. Add `autoGenerate()` to LeagueTeamController

```php
public function autoGenerate(Request $request, League $league)
{
    // Validate ownership
    // Validate league status in [draft, registration]
    // Validate mode in [skill_ranked, random]
    // Call LeagueService::autoGenerateTeams()
    // Return JSON response with created teams count
}
```

**Request params:**
- `mode`: string, required, in:skill_ranked,random
- `players_per_team`: integer, required, min:2, max:10

**Response:**
- Success: `{ success: true, message: "Da tao {N} doi tu {M} VDV.", teams_created: N }`
- Error: `{ success: false, message: "..." }`

## Todo
- [ ] Add route to web.php
- [ ] Add autoGenerate method to LeagueTeamController
- [ ] Validate request params
- [ ] Return JSON response

## Success Criteria
- Route accessible only by authenticated league owner
- Proper validation and error messages
- Returns JSON for AJAX consumption
