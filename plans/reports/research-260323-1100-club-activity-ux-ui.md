# Research Report: UX/UI cho Club Activity Management

**Date**: 2026-03-23
**Stack**: Laravel Blade + Alpine.js + Custom CSS (no framework)
**Constraint**: Mobile-first, Vietnamese UI, AJAX polling

---

## Executive Summary

Research tong hop UX/UI cho 6 features cua Club Activity Management, ket hop phan tich codebase hien tai va best practices tu NN/g, IxDF, Baymard. Tat ca UI dung custom CSS (CSS variables da co san trong project), Alpine.js function factory pattern (nhat quan voi codebase), va AJAX polling (recursive setTimeout). Prefix CSS: `ca-` (club-activity).

---

## Design System - Reuse tu Codebase

Project da co CSS variables day du. Them cac bien moi cho Club Activity:

```css
/* Them vao style.css :root */
--ca-status-queued: var(--accent-orange);    /* #FF8E53 */
--ca-status-playing: var(--primary-color);   /* #00D9B5 */
--ca-status-idle: var(--text-light);         /* #999999 */
--ca-status-left: var(--accent-red);         /* #FF6B6B */
--ca-court-empty: var(--primary-light);      /* #33E3C6 */
```

**CSS naming**: `ca-` prefix, BEM-inspired kebab-case (nhat quan voi `td-` prefix cua tournament-dashboard).
**Alpine pattern**: Function factory `function caComponentName(config) { return { ... } }` (nhat quan voi `tournamentAthletes()` pattern).
**Breakpoints**: 1024px / 768px / 480px (giu nguyen).

---

## Feature 1: QR Check-in (Mobile)

### Layout
Single-page progressive flow, 1 step visible/luc:

```
Step 1: Club logo + "Nhap so dien thoai de tham gia"
        Phone input (type=tel, 56px height, centered, letter-spacing 4px)
        CTA: "Tham gia" (fixed bottom, full-width)

Step 2 (found): Avatar + "Xin chao, [Ten]!" + OPRS badge
                Activity info (ten buoi choi, so nguoi cho)
                CTA: "Xac nhan tham gia"

Step 3 (not found): Phone pre-filled + Ten (text) + Gender (radio)
                    CTA: "Dang ky & Tham gia"
                    Micro-copy: "Tai khoan se duoc tao tu dong"
```

### UX Principles
- **Progressive disclosure**: Chi hien phone field ban dau. Khong show full form.
- **Explain why**: "Dung de tim kiem tai khoan cua ban" duoi phone field (Baymard: 14% abandon khi khong giai thich).
- **type="tel"**: Bat numeric keyboard. KHONG dung type="number" (mat leading zero).
- **Auto-format**: `0912 345 678` on-the-fly. Accept `0` va `+84` prefix.
- **Loading in-button**: Spinner thay text trong CTA khi fetch. Giu width button.
- **Error inline**: Red border + message duoi field. Khong reload page.

### Alpine.js
```javascript
function caCheckin(config) {
    return {
        step: 1, phone: '', user: null, loading: false, error: '',
        form: { name: '', gender: null },

        async lookup() {
            this.loading = true; this.error = '';
            try {
                const res = await fetch(config.lookupUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
                    body: JSON.stringify({ phone: this.phone })
                });
                const data = await res.json();
                if (data.found) { this.user = data.user; this.step = 2; }
                else { this.step = 3; }
            } catch { this.error = 'Loi ket noi, thu lai.'; }
            finally { this.loading = false; }
        },

        async checkin() { /* POST confirm or register+checkin */ },
        async register() { /* POST create user + checkin */ }
    };
}
```

### CSS
```css
/* File: public/assets/css/club-activity-checkin.css */
.ca-checkin-page { min-height: 100vh; padding: var(--spacing-md); display: flex; flex-direction: column; }
.ca-phone-input {
    font-size: 24px; letter-spacing: 4px; text-align: center;
    height: 56px; border: 2px solid var(--border-color); border-radius: var(--radius-lg);
    width: 100%; padding: 0 var(--spacing-md);
    transition: border-color var(--transition-fast);
}
.ca-phone-input:focus { border-color: var(--primary-color); outline: none; }
.ca-phone-input.has-error { border-color: var(--accent-red); }
.ca-cta-fixed {
    position: fixed; bottom: env(safe-area-inset-bottom, 16px); left: var(--spacing-md);
    right: var(--spacing-md); z-index: 10;
}
.ca-step-enter { animation: caSlideUp 300ms ease forwards; }
@keyframes caSlideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.ca-user-card {
    border: 2px solid var(--primary-color); border-radius: var(--radius-lg);
    padding: var(--spacing-md); background: rgba(0, 217, 181, 0.06);
    animation: caScaleIn 300ms ease;
}
@keyframes caScaleIn {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
```

