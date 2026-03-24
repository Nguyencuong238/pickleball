# Brainstorm: Club Activity Management Feature

**Date**: 2026-03-23
**Status**: Analysis & Proposal
**Scope**: 4 modules, ~15 sub-features

---

## 1. Problem Statement

Boss yeu cau bo sung tinh nang quan ly hoat dong CLB tren OnePickleball. Muc tieu: so hoa quy trinh buoi choi CLB tu check-in, ghep cap, chia san, nhap ket qua den thong ke noi bo.

## 2. Current System Analysis

### What Already Exists (Reusable)

| Component | Status | Reuse Level |
|-----------|--------|-------------|
| `Club` model + `club_members` (roles: creator/admin/moderator/member) | Done | **High** - da co role system |
| `ClubActivity` (type: one_off/recurring/competition, RSVP, waitlist) | Done | **High** - extend, khong viet lai |
| `ClubActivityParticipant` (confirmed/waitlisted/cancelled) | Done | **High** - da co waitlist logic |
| `ClubActivityMatch` (singles/doubles, scores, 4 players) | Done | **Medium** - can extend them trang thai |
| `ClubActivityMatchStanding` (wins/losses/points_scored/against) | Done | **High** - gan giong yeu cau BXH |
| `ClubActivityMatchRound` (round_number, status) | Done | **Medium** - can them metadata |
| `ClubMatchService` (3 algorithms: singles_rr, rotating_doubles, fixed_doubles) | Done | **High** - da co ghep cap |
| `ClubActivityService` (RSVP + waitlist + lockForUpdate) | Done | **High** - co the extend |
| `OprsService` (Elo 70% + Challenge 20% + Community 10%) | Done | **Medium** - can them reason moi |
| `EloService` (K-factor system, expected score, doubles support) | Done | **Medium** - can reuse algorithm |
| `User` model (elo_rating, total_oprs, opr_level) | Done | **High** |
| `Event` + `EventCheckin` (QR check-in for points) | Done | **Medium** - tham khao flow QR |

### What Needs to Be Built

| Feature | Complexity | Priority |
|---------|-----------|----------|
| QR code generation per activity | Low | P1 |
| QR check-in flow (phone lookup, quick register) | Medium | P1 |
| Auto matchmaking by OPRS level | Medium-High | P1 |
| Real-time* waiting queue UI | Medium | P1 |
| Match lifecycle (waiting → playing → completed → back to queue) | Medium | P1 |
| Player result submission + confirmation | Low | P1 |
| OPRS training match integration | Medium | P2 |
| CLB member management (add by phone/email, create user) | Low-Medium | P2 |
| CLB internal leaderboard | Low | P2 |
| Admin activity config panel | Low | P3 |

*Real-time = polling/AJAX, khong can WebSocket phase dau.

---

## 3. Approach Evaluation

### Approach A: Extend Existing ClubActivity System (RECOMMENDED)

**Mo ta**: Mo rong `ClubActivity` them type `open_play`, extend `ClubMatchService` them OPRS-based matchmaking, them QR check-in flow.

**Pros**:
- Tan dung 80%+ code hien co (models, services, migrations, views)
- Club members/roles/RSVP/waitlist da san sang
- Match standing da co cau truc gần giong yeu cau
- Giam risk, giam thoi gian dev

**Cons**:
- ClubActivity fillable se dai them
- Can refactor ClubMatchService de ho tro OPRS matching (hien tai random)

### Approach B: Tao Module Rieng "ClubSession"

**Mo ta**: Tao he thong moi hoan toan voi models/tables rieng (club_sessions, club_session_matches, etc.)

**Pros**:
- Sach se, khong anh huong code cu

**Cons**:
- Duplicate nhieu logic (RSVP, waitlist, scoring, standings)
- Nhieu migration, model, controller moi => bloat
- Vi pham DRY

### Approach C: Hybrid - Extend Activity + New Matchmaking Engine

