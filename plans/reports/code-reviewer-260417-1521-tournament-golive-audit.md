# Tournament Go-Live Audit Report
**Date:** 2026-04-17 | **Deploy target:** 2026-04-18
**Scope:** Tournament management + display module

---

## Scope Summary
- Files reviewed: 28 PHP files (controllers, services, models, traits), 5+ blade view files, routes, migrations
- LOC: ~8,000+ across module
- Recent commits audited: 1fff903, bc7a32e, 7181b7f, b4f1eea, c27627c

---

## BLOCKER Issues (must fix truoc live)

### B1 - APP_DEBUG=true trong .env
**File:** `.env:4`
**Issue:** `APP_DEBUG=true` — tren production, loi exception se leak full stack trace + env vars ra browser. An ninh nghiem trong.
**Impact:** Attacker co the doc duong dan file, bien moi truong, cau hinh DB tu stack trace.
**Fix:** Set `APP_DEBUG=false` trong .env production truoc khi deploy. Dam bao LOG_LEVEL phu hop (error/warning).

### B2 - Race condition: public registration khong dung lockForUpdate
**File:** `app/Http/Controllers/Front/TournamentRegistrationController.php:101-144`
**Issue:** Slot-check (`availableSlots < requiredSlots`, `currentCount > max_participants`) duoc thuc hien TRUOC `DB::transaction`, khong co `lockForUpdate`. Ket qua: 2 request dong thoi co the doc cung mot slot available va ca hai deu pass check, ghi vuot qua gioi han.
```php
// Hien tai: check o ngoai transaction → race condition
$availableSlots = $category->max_participants - $category->current_participants;
if ($availableSlots < $requiredSlots) { ... }
// ... roi moi vao DB::transaction(function() { ... })
```
**Impact:** Tournament co the bi dang ky vuot max_participants, dac biet trong su kien lon (nhieu nguoi dang ky cung luc).
**Fix:**
```php
DB::transaction(function () use (...) {
    $category = TournamentCategory::lockForUpdate()->find($category->id);
    if ($category->current_participants + $requiredSlots > $category->max_participants) {
        throw new \Exception('Category full');
    }
    // create athlete here
    $category->increment('current_participants', $requiredSlots);
});
```

### B3 - SQL Injection via sort/direction params trong API
**File:** `app/Http/Controllers/Api/TournamentController.php:43-45`
**Issue:** `$sort` va `$direction` lay truc tiep tu request va truyen vao `orderBy()` ma khong co whitelist validation.
```php
$sort = $request->get('sort', 'start_date');   // user-controlled
$direction = $request->get('direction', 'desc'); // user-controlled
$query->orderBy($sort, $direction);              // potential injection
```
**Impact:** Attacker co the truyen ten column bat ky hoac raw SQL fragment vao `orderBy`. Eloquent co xu ly mot phan nhung khong phai tat ca truong hop.
**Fix:**
```php
$allowedSorts = ['start_date', 'end_date', 'name', 'created_at'];
$allowedDirections = ['asc', 'desc'];
$sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'start_date';
$direction = in_array($request->get('direction'), $allowedDirections) ? $request->get('direction') : 'desc';
```

### B4 - Legacy stale standings CHUA duoc repair tren production
**File:** `app/Console/Commands/RepairTournamentStandings.php` (commit 1fff903)
**Issue:** Commit nay them artisan command `tournament:repair-standings` de heal data stale tu bug truoc day. Neu command nay CHUA duoc chay tren production data, thi cac tournament cu van co group_standings sai → bieu dien bang xep hang sai.
**Impact:** Nguoi dung thay sai thu hang trong tournament da dien ra. Cuc ky xau cho UX khi go live.
**Action Required:** Team phai xac nhan: da chay `php artisan tournament:repair-standings` tren production chua? Neu chua → chay TRUOC khi deploy.
```bash
# First dry-run to assess impact
php artisan tournament:repair-standings --dry-run

# If output shows stale/missing → run for real
php artisan tournament:repair-standings
```

---

