# Plan: Thanh toan Gems cho hoat dong CLB (Social)

## Tong quan
Them kha nang thu phi Gems khi user dang ky tham gia hoat dong CLB (social, open_play, competition). CLB manager dat phi cho activity, user thanh toan bang Gems khi RSVP, hoan Gems khi huy (theo chinh sach).

## Trang thai hien tai
- `ClubActivity` **khong co** truong fee/gia
- RSVP hoan toan mien phi: `ClubActivityService::rsvp()` chi check skill level + capacity
- `GemWalletService::deduct()` da san sang, dung atomic lock, tra ve `GemTransaction`
- `GemCashbackService::award()` tu dong cong Point khi payment completed

## Pham vi
- Them fee_gems vao ClubActivity (tuy chon, null = mien phi)
- Thu Gems khi RSVP confirmed (khong thu khi waitlisted)
- Hoan Gems khi huy (neu trong thoi han)
- Thu Gems khi waitlisted -> confirmed (promote)
- Hien thi phi + so du tren UI

## Phases

| # | Phase | Status | File |
|---|-------|--------|------|
| 1 | Database migration | Done | [phase-01](phase-01-database-migration.md) |
| 2 | Backend service logic | Done | [phase-02](phase-02-backend-service.md) |
| 3 | Controller + API update | Done | [phase-03](phase-03-controller-api.md) |
| 4 | Frontend UI update | Done | [phase-04](phase-04-frontend-ui.md) |

## Dependencies
- GemWalletService (da co)
- GemCashbackService (da co)
- ClubActivityService (can sua)

## Quyet dinh thiet ke
1. **Waitlisted user KHONG bi thu phi** - chi thu khi confirmed hoac promote
2. **Hoan tien khi huy**: hoan 100% neu huy truoc activity_date, khong hoan neu da bat dau
3. **Cancel tu waitlist**: khong can hoan (chua thu phi)
4. **fee_gems nullable**: null/0 = mien phi (backward compatible)
5. **Recurring activity**: ke thua fee tu template khi tao instance
6. **Khoa fee**: khong cho sua fee_gems khi da co >= 1 confirmed participant
7. **Check-in QR (open_play)**: thu Gems khi check-in neu activity co fee
8. **Promote skip**: neu user duoc promote nhung khong du Gems -> skip, promote nguoi tiep theo
