# Phase 06 — Feature Tests (24 cases)

## Context Links
- Parent: `./plan.md` | Deps: phases 02.5, 03, 04
- Memory note: Gems payment logic has ZERO existing test coverage

## Overview
**Priority:** P1 | **Status:** pending | **Est:** 5h

First real test suite for the Gems subsystem. Cover transfer, refund, release command, controller integration, invariants. No mocks of the database — use `RefreshDatabase` with real MySQL/SQLite.

## Key Insights
- Database-backed tests mandatory (memory: user explicitly against mocking DB).
- Invariant assertion helper reused across tests keeps them DRY.
- Time manipulation via `Carbon::setTestNow()` to advance past refund window.

## Requirements
**Functional — 21 test cases**

**Transfer tests** (`tests/Feature/Gems/GemTransferTest.php`)
1. `transfer_happy_path_credits_payee_with_lock` — verify balance+locked_balance both increased on payee, only balance decreased on payer
2. `transfer_throws_on_self_payment`
3. `transfer_throws_when_owner_null`
4. `transfer_throws_when_owner_cannot_spend_locked_gems` — payer has balance=100, locked_balance=100 (spendable=0), attempt transfer 50 → throws `insufficientSpendable`
5. `transfer_throws_on_non_positive_amount`
6. `transfer_respects_platform_fee_percent` — 10% fee: payer -100, payee +90, 10 burned (not credited anywhere)
7. `transfer_back_links_counterparty_transaction_ids` — both debit and credit rows have counterparty_transaction_id pointing at each other
8. `transfer_duplicate_throws_via_unique_index` — call transfer twice with same (refType, refId) → second throws `duplicateTransaction`

**Refund tests** (`tests/Feature/Gems/GemRefundTest.php`)
9. `refund_within_window_credits_payer_and_clawsback_payee`
10. `refund_throws_after_release` (set `released_at` manually → expect throw)
11. `refund_throws_when_already_refunded` (double call)
12. `refund_preserves_invariants`

**Release command tests** (`tests/Feature/Gems/GemsReleaseLockedCommandTest.php`)
13. `release_command_unlocks_matured_receipts`
14. `release_command_idempotent_on_second_run`
15. `release_command_skips_immature_receipts`

**Payment processor tests** (`tests/Feature/Gems/GemPaymentProcessorTest.php`)
16. `processor_pay_uses_payable_getPayer_when_no_explicit_payer` — Booking case
17. `processor_pay_throws_missing_payer_when_both_null` — ClubActivity without explicit payer
18. `processor_refundFor_locates_debit_via_unique_index_and_clawsback`

**Controller/integration tests** (`tests/Feature/Gems/BookingGemPaymentTest.php`, `ClubActivityGemPaymentTest.php`)
19. `booking_payment_transfers_to_stadium_owner_when_flag_on` — verify stadium.user wallet receives locked gems
20. `booking_cancellation_within_window_refunds_full_chain` — call cancel endpoint, verify payer refunded + owner clawed back, payer cashback NOT reversed (by design — cashback is in Points wallet)
21. `booking_cancellation_after_window_returns_422_vietnamese_message` — advance time past window via `Carbon::setTestNow()`, cancel → expect 422 with "Không thể hoàn Gems sau 24 giờ..." message
22. `club_activity_payment_transfers_to_club_owner_via_explicit_payer`
23. `club_activity_cancellation_refunds_full_chain`
24. `feature_flag_off_still_uses_burn_model` — smoke test confirming fallback path works

**Invariant helper** (`tests/Feature/Gems/Concerns/AssertsWalletInvariants.php`)
- Single assertion method: `assertWalletInvariants(int $userId)` checks `balance >= 0 && locked_balance >= 0 && locked_balance <= balance`
- Called at start and end of every test

**Non-functional**
- Use `RefreshDatabase` trait
- No sleep-based concurrency tests; simulate via direct DB state manipulation
- All tests must pass with `php artisan test --testsuite=Feature --filter=Gems`

## Architecture
```
tests/Feature/Gems/
  ├─ Concerns/AssertsWalletInvariants.php  (trait)
  ├─ GemTransferTest.php                    (8 tests)
  ├─ GemRefundTest.php                      (4 tests)
  ├─ GemsReleaseLockedCommandTest.php       (3 tests)
  ├─ GemPaymentProcessorTest.php            (3 tests)
  ├─ BookingGemPaymentTest.php              (3 tests)
  └─ ClubActivityGemPaymentTest.php         (3 tests)
```

## Related Code Files
**Create**
- 5 test files + 1 trait (above)
- `database/factories/GemWalletFactory.php` if missing
- `database/factories/GemTransactionFactory.php` if missing

**Modify**
- `phpunit.xml` — ensure `Feature` testsuite includes `tests/Feature/Gems/`

## Implementation Steps
1. Grep for existing factories: `Glob "database/factories/Gem*.php"`. Create if missing.
2. Create `AssertsWalletInvariants` trait with helper method.
3. Implement `GemTransferTest` — 7 tests, each using real users + wallets.
4. Implement `GemRefundTest` — use `Carbon::setTestNow()` to control time; use direct DB updates to simulate already-released state.
5. Implement `GemsReleaseLockedCommandTest` — insert receipt tx with `available_at` in past, call `$this->artisan('gems:release-locked')`, assert outcome.
6. Implement `BookingGemPaymentTest` — use existing Booking/Court/Stadium factories; toggle `config(['gems.transfer_enabled' => true])` per test.
7. Implement `ClubActivityGemPaymentTest` — same pattern with ClubActivity/Club factories.
8. Run `php artisan test --filter=Gems`; iterate until green.
9. Check coverage: target >80% on `GemWalletService`.

## Todo List
- [ ] Verify/create factories
- [ ] AssertsWalletInvariants trait
- [ ] GemTransferTest (8)
- [ ] GemRefundTest (4)
- [ ] GemsReleaseLockedCommandTest (3)
- [ ] GemPaymentProcessorTest (3)
- [ ] BookingGemPaymentTest (3)
- [ ] ClubActivityGemPaymentTest (3)
- [ ] All green
- [ ] Coverage spot-check

## Success Criteria
- 24 new tests passing
- Zero uses of fake/mock data for DB operations
- Invariants asserted at every test's start + end
- `php artisan test` overall still green
- Coverage of `GemWalletService::transfer/refund/releaseReceipt` > 80%

## Risk Assessment
- **Factory gaps** for Booking/Court/Stadium chain — may require creating or reusing test helpers
- **Slow test suite** from real DB — acceptable; bounded to Gems namespace
- **Time-based flakiness** — use `Carbon::setTestNow()` strictly, never `sleep()`

## Security Considerations
- Test users use unique random emails to avoid unique constraint collisions

## Next Steps
- Phase 07: rollout flag on after tests green
