# Code Review: Match End + Score Flow (Follow-up)

**Date:** 2026-03-25
**Scope:** 9 files - controller, service, model, 3 Blade views, 3 JS files
**Focus:** Verify previous fixes, find new issues

## Previous Fixes Verification

| ID | Issue | Status |
|----|-------|--------|
| C1 | Auth bypass (null userId) | FIXED - null checks with 401 on lines 92-93, 155-157, 195-197 |
| C2 | Missing imports | FIXED - unused Club import removed (Club still used via route model binding) |
| H1/H2 | Race conditions | FIXED - `lockForUpdate()` in all 3 transactional methods |
| H4 | rejectScore stuck state | FIXED - sets `status='in_progress'` (line 81) |
| M1 | XSS activityTitle | FIXED - uses `@json()` in queue.blade.php (line 19) |
| M3 | getOpposingTeamPlayerIds(0) | FIXED - accepts `?int`, returns `[]` for null/0, checks both teams |

All previous fixes verified and correctly applied.

## New Issues Found

### HIGH: Missing Vietnamese diacritics on auth error messages

**File:** `app/Http/Controllers/ClubOpenPlayController.php` lines 93, 157, 197

Three instances of `'Vui long dang nhap.'` -- missing diacritics. Should be `'Vui long dang nhap.'` --> `'Vui lòng đăng nhập.'`

User requirement mandates Vietnamese text with proper diacritics. All other Vietnamese strings in the codebase use diacritics correctly.

**Fix:**
```php
// Lines 93, 157, 197 - change:
'Vui long dang nhap.'
// To:
'Vui lòng đăng nhập.'
```

### MEDIUM: rejectScore lacks lockForUpdate (inconsistent with other methods)

**File:** `app/Services/ClubScoreService.php` lines 73-83

`adminSubmitScore`, `playerSubmitScore`, and `confirmScore` all use `DB::transaction` + `lockForUpdate()`. But `rejectScore` does a plain `update()` without transaction or lock. A concurrent confirm and reject could race.

**Fix:**
```php
public function rejectScore(ClubActivityMatch $match): void
{
    DB::transaction(function () use ($match) {
        $match = ClubActivityMatch::lockForUpdate()->find($match->id);
        if ($match->score_status !== 'pending_confirmation') return;

        $match->update([
            'score_status' => 'rejected',
            'result_confirmed' => false,
            'set_scores' => null,
            'team1_score' => null,
            'team2_score' => null,
            'status' => 'in_progress',
        ]);
    });
}
```

### MEDIUM: adminRejectAndEnd has no error handling between sequential requests

**File:** `public/assets/js/club-activity-dashboard.js` lines 97-112

`adminRejectAndEnd` fires reject then endMatch sequentially but doesn't check if the reject succeeded before calling endMatch. If reject fails (e.g., already confirmed), endMatch will still fire and could complete the match without scores.

**Fix:** Check response before proceeding:
```javascript
async adminRejectAndEnd(matchId) {
    if (!confirm('K\u1ebft th\u00fac tr\u1eadn kh\u00f4ng \u0111i\u1ec3m?')) return;
    var confirmUrl = config.baseUrl + '/matches/' + matchId + '/confirm-score';
    var res = await fetch(confirmUrl, {
        method: 'POST',
        headers: this._headers(),
        body: JSON.stringify({ action: 'reject' }),
    });
    var data = await res.json();
    if (!data.success) {
        alert(data.message || 'Loi tu choi diem.');
        await this._poll();
        return;
    }
    var endUrl = config.triggerUrl.replace('trigger-match', 'end-match/' + matchId);
    await fetch(endUrl, { method: 'POST', headers: this._headers(), body: JSON.stringify({ skip_score: true }) });
    await this._poll();
}
```

### LOW: skipScore URL construction is fragile

**File:** `public/assets/js/club-activity-dashboard.js` line 78

`config.triggerUrl.replace('trigger-match', 'end-match/' + matchId)` -- string replacement on URL is brittle. If route naming changes, this silently breaks. Same pattern on line 105. Consider passing `endMatchUrlBase` as a config param instead.

### LOW: adminConfirmScore/skipScore swallow errors silently

**File:** `public/assets/js/club-activity-dashboard.js` lines 87-95, 76-85

Both `adminConfirmScore` and `skipScore` don't check response status or show errors. Failed operations go unnoticed by admin.

## Positive Observations

- Clean separation: controller handles auth/validation, service handles state transitions
- lockForUpdate correctly applied in transaction closures (re-fetches model inside transaction)
- `getOpposingTeamPlayerIds` properly handles edge cases (null submitter, unknown player)
- Alpine.js components are well-structured with clear state management
- CSRF tokens properly included in all JS fetch calls
- Blade templates use proper escaping throughout

## Summary

Previous fixes all correctly applied. Found 1 high-priority issue (missing diacritics), 2 medium issues (race condition in rejectScore, error handling in adminRejectAndEnd), and 2 low issues (fragile URL construction, silent error swallowing).

### Recommended Actions (priority order)
1. Fix Vietnamese diacritics on 3 auth error messages
2. Add lockForUpdate to rejectScore for consistency
3. Add error checking in adminRejectAndEnd before firing endMatch
