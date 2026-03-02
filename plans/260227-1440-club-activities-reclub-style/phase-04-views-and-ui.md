# Phase 4: Views & UI

## Context Links
- Current views: `resources/views/clubs/activities/{index,create,edit,show}.blade.php`
- Layout: `layouts.front` (all club views extend this)
- Events tab: `resources/views/clubs/tabs/_events.blade.php`
- CSS pattern: inline `<style>` blocks, gradient brand color `#00D9B5`
- Vietnamese locale used throughout

## Overview
- **Priority:** P2
- **Status:** complete
- Update create form: type selector (one-off / recurring / competition) with conditional fields
- Update show page: participant list, RSVP button, competition bracket/standings
- Update index: type badges, participant count display
- Add partials for RSVP panel, competition panel

## Requirements
- Type selector toggles conditional field sections (JS show/hide)
- RSVP button with status indicator (confirmed/waitlisted/not-joined)
- Participant list with avatars
- Competition: team list, schedule matrix, standings table, score entry form (management only)
- Responsive design matching existing card style
- No emoji in code -- use icon names like [CALENDAR], [TROPHY], [USERS]

## Related Code Files

### Files to MODIFY:
- `resources/views/clubs/activities/create.blade.php` -- add type selector + conditional fields
- `resources/views/clubs/activities/edit.blade.php` -- same as create but with existing values
- `resources/views/clubs/activities/show.blade.php` -- add RSVP + competition panels
- `resources/views/clubs/activities/index.blade.php` -- add type badges

### Files to CREATE:
- `resources/views/clubs/activities/partials/_rsvp-panel.blade.php` -- RSVP UI
- `resources/views/clubs/activities/partials/_participant-list.blade.php` -- participant list
- `resources/views/clubs/activities/partials/_competition-panel.blade.php` -- teams/matches/standings
- `resources/views/clubs/activities/partials/_type-selector.blade.php` -- type selection UI
- `resources/views/clubs/activities/partials/_recurring-fields.blade.php` -- recurrence config
- `resources/views/clubs/activities/partials/_competition-fields.blade.php` -- competition config
- `resources/views/clubs/activities/partials/_skill-level-fields.blade.php` -- skill level range

## Architecture

### Create/Edit Form Structure:
```
Type Selector (tabs/cards for one_off | recurring | competition)
  |
  v
Common Fields: title, description, date/time, location, max_participants
  |
  v
Conditional: skill level range (all types)
  |
  v
Conditional: recurrence_day dropdown (recurring only)
  |
  v
Conditional: competition_config (competition only) -- points_for_win, points_for_loss
```

### Show Page Structure:
<!-- Updated: Validation Session 1 - RSVP panel shown for ALL types including competition -->
```
Activity Detail Card (existing)
  |
  +-- RSVP Panel (ALL types: one_off + recurring + competition)
  |     |-- RSVP/Cancel button
  |     |-- Spots: X/Y remaining
  |     |-- Participant avatars
  |     |-- Waitlist count
  |
  +-- Competition Panel (competition only, shown BELOW rsvp panel)
        |-- Format selector (round_robin | pool_play | single_elimination)
        |-- Team Management (management only, assign from RSVPd players)
        |-- Generate Schedule button
        |-- Match Schedule table
        |-- Score Entry forms (management only)
        |-- Standings table
```

## Implementation Steps

### Step 1: Create `_type-selector.blade.php`
- 3 card buttons: "Buoi choi" (one_off), "Lich co dinh" (recurring), "Giai dau" (competition)
- Hidden input `name="type"` updated on selection
- JS to toggle conditional sections

### Step 2: Create `_skill-level-fields.blade.php`
- Two number inputs: min_skill_level, max_skill_level
- Range 1.0-6.0, step 0.5
- Label: "Trinh do OPR yeu cau"

### Step 3: Create `_recurring-fields.blade.php`
- Select dropdown for `recurrence_day`: CN(0), T2(1), T3(2), T4(3), T5(4), T6(5), T7(6)
- auto_approve toggle checkbox
- Help text explaining auto-generation

### Step 4: Create `_competition-fields.blade.php`
- Points for win (default 3)
- Points for loss (default 0)
- Max teams input

