# Booking Creation & Court Locking Flow - Scout Report

## Summary
Comprehensive mapping of booking creation paths, court availability checking, and court lock/unlock mechanisms across 3 booking creation endpoints with server-side booking code generation and transfer payment locking.

---

## 1. BOOKING CREATION PATHS (3 Entry Points)

### Path 1: API - BookingController::store()
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Api/BookingController.php` (lines 99-173)

**Flow**:
1. Validate input (court_id, booking_date, start_time, duration_hours, payment_method, etc.)
2. Find court by ID
3. Calculate end_time from start_time + duration_hours
4. Calculate fees (5% service fee)
5. Determine status based on payment_method:
   - `transfer` → `pending_payment` (triggers auto-cancel job after 5 min)
   - Other methods → `pending`
6. Generate booking_code inside DB::transaction
7. Create booking with all fields
8. If transfer payment: dispatch CancelUnpaidBooking job (300 sec = 5 min delay)

**Key Code Snippet**:
```php
// Lines 130-161
$status = $request->payment_method === 'transfer' ? 'pending_payment' : 'pending';

$booking = DB::transaction(function () use ($request, $endTime, $subtotal, $serviceFee, $status) {
    $bookingCode = Booking::generateBookingCode($request->court_id, $request->booking_date);
    return Booking::create([...]);
});

if ($request->payment_method === 'transfer') {
    Bus::dispatch(new CancelUnpaidBooking($booking->id, 300)); // 5 min
}
```

---

### Path 2: Front - HomeController::bookingCourt()
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/HomeController.php` (lines 210-319)

**Flow**:
1. Validate input (court_id, customer details, booking_date, start_time, duration_hours, payment_method)
2. Find court by ID
3. Calculate end_time
4. Recalculate total price server-side with multi-price support
5. Check for existing bookings (time conflict check)
6. Determine lock based on payment_method:
   - `transfer` → lock_expires_at = now + 900 sec (15 min)
   - Other → lock_expires_at = null
7. Generate booking_code inside DB::transaction
8. Create booking with lock_expires_at set

**Key Code Snippet**:
```php
// Lines 270-305
$status = 'pending';
$lockExpiresAt = null;

if ($request->payment_method === 'transfer') {
    // Lock for 15 minutes for transfer payments
    $lockExpiresAt = time() + (15 * 60);
}

$booking = DB::transaction(function () use ($request, $endTime, $durationHours, $totalPrice, $status, $lockExpiresAt) {
    $bookingCode = Booking::generateBookingCode($request->court_id, $request->booking_date);
    return Booking::create([
        'booking_code' => $bookingCode,
        'lock_expires_at' => $lockExpiresAt,
        ...
    ]);
});
```

**Conflict Check** (lines 257-268):
```php
$existingBooking = Booking::where('court_id', $request->court_id)
    ->where('booking_date', $request->booking_date)
    ->where('status', '!=', 'cancelled')
    ->whereRaw("TIME(start_time) < ? AND TIME(end_time) > ?", [$endTime, $request->start_time])
    ->first();
```

---

### Path 3: API - BookingController::bookingCourt()
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Api/BookingController.php` (lines 301-394)

**Flow**:
1. Validate input (same as Path 2)
2. Find court by ID
3. Calculate end_time and duration
4. Recalculate total price server-side
5. Check for existing bookings (same conflict logic as Path 2)
6. Generate booking_code inside DB::transaction
7. Create booking with status = 'pending' (NO lock_expires_at set)

**Key Code Snippet**:
```php
// Lines 358-380
$booking = DB::transaction(function () use ($request, $endTime, $durationHours, $hourlyRate, $totalPrice, $serviceFee) {
    $bookingCode = Booking::generateBookingCode($request->court_id, $request->booking_date);
    return Booking::create([
        'booking_code' => $bookingCode,
        'status' => 'pending',
        'payment_method' => $request->payment_method,
        ...
    ]);
});
```

---

## 2. COURT AVAILABILITY CHECKING

### Method 1: BookingController::getAvailableSlots()
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Api/BookingController.php` (lines 440-556)

**Query Bookings** (lines 457-469):
```php
$bookings = Booking::where('court_id', $court->id)
    ->where('booking_date', $date)
    ->where('status', '!=', 'cancelled')
    ->get(['start_time', 'end_time', 'status']);
```

**Status Check** (lines 522-530):
```php
if ($slotTime < $bookedEnd && $currentSlotEnd > $bookedStart && $booked['status'] != 'cancelled') {
    if($booked['status'] == 'pending') {
        $isPending = true;  // Show as pending, still bookable
    } else {
        $isBooked = true;   // Confirmed, not bookable
    }
}
```

---

