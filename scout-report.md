# CODEBASE SCOUT REPORT
**Date:** 2026-01-15

## 1. PointTask Model - Task Codes & Meanings

### Model Location
- File: `/Users/thaopv/Desktop/php/pickleball/app/Models/PointTask.php`
- Database table: `point_tasks`

### Task Code Constants (16 total)
| Code | Constant Name | Role | Description |
|------|------------------|------|-------------|
| `referral` | CODE_REFERRAL | user | Refer new players to platform |
| `check_in_stadium` | CODE_CHECK_IN_STADIUM | user | Daily check-in at pickleball venue |
| `weekly_5_matches` | CODE_WEEKLY_5_MATCHES | user | Play 5+ matches in a week |
| `join_event` | CODE_JOIN_EVENT | user | Participate in community events |
| `special_challenge` | CODE_SPECIAL_CHALLENGE | user | Complete time-limited special challenges |
| `join_fb_group` | CODE_JOIN_FB_GROUP | user | Join Facebook group & verify |
| `follow_fb_page` | CODE_FOLLOW_FB_PAGE | user | Follow Facebook page & verify |
| `subscribe_youtube` | CODE_SUBSCRIBE_YOUTUBE | user | Subscribe YouTube channel & verify |
| `follow_tiktok` | CODE_FOLLOW_TIKTOK | user | Follow TikTok account & verify |
| `join_club` | CODE_JOIN_CLUB | user | Join or create community club |
| `create_ocr_match` | CODE_CREATE_OCR_MATCH | user | Create ranked OCR match challenge |
| `update_stadium_info` | CODE_UPDATE_STADIUM_INFO | home_yard | Update stadium/venue information |
| `create_social_schedule` | CODE_CREATE_SOCIAL_SCHEDULE | home_yard | Create social event schedule |
| `create_tournament` | CODE_CREATE_TOURNAMENT | home_yard | Create tournament event |
| `referee_score_match` | CODE_REFEREE_SCORE_MATCH | referee | Score/officiate tournament matches |
| `expert_verify_elo` | CODE_EXPERT_VERIFY_ELO | expert_host | Verify user ELO ratings |

### Role Types
- `user` - Regular users
- `home_yard` - Stadium/venue owners
- `referee` - Match officials
- `expert_host` - Expert verifiers

### Category Types
- `daily` - Daily recurring tasks
- `social` - Social media tasks
- `event` - Event participation tasks
- `tournament` - Tournament & competition tasks

### Frequency Types
- `unlimited` - No limit
- `daily` - Once per day
- `weekly` - Once per week
- `monthly` - Once per month
- `once` - One-time only

### Proof Types Required
- `none` - No proof needed (auto-earned)
- `image` - Image upload (1-5 images, max 5MB each)
- `link` - URL/profile link submission
- `qr_code` - QR code scanning

### Model Attributes
```php
$fillable = [
    'code',              // Task identifier (e.g., 'referral')
    'name',              // Display name
    'description',       // Task description
    'points',            // Points awarded
    'role',              // Target role
    'category',          // Task category
    'frequency',         // Earning frequency
    'requires_approval', // Admin approval needed (boolean)
    'proof_type',        // Proof type required
    'is_active'          // Active/disabled flag (boolean)
];
```

### Key Methods
- `findByCode(string $code)` - Get task by code
- `getActiveByRole(string $role)` - Get all active tasks for role
- `getActiveByCategory(string $category)` - Get active tasks by category
- `requiresProof()` - Check if proof is needed
- `scopeActive($query)` - Query scope for active tasks
- `scopeRequiresApproval($query)` - Query scope for approval-required tasks

---

## 2. Routes for Creating Social, Tournament, Updating Stadium, etc.

### Web Routes Location
- File: `/Users/thaopv/Desktop/php/pickleball/routes/web.php` (637 lines)

### Social Event Routes (HomeYard)
```
Prefix: /homeyard/socials (requires: auth, role:home_yard)

GET  /homeyard/socials              → SocialController@index (list)
POST /homeyard/socials              → SocialController@store (create)
GET  /homeyard/socials/{social}     → SocialController@show
PUT  /homeyard/socials/{social}     → SocialController@update
DELETE /homeyard/socials/{social}   → SocialController@destroy
POST /homeyard/socials/bulk-delete  → SocialController@bulkDelete (bulk operations)
```
**Named Routes:**
- `homeyard.socials.index` - List socials
- `homeyard.socials.store` - Create social
- `homeyard.socials.show` - View social
- `homeyard.socials.update` - Update social
- `homeyard.socials.destroy` - Delete social
- `homeyard.socials.bulkDelete` - Bulk delete

