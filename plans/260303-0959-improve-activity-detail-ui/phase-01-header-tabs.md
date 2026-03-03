# Phase 1: Header Banner & Tab Navigation

**Priority**: High
**Status**: Pending
**Effort**: ~1.5h

## Context

- [Plan overview](plan.md)
- Current show page: `resources/views/clubs/activities/show.blade.php`
- Current styles: `resources/views/clubs/activities/partials/_show-styles.blade.php`

## Overview

Replace plain white card header with colored gradient banner (Reclub-style). Add horizontal tab navigation below header. Restructure `show.blade.php` to use tab content areas.

## Key Insights

- Reclub uses green gradient header with white text, back/share/more buttons
- Tabs: Chi tiet | Nguoi tham gia | Tran dau | Tro chuyen (we skip chat)
- Header shows: time, date, event title
- Tab content areas toggle via JS (no page reload)

## Requirements

### Functional
- Gradient header banner with activity title, date/time, type badge
- Back button (arrow) top-left, share + kebab menu top-right
- Horizontal tab bar: "Chi tiet" | "Nguoi tham gia" | "Tran dau" (competition only)
- Active tab indicator (underline)
- Tab switching without page reload
- Share button: Web Share API with clipboard fallback

### Non-functional
- Mobile-first responsive
- Smooth tab transitions
- No layout shift on tab switch

## Architecture

```
show.blade.php
├── @include('_header-banner')     ← NEW: gradient header
├── @include('_tab-navigation')    ← NEW: tab bar
├── <div id="tab-detail">
│   └── @include('_detail-tab')    ← NEW: Phase 2
├── <div id="tab-participants">
│   └── @include('_participants-tab')  ← NEW: Phase 3
├── <div id="tab-competition">     ← competition only
│   └── @include('_competition-panel')  ← existing
└── @include('_tab-scripts')       ← NEW: tab JS
```

## Related Code Files

### Modify
- `resources/views/clubs/activities/show.blade.php` - Restructure to tab layout
- `resources/views/clubs/activities/partials/_show-styles.blade.php` - New header + tab CSS

### Create
- `resources/views/clubs/activities/partials/_header-banner.blade.php`
- `resources/views/clubs/activities/partials/_tab-navigation.blade.php`
- `resources/views/clubs/activities/partials/_tab-scripts.blade.php`

## Implementation Steps

### 1. Create `_header-banner.blade.php`

```blade
{{-- Gradient header banner --}}
<div class="activity-header-banner">
    <div class="header-top-bar">
        <a href="{{ route('clubs.activities.index', $club) }}" class="header-back-btn">
            [ARROW_LEFT]
        </a>
        <div class="header-datetime">
            {{ $activity->activity_date->format('H:i') }}
            {{ $activity->activity_date->isoFormat('dddd, D/M') }}
        </div>
        <div class="header-actions">
            <button class="header-action-btn" onclick="shareActivity()">
                [SHARE]
            </button>
            @if($isManagement)
            <div class="header-menu-wrapper">
                <button class="header-action-btn" onclick="toggleMenu()">
                    [MORE_VERT]
                </button>
                <div class="header-dropdown" id="header-dropdown">
                    <a href="{{ route('clubs.activities.edit', [$club, $activity]) }}">Chinh sua</a>
                    <form method="POST" action="{{ route('clubs.activities.destroy', [$club, $activity]) }}">
                        @csrf @method('DELETE')
                        <button onclick="return confirm('Xoa hoat dong?')">Xoa</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
    <h1 class="header-title">{{ strtoupper($activity->title) }}</h1>
</div>
```

### 2. Create `_tab-navigation.blade.php`

```blade
<div class="tab-navigation">
    <button class="tab-btn active" data-tab="detail">Chi tiet</button>
    <button class="tab-btn" data-tab="participants">Nguoi tham gia</button>
    @if($activity->type === 'competition')
        <button class="tab-btn" data-tab="competition">Tran dau</button>
    @endif
</div>
```

### 3. Create `_tab-scripts.blade.php`

Tab switching logic + share function + dropdown menu toggle.

### 4. Update `show.blade.php`

Replace current layout with:
- Remove old header, badge-row, btn-back
- Include new header banner
- Include tab navigation
- Wrap content in tab panels

### 5. Update `_show-styles.blade.php`

- Header banner: gradient bg (green like Reclub), white text
- Tab bar: horizontal scroll, underline active indicator
- Tab panels: show/hide based on active tab
- Mobile-first breakpoints

## CSS Key Styles

```css
.activity-header-banner {
    background: linear-gradient(135deg, #00D9B5, #00C4A3);
    padding: 20px;
    color: white;
    border-radius: 16px 16px 0 0;
}
.tab-navigation {
    display: flex;
    border-bottom: 2px solid #e5e7eb;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.tab-btn.active {
    color: #2563eb;
    border-bottom: 3px solid #2563eb;
}
.tab-content { display: none; }
.tab-content.active { display: block; }
```

## Todo

- [ ] Create `_header-banner.blade.php` with gradient header
- [ ] Create `_tab-navigation.blade.php` with tab buttons
- [ ] Create `_tab-scripts.blade.php` with tab switching + share + menu JS
- [ ] Restructure `show.blade.php` to use tab layout
- [ ] Rewrite `_show-styles.blade.php` for new header + tab CSS
- [ ] Test mobile responsiveness
- [ ] Test share functionality (HTTPS required for Web Share API)

## Success Criteria

- Header displays gradient banner with title, date/time
- Back button navigates to activity list
- Share button works (Web Share API or clipboard copy)
- Management kebab menu shows edit/delete
- Tab navigation switches between panels smoothly
- No page reload on tab switch
- Mobile responsive (tabs horizontally scrollable)

## Risk Assessment

- **Web Share API**: Only works on HTTPS + supported browsers. Clipboard fallback covers desktop/HTTP.
- **Tab state**: URL hash (#participants) should update so direct linking works. Use `hashchange` event.
