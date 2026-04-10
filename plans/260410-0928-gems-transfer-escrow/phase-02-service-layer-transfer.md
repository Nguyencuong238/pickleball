# Phase 02 — Service Layer: transfer()

## Context Links
- Service: `app/Services/GemWalletService.php`
- Config: `config/gems.php`
- Parent: `./plan.md` | Prev: `phase-01-schema-migrations.md`

## Overview
**Priority:** P1 | **Status:** pending | **Est:** 3h

Add `transfer()` method implementing double-entry payer→payee Gem movement with locked escrow on credit side. Add config keys. Add ownership resolvers.

## Key Insights
- Double wallet lock ordered by `user_id ASC` prevents deadlocks when concurrent inverse transfers happen.
- Counterparty back-linking: insert debit first (no counterparty_tx_id), insert credit WITH `counterparty_transaction_id=debit.id`, then UPDATE debit to set `counterparty_transaction_id=credit.id`. One UPDATE instead of two.
- `available_at` computed from `config('gems.refund_window_days')` × 24h — must be stored server-side at transfer time (not at refund time), so window stays stable even if config changes later.
- **refType format**: use FQCN strings (`\App\Models\Booking::class`, `\App\Models\ClubActivity::class`) — matches current `ClubActivityService::chargeGems` convention and supports Eloquent morph map later.
- **Platform fee semantics (phase 1)**: fee is **burned** — payer debited `$gems`, payee credited `$gems - $fee`, difference vanishes. Future phase may credit a platform wallet; schema already supports via `platform_fee` column tracking.
- **Cashback integration**: `GemCashbackService::award()` must still fire on payer's debit tx (type=`payment`) after successful transfer. Receipt type is NOT `payment` → owner correctly receives no cashback without additional logic.

## Requirements
**Functional**
- New method signature:
  ```php
  public function transfer(
      User $payer,
      User $payee,
      int $gems,
      string $refType,        // FQCN, e.g. \App\Models\Booking::class
      int $refId,
      string $desc,
      ?int $platformFeePercent = null   // null → read from config('gems.platform_fee_percent')
  ): array
  ```
- Hard blocks (throw `GemTransferException` — consistent domain exception):
  - `$payer->id === $payee->id` → `GemTransferException::selfPayment()`
  - `$payee === null` → `GemTransferException::missingOwner()`  (callers check before calling; also guarded in method)
  - `$gems <= 0` → `GemTransferException::invalidAmount()`
  - Payer spendable < gems → `GemTransferException::insufficientSpendable()`
- Resolve `$platformFeePercent` from config if null
- Compute platform fee: `$fee = intdiv($gems * $feePercent, 100)` (burned in phase 1)
- Debit payer: `balance -= $gems` (full amount)
- Credit payee: `balance += ($gems - $fee)`, `locked_balance += ($gems - $fee)`, `available_at = now()->addDays(config('gems.refund_window_days'))`
- Create 2 `gem_transactions` rows:
  - Debit: `type=payment`, `amount=-$gems`, `platform_fee=$fee`, `counterparty_user_id=$payee->id`, `status=completed`
  - Credit: `type=receipt`, `amount=+($gems-$fee)`, `counterparty_user_id=$payer->id`, `counterparty_transaction_id=$debit->id`, `available_at`, `status=completed`
- After insert, update debit row with `counterparty_transaction_id=$credit->id` (single UPDATE)
- Wrap in `DB::transaction()`; lock wallets via `lockForUpdate()` ordered by user_id ASC
- Return `['debit' => $debitTx, 'credit' => $creditTx]`

**Non-functional**
- Idempotent against retries via unique index `ux_gem_tx_ref_type_user` (already added in phase-01) — second call with same `(refType, refId, type=payment, user_id=payer)` triggers MySQL unique violation → wrapped as `GemTransferException::duplicateTransaction()`
- All writes within single DB transaction

## Architecture

```
transfer(payer, payee, gems, ref, desc, feePct)
  │
  ├─ validate (self, amount, owner)
  ├─ DB::transaction
  │   ├─ lock wallets ordered user_id ASC
  │   ├─ validate spendable
  │   ├─ compute fee + net
  │   ├─ debit payer (balance only)
  │   ├─ credit payee (balance + locked_balance, available_at)
  │   ├─ insert debitTx (type=payment)
  │   ├─ insert creditTx (type=receipt, available_at, counterparty_user_id=payer)
  │   ├─ update both with counterparty_transaction_id
  │   └─ return [debitTx, creditTx]
```

