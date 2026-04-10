# Phase 05 — UI: Owner Wallet Display

## Context Links
- Controller: `app/Http/Controllers/Front/GemController.php`
- Route: `/user/gems`
- Parent: `./plan.md` | Deps: phase-01

## Overview
**Priority:** P2 | **Status:** completed | **Est:** 2h

Update existing user wallet page to display three values: total balance, spendable, locked — plus label "receipt" tx type with "Đang chờ giải phóng" badge for unreleased rows. Serves both regular users and stadium/club owners (same page).

## Key Insights
- Reuse same view; owners are just users with higher `balance` / `locked_balance`.
- Vietnamese diacritics mandatory per project memory.
- Show countdown: "Giải phóng sau X giờ Y phút" based on `available_at`.

## Requirements
**Functional**
- Wallet summary card displays:
  - "Tổng số Gems": balance
  - "Có thể sử dụng": balance - locked_balance
  - "Đang khóa": locked_balance (tooltip: "Gems nhận từ khách, sẽ mở khóa sau 24 giờ để đảm bảo chính sách hoàn tiền")
- Transaction list row enhancements:
  - Type `receipt`: label "Nhận Gems", icon ↓, green
  - Type `refund_clawback`: label "Hoàn Gems cho khách", icon ↑, red
  - Type `refund`: "Hoàn từ giao dịch", green
  - Type `payment`: "Thanh toán", red
  - Type `top_up`: "Nạp Gems", green
  - Status badge "Đang chờ giải phóng" when `type=receipt` AND `released_at IS NULL`
  - Show `available_at` as "Mở khóa lúc HH:mm DD/MM/YYYY" for locked receipts

**Non-functional**
- No new routes
- File changes <200 LOC per file

## Architecture
Pure Blade + controller: controller passes `locked_balance` + `spendable_balance` to view; view renders via existing layout.

## Related Code Files
**Modify**
- `app/Http/Controllers/Front/GemController.php` — include `locked_balance`, `spendable_balance` in view data
- `resources/views/front/gems/index.blade.php` (or current wallet view — grep to confirm) — add summary card fields + tx type labels

**No create/delete**

## Implementation Steps
1. Grep for current wallet view path: `Grep "gem" --glob "resources/views/**/*.blade.php"`.
2. Update `GemController` index action:
   - Load wallet with both `balance` and `locked_balance`
   - Pass `$spendableBalance = $wallet->spendable_balance`
3. Update Blade view:
   - Replace single-balance display with 3-card layout (Tổng / Có thể dùng / Đang khóa)
   - Tooltip via title attribute or existing tooltip component
   - In transaction table: switch on `$tx->type` for label/icon/color
   - Conditional badge: `@if($tx->type === 'receipt' && !$tx->released_at) Đang chờ giải phóng @endif`
   - Format `available_at` with Vietnamese-friendly format
4. Verify no hard-coded English strings remain in modified sections.
5. Browser smoke test on local.

## Todo List
- [ ] Grep current wallet view path
- [ ] Update GemController to expose locked/spendable
- [ ] Update Blade summary card (3 values)
- [ ] Update tx list type labels (5 types)
- [ ] Add locked receipt badge + available_at display
- [ ] Manual browser verification

## Success Criteria
- Owner sees distinct spendable vs locked amounts
- Locked receipts show release time in Vietnamese
- All 5 transaction types render with correct labels & colors
- Tooltip explains 24-hour lock policy
- No English string regressions

## Risk Assessment
- **View file exceeds 200 LOC**: split into partial `_wallet-summary.blade.php` if needed
- **Missing tooltip component**: fall back to `title="..."` attribute

## Security Considerations
- Only render current user's own wallet — verify `auth()->id()` scoping already in place
- Escape all dynamic content via `{{ }}` (Blade default)

## Next Steps
- Phase 06: tests (includes controller-level assertions for exposed view data)
