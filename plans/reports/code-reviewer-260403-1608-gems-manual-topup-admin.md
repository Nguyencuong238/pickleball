# Code Review: Gems Manual Top-up with Admin Approval

**Score: 7/10**

## Scope
- Files: 8 (2 new, 6 modified)
- Focus: Admin approve/reject flow, QR generation, cancel logic, routing, views

## Critical Issues

### 1. Race Condition: Webhook vs Admin Approve (CRITICAL)
Both SePay webhook (`SepayService::handleWebhook`) and admin (`GemTopupController::approve`) can confirm the same pending transaction simultaneously.

- `confirmTopUp()` uses `lockForUpdate` which protects against double-credit within the DB transaction, but the admin controller checks `$transaction->status !== 'pending'` **outside** the lock. If webhook confirms between the check and `confirmTopUp()`, the lock inside `confirmTopUp` silently returns (status no longer pending), but the admin sees a success redirect with the message "Da duyet nap X Gems" -- misleading.

**Fix:** `confirmTopUp()` should return a bool indicating whether it actually processed, and the controller should check:
```php
public function confirmTopUp(GemTransaction $transaction): bool
{
    return DB::transaction(function () use ($transaction) {
        $tx = GemTransaction::where('id', $transaction->id)->lockForUpdate()->first();
        if ($tx->status !== 'pending' || $tx->type !== 'top_up') {
            return false;
        }
        // ... credit wallet, update status
        return true;
    });
}
```
Controller:
```php
if (!$this->walletService->confirmTopUp($transaction)) {
    return back()->with('error', 'Giao dich da duoc xu ly truoc do.');
}
```

### 2. `cancelTopUp()` Not Atomic (HIGH)
`cancelTopUp()` checks status then updates without a lock. Same race condition -- webhook could confirm between check and update, then admin cancel overwrites `completed` back to `cancelled` (gems already credited but status says cancelled).

**Fix:** Add `lockForUpdate` in a DB transaction:
```php
public function cancelTopUp(GemTransaction $transaction): bool
{
    return DB::transaction(function () use ($transaction) {
        $tx = GemTransaction::where('id', $transaction->id)->lockForUpdate()->first();
        if ($tx->status !== 'pending' || $tx->type !== 'top_up') {
            return false;
        }
        $tx->update(['status' => 'cancelled']);
        return true;
    });
}
```

## High Priority

### 3. N+1 Query in Sidebar Badge (HIGH)
`app.blade.php` line 506: raw Eloquent query `GemTransaction::where(...)` runs on **every admin page load**. Combined with existing `PermissionRequest`, `PointSubmission`, `SkillQuizAttempt` sidebar queries, this is 4+ uncached queries per page.

**Fix:** Use a View Composer or cache the count for 60 seconds:
```php
$pendingGemTopups = Cache::remember('pending_gem_topups_count', 60, fn() =>
    GemTransaction::where('type', 'top_up')->where('status', 'pending')->count()
);
```

### 4. SepayService `buildQrUrl` Logic Inverted (MEDIUM)
Comment says "VietQR by default, SePay as fallback" but code checks SePay config first (`if config('gems.sepay.account_number')`). If both SePay and bank configs are set, SePay takes priority -- contradicts the stated intent.

**Fix:** Either swap the if/else order to check `gems.bank.bin` first, or fix the comment. Clarify which is truly the default.

### 5. No Admin Audit Trail (MEDIUM)
When admin approves/rejects, no record of **who** approved. The `GemTransaction` metadata should store `approved_by` / `rejected_by` with admin user ID and timestamp.

**Fix:** Pass `auth()->id()` to service methods and store in metadata:
```php
$transaction->update([
    'status' => 'cancelled',
    'metadata' => array_merge($transaction->metadata ?? [], [
        'rejected_by' => auth()->id(),
        'rejected_at' => now()->toIso8601String(),
    ]),
]);
```

## Medium Priority

### 6. Bank Account Exposed in Blade Config (MEDIUM)
`topup-modal.blade.php` renders `config('gems.bank.account_number')` directly. This is intentional (user needs it to transfer), but the hidden input `bankAccountRaw` unnecessarily duplicates it. Not a security issue per se, but ensure the bank config values are never accidentally leaked in API responses.

### 7. Approve/Reject Forms Missing HTTP Method Spoofing Consideration
The forms use POST which is correct. However, semantically approve and reject are state-changing operations on a resource -- consider using PATCH for RESTful consistency. Minor, not blocking.

### 8. `$tx->user->name` in Confirm Dialog -- XSS Risk (LOW)
In `index.blade.php` line 114: `confirm('Duyet nap {{ $tx->amount }} Gems cho {{ $tx->user->name ?? '' }}?')` -- if user name contains a single quote, it breaks the JS confirm. Blade's `{{ }}` escapes HTML but not JS string context.

**Fix:** Use `@json` or `e()` with addslashes:
```blade
onsubmit="return confirm('Duyet nap {{ $tx->amount }} Gems cho {{ addslashes($tx->user->name ?? '') }}?')"
```

## Positive Observations
- Clean controller structure, proper constructor injection
- `confirmTopUp` uses `lockForUpdate` for wallet balance (good)
- Status filter uses whitelist validation (`in_array`)
- CSRF tokens present on all forms
- Proper admin middleware (`role:admin`) on routes
- Pagination with `withQueryString()` preserves filters

## Recommended Actions (Priority Order)
1. **[CRITICAL]** Fix race condition in `confirmTopUp` -- return bool, handle in controller
2. **[CRITICAL]** Add `lockForUpdate` to `cancelTopUp`
3. **[HIGH]** Cache sidebar badge queries
4. **[MEDIUM]** Add admin audit trail to approve/reject metadata
5. **[MEDIUM]** Clarify VietQR vs SePay priority in `buildQrUrl`
6. **[LOW]** Fix JS string escaping in confirm dialogs

## Unresolved Questions
- Is the SePay webhook still active alongside manual admin approval? If so, which path takes precedence needs to be documented.
- Should there be a time-based auto-cancellation for stale pending top-ups (e.g., 24h)?
- Is there a plan to invalidate the sidebar badge cache when a top-up is approved/rejected?
