# Phase 5: Score Confirmation Flow (Integration)

**Status**: Complete

## Overview
Wire up the full confirmation cycle and handle edge cases. This phase is about testing integration and handling rejected/re-submitted scores.

## Edge Cases to Handle

### 1. Rejected score → re-submit
When opposing team rejects, `score_status = 'rejected'`, `set_scores = null`.
- Original submitter's queue hero card should show: "Diem bi tu choi. Nhap lai."
- Link to score form again (submit mode, not confirm mode)
- Need to track this in `queueStatus` response

**In `getMyStatus()`**: Add check for rejected match:
```php
$rejectedMatch = $activity->matches()
    ->where('score_status', 'rejected')
    ->where('result_submitted_by', $userId)
    ->whereNull('ended_at') // still not completed
    ->first();

// Return:
'rejected_match_id' => $rejectedMatch?->id,
```

### 2. Player status during pending_confirmation
Players in a `pending_confirmation` match should NOT be returned to queue yet.
- `completeMatch()` is NOT called during pending state (Phase 2 handles this)
- Their `current_status` stays `playing` until confirmed
- Queue page shows them as "Cho xac nhan" instead of "Dang choi"

### 3. Admin override for stuck matches
Admin dashboard already has "Admin xac nhan" button (Phase 3).
Admin can also reject and end without score from dashboard.

Add to admin pending card:
```html
<button class="ca-btn-sm ca-btn-danger" @click="adminRejectAndEnd(pm.id)">Tu choi & Ket thuc</button>
```

```js
async adminRejectAndEnd(matchId) {
    if (!confirm('Ket thuc tran khong diem?')) return;
    // First reject score
    await fetch(url + '/confirm-score', {
        method: 'POST',
        headers: this._headers(),
        body: JSON.stringify({ action: 'reject' }),
    });
    // Then end match without score
    await fetch(url.replace('confirm-score', '').replace('matches/' + matchId, 'end-match/' + matchId), {
        method: 'POST',
        headers: this._headers(),
        body: JSON.stringify({ skip_score: true }),
    });
    await this._poll();
},
```

### 4. Match already ended by admin while player is scoring
If admin ends match (skip_score) while player is on score form:
- `submitScore` should check `match->status === 'completed'` and return error
- Already partially handled by `result_confirmed` check (line 159)

Add explicit check:
```php
if ($match->status === 'completed') {
    return response()->json(['success' => false, 'message' => 'Tran dau da ket thuc boi admin.'], 422);
}
```

### 5. Both teams try to submit score simultaneously
First submit wins (DB transaction). Second submit sees `score_status = 'pending_confirmation'` and gets redirected to confirm mode instead.

In `submitScore`, add at top:
```php
if ($match->score_status === 'pending_confirmation') {
    return response()->json([
        'success' => false,
        'message' => 'Diem da duoc nhap, dang cho xac nhan.',
        'redirect' => route('club.activity.score-form', [$club->slug, $activity->id, $match->id]),
    ], 422);
}
```

## Queue Hero Card State Machine

| my_status condition | Display |
|---|---|
| `current_status = 'queued'` | Position + estimated wait |
| `current_status = 'playing'` + no pending | "Dang thi dau" + "Ket thuc & Nhap diem" button |
| `pending_score_match_id` + `can_confirm_score` | "Xac nhan diem" link |
| `pending_score_match_id` + !`can_confirm_score` | "Dang cho xac nhan..." |
| `rejected_match_id` | "Diem bi tu choi. Nhap lai." link |

## Testing Checklist
- [ ] Admin end match -> score -> complete (immediate)
- [ ] Admin end match -> skip score -> complete (no score)
- [ ] Player end match -> score -> pending -> opposing confirms -> complete
- [ ] Player end match -> score -> pending -> opposing rejects -> re-submit
- [ ] Player end match -> score -> pending -> admin confirms
- [ ] Player end match -> score -> pending -> admin rejects & ends
- [ ] Race condition: two players submit simultaneously
- [ ] Race condition: admin ends while player scoring
- [ ] Elo/OPRS only processed on confirmed matches
- [ ] Players returned to queue only after final confirmation
- [ ] Dashboard shows pending matches prominently

## Success Criteria
- Full cycle works for all flows (admin, player-confirm, player-reject-resubmit)
- Edge cases handled gracefully with clear error messages
- No orphaned matches stuck in pending state (admin can always resolve)
- Elo/OPRS integrity maintained (only on confirmed scores)
