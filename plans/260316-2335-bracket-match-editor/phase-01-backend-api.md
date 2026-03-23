# Phase 1: Backend API

## Overview
- **Priority**: P0
- **Status**: pending
- Add two endpoints: get eligible athletes for a match, update match athletes/properties

## Requirements
- R1: `GET bracket/eligible-athletes?match_id=X` - return athletes eligible for this match slot
- R2: `PUT bracket/update-match` - update athlete assignments + match properties (no court_id)
- R3: Validate athlete eligibility server-side
- R4: Re-evaluate bye status and advancement after update
- R5: Cascade detection - count downstream matches affected by athlete change, return `affected_count`
- R6: `PUT bracket/update-match` with `confirm_cascade=true` to execute cascade clear
<!-- Updated: Validation Session 1 - Removed court_id, added cascade detection -->

## Architecture

### Eligible Athletes Logic
For a match in round N:
1. Get all `tournament_athletes` with `is_advanced = true` for the category (base pool)
2. If round N > first bracket round: filter to athletes who appear as `winner_id` in round N-1 matches (or earlier completed rounds)
3. For first bracket round: all advanced athletes from group stage are eligible
4. Exclude athletes already assigned in other matches of the SAME round (except current match)

### Update Match Logic
1. Validate match belongs to tournament (ownership check)
2. Validate match status is `scheduled` or bye (not `completed` with both athletes and scores)
3. Update `athlete1_id`, `athlete1_name`, `athlete2_id`, `athlete2_name` as needed
4. Update optional fields: `match_time`, `best_of`, `notes` (no court_id)
5. Re-evaluate bye: if one athlete null + one present -> auto-complete as bye
6. If was bye and now has 2 athletes -> revert to scheduled, clear winner

## Related Code Files

### Files to Modify
- `app/Http/Controllers/Front/Tournament/TournamentBracketController.php` - add `getEligibleAthletes()`, `updateMatch()`
- `app/Services/Tournament/KnockoutBracketQuery.php` - add `getEligibleAthletes()`, `updateMatchAthletes()`
- `routes/web.php` - add 2 routes

### Files to Reference
- `app/Models/MatchModel.php`
- `app/Models/TournamentAthlete.php`
- `app/Services/Tournament/KnockoutMatchBuilder.php` (handleBye logic)

## Implementation Steps

### Step 1: Add `getEligibleAthletes` to KnockoutBracketQuery
```php
public function getEligibleAthletes(MatchModel $match): array
{
    $round = $match->round;
    $categoryId = $match->category_id;
    $tournamentId = $match->tournament_id;

    // Base pool: all advanced athletes in this category
    $basePool = TournamentAthlete::where('tournament_id', $tournamentId)
        ->where('category_id', $categoryId)
        ->where('is_advanced', true)
        ->get();

    // Get current round number
    $currentRoundNumber = $round->round_number;

    // Get all bracket round types
    $bracketRoundTypes = ['knockout', 'quarterfinal', 'semifinal', 'final'];

    // First bracket round: all advanced athletes eligible
    $firstBracketRound = Round::where('tournament_id', $tournamentId)
        ->where('category_id', $categoryId)
        ->whereIn('round_type', $bracketRoundTypes)
        ->orderBy('round_number')
        ->first();

    if ($firstBracketRound && $currentRoundNumber === $firstBracketRound->round_number) {
        $eligible = $basePool;
    } else {
        // For later rounds: only athletes who won in previous bracket rounds
        $previousRounds = Round::where('tournament_id', $tournamentId)
            ->where('category_id', $categoryId)
            ->whereIn('round_type', $bracketRoundTypes)
            ->where('round_number', '<', $currentRoundNumber)
            ->pluck('id');

        $winnerIds = MatchModel::whereIn('round_id', $previousRounds)
            ->whereNotNull('winner_id')
            ->pluck('winner_id')
            ->unique();

        $eligible = $basePool->whereIn('id', $winnerIds);
    }

    // Exclude athletes already in other matches of same round (except this match)
    $usedInRound = MatchModel::where('round_id', $round->id)
        ->where('id', '!=', $match->id)
        ->get()
        ->flatMap(fn($m) => [$m->athlete1_id, $m->athlete2_id])
        ->filter()
        ->unique();

    return $eligible->whereNotIn('id', $usedInRound)
        ->map(fn($a) => [
            'id' => $a->id,
            'name' => $a->athlete_name,
            'partner_name' => $a->partner?->athlete_name,
            'pair_name' => $a->pair_name,
            'seed' => $a->seed_number,
        ])
        ->values()
        ->toArray();
}
```

