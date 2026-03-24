# Phase 4: Score Submission & OPRS Integration

**Priority**: P1-P2 | **Effort**: Medium | **Status**: Complete
**Depends on**: Phase 3 (Queue & Matchmaking)

## Context

- UX Research: Feature 3 (Score Submission)
- Brainstorm Section 5: OPRS Integration
- Existing: `EloService` with K-factor system (40/24/16)
- Existing: `OprsService` with `recalculateAfterMatch()`
- Existing: `ClubMatchService.saveScore()` already handles basic scoring

## Requirements

- Player submits set scores via stepper UI (no keyboard)
- Set 3 appears only when needed (1-1 split)
- Winner preview before confirm
- OPRS/Elo update with reduced K-factor (K * oprs_weight)
- oprs_processed flag prevents double-processing

## Related Code Files

### Modify
- `app/Services/ClubMatchService.php` - extend saveScore() for set_scores JSON
- `app/Services/EloService.php` - add processClubMatchElo()
- `app/Services/OprsService.php` - add recalculateAfterClubMatch()
- `app/Http/Controllers/ClubMatchController.php` - add submitScore endpoint

### Create
- `resources/views/front/clubs/score-submit.blade.php`
- `public/assets/css/club-activity-score.css`
- `public/assets/js/club-activity-score.js`

## Implementation Steps

### Step 1: Score Submission Endpoint

```php
// In ClubMatchController
public function submitScore(Club $club, ClubActivity $activity, ClubActivityMatch $match, Request $request)
{
    $validated = $request->validate([
        'set_scores' => 'required|array|min:2|max:3',
        'set_scores.*.team1' => 'required|integer|min:0|max:21',
        'set_scores.*.team2' => 'required|integer|min:0|max:21',
    ]);

    // Validate: match belongs to activity, not already submitted
    // Validate: submitter is one of the 4 players
    // Validate: scores make sense (each set has a winner, best-of-3 logic)

    DB::transaction(function () use ($match, $validated, $request) {
        $match->update([
            'set_scores' => $validated['set_scores'],
            'result_submitted_by' => $request->user()->id ?? session('checkin_user_id'),
            'result_confirmed' => true, // 1 nguoi nhap la du
            'ended_at' => $match->ended_at ?? now(),
        ]);

        // Determine winner team
        $winner = $this->determineWinner($validated['set_scores']);

        // Save to existing score fields for compatibility
        $this->saveMatchScores($match, $validated['set_scores'], $winner);

        // Update standings
        app(ClubMatchService::class)->updateStandingsAfterMatch($match, $winner);

        // Process OPRS if enabled
        if ($match->activity->oprs_weight > 0 && !$match->oprs_processed) {
            app(OprsService::class)->recalculateAfterClubMatch($match);
            $match->update(['oprs_processed' => true]);
        }

        // Return players to queue
        app(ClubMatchService::class)->completeMatch($match);

        // Update club member stats
        app(ClubMemberStatsService::class)->updateAfterMatch($match, $winner);
    });

    return response()->json(['success' => true]);
}

private function determineWinner(array $setScores): string
{
    $t1Wins = collect($setScores)->filter(fn($s) => $s['team1'] > $s['team2'])->count();
    $t2Wins = collect($setScores)->filter(fn($s) => $s['team2'] > $s['team1'])->count();
    return $t1Wins > $t2Wins ? 'team1' : 'team2';
}
```

### Step 2: EloService - processClubMatchElo()

```php
// In EloService.php
public function processClubMatchElo(ClubActivityMatch $match, string $winner): void
{
    $oprsWeight = $match->activity->oprs_weight; // 0.0 - 1.0

    // Team 1: player1 + player2, Team 2: player3 + player4
    $team1Elo = ($match->player1->elo_rating + $match->player2->elo_rating) / 2;
    $team2Elo = ($match->player3->elo_rating + $match->player4->elo_rating) / 2;

    $expected1 = $this->expectedScore($team1Elo, $team2Elo);
    $expected2 = 1 - $expected1;

    $actual1 = $winner === 'team1' ? 1 : 0;
    $actual2 = 1 - $actual1;

    $players = [
        ['user' => $match->player1, 'expected' => $expected1, 'actual' => $actual1],
        ['user' => $match->player2, 'expected' => $expected1, 'actual' => $actual1],
        ['user' => $match->player3, 'expected' => $expected2, 'actual' => $actual2],
        ['user' => $match->player4, 'expected' => $expected2, 'actual' => $actual2],
    ];

    foreach ($players as $p) {
        $k = $this->getKFactor($p['user']->elo_rating) * $oprsWeight; // Reduced K
        $change = round($k * ($p['actual'] - $p['expected']));
        $p['user']->elo_rating = max(100, $p['user']->elo_rating + $change);
        $p['user']->save();
    }
}
```