**Mo ta**: Dung ClubActivity lam base, tao `ClubMatchmakingService` rieng cho auto-pairing OPRS.

**Pros**:
- Balance giua reuse va separation of concerns
- Matchmaking logic phuc tap nen tach rieng la hop ly

**Cons**:
- Van can modify existing code

**Ket luan**: **Approach A + C (Hybrid)** la tot nhat. Extend ClubActivity system + tao ClubMatchmakingService rieng cho auto-pairing.

---

## 4. Database Design (Draft)

### Existing tables - MODIFY

```
club_activities
  + ADD: qr_code (varchar, nullable) -- unique QR identifier
  + ADD: courts_count (int, default 1) -- so san su dung
  + ADD: avg_match_duration (int, nullable) -- phut/tran
  + ADD: rotation_mode (enum: round_robin|oprs_based|random, default 'oprs_based')
  + ADD: gender_preference_enabled (bool, default false)
  + ADD: oprs_weight (decimal, default 0.5) -- he so OPRS cho tran tap luyen
  + ADD: started_at (datetime, nullable) -- thoi diem bat dau thuc te
  + ADD: ended_at (datetime, nullable) -- thoi diem ket thuc thuc te
  -- type enum: ADD 'open_play'

club_activity_participants
  + ADD: checked_in_at (datetime, nullable) -- thoi diem check-in QR
  + ADD: gender_preference (varchar, nullable) -- 'male_only', 'random', null
  + ADD: current_status (enum: idle|queued|playing|left, default 'idle')
  + ADD: queue_position (int, nullable) -- vi tri trong hang cho
  + ADD: matches_played_count (int, default 0)
  + ADD: last_match_ended_at (datetime, nullable)

club_activity_matches
  + ADD: match_number (int) -- so thu tu tran trong buoi choi
  + ADD: scheduled_court (int, nullable) -- san duoc xep
  + ADD: started_at (datetime, nullable)
  + ADD: ended_at (datetime, nullable)
  + ADD: result_submitted_by (int, nullable, FK users)
  + ADD: result_confirmed (bool, default false)
  + ADD: oprs_processed (bool, default false) -- da cap nhat OPRS chua
```

### New tables

```
club_member_stats (BXH noi bo CLB - aggregate)
  - id
  - club_id (FK clubs)
  - user_id (FK users)
  - total_matches (int, default 0)
  - total_wins (int, default 0)
  - total_losses (int, default 0)
  - total_points_scored (int, default 0)
  - total_points_against (int, default 0)
  - activities_participated (int, default 0)
  - current_oprs (decimal, nullable) -- snapshot
  - last_played_at (datetime, nullable)
  - timestamps
  - UNIQUE(club_id, user_id)
```

**Luu y**: Khong can tao bang rieng cho QR check-in. Dung `club_activity_participants.checked_in_at` la du.

---

## 5. OPRS Integration - De Xuat

### Hien tai
- OPRS = 0.7 * Elo + 0.2 * Challenge + 0.1 * Community
- EloService dung K-factor (40/24/16) cho OCR matches
- OprsService co `recalculateAfterMatch()` trigger tu EloService

### De xuat cho tran tap luyen CLB

**Phuong an**: Dung EloService voi **K-factor giam** cho tran tap luyen.

```
K_TRAINING = K_NORMAL * oprs_weight (config per activity)

Vi du:
- New player K=40, training K=40*0.5 = 20
- Intermediate K=24, training K=24*0.5 = 12
- Experienced K=16, training K=16*0.5 = 8
```

**Ly do**:
- Tran tap luyen van anh huong Elo nhung nhe hon 50%
- Admin CLB co the dieu chinh `oprs_weight` (0.0 = khong tinh, 1.0 = tinh nhu tran chinh thuc)
- Default 0.5 la hop ly cho buoi choi noi bo
- Van dung cung cong thuc Elo => nhat quan, khong can he thong scoring rieng

