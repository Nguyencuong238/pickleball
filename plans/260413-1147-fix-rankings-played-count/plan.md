# Fix: Rankings `matches_played` Over-Counting

**Created:** 2026-04-13 11:47
**Priority:** High
**Scope:** Bug fix + backfill
**Mode:** Fast

## Context

Tournament rankings page (`/tournament-manage/{slug}/rankings`) shows `P` (played) values exceeding the theoretical max for round-robin groups. Case observed on tournament id=237, group_id=34 (Bảng B, 5 pairs): 2 cặp có `matches_played = 5` while max phải là 4.

Evidence — tất cả 10 trận C(5,2) tồn tại, mỗi VĐV thực tế chỉ đấu 4 trận, nhưng standings row cho athlete 196 & 197 bị +1 dư. Nguyên nhân: match giữa họ (id 192) đã bị cập nhật tỉ số 2 lần.

## Root Cause

`GroupStanding::updateAfterMatch()` (`app/Models/GroupStanding.php:81`) luôn `increment('matches_played')` — **không idempotent**. Mọi entry point gọi `updateGroupStandingsWithSets()` đều cộng dồn mù quáng:

- `app/Services/Tournament/TournamentStandingService.php:18,53` (service)
- `app/Http/Controllers/Front/Tournament/Traits/MatchScoreTrait.php:82`
- `app/Http/Controllers/Front/RefereeController.php:268` (inline duplicate)
- `app/Http/Controllers/Api/RefereeController.php:322` (inline duplicate)
- `app/Http/Controllers/Front/HomeYardTournamentController.php:4298,4385` (inline duplicate)

Đồng thời `TournamentAthlete` stats (`updateTournamentAthleteStats`) và `GroupStanding.points/sets_won/sets_lost/games_won/games_lost` cũng bị inflate tương tự.

Secondary: code duplicate nhiều nơi (KISS/DRY vi phạm).

## Solution Strategy

**Approach:** Thay incremental update bằng **full recompute from source** (matches table). Idempotent, self-healing, fix root cause một lần cho tất cả entry points.

1. Thêm `recalculateGroupStandings(int $groupId)` vào `TournamentStandingService` — rebuild toàn bộ standings row của group từ matches `completed` + `set_scores`.
2. Thêm `recalculateTournamentAthleteStats(int $athleteId)` — rebuild athlete-level stats từ tất cả matches `completed` liên quan.
3. Các method `updateGroupStandingsWithSets`/`updateGroupStandings`/`updateTournamentAthleteStats` gọi thẳng sang recompute (wrapper để giữ API backward compat cho các call site hiện tại).
4. Xóa các bản inline duplicate trong `Front/RefereeController`, `Api/RefereeController`, `HomeYardTournamentController` — thay bằng inject `TournamentStandingService`.
5. Backfill script (Artisan command) quét tất cả groups, chạy recompute → fix dữ liệu hỏng hiện có (tournament 237 + mọi giải khác nếu có).
6. Add feature test covering double-submit scenario.

**Trade-off:** Full recompute mỗi lần update tỉ số tốn thêm vài query per group, nhưng round-robin group thường ≤ 20 matches → negligible. Lợi: hết bug, hết duplicate code, self-healing nếu data lệch.

## Phases

| # | Phase | Status |
|---|-------|--------|
| 1 | [Add recompute methods to service](phase-01-service-recompute.md) | complete |
| 2 | [Refactor entry points + remove duplicates](phase-02-refactor-entry-points.md) | complete |
| 3 | [Backfill command + run on tournament 237](phase-03-backfill-command.md) | complete |
| 4 | [Tests + manual verification](phase-04-tests-verify.md) | complete |

## Result

- Service `recalculateGroupStandings` / `recalculateTournamentAthleteStats` idempotent (replay from `matches` table).
- Xoá ~700 dòng inline duplicate trong `Front/RefereeController`, `Api/RefereeController`, `HomeYardTournamentController` → delegate về service.
- Command `php artisan tournament:recalculate-standings` (hỗ trợ `--tournament`, `--group`, `--dry-run`).
- Backfill tournament 237: group 34 athlete 196 P 5→4, athlete 197 P 5→4 pts 6→3. Re-run = no-op.
- Feature test `tests/Feature/Tournament/RankingsIdempotencyTest.php` — 4 test cases, all green.
- Thêm `.env.testing` (MySQL `pickleball_test`) vì `.env` trước đó trỏ vào DB chính → `RefreshDatabase` đã từng wipe production data; giờ cách ly.

## Key Files

**Modify:**
- `app/Services/Tournament/TournamentStandingService.php`
- `app/Models/GroupStanding.php` (có thể deprecate `updateAfterMatch`)
- `app/Http/Controllers/Front/Tournament/Traits/MatchScoreTrait.php`
- `app/Http/Controllers/Front/RefereeController.php`
- `app/Http/Controllers/Api/RefereeController.php`
- `app/Http/Controllers/Front/HomeYardTournamentController.php`

**Create:**
- `app/Console/Commands/RecalculateTournamentStandings.php`
- `tests/Feature/Tournament/RankingsIdempotencyTest.php`

## Success Criteria

- Sửa tỉ số một trận nhiều lần → `matches_played` không vượt số trận thực tế.
- Tournament 237 Bảng B sau backfill: tất cả VĐV `matches_played = 4`.
- Không còn inline duplicate của `updateGroupStandingsWithSets` ngoài service.
- Feature test pass: double-submit cùng match không double-count.

## Risks

- Recompute sai nếu `set_scores` JSON có shape khác nhau giữa các trận cũ — cần handle cả `athlete1_score`/`athlete1` key variants (đã thấy trong `TournamentRankingController:105`).
- Backfill có thể ảnh hưởng giải đang diễn ra — chạy trong transaction, log before/after.
- `GroupStanding.is_advanced` + `advancing_count` phải được re-apply sau recompute (đã có sẵn trong `recalculateGroupRankings`).

## Known Anomalies (observed on group 34)

DB dump cho group 34:
```
athlete  played won  lost drawn games_w sets_w sets_l points
196      5      4    0    0     0       4      1      12
197      5      2    0    3(?)  0       2      3      6
```
- `matches_played = 5` nhưng `won+lost+drawn = 4` → inconsistent. Có code path tăng `matches_played` mà không tăng won/lost/drawn. Có thể do 1 lần update với set_scores rỗng (setsWon1=setsWon2=0 → rơi vào branch draw, cộng matches_drawn). Recompute sẽ tự fix.
- `games_won`/`games_lost` luôn = 0 trong DB dù tie-break code dùng chúng. **Preserve này trong phase 1** để tránh ngầm đổi thứ tự hiện tại.

## Open Questions

- Có giữ `GroupStanding::updateAfterMatch()` cho backward compat không? → Đề xuất: xóa hẳn sau phase 2 vì không còn caller.
- Tie-break hiện `games_won - games_lost` = 0 luôn → thực tế chỉ tie-break bằng points + matches_won. Có nên fix sang `sets_differential` không? → Out of scope, plan riêng nếu cần.
- `TournamentAthlete.matches_played` có được hiển thị ngoài rankings page không (profile, leaderboard)? Nếu có, inflated data đã lan sang user-facing views khác — backfill ở phase 3 đã cover.
