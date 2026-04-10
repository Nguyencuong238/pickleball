# Code Review — Gems Transfer + Escrow Refund Window

Date: 2026-04-10
Reviewer: code-reviewer
Plan: plans/260410-0928-gems-transfer-escrow/plan.md
Scope: schema, service, processor, Payable abstraction, controllers, command, tests.

## Overall Assessment

Strong, well-bounded implementation. Transfer/refund/release semantics are coherent, the Payable abstraction is clean, locking is correct, tests cover the main invariants. A few real correctness risks around the unique index + re-enrollment and the "burned fee resurrected on refund" edge case should be addressed before the flag is flipped on in prod.

## Critical (must fix before merge/rollout)

1. Unique index `ux_gem_tx_ref_type_user (reference_type, reference_id, type, user_id)` blocks legitimate re-payment after cancel.
   - Scenario: user RSVPs → cancels (refund writes new rows; debit row flipped to `refunded`) → re-RSVPs to same activity. `GemPaymentProcessor::pay` → `transfer` inserts another `payment` row with same `(ref_type, ref_id, type=payment, user_id)`. Unique index fires, `GemTransferException::duplicateTransaction` thrown.
   - Also affects `promoteFromWaitlist` re-charging the same user if they cycled.
   - Fix options: (a) add `status` to unique key, (b) partial/functional index excluding refunded/cancelled (MySQL 8 supports generated column trick), or (c) enforce via app-level check inside the transfer transaction.
   - Tests do not cover this; `GemTransferTest::test_transfer_duplicate_throws_via_unique_index` asserts the blocking behavior but no test re-tries after refund.

2. Platform fee is "burned" on debit but fully restored on refund — creates Gems out of thin air.
   - `transfer()` decrements payer by full `$gems`, credits payee `$netToPayee = gems - fee`; the `fee` delta is unaccounted. `refund()` then credits payer full `refundAmount = abs(debit.amount) = gems` and clawsback only `creditTx.amount = netToPayee`. Net effect: system supply grew by `fee`.
   - `GemRefundTest::test_refund_preserves_invariants_with_fee` only checks per-wallet invariants, not conservation; it passes because supply is not asserted.
   - Acceptable for phase 1 (fee=0) but must be flagged. Fix before turning on `platform_fee_percent > 0`: either debit only `netToPayee` from payer (fee becomes a separate "burn" tx) or on refund only credit `netToPayee` back.

3. Booking cancel wiring in `HomeYardTournamentController::cancelBooking` (L5099-5153) wraps everything in `try { ... } catch (\Exception $e) { return 500 }`. A `GemTransferException` thrown for "outside window" is already caught and returned 422 inside the inner try, but if the refund itself emits any other exception (DB deadlock retry, QueryException) it flows to the outer catch and returns 500 with raw `$e->getMessage()` — leaking internals. Redact or map to generic Vietnamese message.

## High

