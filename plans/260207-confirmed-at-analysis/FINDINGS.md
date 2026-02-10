# Key Findings: confirmed_at & lock_expires_at Analysis

## Executive Summary

The system uses a **two-column dual-lock mechanism**:

1. **`lock_expires_at`** (Unix timestamp, INTEGER)
   - Prevents double-booking during transfer payment verification
   - Automatically set to `now + 15 minutes` when transfer payment is selected
   - Cleared when booking is confirmed
   - Checked in real-time during slot availability queries

2. **`confirmed_at`** (Timestamp, TIMESTAMP)
   - Records when admin/owner confirms the booking
   - Set via `Booking::confirm()` method
   - Remains NULL until admin explicitly confirms
   - Used for audit trail and statistics

---

## Critical Implementation Gaps

### Gap #1: API BookingController::store() - Missing lock_expires_at
**Severity:** HIGH  
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Api/BookingController.php` (Lines 130-160)

**Current Issue:**
- Creates booking with status `pending_payment` (non-standard)
- Does NOT set `lock_expires_at` database field
- Uses job-based expiration (`CancelUnpaidBooking`, 5-minute timeout) instead
- Mismatch: Front uses 15-min lock, API uses 5-min job

**Current Code:**
```php
$status = $request->payment_method === 'transfer' ? 'pending_payment' : 'pending';
// Missing: lock_expires_at setup
Booking::create([
    // ... fields ...
    // MISSING: 'lock_expires_at' => $lockExpiresAt
]);

// Uses job instead:
if ($request->payment_method === 'transfer') {
    Bus::dispatch(new CancelUnpaidBooking($booking->id, 300));
}
```

**Impact:**
- Cannot rely on `lock_expires_at` to check slot availability from this API endpoint
- Overlap detection may not work correctly for API-created transfer bookings
- Job-based expiration unreliable (depends on queue processing)

---

### Gap #2: API BookingController::bookingCourt() - Missing both columns
**Severity:** CRITICAL  
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Api/BookingController.php` (Lines 358-380)

**Current Issue:**
- Does NOT set `confirmed_at`
- Does NOT set `lock_expires_at`
- Always creates with status `pending`
- No transfer payment lock mechanism

**Current Code:**
```php
Booking::create([
    // ... fields ...
    'status' => 'pending',
    'payment_method' => $request->payment_method,
    // MISSING: confirmed_at
    // MISSING: lock_expires_at
]);
```

**Impact:**
- Transfer payment bookings can be double-booked (no lock)
- Cannot track confirmation time
- Slot availability always shows as "pending available" regardless of lock

---

### Gap #3: HomeYardTournamentController::bookingCourt() - Missing both columns
**Severity:** CRITICAL  
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/HomeYardTournamentController.php` (Lines 2885-2902)

**Current Issue:**
- Does NOT set `confirmed_at`
- Does NOT set `lock_expires_at`
- Creates with status based on request input
- No transfer payment lock

**Current Code:**
```php
Booking::create([
    // ... fields ...
    'status' => $request->status ?? 'pending',
    'payment_method' => $request->payment_method,
    // MISSING: confirmed_at
    // MISSING: lock_expires_at
]);
```

**Impact:**
- Tournament bookings can be double-booked
- No audit trail of when booking was confirmed
- Inconsistent behavior between different booking paths

---

## What Works Correctly

### Front HomeController::bookingCourt()
**Status:** FULLY IMPLEMENTED ✓

**File:** `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/HomeController.php` (Lines 272-305)

- Correctly sets `lock_expires_at = time() + (15 * 60)` for transfer payments
- Sets `confirmed_at = null` on creation
- Creates with proper status transition
- Works with `CancelExpiredTransferBookings` job

---

## Model Implementation

### Booking::confirm() Method
**Status:** CORRECTLY IMPLEMENTED ✓

```php
public function confirm(): void
{
    $this->update([
        'status' => 'confirmed',
        'confirmed_at' => now(),      // Correctly sets timestamp
        'lock_expires_at' => null,    // Correctly clears lock
    ]);
}
```

**Returns:**
- `confirmed_at`: Carbon datetime instance (cast to datetime)
- `lock_expires_at`: Raw integer (NOT cast, requires manual casting)

---

## Lock Checking Methods

### isLocked() - Transfer Payment Lock Check
**Status:** CORRECTLY IMPLEMENTED ✓

```php
public function isLocked(): bool
{
    return $this->status === 'pending' 
        && $this->payment_method === 'transfer'
        && $this->lock_expires_at !== null
        && $this->lock_expires_at > time();
}
```

---

### isLockExpired() - Lock Expiration Check
**Status:** CORRECTLY IMPLEMENTED ✓

```php
public function isLockExpired(): bool
{
    return $this->lock_expires_at !== null && $this->lock_expires_at <= time();
}
```

---

## Overlap Detection Logic

**File:** `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Api/BookingController.php` (Lines 617-687)

**Current Logic:**
```php
if ($booked['payment_method'] == 'transfer' 
    && $booked['lock_expires_at'] !== null 
    && $booked['lock_expires_at'] > time()) {
    $isLocked = true;  // Slot RESERVED
}
```

**Status:** WORKS ONLY for front/HomeController bookings, fails for API bookings (which don't set lock_expires_at)

---

## Database Consistency Issues

### Issue: lock_expires_at Not in Model Casts
**Severity:** MEDIUM

The `lock_expires_at` field is NOT in the `$casts` array, though it should be:

```php
// Current (INCOMPLETE):
protected $casts = [
    'confirmed_at' => 'datetime',
    // lock_expires_at NOT CASTED
];

// Should be:
protected $casts = [
    'confirmed_at' => 'datetime',
    'lock_expires_at' => 'integer',  // Explicit cast for clarity
];
```

**Impact:**
- Column returns raw integer (works but not explicit)
- No type safety or IDE hints for `lock_expires_at`

---

## Status Value Inconsistency

| Endpoint | Transfer Status | Lock Field | Notes |
|---|---|---|---|
| Front HomeController | `pending` | `lock_expires_at` (set) | Correct |
| Api store() | `pending_payment` | Not set | INCONSISTENT |
| Api bookingCourt() | `pending` | Not set | MISSING |
| Tournament | Variable | Not set | MISSING |

**Issue:** No standard status for "payment pending with lock"
- Some use `pending` + `lock_expires_at`
- Some use `pending_payment` + job
- Creates confusion in status checking

---

## Recommendations

### Immediate (Critical)
1. Add `lock_expires_at = time() + (15*60)` to Api::bookingCourt()
2. Add `lock_expires_at` setup to Api::store() (standardize to database lock)
3. Add `lock_expires_at = time() + (15*60)` to Tournament controller
4. Remove status `pending_payment` or standardize its usage

### Short-term (High)
5. Add `lock_expires_at` to model casts array for clarity
6. Add `confirmed_at` to Api::bookingCourt() and Tournament controller
7. Document lock behavior in code comments
8. Add unit tests for lock expiration logic

### Long-term (Should)
9. Implement background job to clean up expired bookings
10. Add middleware/observer to prevent modification of expired bookings
11. Create booking status constants (vs hardcoded strings)
12. Add database indexes on (status, payment_method, lock_expires_at) for queries

