# Phase 04 — Controller Integration (4 call sites)

## Context Links
- Front/HomeController.php:324
- Front/HomeYardTournamentController.php:3258
- Api/BookingController.php:387
- Services/ClubActivityService.php:214-228
- Parent: `./plan.md` | Deps: phases 02.5, 03

## Overview
**Priority:** P1 | **Status:** pending | **Est:** 2h

Replace 4 burn-model call sites with `transfer()` call. Resolve owner via ownership chain. Wire cancellation paths to call `refund()`. Gate behind feature flag `config('gems.transfer_enabled')`.

## Key Insights
- **Uses `GemPaymentProcessor` from phase 2.5** — no inline transfer/cashback logic in this phase.
- `payForBooking()` becomes thin adapter → `GemPaymentProcessor::pay($booking)`.
- `ClubActivityService::chargeGems()` becomes thin adapter → `GemPaymentProcessor::pay($activity, $user)` (explicit payer arg; ClubActivity::getPayer() returns null).
- **Verified gap**: `Booking::cancel()` only `update(['status' => 'cancelled'])` — **NO refund logic exists anywhere**. Must add refund wiring at ALL 3 booking cancel call sites (`Api/BookingController::cancel` L286-294, L833; `Front/HomeController`; `Front/HomeYardTournamentController`).
- **Verified OK**: ClubActivity refund wiring already exists via `ClubActivityService::cancelRsvp` L124 → `refundGems` L233 → `GemWalletService::refund`. No new wiring needed; rewritten `refund()` takes effect automatically.
- Feature flag wraps the **service internals** (in `payForBooking`, `chargeGems`), not the controllers — keeps call sites identical.
- **Cashback**: must still fire via `GemCashbackService::award($debitTx)` AFTER `transfer()` returns, in both `payForBooking` and `chargeGems` flows (preserves payer cashback).

## Requirements
**Functional**
- `GemWalletService::payForBooking(User $user, Booking $booking, int $totalPrice)` becomes a thin adapter:
  ```php
  public function payForBooking(User $user, Booking $booking, int $totalPrice): array {
      if (config('gems.transfer_enabled')) {
          [$debitTx, ] = app(GemPaymentProcessor::class)->pay($booking, $user);
          $cashbackPoints = (int) (abs($debitTx->amount) * config('gems.cashback_percent') / 100);
          return [$debitTx, $cashbackPoints];
      }
      // Legacy burn-model fallback (preserve existing body)
      return $this->legacyPayForBooking($user, $booking, $totalPrice);
  }
  ```
  - `$totalPrice` param kept for signature stability but ignored in processor branch (amount read from `$booking->total_price` via `Payable::getPayableAmountVnd()`)
  - Move current body to private `legacyPayForBooking()` for fallback
  - All validation (missing owner, self-payment, insufficient spendable) now handled inside `GemPaymentProcessor::pay()` + `GemWalletService::transfer()`
- `ClubActivityService::chargeGems()` becomes a thin adapter:
  ```php
  private function chargeGems(ClubActivity $activity, User $user): GemTransaction {
      if (config('gems.transfer_enabled')) {
          [$debitTx, ] = app(GemPaymentProcessor::class)->pay($activity, $user);
          return $debitTx;
      }
      return $this->legacyChargeGems($activity, $user);
  }
  ```
  - Move current body to `legacyChargeGems()` private helper
- **New wiring — Booking cancellation refund** (3 call sites): add to each booking cancel handler (before `$booking->cancel()`):
  ```php
  if (config('gems.transfer_enabled')) {
      try {
          app(GemPaymentProcessor::class)->refundFor($booking);
      } catch (ModelNotFoundException $e) {
          // booking wasn't paid with gems — skip
      } catch (GemTransferException $e) {
          return response()->json([
              'success' => false,
              'message' => 'Không thể hoàn Gems sau 24 giờ. Vui lòng liên hệ hỗ trợ.',
          ], 422);
      }
  }
  ```
- **ClubActivity cancellation**: NO new wiring — existing `cancelRsvp → refundGems → refund` chain automatically uses rewritten `refund()`. Just verify the participant.gem_transaction_id path works end-to-end.
- Owner resolution throws `GemTransferException::missingOwner()` when null (hard block)

**Non-functional**
- Zero behavioral change when flag off
- Single-point switch via config

