# Brainstorm: OnePickleball Card - MVP

## Problem Statement
Implement OnePickleball Card membership system. MVP scope: Gems wallet + SePay top-up + booking payment with Gems + fixed % point cashback.

## Key Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Wallet architecture | New `gem_wallets` table (separate from existing UserWallet/points) | User wants clean separation, points system untouched |
| Payment gateway | SePay (VietQR) | Popular VN, low fee, skill available. User needs to register first |
| Gems exchange rate | Configurable via `config/gems.php` + `.env` | Flexible, easy to change without code deploy |
| Withdrawal | Not allowed | Lock-in ecosystem per requirement |
| Cashback | Fixed % from config | Simple, MVP-appropriate. Points go to existing UserWallet |
| MVP scope | Wallet + Top-up + Booking payment | Foundation first, expand later |

## Architecture

### New Database Tables

**`gem_wallets`**
- id, user_id (unique FK), balance (bigint, unit: gems), created_at, updated_at
- Balance stored as integer (no decimals). Use DB-level locking for concurrency.

**`gem_transactions`**
- id, user_id (FK), wallet_id (FK)
- type: enum(top_up, payment, refund, admin_adjust)
- amount (signed int: + for credit, - for debit)
- balance_after (snapshot for audit)
- reference_type, reference_id (polymorphic: Booking, Tournament, etc.)
- description, metadata (json)
- status: enum(pending, completed, failed, cancelled)
- created_at, updated_at

### Config File

```php
// config/gems.php
return [
    'exchange_rate' => env('GEMS_EXCHANGE_RATE', 1000), // 1 Gem = 1000 VND default
    'cashback_percentage' => env('GEMS_CASHBACK_PERCENT', 5), // 5% default
    'min_topup_vnd' => env('GEMS_MIN_TOPUP', 50000),
    'max_topup_vnd' => env('GEMS_MAX_TOPUP', 5000000),
];
```

### Services

**GemWalletService**
- `getOrCreateWallet(User)` - Lazy create wallet on first access
- `getBalance(User): int`
- `topUp(User, amountVnd, transactionRef): GemTransaction` - Convert VND->Gems, credit
- `deduct(User, gems, referenceType, referenceId, desc): GemTransaction` - Check balance, lock, deduct
- `refund(GemTransaction): GemTransaction` - Reverse a payment transaction

**SepayService**
- `createTopUpRequest(User, amountVnd): array` - Generate QR code + pending transaction
- `handleWebhook(payload): void` - Verify signature, match pending tx, call topUp
- `verifySignature(payload): bool`

**CashbackService**
- `awardCashback(GemTransaction): void` - Calculate % -> add to UserWallet (existing points system)

### Payment Flows

**Top-up Flow (VND -> Gems)**
```
User requests top-up (amount VND)
-> Validate min/max
-> Create pending gem_transaction (status=pending)
-> SepayService generates QR with unique ref (content = "GEMS{userId}T{txId}")
-> Return QR to user
-> [Async] SePay webhook confirms payment
-> Verify signature + match transaction
-> DB::transaction: update tx status=completed, credit wallet, snapshot balance_after
-> Notify user (optional)
```

**Booking Payment Flow (Gems -> Court)**
```
User selects court + time + "Pay with Gems"
-> Calculate total Gems needed (total_price_vnd / exchange_rate)
-> Check balance >= required
-> DB::transaction {
     lockForUpdate wallet
     Deduct Gems (gem_transaction type=payment, ref=Booking)
     Create/confirm booking (payment_method=wallet)
     Award cashback points (CashbackService -> UserWallet)
   }
-> Return confirmed booking
```

### Integration Points

**Existing Booking System:**
- `payment_method` already supports `wallet` enum value
- Modify BookingController to handle wallet payment path
- Add Gems amount display alongside VND pricing

**Existing Points System:**
- CashbackService calls `UserWallet::addPoints()` (existing method)
- New point transaction type: `gems_cashback`
- No changes to UserWallet model needed

### File Changes Estimate

**New Files (~8):**
- Migration: `create_gem_wallets_table`, `create_gem_transactions_table`
- Model: `GemWallet`, `GemTransaction`
- Service: `GemWalletService`, `SepayService`, `CashbackService`
- Config: `config/gems.php`
- Controller: `Api/GemController` (or extend existing)

**Modified Files (~4-6):**
- BookingController(s) - add Gems payment path
- Booking views - show Gems option + balance
- Routes - new API endpoints
- `.env.example` - SePay keys + Gems config

### API Endpoints (MVP)

```
GET    /api/wallet/gems          - Get balance + recent transactions
POST   /api/wallet/gems/topup    - Request top-up, get QR
POST   /api/webhook/sepay        - SePay callback (no auth)
GET    /api/wallet/gems/history   - Transaction history (paginated)
```

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| Race condition on balance | DB::transaction + lockForUpdate (existing pattern in project) |
| SePay webhook replay | Idempotent: check tx status before processing, unique ref matching |
| Incorrect balance | balance_after snapshot on every tx, reconciliation query possible |
| SePay account not ready | Can develop with mock webhook, switch to real when account ready |
| Exchange rate change mid-transaction | Lock rate at tx creation time, store in metadata |

## Out of MVP Scope (Phase 2+)
- Digital Card UI (profile page with QR)
- NFC physical card integration
- Tournament/League/Social payment with Gems
- Reward engine (variable cashback rules)
- Point redemption system
- Admin dashboard for Gems management
- Gems gifting between users
- Campaign/promotion engine

## Success Criteria
- User can top-up Gems via SePay QR
- User can pay for court booking with Gems
- Balance correctly debited, transaction logged
- Cashback points awarded to existing UserWallet
- No double-spend or race conditions
- Webhook handles duplicate calls safely

## Unresolved Questions
1. SePay account registration timeline - blocks real testing
2. Should admin be able to manually adjust Gems balance? (recommended: yes, with audit log)
3. Gems display in UI - show both Gems and VND equivalent, or Gems only?
