# Phase 2: Detail Tab Content

**Priority**: High
**Status**: Pending
**Effort**: ~1.5h

## Context

- [Plan overview](plan.md)
- [Phase 1: Header & Tabs](phase-01-header-tabs.md)
- Reclub screenshot reference: organizer section, participant avatars, date/calendar, location/map, activity type tag

## Overview

Build the "Chi tiet" tab content inspired by Reclub layout. Replaces current stacked meta items with structured sections: organizer, participant preview, datetime with calendar, location with map link, activity type, description, RSVP button.

## Key Insights from Reclub

- Organizer shown with icon + name + frequency ("Moi Thu ba") + "Xem lich" button
- Participant avatars in horizontal row with +N counter (not full list)
- DateTime: "Thu ba 03/03 vao luc 15:00 / 1 tieng / Them vao lich"
- Location: address + plus code + distance + "Hien thi trong ban do"
- Activity type: tag icon + "Giao huu"
- Dividers between sections
- CTA at bottom: "Tim them nguoi choi"

## Requirements

### Functional
- Organizer section: creator avatar + name + club name + "Xem lich" link
- Participant preview: horizontal avatar row (max 5) + "+N" badge, clickable to switch to Participants tab
- DateTime section: formatted Vietnamese date + duration + "Them vao lich" link (Google Calendar URL)
- Location section: address + "Hien thi trong ban do" link (Google Maps)
- Activity type tag
- Description (if any)
- RSVP action button at bottom (sticky on mobile)
- Management actions in kebab menu (moved from Phase 1)

### Non-functional
- Clean spacing with dividers between sections
- Icons for each section (SVG inline)
- Mobile-first

## Architecture

```
_detail-tab.blade.php
├── Organizer section (creator info)
├── Participant preview row (avatar + +N)
├── DateTime section + calendar link
├── Location section + map link
├── Activity type tag
├── Description
├── RSVP section (join/cancel/status)
└── Sticky CTA button (mobile)
```

## Related Code Files

### Modify
- `resources/views/clubs/activities/partials/_rsvp-panel.blade.php` - Extract RSVP button into detail tab, keep participant list for participants tab
- `app/Http/Controllers/ClubActivityController.php` - Eager load `creator` relationship

### Create
- `resources/views/clubs/activities/partials/_detail-tab.blade.php`
- `resources/views/clubs/activities/partials/_detail-tab-styles.blade.php`

## Implementation Steps

### 1. Update Controller

In `ClubActivityController::show()`, add creator eager load:
```php
$activity->load(['confirmedParticipants.user', 'waitlistedParticipants.user', 'creator']);
```

### 2. Create `_detail-tab.blade.php`

Layout sections with dividers:

```blade
<div class="detail-tab">
    {{-- Organizer --}}
    <div class="detail-section organizer-section">
        <div class="organizer-avatar">...</div>
        <div class="organizer-info">
            <strong>{{ $activity->creator->name ?? $club->name }}</strong>
            <span>{{ type_label }} {{ recurrence_info }}</span>
        </div>
        <a href="{{ route('clubs.activities.index', $club) }}" class="btn-outline-sm">Xem lich</a>
    </div>

    {{-- Participant preview --}}
    <div class="detail-section participant-preview" onclick="switchTab('participants')">
        <div class="avatar-row">
            @foreach($activity->confirmedParticipants->take(5) as $p) ... @endforeach
            @if($activity->confirmed_participants_count > 5)
                <span class="avatar-more">+{{ $activity->confirmed_participants_count - 5 }}</span>
            @endif
        </div>
    </div>

    {{-- DateTime --}}
    <div class="detail-section">
        [CALENDAR_CHECK] icon
        <div>
            {{ Vietnamese formatted date + time }}
            {{ duration if end_time }}
            <a href="{{ google_calendar_url }}" target="_blank">Them vao lich</a>
        </div>
    </div>

    {{-- Location --}}
    @if($activity->location)
    <div class="detail-section">
        [LOCATION] icon
        <div>
            <strong>{{ $activity->location }}</strong>
            <a href="https://maps.google.com/?q={{ urlencode($activity->location) }}" target="_blank">
                Hien thi trong ban do
            </a>
        </div>
    </div>
    @endif

    {{-- Activity type --}}
    <div class="detail-section">
        [TAG] icon
        <span>{{ type_label }}</span>
    </div>

    <hr class="section-divider">

    {{-- Description --}}
    @if($activity->description)
    <div class="detail-section description-section">
        {!! nl2br(e($activity->description)) !!}
    </div>
    @endif

    {{-- RSVP --}}
    @include('clubs.activities.partials._rsvp-button')
</div>
```

### 3. Google Calendar URL Builder

Inline in Blade (no package needed):
```php
@php
$calStart = $activity->activity_date->format('Ymd\THis');
$calEnd = $activity->end_time
    ? $activity->activity_date->copy()->setTimeFromTimeString($activity->end_time)->format('Ymd\THis')
    : $activity->activity_date->copy()->addHour()->format('Ymd\THis');
$calUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
    . '&text=' . urlencode($activity->title)
    . '&dates=' . $calStart . '/' . $calEnd
    . '&location=' . urlencode($activity->location ?? '')
    . '&details=' . urlencode($activity->description ?? '');
@endphp
```

### 4. Refactor `_rsvp-panel.blade.php`

Split into two:
- **`_rsvp-button.blade.php`**: Just the RSVP action (join/cancel button + status) for detail tab
- **`_rsvp-panel.blade.php`**: Keep existing but remove participant list (moved to participants tab)

### 5. Create `_detail-tab-styles.blade.php`

Key styles:
```css
.detail-section {
    display: flex; align-items: flex-start; gap: 16px;
    padding: 16px 20px;
    border-bottom: 1px solid #f3f4f6;
}
.organizer-section { padding: 20px; }
.participant-preview { cursor: pointer; }
.avatar-row { display: flex; gap: -8px; } /* overlapping avatars */
.avatar-row img { margin-left: -8px; border: 2px solid white; }
.avatar-more {
    width: 40px; height: 40px; border-radius: 50%;
    background: #e5e7eb; display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem; font-weight: 600;
}
.sticky-cta {
    position: sticky; bottom: 0;
    padding: 16px 20px; background: white;
    border-top: 1px solid #e5e7eb;
}
```

## Todo

- [ ] Update `ClubActivityController::show()` to eager load `creator`
- [ ] Create `_detail-tab.blade.php` with all sections
- [ ] Create `_detail-tab-styles.blade.php`
- [ ] Build Google Calendar URL inline in Blade
- [ ] Extract RSVP button from `_rsvp-panel.blade.php`
- [ ] Overlapping avatar row for participant preview
- [ ] Google Maps link for location
- [ ] Test Vietnamese date formatting (Carbon locale)
- [ ] Test calendar link generation

## Success Criteria

- Organizer section shows creator info
- Participant avatars displayed as overlapping row with +N
- Clicking avatars switches to Participants tab
- "Them vao lich" opens Google Calendar with pre-filled event
- "Hien thi trong ban do" opens Google Maps with location query
- Activity type tag displayed
- RSVP button functional at bottom
- Clean section dividers between items

## Risk Assessment

- **Carbon Vietnamese locale**: Need `Carbon::setLocale('vi')` or use `isoFormat()`. Already configured if app locale = vi.
- **Calendar timezone**: Google Calendar URL needs timezone param. Use `&ctz=Asia/Ho_Chi_Minh`.
- **Location without lat/lng**: Maps link uses text query (less precise but works). Acceptable for now.