## Architecture
```
payForBooking(user, booking, totalPrice)
  ├─ if config('gems.transfer_enabled')
  │   └─ GemPaymentProcessor::pay($booking, $user)
  │       ├─ Payable::getPayee()     → owner via chain
  │       ├─ Payable::getPayableAmountVnd()
  │       ├─ GemWalletService::transfer()
  │       ├─ Payable::markPaidWithGems() → confirm booking
  │       └─ GemCashbackService::award()
  └─ else legacyPayForBooking() (burn-model fallback preserved)
```

## Related Code Files
**Modify**
- `app/Services/GemWalletService.php` — `payForBooking()` rewrite, preserve `deduct()` as legacy fallback
- `app/Services/ClubActivityService.php` — `chargeGems()` lines 214-228 rewrite
- `app/Http/Controllers/Front/HomeController.php:324` — payment call site unchanged; ADD cancel refund wiring at cancel endpoint
- `app/Http/Controllers/Front/HomeYardTournamentController.php:3258` — payment call site unchanged; ADD cancel refund wiring at cancel endpoint
- `app/Http/Controllers/Api/BookingController.php:387` — payment call site unchanged; ADD cancel refund wiring at `cancel()` (L286-294) AND `destroy()` / L833 path

**No deletions** — legacy `deduct()` preserved for flag=off fallback

## Implementation Steps
1. Refactor `GemWalletService::payForBooking()`:
   - Move current burn-model body to private `legacyPayForBooking()`
   - Rewrite `payForBooking()` as flag-branching thin adapter (see Requirements snippet)
   - Keep `GemTransaction[]` return shape for 3 controller call sites (unchanged)
2. Refactor `ClubActivityService::chargeGems()`:
   - Move current body to private `legacyChargeGems()`
   - Rewrite as flag-branching thin adapter (see Requirements snippet)
3. Add booking cancellation refund wiring at 3 call sites:
   - `Api/BookingController::cancel` (around L286-294)
   - `Api/BookingController` second cancel path (around L833)
   - `Front/HomeController` + `Front/HomeYardTournamentController` cancel endpoints (grep for `booking->cancel()` and `status.*cancelled`)
   - Use the snippet from Requirements section; place BEFORE `$booking->cancel()` so refund failure blocks cancellation
4. ClubActivity cancellation: verify `cancelRsvp → refundGems` chain works with rewritten `refund()`. No code change expected — just test coverage in phase 06.
5. Verify 4 payment call sites pass unchanged params (backward-compat via stable signature).
6. Lint all modified files (`php -l`).

## Todo List
- [ ] Extract `legacyPayForBooking()` from current `payForBooking()`
- [ ] Rewrite `payForBooking()` as thin adapter → `GemPaymentProcessor::pay($booking, $user)`
- [ ] Extract `legacyChargeGems()` from current `ClubActivityService::chargeGems()`
- [ ] Rewrite `chargeGems()` as thin adapter → `GemPaymentProcessor::pay($activity, $user)`
- [ ] Add booking cancel refund wiring — Api/BookingController L286-294
- [ ] Add booking cancel refund wiring — Api/BookingController L833
- [ ] Add booking cancel refund wiring — Front/HomeController cancel endpoint (grep first)
- [ ] Add booking cancel refund wiring — Front/HomeYardTournamentController cancel endpoint (grep first)
- [ ] Verify ClubActivity cancelRsvp → refund chain (no code change, test only)
- [ ] Verify 4 payment call sites unchanged (stable signature)
- [ ] Lint check all modified files

## Success Criteria
- Feature flag OFF: all existing gem flows behave identically (burn model)
- Feature flag ON: booking payment transfers to stadium owner with locked balance
- Booking cancellation within 24h: payer refunded, owner clawed back
- ClubActivity cancellation within 24h: same behavior
- Missing stadium owner: booking confirmation fails with clear message
- Self-payment (owner books own court): blocked with clear message

## Risk Assessment
- **Eager-load N+1**: mitigated by `loadMissing`
- **Silent feature-flag drift**: cache config; document env var in `.env.example`
- **Cancellation path duplication**: multiple controllers may cancel — ensure all route through the service method so refund is called once

## Security Considerations
- Owner must be verified as actual owner at time of transfer (no cached/stale refs)
- Cancellation authorization: only payer or owner may trigger refund (enforce at controller layer as today)
- No raw SQL; all via Eloquent + locked wallets

## Next Steps
- Phase 05: UI display for owner wallet
- Phase 06: feature tests exercising all flag=on paths