### Accessibility
- `aria-live="polite"` tren error container
- Label `for` linked input; `autocomplete="tel"`
- Focus trap trong step hien tai
- Contrast ratio 4.5:1+

---

## Feature 2: Waiting Queue (Mobile)

### Layout
```
[FIXED TOP ~120px]
  Activity name + "Dang cho" badge (count)
  "Cap nhat: Xs truoc" (stale indicator)

[HERO CARD - player's own status]
  Large: "#3 trong hang cho" hoac "Dang thi dau - San 2"
  Estimated wait: "~12 phut"
  Pulsing dot: green=playing, amber=waiting, gray=idle

[QUEUE LIST - scrollable]
  Compact row/player: rank circle + name + OPRS dot + status chip
  Own row: highlighted left border + bold

[COURT STATUS - bottom strip]
  Court cards: "San 1" (Team A vs Team B), "San 2" (Trong)
```

### UX Principles
- **Queue position = hero number**: `clamp(48px, 12vw, 72px)` font-size. Largest element.
- **Never blank old data**: Update data in-place. Show old data + shimmer during refresh. KHONG dung full spinner.
- **Stale indicator**: "Cap nhat: 8s truoc" → amber >30s, red >60s.
- **Predict wait**: `avg_match_duration * ceil(people_ahead / courts_count / 4)`.
- **State transition animated**: Queue position change number-animate. Court assignment = pulse animation + `aria-live="assertive"`.

### Alpine.js - Canonical Polling Pattern
```javascript
function caQueue(config) {
    return {
        queue: [], courts: [], myStatus: {},
        lastUpdated: null, isStale: false,
        _pollTimer: null,

        init() { this._poll(); },

        async _poll() {
            try {
                const res = await fetch(config.statusUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error(res.status);
                const data = await res.json();
                this.queue = data.queue;
                this.courts = data.courts;
                this.myStatus = data.my_status;
                this.lastUpdated = Date.now();
                this.isStale = false;
            } catch {
                this.isStale = true;
            } finally {
                this._pollTimer = setTimeout(() => this._poll(), 12000);
            }
        },

        destroy() { clearTimeout(this._pollTimer); },

        get staleSince() {
            if (!this.lastUpdated) return null;
            return Math.floor((Date.now() - this.lastUpdated) / 1000);
        },

        get estimatedWait() {
            if (!this.myStatus.queue_position) return null;
            const avgMin = config.avgMatchDuration || 15;
            return Math.ceil(this.myStatus.queue_position / (config.courtsCount * 4)) * avgMin;
        }
    };
}
```

**Recursive setTimeout** (KHONG dung setInterval): Doi response xong moi schedule next. Tranh request storm tren mang cham.

### CSS
```css
/* File: public/assets/css/club-activity-queue.css */
.ca-queue-position {
    font-size: clamp(48px, 12vw, 72px); font-weight: 800;
    line-height: 1; color: var(--text-primary);
}
.ca-status-dot {
    width: 10px; height: 10px; border-radius: 50%; display: inline-block;
    animation: caPulseDot 2s ease infinite;
}
.ca-status-dot.queued { background: var(--ca-status-queued); }
.ca-status-dot.playing { background: var(--ca-status-playing); }
.ca-status-dot.idle { background: var(--ca-status-idle); }
@keyframes caPulseDot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.3); }
}
.ca-queue-row {
    display: flex; align-items: center; gap: var(--spacing-sm);
    padding: var(--spacing-sm) var(--spacing-md); border-bottom: 1px solid var(--border-color);
}
.ca-queue-row.is-me {
    border-left: 3px solid var(--primary-color);
    background: rgba(0, 217, 181, 0.06); font-weight: 600;
}
.ca-stale-badge { font-size: var(--font-size-xs); color: var(--text-light); transition: color 1s ease; }
.ca-stale-badge.warning { color: var(--accent-orange); }
.ca-stale-badge.error { color: var(--accent-red); }
.ca-court-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px;
}
.ca-court-card {
    padding: var(--spacing-sm); border-radius: var(--radius-sm);
    border: 1px solid var(--border-color); text-align: center; font-size: var(--font-size-sm);
}
.ca-court-card.empty { background: rgba(0, 217, 181, 0.08); border-color: var(--ca-court-empty); }
```