## HIGH Priority Issues

### H1 - Registration public endpoint khong co rate limiting / throttle
**File:** `routes/web.php:225`
**Issue:** `POST /tournament/{tournament}/register` khong co middleware throttle. Endpoint nay public (khong can auth).
```php
Route::post('/tournament/{tournament}/register', [TournamentRegistrationController::class, 'register'])
    ->name('tournament.register');
// Thieu: ->middleware('throttle:10,1')
```
**Impact:** Bot co the spam dang ky tournament voi email gia, lam day chot so. Cung anh huong B2 (race condition).
**Fix:** Them `->middleware('throttle:10,1')` vao route. 10 req/min la hop ly cho registration.

### H2 - Category validation trong public registration khong check tournament ownership
**File:** `app/Http/Controllers/Front/TournamentRegistrationController.php:71`
**Issue:** Rule validation la `'category_id' => 'required|exists:tournament_categories,id'` — chi kiem tra ton tai trong bang, KHONG kiem tra category thuoc tournament. Tuy nhien code sau do co check `$tournament->categories()->find($request->category_id)` (line 56) — nhung check nay duoc thuc hien TRUOC validation, va dung `$request->category_id` (chua validate).
**Impact:** Thap — vi check tournament ownership da co o line 56, nhung thu tu logic lac (check truoc validate, val rule khong rang buoc). Neu check 56 bi xoa bot nao cung mat bao ve.
**Fix:** Sua validation rule:
```php
'category_id' => ['required', Rule::exists('tournament_categories', 'id')->where('tournament_id', $tournament->id)],
```
Va xoa check thu cong o line 56 (thua sau khi fix validation).

### H3 - HomeYardTournamentController qua lon, tham chieu kep code (legacy draw vs new)
**File:** `app/Http/Controllers/Front/HomeYardTournamentController.php` (5,243 lines)
**Issue:** Controller nay ton tai song song voi `tournament-manage` route group moi (dung Tournament namespace controllers rieng biet). Co 2 he thong draw: `homeyard/tournaments/{id}/draw` (HomeYardTournamentController) va `tournament-manage/{tournament}/draw` (TournamentDrawController). Chua ro frontend dang su dung endpoint nao.
**Impact:** Bug fix chi ap dung 1 noi (vi du bc7a32e fix group_standings sync trong DrawService nhung HomeYardTournamentController co the co logic draw rieng).
**Verify:** Kiem tra xem view files cua homeyard dang goi endpoint nao, dam bao khong dung legacy draw endpoint da outdate.

### H4 - Standings update o ngoai transaction chính (non-atomic score update)
**File:** `app/Http/Controllers/Front/Tournament/Traits/MatchScoreTrait.php:80-87`
**Issue:** Score duoc save trong `DB::transaction` (line 35-78), nhung `standingService->updateGroupStandingsWithSets()` va `updateTournamentAthleteStats()` duoc goi NGOAI transaction (lines 81-87) voi try/catch boc ngoai chi log warning.
```php
// Score luu trong transaction
DB::transaction(function() use (...) { $match->save(); });

// standings update NGOAI transaction — neu fail, score da luu nhung standings sai
if ($match->group_id && $match->status === 'completed') {
    try {
        $standingService->updateGroupStandingsWithSets(...); // <-- ngoai transaction
    } catch (\Exception $e) {
        Log::warning('...');  // silent failure!
    }
}
```
**Impact:** Neu standings update throw exception, score van duoc luu nhung standings sai ma khong bao loi ro rang → data inconsistency. Tuy nhien `recalculateGroupStandings` la idempotent nen co the repair sau.
**Fix:** Goi standings update trong cung transaction, hoac it nhat re-throw exception de loi co the phoi ra.

