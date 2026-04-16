---
title: "Xoa ti so tran bracket va mo khoa chinh sua"
description: "Them endpoint reset score cho tran knockout bracket, mo khoa chinh sua tran dau da hoan thanh"
status: pending
priority: P1
effort: 3h
branch: main
tags: [backend, frontend, tournament, bracket]
created: 2026-04-16
---

# Xoa ti so tran bracket va mo khoa chinh sua

## Van de

Khi tran dau bracket co `status=completed` va co `set_scores`, hien tai:
- `updateMatch` (chinh sua VDV/lich thi dau) bi **BLOCK** voi message "Xoa ti so truoc"
- **Khong co endpoint** nao de xoa ti so -> **deadlock**, khong the chinh sua tran dau

Ngoai ra, cac truong khong lien quan ket qua (ngay, gio, best_of, ghi chu) cung bi block khi tran da completed.

## Giai phap

1. Them endpoint `DELETE bracket/reset-score` de xoa ti so + cascade downstream
2. Noi long guard `updateMatch` cho phep sua truong phi-ket-qua (ngay, gio, best_of, notes) tren tran completed
3. Them nut "Xoa ti so" tren UI cho tran completed

## Phases

| # | Phase | Status | Effort | Link |
|---|-------|--------|--------|------|
| 1 | Backend - Reset score endpoint | Pending | 1.5h | [phase-01](./phase-01-backend-reset-score.md) |
| 2 | Backend - Noi long guard updateMatch | Pending | 0.5h | [phase-02](./phase-02-relax-update-guard.md) |
| 3 | Frontend - Nut xoa ti so | Pending | 1h | [phase-03](./phase-03-frontend-reset-button.md) |

## Dependencies

- Phase 2 doc lap voi Phase 1
- Phase 3 can Phase 1 hoan thanh truoc (can endpoint)
- `cascadeClearDownstream` da co san trong `KnockoutBracketQuery`
- `BracketAdvancementTrait::handleBracketAdvancement` xu ly advance winner
