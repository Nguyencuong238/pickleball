# Club Activities Feature Guide

**Last Updated**: 2026-04-08
**Status**: Complete (All 6 Phases Done + Mar & Apr Updates)
**Phases Complete**: Database, Models, Controllers, Views, Scheduling, Testing
**Enhancement Note**: Auto-create club post when activity is created (2026-03-14+), Gems payment system added (2026-04-08)

**Mar 2026 Updates**:
- Check-in system: Real-time participant tracking with timestamps and status management
- Leaderboard: Per-activity player stats with rankings and filtering
- Match end flow: Player-initiated match end via `playerEndMatch()` endpoint
- Score confirmation: Admin vs player submission with opposing team validation
- Score rejection: Support for resubmission if scores are rejected
- Match settings: `best_of` (1/3/5 sets) and `points_per_set` (default: 21) configuration
- New controllers: `ClubCheckinController`, `ClubLeaderboardController`
- New service: `WaitlistAutoPromotionService` for automatic promotion from waitlist

**Apr 2026 Updates (v1.13.0 - Gems Payment)**:
- Gems Payment System: Virtual currency fees for club activity participation
- Fee field: `fee_gems` (nullable unsigned int, optional) on ClubActivity model
- RSVP deduction: Gems charged when participant confirmed + activity has fee
- Cancel refund: Full gem refund on cancel if confirmed + activity not yet started
- Waitlist auto-skip: `promoteFromWaitlist()` skips users without sufficient gems, auto-cancels them
- Fee lock: Update blocked if >= 1 confirmed participant exists
- Deletion guard: Activity deletion blocked if >= 1 confirmed participant
- Check-in fee: Gems charged for walk-in users via `checkinByPhone()`
- Recurring copy: `fee_gems` copied from template when creating recurring instances
- Transaction tracking: `gem_transaction_id` (FK) links participants to GemTransaction records
- Service methods: rsvp(), cancelRsvp(), promoteFromWaitlist(), checkinByPhone(), createRecurringInstance()
- Private helpers: chargeGems(), refundGems() with atomic transaction handling

---

## Overview

The Club Activities system enables club members to participate in three types of activities:
1. **One-Off Meets** - Single event with RSVP, participant tracking, and casual match generation
2. **Recurring Activities** - Auto-generated recurring instances of a template activity (scheduled 06:00 daily)
3. **Competitions** - Structured tournaments with teams, matches, standings, and score confirmation

All activity types support:
- RSVP/waitlist with auto-promotion from waitlist
- Skill level filtering (OPR-based)
- Check-in tracking with real-time timestamps
- Leaderboard with per-player statistics
- Match generation with 3 algorithms (singles round-robin, rotating doubles, fixed doubles)
- Score confirmation workflow (pending → confirmed/rejected → admin_confirmed)
- Management controls and admin overrides
- Optional gems payment fees (charged at confirmation, refunded at cancellation)

---

## Gems Payment System (Apr 2026)

### Fee Structure
- **Field**: `fee_gems` on ClubActivity (nullable unsigned int)
- **Optional**: Fee only applies if `fee_gems > 0`
- **Methods**: `hasFee()` checks if fee exists; `isFeeEditable()` checks if fee can be modified

### Deduction Flow
| Event | Trigger | Amount | Condition |
|-------|---------|--------|-----------|
| RSVP | Participant confirmed | fee_gems | status = 'confirmed' AND hasFee() |
| Check-in | Walk-in via phone | fee_gems | First-time user + activity has fee |
| Recurring | Template instance | fee_gems | fee_gems copied to new instance |

### Refund Policy
- **Eligible**: Confirmed participants who cancel
- **Condition**: Only if activity has not started (`activity_date > now()`)
- **Method**: Full refund to gem wallet + credit GemTransaction record
- **Return**: `['gems_refunded' => int]` from `cancelRsvp()`

### Waitlist Auto-Skip Logic
When confirmed slot opens, `promoteFromWaitlist()` loops through waitlist:
1. Check if activity has fee
2. If no fee: promote immediately
3. If fee: attempt gem charge via `chargeGems()`
4. Success: promote with gem_transaction_id
5. Insufficient balance: auto-cancel & try next user

### Fee Lock & Deletion Guard
- **Update Lock**: Cannot modify `fee_gems` if >= 1 confirmed participant
- **Delete Guard**: Cannot delete activity if >= 1 confirmed participant (gem refund liability)