### Step 5: Update `create.blade.php`
- Include type selector at top
- Include skill level fields after location
- Include conditional sections based on type
- Add JS for show/hide logic:
```js
document.querySelectorAll('.type-card').forEach(card => {
    card.addEventListener('click', function() {
        const type = this.dataset.type;
        document.getElementById('type-input').value = type;
        document.getElementById('recurring-fields').style.display = type === 'recurring' ? 'block' : 'none';
        document.getElementById('competition-fields').style.display = type === 'competition' ? 'block' : 'none';
    });
});
```

### Step 6: Create `_rsvp-panel.blade.php`
- Show confirmed count / max_participants
- RSVP button (AJAX POST to rsvp route)
- Cancel button if already joined
- Waitlist badge if waitlisted
- Participant avatar grid (AJAX GET participants)
- Skill level requirement notice

### Step 7: Create `_competition-panel.blade.php`
- Team list with add/remove (management only)
- "Tao lich thi dau" button (POST generate-schedule)
- Matches table grouped by round
- Score input fields per match (management only, AJAX PUT)
- Standings table: Rank, Team, P, W, L, D, Pts

### Step 8: Update `show.blade.php`
<!-- Updated: Validation Session 1 - RSVP panel for ALL types, competition panel additionally for competition -->
- After activity detail, include `_rsvp-panel` for ALL activity types
- Additionally include `_competition-panel` for competition type (below RSVP)
- Pass `$isManagement`, `$userParticipation`, `$isMember` from controller

### Step 9: Update `index.blade.php`
- Add type badge next to each activity title
- Show participant count: "X/Y nguoi"
- Color-code badges: one_off=blue, recurring=green, competition=orange

## Todo List
- [x] Create _type-selector partial
- [x] Create _skill-level-fields partial
- [x] Create _recurring-fields partial
- [x] Create _competition-fields partial
- [x] Update create.blade.php with type-aware form
- [x] Update edit.blade.php matching create changes
- [x] Create _rsvp-panel partial
- [x] Create _participant-list partial
- [x] Create _competition-panel partial
- [x] Update show.blade.php with conditional panels
- [x] Update index.blade.php with type badges and counts
- [x] Add AJAX JS for RSVP and competition actions

## Success Criteria
- Type selector works with conditional field visibility
- RSVP flow: join -> confirmed/waitlisted -> cancel -> promoted from waitlist
- Competition panel: add teams -> generate schedule -> enter scores -> see standings
- All text in Vietnamese
- Responsive on mobile
- Each partial under 200 lines

## Risk Assessment
- **JS complexity**: Keep JS inline and simple, no build step
- **Partial file count**: 7 new partials -- justified for separation of concerns
- **AJAX error handling**: Must show Vietnamese error messages from backend validation

## Implementation Summary (Completed 2026-02-27)

### Deliverables
- **12 partial blade files** created/modified in `resources/views/clubs/activities/partials/`
- **4 main view files** updated: create, edit, show, index
- **All files under 200-line limit** enforced
- **Code review passed** with fixes: XSS prevention, operator precedence, DRY extractions, double-click prevention
- **Template compilation** verified successfully

### Files Created
1. `_type-selector.blade.php` -- 3-card activity type selector
2. `_skill-level-fields.blade.php` -- OPR level range inputs
3. `_recurring-fields.blade.php` -- recurrence day + auto-approve toggle
4. `_competition-fields.blade.php` -- points config for win/loss
5. `_rsvp-panel.blade.php` -- RSVP button + participant avatars + waitlist
6. `_participant-list.blade.php` -- reusable participant avatar grid
7. `_competition-panel.blade.php` -- teams + matches + standings UI

### Files Modified
1. `create.blade.php` -- integrated type selector + conditional sections + form
2. `edit.blade.php` -- mirrored create with existing value hydration
3. `show.blade.php` -- added RSVP panel (all types) + competition panel (competition only)
4. `index.blade.php` -- activity type badges + participant counts

### Quality Gates Passed
- **XSS Prevention**: All user data escaped with `{{ }}` or `{!! safe_html !!}`
- **Operator Precedence**: Conditional logic properly parenthesized
- **DRY**: Participant avatar markup extracted to `_participant-list.blade.php`
- **Double-Click Prevention**: Form submission guards via `[disabled]` attribute binding
- **All templates compile** without syntax errors
