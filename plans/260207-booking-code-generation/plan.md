# Booking Code Generation Feature - Implementation Plan

**Feature:** Server-Side Booking Code Generation with Race Condition Protection
**Date Created:** 2026-02-07
**Status:** Code Review Completed - Critical Issues Identified

---

## Overview

Implementation of server-side booking code generation for the pickleball court booking system. Booking codes follow format: `BK{courtId:3}{date:YYMMDD}{seq:3}` (e.g., BK001260207001 → BK001-260207-001).

---

## Implementation Status

### Completed Tasks

- [x] Database migration for `booking_code` column
- [x] Booking model code generation method
- [x] Transaction wrapper for code generation
- [x] lockForUpdate() for race condition prevention
- [x] Formatted booking code accessor
- [x] API endpoints updated to return booking codes
- [x] Frontend QR flow reversed (server generates code first)
- [x] Backward compatibility for old bookings (nullable column)
- [x] Response format standardization (BookingResource)

### Critical Issues (MUST FIX)

- [ ] **Issue #1**: Race condition in overlap check (outside transaction)
  - Files: `HomeYardTournamentController.php`, `BookingController.php`
  - Fix: Move overlap check inside transaction with lockForUpdate()

- [ ] **Issue #2**: Missing retry logic for unique code generation
  - File: `Booking.php` generateBookingCode method
  - Fix: Add retry loop to handle rare collision cases

- [ ] **Issue #3**: Inconsistent service_fee calculation (float vs int)
  - Files: `HomeController.php`, `BookingController.php`, `HomeYardTournamentController.php`
  - Fix: Use `(int) round($totalPrice * 0.05)` consistently

- [ ] **Issue #4**: Missing lock_expires_at in tournament bookings
  - File: `HomeYardTournamentController.php`
  - Fix: Add lock logic for transfer payment method

### High Priority Issues

- [ ] **Issue #5**: Graceful handling for sequence overflow
- [ ] **Issue #6**: Add lock_expires_at to model casts
- [ ] **Issue #7**: Document backward compatibility strategy
- [ ] **Issue #8**: Add court_id max validation in controllers

### Testing Requirements

- [ ] Race condition test (concurrent bookings)
- [ ] Sequence overflow test (999+ bookings)
- [ ] Lock expiry test (15-min timeout)
- [ ] Backward compatibility test (null booking_code)

---

## Files Modified

### Backend (PHP)
- `database/migrations/2026_02_07_add_booking_code_to_bookings_table.php`
- `app/Models/Booking.php`
- `app/Http/Resources/BookingResource.php`
- `app/Http/Controllers/Api/BookingController.php`
- `app/Http/Controllers/Front/HomeController.php`
- `app/Http/Controllers/Front/HomeYardTournamentController.php`

### Frontend (Blade)
- `resources/views/front/booking.blade.php`
- `resources/views/home-yard/tournaments/bookings.blade.php`

---

## Reports

- [Code Review Report](./reports/260207-code-review-report.md) - Comprehensive review with 17 findings

---

## Next Steps

1. Fix critical issues #1-#4 (blocking production deployment)
2. Add comprehensive tests for race conditions
3. Deploy to staging for QA testing
4. Monitor sequence usage and set up alerts for approaching limits
5. Consider backfill strategy for old bookings

---

## Notes

- Booking code generation uses database-level locking to prevent duplicates
- 15-minute lock period for transfer payments
- Maximum 999 bookings per court per day
- Backward compatible with existing null booking_code records
