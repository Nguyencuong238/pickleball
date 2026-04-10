---
title: "Gems Transfer + Escrow Refund Window"
description: "Convert Gems from burn model to payer→owner transfer with 1-day locked escrow and refund clawback"
status: completed
priority: P1
effort: 19.5h
branch: main
tags: [gems, payments, escrow, refund, wallet]
created: 2026-04-10
---

## Overview

Rewrite Gems payment flow: instead of burning payer balance, transfer Gems to stadium/club owner wallet. Credited Gems locked for 1 day (refund window). Cancellation within window clawsback from owner locked balance. Release command unlocks matured receipts. Schema/enums future-proof for withdraw feature (not implemented).

## Key Decisions (locked)

- Reuse `GemWallet` for users + owners (Phương án A)
- `locked_balance` column; spendable = balance - locked_balance
- Refund window: 1 day, configurable
- Refund outside window: HARD BLOCK (manual DB intervention)
- Owner cannot spend locked Gems: HARD BLOCK
- No cashback on owner receipt; payer cashback unchanged (fires after transfer)
- Platform fee scaffold (0%) — fee burned in phase 1
- Self-payment / missing owner: HARD BLOCK
- Booking + ClubActivity both covered
- refType format: FQCN (`\App\Models\Booking::class`)
- Dev phase: no legacy data, no backfill, `migrate:fresh` acceptable
- ENUM → VARCHAR conversion for `gem_transactions.type` and `status`
- Single-server only: `withoutOverlapping` without `onOneServer` (CACHE_DRIVER=file)
- **Payable abstraction**: generic contract + processor to onboard League/Tournament/Coach/Shop with ~10 LOC per service (known future consumers: League, Tournament)

## Phases

| # | Phase | File | Est | Depends |
|---|-------|------|-----|---------|
| 1 | Schema migrations | `phase-01-schema-migrations.md` | 1h | — |
| 2 | Service: transfer() | `phase-02-service-layer-transfer.md` | 3h | 1 |
| 2.5 | Payable abstraction (contract + processor) | `phase-02-5-payable-abstraction.md` | 1.5h | 2 |
| 3 | Service: refund + release command | `phase-03-service-layer-refund-release.md` | 3h | 2 |
| 4 | Controller integration (4 call sites) | `phase-04-controller-integration.md` | 2h | 2.5, 3 |
| 5 | UI: owner wallet display | `phase-05-ui-owner-wallet.md` | 2h | 1 |
| 6 | Feature tests (24 cases) | `phase-06-tests.md` | 5h | 2.5, 3, 4 |
| 7 | Rollout via feature flag | `phase-07-rollout-feature-flag.md` | 2h | 4, 5, 6 |

## Scope Out

- Admin refund override path
- Owner withdraw to VND
- Backfill of historical burn-model transactions
- MerchantWallet split

## Success Criteria

- All 4 spending call sites use `transfer()` when flag on
- Booking cancellation (3 call sites) + ClubActivity cancellation trigger `refund()` within 24h
- Cancellation after 24h returns 422 with Vietnamese message, no balance mutation
- Invariants `balance >= 0 && locked_balance >= 0 && locked_balance <= balance` hold across all operations
- Release job releases locked gems idempotently every 5 min
- 24 feature tests green; no fake/mock shortcuts; invariants asserted start+end of each
- Owner wallet UI shows balance/spendable/locked correctly in Vietnamese

## Risks

- Deadlocks on double-wallet lock → mitigated by ordering `user_id ASC`
- Race condition between refund + release → mitigated by `released_at` guard inside transaction
- Feature flag rollback leaves mixed-mode txs → documented, no backfill
