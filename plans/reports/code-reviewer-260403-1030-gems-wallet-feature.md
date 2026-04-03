# Code Review: Gems Wallet Feature

**Reviewer:** code-reviewer | **Date:** 2026-04-03 | **Scope:** New Gems wallet + SePay top-up + booking payment integration

## Overall Assessment

Solid feature implementation with good separation of concerns. Core wallet operations use `lockForUpdate` correctly. Several issues found, most notably a **transaction atomicity gap** in booking+deduction flow and a **webhook security weakness**.

---

## Critical Issues

### 1. Booking + Gems Deduction Not Atomic
**All 3 booking controllers** (Api/BookingController, Front/HomeController, Front/HomeYardTournamentController) create the booking in one DB::transaction, then deduct gems **outside** it. If deduction succeeds but `$booking->update(['status' => 'confirmed'])` fails, gems are lost with no confirmed booking.

**Fix:** Wrap the entire flow (booking creation + gem deduction + status update) in a single DB::transaction. On any failure, everything rolls back.

### 2. Webhook Auth Bypass When API Key Not Set
`VerifySepayWebhook` line 25: `if ($apiKey)` -- if `SEPAY_API_KEY` env var is missing/empty, the API key check is **entirely skipped**. Combined with empty `SEPAY_ALLOWED_IPS`, webhook is fully open.

**Fix:** Return 403 if `$apiKey` is falsy. Never allow unauthenticated webhook access. Add a startup check or log warning when config is missing.

### 3. Duplicate Top-Up via Webhook Replay
`SepayService::handleWebhook` checks for `pending` status, and `confirmTopUp` also checks `pending`. However, SePay may retry webhooks. If two retries arrive simultaneously before the first commits, both could pass the `pending()` check and double-credit. The `lockForUpdate` on wallet prevents balance corruption but the transaction status check in `handleWebhook` (line 42) happens **before** entering the DB::transaction in `confirmTopUp`.

**Fix:** Move the `pending` status check + update inside `confirmTopUp`'s DB::transaction block (lock the GemTransaction row too), or use `UPDATE ... WHERE status = 'pending'` with affected-rows check.

---

## High Priority

### 4. DRY Violation: Identical Payment Logic in 3 Controllers
The wallet payment block (deduct, confirm, cashback) is copy-pasted verbatim across 3 controllers (~15 lines each). Any bug fix must be applied 3 times.

**Fix:** Extract to a method on `GemWalletService`, e.g., `payForBooking(User $user, Booking $booking, int $totalPrice): GemTransaction`.

### 5. Route Parameter Mismatch in transactionDetail
Route uses `{transaction}` (implicit model binding) but controller accepts `int $transactionId` and queries manually. This works but the manual query is redundant and the auth scoping could be missed if someone later changes it to model binding.

**Fix:** Either use route model binding with a scoped query, or rename the parameter to `{transactionId}` to avoid confusion.

### 6. No Expiry Cleanup for Pending Top-Up Transactions
Pending transactions created by `createTopUpRequest` have no TTL or scheduled cleanup. They accumulate indefinitely. The 15-minute countdown in the UI is cosmetic only.

**Fix:** Add a scheduled command to cancel stale pending top-ups (e.g., older than 30 minutes). Add an `expires_at` column or use `created_at` comparison.

---

## Medium Priority

### 7. `balance_after` Set Incorrectly on Pending Top-Up
`GemWalletService::createTopUpRequest` line 43 sets `balance_after => $wallet->balance` (current balance, not post-top-up). This is misleading for pending transactions -- it looks like the balance didn't change. Only corrected on confirmation.

**Fix:** Either set `balance_after` to `null` for pending, or to expected value (`$wallet->balance + $gems`), and document the convention.

### 8. Missing Input Validation on `transactions` Endpoint
`Api\GemController::transactions` passes `$request->type` and `$request->status` directly to query scopes without validating allowed values. While Laravel's query builder parameterizes these (no SQL injection), invalid values produce empty results with no user feedback.

**Fix:** Add validation: `'type' => 'in:top_up,payment,refund,admin_adjust'`, `'status' => 'in:pending,completed,failed,cancelled'`.

### 9. Frontend Polls Unauthenticated Detail Endpoint
The polling JS in `index.blade.php` fetches `/api/gems/transactions/{txId}` which requires `auth:api`. The JWT token is read from `localStorage`. If session-based auth users access the gems page (web routes), the polling will fail with 401.

**Fix:** Either add a web route for transaction status check, or ensure the gems page always has a valid JWT token available.

---

## Minor

- **10.** `GemCashbackService::award` uses `User::find()` instead of eager-loading from the transaction relationship. Minor N+1 potential.
- **11.** Config hardcodes SePay IPs in default value (`config/gems.php` line 12). If IPs change, requires code deploy instead of env update. Move defaults to `.env.example` only.
- **12.** `createTopUpRequest` uses `app(SepayService::class)` instead of constructor injection. Inconsistent with other service usages.

---

## Positive Observations

- `lockForUpdate` correctly used in all balance-modifying operations
- Webhook validates transfer direction (`transferType === 'in'`) and amount
- Good model scopes (`pending()`, `completed()`, `byType()`)
- Clean migration design with proper indexes
- CSRF exception correctly added for webhook route

---

## Recommended Actions (Priority Order)

1. **Wrap booking+deduction in single DB::transaction** (Critical)
2. **Fail-closed webhook auth when API key not configured** (Critical)
3. **Make webhook idempotent with row-level locking on transaction status** (Critical)
4. **Extract shared payment logic to service method** (High, reduces bug surface)
5. **Add scheduled cleanup for stale pending transactions** (High)
6. **Fix polling auth for web-session users** (Medium)
7. **Validate filter params on transactions endpoint** (Medium)

---

## Unresolved Questions

- Is there a booking cancellation flow that should trigger gem refunds? `refund()` exists in service but no controller uses it.
- Should there be a daily/monthly top-up limit per user for fraud prevention?
- Does the `addPoints` method on User (called by cashback service) exist and work correctly? Not verified in this review scope.