### Frontend Integration
- Create/edit forms: `fee_gems` input, disabled when locked
- Index view: Gems badge showing fee amount
- Show view: Fee section with VND conversion (1 gem = exchange_rate VND)
- RSVP button: Shows gem balance, disabled if insufficient, fee in button label
- Check-in page: Shows fee + handles insufficient_gems error

---

## View Architecture

### Directory Structure
```
resources/views/clubs/activities/
├── index.blade.php                 # Activity listing with type badges
├── create.blade.php                # Create form with type selector
├── edit.blade.php                  # Edit form (mirrors create)
├── show.blade.php                  # Activity detail + RSVP + competition
└── partials/
    ├── _type-selector.blade.php           # Type selection UI
    ├── _skill-level-fields.blade.php      # OPR skill range inputs
    ├── _recurring-fields.blade.php        # Recurrence configuration
    ├── _competition-fields.blade.php      # Competition scoring config
    ├── _rsvp-panel.blade.php              # RSVP/waitlist UI
    ├── _participant-list.blade.php        # Participant avatars
    ├── _competition-panel.blade.php       # Teams/schedule/standings
    ├── _form-styles.blade.php             # Create/edit page CSS
    ├── _index-styles.blade.php            # Index page CSS
    ├── _show-styles.blade.php             # Show page CSS
    ├── _competition-styles.blade.php      # Competition panel CSS
    └── _competition-scripts.blade.php     # JS for competition UI
```

---

## Type Selector Pattern

### Form Flow

**Create/Edit Form Structure:**
```
Step 1: Type Selection
  User clicks card: "Buổi chơi" | "Lịch cố định" | "Giải đấu"
  JS updates hidden input: <input name="type" value="one_off|recurring|competition">

Step 2: Common Fields (All Types)
  - title (required)
  - description (optional)
  - activity_date + end_time (required)
  - location (optional)
  - max_participants (optional)
  - status (required) - upcoming|completed|cancelled

Step 3: Skill Level Range (All Types)
  - min_skill_level (1.0-6.0, step 0.5)
  - max_skill_level (1.0-6.0, step 0.5)

Step 4: Conditional Sections (Based on Type)
  IF recurring:
    - recurrence_day (0-6, Sunday-Saturday)
    - auto_approve (boolean) - auto-confirm RSVPs

  IF competition:
    - format (round_robin|pool_play|single_elimination)
    - max_teams (optional)
    - points_for_win (default: 3)
    - points_for_loss (default: 0)
```

### Type Selector Implementation
```javascript
// Create.blade.php inline script (lines 89-120)
document.addEventListener('DOMContentLoaded', function() {
    const typeCards = document.querySelectorAll('.type-card');
    const typeInput = document.getElementById('type-input');
    const recurringFields = document.getElementById('recurring-fields');
    const competitionFields = document.getElementById('competition-fields');

    function updateTypeDisplay(type) {
        typeCards.forEach(card => {
            card.classList.toggle('active', card.dataset.type === type);
        });
        typeInput.value = type;
        recurringFields.style.display = type === 'recurring' ? 'block' : 'none';
        competitionFields.style.display = type === 'competition' ? 'block' : 'none';
    }

    typeCards.forEach(card => {
        card.addEventListener('click', function() {
            updateTypeDisplay(this.dataset.type);
        });
    });
});
```

---

## RSVP System (All Activity Types)

### Flow Diagram
```
Activity Show Page (is_member = true)
  ↓
_rsvp-panel.blade.php included
  ↓
Check user participation status:
  - Not joined → Show RSVP button
  - Confirmed → Show "Đã tham gia" + cancel option
  - Waitlisted → Show "Đang chờ" + position + cancel option
  ↓
User clicks RSVP:
  POST /clubs/{club}/activities/{activity}/rsvp
  ↓
Server response (ClubActivityParticipantController::store):
  IF spots available:
    Create participant record with status='confirmed'
    Return: count + participant list
  ELSE IF waitlist enabled:
    Create participant record with status='waitlisted'
    Calculate waitlist_position
    Return: waitlist status + position
  ELSE:
    Return error: "Activity full"
  ↓
JS updates RSVP panel:
  - Update count display
  - Refresh participant avatars
  - Show/hide waitlist indicator
  - Enable cancel button
```

### RSVP Panel UI Components

