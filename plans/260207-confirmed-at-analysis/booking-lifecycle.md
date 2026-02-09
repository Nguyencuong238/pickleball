# Booking Lifecycle: confirmed_at and lock_expires_at

## Sequence Diagram

```
Customer                Backend                Database
   |                      |                        |
   |--- Create Booking --->|                        |
   |                       | (transfer payment)     |
   |                       |-- INSERT booking ----->|
   |                       |   status: pending      |
   |                       |   confirmed_at: NULL   |
   |                       |   lock_expires_at: T+15m
   |                       |<-- booking created -----|
   |                       |                        |
   |                       | Dispatch cancel job    |
   |                       | (checks after 15 min)  |
   |                       |                        |
   |<-- Return booking ----|                        |
   |   (show payment info) |                        |
   |                       |                        |
   |== Waiting for Payment (max 15 minutes) ==      |
   |                       |                        |
   |-- Make Payment ------>|                        |
   |                       |-- Query verified ------>|
   |                       |<-- Payment OK ---------|
   |                       |                        |
   |-- Confirm Booking --->|                        |
   | (or auto-confirm)     |                        |
   |                       | UPDATE booking         |
   |                       |----- booking_id ------>|
   |                       |   status: confirmed    |
   |                       |   confirmed_at: NOW    |
   |                       |   lock_expires_at: NULL
   |                       |<-- Updated ------------|
   |                       |                        |
   |<-- Confirmation ------|                        |
   |    (show ticket)      |                        |
   |                       |                        |
```

---

## State Transitions

### For Transfer Payments

```
┌─────────────────┐
│   NEW BOOKING   │
│  payment_method │
│    = 'transfer' │
└────────┬────────┘
         │ Booking::create([
         │   status: 'pending',
         │   confirmed_at: NULL,
         │   lock_expires_at: time() + 900  ← LOCKED
         │ ])
         ▼
    ┌────────────────────┐
    │  LOCKED & PENDING  │ ◄──── Can NOT double-book this slot
    │ (Waiting for proof │      
    │  of payment up to  │      
    │ 15 minutes max)    │
    └────────┬───────────┘
             │ 
    ┌────────┴──────────────────────────────┐
    │                                        │
    │ Option A:                 Option B:
    │ Payment confirmed          Lock expires
    │ (admin clicks confirm)    (after 15 min)
    │                          
    ▼                          ▼
 ┌──────────────┐        ┌──────────────┐
 │  CONFIRMED   │        │  CANCELLED   │
 │  status: OK  │        │  status: OK  │
 │  confirmed   │        │  (auto-job)  │
 │  _at: NOW    │        │              │
 │  lock_expire │        │  Slot now    │
 │  _at: NULL   │        │  available   │
 └──────────────┘        └──────────────┘
 (Final, locked)         (Slot freed)
```

### For Cash Payments

```
┌─────────────────┐
│   NEW BOOKING   │
│  payment_method │
│     = 'cash'    │
└────────┬────────┘
         │ Booking::create([
         │   status: 'pending',
         │   confirmed_at: NULL,
         │   lock_expires_at: NULL  ← NOT LOCKED
         │ ])
         ▼
    ┌────────────────────┐
    │  PENDING (CASH)    │ ◄──── OTHER customers CAN book same slot
    │ (Waiting for       │      (subject to overlap rules)
    │  admin confirm)    │      
    └────────┬───────────┘
             │ Admin confirms
             ▼
         ┌──────────────┐
         │  CONFIRMED   │
         │  status: OK  │
         │  confirmed   │
         │  _at: NOW    │
         │  lock_expire │
         │  _at: NULL   │
         └──────────────┘
        (Final, locked)
```

---

## Method Relationship

```
Booking Model
├── fillable = [
│   └── 'confirmed_at',
│       'lock_expires_at'
│   ]
│
├── casts = [
│   ├── 'confirmed_at' => 'datetime'  ◄── Returns Carbon instance
│   └── 'lock_expires_at' NOT CASTED  ◄── Returns raw integer (Unix timestamp)
│   ]
│
├── confirm() {
│   └─► Sets:
│       ├── status = 'confirmed'
│       ├── confirmed_at = now()  ◄─ Set on confirmation
│       └── lock_expires_at = null  ◄─ Cleared on confirmation
│   }
│
├── isLocked() {
│   └─► Returns TRUE if:
│       ├── status === 'pending' AND
│       ├── payment_method === 'transfer' AND
│       ├── lock_expires_at !== null AND
│       └── lock_expires_at > time()  ◄─ Not yet expired
│   }
│
└── isLockExpired() {
    └─► Returns TRUE if:
        ├── lock_expires_at !== null AND
        └── lock_expires_at <= time()  ◄─ Past expiry
    }
```

---

## Lock Logic in Overlap Detection

```
When checking if a time slot is available:

FOR EACH booked slot:
    IF booking.status IN ['confirmed', 'pending', 'pending_payment']:
        IF time_overlap WITH booked slot:
            
            IF (payment_method == 'transfer' 
                AND lock_expires_at !== NULL 
                AND lock_expires_at > NOW):
                SLOT IS LOCKED ─► CANNOT BOOK
            
            ELSE IF status == 'confirmed':
                SLOT IS LOCKED ─► CANNOT BOOK
            
            ELSE:
                SLOT IS PENDING ─► MIGHT BE AVAILABLE
                (depends on overlap settings)
```

---

## Column Details

### confirmed_at
- **Type:** TIMESTAMP
- **Nullable:** YES
- **Default:** NULL
- **Cast:** datetime (Carbon instance)
- **Set by:** `Booking::confirm()` method
- **Value:** Current timestamp when admin confirms
- **Use Case:** Audit trail, customer receipt, statistics

### lock_expires_at  
- **Type:** INTEGER (11)
- **Nullable:** YES
- **Default:** NULL
- **Cast:** None (raw integer)
- **Set by:** Created during booking if `payment_method == 'transfer'`
- **Value:** Unix timestamp = `time() + (15 * 60)` = now + 900 seconds
- **Use Case:** Double-booking prevention, slot reservation

---

## Migration SQL

```sql
ALTER TABLE bookings ADD COLUMN confirmed_at TIMESTAMP NULL AFTER status;
ALTER TABLE bookings ADD COLUMN lock_expires_at INT NULL AFTER confirmed_at 
  COMMENT 'Unix timestamp khi khóa hết hạn';

-- Rollback
ALTER TABLE bookings DROP COLUMN confirmed_at, DROP COLUMN lock_expires_at;
```

