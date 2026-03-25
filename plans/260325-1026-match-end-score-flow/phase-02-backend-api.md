# Phase 2: Backend API Changes

**Status**: Complete

## Overview
Modify existing endpoints and add new ones for the two flows.

## Changes to Existing Endpoints

### 1. `ClubOpenPlayController::endMatch()` (line 88)
**Current**: Admin-only, immediately completes match via `completeMatch()`. No `Request` param.
**New behavior**:
- Add `Request $request` parameter to method signature
- If request has `skip_score=true`: confirm dialog already shown on frontend, complete match without score (keep current behavior)
- If no `skip_score`: Return JSON with `redirect_to_score: true` + score form URL. Frontend redirects to score form.

```php
public function endMatch(Club $club, ClubActivity $activity, ClubActivityMatch $match, Request $request): JsonResponse
{
    // ... existing auth + validation ...

    if ($request->boolean('skip_score')) {
        app(ClubMatchmakingService::class)->completeMatch($match);
        return response()->json(['success' => true, 'message' => 'Tran dau da ket thuc (khong co diem).']);
    }

    return response()->json([
        'success' => true,
        'redirect_to_score' => true,
        'score_url' => route('club.activity.score-form', [$club->slug, $activity->id, $match->id]),
    ]);
}
```

### 2. `ClubOpenPlayController::submitScore()` (line 137)
**Current**: Sets `result_confirmed = true` immediately for everyone
**New behavior**: Branch based on submitter role

```php
// After validation and score calculation...
$isAdmin = auth()->check() && $club->isManagement(auth()->user());

if ($isAdmin) {
    // Admin submit = immediate confirmation
    $match->update([
        'set_scores' => $setScores,
        'result_submitted_by' => $userId,
        'result_confirmed' => true,
        'score_status' => 'admin_confirmed',
        'ended_at' => $match->ended_at ?? now(),
        'status' => 'completed',
    ]);
    // Process Elo, update stats, complete match (existing logic)
} else {
    // Player submit = pending confirmation
    // Set ended_at to free up the court for next match
    // Players stay in 'playing' status until confirmation resolves
    $match->update([
        'set_scores' => $setScores,
        'result_submitted_by' => $userId,
        'result_confirmed' => false,
        'score_status' => 'pending_confirmation',
        'ended_at' => $match->ended_at ?? now(),
        'status' => 'pending_score',
    ]);
    // Do NOT process Elo or complete match yet
    // Court is freed (ended_at set), but players NOT returned to queue until confirm
}
```

### 3. `ClubOpenPlayController::scoreForm()` (line 123)
**Current**: No access control beyond activity validation
**Add**: Pass `isAdmin` flag to view, and `mode` (submit vs confirm)

```php
$userId = auth()->id() ?? session('checkin_user_id');
$isAdmin = auth()->check() && $club->isManagement(auth()->user());
$isPendingConfirm = $match->score_status === 'pending_confirmation';
$canConfirm = $isPendingConfirm && in_array($userId, $match->getOpposingTeamPlayerIds($match->result_submitted_by));

return view('front.clubs.score-submit', [
    'club' => $club,
    'activity' => $activity,
    'match' => $match,
    'isAdmin' => $isAdmin,
    'mode' => $canConfirm ? 'confirm' : 'submit',
]);
```

## New Endpoints

### 4. Player End Match
**Route**: `POST /clubs/{club}/activities/{activity}/player-end-match/{match}`
**Name**: `club.activity.player-end-match`

```php
public function playerEndMatch(Club $club, ClubActivity $activity, ClubActivityMatch $match): JsonResponse
{
    $this->validateActivity($club, $activity);
    $this->validateMatchBelongsToActivity($match, $activity);

    $userId = auth()->id() ?? session('checkin_user_id');
    $isPlayer = in_array($userId, [
        $match->player1_id, $match->player2_id,
        $match->player3_id, $match->player4_id,
    ]);

    if (!$isPlayer) {
        return response()->json(['success' => false, 'message' => 'Ban khong o trong tran nay.'], 403);
    }

    if ($match->ended_at) {
        return response()->json(['success' => false, 'message' => 'Tran dau da ket thuc.'], 422);
    }

    return response()->json([
        'success' => true,
        'score_url' => route('club.activity.score-form', [$club->slug, $activity->id, $match->id]),
    ]);
}
```

