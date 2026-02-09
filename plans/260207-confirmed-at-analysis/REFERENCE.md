# Technical Reference: confirmed_at & lock_expires_at

## Column Specifications

### confirmed_at
```
Type:        TIMESTAMP
Nullable:    YES (DEFAULT NULL)
Collation:   utf8mb4_unicode_ci
Position:    After 'status' column
Cast:        datetime (Carbon instance)
```

### lock_expires_at  
```
Type:        INT(11)
Nullable:    YES (DEFAULT NULL)
Collation:   N/A (numeric)
Position:    After 'confirmed_at' column
Comment:     'Unix timestamp khi khóa hết hạn'
Cast:        NONE (raw integer, should be 'integer')
```

---

## Model Definition

### Fillable Array
```php
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
    'confirmed_at',      // [ADD] For direct assignment
    'lock_expires_at',   // [ADD] For direct assignment
];
```

### Casts Array
```php
protected $casts = [
    'booking_date' => 'date',
    'duration_hours' => 'float',
    'hourly_rate' => 'integer',
    'total_price' => 'integer',
    'service_fee' => 'integer',
    'start_time' => 'string',
    'end_time' => 'string',
    'confirmed_at' => 'datetime',
    // [MISSING] 'lock_expires_at' => 'integer',
];
```

---

## Method Signatures

### confirm()
```php
/**
 * Confirm the booking.
 * Sets status to confirmed, timestamp to now, clears lock.
 */
public function confirm(): void
{
    $this->update([
        'status' => 'confirmed',
        'confirmed_at' => now(),    // Carbon\Carbon::now()
        'lock_expires_at' => null,
    ]);
}
```

### isLocked()
```php
/**
 * Check if booking is locked.
 * Locked = pending + transfer + lock_expires_at not expired
 * 
 * @return bool TRUE if slot is reserved, FALSE if available
 */
public function isLocked(): bool
{
    return $this->status === 'pending' 
        && $this->payment_method === 'transfer'
        && $this->lock_expires_at !== null
        && $this->lock_expires_at > time();
}
```

### isLockExpired()
```php
/**
 * Check if lock has expired.
 * 
 * @return bool TRUE if lock timestamp <= current time
 */
public function isLockExpired(): bool
{
    return $this->lock_expires_at !== null && $this->lock_expires_at <= time();
}
```

---

## Database Queries

### Check Lock Status
```php
// Find locked bookings for a slot
$locked = Booking::where('court_id', $courtId)
    ->where('booking_date', $date)
    ->where('status', 'pending')
    ->where('payment_method', 'transfer')
    ->whereNotNull('lock_expires_at')
    ->where('lock_expires_at', '>', time())
    ->first();
```

### Find Expired Bookings
```php
// Find transfer bookings past their lock time
$expired = Booking::where('status', 'pending')
    ->where('payment_method', 'transfer')
    ->whereNotNull('lock_expires_at')
    ->where('lock_expires_at', '<=', time())
    ->get();
```

### Find Confirmed Bookings
```php
// Find confirmed bookings with timestamp
$confirmed = Booking::where('status', 'confirmed')
    ->whereNotNull('confirmed_at')
    ->whereBetween('confirmed_at', [$start, $end])
    ->get();
```

---

## HTTP Endpoints

### Confirm Booking (Admin)
```
POST /api/bookings/{bookingId}/confirm
Authorization: Bearer {token}

Response:
{
    "success": true,
    "message": "Xác nhận booking thành công",
    "booking": {
        "id": 123,
        "booking_code": "BK001260207001",
        "formatted_booking_code": "BK001-260207-001",
        "status": "confirmed",
        "confirmed_at": "2026-02-07 14:30:45"  // SET HERE
    }
}
```

### Reject Booking (Admin)
```
POST /api/bookings/{bookingId}/reject
Authorization: Bearer {token}
Content-Type: application/json

{
    "reason": "Tài khoản không hợp lệ"
}

Response:
{
    "success": true,
    "message": "Hủy booking thành công",
    "booking": {
        "id": 123,
        "status": "cancelled",
        "confirmed_at": null  // NOT SET
    }
}
```

---

## Status State Machine

### Transfer Payment Flow
```
NEW BOOKING
├─ status: 'pending'
├─ confirmed_at: NULL
└─ lock_expires_at: 1707312645 (now + 15min)
    │
    ├─→ ADMIN CONFIRMS
    │   ├─ status: 'confirmed'
    │   ├─ confirmed_at: 1707312603 (SET)
    │   └─ lock_expires_at: NULL (CLEARED)
    │   └─ FINAL STATE ✓
    │
    └─→ LOCK EXPIRES (job)
        ├─ status: 'cancelled'
        ├─ confirmed_at: NULL
        └─ lock_expires_at: [unchanged]
        └─ FINAL STATE
```

### Cash Payment Flow
```
NEW BOOKING
├─ status: 'pending'
├─ confirmed_at: NULL
└─ lock_expires_at: NULL (NO LOCK)
    │
    └─→ ADMIN CONFIRMS
        ├─ status: 'confirmed'
        ├─ confirmed_at: 1707312603 (SET)
        └─ lock_expires_at: NULL
        └─ FINAL STATE ✓
```

