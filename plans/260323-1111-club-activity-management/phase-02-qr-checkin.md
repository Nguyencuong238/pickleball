# Phase 2: QR Check-in Flow

**Priority**: P1 | **Effort**: Medium | **Status**: Complete
**Depends on**: Phase 1 (DB & Models)

## Context

- UX Research: Feature 1 (QR Check-in)
- Existing: `EventCheckin` model has QR flow for Point system - reference pattern
- Existing: `ClubActivityService` has RSVP + waitlist logic with `lockForUpdate`
- Mobile-first, phone-based lookup, progressive disclosure

## Requirements

- QR code generation per activity (admin action)
- Public check-in page (no login required)
- Phone lookup -> existing user confirm OR quick registration
- Auto-add to club if not member
- Set checked_in_at + current_status = 'queued'
- Redirect to queue page

## Related Code Files

### Modify
- `app/Http/Controllers/ClubActivityController.php` - add `generateQr()` method
- `app/Services/ClubActivityService.php` - add `checkinByPhone()` method
- `routes/web.php` - add check-in routes

### Create
- `app/Http/Controllers/ClubCheckinController.php`
- `resources/views/front/clubs/checkin.blade.php`
- `public/assets/css/club-activity-checkin.css`
- `public/assets/js/club-activity-checkin.js`

## Architecture

```
QR URL: /clubs/{club:slug}/activities/{activity}/checkin?token={qr_code}

Routes (public, no auth):
  GET  /clubs/{club:slug}/activities/{activity}/checkin  -> ClubCheckinController@show
  POST /clubs/{club:slug}/activities/{activity}/lookup   -> ClubCheckinController@lookup
  POST /clubs/{club:slug}/activities/{activity}/confirm  -> ClubCheckinController@confirm
  POST /clubs/{club:slug}/activities/{activity}/register -> ClubCheckinController@register
```

## Implementation Steps

### Step 1: QR Generation (in ClubActivityController)

Add method to existing controller:
```php
public function generateQr(Club $club, ClubActivity $activity)
{
    // Only admin/moderator can generate
    // Generate UUID qr_code if not exists
    // Return QR code image (use simplesoftwareio/simple-qrcode or generate URL for client-side QR)
    // QR encodes: URL to check-in page with token
}
```

### Step 2: ClubCheckinController (NEW)

```php
class ClubCheckinController extends Controller
{
    public function show(Club $club, ClubActivity $activity, Request $request)
    {
        // Validate: token matches activity.qr_code
        // Validate: activity is active and type = open_play
        // Return checkin blade view with activity data
    }

    public function lookup(Club $club, ClubActivity $activity, Request $request)
    {
        // Validate phone (required, regex for VN phone)
        // Normalize phone: strip +84 -> 0, strip spaces
        // Query User by phone
        // Return JSON: { found: bool, user: {id, name, avatar, oprs_level} | null }
    }

    public function confirm(Club $club, ClubActivity $activity, Request $request)
    {
        // Validate: user_id required
        // Call ClubActivityService->checkinByPhone(activity, user)
        // Return JSON: { success: true, redirect: queue_url }
    }

    public function register(Club $club, ClubActivity $activity, Request $request)
    {
        // Validate: name required, phone required
        // Create User (name, phone, random password)
        // Call ClubActivityService->checkinByPhone(activity, user)
        // Return JSON: { success: true, redirect: queue_url }
    }
}
```

### Step 3: ClubActivityService - checkinByPhone()

```php
public function checkinByPhone(ClubActivity $activity, User $user): ClubActivityParticipant
{
    return DB::transaction(function () use ($activity, $user) {
        // 1. Check if user is club member, if not -> add as member
        // 2. Check if already participant, if yes -> update checked_in_at
        // 3. lockForUpdate on activity participants (prevent race condition)
        // 4. Get next queue_position = max(queue_position) + 1
        // 5. Create/update participant: checked_in_at=now, current_status='queued', queue_position
        // 6. Return participant
    });
}
```

### Step 4: Blade View (checkin.blade.php)

```
Layout: minimal (no header/footer nav - standalone mobile page)
x-data="caCheckin({config})"

Step 1: Activity info card + phone input + CTA
Step 2: User greeting card + confirm CTA
Step 3: Registration form + CTA

Uses x-show + x-transition for step transitions
Fixed bottom CTA via .ca-cta-fixed
```

### Step 5: Alpine.js (club-activity-checkin.js)

```javascript
function caCheckin(config) {
    return {
        step: 1, phone: '', user: null, loading: false, error: '',
        form: { name: '', gender: null },

        formatPhone() { /* auto-format 0912 345 678 */ },
        async lookup() { /* POST /lookup, set step 2 or 3 */ },
        async confirm() { /* POST /confirm, redirect to queue */ },
        async register() { /* POST /register, redirect to queue */ }
    };
}
```

### Step 6: CSS (club-activity-checkin.css)

Per UX research:
- `.ca-checkin-page` - min-height 100vh, flex column
- `.ca-phone-input` - 56px height, 24px font, centered, letter-spacing
- `.ca-cta-fixed` - fixed bottom with safe-area-inset
- `.ca-step-enter` - slideUp animation
- `.ca-user-card` - green border, scaleIn animation
- `.ca-error-msg` - red, aria-live polite

### Step 7: Routes

Add to `routes/web.php`:
```php
// Club Activity Check-in (public, no auth)
Route::prefix('clubs/{club:slug}/activities/{activity}')->group(function () {
    Route::get('/checkin', [ClubCheckinController::class, 'show'])->name('club.activity.checkin');
    Route::post('/lookup', [ClubCheckinController::class, 'lookup'])->name('club.activity.lookup');
    Route::post('/confirm', [ClubCheckinController::class, 'confirm'])->name('club.activity.confirm');
    Route::post('/register', [ClubCheckinController::class, 'register'])->name('club.activity.register');
});
```

## Todo

- [x] Add generateQr() to ClubActivityController
- [x] Create ClubCheckinController with show/lookup/confirm/register
- [x] Add checkinByPhone() to ClubActivityService
- [x] Create checkin.blade.php (mobile standalone layout)
- [x] Create club-activity-checkin.js (Alpine component)
- [x] Create club-activity-checkin.css
- [x] Add routes to web.php
- [x] Phone normalization utility (strip +84, spaces)
- [x] Compile check + manual test

## Success Criteria

- QR code generated for activity with unique UUID
- Scan QR -> opens mobile check-in page
- Phone lookup finds existing user -> 1-tap confirm -> joins queue
- Phone not found -> quick register form -> creates user + joins queue
- Race condition safe (lockForUpdate on queue_position)
- No auth required for check-in page

## Security

- Token validation: QR token must match activity.qr_code
- Rate limit lookup endpoint (10/min per IP)
- Phone validation: VN format only
- CSRF on all POST endpoints
- No sensitive data exposed in lookup response (no email/phone returned)