**Participants Display** (_rsvp-panel.blade.php):
```blade
<!-- Spot count -->
<span class="rsvp-count">{{ $confirmedCount }} / {{ $activity->max_participants }}</span>

<!-- RSVP button (AJAX POST) -->
<button class="btn-rsvp" data-activity-id="{{ $activity->id }}">
  @if($userParticipation === 'confirmed')
    Hủy tham gia
  @elseif($userParticipation === 'waitlisted')
    Hủy chờ (vị trí: {{ $userPosition }})
  @else
    RSVP Tham gia
  @endif
</button>

<!-- Participant avatars -->
<div class="participant-avatars">
  @foreach($confirmedParticipants as $p)
    <img src="{{ $p->user->avatar_url }}" alt="{{ $p->user->name }}" title="{{ $p->user->name }}">
  @endforeach
</div>

<!-- Waitlist badge -->
@if($waitlistCount > 0)
  <span class="waitlist-badge">[WAITLIST] {{ $waitlistCount }} người chờ</span>
@endif
```

---

## Competition System (Competition Type Only)

### Overview
Competition activities create teams, generate match schedules, and track standings. Shows alongside RSVP panel on show page.

### Flow Diagram
```
Activity Show Page (type = 'competition')
  ↓
Both panels shown:
  1. RSVP Panel → Join activity first
  2. Competition Panel → Team management + scheduling
  ↓
Management Only (isManagement = true):
  ↓
  Step 1: Team Management
    - View list of RSVPd players
    - Assign players to teams (AJAX)
    - Max teams = max_participants / 2 (suggested)
  ↓
  Step 2: Generate Schedule
    - Click "Tao lich thi dau" button
    - Select format: round_robin|pool_play|single_elimination
    - POST /clubs/{club}/activities/{activity}/generate-schedule
  ↓
  Step 3: View Schedule Matrix
    - Matches grouped by round
    - Show: Team1 vs Team2, Schedule time (if set)
    - Score entry form (management only, AJAX)
  ↓
  Step 4: Track Standings
    - Auto-calculate after each score update
    - Display: Rank, Team, P(layed), W(ins), L(osses), Pts
    - Real-time refresh via AJAX
```

### Competition Panel Components

**Team Management:**
```blade
<div class="teams-section">
  <h3>Đội Thi Đấu</h3>
  <div class="teams-list">
    @foreach($competitionTeams as $team)
      <div class="team-card" data-team-id="{{ $team->id }}">
        <h4>{{ $team->team_name }}</h4>
        <ul class="team-players">
          @foreach($team->players as $player)
            <li>{{ $player->player_name }}
              <button class="btn-remove-player" data-player-id="{{ $player->id }}">x</button>
            </li>
          @endforeach
        </ul>
      </div>
    @endforeach
  </div>
  <button class="btn-add-team" data-activity-id="{{ $activity->id }}">
    [+] Thêm Đội
  </button>
</div>
```

**Match Schedule:**
```blade
<div class="matches-section">
  <div class="format-selector">
    <select id="schedule-format" data-activity-id="{{ $activity->id }}">
      <option value="round_robin">Vòng Tròn (Round Robin)</option>
      <option value="pool_play">Bảng + Playoff (Pool Play)</option>
      <option value="single_elimination">Loại Trực Tiếp</option>
    </select>
    <button class="btn-generate-schedule">[GENERATE] Tao lich</button>
  </div>

  <div class="schedule-matrix">
    @foreach($competitionMatches->groupBy('round_number') as $round => $matches)
      <div class="round-group">
        <h4>Vòng {{ $round }}</h4>
        @foreach($matches as $match)
          <div class="match-row">
            <span class="team-name">{{ $match->team1->team_name }}</span>
            <span class="vs">vs</span>
            <span class="team-name">{{ $match->team2->team_name }}</span>

            @if(auth()->user()->can('updateCompetition', $activity))
              <input type="number" class="score-input" placeholder="X" data-match-id="{{ $match->id }}" data-team="1">
              <input type="number" class="score-input" placeholder="X" data-match-id="{{ $match->id }}" data-team="2">
            @else
              <span class="score">{{ $match->team1_score ?? '-' }}</span>
              <span class="score">{{ $match->team2_score ?? '-' }}</span>
            @endif
          </div>
        @endforeach
      </div>
    @endforeach
  </div>
</div>
```

**Standings Table:**
```blade
<div class="standings-section">
  <h3>Bảng Xếp Hạng</h3>
  <table class="standings-table">
    <thead>
      <tr>
        <th>Vị Trí</th>
        <th>Đội</th>
        <th>T</th>
        <th>W</th>
        <th>L</th>
        <th>D</th>
        <th>Điểm</th>
      </tr>
    </thead>
    <tbody>
      @foreach($competitionStandings as $idx => $standing)
        <tr class="rank-{{ $idx + 1 }}">
          <td>{{ $idx + 1 }}</td>
          <td>{{ $standing->team->team_name }}</td>
          <td>{{ $standing->matches_played }}</td>
          <td>{{ $standing->wins }}</td>
          <td>{{ $standing->losses }}</td>
          <td>{{ $standing->draws }}</td>
          <td><strong>{{ $standing->points }}</strong></td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
```

