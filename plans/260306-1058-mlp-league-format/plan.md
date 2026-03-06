---
title: "MLP League Format - Doubles Pair Combination Sub-Games"
description: "Generate 6 sub-games per match using C(4,2) player pair combinations for MLP format"
status: complete
priority: P1
effort: 6h
branch: main
tags: [league, mlp, game-format]
created: 2026-03-06
---

# MLP League Format Enhancement

## Goal
When `competition_format = 'mlp'`, each match generates 6 sub-games based on all C(4,2)=6 doubles pair combinations from each team's 4 players. Admin scores each sub-game; match score = aggregate.

## Current State
- `competition_format` enum exists (`traditional`/`mlp`) but MLP has no special logic
- Schedule generation creates games from `config.match_format` array (e.g. `['MD','WD','MXD','MXD']`)
- `LeagueMatchGame` stores `game_number`, `game_type`, `home_score`, `away_score`, `winner_team_id`
- Score flow: `saveGameScore()` -> auto `determineMatchWinner()` when all games completed
- Players tracked in `league_team_players` with `gender` field
- Existing match score modal: simple home/away number inputs (match-level, no game-level UI)

## Phases

| # | Phase | Status | File |
|---|-------|--------|------|
| 1 | Database: add player pair columns to league_match_games | complete | [phase-01](phase-01-database.md) |
| 2 | Service: MLP game generation logic in LeagueScheduleService | complete | [phase-02](phase-02-service-logic.md) |
| 3 | UI: MLP sub-game score entry modal | complete | [phase-03](phase-03-ui-score-entry.md) |

## Key Design Decisions
- No new tables needed; add 4 nullable FK columns to `league_match_games` for player assignments
- MLP validation: require min 4 active players per team before schedule generation
- `game_type` for MLP sub-games: use `'DOUBLES'` (gender-agnostic pairing)
- Match winner = team with higher total score across all 6 games (NOT best-of)
- Admin (league creator) enters score per sub-game, system sums total
- Admin arranges player order (A1,A2,A3,A4) before generating schedule

## Validation Log

### Session 1 — 2026-03-06
**Trigger:** Initial plan validation before implementation
**Questions asked:** 6

#### Questions & Answers

1. **[Player Pick]** MLP yeu cau min 4 VDV/doi. Neu doi co >4 VDV, lay 4 nguoi nao de ghep cap?
   - Options: 4 nguoi dau theo ID | Admin tu chon 4 nguoi | Dung tat ca VDV
   - **Answer:** Dung tat ca VDV (nhieu to hop hon)
   - **Update:** Boss later confirmed fixed 4 VDV/doi, C(4,2)=6 games

2. **[Scoring]** Cach tinh diem tran MLP?
   - **Answer (from boss):** 6 tran, danh cham 5 (tuy giai), doi cap sau khi 1 ben cham 5, cong tong diem 6 tran de biet doi nao thang
   - **Update:** Khong can configurable max_score. Admin chi nhap ket qua tung cap.

3. **[Player Order]** Admin co can tuy chinh thu tu VDV?
   - Options: Khong can, tu dong | Admin sap xep thu tu
   - **Answer:** Admin sap xep thu tu

4. **[Results UI]** UI hien thi ket qua MLP match?
   - Options: Collapsible chi tiet | Chi hien tong diem | Luon hien chi tiet
   - **Answer:** Collapsible chi tiet

5. **[Format]** MLP chuan (4 game WD/MD/MXD/MXD) hay format rieng?
   - **Answer (from boss):** Cho 2 option: MLP chuan (2M+2F) hoac MLP vong tron (4 VDV bat ky, 6 luot)
   - **Update:** Boss later simplified to just 1 format: 4 VDV, 6 tran vong tron, tong diem

6. **[Pairing]** Cap doi A ghep vs cap doi B theo cung index hay cross?
   - Options: Cung index | Cross match
   - **Answer:** Cung index (A12 vs B12, A13 vs B13, ...)

#### Confirmed Decisions
- MLP = 4 VDV/doi, 6 games C(4,2), cung index pairing
- Scoring = tong diem 6 game, doi tong cao hon thang
- Admin nhap diem tung game, khong can max_score config
- Admin sap xep thu tu VDV trong doi
- UI = collapsible chi tiet 6 sub-game

#### Impact on Phases
- Phase 1: Unchanged (4 FK columns van can) + order column on league_team_players
- Phase 2: Update scoring logic - determineMatchWinner by total score (not game count). Add player ordering support.
- Phase 3: Score modal = 6 rows nhap diem, hien running total. Collapsible results display. Drag-drop player order UI in team detail.

### Session 2 — 2026-03-06
**Trigger:** Additional validation - scoring rules and UI clarification
**Questions asked:** 3

#### Questions & Answers

1. **[Tie Score]** Khi tong diem 2 doi bang nhau sau 6 game?
   - Options: Ghi nhan hoa | Doi thang nhieu game hon | Hoi boss
   - **Answer:** Other
   - **Custom input:** Luat se la cham 5 thi doi va cham diem toi da (vi du 30) truoc la thang
   - **Rationale:** Moi game danh cham 5 -> doi cap. Toan tran: doi cham tong diem toi da truoc thang. System chi ghi nhan diem admin nhap, khong enforce max. So tong de xac dinh winner.

2. **[Order UI]** Admin sap xep thu tu VDV nhu the nao?
   - Options: Drag-drop | Nhap so thu tu | Tu dong theo thu tu them
   - **Answer:** Drag-drop trong team detail
   - **Rationale:** Admin can UI truc quan de sap xep VDV (A1,A2,A3,A4) truoc khi generate schedule

3. **[Scope]** Tournament cung can MLP?
   - Options: Chi League | Ca League va Tournament
   - **Answer:** Chi League
   - **Rationale:** Tournament giu format hien tai (single/double/mixed), MLP chi ap dung cho League

#### Confirmed Decisions
- Scoring: admin nhap diem tung game, system sum total, doi tong cao hon thang
- Player order: drag-drop UI trong team detail page
- Scope: chi League, khong Tournament

#### Impact on Phases
- Phase 1: No additional changes
- Phase 2: No additional changes (system chi ghi diem, khong enforce max)
- Phase 3: Them drag-drop player order UI trong team detail view
