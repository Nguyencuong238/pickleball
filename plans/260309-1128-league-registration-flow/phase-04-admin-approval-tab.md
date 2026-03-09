# Phase 4: Frontend - Admin Approval Tab

## Context Links
- [Phase 2: Backend Logic](./phase-02-backend-logic.md)
- [League show view](../../resources/views/home-yard/leagues/show.blade.php)
- [Existing tab partials](../../resources/views/home-yard/leagues/_tab-teams.blade.php)

## Overview
- **Priority**: P1
- **Status**: pending
- **Effort**: 1.5h

## Key Insights
- League show page has tab system: overview, teams, schedule, standings
- Add "Dang ky" tab between overview and teams tabs
- Tab loads registrations via AJAX (JSON endpoint from Phase 2)
- Show registration count badge on tab button
- Admin = league owner (already checked via `$league->user_id === auth()->id()`)

## Requirements

### Functional
- New tab "Dang ky (N)" showing pending count
- List registration groups with: status badge, player names, submission date
- Expandable group: show player details + payment proof image
- Filter by status: All / Pending / Approved / Rejected
- Approve button: confirms, optional admin note, calls PUT approve endpoint
- Reject button: confirms, requires admin note, calls PUT reject endpoint
- Show payment proof as clickable thumbnail (opens full-size in modal/new tab)

### Non-functional
- AJAX-loaded to avoid slowing show page initial load
- Consistent styling with existing tabs

## Architecture

### View File
`resources/views/home-yard/leagues/_tab-registrations.blade.php`

### Data Flow
```
Tab click --> fetch /homeyard/leagues/{slug}/registrations?status=pending
  --> render registration cards client-side
  --> Approve/Reject --> PUT endpoint --> refresh list
```

## Related Code Files

### Files to CREATE
- `resources/views/home-yard/leagues/_tab-registrations.blade.php`

### Files to MODIFY
- `resources/views/home-yard/leagues/show.blade.php` -- add tab button + tab content div
- `app/Http/Controllers/Front/HomeYardLeagueController.php` -- load registration count for show view

## Implementation Steps

### 1. Update `show.blade.php` - Add Tab

In the tab buttons section (line ~63-66), add after overview tab:
```html
<button class="league-tab" onclick="switchTab('registrations', this)">
    <i class="fas fa-clipboard-list"></i> Dang ky
    @if($pendingRegistrationCount > 0)
        <span style="background:#ef4444;color:white;border-radius:50%;padding:2px 6px;font-size:0.7rem;margin-left:4px;">
            {{ $pendingRegistrationCount }}
        </span>
    @endif
</button>
```

Add tab content div:
```html
<div id="tab-registrations" class="league-tab-content">
    @include('home-yard.leagues._tab-registrations')
</div>
```

Update JS `tabNames` array to include 'registrations'.

### 2. Update `HomeYardLeagueController::show()`

Add pending registration count:
```php
$pendingRegistrationCount = $league->registrations()
    ->where('status', 'pending')->count();

return view('home-yard.leagues.show', compact('league', 'pendingRegistrationCount'));
```

### 3. Create `_tab-registrations.blade.php`

Structure:
```
<!-- Status Filter Bar -->
<div> [All] [Pending] [Approved] [Rejected] </div>

<!-- Registration List (AJAX loaded) -->
<div id="registration-list">
  <!-- Populated by JS -->
</div>

<!-- Approve/Reject Modal -->
<div id="registrationActionModal">
  <textarea name="admin_note" placeholder="Ghi chu..."></textarea>
  <button>Xac nhan</button>
</div>

<script>
  // fetchRegistrations(status) - load from JSON endpoint
  // renderRegistrationCard(reg) - card with expand/collapse
  // approveRegistration(id) - PUT with CSRF
  // rejectRegistration(id) - PUT with CSRF + required note
</script>
```

### 4. Registration Card UI

Each card shows:
```
[Status Badge] | Nhom #ID | Ngay DK: dd/mm/yyyy | N VDV
[Expand arrow]

Expanded:
  ┌─────────────────────────────────┐
  │ Payment Proof [thumbnail]       │
  ├─────────────────────────────────┤
  │ VDV 1: Ten - SDT - Gioi tinh   │
  │ VDV 2: Ten - SDT - Gioi tinh   │
  ├─────────────────────────────────┤
  │ [Duyet] [Tu choi]  (if pending) │
  └─────────────────────────────────┘
```

### 5. JSON Response Format (from Phase 2 controller)

```json
{
  "data": [
    {
      "id": 1,
      "status": "pending",
      "payment_proof": "/storage/league-registrations/abc.jpg",
      "admin_note": null,
      "created_at": "2026-03-09T10:00:00",
      "players": [
        { "id": 1, "name": "Nguyen Van A", "phone": "0901234567", "gender": "male", "skill_level": "4.0", "province": "HCM" },
        { "id": 2, "name": "Tran Thi B", "phone": "0907654321", "gender": "female", "skill_level": "3.5", "province": "HN" }
      ]
    }
  ]
}
```

## Todo List
- [ ] Create _tab-registrations.blade.php
- [ ] Update show.blade.php (tab button + content div + JS tabNames)
- [ ] Update HomeYardLeagueController::show() for pendingRegistrationCount
- [ ] Implement fetchRegistrations JS function
- [ ] Implement registration card rendering
- [ ] Implement approve/reject with modal + admin note
- [ ] Status filter buttons
- [ ] Payment proof thumbnail with full-size view
- [ ] Test approve/reject flow end-to-end

## Success Criteria
- Tab appears in league show page with pending count badge
- Registrations load via AJAX on tab click
- Filter by status works
- Approve sets status=approved, refreshes list
- Reject requires note, sets status=rejected
- Payment proof viewable as thumbnail

## Risk Assessment
- **Many registrations**: paginate if >50 (YAGNI for now, add later if needed)
- **Tab not auto-loading**: use lazy load on first tab click

## Security
- All admin actions check `$league->user_id === auth()->id()` (Phase 2)
- CSRF token on all AJAX calls
- Payment proof served via `/storage/` public disk
