# Phase 1: Controller Data Loading

## Overview
- **Priority**: P1 (blocking for all UI phases)
- **Status**: Completed
- **Effort**: 1.5h

Load tournament match data (groups, standings, bracket rounds) in `HomeController::tournamentsDetail()` and pass to view.

## Key Insights
- Current controller only passes `$tournament` and `$registered`
- Need: groups with matches + standings per category, knockout rounds with matches per category
- Must eager-load to avoid N+1 queries
- Keep controller thin - do query in controller, format in view

## Requirements
- Load all categories with groups, matches, standings for this tournament
- Load knockout bracket rounds (round_type != 'group_stage') with matches
- Group data by category_id for easy iteration in Blade
- Only load if `$tournament->is_watch != 1` (schedule tab hidden for watch-only)

## Related Code Files
- **Modify**: `app/Http/Controllers/Front/HomeController.php` (tournamentsDetail method, ~line 626)
- **Read**: `app/Models/TournamentCategory.php`, `app/Models/Group.php`, `app/Models/GroupStanding.php`, `app/Models/Round.php`, `app/Models/MatchModel.php`

## Implementation Steps

1. In `tournamentsDetail()`, after `$tournament->load('categories')`, add eager loading:
   ```php
   $tournament->load([
       'categories.groups.matches' => fn($q) => $q->orderBy('match_number'),
       'categories.groups.standings' => fn($q) => $q->orderBy('rank_position'),
       'categories.groups.standings.athlete',
   ]);
   ```

2. Load knockout rounds per category (rounds where `round_type` in ['knockout','quarterfinal','semifinal','final','bronze']):
   ```php
   $bracketRounds = Round::where('tournament_id', $tournament->id)
       ->whereNotIn('round_type', ['group_stage'])
       ->with(['matches' => fn($q) => $q->orderBy('bracket_position')])
       ->orderBy('round_number')
       ->get()
       ->groupBy('category_id');
   ```

3. Pass `$bracketRounds` to view:
   ```php
   return view('front.tournaments.tournaments_detail', [
       'tournament' => $tournament,
       'registered' => $registered,
       'bracketRounds' => $bracketRounds,
   ]);
   ```

## Todo List
- [ ] Add eager loading for categories.groups.matches
- [ ] Add eager loading for categories.groups.standings.athlete
- [ ] Query bracket rounds grouped by category_id
- [ ] Pass bracketRounds to view
- [ ] Test: verify no N+1 queries with debugbar

## Success Criteria
- View receives all data needed for group stage and bracket rendering
- No N+1 query issues
- Fallback: if no groups/matches, existing text schedule still works

## Risk Assessment
- **Performance**: Large tournaments with many matches could be slow. Mitigate with eager loading.
- **Data gaps**: Some tournaments may have groups but no standings yet. Handle null checks in view.