### Step 3: OprsService - recalculateAfterClubMatch()

```php
// In OprsService.php
public function recalculateAfterClubMatch(ClubActivityMatch $match): void
{
    $playerIds = [$match->player1_id, $match->player2_id, $match->player3_id, $match->player4_id];

    foreach ($playerIds as $userId) {
        $user = User::find($userId);
        if (!$user) continue;

        // Recalculate OPRS using existing formula
        // 0.7 * Elo + 0.2 * Challenge + 0.1 * Community
        $this->recalculateForUser($user);

        // Log to OprsHistory
        OprsHistory::create([
            'user_id' => $userId,
            'oprs_score' => $user->total_oprs,
            'reason' => 'club_match', // New reason
            'metadata' => json_encode([
                'club_activity_match_id' => $match->id,
                'club_id' => $match->activity->club_id,
                'oprs_weight' => $match->activity->oprs_weight,
            ]),
        ]);
    }
}
```

### Step 4: ClubMemberStatsService (NEW)

```php
class ClubMemberStatsService
{
    public function updateAfterMatch(ClubActivityMatch $match, string $winner): void
    {
        $clubId = $match->activity->club_id;
        $winnerIds = $winner === 'team1'
            ? [$match->player1_id, $match->player2_id]
            : [$match->player3_id, $match->player4_id];

        $allIds = [$match->player1_id, $match->player2_id, $match->player3_id, $match->player4_id];

        // Calculate total points from set_scores
        $totalT1 = collect($match->set_scores)->sum('team1');
        $totalT2 = collect($match->set_scores)->sum('team2');

        foreach ($allIds as $userId) {
            $isWinner = in_array($userId, $winnerIds);
            $isTeam1 = in_array($userId, [$match->player1_id, $match->player2_id]);

            ClubMemberStat::updateOrCreate(
                ['club_id' => $clubId, 'user_id' => $userId],
                [] // just ensure exists
            );

            $stat = ClubMemberStat::where('club_id', $clubId)->where('user_id', $userId)->first();
            $stat->increment('total_matches');
            $stat->increment($isWinner ? 'total_wins' : 'total_losses');
            $stat->increment('total_points_scored', $isTeam1 ? $totalT1 : $totalT2);
            $stat->increment('total_points_against', $isTeam1 ? $totalT2 : $totalT1);
            $stat->update([
                'last_played_at' => now(),
                'current_oprs' => User::find($userId)->total_oprs,
            ]);
        }
    }
}
```

### Step 5: Score Submit View + Alpine + CSS

**Blade**: `score-submit.blade.php`
- Court hero, team cards (Team A vs Team B), set score rows with steppers
- Winner preview with green highlight
- Confirm CTA (disabled until valid)

**Alpine**: `club-activity-score.js` - caScoreSubmit() function factory
- sets array, increment/decrement, checkSet3(), winner computed, isValid computed, submit()

**CSS**: `club-activity-score.css`
- `.ca-stepper-btn` 56x56px circle buttons
- `.ca-score-display` 40px font
- `.ca-team-card.is-winner` green border transition
- `.ca-set-row` flex layout

### Step 6: Routes

```php
Route::post('/clubs/{club:slug}/activities/{activity}/matches/{match}/submit-score',
    [ClubMatchController::class, 'submitScore'])->name('club.activity.submit-score');
```

## Todo

- [x] Add submitScore endpoint to ClubMatchController
- [x] Add score validation logic (best-of-3, winner determination)
- [x] Add processClubMatchElo() to EloService
- [x] Add recalculateAfterClubMatch() to OprsService
- [x] Create ClubMemberStatsService
- [x] Create score-submit.blade.php
- [x] Create club-activity-score.js (stepper UI)
- [x] Create club-activity-score.css
- [x] Add route
- [x] Test: score submission updates standings + OPRS
- [x] Test: oprs_processed prevents double-processing

## Success Criteria

- Player taps +/- to enter scores per set
- Set 3 only appears when 1-1 split
- Winner preview shown before confirm
- OPRS updated with reduced K-factor (K * oprs_weight)
- Club member stats aggregated correctly
- oprs_processed flag prevents double-processing

## Risk

- Existing saveScore() in ClubMatchService may conflict - need to check compatibility
- OprsHistory reason field may need migration if using enum
- Edge case: player leaves before submitting score -> admin needs manual score entry