---

## Booking Creation Patterns

### Pattern 1: Front HomeController (CORRECT)
```php
$lockExpiresAt = null;
if ($request->payment_method === 'transfer') {
    $lockExpiresAt = time() + (15 * 60);  // 900 seconds
}

DB::transaction(function () use (..., $lockExpiresAt) {
    Booking::create([
        // ... fields ...
        'status' => $status,
        'payment_method' => $request->payment_method,
        'confirmed_at' => null,
        'lock_expires_at' => $lockExpiresAt,  // [CORRECT]
    ]);
});
```

### Pattern 2: API store() (INCOMPLETE)
```php
$status = $request->payment_method === 'transfer' 
    ? 'pending_payment' 
    : 'pending';

Booking::create([
    // ... fields ...
    'status' => $status,
    'payment_method' => $request->payment_method,
    // [MISSING] confirmed_at
    // [MISSING] lock_expires_at
]);

// Uses job fallback:
if ($request->payment_method === 'transfer') {
    Bus::dispatch(new CancelUnpaidBooking($booking->id, 300));  // 5 min
}
```

### Pattern 3: API bookingCourt() (INCOMPLETE)
```php
Booking::create([
    // ... fields ...
    'status' => 'pending',
    'payment_method' => $request->payment_method,
    // [MISSING] confirmed_at
    // [MISSING] lock_expires_at
]);
```

### Pattern 4: Tournament Controller (INCOMPLETE)
```php
Booking::create([
    // ... fields ...
    'status' => $request->status ?? 'pending',
    'payment_method' => $request->payment_method,
    // [MISSING] confirmed_at
    // [MISSING] lock_expires_at
]);
```

---

## Validation Rules

When creating transfer payment bookings, ensure:

1. `lock_expires_at` is set to Unix timestamp
2. `lock_expires_at` = `time() + (15 * 60)`
3. `confirmed_at` is NULL on creation
4. `payment_method` is 'transfer'
5. `status` is 'pending' (not pending_payment)

When confirming:

1. Only 'pending' status can be confirmed
2. Sets `confirmed_at = now()`
3. Sets `lock_expires_at = null`
4. Changes status to 'confirmed'

---

## Common Mistakes

### Mistake 1: Not Setting lock_expires_at for Transfer
```php
// WRONG - No lock, double-booking possible
Booking::create([
    'payment_method' => 'transfer',
    // Missing: lock_expires_at
]);
```

### Mistake 2: Setting lock_expires_at as DateTime
```php
// WRONG - Wrong type, comparison fails
$booking->lock_expires_at = now()->addMinutes(15);  // DateTime

// RIGHT - Unix timestamp
$booking->lock_expires_at = time() + (15 * 60);  // Integer
```

### Mistake 3: Not Clearing lock_expires_at on Confirmation
```php
// WRONG - Lock remains after confirmation
$booking->update(['status' => 'confirmed', 'confirmed_at' => now()]);

// RIGHT - Clear the lock
$booking->update([
    'status' => 'confirmed',
    'confirmed_at' => now(),
    'lock_expires_at' => null,  // [IMPORTANT]
]);
```

### Mistake 4: Checking Lock at Wrong Time
```php
// WRONG - Compares Carbon datetime to Unix timestamp
if ($booking->lock_expires_at > now()) { }

// RIGHT - Compare Unix timestamps
if ($booking->lock_expires_at > time()) { }
```

---

## Performance Considerations

### Index Recommendations
```sql
-- Add for lock expiration queries
ALTER TABLE bookings ADD INDEX idx_lock_expires (lock_expires_at);

-- Add for overlap detection
ALTER TABLE bookings ADD INDEX idx_transfer_lock 
    (payment_method, lock_expires_at, status);

-- Add for confirmation audit
ALTER TABLE bookings ADD INDEX idx_confirmed_at (confirmed_at);
```

### Query Optimization
```php
// SLOW - Fetches all columns
$bookings = Booking::where('status', 'pending')->get();

// FAST - Fetch only needed columns
$bookings = Booking::where('status', 'pending')
    ->select(['id', 'status', 'payment_method', 'lock_expires_at'])
    ->get();

// FASTEST - Use raw query for reporting
$bookings = DB::select(
    "SELECT id, lock_expires_at FROM bookings 
     WHERE status = ? AND lock_expires_at <= ?",
    ['pending', time()]
);
```

---

## Testing Checklist

```php
// Test: Create transfer booking sets lock
$booking = Booking::create([...transfer...]);
$this->assertNotNull($booking->lock_expires_at);
$this->assertTrue($booking->isLocked());

// Test: Create cash booking no lock
$booking = Booking::create([...cash...]);
$this->assertNull($booking->lock_expires_at);
$this->assertFalse($booking->isLocked());

// Test: Confirm clears lock
$booking->confirm();
$this->assertNull($booking->lock_expires_at);
$this->assertEquals('confirmed', $booking->status);
$this->assertNotNull($booking->confirmed_at);

// Test: Expired lock detected
$booking->lock_expires_at = time() - 1;
$this->assertTrue($booking->isLockExpired());
```

