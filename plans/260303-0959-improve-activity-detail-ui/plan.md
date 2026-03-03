# Improve Club Activity Detail UI (Reclub-inspired)

**Created**: 2026-03-03
**Status**: Draft
**Priority**: Medium
**Effort**: ~4-6 hours

## Overview

Redesign club activity detail page (`show.blade.php`) from basic single-scroll layout to modern tab-based UI inspired by Reclub. Pure Blade + CSS changes, no new packages or DB tables.

## Current State

- Single scroll page with stacked sections
- Desktop-first layout
- No tab navigation
- Plain white card design
- Participant list buried at bottom
- No map/calendar integration

## Target State

- Mobile-first tab-based layout
- Colored gradient header with event info
- Tab navigation: Chi tiet | Nguoi tham gia | Tran dau (competition only)
- Prominent participant avatar row
- Google Maps link for location
- Calendar export (iCal/Google Calendar)
- Share button (Web Share API)

## Phases

| # | Phase | Status | Effort |
|---|-------|--------|--------|
| 1 | [Header & Tab Navigation](phase-01-header-tabs.md) | Pending | 1.5h |
| 2 | [Detail Tab Content](phase-02-detail-tab.md) | Pending | 1.5h |
| 3 | [Participants Tab](phase-03-participants-tab.md) | Pending | 1h |
| 4 | [Competition Tab & Polish](phase-04-competition-polish.md) | Pending | 1h |

## Key Dependencies

- No new packages needed
- No DB migrations
- No new routes (calendar export uses inline URL generation)
- Controller: minor data additions in `show()` method

## Files Modified

- `resources/views/clubs/activities/show.blade.php` - Main layout restructure
- `resources/views/clubs/activities/partials/_show-styles.blade.php` - Complete CSS rewrite
- `resources/views/clubs/activities/partials/_rsvp-panel.blade.php` - Refactor into tab content
- `resources/views/clubs/activities/partials/_participant-list.blade.php` - Avatar row + full list
- `resources/views/clubs/activities/partials/_competition-panel.blade.php` - Tab wrapper
- `app/Http/Controllers/ClubActivityController.php` - Add creator eager load

## Files Created

- `resources/views/clubs/activities/partials/_header-banner.blade.php` - Gradient header
- `resources/views/clubs/activities/partials/_tab-navigation.blade.php` - Tab system
- `resources/views/clubs/activities/partials/_detail-tab.blade.php` - Detail tab content
- `resources/views/clubs/activities/partials/_participants-tab.blade.php` - Participants tab
- `resources/views/clubs/activities/partials/_tab-scripts.blade.php` - Tab switching JS

## Architecture Decision

**Tab system**: Pure CSS + minimal vanilla JS. No framework needed. Each tab content is a `<div>` with `display:none/block` toggling. All content server-rendered (no AJAX tab loading) to keep it simple and SEO-friendly.

## Out of Scope

- Chat tab (requires WebSocket infra - YAGNI)
- User notes per activity (new DB table - YAGNI)
- Embedded map (Google Maps link sufficient)
- Distance calculation (requires Geolocation API + lat/lng fields)
