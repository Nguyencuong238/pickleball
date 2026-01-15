# Research Report: UserWallet, UserPointTransaction & Event System
**Date**: 2026-01-14 | **Phase**: Infrastructure Analysis

## Executive Summary
Pickleball codebase has foundational point wallet infrastructure with transaction tracking. Event-driven architecture exists for OCR matches but lacks comprehensive event coverage for point-earning triggers. Referral system partially implemented. Ready for point-earning system integration with minimal gaps.

---

## 1. UserWallet Model
**Location**: `app/Models/UserWallet.php`

### Structure
- **Fields**: `id`, `user_id`, `points` (integer cast), `timestamps`
- **Relationship**: `belongsTo(User::class)`
- **Fillable**: `user_id`, `points`

### Methods
| Method | Signature | Behavior |
|--------|-----------|----------|
| `addPoints()` | `addPoints(int $points, string $type='earn', string $description='', array $metadata=[])` | Increments wallet points, creates transaction record. No balance checks. |
| `deductPoints()` | `deductPoints(int $points, string $type='use', string $description='', array $metadata=[])` | Checks sufficient balance before decrement, creates negative transaction. Returns bool. |
| `transactions()` | - | Proxies to `user->pointTransactions()` |
| `getFormattedPoints()` | - | Returns `number_format($points)` for display |

### Design Notes
- **Transaction-driven**: Every point mutation creates audit trail via `UserPointTransaction`
- **No event emission**: Methods don't fire events (gap identified)
- **Metadata support**: JSON array for storing context (e.g., referral_id, match_id)

---

## 2. UserPointTransaction Model
**Location**: `app/Models/UserPointTransaction.php`

### Fields
| Field | Type | Notes |
|-------|------|-------|
| `id` | bigint | Primary key |
| `user_id` | bigint | FK to users table |
| `points` | integer | Signed: positive=earn, negative=use |
| `type` | string | Categorical: 'earn', 'use', 'refund', 'admin', 'referral' |
| `description` | string | Human-readable reason |
| `metadata` | JSON | Context data (e.g., `{"referral_id": 123}`) |
| `timestamps` | - | `created_at`, `updated_at` |

### Type Labels (Localized)
- `'earn'` → "Kiếm điểm" (Earn points)
- `'use'` → "Sử dụng" (Use points)
- `'refund'` → "Hoàn lại" (Refund)
- `'admin'` → "Cấp bởi admin" (Admin grant)
- `'referral'` → "Thưởng referral" (Referral reward)

### Methods
- `isPositive()`: Returns `points > 0`
- `getFormattedPoints()`: Returns signed string (e.g., "+500", "-100")
- `getTypeLabel()`: Maps type to Vietnamese labels

---

## 3. Event System Architecture
**Location**: `app/Providers/EventServiceProvider.php`

### Current Event Coverage
**Custom Events Found** (4 total):
- `OcrMatchCreated` - Fired when OCR match created
- `OcrMatchAccepted` - When opponent accepts challenge
- `OcrMatchResultSubmitted` - When result posted (before confirmation)
- `OcrMatchConfirmed` - When result confirmed (Elo updates)

### Event Pattern
```php
// Example: OcrMatchCreated.php
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OcrMatchCreated {
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public OcrMatch $match) {}
}
```

### Model Observers
| Observer | Observed Model | Hooks |
|----------|---|---|
| `UserObserver` | User | `creating()` - generates unique referral codes |
| `MatchObserver` | MatchModel | (tournament matches) |
| `StadiumObserver` | Stadium | - |
| `TournamentObserver` | Tournament | - |
| `InstructorObserver` | Instructor | - |
| `ClubObserver` | Club | - |

### Listener Mapping
- `Registered::class` → `SendEmailVerificationNotification::class` (only built-in)

### Gaps Identified
1. **No event listeners** for OCR events detected in EventServiceProvider
2. **No User model events** (created, updated) for point triggers
3. **No referral completion events** for immediate point award
4. **No booking/tournament events** registered for point earning

---

## 4. Referral System Implementation
**Status**: Partially implemented

### Structure
**Location**: `app/Models/Referral.php`
- `referrer_id` (FK) → referrer User
- `referred_user_id` (FK) → referred User
- `status`: 'pending' | 'completed'
- `referred_at`, `completed_at` timestamps

### User Integration
**Location**: `app/Models/User.php`
- Field: `referral_code` - 8-char uppercase string (generated on user creation)

### Related Controllers
- `ReferralController` (Front)
- API endpoints in `routes/api.php`

### Migrations
1. `2025_12_23_create_referrals_table.php` - Main referral tracking
2. `2025_12_23_add_referral_code_to_users_table.php` - Referral code field
3. `2025_12_23_populate_referral_codes_for_existing_users.php` - Backfill
4. `2025_12_23_add_referrer_name_to_referrals_table.php` - Name snapshot

### Flow
1. User A generates unique `referral_code` on registration (UserObserver)
2. User B signs up with code → Referral(referrer_id=A, referred_user_id=B, status='pending')
3. Referral completion trigger unknown → status='completed' (gap)

---

## 5. Community Activity System
**Location**: `app/Models/CommunityActivity.php`

### Mentioned Point Types
From README (OPRS Community Activities):
- Check-in: 10 pts
- Event Participation: 50 pts
- Referral: 100 pts ← **Hook point**
- Weekly Matches: 30 pts
- Monthly Challenge: 150 pts

### Service Layer
- `app/Services/CommunityService.php` - Likely handles activity recording

---

## 6. Infrastructure Readiness Matrix

| Component | Status | Confidence | Notes |
|-----------|--------|------------|-------|
| **Point wallet** | Ready | 100% | Full addPoints/deductPoints with validation |
| **Transaction audit** | Ready | 100% | Comprehensive logging with metadata |
| **Event system** | Partial | 70% | Infrastructure exists, OCR events present but no listeners registered |
| **Referral tracking** | Ready | 90% | Tables/models exist, completion flow unknown |
| **Event listeners** | Gap | 40% | Need to register listeners for point awards |
| **Point triggers** | Gap | 30% | No events for booking, match, activity completion |

---

## 7. Unresolved Questions
1. **Referral completion trigger**: What action triggers `Referral.status='completed'`? (e.g., first booking? signup confirmation?)
2. **Point scaling**: Are point values configurable or hardcoded?
3. **Real-time tracking**: Do existing booking/tournament systems fire model events?
4. **CommunityService implementation**: How does it currently award points? Via wallet or transaction only?
5. **Event listener dispatch**: Are OCR match events actually being dispatched/listened to?
6. **Admin adjustments**: Is there audit trail for manual point adjustments via admin panel?

---

## Recommendations
1. **Create PointEventListener interface** to standardize point-earning event handling
2. **Hook existing model events** via UserObserver patterns before creating new events
3. **Register OCR event listeners** in EventServiceProvider if not auto-discovered
4. **Define referral completion criteria** explicitly (currently ambiguous)
5. **Create PointAwardedEvent** wrapper for all point-earning scenarios
