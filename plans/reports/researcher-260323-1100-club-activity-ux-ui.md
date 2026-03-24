# UX/UI Research: Club Activity Management System

**Date**: 2026-03-23
**Scope**: 6 features + cross-cutting concerns
**Stack**: Laravel Blade + Alpine.js + custom CSS, mobile-first, Vietnamese UI, AJAX polling

---

## Context

Based on brainstorm doc (`brainstorm-260323-0951-club-activity-management.md`):
- Player journey: QR scan → phone lookup → queue → match → score submit → back to queue
- Two audiences: Players (mobile) and Admin (desktop)
- Real-time = AJAX polling (10-15s interval), no WebSocket
- Vietnamese language throughout

---

## Feature 1: QR Check-in Flow (Mobile)

### Layout / Structure

Single-page progressive flow — one step visible at a time, no back button needed:

```
Step 1 (default visible):
  - Activity name + club logo at top
  - Large headline: "Nhập số điện thoại để tham gia"
  - Phone input (numeric keyboard, large 56px height)
  - CTA button: "Tham gia" (full-width, 56px, high contrast green)
  - No other elements

Step 2 (phone found, auto-transition):
  - Avatar + name: "Xin chào, [Tên]!"
  - Skill level badge (OPRS level)
  - Activity details (tên buổi chơi, số người đang chờ)
  - CTA: "Xác nhận tham gia" (full-width)

Step 3 (phone NOT found, expand registration):
  - Keep phone number pre-filled
  - Add: Tên (text) + optional gender (radio: Nam / Nữ)
  - CTA: "Đăng ký & Tham gia"
  - Micro-copy: "Tài khoản sẽ được tạo tự động"
```

### Key UX Principles

- **One field at a time**: Phone is the only required input initially. Do not show full registration upfront (progressive disclosure).
- **Explain why**: Show "Dùng để tìm kiếm tài khoản của bạn" below phone field. NN/g Baymard research: 14% abandon when phone required with no explanation.
- **Auto-format**: Format phone as `0912 345 678` on-the-fly. Accept both `0` and `+84` prefix, normalize internally.
- **Numeric keyboard**: Input `type="tel"` forces numeric keyboard on mobile — do not use `type="number"` (lacks leading zero support).
- **Immediate feedback**: After phone submit, show inline loading spinner inside the CTA button (replace text, keep button width). Disable button during fetch to prevent double-submit.
- **Success path frictionless**: If existing member, show greeting with name and one-tap confirm. No extra forms.
- **Error inline**: Wrong/missing phone → red border + message under field, no page reload.

### Alpine.js Patterns

```
x-data = {
  step: 1,           // 1=phone, 2=confirm, 3=register
  phone: '',
  user: null,
  loading: false,
  error: '',

  async lookup() {
    this.loading = true; this.error = '';
    try {
      const res = await fetch('/clubs/checkin/lookup', {method:'POST', body: JSON.stringify({phone})...});
      const data = await res.json();
      if (data.found) { this.user = data.user; this.step = 2; }
      else { this.step = 3; }
    } catch { this.error = 'Lỗi kết nối, thử lại.'; }
    finally { this.loading = false; }
  }
}
```

- Use `x-show` with `x-transition` for each step (CSS transition: fade + slide-up).
- `@keydown.enter` on phone input triggers lookup.
- Alpine `$focus.within(...)` to auto-focus first field of next step.

### Custom CSS Techniques

- Phone input: `font-size: 24px; letter-spacing: 4px; text-align: center; border-radius: 12px;` — legible from arm's length.
- Step transition: `transition: opacity 300ms ease, transform 300ms ease`. New step slides up from below.
- Sticky bottom CTA: `position: fixed; bottom: env(safe-area-inset-bottom, 16px)` — above iOS home bar.
- Activity card at top: max-height 80px, logo left + name. Does not push phone field off screen.
- Success state (step 2): Green accent border on user card, scale-in animation (keyframe: scale 0.95 → 1).

### Accessibility

- `aria-live="polite"` on error container — screen readers announce errors.
- Label `for` linked to input; phone field `autocomplete="tel"`.
- Focus trapped to current step's interactive elements.
- Contrast ratio 4.5:1+ for all text on background.

