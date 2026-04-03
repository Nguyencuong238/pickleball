---
title: "OnePickleball Card MVP - Gems Wallet"
description: "Gems wallet system with SePay top-up and booking payment integration"
status: complete
priority: P1
effort: 20h
branch: feat/gems-wallet
tags: [feature, backend, frontend, payment]
created: 2026-04-03
---

# OnePickleball Card MVP - Gems Wallet

## Overview

Implement Gems wallet: top-up via SePay VietQR, pay for court bookings with Gems, fixed % cashback to existing Points wallet. New `gem_wallets` + `gem_transactions` tables, separate from existing UserWallet/points system.

## Research Reports

- [Brainstorm](../reports/brainstorm-260403-0958-onepickleball-card-mvp.md)
- [SePay Integration](../reports/research-260403-0958-sepay-integration.md)
- [Wallet UX Patterns](../reports/researcher-260403-1004-wallet-ux-patterns.md)

## Key Decisions

- New tables (gem_wallets, gem_transactions) - no changes to UserWallet
- SePay VietQR for top-up, no withdrawal
- Exchange rate configurable via config/gems.php + .env
- Fixed % cashback -> existing UserWallet points
- DB::transaction + lockForUpdate for all balance ops

## Phases

| # | Phase | Status | Effort | Link |
|---|-------|--------|--------|------|
| 1 | Database & Config | Complete | 3h | [phase-01](./phase-01-database-and-config.md) |
| 2 | Backend Services | Complete | 6h | [phase-02](./phase-02-backend-services.md) |
| 3 | API & Controllers | Complete | 4h | [phase-03](./phase-03-api-and-controllers.md) |
| 4 | Frontend - Wallet UI | Complete | 4h | [phase-04](./phase-04-frontend-wallet-ui.md) |
| 5 | Frontend - Booking Integration | Complete | 3h | [phase-05](./phase-05-booking-integration.md) |

## Dependencies

- SePay account registration (external, blocks real webhook testing)
- Phase 1 -> 2 -> 3 (sequential)
- Phase 4 + 5 can start after Phase 3 (parallel)
