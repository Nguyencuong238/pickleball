# Scout Report: `confirmed_at` and Booking Lock Mechanism Analysis

**Date:** 2026-02-07  
**Task:** Search all references to `confirmed_at` in the bookings context and understand relationship with booking lock mechanism.

---

## Summary

The `confirmed_at` and `lock_expires_at` columns are part of a **two-tier confirmation system**:
1. **Lock Mechanism** (`lock_expires_at`): Prevents double-booking during transfer payment verification (15 min timeout)
2. **Confirmation Timestamp** (`confirmed_at`): Records when admin/owner confirms the booking

---

## Key Files Found

### 1. Database Schema
**File:** `/Users/thaopv/Desktop/php/pickleball/database/migrations/2026_02_07_add_confirmed_at_to_bookings.php`

```php
// Line 15-18: Migration adds both columns
Schema::table('bookings', function (Blueprint $table) {
    // Tracking when booking is confirmed by admin/owner
    $table->timestamp('confirmed_at')->nullable()->after('status');
    // Unix timestamp when lock expires (for transfer payment bookings)
    $table->integer('lock_expires_at')->nullable()->after('confirmed_at')
        ->comment('Unix timestamp khi khóa hết hạn');
});
```

**Column Types:**
- `confirmed_at`: `timestamp` (nullable, casted to datetime in model)
- `lock_expires_at`: `integer` (nullable, Unix timestamp)

---

### 2. Booking Model
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Models/Booking.php`

```php
// Lines 15-34: Fillable attributes
protected $fillable = [
    'booking_code',
    'court_id',
    'user_id',
    'customer_name',
    'customer_phone',
    'customer_email',
    'booking_date',
    'start_time',
    'end_time',
    'duration_hours',
    'hourly_rate',
    'total_price',
    'service_fee',
    'status',
    'payment_method',
    'notes',
    'confirmed_at',      // Added to fillable
    'lock_expires_at',   // Added to fillable
];

// Lines 36-45: Type Casts
protected $casts = [
    'booking_date' => 'date',
    'duration_hours' => 'float',
    'hourly_rate' => 'integer',
    'total_price' => 'integer',
    'service_fee' => 'integer',
    'start_time' => 'string',
    'end_time' => 'string',
    'confirmed_at' => 'datetime',  // Casted to datetime
    // NOTE: lock_expires_at NOT in casts - stored as integer Unix timestamp
];
```

---

### 3. Booking Confirmation & Lock Methods

**File:** `/Users/thaopv/Desktop/php/pickleball/app/Models/Booking.php`

#### 3a. Confirm Booking (Lines 90-97)
```php
/**
 * Confirm the booking.
 */
public function confirm(): void
{
    $this->update([
        'status' => 'confirmed',
        'confirmed_at' => now(),      // Sets to current timestamp
        'lock_expires_at' => null,    // Clears the lock
    ]);
}
```

#### 3b. Check if Locked (Lines 100-109)
```php
/**
 * Check if booking is locked (chuyển khoản, chờ xác nhận trong 15 phút)
 * Locked = pending + transfer + lock_expires_at not expired
 */
public function isLocked(): bool
{
    return $this->status === 'pending' 
        && $this->payment_method === 'transfer'
        && $this->lock_expires_at !== null
        && $this->lock_expires_at > time();  // Compares Unix timestamp
}
```

#### 3c. Check Lock Expiration (Lines 114-117)
```php
/**
 * Check if lock has expired
 */
public function isLockExpired(): bool
{
    return $this->lock_expires_at !== null && $this->lock_expires_at <= time();
}
```

---

## Booking Creation Points (3 paths)

All 3 booking creation endpoints now have different lock behaviors:

### 1. Front HomeController (Web UI)
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/HomeController.php`

**Lines 272-305:** Complete lock setup for transfer payments
```php
// Lines 272-280: Lock logic for transfer payment (15 minutes)
$status = 'pending';
$lockExpiresAt = null;

if ($request->payment_method === 'transfer') {
    // Lock for 15 minutes for transfer payments
    $lockExpiresAt = time() + (15 * 60);  // 900 seconds
}

// Lines 283-305: Booking creation inside transaction
$booking = DB::transaction(function () use ($request, $endTime, $durationHours, $totalPrice, $status, $lockExpiresAt) {
    $bookingCode = Booking::generateBookingCode($request->court_id, $request->booking_date);
    return Booking::create([
        'booking_code' => $bookingCode,
        'court_id' => $request->court_id,
        'user_id' => auth()->id() ?? null,
        'customer_name' => $request->customer_name,
        'customer_phone' => $request->customer_phone,
        'customer_email' => $request->customer_email,
        'booking_date' => $request->booking_date,
        'start_time' => $request->start_time,
        'end_time' => $endTime,
        'duration_hours' => $durationHours,
        'hourly_rate' => (int) $request->hourly_rate,
        'total_price' => $totalPrice,
        'service_fee' => $totalPrice * 0.05,
        'status' => $status,
        'payment_method' => $request->payment_method,
        'notes' => $request->notes ?? null,
        'confirmed_at' => null,           // NOT set during creation
        'lock_expires_at' => $lockExpiresAt,  // Set to now + 15min for transfer
    ]);
});
```

