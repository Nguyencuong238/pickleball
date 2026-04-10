# Phase 02.5 — Payable Abstraction

## Context Links
- Parent: `./plan.md` | Prev: `phase-02-service-layer-transfer.md` | Next: `phase-03-service-layer-refund-release.md`
- Future consumers: League, Tournament (verified: both have `user_id` owner column)

## Overview
**Priority:** P2 | **Status:** pending | **Est:** 1.5h

Extract generic `Payable` contract + `GemPaymentProcessor` service to eliminate boilerplate when adding new paid services (League, Tournament, Coach, Shop...). Each new service implements 6 interface methods instead of duplicating owner resolution + validation + transfer + cashback + refund wiring.

## Key Insights
- League model already has `user_id` (owner), `club_id`, `registration_fee` column — ready for `Payable` without schema change.
- Tournament model has `user_id` (owner via `belongsTo(User::class)` at L40) — ready.
- Boilerplate saved per service: ~30 LOC → ~10 LOC (6 interface methods + 2 line controller call).
- Abstraction is thin: no magic, no runtime resolution — just polymorphism.
- YAGNI guardrail: only create what's needed NOW — don't add optional methods for hypothetical future cases. Known future consumers: League, Tournament (confirmed by user).

## Requirements

**Functional**
- Create `App\Contracts\Payable` interface with 7 methods:
  ```php
  public function getPayer(): ?User;              // nullable: some items have inherent payer (Booking), some don't (ClubActivity)
  public function getPayee(): ?User;
  public function getPayableAmountVnd(): int;
  public function getPayableDescription(): string;
  public function getPayableReferenceType(): string;  // default impl: static::class via trait
  public function getPayableReferenceId(): int;       // default impl: $this->id via trait
  public function markPaidWithGems(GemTransaction $debitTx): void;
  ```
- `GemPaymentProcessor::pay(Payable $item, ?User $payer = null)`: if `$payer` null, falls back to `$item->getPayer()`. If both null → throw. No transient state on models.
- Create `App\Concerns\IsPayable` trait with default implementations for `getPayableReferenceType()` and `getPayableReferenceId()`.
- Create `App\Services\GemPaymentProcessor` service with 2 methods:
  - `pay(Payable $item): array` — resolves payer/payee, validates, calls transfer(), confirms domain entity, awards cashback, returns `[debitTx, creditTx]`
  - `refundFor(Payable $item): GemTransaction` — locates debit tx via unique index, calls `GemWalletService::refund()`
- Apply `Payable` to existing `Booking` and `ClubActivity` models (replaces inline logic from phase-04).

**Non-functional**
- Interface + processor files < 100 LOC each
- No new DB changes (pure code abstraction)
- Backward-compatible: `GemWalletService::payForBooking()` still exists as thin wrapper for phase-04 transition

## Architecture

```
┌─────────────────┐       ┌──────────────────────┐
│ Booking         │       │ ClubActivity         │       (future: League, Tournament)
│ uses IsPayable  │       │ uses IsPayable       │
│ implements      │       │ implements           │
│   Payable       │       │   Payable            │
└────────┬────────┘       └──────────┬───────────┘
         │                           │
         └─────────────┬─────────────┘
                       ▼
         ┌──────────────────────────────┐
         │ GemPaymentProcessor          │
         │   ├─ pay(Payable)            │
         │   └─ refundFor(Payable)      │
         └─────────┬────────────────────┘
                   │
                   ▼
         ┌──────────────────────────────┐
         │ GemWalletService             │
         │   ├─ transfer()  (phase 02)  │
         │   └─ refund()    (phase 03)  │
         └──────────────────────────────┘
```

## Related Code Files

**Create**
- `app/Contracts/Payable.php` — interface
- `app/Concerns/IsPayable.php` — trait with default ref methods
- `app/Services/GemPaymentProcessor.php` — orchestrator

**Modify**
- `app/Models/Booking.php` — implement Payable + use IsPayable trait
- `app/Models/ClubActivity.php` — implement Payable + use IsPayable trait
- Phase 04 controller integration — use `GemPaymentProcessor::pay()` instead of inline logic (update phase-04 todo list accordingly)