---

## Feature 3: Match Assignment & Score Submission (Mobile)

### Layout
```
[MATCH VIEW - after court assignment]
  Court hero: "SAN 2" large
  Team A (avatar + ten + OPRS) vs Team B
  CTA: "Bat dau tran" (if assigned, not started)

[SCORE SUBMISSION]
  Header: "Nhap ket qua - San 2"
  Set rows:
    Set 1: [-] [11] [+]  vs  [-] [ 9] [+]
    Set 2: [-] [ 7] [+]  vs  [-] [11] [+]
    Set 3: (chi hien khi sets split 1-1)
  Winner preview: green border team thang
  CTA: "Xac nhan ket qua" (disabled until valid)
```

### UX Principles
- **Stepper buttons 56x56px**: KHONG dung keyboard input. Sports score entry = tapping, not typing.
- **"Doi toi" always left**: Submitter's team luon ben trai/tren. Giam confusion.
- **Set 3 progressive**: Chi hien khi set 1-2 split (x-show + x-transition).
- **Preview winner before confirm**: Green border + "Thang" badge. Prevent fat-finger errors.
- **Score range guard**: Stepper stops at max (11/15/21 tuy config). Backend validate.
- **Disable confirm until valid**: Gray out + `aria-disabled` cho den khi du dieu kien.

### Alpine.js
```javascript
function caScoreSubmit(config) {
    return {
        sets: [{ team1: 0, team2: 0 }, { team1: 0, team2: 0 }],
        submitted: false, loading: false,
        maxScore: config.maxScore || 11,

        increment(setIdx, team) {
            if (this.sets[setIdx][team] < this.maxScore) this.sets[setIdx][team]++;
            this.checkSet3();
        },
        decrement(setIdx, team) {
            if (this.sets[setIdx][team] > 0) this.sets[setIdx][team]--;
            this.checkSet3();
        },
        checkSet3() {
            const s = this.sets;
            const w1 = s[0].team1 > s[0].team2;
            const w2 = s[1].team1 > s[1].team2;
            const played = (s[0].team1 + s[0].team2 > 0) && (s[1].team1 + s[1].team2 > 0);
            const needSet3 = played && (w1 !== w2);
            if (needSet3 && this.sets.length < 3) this.sets.push({ team1: 0, team2: 0 });
            if (!needSet3 && this.sets.length > 2) this.sets.splice(2);
        },
        get winner() {
            let t1 = 0, t2 = 0;
            this.sets.forEach(s => { if (s.team1 > s.team2) t1++; else if (s.team2 > s.team1) t2++; });
            if (t1 > t2) return 'team1'; if (t2 > t1) return 'team2'; return null;
        },
        get isValid() {
            return this.sets.every(s => (s.team1 > 0 || s.team2 > 0) && s.team1 !== s.team2)
                && this.winner !== null;
        },
        async submit() {
            this.loading = true;
            await fetch(config.submitUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
                body: JSON.stringify({ set_scores: this.sets })
            });
            this.submitted = true; this.loading = false;
        }
    };
}
```

### CSS
```css
/* File: public/assets/css/club-activity-score.css */
.ca-score-stepper {
    display: flex; align-items: center; gap: 4px;
}
.ca-stepper-btn {
    width: 56px; height: 56px; border-radius: 50%; border: 2px solid var(--border-color);
    background: var(--bg-light); font-size: 24px; font-weight: 700;
    cursor: pointer; transition: all var(--transition-fast);
    display: flex; align-items: center; justify-content: center;
}
.ca-stepper-btn:active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
.ca-score-display {
    font-size: 40px; font-weight: 700; min-width: 64px; text-align: center;
}
.ca-set-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: var(--spacing-sm) 0; gap: 8px;
}
.ca-team-card { padding: var(--spacing-md); border-radius: var(--radius-md); border: 2px solid transparent; transition: all var(--transition-base); }
.ca-team-card.is-winner { border-color: var(--primary-color); background: rgba(0, 217, 181, 0.08); }
.ca-vs-label { font-size: var(--font-size-sm); color: var(--text-light); font-weight: 600; }
```