### Real-World References

- MatchUp Tennis: players mark themselves "available" on arrival — same concept but app-based.
- Event check-in kiosks (concerts, sports events): phone-first lookup is dominant pattern in Vietnam.
- Existing `EventCheckin` system in this codebase — reuse QR token validation pattern.

---

## Feature 2: Waiting Queue UI (Mobile)

### Layout / Structure

**Top section (fixed, ~120px):**
- Activity name + "Đang chờ" badge with count
- "Làm mới lần cuối: X giây trước" — stale data timestamp (auto-updates every poll)

**Hero card (player's own status, prominent):**
- Large: `#3 trong hàng chờ` or `Đang thi đấu - Sân 2`
- Estimated wait: `~12 phút` (if in queue)
- Animated pulsing dot: green=playing, amber=waiting, gray=idle

**Queue list (scrollable):**
- Compact row per player: rank circle + name + OPRS level dot + status chip
- Player's own row: highlighted with left border accent + bold name
- Status chips: `Đang chờ` (amber), `Đang đấu` (green), `Nghỉ` (gray)

**Court status strip (fixed bottom or scrollable section):**
- Row of court cards: `Sân 1`, `Sân 2`... with match currently playing (Team A vs Team B)
- Empty court: "Trống" + green tint

### Key UX Principles

- **Position as the hero number**: The queue position must be largest element on screen. Users care about one thing: "Tôi đứng thứ mấy?".
- **Auto-refresh without disruption**: NN/g best practice: inform users the page refreshes automatically. Show subtle "Đang cập nhật..." shimmer during poll, NOT a full spinner that disrupts reading.
- **Timestamp on stale data**: Show "Cập nhật: 8 giây trước" — turns red if > 30s (poll failed). This matches Google Drive's sync indicator pattern.
- **Predict wait**: If avg match = 15 min and there are 2 courts and 4 people ahead → show "~15 phút" estimate.
- **State transitions are animated**: Queue position change (e.g. 4 → 3) should number-animate, not just swap instantly. CSS counter animation.
- **Court assignment = major event**: When assigned to court, the hero card transitions from amber "Đang chờ" to green "Đến Sân 2 ngay!" with a subtle pulse animation. No notification sound needed — visual is sufficient.

### Alpine.js Patterns

```
x-data = {
  queue: [],
  courts: [],
  myStatus: {},
  lastUpdated: null,
  pollInterval: null,
  isStale: false,

  init() {
    this.fetchQueue();
    // Recursive setTimeout safer than setInterval (handles network lag)
    const poll = () => {
      this.fetchQueue().finally(() => {
        this.pollInterval = setTimeout(poll, 12000);
      });
    };
    this.pollInterval = setTimeout(poll, 12000);
  },

  async fetchQueue() {
    try {
      const res = await fetch('/clubs/activity/{id}/queue-status');
      const data = await res.json();
      this.queue = data.queue;
      this.courts = data.courts;
      this.myStatus = data.my_status;
      this.lastUpdated = Date.now();
      this.isStale = false;
    } catch { this.isStale = true; }
  }
}
```

Use `x-effect` to detect when `myStatus.status` changes from `queued` to `playing` → trigger CSS class swap + brief attention animation.

**Recursive setTimeout** preferred over `setInterval`: if a poll takes 11s on slow network, setInterval would fire next immediately creating a storm. setTimeout waits until previous finishes.

### Custom CSS Techniques

- Position number: `font-size: clamp(48px, 12vw, 72px); font-weight: 800;` — dominant on any screen.
- Stale indicator: `.stale-badge { background: #f59e0b; }` transitions via `transition: background 1s ease`.
- Queue list: CSS scroll snap on container + `scroll-snap-align: start` on rows — silky scroll on mobile.
- Court cards: `display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px;` — wraps automatically.
- Status pulse animation:
  ```css
  @keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.3); }
  }
  .status-dot { animation: pulse-dot 2s ease infinite; }
  ```
- Highlight own row: `border-left: 3px solid var(--green-500); background: rgba(var(--green-rgb), 0.06);`

### Accessibility

- `aria-live="polite"` on position counter — announces position changes.
- Court assignment notification: `aria-live="assertive"` when status changes to playing.
- Status chips use role="status" + appropriate color + text (not color alone for state).

### Reference

- NN/g Virtual Queue best practices (all 13 apply): show position + ETA, auto-refresh, explain exit policy, next-step prep.
- Waitwhile queue management system UX: prominent position, SMS option, ETA.

---

## Feature 3: Match Assignment & Score Submission (Mobile)

### Layout / Structure

**Match view (after court assignment):**
- Court number hero: `SÂN 2` in large text
- Match card: Team A (2 players, avatars + names) vs Team B (2 players)
- OPRS ratings shown under each name
- "Bắt đầu trận" CTA (if status=assigned, not yet started)

**Score submission view:**
- Header: `Nhập kết quả - Sân 2`
- Per-set rows:
  ```
  Set 1:  [  11  ]  vs  [   9  ]
  Set 2:  [   7  ]  vs  [  11  ]
  Set 3:  [      ]  vs  [      ]   ← only shows if needed
  ```
- Each score cell: large number, +/- buttons flanking (not tiny inputs)
- "Xác nhận kết quả" CTA (full-width green)
- Result preview: winner team highlighted in green before confirm

### Key UX Principles

- **Large tap targets for score entry**: Use +/- stepper buttons (min 56px × 56px), NOT keyboard input. Sports score entry on mobile = frequent tapping, not typing. T-Score Tracker uses "intuitive two-tap interface" — player taps their team's score to increment.
- **Team A = "Đội tôi"**: Always orient the UI so the submitting player's team is on the left/top. Reduces "who is team A?" confusion.
- **Show set 3 only when needed**: Progressive disclosure. Only add set 3 row when sets 1 and 2 are split (1-1). Default: 2 set rows visible.
- **Preview winner before confirm**: After scores entered, highlight the winning team with green border + "Thắng" badge. Player can verify before submit. Prevents fat-finger errors.
- **One submission enough**: From brainstorm: "1 người nhập là đủ". Show "Kết quả đã được ghi nhận" after submit. No forced confirmation from opponent.
- **Disable confirm until valid**: Both sets must have valid scores before "Xác nhận" is enabled. Gray out + `aria-disabled` until valid.
- **Score range guard**: Pickleball to 11 (or 15/21 in rally scoring). Stepper stops at configured max. Backend validates too.

### Alpine.js Patterns

```
x-data = {
  sets: [
    {team1: 0, team2: 0},
    {team1: 0, team2: 0}
  ],
  showSet3: false,
  submitted: false,

  increment(setIdx, team) { this.sets[setIdx][team] = Math.min(21, this.sets[setIdx][team] + 1); this.checkSet3(); },
  decrement(setIdx, team) { this.sets[setIdx][team] = Math.max(0, this.sets[setIdx][team] - 1); },

  checkSet3() {
    const w1 = this.sets[0].team1 > this.sets[0].team2;
    const w2 = this.sets[1].team1 > this.sets[1].team2;
    this.showSet3 = (w1 !== w2) && this.sets[0].team1 + this.sets[0].team2 > 0 && this.sets[1].team1 + this.sets[1].team2 > 0;
    if (!this.showSet3 && this.sets.length > 2) this.sets.splice(2);
    if (this.showSet3 && this.sets.length < 3) this.sets.push({team1: 0, team2: 0});
  },

  get winner() { /* count set wins */ },
  get isValid() { /* all played sets have non-zero scores and a clear winner */ }
}
```

Use `x-show` + `x-transition` on set 3 row.

### Custom CSS Techniques

- Stepper button: `width: 56px; height: 56px; border-radius: 50%; font-size: 24px; cursor: pointer;`
- Score display: `font-size: 40px; font-weight: 700; min-width: 64px; text-align: center;`
- Set row: `display: flex; align-items: center; justify-content: space-between; gap: 8px;`
- Winner highlight: `border: 2px solid var(--green-500); background: rgba(green-rgb, 0.08);` with `transition: all 300ms ease`.
- "Set 3" slide-in: keyframe `slideDown` (height: 0 → auto using max-height trick).

### Accessibility

- `aria-label="Điểm đội A set 1"` on each score display.
- `role="button"` + `aria-label="Tăng điểm"` on +/- controls.
- Keyboard: stepper responds to arrow keys.
- Winner preview announced via `aria-live="polite"`.

### Reference

- T-Score Tracker (pickleball): two-tap interface, no keyboard.
- All Sports Score Keeper: configurable sets (1-3), best-of format.
- ScoreMate: large number display, minimal chrome.

---

## Feature 4: Admin Activity Dashboard (Desktop)

### Layout / Structure

**3-column desktop layout:**

```
[LEFT SIDEBAR 240px]     [MAIN CONTENT]              [RIGHT PANEL 280px]
- Activity controls      - Court grid (cards)          - Queue list
- Start/pause activity   - Match cards per court        - Auto-match button
- Rotation settings      - Each: players, scores, ETA  - Recent events log
- Player count summary
```

**Court grid (main area):**
- Card per court: `Sân 1`, `Sân 2`...
- Each card shows: match in progress (players + score), match status, elapsed time, action buttons (mark complete, swap players)
- Empty court: green "Trống" placeholder with "Ghép trận" quick action

**Queue panel (right):**
- List of all players: avatar + name + OPRS + status chip + "thời gian chờ: Xm"
- Drag-to-reorder (admin manual priority) or up/down arrows for simpler UX
- "Ghép trận tự động" button (triggers OPRS-based matchmaking)
- Filters: all / queued / playing / idle

**Bottom log strip:**
- Rolling event log: "Sân 2: Đội A thắng 11-7, 11-9 - 14:32"
- Timestamped, newest first

### Key UX Principles

- **Single-screen awareness**: Admin must see everything at once — courts, queue, status. No tabs hiding critical info. Use columns, not pages.
- **Color = status at a glance**: Green = court in use, amber = match finishing soon (near time limit), gray = empty. No legend needed if color is consistent.
- **Bulk actions**: "Ghép trận tự động" should preview the proposed pairings before executing. Show confirmation modal: "Sẽ ghép 8 người thành 2 trận. Xác nhận?"
- **Inline controls, not modals**: Mark match complete, adjust score — inline in court card. Modals for destructive actions only (cancel match, remove player).
- **Auto-refresh badge**: Top right: "Tự động cập nhật • 8s trước" — admin needs to trust the data is live.
- **Quick-add player**: "Thêm người chơi" button at top of queue panel opens a slide-in drawer (not modal) with phone search.

### Alpine.js Patterns

- Global activity state as `Alpine.store('activity', {...})` — shared between sidebar, court grid, queue panel.
- Polling on the page root: same recursive setTimeout pattern. Dashboard polls `/admin/activity/{id}/dashboard-state`.
- Court card: separate `x-data` component that reads from store. Avoids re-rendering entire page on poll.
- Queue list: `x-for` with stable `:key="player.id"` — prevents flicker on re-render during poll.
- Matchmaking modal: `Alpine.store('ui', {matchmakingModal: false, proposed: []})`

### Custom CSS Techniques

- 3-column grid: `display: grid; grid-template-columns: 240px 1fr 280px; gap: 16px; height: 100vh;` for sticky sidebar panels.
- Court cards: `display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;`
- Status border on court cards: `border-left: 4px solid var(--status-color);` — status-color via CSS custom property set by Alpine.
- Elapsed timer: CSS counter not needed — Alpine updates `data-elapsed` every second.
- Log strip: `overflow-y: auto; max-height: 120px;` scroll latest to top.
- Hover actions on court card: `opacity: 0; transition: opacity 150ms;` on action buttons, parent `:hover` sets `opacity: 1`.

### Accessibility

- Live regions on queue count and court status for screen readers.
- Table-mode fallback if grid not supported: `@media (forced-colors: active)` adjustments.

### Reference

- SportMember admin: multi-column member view with filtering.
- MatchUp external display: court-by-court match view.
- B2B sports data platforms: real-time stats dashboard with competition/event views.

---

## Feature 5: Admin Member Management (Desktop)

### Layout / Structure

**Top bar:**
- Search input (search by name / phone / email, debounced 300ms)
- Filter pills: `Tất cả` / `Đang hoạt động` / `Không hoạt động` / `Bị tạm ngưng`
- "Thêm thành viên" CTA button (right-aligned)
- Export dropdown (CSV)

**Data table:**
```
| Avatar | Tên | SĐT | Email | OPRS (ban đầu) | Cấp độ | Trạng thái | Hành động |
```
- Sortable: Name, OPRS, Status columns (click header to toggle asc/desc)
- Inline OPRS edit: click OPRS cell → input field + save icon. No modal.
- Action column: icons only (edit full = pencil, suspend = lock, remove = trash with confirm)
- Pagination: 20 per page, page numbers + prev/next

**"Thêm thành viên" drawer (slide-in from right, not modal):**
- Phone field (lookup existing user) OR
- Email field
- If user not found: full name + phone form (create user)
- Optional: initial OPRS override + notes (textarea, 200 char max)
- "Thêm vào CLB" save button

### Key UX Principles

- **Search as primary navigation**: Phone lookup is main workflow. Instant search as admin types (debounced fetch). Show "Không tìm thấy" state with "Thêm mới" shortcut.
- **Inline OPRS edit**: Most frequent action. Do NOT open a modal for this. Click-to-edit + blur-to-save or explicit save icon. Cloudscape inline edit pattern.
- **Status chips clearly colored**: Active (green), Inactive (gray), Suspended (red). Text + color (not color alone).
- **Confirm destructive actions**: Remove from club → tooltip confirmation ("Xác nhận xóa?") inline, not modal. Suspend → modal with reason field.
- **Bulk select**: Checkbox per row. Bulk actions: suspend selected, export selected.
- **Mobile fallback for admin**: While admin is desktop-focused, table should collapse to card view at < 768px for tablet use in field.

### Alpine.js Patterns

```
x-data = {
  search: '',
  filter: 'all',
  members: [],
  loading: false,
  page: 1,

  init() { this.fetch(); },

  get filtered() { /* client-side filter on already-loaded page, or server-side */ },

  async fetch() {
    this.loading = true;
    const res = await fetch(`/admin/clubs/{id}/members?q=${this.search}&filter=${this.filter}&page=${this.page}`);
    this.members = await res.json();
    this.loading = false;
  }
}
```

- `x-model` with `@input.debounce.300ms` on search field.
- Inline edit: per-row `editing: false` state in member object. Toggle with click.
- Sort: `sortBy` + `sortDir` state, computed property returns sorted `members`.

### Custom CSS Techniques

- Table responsive: at `< 768px`, switch to card view:
  ```css
  @media (max-width: 768px) {
    table, thead, tbody, th, td, tr { display: block; }
    thead tr { position: absolute; top: -9999px; }
    td { border: none; padding: 4px 8px; }
    td::before { content: attr(data-label); font-weight: 600; display: inline-block; width: 120px; }
  }
  ```
- Drawer slide-in: `transform: translateX(100%); transition: transform 300ms ease;` toggled by Alpine class.
- Skeleton rows for loading: CSS `@keyframes shimmer` on placeholder divs.
- Sortable header: `cursor: pointer; user-select: none;` + arrow indicator via `::after` pseudo-element.

### Accessibility

- Table `<th scope="col">` with `aria-sort="ascending"` on active column.
- Drawer has `role="dialog"` + `aria-label` + focus trap.
- Search: `aria-label="Tìm kiếm thành viên"` + `role="search"` on form.

### Reference

- SportMember: multi-field filter (team, role, payment status).
- Cloudscape Design System: inline edit pattern for tables.
- WildApricot: member list with bulk actions.

---

## Feature 6: Club Leaderboard (Desktop + Mobile)

### Layout / Structure

**Top controls:**
- Filter tabs: `Tháng này` / `Tất cả thời gian` (toggle pills)
- Filter by gender (optional, show if data exists)
- Search by name

**Top 3 podium (desktop only, hide on mobile):**
- #1: large center card with gold accent
- #2 left, #3 right, slightly smaller
- Show: avatar, name, win rate, total matches

**Ranked table:**
```
| # | Avatar | Tên | Trận | Thắng | Thua | Tỷ lệ | OPRS | Điểm |
```
- Current user row: sticky + highlighted (amber left border)
- If user is rank 15, they see rows 1-5 + ... + 13-17 + ... (virtual scrolling not needed at club scale)
- Compact rows: 48px height, avatar 32px circle
- Clicking a row: expand in-place to show breakdown (points scored, opponents, last match)

**Mobile layout:**
- No podium (omit)
- Card per player: rank number (left, large) + name + win/loss bar + OPRS chip
- Swipe-left on card: reveal "Xem chi tiết" action
- Current user card: always visible at bottom (fixed) if not in viewport

### Key UX Principles

- **Show user's own position prominently**: IxDF leaderboard principle — show where user stands relative to peers. If they're rank 20, show a sticky "own row" so they always know. Motivates improvement without showing an insurmountable gap.
- **Multiple time frames**: "Tháng này" default (shows active players, feels more achievable). "Tất cả" for bragging rights.
- **Win rate as primary metric**: Wins + Losses visible, but Win Rate (%) is the hero stat. Format: `75%` large, then `(9W 3L)` smaller below.
- **OPRS as sorting default**: Leaderboard sorts by OPRS (the platform's rating system), not just wins. Makes it skill-authentic.
- **Avoid showing huge gaps**: If #1 has 200 matches and everyone else has 10, it's discouraging. Default to "Tháng này" which levels the field.
- **Real-time not required**: Leaderboard can cache 5-10 minutes. No polling needed — user can manual refresh.

### Alpine.js Patterns

```
x-data = {
  period: 'month',
  expanded: null,
  search: '',
  members: [],

  init() { this.fetchLeaderboard(); },

  async fetchLeaderboard() {
    const res = await fetch(`/clubs/{id}/leaderboard?period=${this.period}`);
    this.members = await res.json();
  },

  toggle(id) { this.expanded = this.expanded === id ? null : id; }
}
```

- `x-show` + `x-transition` on expand row.
- `@click` on period pill calls `fetchLeaderboard()` after setting `period`.

### Custom CSS Techniques

- Rank number: `font-size: 20px; font-weight: 800; color: var(--rank-color);` (gold/silver/bronze for top 3 via `:nth-child` or Alpine-bound class).
- Win rate bar: `background: linear-gradient(to right, var(--green) var(--win-pct), var(--gray) 0);` — CSS variable set by Alpine `style` binding.
- Own-row sticky on mobile: `position: sticky; bottom: 0; background: var(--card-bg); border-top: 2px solid var(--accent);`.
- Podium: `display: flex; align-items: flex-end; gap: 8px;` — items have different height padding to create step effect. `@media (max-width: 640px) { .podium { display: none; } }`.
- Expand transition: `max-height: 0 → 200px; overflow: hidden; transition: max-height 300ms ease;` (no `height: auto` limitation with max-height trick).

### Accessibility

- `<caption>` on leaderboard table.
- Rank icons use `aria-label="Hạng 1"`.
- Period toggle uses `role="tablist"` pattern.

### Reference

- IxDF leaderboard pattern: show relative position, multiple time views.
- NN/g gamification: leaderboards motivate if gap is achievable.
- Mobbin leaderboard screens: compact mobile list + podium hybrid.

---

## Cross-Cutting: AJAX Polling UX Patterns

### Recommendations

| Concern | Solution |
|---------|----------|
| Show data is live | "Cập nhật: Xs trước" timestamp. Turns amber after 30s, red after 60s. |
| Avoid full-page flash | Update Alpine data arrays in-place. DOM diffs via `x-for` keyed by `id`. |
| Network failure | `isStale: true` flag → banner "Mất kết nối - đang thử lại..." |
| Prevent double-poll | Recursive setTimeout, NOT setInterval. Wait for response before next poll. |
| Cancel on navigate | `clearTimeout(pollInterval)` in `destroy()` hook or page unload. |
| Loading state | Show subtle shimmer on updated sections, NOT full spinner (disrupts reading). |
| Stale data visible | Never blank out old data while fetching. Show old data + update indicator. |
| Polling interval | Queue page: 12s. Admin dashboard: 8s. Score submission: no polling needed. |

### Alpine.js Polling Template (canonical)

```javascript
{
  data: [],
  lastUpdated: null,
  isStale: false,
  _pollTimer: null,

  init() { this._poll(); },

  async _poll() {
    try {
      const r = await fetch(this.endpoint);
      if (!r.ok) throw new Error(r.status);
      this.data = await r.json();
      this.lastUpdated = Date.now();
      this.isStale = false;
    } catch {
      this.isStale = true;
    } finally {
      this._pollTimer = setTimeout(() => this._poll(), this.interval);
    }
  },

  destroy() { clearTimeout(this._pollTimer); },

  get staleSince() {
    if (!this.lastUpdated) return null;
    return Math.floor((Date.now() - this.lastUpdated) / 1000);
  }
}
```

Use `x-effect` to watch `staleSince` and update a UI indicator reactively.

---

## Cross-Cutting: Mobile Touch UI Patterns

- **Tap targets**: 48×48px minimum for all interactive elements (WCAG 2.5.8). Score steppers: 56×56px.
- **One-hand zone**: Primary CTAs in bottom third of screen. Top area for read-only info.
- **Avoid small inputs**: No keyboard for score entry. Use stepper buttons. `type="tel"` for phone.
- **Swipe gestures**: Use sparingly (not discoverable without onboarding). Reserve for: swipe-left to reveal actions in lists.
- **Loading feedback**: Spinner inside button (replace text). Never disable without visual indication.
- **Prevent accidental actions**: 2-step confirm for destructive actions (tap → confirm tooltip → execute).
- **Vietnamese text**: Vietnamese words are longer than English equivalents. Allow `white-space: normal` on buttons and labels, or use `font-size: clamp(14px, 3.5vw, 16px)`.

---

## Cross-Cutting: CSS Techniques (No Framework)

### Custom Properties System (Required)

Define a set of CSS variables in `:root` to replace framework utilities:

```css
:root {
  --green-500: #22c55e; --green-rgb: 34, 197, 94;
  --amber-500: #f59e0b;
  --red-500: #ef4444;
  --gray-100: #f3f4f6; --gray-300: #d1d5db; --gray-700: #374151;
  --font-base: 16px;
  --radius-sm: 8px; --radius-md: 12px; --radius-lg: 20px;
  --shadow-card: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
  --transition-fast: 150ms ease;
  --transition-base: 300ms ease;
}
```

### Key Reusable Patterns

**Full-width CTA button (mobile):**
```css
.btn-primary {
  display: block; width: 100%; padding: 16px 24px;
  font-size: 16px; font-weight: 600; text-align: center;
  background: var(--green-500); color: #fff; border-radius: var(--radius-md);
  border: none; cursor: pointer; transition: opacity var(--transition-fast);
}
.btn-primary:active { opacity: 0.8; }
.btn-primary[disabled] { background: var(--gray-300); cursor: not-allowed; }
```

**Card:**
```css
.card { background: #fff; border-radius: var(--radius-md); padding: 16px; box-shadow: var(--shadow-card); }
```

**Responsive table → cards at mobile:**
```css
@media (max-width: 640px) {
  table, thead, tbody, th, td, tr { display: block; }
  thead tr { display: none; }
  tr { border: 1px solid var(--gray-300); border-radius: var(--radius-sm); margin-bottom: 8px; padding: 8px; }
  td::before { content: attr(data-label); font-weight: 600; display: block; font-size: 12px; color: var(--gray-700); }
}
```

**Shimmer skeleton:**
```css
@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}
.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: var(--radius-sm);
}
```

**Slide-in drawer (mobile/desktop):**
```css
.drawer { position: fixed; top: 0; right: 0; height: 100%; width: 320px;
  background: #fff; transform: translateX(100%); transition: transform var(--transition-base);
  box-shadow: -4px 0 24px rgba(0,0,0,0.15); z-index: 100; }
.drawer.is-open { transform: translateX(0); }
```

**Slide-up step transition (check-in):**
```css
@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.step-enter { animation: slideUp 300ms ease forwards; }
```

---

## Vietnamese UI Conventions

- Use `font-display: swap` for any web font. System font stack fallback: `-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif` — renders Vietnamese correctly.
- Avoid ALL CAPS for Vietnamese text — diacritics become illegible at certain fonts/sizes.
- Status labels: `Đang chờ` (queued), `Đang đấu` (playing), `Nghỉ` (idle/resting), `Đã rời` (left), `Trống` (court empty), `Hoàn thành` (completed).
- Use `lang="vi"` on `<html>` for correct hyphenation behavior.
- Date/time format: `HH:mm DD/MM` (24h clock, Vietnamese convention).

---

## Unresolved Questions

1. **Poll failure escalation**: Should failed polls (after 3 retries) show a "Tải lại trang" button, or attempt reconnection silently? Need UX decision on error severity.
2. **Player opt-out from queue**: Can a player tap "Tôi muốn nghỉ" without leaving the activity? UX flow for voluntary rest state vs. leaving entirely.
3. **Score dispute**: Brainstorm says "1 người nhập là đủ" but what if opponent disputes? UX flow for admin override? No UI designed for this yet.
4. **Gender filter in leaderboard**: If gender data is incomplete (many users have null gender), does the filter show misleading data? Need data quality assessment.
5. **OPRS display precision**: Show OPRS as "3.2" or "3.24"? Rounding affects ranking tie-breaking visibility.
6. **Offline tolerance**: Mobile players on courts may have spotty WiFi. Queue page must fail gracefully. Need explicit "offline" state UX.
7. **Admin matchmaking preview**: Proposed pairing preview before confirm — does admin see OPRS per player? How detailed?

---

## Sources

- [NN/g: 13 Virtual Queue Best Practices](https://www.nngroup.com/articles/virtual-queue-best-practices/)
- [NN/g: QR Code Usability Guidelines](https://www.nngroup.com/articles/qr-code-guidelines/)
- [Baymard: Phone Field Explanation Required](https://baymard.com/blog/explain-phone-number-field)
- [IxDF: Leaderboard Design Pattern](https://www.interaction-design.org/literature/article/increase-competitiveness-in-users-with-leader-boards/)
- [UI Patterns: Leaderboard](https://ui-patterns.com/patterns/leaderboard)
- [Cloudscape: Inline Edit Pattern](https://cloudscape.design/patterns/resource-management/edit/inline-edit/)
- [DEV: Think Twice Before setInterval for Polling](https://dev.to/igadii/think-twice-before-using-setinterval-for-api-polling-it-might-not-be-ideal-3n3)
- [Alpine.js Polling Pattern](https://khalidabuhakmeh.com/alpinejs-polling-aspnet-core-apis-for-updates)
- [CSS-Tricks: Responsive Data Tables](https://css-tricks.com/responsive-data-tables/)
- [Smashing Magazine: Accessible Tap Targets](https://www.smashingmagazine.com/2023/04/accessible-tap-target-sizes-rage-taps-clicks/)
- [MatchUp Tennis & Pickleball Features](https://www.matchuptennis.app/app-features)
- [Pencilandpaper: UX Patterns for Loading](https://www.pencilandpaper.io/articles/ux-pattern-analysis-loading-feedback)
- [Nemo-Q: Design Principles for Digital Queue Management](https://nemo-q.com/blog/design-principles-digital-queue-management/)
- [LogRocket: Progressive Disclosure in UX](https://blog.logrocket.com/ux-design/progressive-disclosure-ux-types-use-cases/)
- [DEV: Responsive Card Tables CSS](https://dev.to/subu_hunter/build-stunning-responsive-card-tables-with-css4-css5-1fai)
- [SportMember: Member Administration](https://www.sportmember.com/en/membership-management-software-for-clubs-free/member-administration-with-advanced-search-and-filtering-capabilities)
