# Booking Flow Diagrams & Quick Reference

## 1. BOOKING CREATION FLOW

```
┌─────────────────────────────────────────────────────────────────────┐
│                    BOOKING REQUEST                                   │
│         (3 Paths: API store | API bookingCourt | Front bookingCourt) │
└────────────────────────────┬────────────────────────────────────────┘
                             │
                ┌────────────┴────────────┐
                │                         │
        ┌───────▼────────┐       ┌───────▼────────┐
        │  Validate      │       │  Check Court   │
        │  Input Data    │       │  Existence     │
        └───────┬────────┘       └───────┬────────┘
                │                        │
                └────────┬───────────────┘
                         │
                ┌────────▼────────────┐
                │ Calculate Times    │
                │ & Pricing          │
                └────────┬────────────┘
                         │
                ┌────────▼────────────────────────┐
                │ Check Time Conflict             │
                │ (existing bookings)             │
                └────────┬───────────────────────┘
                         │
        ┌────────────────▼──────────────────────┐
        │      DB TRANSACTION BEGIN             │
        │                                        │
        │  1. Generate Booking Code             │
        │     (lockForUpdate + sequence)        │
        │                                        │
        │  2. Create Booking Record             │
        │     with all data + lock_expires_at  │
        │                                        │
        └────────────────┬──────────────────────┘
                         │
                ┌────────▼────────────┐
                │  Payment Method?   │
                └────┬───────┬───────┬┘
                     │       │       │
         ┌───────────┘       │       └──────────────┐
         │                   │                      │
    ┌────▼────┐      ┌──────▼──────┐      ┌───────▼────┐
    │ transfer │      │ cash/card   │      │  wallet    │
    │          │      │ /wallet     │      │            │
    └────┬─────┘      └──────┬──────┘      └───────┬────┘
         │                   │                     │
    API Path 1:         All Paths:           All Paths:
    ├─ Status:          ├─ Status: pending   ├─ Status:
    │  pending_payment  │ ├─ Lock: null      │  pending
    │ ├─ Lock: null     │ └─ Done            │ ├─ Lock: null
    │ └─ DISPATCH JOB   │                    │ └─ Done
    │   (5 min delay)   │                    │
    │   CancelUnpaid    │                    │
    │                   │                    │
    Front/API Path 2-3: │                    │
    ├─ Status: pending  │                    │
    │ ├─ Lock: time() + │                    │
    │ │  900 (15 min)   │                    │
    │ └─ Done           │                    │
    │                   │                    │
    └───────────────────┴────────────────────┘
              Response: Booking Created
```

---

## 2. COURT AVAILABILITY CHECK FLOW

```
┌──────────────────────────────────────────────┐
│  GET AVAILABLE SLOTS REQUEST                 │
│  (court_id, date)                            │
└──────────────────────────┬───────────────────┘
                           │
                ┌──────────▼───────────┐
                │ Parse Date & Day    │
                │ Of Week             │
                └──────────┬──────────┘
                           │
        ┌──────────────────▼────────────────────┐
        │ Query Bookings for Court on Date     │
        │                                       │
        │ Status IN: [confirmed, pending,       │
        │            pending_payment]           │
        │ (exclude cancelled)                  │
        └──────────────────┬────────────────────┘
                           │
                ┌──────────▼──────────┐
                │ For Each Time Slot  │
                │ (hour by hour)      │
                └──────────┬──────────┘
                           │
           ┌───────────────▼──────────────────┐
           │ Get Pricing for This Hour        │
           │ (from court_pricing table)       │
           └───────────────┬──────────────────┘
                           │
           ┌───────────────▼──────────────────┐
           │ Check Overlap with Bookings      │
           └───────────────┬──────────────────┘
                           │
               ┌───────────┼───────────┐
               │           │           │
        ┌──────▼──┐  ┌─────▼────┐  ┌──▼───────┐
        │No Match │  │Pending   │  │Confirmed │
        │         │  │Booking   │  │Booking   │
        └──────┬──┘  └─────┬────┘  └──┬───────┘
               │           │          │
        ┌──────▼──┐   ┌─────▼────┐  ┌─▼──────────┐
        │AVAILABLE│   │LOCK CHECK?│  │NOT BOOKABLE│
        │         │   └─────┬────┘  │ is_booked: │
        │is_booked│         │       │ true       │
        │ false   │         │       └────────────┘
        │is_locked│    ┌────┴─────────────┐
        │ false   │    │                  │
        └─────────┘    │ Payment=transfer?│
                       │ Lock not expired?│
                       │                  │
                   ┌───▼──┐      ┌───────▼──┐
                   │LOCKED│      │PENDING   │
                   │      │      │          │
                   │is_   │      │is_       │
                   │locked│      │pending   │
                   │ true │      │ true     │
                   └──────┘      │is_locked │
                                 │ false    │
                                 └──────────┘
                                 
               Response: Time Slots Array
               with is_booked, is_pending, is_locked flags
```