**Status:** Fully implemented with proper lock mechanism.

---

### 2. API BookingController::store (Lines 130-154)
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Api/BookingController.php`

```php
// Line 131: Uses pending_payment status instead of pending
$status = $request->payment_method === 'transfer' ? 'pending_payment' : 'pending';

// Lines 136-153: Booking creation
return Booking::create([
    'booking_code' => $bookingCode,
    'user_id' => Auth::id(),
    'court_id' => $request->court_id,
    'customer_name' => $request->customer_name,
    'customer_phone' => $request->customer_phone,
    'customer_email' => $request->customer_email ?? null,
    'booking_date' => $request->booking_date,
    'start_time' => $request->start_time,
    'end_time' => $endTime->format('H:i'),
    'duration_hours' => $request->duration_hours,
    'hourly_rate' => $request->hourly_rate,
    'total_price' => $subtotal,
    'service_fee' => $serviceFee,
    'status' => $status,
    'payment_method' => $request->payment_method,
    'notes' => $request->notes ?? null,
    // NOTE: Missing confirmed_at and lock_expires_at!
]);

// Line 157-160: Dispatches CancelUnpaidBooking job (5 min timeout)
if ($request->payment_method === 'transfer') {
    \Illuminate\Support\Facades\Bus::dispatch(
        new \App\Jobs\CancelUnpaidBooking($booking->id, 300) // 300 seconds = 5 minutes
    );
}
```

**Status:** ISSUE - Missing `lock_expires_at` setup. Uses job-based expiration instead of database lock.

---

### 3. API BookingController::bookingCourt (Lines 358-380)
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Api/BookingController.php`

```php
// Line 376: Always creates as 'pending' status
$booking = DB::transaction(function () use ($request, $endTime, $durationHours, $hourlyRate, $totalPrice, $serviceFee) {
    $bookingCode = Booking::generateBookingCode($request->court_id, $request->booking_date);
    return Booking::create([
        'booking_code' => $bookingCode,
        'court_id' => $request->court_id,
        'user_id' => auth()->id() ?? null,
        'customer_name' => $request->customer_name,
        'customer_phone' => $request->customer_phone,
        'customer_email' => $request->customer_email,
        'booking_date' => $request->booking_date,
        'start_time' => $request->start_time,
        'end_time' => $endTime->format('H:i'),
        'duration_hours' => $durationHours,
        'hourly_rate' => $hourlyRate,
        'total_price' => $totalPrice,
        'service_fee' => $serviceFee,
        'status' => 'pending',
        'payment_method' => $request->payment_method,
        'notes' => $request->notes ?? null,
        // NOTE: Missing confirmed_at and lock_expires_at!
    ]);
});
```

**Status:** ISSUE - Missing both `confirmed_at` and `lock_expires_at` setup.

---

### 4. HomeYardTournamentController::bookingCourt (Lines 2882-2902)
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/HomeYardTournamentController.php`

```php
// Lines 2885-2902: Booking creation
return Booking::create([
    'booking_code' => $bookingCode,
    'court_id' => $request->court_id,
    'user_id' => auth()->id() ?? null,
    'customer_name' => $request->customer_name,
    'customer_phone' => $request->customer_phone,
    'customer_email' => $request->customer_email,
    'booking_date' => $request->booking_date,
    'start_time' => $request->start_time,
    'end_time' => $endTime,
    'duration_hours' => $durationHours,
    'hourly_rate' => (int) $request->hourly_rate,
    'total_price' => $totalPrice,
    'service_fee' => $totalPrice * 0.05,
    'status' => $request->status ?? 'pending',
    'payment_method' => $request->payment_method,
    'notes' => $request->notes ?? null,
    // NOTE: Missing confirmed_at and lock_expires_at!
]);
```

**Status:** ISSUE - Missing both `confirmed_at` and `lock_expires_at` setup.

---

## Confirmation Workflow

### Admin Confirms Booking
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Api/BookingController.php`