### H5 - Tournament model dung `$guarded = []` (unguarded mass assignment)
**File:** `app/Models/Tournament.php:15`
**Issue:** `protected $guarded = []` co nghia la TAT CA columns deu co the mass-assign. Admin controller truyen tat ca fields truc tiep tu `$request->only([...])` — dieu nay OK. Nhung neu co bug hay thiet sot nao trong future, mass assignment se la diem yeu.
**Impact:** Thap truc tiep (controllers dang filter request.only()), nhung la code smell nguy hiem cho long-term maintenance.
**Fix:** Chuyen sang `$fillable = [...]` ro rang nhu TournamentAthlete model da lam.

---

## MEDIUM Priority Issues

### M1 - Duplicate slug logic (2 noi, logic khac nhau)
**File:** `app/Http/Controllers/Admin/TournamentController.php:143-149` vs `app/Services/Tournament/TournamentCrudService.php`
**Issue:** Admin controller tao slug va save sau `Tournament::create()` trong 2 buoc rieng (co the fail giua). Front controller dung `TournamentCrudService::generateSlug()` trong 1 buoc atomic. Race condition nhỏ nếu 2 tournament tao cung ten cung luc.

### M2 - N+1 trong getCategories (registration)
**File:** `app/Http/Controllers/Front/TournamentRegistrationController.php:27-36`
**Issue:** Vong lap `$categories->map()` goi `$category->athletes()->count()` cho moi category → N+1 query.
**Fix:**
```php
$categories = $tournament->categories()
    ->withCount('athletes')
    ->orderBy('category_name')
    ->get()
    ->map(function ($category) {
        return [
            ...
            'current_participants' => $category->athletes_count,
        ];
    });
```

### M3 - Bracket score entry cache version co the gay stale cache
**File:** `resources/views/home-yard/tournaments/bracket.blade.php:20-21`
**Issue:** `bracket-score-entry.js?v=1.0` va `bracket-swap-editor.js?v=1.2` dung version string cung. Neu file JS duoc update ma version khong tang, browser van dung cache cu. Commit c27627c da fix dieu nay nhung phuong phap thay the tot hon la dung Laravel Mix versioning (`{{ mix('...') }}`).
**Impact:** Users bị stale cache sau deploy mà không biết.

### M4 - Unguarded storeCategoryFormats trong HomeYardTournamentController
**File:** `app/Http/Controllers/Front/HomeYardTournamentController.php:5054-5088`
**Issue:** `storeCategoryFormats()` chi ho tro 3 format (single/double/mixed) nhung map chung vao chi 3 enum value (single_men, double_men, double_mixed). Cac loai nhu single_women, double_women khong the tao qua luong nay. Phai dung front tournament-manage flow moi de tao cac loai khac.
**Impact:** HomeYard users (luong cu) bi gioi han o 3 loai. Kho nhan ra vi form chi show 3 checkbox.

### M5 - `registration_deadline` check: null se bo qua check deadline
**File:** `app/Http/Controllers/Front/TournamentRegistrationController.php:84`
**Issue:**
```php
if ($tournament->registration_deadline <= now()) { // fails if null → returns false → skip check!
```
Neu `registration_deadline` la null, check nay return false (null <= now()) → registration van duoc phep bat ky luc nao, kể cả sau khi tournament da xong.
**Impact:** Tournament khong set deadline se cho phep dang ky mai mai.
**Fix:**
```php
if ($tournament->registration_deadline && $tournament->registration_deadline <= now()) { ... }
```
Hoac them logic "if deadline null → registration open" vs "deadline null → block" theo business logic.

*(Note: API TournamentController@register line 157 da co check null dung: `$tournament->registration_deadline && ...`. Front controller thieu check nay.)*

---

## LOW Priority Issues

### L1 - `matches_fix.blade.php` ton tai nhung khong duoc reference
**File:** `resources/views/home-yard/tournaments/matches_fix.blade.php`
**Issue:** File blade nay ton tai nhung khong co route/include nao reference. Co the la dead code tu bug fix cu.
**Action:** Xoa file de tranh confusion.

### L2 - Admin update validation khong nhat quan voi store
**File:** `app/Http/Controllers/Admin/TournamentController.php:197`
**Issue:** Store validate `prizes` la `nullable|numeric|min:0`, nhung update validate `prizes` la `nullable|numeric|min:0` — OK. Nhung `status` bi thieu trong update rules du no duoc include trong `$request->only()`. Minor logic gap.