---

## Feature 4: Admin Activity Dashboard (Desktop)

### Layout
```
3-column grid (1024px+):
[LEFT SIDEBAR 240px]     [MAIN - Court Grid]              [RIGHT PANEL 280px]
- Activity controls       - Court cards (auto-fill grid)    - Queue list
- Start/pause/end         - Each: players, score, elapsed   - Auto-match button
- Rotation settings       - Empty: "Trong" + "Ghep tran"    - Event log (scroll)
- Player count

Mobile (<1024px): single column, tabs for Courts/Queue/Controls
```

### UX Principles
- **Single-screen awareness**: Admin thay tat ca cung luc. KHONG tabs che info quan trong.
- **Color = status**: Green=dang choi, amber=sap xong, gray=trong. Nhat quan, khong can legend.
- **Matchmaking preview**: "Se ghep 8 nguoi thanh 2 tran. Xac nhan?" modal truoc khi execute.
- **Inline controls**: Mark match complete, adjust score = inline trong court card. Modal chi cho destructive actions.
- **Auto-refresh badge**: "Tu dong cap nhat - 8s truoc" (poll interval 8s cho admin).

### Alpine.js
- Dung `Alpine.store('activity', {...})` cho shared state giua 3 columns
- Court card: `x-data` rieng, doc tu store. Tranh re-render toan page khi poll.
- Queue list: `x-for` voi `:key="player.id"` stable - tranh flicker.
- Poll interval: 8s (nhanh hon player 12s)

### CSS
```css
/* File: public/assets/css/club-activity-dashboard.css */
.ca-dashboard {
    display: grid; grid-template-columns: 240px 1fr 280px; gap: var(--spacing-md);
    height: 100vh; overflow: hidden;
}
.ca-dashboard-sidebar, .ca-dashboard-panel {
    overflow-y: auto; padding: var(--spacing-md);
    border-right: 1px solid var(--border-color);
}
.ca-dashboard-panel { border-right: none; border-left: 1px solid var(--border-color); }
.ca-court-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 12px; padding: var(--spacing-md); overflow-y: auto;
}
.ca-court-card-admin {
    border-radius: var(--radius-md); padding: var(--spacing-md);
    background: white; box-shadow: var(--shadow-sm);
    border-left: 4px solid var(--border-color);
}
.ca-court-card-admin.playing { border-left-color: var(--primary-color); }
.ca-court-card-admin.empty { border-left-color: var(--primary-light); background: var(--bg-light); }
.ca-court-actions { opacity: 0; transition: opacity var(--transition-fast); }
.ca-court-card-admin:hover .ca-court-actions { opacity: 1; }
.ca-event-log { max-height: 120px; overflow-y: auto; font-size: var(--font-size-xs); }

@media (max-width: 1024px) {
    .ca-dashboard { grid-template-columns: 1fr; height: auto; }
    .ca-dashboard-sidebar, .ca-dashboard-panel {
        border: none; border-bottom: 1px solid var(--border-color);
    }
}
```

---

## Feature 5: Admin Member Management (Desktop)

### Layout
```
[TOP BAR]
  Search (debounce 300ms) | Filter pills (Tat ca/Active/Inactive/Suspended) | "Them thanh vien" CTA

[DATA TABLE]
  | Avatar | Ten | SDT | Email | OPRS | Cap do | Trang thai | Hanh dong |
  Inline OPRS edit (click cell -> input -> blur save)
  Mobile (<768px): card view via CSS data-label trick

[SLIDE-IN DRAWER] (right, 320px)
  Phone lookup -> user found: confirm add | not found: create form
  Optional: initial OPRS + notes
```

### UX Principles
- **Phone search primary**: Instant search khi admin type (debounced).
- **Inline OPRS edit**: Click-to-edit + blur-to-save. KHONG mo modal cho action nay.
- **Table -> card mobile**: CSS `display: block` + `::before` data-label trick.
- **Drawer, not modal**: Slide-in tu phai cho "Them thanh vien". `translateX(100%) -> translateX(0)`.