### Step 2: Add `updateMatchAthletes` to KnockoutBracketQuery
```php
public function updateMatchAthletes(MatchModel $match, array $data): void
{
    $updates = [];

    if (array_key_exists('athlete1_id', $data)) {
        $updates['athlete1_id'] = $data['athlete1_id'];
        $updates['athlete1_name'] = $data['athlete1_id']
            ? TournamentAthlete::find($data['athlete1_id'])?->pair_name
            : null;
    }
    if (array_key_exists('athlete2_id', $data)) {
        $updates['athlete2_id'] = $data['athlete2_id'];
        $updates['athlete2_name'] = $data['athlete2_id']
            ? TournamentAthlete::find($data['athlete2_id'])?->pair_name
            : null;
    }

    foreach (['match_time', 'best_of', 'notes'] as $field) {
        if (array_key_exists($field, $data)) {
            $updates[$field] = $data[$field];
        }
    }

    $match->update($updates);

    // Re-evaluate bye status
    $match->refresh();
    $hasA1 = $match->athlete1_id !== null;
    $hasA2 = $match->athlete2_id !== null;

    if ($hasA1 && !$hasA2 || !$hasA1 && $hasA2) {
        // Become a bye - auto-complete
        $winnerId = $hasA1 ? $match->athlete1_id : $match->athlete2_id;
        $match->update(['status' => 'completed', 'winner_id' => $winnerId]);
        // Advance to next match
        if ($match->next_match_id) {
            (new KnockoutMatchBuilder())->advanceWinner($match, $winnerId);
        }
    } elseif ($hasA1 && $hasA2 && $match->status === 'completed' && !$match->set_scores) {
        // Was bye, now has 2 athletes: revert to scheduled
        $match->update(['status' => 'scheduled', 'winner_id' => null]);
        // Clear from next match
        if ($match->next_match_id) {
            $this->clearFromNextMatch($match);
        }
    }
}
```

### Step 3: Add routes
```php
// In web.php tournament-manage group
Route::get('/tournaments/{tournament}/bracket/eligible-athletes', [TournamentBracketController::class, 'getEligibleAthletes'])
    ->name('tournament-manage.bracket.eligible-athletes');
Route::put('/tournaments/{tournament}/bracket/update-match', [TournamentBracketController::class, 'updateMatch'])
    ->name('tournament-manage.bracket.update-match');
```

### Step 4: Add controller methods
```php
public function getEligibleAthletes(Request $request, Tournament $tournament): JsonResponse
{
    $this->authorizeOwner($tournament);
    $validated = $request->validate([
        'match_id' => ['required', 'integer', Rule::exists('matches', 'id')->where('tournament_id', $tournament->id)],
    ]);
    $match = MatchModel::findOrFail($validated['match_id']);
    $athletes = $this->bracketService->getEligibleAthletes($match);
    return response()->json(['success' => true, 'athletes' => $athletes]);
}

public function updateMatch(Request $request, Tournament $tournament): JsonResponse
{
    $this->authorizeOwner($tournament);
    $validated = $request->validate([
        'match_id'    => ['required', 'integer', Rule::exists('matches', 'id')->where('tournament_id', $tournament->id)],
        'athlete1_id' => 'nullable|integer|exists:tournament_athletes,id',
        'athlete2_id' => 'nullable|integer|exists:tournament_athletes,id',
        'match_time'  => 'nullable|date_format:H:i',
        'confirm_cascade' => 'nullable|boolean',
        'best_of'     => 'nullable|integer|in:1,3,5',
        'notes'       => 'nullable|string|max:500',
    ]);
    $match = MatchModel::findOrFail($validated['match_id']);
    $this->bracketService->updateMatchAthletes($match, $validated);
    return response()->json(['success' => true, 'message' => 'Cap nhat thanh cong']);
}
```

## Todo List
- [ ] Add `getEligibleAthletes()` to `KnockoutBracketQuery`
- [ ] Add `updateMatchAthletes()` to `KnockoutBracketQuery`
- [ ] Wire through `KnockoutBracketService` facade methods
- [ ] Add controller methods `getEligibleAthletes`, `updateMatch`
- [ ] Add routes in `web.php`
- [ ] Test eligible athletes filtering by round
- [ ] Test update with bye re-evaluation

## Success Criteria
1. `GET eligible-athletes?match_id=X` returns correct pool per round logic
2. `PUT update-match` updates athletes and properties
3. Bye auto-detection works after update
4. Athletes already used in same round are excluded from eligible list

## Risk Assessment
| Risk | Impact | Mitigation |
|------|--------|------------|
| Circular advancement after edit | High | Clear next match slot when reverting bye |
| Stale winner in next round | Medium | Clear advancement chain on athlete change |
