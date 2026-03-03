# Phase 3: Participants Tab

**Priority**: Medium
**Status**: Pending
**Effort**: ~1h

## Context

- [Plan overview](plan.md)
- Current participant list: `resources/views/clubs/activities/partials/_participant-list.blade.php`

## Overview

Create dedicated "Nguoi tham gia" tab showing full participant list with confirmed + waitlisted sections. Improve layout from current chip-style to card-style with larger avatars and OPR level display.

## Requirements

### Functional
- Full participant list with larger avatars (48px)
- Show user name + OPR level for each participant
- Confirmed section with count header
- Waitlisted section with position numbers
- RSVP button at bottom of this tab too (for convenience)
- Empty state message when no participants

### Non-functional
- Scrollable list for many participants
- Consistent with Reclub's participant view

## Related Code Files

### Modify
- `resources/views/clubs/activities/partials/_participant-list.blade.php` - Redesign layout

### Create
- `resources/views/clubs/activities/partials/_participants-tab.blade.php` - Tab wrapper

## Implementation Steps

### 1. Create `_participants-tab.blade.php`

```blade
<div class="participants-tab">
    <div class="participants-header">
        <h3>Nguoi tham gia ({{ $activity->confirmed_participants_count ?? 0 }}
            @if($activity->max_participants) / {{ $activity->max_participants }} @endif)
        </h3>
    </div>

    @include('clubs.activities.partials._participant-list', [
        'confirmed' => $activity->confirmedParticipants,
        'waitlisted' => $activity->waitlistedParticipants,
    ])

    {{-- RSVP button --}}
    @include('clubs.activities.partials._rsvp-button')
</div>
```

### 2. Redesign `_participant-list.blade.php`

From chip-style to card rows:
- Larger avatar (48px)
- User name + OPR level on right
- Confirmed: green check indicator
- Waitlisted: position number badge
- Divider between items

### 3. Add empty state

When no participants:
```blade
<div class="empty-state">
    [USERS] icon
    <p>Chua co nguoi tham gia</p>
    <p>Hay la nguoi dau tien dang ky!</p>
</div>
```

## Todo

- [ ] Create `_participants-tab.blade.php`
- [ ] Redesign `_participant-list.blade.php` with card-row layout
- [ ] Add OPR level display per participant
- [ ] Add empty state
- [ ] Include RSVP button in this tab
- [ ] Test with 0, 1, 5, 20+ participants

## Success Criteria

- Full participant list with larger avatars
- OPR level shown per participant
- Confirmed vs waitlisted clearly separated
- Empty state shows when no participants
- RSVP button accessible from this tab