4. `refundFor()` uses `firstOrFail()` + `whereIn('status', [COMPLETED])`. After partial refund attempt that failed mid-transaction but left status flipped (shouldn't happen, but), or after refund succeeded (status=refunded), a subsequent cancel path would raise `ModelNotFoundException` — which is caught as "not paid with Gems" in the controllers. That silently masks real bugs. Consider distinguishing "no payment row" vs "already refunded" and returning a clearer Vietnamese message for the latter.

5. `Booking::getPayee()` returns `$this->court?->stadium?->user`. If stadium has no owner (legacy/orphan row) this returns null → `missingOwner` exception during pay, which is correct. But there is no pre-validation at booking creation time to guarantee the stadium has an owner, so users can get far into the flow before hitting this. Recommend a one-line guard at booking creation or an admin integrity check.

6. `releaseReceipt()` bypasses the wallet ordering lock pattern (locks credit row first, then wallet). This is safe because only one wallet is involved, but if any future path inside the release tx touches a second wallet the ordering rule is violated. Add a code comment calling out the invariant.

7. Release command uses `chunkById(500)` over a live-updating set. Rows released inside the chunk loop may cause `orderBy('id')` chunk cursor to re-read updated rows. Safe today because the WHERE filters on `released_at IS NULL` and the release sets `released_at`, so next chunk naturally excludes them. Worth a short comment documenting why it is safe.

## Medium

8. `ClubActivity::getPayableAmountVnd()` returns `fee_gems * exchange_rate`, then `GemPaymentProcessor::pay` divides back via `ceil(amountVnd / exchange_rate)`. Works but brittle if exchange rate changes between multiplication and division (e.g., mid-request config reload). Prefer a direct `getPayableAmountGems()` on Payable (or override in ClubActivity) to skip the round-trip.

9. `GemWalletService::isUniqueViolation` compares `$e->getCode() === '23000'` as string; some drivers return int. Also `str_contains($e->getMessage(), 'Duplicate entry')` is English-dependent; safer to inspect `$e->errorInfo[1] === 1062` for MySQL.

10. `GemPaymentProcessor::pay` calls `$item->markPaidWithGems()` and `cashback->award()` OUTSIDE the transfer DB transaction. If cashback fails, transfer is committed but cashback/domain state diverges. Wrap the three side effects in an outer `DB::transaction` or make cashback idempotent + queueable.

11. `refund()` description concatenation `"Hoàn Gems: {$originalDebitTx->description} ({$reason})"` can exceed description column length if long reasons arrive. Add `Str::limit()`.

12. `legacyRefund()` path comment says "Chỉ chạy trong cửa sổ hoàn tiền" but legacy has no window — it will happily refund stale burn-model transactions indefinitely. Not wrong, but comment mismatches behavior.

13. `GemTransferException::refundOutsideWindow()` message hardcodes "24 giờ" but the window is `config('gems.refund_window_days')`. Message will lie if config changes.

## Low / Nice-to-have

14. `config/gems.php` defaults `transfer_enabled = false`; ensure the rollout phase plan explicitly records which env flips it.
15. `GemTransaction` model lacks a `scopeRefundable()` helper — would DRY up the `refundFor` lookup.
16. `IsPayable` trait only covers `getPayableReferenceType` / `getPayableReferenceId` — consider adding a default `getPayableDescription()` fallback.
17. Console Kernel comment correctly notes single-server assumption. Add a `TODO` to revisit when infra scales out.
18. Nothing validates `refund_window_days >= 0` at boot; negative config would make `available_at` in the past and release immediately.

## Security

- No raw SQL outside the ENUM→VARCHAR migration ALTER (safe, no user input).
- Authorization preserved: cancel endpoints still check stadium ownership / booking ownership before invoking refund.
- No secrets logged. Exception messages are Vietnamese but could still leak refType/refId (acceptable, they are internal identifiers).
- Idempotency enforced via `released_at`/`status` re-checks inside locks; looks sound.

## Vietnamese Diacritics

Spot-checked all user-facing strings in service, exceptions, controllers, command, migration comments — all correct with diacritics. Compliant with project rule.

## Test Coverage Gaps

- No test for re-enrollment after refund (critical #1).
- No test for supply conservation across transfer+refund with nonzero fee (critical #2).
- No deadlock / concurrent-transfer test (hard in feature tests; acceptable).
- No controller-level booking cancel integration tests (skipped per plan — missing Booking/Stadium/Court factories). Acceptable per YAGNI; flag as follow-up.
- No test for `refundFor` on a booking that was never paid with Gems (ModelNotFoundException swallow path).
- No test asserting 422 + Vietnamese message in the HomeYardTournamentController cancel path.
- Release command has no test for a mixed batch (some matured, some refunded, some immature) to prove idempotency across states.

Recommend adding the re-enrollment and supply-conservation tests before flipping the flag; the others are follow-ups.

## Positive Observations

- Double-wallet lock ordering by `user_id ASC` is textbook-correct.
- Invariant trait applied in setUp AND end of every test.
- Clear separation of transfer / refund / release responsibilities.
- Legacy fallback preserved and gated by flag — rollback is safe.
- `Payable` + `IsPayable` abstraction is small, purposeful, and immediately reused by Booking and ClubActivity.
- All exception messages in Vietnamese with diacritics.
- Release command is idempotent with in-tx re-check against `released_at`.

## Recommended Actions (priority order)

1. Fix unique index re-enrollment collision (critical #1) — index shape or service-level guard.
2. Decide conservation policy for nonzero platform fee; add a test pinning it (critical #2).
3. Tighten outer catch in `HomeYardTournamentController::cancelBooking` to not leak internals (critical #3).
4. Add re-enrollment integration test in `GemPaymentProcessorTest`.
5. Wrap processor `pay()` side effects in outer transaction OR make cashback idempotent.
6. Parameterize Vietnamese "24 giờ" message from config.
7. Use `errorInfo[1] === 1062` for duplicate detection.

## Score

7.5 / 10 — solid architecture, correct locking, good tests, but two real correctness bugs (unique index re-use, fee resurrection) must be fixed before flipping `GEMS_TRANSFER_ENABLED=true` in production.

## Unresolved Questions

- Q1: Is re-enrollment after cancel an actual product requirement? If yes, #1 is blocking. If RSVP→cancel→re-RSVP is disallowed at UI level, #1 downgrades to a defense-in-depth concern.
- Q2: What is the intended long-term semantics of `platform_fee_percent` on refund — should the platform absorb the fee loss (current behavior, violates supply conservation) or keep it burned (correct behavior)?
- Q3: Who monitors `gems:release-locked` failures? No alerting hook detected.
- Q4: Was the ENUM→VARCHAR down migration tested on a DB with live data? `migrate:fresh` is acceptable per plan but the down path must match production enum values.
