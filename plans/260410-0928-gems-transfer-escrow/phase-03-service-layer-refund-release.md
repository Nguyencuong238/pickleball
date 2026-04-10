# Phase 03 — Service Layer: refund() + Release Command

## Context Links
- Service: `app/Services/GemWalletService.php`
- Kernel: `app/Console/Kernel.php`
- Parent: `./plan.md` | Prev: `phase-02-service-layer-transfer.md`

## Overview
**Priority:** P1 | **Status:** pending | **Est:** 3h

Rewrite `refund()` to clawback from owner's locked balance. Add `gems:release-locked` console command + schedule.

## Key Insights
- Refund must be idempotent: second call must fail cleanly (not double-credit).
- Release job and refund compete on same row; both must check `released_at IS NULL` inside a locked transaction.
- Use `chunkById` + `withoutOverlapping` on schedule to avoid stampede.
- **Verified**: project `CACHE_DRIVER=file` → `onOneServer()` silently no-ops (file driver lacks atomic lock). Do NOT use `onOneServer()`; rely on `withoutOverlapping()` which uses local cache file — adequate for single-server deploy. If multi-server deploy added later, switch `CACHE_DRIVER=database` and re-enable `onOneServer()`.
- **refund() signature**: caller must pre-fetch the debit `GemTransaction` (type=`payment`) via `reference_type+reference_id+user_id=payer` lookup. For Booking, no dedicated column exists — query via unique index. For ClubActivity, `ClubActivityParticipant.gem_transaction_id` already stores the ID (use it directly).

## Requirements
**Functional — refund()**
- Signature: `refund(GemTransaction $originalDebitTx, ?string $reason = null): array`
- Lookup credit counterparty via `counterparty_transaction_id`
- Hard blocks:
  - Credit tx not found → throw
  - Credit tx `released_at !== null` → throw (outside window)
  - Credit tx already `status='refunded'` → throw (idempotent guard)
- Lock both wallets ordered; re-check guards inside lock
- Credit payer balance +amount
- Decrement payee `balance -= amount`, `locked_balance -= amount`
- Mark creditTx `released_at = now()`, `status = 'refunded'`
- Mark debitTx `status = 'refunded'`
- Insert 2 audit rows: `refund` (payer side), `refund_clawback` (payee side) — both linked via counterparty
- Return `[refundTx, clawbackTx]`

**Functional — release command**
- Signature: `php artisan gems:release-locked`
- Query `gem_transactions WHERE type='receipt' AND available_at <= NOW() AND released_at IS NULL`
- Chunk by 500 using `chunkById`
- For each row, open transaction, lock the receiving wallet, re-check `released_at IS NULL`, decrement `locked_balance` by `amount`, set `released_at = now()`
- Log released count
- Schedule every 5 minutes, `withoutOverlapping(10)`

**Non-functional**
- Release job idempotent: running twice releases nothing extra
- Safe under concurrent refund: whichever wins sets `released_at`; loser throws/skips

## Architecture
```
refund(debitTx)
  ├─ find creditTx via counterparty link
  ├─ pre-check released_at, status
  ├─ DB::transaction
  │   ├─ lock wallets ordered
  │   ├─ re-check released_at, status
  │   ├─ credit payer balance
  │   ├─ decrement payee balance + locked_balance
  │   ├─ update creditTx (released_at, status)
  │   ├─ update debitTx (status)
  │   ├─ insert refundTx (payer)
  │   ├─ insert clawbackTx (payee)
  │   └─ return [refundTx, clawbackTx]

release-locked (cron 5min)
  └─ GemTransaction::where('type', receipt)->whereNull('released_at')->where('available_at', '<=', now())
      └─ chunkById(500)
          └─ foreach row: DB::transaction { lock wallet → re-check → decrement locked → set released_at }
```

## Related Code Files
**Modify**
- `app/Services/GemWalletService.php` — rewrite `refund()`, add `releaseReceipt(GemTransaction $receiptTx)` helper
- `app/Console/Kernel.php` — register schedule

**Create**
- `app/Console/Commands/GemsReleaseLockedCommand.php`

## Implementation Steps
1. Rewrite `GemWalletService::refund()` per requirements above. Replace existing body entirely (old behavior credits payer only — incompatible).
2. Extract `releaseReceipt(GemTransaction $receiptTx): bool` helper that performs lock + decrement + set released_at inside its own DB::transaction. Returns true if released, false if already released (race loser).
3. Create `app/Console/Commands/GemsReleaseLockedCommand.php`:
   ```php
   protected $signature = 'gems:release-locked';
   protected $description = 'Release matured locked Gem receipts to spendable balance';
   public function handle(GemWalletService $svc): int {
       $count = 0;
       GemTransaction::query()
           ->where('type', GemTransaction::TYPE_RECEIPT)
           ->whereNull('released_at')
           ->where('available_at', '<=', now())
           ->chunkById(500, function ($rows) use ($svc, &$count) {
               foreach ($rows as $row) {
                   if ($svc->releaseReceipt($row)) $count++;
               }
           });
       $this->info("Released {$count} receipts");
       return self::SUCCESS;
   }
   ```
4. Register in `Kernel::schedule()` (omit `onOneServer()` — CACHE_DRIVER=file incompatible):
   ```php
   $schedule->command('gems:release-locked')
       ->everyFiveMinutes()
       ->withoutOverlapping(10);
   // Note: add ->onOneServer() when CACHE_DRIVER switches to database/redis for multi-server.
   ```
5. Manual tinker test: create a receipt tx with `available_at = now()->subMinute()`, run command, verify `released_at` set + wallet `locked_balance` decreased.

## Todo List
- [ ] Rewrite refund() with clawback logic
- [ ] Extract releaseReceipt() helper
- [ ] Create GemsReleaseLockedCommand
- [ ] Register in Kernel schedule
- [ ] Manual tinker verification
- [ ] Lint check (php -l)

## Success Criteria
- `refund()` within window: payer credited, payee `balance` + `locked_balance` both decreased
- `refund()` after release: throws cleanly
- `refund()` second call: throws (idempotency guard)
- Release job: moves matured receipts to spendable (decrements locked_balance only, leaves `balance` intact)
- Release job twice: second pass releases nothing
- Concurrent refund + release: invariants hold, no double-clawback

## Risk Assessment
- **Race refund vs release**: mitigated by checking `released_at` inside lock; loser sees updated value.
- **Large chunk causing long lock**: chunkById 500 + per-row transaction keeps locks short.
- **Clock skew across servers**: `available_at` comparison uses DB `NOW()` — acceptable.
- **Multi-server scheduler duplicate runs**: `withoutOverlapping` uses local file cache — safe on single server only. Flag for DevOps when scaling horizontally.
- **Legacy burn-model rows**: N/A — dev phase, no legacy data. `refund()` assumes every payment tx has `counterparty_transaction_id` set. If NULL encountered → throw `LogicException` (data corruption signal, should never happen).

## Security Considerations
- Command runs as CLI; no external inputs
- Refund can only be triggered by controllers that have authenticated context (not exposed as public API)

## Next Steps
- Phase 04: wire controllers to call transfer() + refund()