### Method 2: BookingController::getAvailableSlotsForAllCourts()
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Api/BookingController.php` (lines 561-721)

**Query Bookings** (lines 617-620):
```php
$bookings = Booking::where('court_id', $court->id)
    ->where('booking_date', $date)
    ->whereIn('status', ['confirmed', 'pending', 'pending_payment'])
    ->get(['start_time', 'end_time', 'status', 'payment_method', 'lock_expires_at']);
```

**Lock Checking** (lines 681-694):
```php
if ($slotTime < $bookedEnd && $currentSlotEnd > $bookedStart && $booked['status'] != 'cancelled') {
    if ($booked['status'] == 'pending') {
        // Check if transfer payment is locked (not expired)
        if ($booked['payment_method'] == 'transfer' && 
            $booked['lock_expires_at'] !== null && 
            $booked['lock_expires_at'] > time()) {
            $isLocked = true;   // Locked, cannot book
        } else {
            $isPending = true;  // Not locked, may be bookable
        }
    } else {
        $isBooked = true;       // Confirmed, cannot book
    }
}
```

---

### Method 3: HomeController::getAvailableSlots()
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/HomeController.php` (lines 95-208)

Same as Method 1 (no lock check).

---

## 3. LOCK/UNLOCK MECHANISMS

### Lock Structure
- **Column**: `lock_expires_at` (integer, Unix timestamp)
- **Stored in**: bookings table
- **Set by**: HomeController::bookingCourt() for transfer payments
- **Duration**: 15 minutes (900 seconds) from booking creation

### Lock Conditions (Booking Model)
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Models/Booking.php` (lines 103-117)

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
        && $this->lock_expires_at > time();
}

/**
 * Check if lock has expired
 */
public function isLockExpired(): bool
{
    return $this->lock_expires_at !== null && $this->lock_expires_at <= time();
}
```

### Unlock Methods

#### Method 1: Manual Confirmation (Admin)
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Api/BookingController.php` (lines 727-783)

```php
public function confirmBooking($bookingId, Request $request)
{
    // ... authorization checks ...
    
    // Only pending bookings can be confirmed
    if ($booking->status !== 'pending') {
        return error response
    }

    // Confirm the booking
    $booking->confirm();  // Lines 758
}
```

**Confirm Method** (Booking Model, lines 90-97):
```php
public function confirm(): void
{
    $this->update([
        'status' => 'confirmed',
        'confirmed_at' => now(),
        'lock_expires_at' => null,  // Clear lock
    ]);
}
```

#### Method 2: Auto-Cancel via Job (Expired Lock)
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Jobs/CancelExpiredTransferBookings.php` (lines 27-45)

```php
public function handle(): void
{
    $now = time();

    // Find pending transfer bookings with expired lock
    $expiredBookings = Booking::where('status', 'pending')
        ->where('payment_method', 'transfer')
        ->whereNotNull('lock_expires_at')
        ->where('lock_expires_at', '<=', $now)
        ->get();

    foreach ($expiredBookings as $booking) {
        $booking->cancel();
        Log::info("Cancelled expired transfer booking #{$booking->id}");
    }
}
```

#### Method 3: Manual Rejection (Admin)
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Api/BookingController.php` (lines 788-843)

```php
public function rejectBooking($bookingId, Request $request)
{
    // ... authorization checks ...
    
    // Only pending bookings can be rejected
    if ($booking->status !== 'pending') {
        return error response
    }

    $booking->cancel();  // Sets status to 'cancelled'
}
```

---

## 4. TIMEOUT/EXPIRY LOGIC

### Path 1 API (Api/BookingController::store()) - 5 Min Auto-Cancel
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Jobs/CancelUnpaidBooking.php` (lines 22-45)

```php
public function __construct($bookingId, $delay = 300)  // 300 sec = 5 min
{
    $this->bookingId = $bookingId;
    $this->delay = $delay;
}

public function handle(): void
{
    $booking = Booking::find($this->bookingId);
    
    // Only cancel if still in pending_payment status
    if ($booking->status === 'pending_payment') {
        $booking->update(['status' => 'cancelled']);
        Log::info('Booking ' . $booking->id . ' auto-cancelled due to unpaid transfer');
    }
}
```

**Dispatch Code** (Api/BookingController, lines 157-161):
```php
if ($request->payment_method === 'transfer') {
    Bus::dispatch(new CancelUnpaidBooking($booking->id, 300)); // 5 minutes
}
```

### Path 2 Front (Front/HomeController::bookingCourt()) - 15 Min Lock
- No automatic cancellation job
- Lock expires after 15 minutes (timestamp stored in lock_expires_at)
- Admin must manually confirm or reject
- CancelExpiredTransferBookings job clears expired locks

---

## 5. BOOKING CODE GENERATION

**File**: `/Users/thaopv/Desktop/php/pickleball/app/Models/Booking.php` (lines 123-153)

**Format**: `BK{courtId:3}{date:YYMMDD}{seq:3}`
- Example: `BK001260207001` (formatted: `BK001-260207-001`)