---

## Index Page Features

### Type Badges
```blade
@php
  $typeBadges = [
    'one_off' => ['label' => 'Buổi chơi', 'class' => 'badge-blue'],
    'recurring' => ['label' => 'Lịch cố định', 'class' => 'badge-green'],
    'competition' => ['label' => 'Giải đấu', 'class' => 'badge-orange'],
  ];
@endphp

<span class="type-badge {{ $typeBadges[$activity->type]['class'] }}">
  {{ $typeBadges[$activity->type]['label'] }}
</span>
```

### Participant Count Display
```blade
<div class="participant-info">
  {{ $activity->confirmed_participants_count ?? 0 }} / {{ $activity->max_participants ?? '-' }} người
</div>
```

---

## View Standards for Club Activities

### File Size Constraints
- **Main files** (create, edit, show, index): max 150 LOC
- **Partial files**: max 200 LOC each
- **Styling partials**: max 100 LOC
- **JS inline**: max 50 LOC (defer to `_competition-scripts.blade.php` if larger)

### Naming Conventions
- Partial files: `_kebab-case.blade.php`
- Data attributes for JS: `data-entity-action` (e.g., `data-activity-id`)
- CSS classes: `kebab-case` (e.g., `.btn-rsvp`, `.type-badge`)
- No emoji in code - use placeholder icon names: `[CALENDAR]`, `[USERS]`, `[TROPHY]`

### Styling Pattern
```blade
@include('clubs.activities.partials._form-styles')      <!-- Create/edit page -->
@include('clubs.activities.partials._index-styles')     <!-- Index page -->
@include('clubs.activities.partials._show-styles')      <!-- Show page -->
@include('clubs.activities.partials._competition-styles') <!-- Comp panel (if competition) -->

<style>
  /* Inline gradient brand color #00D9B5 */
  .activity-detail-card {
    background: linear-gradient(135deg, #00D9B5 0%, #00B399 100%);
    border-radius: 8px;
  }
</style>
```

### Vietnamese Localization
```blade
<!-- Always provide Vietnamese labels for all UI elements -->
Type badges: 'Buổi chơi', 'Lịch cố định', 'Giải đấu'
Buttons: '[+] Thêm', '[OK] Tạo', '[BACK] Quay Lại'
Status: 'Sắp diễn ra', 'Đã hoàn thành', 'Đã hủy'
Fields: 'Ngày & Giờ', 'Địa Điểm', 'Số người', 'Trình độ OPR'
RSVP: 'RSVP Tham gia', 'Hủy tham gia', '[WAITLIST]'
Competition: 'Tao lich thi dau', 'Bảng Xếp Hạng', 'Vòng'
```

---

## AJAX Patterns

### RSVP Toggle (POST)
```javascript
document.querySelector('.btn-rsvp').addEventListener('click', function() {
  const activityId = this.dataset.activityId;
  fetch(`/clubs/${clubId}/activities/${activityId}/rsvp`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrfToken },
    body: JSON.stringify({ user_id: userId })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      document.querySelector('.rsvp-count').textContent =
        `${data.confirmed_count} / ${data.max_participants}`;
      location.reload(); // Refresh participant list
    } else {
      alert(data.message);
    }
  });
});
```

### Score Entry (PUT)
```javascript
document.querySelectorAll('.score-input').forEach(input => {
  input.addEventListener('change', function() {
    const matchId = this.dataset.matchId;
    const team = this.dataset.team;
    const score = this.value;

    fetch(`/clubs/${clubId}/competitions/matches/${matchId}`, {
      method: 'PUT',
      headers: { 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify({ [`team${team}_score`]: score })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        // Update standings table
        location.reload();
      }
    });
  });
});
```

---

## Related Documentation

- [System Architecture](./system-architecture.md) - Club Activity data models & flows
- [Codebase Summary](./codebase-summary.md) - File overview
- [Code Standards](./code-standards.md) - General Laravel patterns
- Phase 4 Plan: `plans/260227-1440-club-activities-reclub-style/phase-04-views-and-ui.md`

---

## Unresolved Questions

None. Phase 4 implementation complete with all views & partials documented.
