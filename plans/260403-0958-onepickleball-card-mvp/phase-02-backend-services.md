# Phase 2: Backend Services

## Context
- [Brainstorm](../reports/brainstorm-260403-0958-onepickleball-card-mvp.md)
- [SePay Research](../reports/research-260403-0958-sepay-integration.md)
- Depends on: Phase 1 (models + config)

## Overview
- **Priority**: P1
- **Status**: Pending
- **Effort**: 6h
- Implement GemWalletService, SepayService, CashbackService

## Key Insights
- SePay QR: `https://qr.sepay.vn/img?accountNumber=X&bankCode=X&amount=X&description=X`
- Webhook: POST JSON, verify via IP whitelist + API key header
- Transfer content pattern for matching: `GEMS{userId}T{txId}`
- Existing UserWallet::addPoints() for cashback delivery
- lockForUpdate pattern already used in booking_code generation

## Requirements

### Functional
- Create/get wallet lazily on first access
- Top-up: generate SePay QR, handle webhook, credit Gems
- Deduct: check balance, lock, debit, log transaction
- Refund: reverse a completed payment transaction
- Cashback: calculate % of Gems spent, award Points

### Non-Functional
- All balance mutations inside DB::transaction + lockForUpdate
- Idempotent webhook (dedup by sepay transaction id)
- Webhook response < 8 seconds (SePay requirement)

## Architecture

```
Top-up Flow:
  User -> GemController::topUp -> GemWalletService::createTopUpRequest
    -> Generate pending GemTransaction
    -> Build SePay QR URL
    -> Return QR + tx info

  SePay -> SepayWebhookController::handle -> SepayService::handleWebhook
    -> Verify IP + API key
    -> Parse transfer content -> extract userId + txId
    -> Match pending transaction
    -> DB::transaction { credit wallet, update tx status }
    -> CashbackService (no cashback on top-up)

Payment Flow:
  User -> BookingController -> GemWalletService::deduct
    -> DB::transaction {
         lockForUpdate wallet
         Check balance >= amount
         Debit Gems, snapshot balance_after
         Create GemTransaction(type=payment, ref=Booking)
       }
    -> CashbackService::award (% of Gems -> UserWallet points)
```

## Related Code Files

### Create
- `app/Services/GemWalletService.php`
- `app/Services/SepayService.php`
- `app/Services/GemCashbackService.php`
- `app/Http/Middleware/VerifySepayWebhook.php`

### Reference (read-only)
- `app/Models/UserWallet.php` - addPoints() method for cashback
- `app/Services/PointEarningService.php` - pattern reference

## Implementation Steps

### GemWalletService (~80 lines)

1. `getOrCreateWallet(User $user): GemWallet`
   - Return existing or create with balance=0

2. `getBalance(User $user): int`
   - Return wallet balance (0 if no wallet)

3. `createTopUpRequest(User $user, int $amountVnd): array`
   - Validate min/max from config
   - Calculate gems = $amountVnd / config('gems.exchange_rate')
   - Create GemTransaction(type=top_up, amount=+gems, status=pending)
   - Build SePay QR URL with description=`GEMS{userId}T{txId}`
   - Return ['transaction' => $tx, 'qr_url' => $url, 'amount_vnd' => $amountVnd, 'gems' => $gems]

4. `confirmTopUp(GemTransaction $transaction): void`
   - DB::transaction with lockForUpdate on wallet
   - Increment balance, set balance_after, update status=completed

5. `deduct(User $user, int $gems, string $refType, int $refId, string $desc): GemTransaction`
   - DB::transaction with lockForUpdate on wallet
   - Check balance >= gems, throw InsufficientGemsException if not
   - Decrement balance, create GemTransaction(type=payment, amount=-gems, status=completed)
   - Set balance_after snapshot

6. `refund(GemTransaction $originalTx): GemTransaction`
   - Verify original is completed payment
   - DB::transaction: increment balance, create GemTransaction(type=refund, amount=+abs)

### SepayService (~60 lines)

1. `buildQrUrl(int $amountVnd, string $description): string`
   - Construct `https://qr.sepay.vn/img?accountNumber=X&bankCode=X&amount=X&description=X`

2. `handleWebhook(array $payload): void`
   - Extract transfer content from payload['content']
   - Parse `GEMS{userId}T{txId}` pattern via regex
   - Find pending GemTransaction by id + user_id
   - Validate amount matches (payload transferAmount / exchange_rate == tx amount)
   - Call GemWalletService::confirmTopUp
   - Log success

3. `isValidPayload(array $payload): bool`
   - Check required fields: id, content, transferAmount, transferType='in'

### GemCashbackService (~30 lines)

1. `award(GemTransaction $transaction): void`
   - Only for type=payment, status=completed
   - Calculate points = abs($transaction->amount) * config('gems.cashback_percent') / 100
   - If points > 0: get/create UserWallet, call addPoints(points, 'gems_cashback', description, metadata)

### VerifySepayWebhook Middleware (~30 lines)

1. Check request IP against config('gems.sepay.allowed_ips')
2. Check Authorization header matches `Apikey {config('gems.sepay.api_key')}`
3. Return 403 if either fails

## Todo List
- [ ] Create GemWalletService with wallet CRUD + balance ops
- [ ] Create SepayService with QR URL builder + webhook handler
- [ ] Create GemCashbackService with award logic
- [ ] Create VerifySepayWebhook middleware
- [ ] Register middleware in Kernel.php

## Success Criteria
- GemWalletService: create wallet, top-up, deduct, refund all work with proper locking
- SepayService: builds correct QR URL, parses webhook, matches transactions
- CashbackService: awards correct % Points to UserWallet
- No race conditions on concurrent balance operations
- Idempotent webhook handling (same payload twice = no double credit)

## Risk Assessment
- **Race condition**: Mitigated by lockForUpdate inside DB::transaction
- **Webhook replay**: Check tx status before processing (only pending -> completed)
- **Amount mismatch**: Validate webhook amount matches pending tx amount
- **SePay downtime**: Pending tx can be manually resolved by admin later

## Security Considerations
- IP whitelist for webhook endpoint (6 SePay IPs)
- API key verification in Authorization header
- No user auth on webhook route (public but IP-restricted)
- All financial operations in DB transactions