### 5. Confirm Score
**Route**: `POST /clubs/{club}/activities/{activity}/matches/{match}/confirm-score`
**Name**: `club.activity.confirm-score`

```php
public function confirmScore(Club $club, ClubActivity $activity, ClubActivityMatch $match, Request $request): JsonResponse
{
    $this->validateActivity($club, $activity);
    $this->validateMatchBelongsToActivity($match, $activity);

    $userId = auth()->id() ?? session('checkin_user_id');
    $isAdmin = auth()->check() && $club->isManagement(auth()->user());
    $opposingIds = $match->getOpposingTeamPlayerIds($match->result_submitted_by);

    // Admin can always confirm/reject; players only from opposing team
    if (!$isAdmin && !in_array($userId, $opposingIds)) {
        return response()->json(['success' => false, 'message' => 'Ban khong co quyen xac nhan.'], 403);
    }

    if ($match->score_status !== 'pending_confirmation') {
        return response()->json(['success' => false, 'message' => 'Diem da duoc xu ly.'], 422);
    }

    $action = $request->input('action'); // 'confirm' or 'reject'

    if ($action === 'reject') {
        // Keep result_submitted_by so we can show "rejected" state to the original submitter
        $match->update([
            'score_status' => 'rejected',
            'result_confirmed' => false,
            'set_scores' => null,
        ]);
        return response()->json(['success' => true, 'message' => 'Diem da bi tu choi. Nguoi nhap co the nhap lai.']);
    }

    // Confirm
    DB::transaction(function () use ($match, $userId) {
        $match->update([
            'score_status' => 'confirmed',
            'score_confirmed_by' => $userId,
            'result_confirmed' => true,
            'status' => 'completed',
        ]);

        $setScores = $match->set_scores;
        $winner = $this->determineWinner($setScores);

        // Process Elo
        if ($match->activity->oprs_weight > 0 && !$match->oprs_processed) {
            app(EloService::class)->processClubMatchElo($match, $winner);
            app(OprsService::class)->recalculateAfterClubMatch($match);
            $match->update(['oprs_processed' => true]);
        }

        // Complete match + return players to queue
        app(ClubMatchmakingService::class)->completeMatch($match);
        app(ClubMemberStatsService::class)->updateAfterMatch($match, $winner);
    });

    return response()->json(['success' => true, 'message' => 'Diem da duoc xac nhan.']);
}
```

### 6. Update `dashboardState()` in ClubDashboardController
Add pending confirmation matches to response:

```php
'pending_scores' => $activity->matches()
    ->where('score_status', 'pending_confirmation')
    ->with(['player1:id,name', 'player2:id,name', 'player3:id,name', 'player4:id,name', 'submittedBy:id,name'])
    ->get(),
```

### 7. Update `queueStatus()` in ClubOpenPlayController
Add pending match info to `my_status`:

```php
// In getMyStatus(), after currentMatchId logic, add:
$pendingMatch = $activity->matches()
    ->where('score_status', 'pending_confirmation')
    ->where(function ($q) use ($userId) {
        $q->where('player1_id', $userId)->orWhere('player2_id', $userId)
          ->orWhere('player3_id', $userId)->orWhere('player4_id', $userId);
    })
    ->first();

// Return in my_status:
'pending_score_match_id' => $pendingMatch?->id,
'can_confirm_score' => $pendingMatch && in_array($userId, $pendingMatch->getOpposingTeamPlayerIds($pendingMatch->result_submitted_by)),
```

## Routes

**File**: `routes/web.php` (after line 362)

```php
Route::post('/player-end-match/{match}', [ClubOpenPlayController::class, 'playerEndMatch'])->name('club.activity.player-end-match');
Route::post('/matches/{match}/confirm-score', [ClubOpenPlayController::class, 'confirmScore'])->name('club.activity.confirm-score');
```

## Success Criteria
- Admin end match: redirects to score form OR skips score with confirmation
- Admin submit score: immediate completion + Elo processing
- Player end match: validates player in match, returns score URL
- Player submit score: sets `pending_confirmation`
- Opposing player confirm: completes match + Elo
- Opposing player reject: clears score, allows re-entry
- Dashboard state includes pending scores
- Queue status includes pending match info