**Code**:
```php
public static function generateBookingCode(int $courtId, string $bookingDate): string
{
    if ($courtId < 1 || $courtId > 999) {
        throw new InvalidArgumentException("Court ID must be 1-999, got {$courtId}");
    }

    $date = Carbon::parse($bookingDate);
    $datePart = $date->format('ymd');  // YYMMDD
    $courtPart = str_pad($courtId, 3, '0', STR_PAD_LEFT);
    $prefix = 'BK' . $courtPart . $datePart;

    // Lock for update + get last booking code same court, same date
    $lastCode = static::where('court_id', $courtId)
        ->whereDate('booking_date', $date->toDateString())
        ->whereNotNull('booking_code')
        ->lockForUpdate()  // Row-level lock
        ->orderByDesc('booking_code')
        ->value('booking_code');

    if ($lastCode) {
        $lastSeq = (int) substr($lastCode, -3);
        $nextSeq = $lastSeq + 1;
    } else {
        $nextSeq = 1;
    }

    if ($nextSeq > 999) {
        throw new OverflowException("Booking sequence overflow for court {$courtId}");
    }

    return $prefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
}
```

**Formatted Output**:
```php
public function getFormattedBookingCodeAttribute(): string
{
    if (!$this->booking_code) {
        return '';
    }
    // BK001260207001 -> BK001-260207-001
    return substr($this->booking_code, 0, 5) . '-'
         . substr($this->booking_code, 5, 6) . '-'
         . substr($this->booking_code, 11, 3);
}
```

---

## 6. SEARCH & RETRIEVAL ENDPOINTS

### SearchBookings (Admin/Stadium Owner)
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/HomeYardTournamentController.php` (lines 3053-3154)

**Returns formatted booking data** (lines 3118-3134):
```php
$formattedBookings = $bookings->getCollection()->map(function ($booking) {
    return [
        'id' => $booking->id,
        'booking_code' => $booking->booking_code,
        'formatted_booking_code' => $booking->formatted_booking_code,
        'court_id' => $booking->court_id,
        'court_name' => $booking->court->court_name,
        'customer_name' => $booking->customer_name,
        'customer_phone' => $booking->customer_phone,
        'booking_date' => $booking->booking_date,
        'start_time' => $booking->start_time,
        'end_time' => $booking->end_time,
        'status' => $booking->status,
        'payment_method' => $booking->payment_method,
    ];
});
```

### GetBookingDetails (Admin/Stadium Owner)
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/HomeYardTournamentController.php` (lines 3156-3196)

Returns full booking details including all fields.

---

## 7. DATABASE SCHEMA

### Migrations
- **Table**: `bookings`
- **Added Columns**:
  - `booking_code` (string, 14 chars, unique) - Lines 11-12 of 2026_02_07_add_booking_code_to_bookings_table.php
  - `confirmed_at` (timestamp, nullable) - Line 16 of 2026_02_07_add_confirmed_at_to_bookings.php
  - `lock_expires_at` (integer, nullable, Unix timestamp) - Line 18 of 2026_02_07_add_confirmed_at_to_bookings.php
  - Index on (court_id, booking_date, booking_code)

### Booking Model Fillable
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Models/Booking.php` (lines 15-34)
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
    'confirmed_at',
    'lock_expires_at',
];
```

---

## 8. PAYMENT METHOD BEHAVIOR

| Payment | Path 1 API | Path 2 Front | Path 3 API | Lock | Auto-Cancel |
|---------|-----------|------------|-----------|------|------------|
| `cash` | pending | pending | pending | No | No |
| `card` | pending | pending | pending | No | No |
| `transfer` | pending_payment | pending | pending | 15 min | 5 min job (API only) |
| `wallet` | pending | pending | pending | No | No |

---

## 9. KEY FILES SUMMARY

| File | Purpose |
|------|---------|
| `/app/Http/Controllers/Api/BookingController.php` | API booking endpoints (store, bookingCourt, getAvailableSlots) |
| `/app/Http/Controllers/Front/HomeController.php` | Front booking endpoint (bookingCourt) |
| `/app/Http/Controllers/Front/HomeYardTournamentController.php` | Admin search & details (searchBookings, getBookingDetails) |
| `/app/Models/Booking.php` | Model with generateBookingCode, isLocked, confirm, cancel |
| `/app/Jobs/CancelUnpaidBooking.php` | Auto-cancel after 5 min (API only) |
| `/app/Jobs/CancelExpiredTransferBookings.php` | Clear expired locks via scheduled job |
| `/database/migrations/2026_02_07_add_booking_code_to_bookings_table.php` | Add booking_code column |
| `/database/migrations/2026_02_07_add_confirmed_at_to_bookings.php` | Add confirmed_at & lock_expires_at |

---

## Unresolved Questions

None - Full flow documented with code references.
