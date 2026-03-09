# Auto Team Generation for League

## Overview
Add "Auto Generate Teams" feature alongside existing manual team creation. Two pairing modes:
1. **Skill-ranked**: Pair high-skill with low-skill players (A mix B)
2. **Random**: Shuffle all players randomly into teams

## Status: Implemented

## Phases

| # | Phase | Status | Effort |
|---|-------|--------|--------|
| 1 | Backend - Service method | Complete | Small |
| 2 | Frontend - UI button + modal | Complete | Medium |
| 3 | Backend - Controller endpoint | Complete | Small |

## Key Decisions
- Source: Approved registration players NOT yet assigned to teams (existing pool)
- Teams auto-named: "Doi 1", "Doi 2", etc.
- **Team size: Admin nhap trong modal**, mac dinh Traditional=2, MLP=4
- Skill-ranked mode: Sort by skill_level DESC, pair #1 with #N, #2 with #N-1, etc.
- Random mode: Shuffle then chunk into pairs
- Null skill_level = 0 (xep cuoi, ghep voi nguoi trinh cao)
- Giu nguyen doi cu, chi tao doi moi tu pool
- Khong can balance gioi tinh
- No new DB migration needed - uses existing tables

## Files to Modify
- `app/Services/LeagueService.php` - Add `autoGenerateTeams()` method
- `app/Http/Controllers/Front/LeagueTeamController.php` - Add `autoGenerate()` endpoint
- `resources/views/home-yard/leagues/_tab-teams.blade.php` - Add button + modal
- `routes/web.php` - Add route

## Dependencies
- Approved registrations with unassigned players must exist
- League must be in draft/registration status

## Validation Log

### Session 1 — 2026-03-09
**Trigger:** Initial plan validation before implementation
**Questions asked:** 4

#### Questions & Answers

1. **[Scope]** Khi auto-generate, neu league da co doi (tao thu cong truoc do), nen xu ly the nao?
   - Options: Giu nguyen doi cu | Xoa het doi cu, tao lai tu dau | Hoi confirm truoc khi chay
   - **Answer:** Giu nguyen doi cu
   - **Rationale:** Chi tao doi moi tu VDV chua xep doi. Doi da co khong bi anh huong. An toan, khong mat du lieu.

2. **[Architecture]** So VDV moi doi lay tu dau?
   - Options: Dung required_players_per_registration | Cho user nhap rieng
   - **Answer:** Dung required_players_per_registration
   - **Rationale:** Don gian, nhat quan voi cau truc dang ky. Khong can them input.

3. **[Assumptions]** Voi mode Phan hang theo trinh, VDV khong co skill_level thi xep the nao?
   - Options: Xep cuoi (skill = 0) | Xep giua (trung binh) | Loai khoi auto-generate
   - **Answer:** Xep cuoi (skill = 0)
   - **Rationale:** VDV null skill coi nhu trinh thap nhat, se duoc ghep voi nguoi trinh cao.

4. **[Scope]** Co can tinh den gioi tinh khi xep doi khong?
   - Options: Khong can, xep theo skill/random | Bat buoc can bang gioi tinh
   - **Answer:** Khong can, xep theo skill/random thoi
   - **Rationale:** Don gian. Admin tu dieu chinh sau neu can.

#### Confirmed Decisions
- Existing teams: Keep as-is, only create new teams from unassigned pool
- Team size: Use `required_players_per_registration` config value
- Null skill_level: Treat as 0 (lowest)
- Gender balance: Not required, simple skill/random pairing

#### Action Items
- None - plan already aligned with all confirmed decisions

#### Impact on Phases
- No phase changes required - plan already matches all validated decisions