### Alpine.js
```javascript
function caMembers(config) {
    return {
        search: '', filter: 'all', members: config.members || [],
        page: 1, loading: false, drawerOpen: false,
        editingOprs: null, // member id being edited

        get filtered() {
            return this.members.filter(m => {
                const matchFilter = this.filter === 'all' || m.member_status === this.filter;
                const q = this.search.toLowerCase().trim();
                const matchSearch = !q || m.name.toLowerCase().includes(q)
                    || (m.phone && m.phone.includes(q));
                return matchFilter && matchSearch;
            });
        },

        startEditOprs(id) { this.editingOprs = id; },
        async saveOprs(member) {
            await fetch(config.updateOprsUrl.replace(':id', member.id), {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrfToken },
                body: JSON.stringify({ initial_oprs: member.initial_oprs })
            });
            this.editingOprs = null;
        }
    };
}
```

### CSS
```css
/* File: public/assets/css/club-activity-members.css */
.ca-members-table { width: 100%; border-collapse: collapse; }
.ca-members-table th { font-size: var(--font-size-sm); color: var(--text-secondary); text-align: left; padding: var(--spacing-sm); cursor: pointer; user-select: none; }
.ca-members-table td { padding: var(--spacing-sm); border-bottom: 1px solid var(--border-color); }
.ca-inline-edit {
    width: 60px; padding: 4px 8px; border: 1px solid var(--primary-color);
    border-radius: var(--radius-sm); font-size: var(--font-size-sm); text-align: center;
}
.ca-drawer {
    position: fixed; top: 0; right: 0; height: 100%; width: 320px;
    background: white; transform: translateX(100%); transition: transform var(--transition-base);
    box-shadow: -4px 0 24px rgba(0,0,0,0.15); z-index: 100; overflow-y: auto;
    padding: var(--spacing-lg);
}
.ca-drawer.is-open { transform: translateX(0); }
.ca-drawer-backdrop {
    position: fixed; inset: 0; background: rgba(0,0,0,0.3);
    opacity: 0; transition: opacity var(--transition-base); pointer-events: none;
}
.ca-drawer-backdrop.is-open { opacity: 1; pointer-events: auto; }

@media (max-width: 768px) {
    .ca-members-table, .ca-members-table thead,
    .ca-members-table tbody, .ca-members-table th,
    .ca-members-table td, .ca-members-table tr { display: block; }
    .ca-members-table thead tr { position: absolute; top: -9999px; }
    .ca-members-table tr { border: 1px solid var(--border-color); border-radius: var(--radius-sm); margin-bottom: 8px; padding: var(--spacing-sm); }
    .ca-members-table td { border: none; padding: 4px var(--spacing-sm); }
    .ca-members-table td::before { content: attr(data-label); font-weight: 600; display: block; font-size: var(--font-size-xs); color: var(--text-secondary); }
}
```

---

## Feature 6: Club Leaderboard (Desktop + Mobile)

### Layout
```
[TOP] Filter tabs: "Thang nay" / "Tat ca" | Search by name

[PODIUM - desktop only, hide mobile]
  #1 center (gold), #2 left (silver), #3 right (bronze)

[RANKED TABLE/LIST]
  | # | Avatar | Ten | Tran | Thang | Thua | Ty le | OPRS |
  Own row: sticky bottom on mobile if out of viewport
  Click row: expand in-place for detail breakdown

[MOBILE] Card per player: rank (large left) + name + win/loss bar + OPRS chip
```

### UX Principles
- **"Thang nay" default**: Smaller gap between players = more motivating (NN/g gamification).
- **Win rate hero stat**: `75%` large, `(9T 3B)` smaller below.
- **OPRS sort default**: Skill-authentic ranking.
- **Own row sticky**: Player luon thay vi tri cua minh.
- **No polling needed**: Cache 5-10 min. Manual refresh OK.

### CSS
```css
/* File: public/assets/css/club-activity-leaderboard.css */
.ca-leaderboard-podium {
    display: flex; align-items: flex-end; justify-content: center; gap: var(--spacing-sm);
    padding: var(--spacing-xl) 0;
}
.ca-podium-item { text-align: center; padding: var(--spacing-md); border-radius: var(--radius-md); }
.ca-podium-item.gold { background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); order: 2; padding-bottom: var(--spacing-xl); }
.ca-podium-item.silver { background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%); order: 1; }
.ca-podium-item.bronze { background: linear-gradient(135deg, #cd7f32 0%, #dba058 100%); order: 3; }
.ca-rank-number { font-size: 20px; font-weight: 800; }
.ca-rank-number.gold { color: #b8860b; }
.ca-rank-number.silver { color: #71706e; }
.ca-rank-number.bronze { color: #8b5e3c; }
.ca-winrate-bar {
    height: 6px; border-radius: 3px; background: var(--border-color); overflow: hidden;
}
.ca-winrate-fill { height: 100%; background: var(--primary-color); transition: width var(--transition-base); }
.ca-own-row {
    position: sticky; bottom: 0; background: white;
    border-top: 2px solid var(--primary-color); z-index: 5;
}

@media (max-width: 640px) { .ca-leaderboard-podium { display: none; } }
```

