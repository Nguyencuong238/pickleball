# Browser Test Report: Club Activities ReClub-Style

**Date:** 2026-03-02
**Tester:** Claude (Chrome DevTools MCP)
**Feature:** Club Activities ReClub-Style Upgrade
**Plan:** `plans/260227-1440-club-activities-reclub-style/plan.md`
**URL:** `http://127.0.0.1:8000/clubs/clb-pickleball-quan-8/activities`
**Club:** CLB Pickleball Quan 8 (ID: 4, slug: `clb-pickleball-quan-8`)

---

## Summary

| Category | Total | Passed | Failed | Bugs Found |
|----------|-------|--------|--------|------------|
| Index Page | 4 | 4 | 0 | 0 |
| Create Activity | 6 | 6 | 0 | 0 |
| Edit Activity | 3 | 3 | 0 | 0 |
| Show Page + RSVP | 6 | 6 | 0 | 0 |
| Competition | 8 | 6 | 2 | 2 |
| Delete Activity | 2 | 2 | 0 | 0 |
| Edge Cases & Validation | 8 | 7 | 1 | 1 |
| **TOTAL** | **37** | **34** | **3** | **3** |

**Overall Result: 34/37 PASSED (92%)**

---

## Test 1: Activities Index Page - PASSED

| # | Test Case | Result | Notes |
|---|-----------|--------|-------|
| 1 | Page loads with activity list | PASS | Shows 4 activities |
| 2 | Type badges display correctly | PASS | "Buoi choi" (blue), "Giai dau" (orange) |
| 3 | Participant count shown | PASS | Format: "0/20 nguoi" |
| 4 | Edit/Delete buttons for management | PASS | Visible for admin user |

---

## Test 2: Create Activity (All 3 Types) - PASSED

| # | Test Case | Result | Notes |
|---|-----------|--------|-------|
| 1 | Type selector shows 3 cards | PASS | Buoi choi, Lich co dinh, Giai dau |
| 2 | Type cards toggle conditional fields | PASS | JS show/hide works correctly |
| 3 | Create one_off activity | PASS | "Test Buoi Choi One-Off" created |
| 4 | Create recurring activity | PASS | With recurrence_day=Thu 5, auto_approve, skill level 2.0-4.5 |
| 5 | Create competition activity | PASS | Round robin format, points 3/0 |
| 6 | Recurring fields show day selector + auto-approve | PASS | 7-day dropdown + checkbox |

---

## Test 3: Edit Activity - PASSED

| # | Test Case | Result | Notes |
|---|-----------|--------|-------|
| 1 | Edit form loads with existing values | PASS | All fields pre-populated |
| 2 | Type shown as read-only badge | PASS | Prevents type change after creation |
| 3 | Update saves successfully | PASS | Title updated, redirect to index with success msg |

---

## Test 4: Show Page + RSVP - PASSED

| # | Test Case | Result | Notes |
|---|-----------|--------|-------|
| 1 | Activity details displayed | PASS | Title, description, date, location, type badge |
| 2 | RSVP panel visible for all types | PASS | Shows for one_off, recurring, competition |
| 3 | RSVP join works | PASS | Count 0/12 -> 1/12, button changes to "Huy dang ky" |
| 4 | Participant list updates | PASS | Shows "DA XAC NHAN (1)" with "Admin User" |
| 5 | Cancel RSVP works | PASS | Count back to 0/12, button returns to "Dang ky tham gia" |
| 6 | Status badge displays | PASS | "Sap toi" badge shown |

---

## Test 5: Competition Features - 6/8 PASSED

| # | Test Case | Result | Notes |
|---|-----------|--------|-------|
| 1 | Competition panel shows on competition type | PASS | Both RSVP + Competition panels visible |
| 2 | Add team via AJAX | PASS | Team created in DB |
| 3 | **Team list visible after adding** | **FAIL** | **BUG #1** - see below |
| 4 | Duplicate team name validation | PASS | Alert: "Ten doi da ton tai" |
| 5 | Generate round robin schedule | PASS | 3 rounds, 6 matches for 4 teams |
| 6 | Matches displayed by round | PASS | "Vong 1", "Vong 2", "Vong 3" headers |
| 7 | Score entry and save | PASS | Doi A 11-5 Doi D, standings updated |
| 8 | **Standings update after score** | **FAIL** | **BUG #2** - partially related to #1 |

### BUG #1: Team list invisible before schedule generation (MEDIUM)

**Location:** `resources/views/clubs/activities/partials/_competition-scripts.blade.php:134-150`