## Implementation Steps

### 1. Interface
```php
// app/Contracts/Payable.php
namespace App\Contracts;

use App\Models\GemTransaction;
use App\Models\User;

interface Payable
{
    public function getPayer(): ?User;          // nullable: see phase insights
    public function getPayee(): ?User;
    public function getPayableAmountVnd(): int;
    public function getPayableDescription(): string;
    public function getPayableReferenceType(): string;
    public function getPayableReferenceId(): int;
    public function markPaidWithGems(GemTransaction $debitTx): void;
}
```

### 2. Trait (default ref implementations)
```php
// app/Concerns/IsPayable.php
namespace App\Concerns;

trait IsPayable
{
    public function getPayableReferenceType(): string
    {
        return static::class;
    }

    public function getPayableReferenceId(): int
    {
        return (int) $this->getKey();
    }
}
```

### 3. GemPaymentProcessor
```php
// app/Services/GemPaymentProcessor.php
namespace App\Services;

use App\Contracts\Payable;
use App\Exceptions\GemTransferException;
use App\Models\GemTransaction;

class GemPaymentProcessor
{
    public function __construct(
        private GemWalletService $wallet,
        private GemCashbackService $cashback,
    ) {}

    public function pay(Payable $item, ?User $payer = null): array
    {
        $payer ??= $item->getPayer();
        $payee = $item->getPayee();

        if (!$payer) {
            throw GemTransferException::missingPayer();
        }
        if (!$payee) {
            throw GemTransferException::missingOwner();
        }
        if ($payee->id === $payer->id) {
            throw GemTransferException::selfPayment();
        }

        $gems = (int) ceil($item->getPayableAmountVnd() / config('gems.exchange_rate'));

        [$debitTx, $creditTx] = $this->wallet->transfer(
            $payer,
            $payee,
            $gems,
            $item->getPayableReferenceType(),
            $item->getPayableReferenceId(),
            $item->getPayableDescription(),
        );

        $item->markPaidWithGems($debitTx);
        $this->cashback->award($debitTx);

        return [$debitTx, $creditTx];
    }

    public function refundFor(Payable $item): GemTransaction
    {
        $debitTx = GemTransaction::where([
            'reference_type' => $item->getPayableReferenceType(),
            'reference_id'   => $item->getPayableReferenceId(),
            'type'           => GemTransaction::TYPE_PAYMENT,
            'user_id'        => $item->getPayer()->id,
            'status'         => GemTransaction::STATUS_COMPLETED,
        ])->firstOrFail();

        [, $clawbackTx] = $this->wallet->refund($debitTx);
        return $clawbackTx;
    }
}
```

### 4. Apply to Booking
```php
// app/Models/Booking.php
use App\Concerns\IsPayable;
use App\Contracts\Payable;

class Booking extends Model implements Payable
{
    use IsPayable;

    public function getPayer(): User
    {
        return $this->user;
    }

    public function getPayee(): ?User
    {
        $this->loadMissing('court.stadium.user');
        return $this->court?->stadium?->user;
    }

    public function getPayableAmountVnd(): int
    {
        return (int) $this->total_price;
    }

    public function getPayableDescription(): string
    {
        return "Đặt sân - {$this->booking_code}";
    }

    public function markPaidWithGems(GemTransaction $debitTx): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }
}
```

### 5. Apply to ClubActivity
```php
public function getPayer(): ?User
{
    return null;    // no inherent payer — caller passes explicitly via processor->pay($activity, $user)
}

public function getPayee(): ?User
{
    $this->loadMissing('club.user');
    return $this->club?->user;
}

public function getPayableAmountVnd(): int
{
    return (int) ($this->fee_gems * config('gems.exchange_rate'));
}

public function getPayableDescription(): string
{
    return "Phí tham gia: {$this->title}";
}

public function markPaidWithGems(GemTransaction $debitTx): void
{
    // No-op: participant row managed by ClubActivityService outer transaction
}
```