---

## 3. LOCK/UNLOCK LIFECYCLE

```
┌─────────────────────────────────────────────────────────┐
│ BOOKING CREATED WITH TRANSFER PAYMENT (Path 2)          │
│                                                         │
│ Status: pending                                         │
│ Payment: transfer                                       │
│ Lock_expires_at: time() + 900 (UNIX timestamp 15 min) │
└──────────────────────┬──────────────────────────────────┘
                       │
            ┌──────────▴──────────┐
            │                     │
      ┌─────▼─────┐       ┌──────▼──────┐
      │ LOCKED    │       │ Court in    │
      │ STATE     │       │ "is_locked" │
      │           │       │ status for  │
      │ Others    │       │ availability│
      │ cannot    │       │ check       │
      │ book this │       └──────┬──────┘
      │ slot      │              │
      └─────┬─────┘              │
            │                    │
            │   ┌────────────────┴──────────┐
            │   │                           │
    ADMIN CONFIRMS            LOCK EXPIRES
    (manual action)           (after 15 min)
            │                           │
    ┌───────▼─────────┐        ┌───────▼──────────┐
    │ confirmBooking  │        │ CancelExpired    │
    │ API Endpoint    │        │ TransferBookings │
    │                 │        │ Job (scheduled)  │
    │ Update to:      │        │                  │
    │ - status:       │        │ Updates to:      │
    │   confirmed     │        │ - status:        │
    │ - confirmed_at: │        │   cancelled      │
    │   now()         │        └───────┬──────────┘
    │ - lock_expires_ │                │
    │   at: null      │       Slot Available
    │                 │       for New Booking
    └───────┬─────────┘
            │
    Slot Locked
    until confirmed
            │
    ┌───────▼──────────┐
    │ CONFIRMED STATE  │
    │                  │
    │ Slot is reserved │
    │ (is_booked:true) │
    │ for this booking │
    └──────────────────┘
```

---

## 4. KEY LOCK LOGIC IN CODE

```
Booking::isLocked() [Booking Model]

Returns TRUE if ALL conditions met:
  ✓ $this->status === 'pending'
  ✓ $this->payment_method === 'transfer'
  ✓ $this->lock_expires_at !== null
  ✓ $this->lock_expires_at > time()  [NOT EXPIRED]

Returns FALSE if ANY condition fails
```

---

## 5. BOOKING CODE SEQUENCE

```
Court ID: 001
Date: 2026-02-07
Format: BK{court:3}{date:YYMMDD}{seq:3}

First booking:
  BK + 001 + 260207 + 001 = BK001260207001
  Displayed: BK001-260207-001

Second booking (same court, same date):
  BK + 001 + 260207 + 002 = BK001260207002
  Displayed: BK001-260207-002

...continues up to 999 per court per day

Overflow protection: Throws OverflowException if > 999
```

---

## 6. ADMIN BOOKING MANAGEMENT

```
┌──────────────────────────────────────┐
│ HOMEYARD TOURNAMENT CONTROLLER       │
│ (Stadium Owner Dashboard)            │
└──────┬───────────────────────────────┘
       │
    ┌──┴──────────────────────────────┐
    │                                  │
┌───▼────────────┐        ┌───────────▼───┐
│ searchBookings │        │ getBookingInfo│
│                │        │               │
│ Input:         │        │ Input:        │
│ - court_id     │        │ - booking_id  │
│ - date_from    │        │               │
│ - date_to      │        │ Output:       │
│ - keyword      │        │ - Full booking│
│                │        │   details     │
│ Output:        │        │ - Formatted   │
│ - Paginated    │        │   code        │
│   results      │        │ - All fields  │
│ - Formatted    │        └───────────────┘
│   booking codes│
│ - Court names  │
└────────────────┘
           │
      Admin Actions:
      ├─ confirmBooking()
      │  └─ Sets status: confirmed
      │     Clears lock_expires_at
      │
      └─ rejectBooking()
         └─ Sets status: cancelled
            Slot available for rebook
```