### Tournament Routes (HomeYard)
```
Prefix: /homeyard/tournaments (requires: auth, role:home_yard)

GET    /homeyard/tournaments              → HomeYardTournamentController@index
POST   /homeyard/tournaments              → HomeYardTournamentController@store
GET    /homeyard/tournaments/{id}         → HomeYardTournamentController@show
PUT    /homeyard/tournaments/{id}         → HomeYardTournamentController@update
DELETE /homeyard/tournaments/{id}         → HomeYardTournamentController@destroy

Special Routes:
GET    /tournaments/export/list           → exportTournamentsList
GET    /tournaments/{id}/athletes/export  → exportAthletes
GET    /tournaments-list                  → getTournamentsListJson
GET    /tournaments/stats                 → getTournamentStats
POST   /tournaments/bulk-delete           → bulkDelete
POST   /tournaments/{id}/draw             → drawAthletes
POST   /tournaments/{id}/reset-draw       → resetDraw
GET    /tournaments/{id}/draw-results     → getDrawResults
```

### Stadium Routes (HomeYard)
```
Prefix: /homeyard/stadiums (requires: auth, role:home_yard)

GET  /homeyard/stadiums              → HomeYardStadiumController@index
POST /homeyard/stadiums              → HomeYardStadiumController@store
GET  /homeyard/stadiums/{stadium}    → HomeYardStadiumController@show
PUT  /homeyard/stadiums/{stadium}    → HomeYardStadiumController@update
DELETE /homeyard/stadiums/{stadium}  → HomeYardStadiumController@destroy
```
**Note:** Stadium create/store are excluded (requires manual admin setup)
**Named Routes:** `homeyard.stadiums.*`

### Point Earning System Routes (User)
```
Prefix: /user/points (requires: auth)

GET  /user/points/                       → UserPointController@index (list tasks)
GET  /user/points/task/{task}            → UserPointController@showSubmitForm (form)
POST /user/points/task/{task}            → UserPointController@submit (submit proof)
GET  /user/points/history                → UserPointController@history (transaction history)
GET  /user/points/submissions            → UserPointController@submissions (submission history)
```
**Named Routes:**
- `user.points.index` - Main points page
- `user.points.submit-form` - Show submission form
- `user.points.submit` - Submit task (throttled: 10 per minute)
- `user.points.history` - View transaction history
- `user.points.submissions` - View submission history

---

## 3. Navigation & Menu Structure (Frontend)

### Primary Navigation
Location: `/Users/thaopv/Desktop/php/pickleball/resources/views/layouts/front.blade.php` (642 lines)

**Main Menu Items (Public/Guest):**
1. **Trang chủ** → route('home')
2. **Sân thi đấu** (Dropdown)
   - Danh sách sân → route('courts')
   - Lịch thi đấu Social → route('social')
3. **Giải đấu** (Dropdown)
   - Trận đấu → route('ocr.matches.list')
4. **Bảng xếp hạng** (Dropdown)
   - BXH VĐV Toàn Cầu → route('athlete-leaderboard', ['type' => 'athlete_international'])
   - BXH VĐV Việt Nam → route('athlete-leaderboard', ['type' => 'athlete_vietnam'])
   - BXH Cộng Đồng → route('athlete-leaderboard', ['type' => 'athlete'])
5. **Điểm trình OPR** (Dropdown - OPRS System)
   - Tổng quan OPRS → route('ocr.index')
   - Bảng xếp hạng OPRS → route('ocr.leaderboard')
   - Trận đấu OCR → route('ocr.ocr-matches')
   - Hồ sơ của tôi (Auth) → route('ocr.profile.id', auth()->user()->id)
   - Trận đấu của tôi (Auth) → route('ocr.matches.index')
6. **Cộng đồng** (Dropdown)
   - Đánh giá trình độ → route('skill-quiz.index')
   - Nhóm & CLB → route('clubs.index')
   - Giảng viên → route('instructors')
   - Trọng tài → route('academy.referees.index')
   - Video Pickleball → route('course')
   - Community Hub (Auth) → route('ocr.community.index')
7. **Tin tức** → route('news')

