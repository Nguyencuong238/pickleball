# Booking Flow - Quick Start Guide

## Executive Summary

The application has 3 separate booking creation endpoints with a unified locking mechanism for transfer payments:

1. **API `store()` endpoint** - 5-minute auto-cancel (pending_payment status)
2. **Front/API `bookingCourt()` endpoints** - 15-minute manual lock (pending status + lock_expires_at)
3. All paths use `DB::transaction` + `lockForUpdate()` for booking code generation

---

## CRITICAL FLOWS

### Creating a Booking (All 3 Paths)

```
1. Validate input data
2. Find court (fail if not found)
3. Calculate end_time + pricing
4. Check for time conflicts (existing bookings)
5. START TRANSACTION
   └─ Generate booking code (BK{court:3}{date:YYMMDD}{seq:3})
   └─ Create Booking record
6. IF transfer payment → dispatch job/set lock
7. Return success with booking_code
```

**Lock Details for Transfer Payments**:
- **Path 1 (API store)**: Status = `pending_payment`, no lock_expires_at, auto-cancel after 5 min
- **Path 2-3 (Front/API bookingCourt)**: Status = `pending`, lock_expires_at = now + 15 min, manual confirm needed

---

### Checking Court Availability

**Stable API**: `getAvailableSlotsForAllCourts()` (includes lock checking)

```
FOR each hour:
  ├─ Check for bookings with status IN [confirmed, pending, pending_payment]
  ├─ IF overlap found:
  │  ├─ IF pending + transfer + lock not expired → is_locked = true
  │  ├─ ELSE IF pending → is_pending = true
  │  └─ ELSE → is_booked = true
  └─ Return slot with flags
```

**Flags Returned**:
- `is_booked` - Confirmed booking, cannot book
- `is_pending` - Pending booking (not locked), may be available
- `is_locked` - Transfer payment locked 15 min, cannot book

---

### Admin Actions

**confirmBooking()** - Admin approves a pending booking
```
Sets:
- status = 'confirmed'
- confirmed_at = now()
- lock_expires_at = null
Result: Slot fully booked
```

**rejectBooking()** - Admin rejects a pending booking
```
Sets:
- status = 'cancelled'
Result: Slot available for new bookings
```

---

## KEY CODE LOCATIONS

| Action | File | Lines |
|--------|------|-------|
| Booking code generation | `/app/Models/Booking.php` | 123-153 |
| isLocked() check | `/app/Models/Booking.php` | 103-109 |
| confirm() method | `/app/Models/Booking.php` | 90-97 |
| API store booking | `/app/Http/Controllers/Api/BookingController.php` | 99-173 |
| Front bookingCourt | `/app/Http/Controllers/Front/HomeController.php` | 210-319 |
| API bookingCourt | `/app/Http/Controllers/Api/BookingController.php` | 301-394 |
| Check availability | `/app/Http/Controllers/Api/BookingController.php` | 561-721 |
| Admin confirm | `/app/Http/Controllers/Api/BookingController.php` | 727-783 |
| Admin reject | `/app/Http/Controllers/Api/BookingController.php` | 788-843 |

---

## DATABASE

**Bookings Table Columns**:
```sql
booking_code (string, 14) ← BK001260207001
status (string)            ← pending | confirmed | cancelled | pending_payment
payment_method (string)    ← cash | card | transfer | wallet
confirmed_at (timestamp)   ← Admin confirmation time
lock_expires_at (int)      ← Unix timestamp or null
court_id, booking_date, start_time, end_time, etc.
```

**Index**: `(court_id, booking_date, booking_code)`

---

## COMMON QUERIES

### Get all bookings for a court on a date
```php
Booking::where('court_id', $courtId)
  ->where('booking_date', $date)
  ->where('status', '!=', 'cancelled')
  ->get();
```

### Check if a time slot is locked
```php
$booking = Booking::find($id);
if ($booking->isLocked()) {
  // Cannot book
}
```

### Check if a lock has expired
```php
if ($booking->isLockExpired()) {
  // Lock is stale, safe to re-book
}
```

### Confirm a booking
```php
$booking->confirm();  // Sets status=confirmed, confirmed_at=now, lock_expires_at=null
```

### Cancel a booking
```php
$booking->cancel();   // Sets status=cancelled
```

---

## TIMELINE REFERENCE

### Transfer Payment Booking Lifecycle

| Time | Event | State |
|------|-------|-------|
| T+0 | Created | **LOCKED** (pending, transfer, lock_expires_at = now + 15min) |
| T+0-15min | Others see as locked | Cannot book this slot |
| T+15min- | Lock expires | Available again (if not confirmed) |
| T+anytime | Admin confirms | **BOOKED** (confirmed, lock_expires_at = null) |
| T+anytime | Admin rejects | **CANCELLED** (available for rebook) |

### API store() Path Timeline

| Time | Event | State |
|------|-------|-------|
| T+0 | Created | pending_payment (no lock) |
| T+5min | Unpaid auto-cancel job fires | CANCELLED |
| T+anytime | Payment confirmed manually | Contact support or API |

---

## GOTCHAS & IMPORTANT NOTES

1. **Path 1 (API store) is different**: Uses `pending_payment` status without lock_expires_at, with 5-min auto-cancel job

2. **Path 2 & 3 (bookingCourt)**: Use `pending` status with 15-min lock_expires_at, require manual admin confirm/reject

3. **Booking code is unique**: Enforced by database unique constraint on booking_code

4. **lockForUpdate()**: Critical for preventing duplicate sequence numbers. Must be in transaction.

5. **Availability check missing lock logic**: `HomeController::getAvailableSlots()` does NOT check is_locked flag (only `getAvailableSlotsForAllCourts()` does)

6. **Formatted booking code**: Display uses `formatted_booking_code` accessor (BK001-260207-001 format)

---

## FILES CHANGED BY RECENT COMMITS

- `app/Models/Booking.php` - Added generateBookingCode(), isLocked(), confirm() methods
- `database/migrations/2026_02_07_add_booking_code_to_bookings_table.php` - Added booking_code column + index
- `database/migrations/2026_02_07_add_confirmed_at_to_bookings.php` - Added confirmed_at + lock_expires_at columns
- `app/Http/Controllers/Api/BookingController.php` - Updated store() & bookingCourt() with code generation
- `app/Http/Controllers/Front/HomeController.php` - Updated bookingCourt() with lock logic
- `app/Jobs/CancelUnpaidBooking.php` - Auto-cancel for API store path
- `app/Jobs/CancelExpiredTransferBookings.php` - Clear expired locks

---

## NEXT STEPS FOR IMPLEMENTATION

If you need to:

1. **Add payment confirmation logic**: Check `CancelUnpaidBooking.php` job, add endpoint to manually confirm transfer
2. **Display countdown timer**: Get `lock_expires_at` from availability API, calculate remaining time in JavaScript
3. **Add email notifications**: Hook into `confirm()`, `cancel()` methods in Booking model
4. **Change lock duration**: Modify `time() + (15 * 60)` in `HomeController::bookingCourt()` (line 279)
5. **Add refund logic**: Add method to Booking model, call after `cancel()`

---

## REPORTS AVAILABLE

- **scout-report.md** - Detailed code walkthrough with line numbers
- **booking-flow-diagram.md** - Visual ASCII diagrams of all flows
- **260207-code-review-report.md** - Code review findings (if any)