---

## Cross-Cutting Patterns

### AJAX Polling (tat ca features can real-time)

| Concern | Solution |
|---------|----------|
| Show data is live | "Cap nhat: Xs truoc" - amber >30s, red >60s |
| Network failure | `isStale: true` -> banner "Mat ket noi - dang thu lai..." |
| Prevent double-poll | Recursive setTimeout, KHONG setInterval |
| Loading state | Shimmer skeleton, KHONG full spinner |
| Old data visible | Never blank. Show old data + update indicator |
| Intervals | Queue: 12s, Admin dashboard: 8s, Score: no polling |

### Shimmer Skeleton (loading state)
```css
@keyframes caShimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
.ca-skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
    background-size: 200% 100%; animation: caShimmer 1.5s infinite;
    border-radius: var(--radius-sm); height: 16px;
}
```

### Vietnamese UI Conventions
- **KHONG** dung ALL CAPS cho tieng Viet (diacritics bi vo)
- Date/time: `HH:mm DD/MM` (24h)
- `lang="vi"` tren `<html>`
- Status labels: `Dang cho`, `Dang dau`, `Nghi`, `Da roi`, `Trong`, `Hoan thanh`
- Vietnamese text dai hon English -> `white-space: normal` tren buttons, `font-size: clamp(14px, 3.5vw, 16px)`

### Touch Targets (mobile)
- Min 48x48px cho tat ca interactive elements (WCAG 2.5.8)
- Score steppers: 56x56px
- Primary CTAs: bottom 1/3 man hinh (one-hand zone)
- Loading: spinner trong button (thay text), KHONG disable khong co visual indication

### CSS File Organization
```
public/assets/css/
  club-activity-checkin.css      (~80 lines)
  club-activity-queue.css        (~100 lines)
  club-activity-score.css        (~60 lines)
  club-activity-dashboard.css    (~80 lines)
  club-activity-members.css      (~90 lines)
  club-activity-leaderboard.css  (~70 lines)
```

### Alpine.js File Organization
```
public/assets/js/
  club-activity-checkin.js       (~60 lines)
  club-activity-queue.js         (~80 lines)
  club-activity-score.js         (~70 lines)
  club-activity-dashboard.js     (~100 lines)
  club-activity-members.js       (~80 lines)
  club-activity-leaderboard.js   (~50 lines)
```

---

## Unresolved Questions

1. **Poll failure escalation**: Failed polls sau 3 retries -> "Tai lai trang" button hay silent retry? Can UX decision.
2. **Player opt-out from queue**: "Toi muon nghi" flow chua co UX design. Voluntary rest vs leaving entirely.
3. **Score dispute**: "1 nguoi nhap la du" nhung opponent dispute thi sao? Admin override flow chua thiet ke.
4. **Gender filter data quality**: Nhieu users co null gender. Filter co misleading?
5. **OPRS display precision**: "3.2" hay "3.24"? Anh huong tie-breaking visibility.
6. **Offline tolerance**: Player tren san co the WiFi yeu. Queue page can explicit "offline" state.
7. **Admin matchmaking preview**: Preview pairing co show OPRS tung nguoi? Detail level nao?

---

## Sources

- NN/g: Virtual Queue Best Practices, QR Code Usability Guidelines
- Baymard: Phone Field Explanation Required (14% abandon)
- IxDF: Leaderboard Design Pattern
- Cloudscape Design System: Inline Edit Pattern
- CSS-Tricks: Responsive Data Tables
- DEV: Recursive setTimeout vs setInterval for polling
- Alpine.js Polling Pattern (khalidabuhakmeh)
- MatchUp Tennis & Pickleball app features
- Existing codebase patterns: tournament-dashboard CSS, Alpine function factories, MatchesApi fetch patterns