**Note**: ClubActivity returns `null` from `getPayer()` because payments are per-participant, not per-activity. Caller MUST pass `$payer` explicitly to `processor->pay($activity, $user)` — processor throws `missingPayer` if both null. No transient state on model.

### 6. Update GemWalletService::payForBooking
Thin adapter delegating to `GemPaymentProcessor`:
```php
public function payForBooking(User $user, Booking $booking, int $totalPrice): array
{
    // Legacy signature preserved for 3 controller call sites (phase 04)
    // $totalPrice ignored — processor reads via Payable::getPayableAmountVnd()
    [$debitTx, ] = app(GemPaymentProcessor::class)->pay($booking, $user);
    $cashbackPoints = (int) (abs($debitTx->amount) * config('gems.cashback_percent') / 100);
    return [$debitTx, $cashbackPoints];
}
```

### 7. Update ClubActivityService::chargeGems
```php
private function chargeGems(ClubActivity $activity, User $user): GemTransaction
{
    [$debitTx, ] = app(GemPaymentProcessor::class)->pay($activity, $user);
    return $debitTx;
}
```

## Todo List
- [ ] Create `App\Contracts\Payable` interface
- [ ] Create `App\Concerns\IsPayable` trait
- [ ] Create `App\Services\GemPaymentProcessor` service
- [ ] Apply Payable to Booking model
- [ ] Apply Payable to ClubActivity model (getPayer returns null, explicit payer via processor arg)
- [ ] Update `payForBooking` to delegate to processor
- [ ] Update `chargeGems` to delegate to processor
- [ ] Lint check (php -l)
- [ ] Manual tinker test: pay for a booking + refund via processor

## Success Criteria
- `Payable` interface exists with 6 methods
- `GemPaymentProcessor::pay()` handles Booking and ClubActivity uniformly
- Phase 04 controller integration uses processor (1-line call)
- Future League/Tournament integration requires only implementing 6 methods on model
- All 21 feature tests from phase 06 still pass after refactor

## Risk Assessment
- **Eager-load performance**: `loadMissing('court.stadium.user')` happens inside `getPayee()` — fine for single-record pay flow, but if called inside a loop would N+1. Mitigation: document as "call on individual model instance only".
- **ClubActivity payer arg required**: caller MUST pass `$user` explicitly to `processor->pay($activity, $user)`. Forgetting triggers `GemTransferException::missingPayer()` at runtime. Covered by test 17 in phase 06.
- **Refactor risk**: phase 04 already wires things — must coordinate. Mitigation: phase 04 todo list rewritten to expect processor delegation from day one.
- **Test rewrite**: phase 06 tests may need minor updates to use processor instead of direct service calls. Minimal — tests remain at integration level.

## Security Considerations
- No new attack surface — interface is internal
- Authorization still enforced at controller layer (who can pay for whom)

## Next Steps
- Phase 03: refund() + release command (unchanged, uses transfer primitives)
- Phase 04: controllers use `GemPaymentProcessor::pay()` / `refundFor()` — update todo to reflect processor-first approach
- Future: League implements Payable (5-10 LOC) — ownership via `$league->user`, fee via `$league->registration_fee`, post-payment via `$league->markRegistered()` or similar
- Future: Tournament implements Payable — ownership via `$tournament->user` (belongsTo User at L40), entry fee column tbd

## Appendix: League / Tournament Readiness Check

**League** (verified `app/Models/League.php`):
- `user_id` ✓ (L13, L63 belongsTo)
- `club_id` ✓ (L14) — secondary owner option
- `registration_fee` column ✓ (L27, L36 cast decimal:0)
- **Verdict**: drop-in ready. Need only implement 6 Payable methods + post-payment action (likely creates `LeagueRegistration`).

**Tournament** (verified `app/Models/Tournament.php`):
- `belongsTo(User::class)` ✓ at L40
- Entry fee column: TBD — verify during integration
- **Verdict**: drop-in ready for ownership. Check if entry fee column exists; add if not.

Both services can be added AFTER current phase completes without touching Gems core — just implement interface, add controller route, done.