---

## 7. STATUS & LOCK STATES MATRIX

```
╔════════════════════════════════════════════════════════════════════╗
║ STATUS         │ PAYMENT  │ LOCK_EXPIRES_AT │ SLOT STATE         ║
╠════════════════════════════════════════════════════════════════════╣
║ pending        │ cash     │ NULL            │ Available          ║
║ pending        │ card     │ NULL            │ Available          ║
║ pending        │ wallet   │ NULL            │ Available          ║
║ pending        │ transfer │ future timestamp│ LOCKED (15 min)    ║
║ pending        │ transfer │ past timestamp  │ Available (expired)║
║ pending_payment│ transfer │ NULL            │ Awaiting Payment   ║
║ confirmed      │ *        │ NULL            │ BOOKED             ║
║ cancelled      │ *        │ NULL/any        │ Available          ║
╚════════════════════════════════════════════════════════════════════╝

LOCKED means: No other user can book this time slot
             Admin must confirm or wait for expiry
```

---

## 8. TIMELINE OF A TRANSFER PAYMENT BOOKING

```
TIME    │ EVENT                                 │ STATE
────────┼───────────────────────────────────────┼─────────────
T+0     │ Booking created                       │ pending
        │ - lock_expires_at = now + 15 min      │ LOCKED
        │ - slot reserved for this customer     │
        │                                       │
T+5 min │ Customer confirms payment (manual)    │ pending
        │ Admin clicks confirmBooking           │ → confirmed
        │ - lock_expires_at = null              │ BOOKED
        │ - confirmed_at = now()                │
        │ - slot fully reserved                 │
        │                                       │
        │ OR                                    │
        │                                       │
T+15 min│ No payment / Lock expires             │ pending
        │ CancelExpiredTransferBookings job     │ → cancelled
        │ auto-cancels this booking             │ AVAILABLE
        │ - status = cancelled                  │ (for rebook)
        │ - lock_expires_at cleared             │
        │ - others can book this slot           │
```

---

## 9. DIFFERENCES BETWEEN 3 BOOKING PATHS

```
╔═══════════════════════════════════════════════════════════════════╗
║                 API::store      API::bookingCourt   Front::booking║
║                 (Path 1)        (Path 2)            (Path 2)      ║
╠═══════════════════════════════════════════════════════════════════╣
║ Used By        │ Mobile App     │ Web API            │ Web Form    ║
╠════════════════╪════════════════╪════════════════════╪═════════════╣
║ Transfer       │ pending_      │ pending            │ pending     ║
║ Payment Status │ payment       │                    │             ║
║                │ (auto-cancel  │ (manual confirm/   │ (manual)    ║
║                │  after 5 min) │  reject)           │             ║
╠════════════════╪════════════════╪════════════════════╪═════════════╣
║ Lock Mechanism │ None           │ lock_expires_at    │ lock_expires║
║                │                │ = +15 min          │ _at = +15 min║
║                │                │ (on creation)      │ (on creation)║
╠════════════════╪════════════════╪════════════════════╪═════════════╣
║ Admin Action   │ N/A            │ confirm/reject     │ confirm/    ║
║                │ (auto-cancel)  │ via API endpoint   │ reject      ║
╠════════════════╪════════════════╪════════════════════╪═════════════╣
║ Availability   │ Pending/Booked │ Locked/Pending/    │ Locked/Pending║
║ Check          │ (no lock check)│ Booked (lock check)│ /Booked (no  ║
║                │                │                    │ lock check)  ║
╚════════════════╧════════════════╧════════════════════╧═════════════╝
```

---

## 10. DATABASE COLUMNS INVOLVED

```
bookings table
├─ booking_code (string, 14) ← Generated BK001260207001
├─ status (string)            ← pending | confirmed | cancelled | pending_payment
├─ payment_method (string)    ← cash | card | transfer | wallet
├─ confirmed_at (timestamp)   ← When admin confirmed (null = not confirmed)
├─ lock_expires_at (integer)  ← Unix timestamp OR null
├─ court_id (int)             ← Foreign key
├─ booking_date (date)        ← YYYY-MM-DD
├─ start_time (varchar)       ← HH:MM
├─ end_time (varchar)         ← HH:MM
├─ customer_name (string)
├─ customer_phone (string)
├─ total_price (int)
├─ service_fee (int)
└─ ... other fields

Index: bookings_court_date_code_idx
       ON (court_id, booking_date, booking_code)
```