**Description:** Team list (`#team-list`) is populated from match data, not from an independent teams API. Before schedule generation, the `GET /matches` endpoint returns empty array, so team list renders empty even though teams exist in DB.

**Root Cause:** Lines 134-150 build `teamMap` from `matches` response objects (`m.home_team`, `m.away_team`). No separate `GET /teams` endpoint is called.

**Impact:** After adding teams, user cannot see/verify them until schedule is generated. Remove team button is also unavailable.

**Fix:** Add a separate fetch to `GET /competition/teams` endpoint (needs new controller method) OR move team list rendering to a server-side Blade partial.

### BUG #2: Team list depends on matches (LOW)

Related to BUG #1. Once schedule is generated and matches exist, team list displays correctly. But if all matches are deleted (future feature), team list disappears again.

---

## Test 6: Delete Activity - PASSED

| # | Test Case | Result | Notes |
|---|-----------|--------|-------|
| 1 | Confirmation dialog shows | PASS | "Ban chac chan muon xoa hoat dong nay?" |
| 2 | Delete removes activity | PASS | Redirect to index with "Hoat dong duoc xoa thanh cong!" |

---

## Test 7: Edge Cases & Validation - 7/8 PASSED

| # | Test Case | Result | Notes |
|---|-----------|--------|-------|
| 1 | Submit empty form | PASS | HTML5 validation blocks (required fields) |
| 2 | Submit with title only, no max_participants | **FAIL** | **BUG #3** - SQL error exposed |
| 3 | Unauthenticated access redirects to login | PASS | 302 redirect for all protected routes |
| 4 | Non-member create page | PASS | 403 Forbidden |
| 5 | Non-member RSVP attempt | PASS | 403 "This action is unauthorized" |
| 6 | Non-member score save attempt | PASS | 403 Forbidden |
| 7 | Non-existent activity | PASS | 404 Not Found |
| 8 | Activity from wrong club | PASS | 404 Not Found |

### BUG #3: SQL error when max_participants is null (HIGH)

**Error:** `SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'max_participants' cannot be null`

**Location:** `ClubActivityController::store()` - validation allows `'max_participants' => 'nullable|integer|min:2|max:200'` but DB column is NOT NULL.

**Reproduction:**
1. Go to Create Activity
2. Fill title + date (required fields)
3. Leave "So nguoi toi da" empty
4. Submit

**Impact:** Unhandled SQL exception exposed to user with full stack trace (debug mode). In production, would show 500 error.

**Fix options:**
1. Add `default(0)` or `default(20)` to `max_participants` column in migration
2. Change validation to `'required|integer|min:2|max:200'`
3. Set default in controller: `$validated['max_participants'] = $validated['max_participants'] ?? 20;`

---

## Authorization Summary

| User Type | Index | Show | Create | Edit | Delete | RSVP | Score |
|-----------|-------|------|--------|------|--------|------|-------|
| Unauthenticated | 302 | 302 | 302 | 302 | 302 | 419 | 419 |
| Non-member | 200 | 200 | 403 | 403 | 403 | 403 | 403 |
| Management (admin) | 200 | 200 | 200 | 200 | 200 | 200 | 200 |

All authorization checks work correctly.

---

## Bugs Summary

| # | Severity | Title | Status |
|---|----------|-------|--------|
| 1 | MEDIUM | Team list invisible before schedule generation | Open |
| 2 | LOW | Team list depends on match data (related to #1) | Open |
| 3 | HIGH | SQL error when max_participants is null | Open |

---

## Recommendations

1. **[HIGH] Fix max_participants null handling** - Either make the field required in validation or provide a default value in the migration/controller.

2. **[MEDIUM] Add independent teams API endpoint** - Create `GET /competition/teams` endpoint in `ClubCompetitionController` and fetch teams separately from matches in `_competition-scripts.blade.php`.

3. **[LOW] Error handling in production** - Ensure `APP_DEBUG=false` in production to prevent SQL error stack traces from leaking to users.

4. **[LOW] Vietnamese diacritics** - Some user-facing text lacks Vietnamese diacritics (e.g., "Giai dau noi bo" should be "Giai dau noi bo" with proper diacritics). This is a data quality issue from test data, not a code bug.

---

## Test Environment

- **Browser:** Chrome 145.0.0.0 (macOS)
- **PHP:** 8.2.27
- **Laravel:** 10.50.0
- **Server:** `php artisan serve` at 127.0.0.1:8000
- **DB:** MySQL
- **Auth User:** admin@pickleball.com (User ID 1, management role in club 4)
- **Non-member User:** viet@gmail.com (User ID 2, not a member of club 4)