**Implementation**:
1. Them `recalculateAfterClubMatch()` vao OprsService
2. Them `processClubMatchElo()` vao EloService voi custom K-factor
3. Change reason: `OprsHistory::REASON_CLUB_MATCH`
4. Metadata luu: `club_activity_match_id`, `club_id`, `oprs_weight`

---

## 6. QR Check-in Flow - De Xuat

### Flow

```
QR Code URL: /clubs/{slug}/activities/{id}/checkin?token={qr_code}

1. Scan QR -> Mobile browser opens check-in page
2. Nhap so dien thoai
3. Backend lookup:
   a. Tim user by phone -> Ton tai + da la member CLB -> Check-in OK
   b. Tim user by phone -> Ton tai + chua la member -> Auto-add member + check-in
   c. Khong tim thay -> Form dang ky nhanh (ten, phone) -> Create user + add member + check-in
4. Set checked_in_at + current_status = 'queued'
5. Redirect to waiting queue page
```

**Reuse**: Tham khao `EventCheckin` (QR check-in da co trong Point system). Nhung CLB check-in phuc tap hon vi co waitlist + matchmaking.

**QR Generation**: Dung `simplesoftwareio/simple-qrcode` (Laravel package) hoac generate client-side. Moi activity co unique `qr_code` UUID.

---

## 7. Auto Matchmaking Algorithm - De Xuat

### ClubMatchmakingService

```
Input: List<QueuedPlayer> (sorted by queue_position)
Config: courts_count, rotation_mode, gender_preference_enabled

Algorithm (oprs_based mode):
1. Filter players: current_status = 'queued'
2. Sort by OPRS score
3. Group into 4-player pods (doubles) tuong duong OPRS:
   - Pair 1-2 vs 3-4 (closest OPRS)
   - Respect gender preference if enabled
4. Assign pods to available courts
5. If odd players: keep lowest queue_position ones in queue
6. Create ClubActivityMatch records
7. Update player status: queued -> playing
8. Notify via AJAX polling (player refresh page thay match assignment)

Special rules:
- Player vua ket thuc tran -> ve cuoi queue (fairness)
- Avoid rematch: khong ghep lai 2 doi giong nhau lien tiep
- Max wait time: uu tien player cho lau nhat
```

**Reuse**: `ClubMatchService.generateRotatingDoubles()` da co logic ghep doi. Extend them OPRS sorting layer.

---

## 8. Module Breakdown & Phases

### Phase 1: Core Activity Flow (P1) - ~60% effort
1. Extend ClubActivity model + migration (add fields)
2. QR code generation + check-in page (mobile-friendly)
3. Phone lookup + quick registration
4. Waiting queue UI (AJAX polling)
5. Auto matchmaking service (OPRS-based)
6. Match lifecycle management (status transitions)
7. Score submission + confirmation
8. Return to queue after match

### Phase 2: OPRS & Member Management (P2) - ~25% effort
9. OPRS training match integration (EloService + OprsService)
10. CLB member management (add by phone/email)
11. CLB internal leaderboard (club_member_stats)
12. Member stats aggregation service

### Phase 3: Admin Config & Polish (P3) - ~15% effort
13. Admin activity config panel (courts, duration, rotation rules)
14. Activity dashboard (live view: courts in use, matches, queue)
15. Activity history/report

---

## 9. Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|-----------|
| Matchmaking fairness complaints | Medium | Config cho admin dieu chinh, transparent queue position |
| Concurrent check-in race condition | High | Da co `lockForUpdate` pattern trong ClubActivityService |
| OPRS gaming (abuse training matches) | Medium | oprs_weight cap thap, rate limit matches/day |
| Mobile UX (check-in, queue) | Medium | Mobile-first responsive design, auto-refresh |
| Performance khi nhieu nguoi poll | Low | AJAX poll interval 10-15s, optimize queries |

