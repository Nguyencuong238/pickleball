# Phase 1: Database & Config

## Context
- [Brainstorm](../reports/brainstorm-260403-0958-onepickleball-card-mvp.md)
- Existing pattern: `database/migrations/2025_01_05_create_user_wallets_table.php`

## Overview
- **Priority**: P1 (foundation for all other phases)
- **Status**: Pending
- **Effort**: 3h
- Create gem_wallets + gem_transactions tables, GemWallet/GemTransaction models, config file

## Key Insights
- Existing UserWallet uses `points` (integer). New system uses `balance` (bigint) for Gems
- Booking model already has `payment_method` enum including 'wallet'
- Use same lockForUpdate pattern as booking_code generation

## Requirements

### Functional
- gem_wallets: 1 wallet per user, stores Gems balance
- gem_transactions: full audit trail with polymorphic references
- Config: exchange rate, cashback %, min/max top-up from .env

### Non-Functional
- bigint for balance (supports large values)
- Unique constraint on user_id in gem_wallets
- Index on gem_transactions(user_id, created_at) for history queries

## Architecture

```
gem_wallets
├── id (bigint, PK)
├── user_id (bigint, FK users, unique)
├── balance (bigint, default 0, unsigned)
├── created_at
└── updated_at

gem_transactions
├── id (bigint, PK)
├── user_id (bigint, FK users, index)
├── gem_wallet_id (bigint, FK gem_wallets)
├── type (enum: top_up, payment, refund, admin_adjust)
├── amount (bigint, signed: + credit, - debit)
├── balance_after (bigint, snapshot)
├── reference_type (nullable, string - polymorphic)
├── reference_id (nullable, bigint - polymorphic)
├── description (string, nullable)
├── metadata (json, nullable)
├── status (enum: pending, completed, failed, cancelled)
├── created_at
└── updated_at
```

## Related Code Files

### Create
- `database/migrations/2026_04_03_create_gem_wallets_table.php`
- `database/migrations/2026_04_03_create_gem_transactions_table.php`
- `app/Models/GemWallet.php`
- `app/Models/GemTransaction.php`
- `config/gems.php`

### Modify
- `app/Models/User.php` - add gemWallet() + gemTransactions() relationships
- `.env.example` - add GEMS_* variables

## Implementation Steps

1. Create migration `create_gem_wallets_table`
   - id, user_id (unique FK), balance (bigint unsigned default 0), timestamps
   
2. Create migration `create_gem_transactions_table`
   - id, user_id (FK), gem_wallet_id (FK), type enum, amount bigint, balance_after bigint
   - reference_type + reference_id (nullable polymorphic)
   - description, metadata (json), status enum, timestamps
   - Index: (user_id, created_at), (status), (reference_type, reference_id)

3. Create `GemWallet` model
   - fillable: user_id, balance
   - casts: balance -> integer
   - Relations: user(), transactions()
   - NO business logic here (lives in service)

4. Create `GemTransaction` model
   - fillable: user_id, gem_wallet_id, type, amount, balance_after, reference_type, reference_id, description, metadata, status
   - casts: amount -> integer, balance_after -> integer, metadata -> array
   - Relations: user(), wallet(), reference() (morphTo)
   - Scopes: completed(), pending(), byType()

5. Create `config/gems.php`
   ```php
   return [
       'exchange_rate' => (int) env('GEMS_EXCHANGE_RATE', 1000),
       'cashback_percent' => (int) env('GEMS_CASHBACK_PERCENT', 5),
       'min_topup_vnd' => (int) env('GEMS_MIN_TOPUP_VND', 50000),
       'max_topup_vnd' => (int) env('GEMS_MAX_TOPUP_VND', 5000000),
       'sepay' => [
           'account_number' => env('SEPAY_ACCOUNT_NUMBER'),
           'bank_code' => env('SEPAY_BANK_CODE'),
           'api_key' => env('SEPAY_API_KEY'),
           'allowed_ips' => env('SEPAY_ALLOWED_IPS', '14.225.204.68,103.163.218.2,103.163.218.66,103.163.218.146,103.163.218.147,14.225.204.130'),
       ],
   ];
   ```

6. Add to User model:
   ```php
   public function gemWallet(): HasOne { return $this->hasOne(GemWallet::class); }
   public function gemTransactions(): HasMany { return $this->hasMany(GemTransaction::class); }
   ```

7. Add to `.env.example`:
   ```
   GEMS_EXCHANGE_RATE=1000
   GEMS_CASHBACK_PERCENT=5
   GEMS_MIN_TOPUP_VND=50000
   GEMS_MAX_TOPUP_VND=5000000
   SEPAY_ACCOUNT_NUMBER=
   SEPAY_BANK_CODE=
   SEPAY_API_KEY=
   SEPAY_ALLOWED_IPS=
   ```

8. Run `php artisan migrate` to verify

## Todo List
- [ ] Create gem_wallets migration
- [ ] Create gem_transactions migration
- [ ] Create GemWallet model
- [ ] Create GemTransaction model
- [ ] Create config/gems.php
- [ ] Add relationships to User model
- [ ] Update .env.example
- [ ] Run migrate and verify

## Success Criteria
- Migrations run without error
- Models have correct relations and casts
- Config values load from .env
- No impact on existing UserWallet/points system

## Risk Assessment
- Low risk phase, standard Laravel patterns
- Ensure unique constraint on gem_wallets.user_id prevents duplicates

## Security Considerations
- SePay API key in .env only, never committed
- balance unsigned prevents negative values at DB level