### User Dropdown Menu (Authenticated)
Located in header when user logged in.
```
⭐ Kiếm điểm          → route('user.points.index')
💰 Ví điểm (balance)  → route('user.wallet.index')
💼 Giới thiệu người   → route('user.referral.index')
Hồ sơ OPRS            → route('ocr.profile', auth()->user())
Chỉnh sửa hồ sơ       → route('user.profile.edit')

[Role-based options]
Admin:     Bảng điều khiển Admin     → route('admin.dashboard')
HomeYard:  Bảng điều khiển           → route('homeyard.overview')
User:      Bảng điều khiển Người dùng → route('user.dashboard')
Referee:   Bảng điều khiển Trọng tài → route('referee.dashboard')
Verifier:  Xác thực tài khoản OPR    → route('verifier.dashboard')

Đăng xuất
```

---

## 4. UserPointController Details

### Controller Location
- File: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/UserPointController.php` (215 lines)
- Namespace: `App\Http\Controllers\Front`

### Dependencies (Injected)
- `PointEarningService` - Get available tasks with eligibility
- `PointSubmissionService` - Submit proofs for approval
- `SocialVerificationService` - Check social media verification status

### Public Methods

#### 1. `index()` - Main Points Earning Page
**Route:** `GET /user/points/`
**Returns:** View with:
- `$tasks` - Array of tasks with eligibility status
  - Each task contains: `['task' => Task object, 'can_earn' => bool, 'reason' => string]`
- `$challenges` - Active special challenges (Eloquent collection)
- `$balance` - Current user's point balance
- `$socialStatus` - Array of social verification states (facebook, youtube, tiktok)
- `$pendingCount` - Number of pending submissions
- `$specialChallengeTask` - Pre-fetched special challenge task object

**View rendered:** `front.points.index`

#### 2. `showSubmitForm(PointTask $task)` - Show Submission Form
**Route:** `GET /user/points/task/{task}`
**Authorization:** Policy check via `$this->authorize('showSubmitForm', $task)`
**Returns:** View with:
- `$task` - PointTask object (passed via route model binding)
- `$pendingSubmission` - Current pending submission if exists
- `$challenges` - Active challenges (if task is special_challenge)

**View rendered:** `front.points.submit`

#### 3. `submit(Request $request, PointTask $task)` - Submit Task Proof
**Route:** `POST /user/points/task/{task}`
**Middleware:** `throttle:10,1` (10 requests per minute)
**Authorization:** Policy check via `$this->authorize('submit', $task)`
**Validation:**
- Image proof: `images required|array|min:1|max:5`, each `image|mimes:jpeg,png,jpg,gif,webp|max:5120`
- Link proof: `url required|url|max:500`
- QR Code proof: `qr_data required|string|max:255`
- Special challenge: optional `challenge_id`

**Returns:** Redirect to `user.points.submissions` with success message
**Transaction:** Wrapped in DB::transaction()

#### 4. `history()` - Point Transaction History
**Route:** `GET /user/points/history`
**Returns:** View with:
- `$transactions` - Paginated point transactions (20 per page)
- `$balance` - Current wallet balance

**View rendered:** `front.points.history`

#### 5. `submissions()` - User Submission History
**Route:** `GET /user/points/submissions`
**Returns:** View with:
- `$submissions` - Paginated submissions with task relation (15 per page)

**View rendered:** `front.points.submissions`

### Private Helper Methods

#### `getValidationRules(PointTask $task): array`
Returns validation rules based on proof type:
- `PROOF_IMAGE` - Image upload rules
- `PROOF_LINK` - URL validation
- `PROOF_QR_CODE` - QR code text validation
- `PROOF_NONE` - Empty array

#### `buildProofData(Request $request, PointTask $task): array`
Builds proof data structure:
- Handles file uploads with security check (prevents path traversal)
- Sanitizes URLs
- Captures challenge_id for special challenges
- Returns array with `['type' => proof_type, ...]`

---

## 5. Frontend Views for Points System

### View Files Location
```
/Users/thaopv/Desktop/php/pickleball/resources/views/front/points/
├── index.blade.php      (220 lines) - Main earn points page
├── submit.blade.php     (286 lines) - Submit proof form
├── submissions.blade.php (175 lines) - User submissions history
└── history.blade.php    - Transaction history
```

### index.blade.php - Main Points Page
**Features:**
- Header with current balance & pending count
- Quick links (History, Submissions, Wallet)
- Special challenges banner (if active)
- Tasks grouped by category:
  - 📅 Nhiệm Vụ Hàng Ngày (Daily)
  - 📣 Nhiệm Vụ Mạng Xã Hội (Social)
  - 🎟️ Sự Kiện & Workshop (Event)
  - 🏆 Giải Đấu & Cộng Đồng (Tournament)
- Each task card shows:
  - Task name & points
  - Description
  - Frequency badge (Daily/Weekly/Monthly)
  - Approval requirement badge
  - Can-earn status
  - Action button (if eligible)
- Social verification status section
  - Shows: Facebook, YouTube, TikTok verification state
  - Visual badges with checkmarks

### submit.blade.php - Submission Form
**Features:**
- Breadcrumb navigation
- Task info header with points display
- Form fields based on proof_type:
  - Image: Multi-file upload with preview (max 5, 5MB each)
  - Link: URL input field
  - QR Code: Text input for QR data
- Special challenge selector (if applicable)
- Instructions sidebar (specific to task code)
- Pending submission alert (if already submitted)
- File preview with thumbnail images

### submissions.blade.php - Submissions History
**Features:**
- Header with back button
- Submission cards with color-coded status:
  - Yellow left border: Pending ⏳
  - Green left border: Approved ✅
  - Red left border: Rejected ❌
- Each card displays:
  - Task name
  - Status badge with icon
  - Submission & review dates
  - Proof type indicator
  - Admin notes (if provided)
  - Points awarded/pending
- Pagination (15 per page)
- Status legend at bottom

---

## 6. Key Integration Points

### Social Verification
- Verified in: `SocialVerificationService::getVerificationStatus()`
- Used by: Points index view to show verification status
- Affects: Social media task eligibility

### Point Earning Service
- Method: `PointEarningService::getAllAvailableTasks($user)`
- Returns: Tasks with `can_earn` flag and eligibility `reason`
- Checks: Role, task frequency, approval requirements, user eligibility

### Point Submission Service
- Method: `PointSubmissionService::submit($user, $code, $proofData)`
- Creates: PointSubmission record with proof data
- Triggers: Events for point allocation/approval workflow

### Models Used
- `PointTask` - Task definition
- `PointSubmission` - User submission with proof
- `SpecialChallenge` - Time-limited challenges
- `UserPointTransaction` - Point transaction history
- `User` - Has `wallet()` relationship for points balance

---

## 7. Admin Dashboard Navigation

Location: `/Users/thaopv/Desktop/php/pickleball/resources/views/layouts/app.blade.php` (605 lines)

**Admin Sidebar Sections:**

**Point Earning Section:**
- Submissions → route('admin.point-submissions.index')
  - Shows pending count badge
  - Approval workflow interface
- Point Tasks → route('admin.point-tasks.index')
  - Edit task settings
  - Activate/deactivate tasks
- Special Challenges → route('admin.special-challenges.index')
  - Create & manage challenges
  - Set point rewards & deadlines

---

## Summary Statistics

| Category | Count |
|----------|-------|
| Task Codes | 16 |
| Roles | 4 (user, home_yard, referee, expert_host) |
| Categories | 4 (daily, social, event, tournament) |
| Proof Types | 4 (none, image, link, qr_code) |
| Routes (web) | 637 lines total |
| Controllers | 1 (UserPointController) |
| Views | 4 point-related |
| Frequencies | 5 (unlimited, daily, weekly, monthly, once) |

---

## Critical Files for Implementation

**Backend:**
1. `/Users/thaopv/Desktop/php/pickleball/app/Models/PointTask.php` - Task definitions
2. `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/UserPointController.php` - Main logic
3. `/Users/thaopv/Desktop/php/pickleball/routes/web.php` - Route definitions
4. `/Users/thaopv/Desktop/php/pickleball/app/Services/PointEarningService.php` - Task eligibility
5. `/Users/thaopv/Desktop/php/pickleball/app/Services/PointSubmissionService.php` - Submission logic

**Frontend:**
1. `/Users/thaopv/Desktop/php/pickleball/resources/views/front/points/index.blade.php` - Main page
2. `/Users/thaopv/Desktop/php/pickleball/resources/views/front/points/submit.blade.php` - Form
3. `/Users/thaopv/Desktop/php/pickleball/resources/views/front/points/submissions.blade.php` - History
4. `/Users/thaopv/Desktop/php/pickleball/resources/views/layouts/front.blade.php` - Navigation

**Navigation:**
1. `/Users/thaopv/Desktop/php/pickleball/resources/views/layouts/app.blade.php` - Admin navigation
2. `/Users/thaopv/Desktop/php/pickleball/resources/views/layouts/front.blade.php` - User navigation