---

## 10. Key Decisions Summary

| Decision | Choice | Reason |
|----------|--------|--------|
| Architecture | Extend existing ClubActivity | 80% code reuse, DRY |
| Matchmaking | New ClubMatchmakingService | Complex logic, tach concern |
| Real-time | AJAX polling | Khong co WebSocket setup, du cho phase 1 |
| OPRS training | K-factor * oprs_weight | Consistent voi EloService hien tai |
| QR check-in | Phone-based lookup | Simple, khong can login |
| DB design | Extend existing + 1 new table | Minimal migration footprint |
| UI | Admin web + Player mobile-responsive | 2 audiences |

---

## 11. Integration Points with Existing Code

```
Controllers to MODIFY:
  - ClubActivityController (add open_play support, QR endpoints)
  - ClubMatchController (add matchmaking trigger, result submission)

Controllers to CREATE:
  - ClubCheckinController (QR check-in flow, phone lookup)
  - ClubLeaderboardController (internal ranking)

Services to MODIFY:
  - ClubActivityService (extend RSVP for check-in flow)
  - ClubMatchService (integrate matchmaking)
  - OprsService (add recalculateAfterClubMatch)
  - EloService (add processClubMatchElo with custom K)

Services to CREATE:
  - ClubMatchmakingService (auto-pairing by OPRS)
  - ClubMemberStatsService (aggregate stats)

Models to MODIFY:
  - ClubActivity (new fields, new type)
  - ClubActivityParticipant (check-in, queue, status)
  - ClubActivityMatch (lifecycle fields)

Models to CREATE:
  - ClubMemberStat (CLB leaderboard aggregate)

Views to CREATE:
  - front/clubs/checkin.blade.php (mobile-friendly)
  - front/clubs/queue.blade.php (waiting queue)
  - front/clubs/match-assignment.blade.php
  - home-yard/clubs/activity-dashboard.blade.php (admin live view)
  - home-yard/clubs/members.blade.php (member management)
  - home-yard/clubs/leaderboard.blade.php
```

---

## 12. Resolved Decisions

| # | Question | Decision |
|---|----------|----------|
| 1 | Match format | **Ca singles + doubles** - Admin chon format khi tao activity |
| 2 | Score format | **Tung set** (11-7, 11-9) - chi tiet nhu thi dau chinh thuc |
| 3 | Xac nhan ket qua | **1 nguoi nhap la du** - nhanh, phu hop casual play |
| 4 | Guest player | **Admin config per activity** - admin chon cho phep guest hay chi member |
| 5 | Match timer | **Chi manual** - nguoi choi/admin bam ket thuc khi xong |
| 6 | OPRS weight default | **0.5 (50%)** - training K = 50% K chinh thuc |

### Gap Analysis Supplement

After comparing with original requirement, 2 gaps found and resolved:

**#10 - Member management**: Them vao `club_members` pivot table:
```
club_members (existing)
  + ADD: initial_oprs (decimal, nullable) -- OPRS ban dau admin set
  + ADD: notes (text, nullable) -- ghi chu noi bo
  + ADD: member_status (enum: active|inactive|suspended, default 'active')
```

**#12 - Leaderboard**: `club_member_stats` da du. `win_rate` tinh = wins/total_matches (computed attribute, khong can luu).

### Impact on DB Design

Score format = tung set -> can them field `set_scores` (JSON) vao `club_activity_matches`:
```
club_activity_matches
  + ADD: set_scores (JSON, nullable) -- e.g. [{"team1": 11, "team2": 7}, {"team1": 9, "team2": 11}]
```

Guest config -> them field vao `club_activities`:
```
club_activities
  + ADD: allow_guests (bool, default false) -- admin config per activity
```

Match format = ca singles + doubles -> `ClubActivityMatch.match_type` da co (singles/doubles). `ClubMatchmakingService` can support ca 2 mode.