**Lines 727-783: confirmBooking method**
```php
public function confirmBooking($bookingId, Request $request)
{
    // ... validation ...
    
    // Line 750-755: Only pending bookings can be confirmed
    if ($booking->status !== 'pending') {
        return response()->json([
            'success' => false,
            'message' => "Chỉ có thể xác nhận booking ở trạng thái chờ xác nhận. Hiện tại: {$booking->status}"
        ], 400);
    }

    // Line 758: Calls model's confirm() method
    $booking->confirm();

    // Line 769: Returns confirmed_at in response
    return response()->json([
        'success' => true,
        'message' => 'Xác nhận booking thành công',
        'booking' => [
            'id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'formatted_booking_code' => $booking->formatted_booking_code,
            'booking_id' => $booking->formatted_booking_code ?: ('BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT)),
            'status' => $booking->status,
            'confirmed_at' => $booking->confirmed_at,  // Returns timestamp
        ]
    ]);
}
```

**Route:** `POST /api/bookings/{bookingId}/confirm` (requires auth)

---

## Lock Expiration Job

**File:** `/Users/thaopv/Desktop/php/pickleball/app/Jobs/CancelExpiredTransferBookings.php`

```php
class CancelExpiredTransferBookings implements ShouldQueue
{
    public function handle(): void
    {
        $now = time();

        // Lines 32-35: Find expired transfer bookings
        $expiredBookings = Booking::where('status', 'pending')
            ->where('payment_method', 'transfer')
            ->whereNotNull('lock_expires_at')
            ->where('lock_expires_at', '<=', $now)  // Compares Unix timestamp
            ->get();

        foreach ($expiredBookings as $booking) {
            // Line 40: Cancels expired booking
            $booking->cancel();
            
            // Line 43: Logs cancellation with lock expiration time
            \Log::info("Cancelled expired transfer booking #{$booking->id} (expires at: {$booking->lock_expires_at})");
        }
    }
}
```

**Purpose:** Periodically cancels transfer bookings where lock has expired.

---

## Overlap Detection Query

**File:** `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Api/BookingController.php`

**Lines 617-687: Booking availability check**
```php
// Line 620: Fetches booked slots with lock_expires_at
$bookings = Booking::where('court_id', $court->id)
    ->where('booking_date', $date)
    ->whereIn('status', ['confirmed', 'pending', 'pending_payment'])
    ->get(['start_time', 'end_time', 'status', 'payment_method', 'lock_expires_at']);

// Lines 684-687: Lock check in overlap detection
if ($booked['payment_method'] == 'transfer' 
    && $booked['lock_expires_at'] !== null 
    && $booked['lock_expires_at'] > time()) {
    $isLocked = true;  // Slot is reserved
} else {
    $isPending = true;  // Slot might be available
}
```

**Logic:** 
- If booking is pending + transfer + lock not expired = slot LOCKED
- Otherwise = slot available (even if pending)

---

## Status Values

| Status | Payment Method | Lock State | Meaning |
|--------|---|---|---|
| `pending` | cash | null | Awaiting customer payment |
| `pending` | transfer | Unix timestamp | Locked for 15 minutes (customer must pay) |
| `pending_payment` | transfer | null (job-based) | Awaiting payment (API-specific) |
| `confirmed` | any | null | Admin confirmed, booking locked |
| `cancelled` | any | null | Booking cancelled |

---

## Relationships Summary

```
Booking Creation (transfer payment)
├─ lock_expires_at = now + 15 min (Unix timestamp)
├─ confirmed_at = null
├─ status = 'pending' OR 'pending_payment'
│
└─ Waiting for payment...
    ├─ If lock expires → CancelExpiredTransferBookings job cancels
    └─ If admin confirms → confirm() sets confirmed_at = now(), lock_expires_at = null
```

---

## Implementation Coverage

| Location | Lock Setup | Confirmed_at | Status |
|---|---|---|---|
| Front HomeController.bookingCourt | YES (15 min) | NULL | ✓ COMPLETE |
| Api BookingController.store | NO (job-based) | NOT SET | INCOMPLETE |
| Api BookingController.bookingCourt | NO | NOT SET | INCOMPLETE |
| HomeYardTournamentController.bookingCourt | NO | NOT SET | INCOMPLETE |

---

## Unresolved Questions

1. Why does API BookingController::store use `pending_payment` status + job-based expiration instead of consistent `pending` + `lock_expires_at`?
2. Should HomeYardTournamentController and bookingCourt API endpoint add lock mechanism for transfer payments?
3. Is `lock_expires_at` casted as integer in model intentional (not in $casts array)?
4. When should old bookings (null booking_code) have confirmed_at set if already confirmed?