### L3 - API TournamentController@standings dung `rank` column khong ton tai
**File:** `app/Http/Controllers/Api/TournamentController.php:87-90`
**Issue:** `$tournament->athletes()->orderBy('rank', 'asc')` — column `rank` khong ton tai trong `tournament_athletes` table (chi co `rank_position` trong group_standings). Query nay se fail hoac sort sai.
**Impact:** API endpoint `/api/tournaments/{id}/standings` tra ve data sai thu tu hoac loi DB.
**Fix:** Sua thanh `orderBy('position', 'asc')` hoac bo sung join voi group_standings.

### L4 - `storeCategoryFormats` unused `$categoryIds` variable
**File:** `app/Http/Controllers/Front/HomeYardTournamentController.php:5064, 5086`
**Issue:** Variable `$categoryIds` duoc khai bao va push vao nhung khong bao gio duoc dung sau do.

### L5 - `_bracket-tree.blade.php` load AlpineJS tu CDN khong co SRI
**File:** `resources/views/home-yard/tournaments/bracket.blade.php:17`
**Issue:** `https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js` — dung floating version `@3.x.x` va khong co `integrity` attribute (SRI). CDN compromise → XSS.
**Fix:** Pin version cu the + them SRI hash: `<script src="...@3.14.1/..." integrity="sha384-..." crossorigin="anonymous">`.

---

## Edge Cases Found

### EC1 - Double draw khi partner khong ton tai trong category
**File:** `app/Services/Tournament/TournamentDrawService.php:33-59`
**Issue:** `getPairsFromAthletes()` bo qua athlete khong co partner (log warning), nhung khong return loi ro rang. Neu tai tournament co 5 athlete doubles dang ky nhung chi 4 nguoi co cap → 1 nguoi bi bo qua im lang, draw tiep tuc voi 4/5 nguoi.
**Impact:** BTC co the khong biet co nguoi bi miss.

### EC2 - Manual draw validate `athlete_id` khong verify thuoc tournament
**File:** `app/Http/Controllers/Front/Tournament/TournamentManualDrawController.php:42-48`
**Issue:** Validation chi kiem tra `'assignments.*.*.athlete_id' => 'required|integer'`, khong verify athlete_id thuoc tournament hien tai. Ai do co the craft request voi athlete_id tu tournament khac.
**Impact:** BTC co the (ngo y hoac co y) assign athlete tu tournament khac vao group. Data corruption nhe.

### EC3 - Score update voi draw match (no athletes) tao winner_id = null nhung status = completed
**File:** `app/Http/Controllers/Front/Tournament/Traits/MatchScoreTrait.php:55-64`
**Issue:** Neu ca 2 sets deu hoa (setsWon1 == setsWon2), match duoc mark `completed` nhung `winner_id = null`. Trong bracket logic, `handleBracketAdvancement` kiem tra `winner_id` truoc khi advance → khong advance duoc. Tran hoa trong knockout se bi stuck.
**Impact:** Neu BTC nhap ti so hoa trong knockout bracket → tran tiep theo se khong co nguoi thi dau, tournament bi stuck.

### EC4 - `recalculateGroupStandings` goi ca trong transaction cua chinh no
**File:** `app/Services/Tournament/TournamentStandingService.php:24-27, 66`
**Issue:** `recalculateGroupStandings()` wrap trong `DB::transaction()` (line 27), nhung no duoc goi tu `applyMatchDeltas()` va cung duoc goi tu `recalculateGroupRankings()` o line 66 — thuoc cung outer transaction. Nested transactions trong MySQL dung savepoints, thong thuong OK nhung cần lưu ý neu dung connection pool.

---

## Security Summary

