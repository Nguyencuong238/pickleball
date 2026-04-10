# Phase 01 — Schema Migrations

## Context Links
- Models: `app/Models/GemWallet.php`, `app/Models/GemTransaction.php`
- Tables: `gem_wallets`, `gem_transactions`
- Parent plan: `./plan.md`

## Overview
**Priority:** P1 | **Status:** completed | **Est:** 1h

Add `locked_balance` to `gem_wallets` and escrow/audit columns to `gem_transactions`. Backward compatible — all new columns nullable or default 0.

## Key Insights
- **Verified**: `gem_transactions.type` is ENUM `['top_up','payment','refund','admin_adjust']`, `status` is ENUM `['pending','completed','failed','cancelled']` — both need extension.
- **Verified**: no unique constraint on `(reference_type, reference_id, type)` — idempotency is application-only. Add composite unique to harden.
- **Verified**: `amount` is signed `bigInteger`, `balance_after` is `unsignedBigInteger` (DB rejects negative — good invariant guard).
- Dev phase: no production data → `migrate:fresh` acceptable. Still create NEW migration files (not edit originals) to preserve clean history.
- **Decision**: convert `type` and `status` from ENUM → VARCHAR(32) in this migration. Avoids painful ENUM ALTERs when adding future types (e.g. `withdraw`, `withdraw_refund`). Validate at application level via model constants.
- Indexes critical: release command will scan by `(type, available_at, released_at)`.
- `counterparty_transaction_id` enables back-linking debit↔credit for refund clawback.

## Requirements
**Functional**
- Add `gem_wallets.locked_balance` INT UNSIGNED NOT NULL DEFAULT 0 (after `balance`)
- Add `gem_transactions.counterparty_user_id` BIGINT UNSIGNED NULL (FK → users.id, ON DELETE SET NULL)
- Add `gem_transactions.counterparty_transaction_id` BIGINT UNSIGNED NULL (FK → gem_transactions.id, ON DELETE SET NULL)
- Add `gem_transactions.platform_fee` INT UNSIGNED NOT NULL DEFAULT 0
- Add `gem_transactions.available_at` TIMESTAMP NULL
- Add `gem_transactions.released_at` TIMESTAMP NULL
- Convert `gem_transactions.type` ENUM → VARCHAR(32) via raw `DB::statement` (new accepted values: `top_up`, `payment`, `refund`, `admin_adjust`, `receipt`, `refund_clawback`)
- Convert `gem_transactions.status` ENUM → VARCHAR(20) via raw `DB::statement` (new accepted values: `pending`, `completed`, `failed`, `cancelled`, `refunded`)
- Indexes:
  - `idx_release_scan (type, available_at, released_at)`
  - `idx_counterparty (counterparty_user_id)`
  - Unique `ux_gem_tx_ref_type_user (reference_type, reference_id, type, user_id)` — idempotency guard for debit/credit per reference per party
- Extend model constants to support new types/statuses

**Non-functional**
- Dev env: `migrate:fresh` acceptable, no backfill needed
- Rollback safe (down() drops cols/indexes, restores ENUM definitions)

## Architecture
Two new ALTER migrations, ordered. Both use `DB::statement` for ENUM→VARCHAR conversion (Laravel schema builder needs doctrine/dbal for `->change()`; raw SQL is dependency-free and explicit).

## Related Code Files
**Create**
- `database/migrations/2026_04_10_000001_add_locked_balance_to_gem_wallets_table.php`
- `database/migrations/2026_04_10_000002_add_escrow_columns_to_gem_transactions_table.php`

**Modify**
- `app/Models/GemWallet.php` — add `locked_balance` to `$fillable`, add `getSpendableBalanceAttribute()`, cast to int
- `app/Models/GemTransaction.php` — add new cols to `$fillable`/`$casts`, add relation `counterpartyTransaction()` (BelongsTo self), add type/status constants

## Implementation Steps

### Migration 1: `add_locked_balance_to_gem_wallets_table`
```php
public function up(): void {
    Schema::table('gem_wallets', function (Blueprint $table) {
        $table->unsignedInteger('locked_balance')->default(0)->after('balance');
    });
}
public function down(): void {
    Schema::table('gem_wallets', function (Blueprint $table) {
        $table->dropColumn('locked_balance');
    });
}
```