## Related Code Files
**Modify**
- `app/Services/GemWalletService.php` — add `transfer()`, helper `lockWalletsOrdered()`
- `config/gems.php` — add `refund_window_days`, `platform_fee_percent`, `transfer_enabled`

**Create**
- `app/Exceptions/GemTransferException.php` — domain exception for transfer failures

## Implementation Steps
1. Extend `config/gems.php`:
   ```php
   'refund_window_days' => (int) env('GEMS_REFUND_WINDOW_DAYS', 1),
   'platform_fee_percent' => (int) env('GEMS_PLATFORM_FEE_PERCENT', 0),
   'transfer_enabled' => (bool) env('GEMS_TRANSFER_ENABLED', false),
   ```
2. Create `GemTransferException extends \RuntimeException` with static factories: `selfPayment()`, `missingOwner()`, `missingPayer()`, `insufficientSpendable(int $have, int $need)`, `invalidAmount(int $gems)`, `duplicateTransaction(string $refType, int $refId)`. Each returns instance with Vietnamese user-facing message (diacritics) in exception message.
3. In `GemWalletService` add private helper:
   ```php
   private function lockWalletsOrdered(int $userIdA, int $userIdB): array {
       [$first, $second] = $userIdA < $userIdB ? [$userIdA, $userIdB] : [$userIdB, $userIdA];
       $w1 = GemWallet::where('user_id', $first)->lockForUpdate()->firstOrFail();
       $w2 = GemWallet::where('user_id', $second)->lockForUpdate()->firstOrFail();
       return [$w1->user_id === $userIdA ? $w1 : $w2, $w1->user_id === $userIdB ? $w1 : $w2];
   }
   ```
4. Implement `transfer()`:
   - Pre-validation outside transaction (cheap throws)
   - Call `getOrCreateWallet($payer)`, `getOrCreateWallet($payee)` before transaction to avoid nested creates
   - Open `DB::transaction`
   - Lock wallets ordered
   - Re-check spendable inside lock
   - Apply math, save wallets
   - Compose debit + credit row arrays, insert both
   - After insert, run `->update(['counterparty_transaction_id' => ...])` on each
5. Catch `QueryException` (SQLSTATE 23000 unique violation) from the inserts → rethrow as `GemTransferException::duplicateTransaction()` for idempotent retry safety.
6. Run `php -l` on modified files; run `composer dump-autoload`.

## Todo List
- [ ] Extend config/gems.php with 3 new keys
- [ ] Create GemTransferException class with 5 factory methods
- [ ] Add lockWalletsOrdered helper
- [ ] Implement transfer() method (with QueryException→duplicateTransaction wrap)
- [ ] Lint check (php -l)
- [ ] Manual tinker test: transfer 100 gems between two users; verify counterparty back-link; verify second call throws duplicate

## Success Criteria
- `transfer()` returns `[debitTx, creditTx]` with linked counterparty IDs
- Payer `balance` decreases by exactly `$gems`
- Payee `balance` and `locked_balance` both increase by `$gems - fee`
- `available_at = now + window` on credit row
- All hard-block scenarios throw `GemTransferException`
- Concurrent inverse transfers (A→B and B→A) do not deadlock

## Risk Assessment
- **Duplicate transfer on retry**: mitigated by unique index `ux_gem_tx_ref_type_user` (phase-01) + QueryException catch.
- **Deadlock on double lock**: mitigated by ordered lock on `user_id ASC`.
- **Floor rounding of fee**: `intdiv` used; fee=0 by default so moot at rollout. Fee bị burn — document rõ trong changelog.
- **Cashback double-fire**: caller must invoke `GemCashbackService::award($debitTx)` exactly once after `transfer()` returns. Do NOT call inside `transfer()` to keep service pure.

## Security Considerations
- All validation server-side; no client-supplied amounts trusted
- Exception messages must not leak other users' wallet data

## Next Steps
- Phase 03: refund + release command use transfer's credit rows