| Area | Status | Notes |
|------|--------|-------|
| Authorization (CRUD) | OK | TournamentPolicy dang ky, authorizeOwner dung nhat quan |
| CSRF | OK | Laravel CSRF middleware active tren tat ca POST routes |
| File upload (Excel import) | OK | mime validation xlsx/xls + max:2048 |
| XSS in views | OK | Tat ca output dung `{{ }}`, chi 1 noi dung `{!! nl2br(e(...)) !!}` an toan |
| Mass assignment (Tournament model) | MEDIUM | $guarded=[] (H5) |
| SQL injection | BLOCKER | API sort param (B3) |
| Rate limiting on registration | HIGH | Thieu throttle (H1) |
| Auth on management routes | OK | middleware auth tren tat ca management routes |
| Input validation | MOSTLY OK | Vài chỗ thiếu (H2, EC2) |

---

## Recent Commits Verification

| Commit | Fix | Status |
|--------|-----|--------|
| bc7a32e | Sync group_standings on draw/manual-draw | VERIFIED OK - resetDraw xoa standings truoc, manual draw xoa va tao lai |
| 1fff903 | Artisan repair legacy stale standings | VERIFY NEEDED - command ton tai nhung chua ro da chay tren prod chua (B4) |
| 7181b7f | Facebook share button | VERIFIED OK - share button mo Facebook dialog |
| c27627c | Bracket score entry versioning | PARTIAL - v=1.0 string, but nên dùng Mix versioning (L3) |
| b4f1eea | Docs update | N/A - docs only |

---

## Go/No-Go Recommendation

**NO-GO** cho deploy ngay mai cho den khi giai quyet 3 issue sau:

1. **B1 (BLOCKER)** — Set `APP_DEBUG=false` trong .env production (1 phut fix)
2. **B3 (BLOCKER)** — Whitelist sort/direction params trong `Api\TournamentController` (5 phut fix)
3. **B4 (ACTION)** — Xac nhan + chay `php artisan tournament:repair-standings` tren production truoc deploy (neu chua chay)

Sau khi fix 3 diem tren thi co the **GO** voi dieu kien:
- Monitor loi sau deploy cho H4 (standings update failure)
- Theo doi dang ky vuot han (B2 - race condition) — co the dung cron kiem tra sau
- Plan sprint tiep theo de fix H1 (throttle) va H2 (validation)

---

## Positive Observations

- Idempotent standing recalculation (recalculateGroupStandings) la thiet ke tot, tranh over-count bug.
- Draw reset logic trong ManualDrawPersistenceHelper xoa standings truoc khi draw lai — fix dung root-cause cua bc7a32e.
- Tat ca management endpoints dung `abort_unless(tournament->user_id === auth()->id(), 403)` nhat quan.
- DB::transaction duoc dung dung o phan lon cac write operations (draw, match creation, athlete CRUD).
- File upload validation cho Excel import day du (mime + size).
- Toan bo output trong views dung `{{ }}` (escaped), khong co unescaped XSS risk.
- TournamentPolicy duoc dang ky va su dung dung cho Admin routes.
- Tat ca migrations da chay (no pending migrations).
- Group standings co unique constraint (group_id, athlete_id) bao ve data integrity.

---

## Unresolved Questions

1. **Artisan repair command** (B4): Team co the xac nhan `tournament:repair-standings` da chay tren production cho cac tournament cu chua? Co bao nhieu tournament/group bi anh huong?
2. **Dual draw system**: `homeyard/tournaments/{id}/draw` (HomeYardTournamentController legacy) vs `tournament-manage/{tournament}/draw` (TournamentDrawController moi) — front-end dang goi endpoint nao? Hai he thong co dung cung `TournamentDrawService` hay co code rieng?
3. **category_type granularity**: `storeCategoryFormats()` trong HomeYardTournamentController chi support 3 loai (single/double/mixed → single_men/double_men/double_mixed). Neu BTC can single_women hay double_women, ho phai dung luong tournament-manage moi? Day co phai la intentional design khong?
4. **Auto bracket generation**: `auto_bracket_generation` flag tren Tournament — co tournament nao hien tai dang bat flag nay khong? Neu co, tu dong tao bracket khi tat ca vong bang xong co the gay surprise cho BTC.
