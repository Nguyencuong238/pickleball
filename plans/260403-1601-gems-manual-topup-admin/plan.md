---
title: "Gems Manual Top-up with Admin Approval"
description: "Replace SePay-only QR with VietQR (free) + admin approval flow for Gems top-up"
status: complete
priority: P1
effort: 4h
branch: feat/gems-wallet
tags: [feature, backend, frontend, admin]
created: 2026-04-03
---

# Gems Manual Top-up with Admin Approval

## Overview

Thay SePay QR bang VietQR (mien phi, khong can API key). User tao QR nap tien -> chuyen khoan -> Admin doi chieu sao ke -> Duyet/Tu choi. Giu SePay webhook lam fallback auto-confirm neu config day du.

## Flow

```
User: Nap Gems -> Chon so tien -> Tao QR (VietQR) voi noi dung "GEMS{userId}T{txId}"
      -> Pending GemTransaction duoc tao
      -> User chuyen khoan theo QR

Admin: /admin/gem-topups -> Thay danh sach pending
       -> Doi chieu noi dung CK voi sao ke ngan hang
       -> "Duyet" -> confirmTopUp() -> Gems cong
       -> "Tu choi" -> status = cancelled
```

## Key Decisions

- QR dung VietQR (`img.vietqr.io`) - mien phi, khong can dang ky
- STK + ma NH cau hinh qua `GEMS_BANK_*` env vars
- Admin view dat trong `/admin/gem-topups`
- Giu SePay webhook nguyen -> neu co SEPAY_API_KEY thi auto-confirm, khong thi manual
- Khong can migration moi (dung gem_transactions co san)

## Phases

| # | Phase | Status | Effort | Link |
|---|-------|--------|--------|------|
| 1 | QR + Config + Service | Complete | 1.5h | [phase-01](./phase-01-qr-and-service.md) |
| 2 | Admin Controller + View | Complete | 2.5h | [phase-02](./phase-02-admin-topup-management.md) |

## Dependencies

- Phai o tren branch `feat/gems-wallet` (da co gem_wallets, gem_transactions, GemWalletService)
- Phase 1 -> Phase 2 (sequential)