### Migration 2: `add_escrow_columns_to_gem_transactions_table`
```php
public function up(): void {
    // Step 1: convert ENUM → VARCHAR (validation moves to app layer)
    DB::statement("ALTER TABLE gem_transactions MODIFY type VARCHAR(32) NOT NULL");
    DB::statement("ALTER TABLE gem_transactions MODIFY status VARCHAR(20) NOT NULL DEFAULT 'pending'");

    // Step 2: add new columns
    Schema::table('gem_transactions', function (Blueprint $table) {
        $table->unsignedBigInteger('counterparty_user_id')->nullable()->after('user_id');
        $table->unsignedBigInteger('counterparty_transaction_id')->nullable()->after('counterparty_user_id');
        $table->unsignedInteger('platform_fee')->default(0)->after('balance_after');
        $table->timestamp('available_at')->nullable()->after('metadata');
        $table->timestamp('released_at')->nullable()->after('available_at');

        $table->foreign('counterparty_user_id')->references('id')->on('users')->nullOnDelete();
        $table->foreign('counterparty_transaction_id')->references('id')->on('gem_transactions')->nullOnDelete();

        $table->index(['type', 'available_at', 'released_at'], 'idx_release_scan');
        $table->index('counterparty_user_id', 'idx_counterparty');
        $table->unique(
            ['reference_type', 'reference_id', 'type', 'user_id'],
            'ux_gem_tx_ref_type_user'
        );
    });
}

public function down(): void {
    Schema::table('gem_transactions', function (Blueprint $table) {
        $table->dropUnique('ux_gem_tx_ref_type_user');
        $table->dropIndex('idx_counterparty');
        $table->dropIndex('idx_release_scan');
        $table->dropForeign(['counterparty_transaction_id']);
        $table->dropForeign(['counterparty_user_id']);
        $table->dropColumn([
            'counterparty_user_id',
            'counterparty_transaction_id',
            'platform_fee',
            'available_at',
            'released_at',
        ]);
    });
    DB::statement("ALTER TABLE gem_transactions MODIFY type ENUM('top_up','payment','refund','admin_adjust') NOT NULL");
    DB::statement("ALTER TABLE gem_transactions MODIFY status ENUM('pending','completed','failed','cancelled') NOT NULL DEFAULT 'pending'");
}
```

### GemWallet model updates
- Add `locked_balance` to `$fillable`
- Cast `balance` + `locked_balance` to `integer`
- Accessor: `getSpendableBalanceAttribute(): int { return max(0, $this->balance - $this->locked_balance); }`

### GemTransaction model updates
- Add to `$fillable`: `counterparty_user_id`, `counterparty_transaction_id`, `platform_fee`, `available_at`, `released_at`
- Casts: `available_at` + `released_at` → `datetime`; `platform_fee` + `counterparty_user_id` + `counterparty_transaction_id` → `integer`
- Constants:
  - `TYPE_TOP_UP='top_up'`, `TYPE_PAYMENT='payment'`, `TYPE_RECEIPT='receipt'`, `TYPE_REFUND='refund'`, `TYPE_REFUND_CLAWBACK='refund_clawback'`, `TYPE_ADMIN_ADJUST='admin_adjust'`
  - `STATUS_PENDING='pending'`, `STATUS_COMPLETED='completed'`, `STATUS_FAILED='failed'`, `STATUS_CANCELLED='cancelled'`, `STATUS_REFUNDED='refunded'`
- Relation: `counterpartyTransaction(): BelongsTo` → self on `counterparty_transaction_id`
- Relation: `counterpartyUser(): BelongsTo` → User on `counterparty_user_id`

### Verification
- Run `php artisan migrate` locally
- `DESCRIBE gem_wallets` → confirm `locked_balance` INT UNSIGNED DEFAULT 0
- `DESCRIBE gem_transactions` → confirm 5 new cols, type/status are VARCHAR
- `SHOW INDEX FROM gem_transactions` → confirm 3 new indexes
- Test rollback: `migrate:rollback --step=2` → confirm clean revert, then `migrate` again

## Todo List
- [ ] Create migration `add_locked_balance_to_gem_wallets_table`
- [ ] Create migration `add_escrow_columns_to_gem_transactions_table`
- [ ] Update GemWallet model (fillable, casts, spendable accessor)
- [ ] Update GemTransaction model (fillable, casts, constants, 2 relations)
- [ ] Run `migrate`, verify schema via DESCRIBE + SHOW INDEX
- [ ] Rollback test (`migrate:rollback --step=2` then `migrate`)

## Success Criteria
- `gem_wallets.locked_balance` column exists, default 0
- `gem_transactions` has 5 new cols + 2 indexes + 1 unique constraint
- `gem_transactions.type` + `status` are VARCHAR (no ENUM)
- `GemWallet::spendable_balance` accessor returns correct diff (never negative)
- `GemTransaction` model constants cover all 6 types + 5 statuses
- Rollback removes everything cleanly, restores ENUM definitions

## Risk Assessment
- **ENUM → VARCHAR conversion**: dev phase with empty tables → instant. Production rollout would need `ALGORITHM=COPY` consideration but N/A now.
- **Unique constraint on existing rows**: current gem_transactions may have dev test rows violating the new unique → run `migrate:fresh` before applying if conflict occurs.
- **FK `counterparty_transaction_id` self-reference**: MySQL allows but `nullOnDelete` must be set so refund chains don't cascade-delete.
- **Validation moves to app layer**: model constants + service-level guards replace DB ENUM. Phase-02 must enforce.

## Security Considerations
- No sensitive data added
- Defaults are safe (0 platform fee, NULL available_at = not an escrow row)

## Next Steps
- Phase 02 depends on completed schema
