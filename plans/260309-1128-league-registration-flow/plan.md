---
title: "League Registration Flow"
description: "Public VDV registration, admin approval, and pool-based team assignment for leagues"
status: pending
priority: P1
effort: 10h
branch: main
tags: [league, registration, frontend, backend]
created: 2026-03-09
---

# League Registration Flow

## Summary
Enable VDV self-registration via public link, admin approval workflow, and pool-based player assignment to teams.

## Phases

| # | Phase | Status | Effort | Files |
|---|-------|--------|--------|-------|
| 1 | Database & Models | pending | 2h | migrations, models |
| 2 | Backend - Registration & Approval | pending | 3h | controllers, service, routes |
| 3 | Frontend - Public Registration Page | pending | 2.5h | blade views, JS |
| 4 | Frontend - Admin Approval Tab | pending | 1.5h | blade partial, show.blade |
| 5 | Frontend - Enhanced "Them VDV" Modal | pending | 1h | _tab-teams.blade.php |

## Key Dependencies
- Existing: League, LeagueTeam, LeagueTeamPlayer models
- Existing: LeagueService, LeagueTeamController
- Existing: OcrController::searchUsers (user search)
- Existing: File upload pattern (`->store('path', 'public')`)

## Architecture
```
Public Form --> LeagueRegistrationController::store()
  --> LeagueRegistrationService::register()
    --> match/create users by phone
    --> store registration + players + payment proof

Admin Tab --> LeagueRegistrationController::approve/reject()
  --> update status, admin_note

Modal "Them VDV" --> 2 tabs:
  Tab 1: fetch approved pool --> add group or individual
  Tab 2: existing user search (unchanged)
```

## Phase Details
- [Phase 1: Database & Models](./phase-01-database-models.md)
- [Phase 2: Backend Logic](./phase-02-backend-logic.md)
- [Phase 3: Public Registration Page](./phase-03-public-registration.md)
- [Phase 4: Admin Approval Tab](./phase-04-admin-approval-tab.md)
- [Phase 5: Enhanced Player Modal](./phase-05-enhanced-player-modal.md)

## Validation Log

### Session 1 — 2026-03-09
**Trigger:** Initial plan validation before implementation
**Questions asked:** 4

#### Questions & Answers

1. **[Architecture]** Phone format: VDV co the nhap '0901234567' hoac '+84901234567'. Cach xu ly phone matching?
   - Options: Normalize to 0xxx | Normalize to +84xxx | Store as-is fuzzy match
   - **Answer:** Normalize to 0xxx
   - **Rationale:** Simplest approach. Strip +84/84 prefix, store as 0xxx. Match user by normalized phone.

2. **[Architecture]** Auto-create user khi VDV chua co tai khoan: user moi can gi ngoai phone + name?
   - Options: Phone + name only | Phone + name + email | Phone + name + auto-login link
   - **Answer:** Phone + name only
   - **Rationale:** Keep simple. Random password, user can reset later. No email required in registration form.

3. **[Scope]** Khi admin reject 1 nhom dang ky, VDV co duoc dang ky lai khong?
   - Options: Cho dang ky lai | Khong cho | Admin unlock
   - **Answer:** Cho dang ky lai
   - **Rationale:** No blocking on rejected registrations. VDV can re-submit with same phone.

4. **[Architecture]** Skill level (diem trinh) va Province (tinh thanh) - free text hay dropdown?
   - Options: Free text ca hai | Dropdown ca hai | Mixed
   - **Answer:** Free text ca hai
   - **Rationale:** KISS - no need to maintain dropdown lists. VDV self-input.

#### Confirmed Decisions
- Phone normalization: strip to 0xxx format before matching/storing
- User creation: phone + name + random password only
- Re-registration: allowed after rejection (no unique phone constraint per league for rejected)
- Form fields: all free text inputs

#### Action Items
- [ ] Add phone normalization helper in LeagueRegistrationService
- [ ] Unique phone constraint: only for pending+approved registrations (not rejected)
- [ ] No email field in registration form

#### Impact on Phases
- Phase 2: Add normalizePhone() helper. Unique phone validation = WHERE status IN (pending, approved) AND league_id = X
- Phase 3: Remove email from form. Skill_level and province are free text inputs (no dropdowns)
