# Brainstorm Report: OnePickleball Point Earning System

**Date**: 2025-01-14
**Status**: Brainstorm Complete

---

## 1. Problem Statement

Implement point earning system cho users dua tren role (Spatie). Diem (OnePickleball Points) luu vao `UserWallet`, doc lap voi OPRS, co the doi thuong/voucher.

### Requirements Summary

| Role | Tasks | Verification |
|------|-------|--------------|
| user | 11 tasks (social, daily, events, matches) | Mixed (auto + admin approval) |
| home_yard | 3 tasks (stadium, social schedule, tournament) | Auto (on create) |
| referee | 1 task (score match) | Auto (per match) |
| expert_host | 1 task (verify VDV elo) | Auto (on verify) |

---

## 2. Existing Infrastructure Analysis

### A. San Co
- **UserWallet**: Luu tong diem, method `addPoints()`, `deductPoints()`
- **UserPointTransaction**: Lich su giao dich, metadata JSON
- **Spatie Roles**: `admin`, `user`, `home_yard`, `referee`, `expert_host`
- **OprVerificationService**: Da co flow expert verify elo - chi can hook diem

### B. Can Tao Moi
- **PointTask Model**: Dinh nghia cac task va diem
- **PointSubmission Model**: User submit proof, admin duyet
- **SocialProfileVerification**: Luu link profile da verify (FB, YT, Tiktok)
- **PointEarningService**: Business logic trao diem
- **Admin Panel**: Duyet submissions

---

## 3. Database Design

### A. `point_tasks` - Dinh nghia cac task

```sql
CREATE TABLE point_tasks (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,  -- 'referral', 'check_in', 'join_fb'...
    name VARCHAR(100) NOT NULL,
    description TEXT,
    points INT NOT NULL,
    role VARCHAR(50) NOT NULL,          -- 'user', 'home_yard', 'referee', 'expert_host'
    category ENUM('daily', 'social', 'event', 'tournament') NOT NULL,
    frequency ENUM('unlimited', 'daily', 'weekly', 'monthly', 'once') NOT NULL,
    requires_approval BOOLEAN DEFAULT FALSE,
    proof_type ENUM('none', 'image', 'link', 'qr_code') DEFAULT 'none',
    is_active BOOLEAN DEFAULT TRUE,
    created_at, updated_at
);
```

**Seed Data**:

| code | name | points | role | category | frequency | approval | proof_type |
|------|------|--------|------|----------|-----------|----------|------------|
| referral | Referral | 10 | user | daily | unlimited | FALSE | none |
| check_in_stadium | Check in san tap | 1 | user | daily | daily | TRUE | image |
| weekly_5_matches | Thu thach 5 tran tuan | 5 | user | daily | weekly | FALSE | none |
| join_event | Tham gia su kien | 5 | user | event | unlimited | FALSE | qr_code |
| special_challenge | Thu thach dac biet | 15 | user | event | unlimited | TRUE | image |
| join_fb_group | Join Group Facebook | 1 | user | social | once | TRUE | link |
| follow_fb_page | Follow kenh Facebook | 1 | user | social | once | TRUE | link |
| subscribe_youtube | Dang ky Youtube | 1 | user | social | once | TRUE | link |
| follow_tiktok | Follow Tiktok | 1 | user | social | once | TRUE | link |
| join_club | Tham gia CLB/Nhom | 5 | user | tournament | once | FALSE | none |
| create_ocr_match | Tao tran dau OCR | 2 | user | tournament | unlimited | FALSE | none |
| update_stadium_info | Cap nhat thong tin cum san | 10 | home_yard | tournament | once | FALSE | none |
| create_social_schedule | Cap nhat lich dau Social | 5 | home_yard | tournament | once | FALSE | none |
| create_tournament | Tao giai dau | 20 | home_yard | tournament | once | FALSE | none |
| referee_score_match | Cham diem giai dau | 10 | referee | tournament | unlimited | FALSE | none |
| expert_verify_elo | Cham trinh VDV | 15 | expert_host | tournament | unlimited | FALSE | none |

---

### B. `point_submissions` - User submit proof

```sql
CREATE TABLE point_submissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    user_id BIGINT NOT NULL REFERENCES users(id),
    point_task_id BIGINT NOT NULL REFERENCES point_tasks(id),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    proof_data JSON,  -- {type: 'image', paths: [...]} or {type: 'link', url: '...'}
    admin_id BIGINT NULL REFERENCES users(id),
    admin_notes TEXT,
    reviewed_at TIMESTAMP NULL,
    points_awarded INT DEFAULT 0,
    created_at, updated_at,

    INDEX (user_id, point_task_id),
    INDEX (status),
    INDEX (created_at)
);
```

---

### C. `social_profile_verifications` - Verified social profiles

```sql
CREATE TABLE social_profile_verifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL REFERENCES users(id),
    platform ENUM('facebook', 'youtube', 'tiktok') NOT NULL,
    profile_url VARCHAR(500) NOT NULL,
    verified_at TIMESTAMP NOT NULL,
    verified_by BIGINT NOT NULL REFERENCES users(id),

    UNIQUE (user_id, platform),  -- 1 profile/platform/user
    UNIQUE (profile_url)          -- prevent duplicate profile across users
);
```

---

### D. `special_challenges` - Admin-created challenges

```sql
CREATE TABLE special_challenges (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    points INT NOT NULL DEFAULT 15,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    max_participants INT NULL,
    created_at, updated_at
);
```

---

## 4. Architecture Design

### A. Service Layer

```
app/Services/
├── PointEarningService.php      # Core logic
├── PointSubmissionService.php   # Handle submissions
└── SocialVerificationService.php # Social profile logic
```

### B. PointEarningService - Core Methods

```php
class PointEarningService
{
    // Auto-award points (no approval needed)
    public function awardPoints(User $user, string $taskCode, array $metadata = []): bool

    // Check if user can earn this task
    public function canEarn(User $user, string $taskCode): bool

    // Get available tasks for user role
    public function getAvailableTasks(User $user): Collection

    // Submit proof for approval
    public function submitForApproval(User $user, string $taskCode, array $proofData): PointSubmission

    // Approve submission (admin)
    public function approveSubmission(PointSubmission $submission, User $admin, ?string $notes): void

    // Reject submission (admin)
    public function rejectSubmission(PointSubmission $submission, User $admin, string $reason): void
}
```

### C. Event Hooks - Auto Award Points

```php
// 1. Referral - when new user registers with referral code
Event::listen(UserRegistered::class, function ($event) {
    if ($event->referrer) {
        app(PointEarningService::class)->awardPoints(
            $event->referrer,
            'referral',
            ['referred_user_id' => $event->user->id]
        );
    }
});

// 2. Join Club - when user joins first club
Event::listen(ClubJoined::class, function ($event) {
    $service = app(PointEarningService::class);
    if ($service->canEarn($event->user, 'join_club')) {
        $service->awardPoints($event->user, 'join_club', ['club_id' => $event->club->id]);
    }
});

// 3. OCR Match Created - when match confirmed
Event::listen(OcrMatchConfirmed::class, function ($event) {
    $service->awardPoints($event->challenger, 'create_ocr_match', ['match_id' => $event->match->id]);
});

// 4. Stadium Info Updated - first time
Event::listen(StadiumUpdated::class, function ($event) {
    // Only if user is home_yard owner
});

// 5. Social Schedule Created - first time
Event::listen(SocialCreated::class, function ($event) {
    // Award points to creator
});

// 6. Tournament Created - first time
Event::listen(TournamentCreated::class, function ($event) {
    // Award points to creator
});

// 7. Referee Score Match
Event::listen(MatchScored::class, function ($event) {
    $service->awardPoints($event->referee, 'referee_score_match', ['match_id' => $event->match->id]);
});

// 8. Expert Verify Elo - hook into existing OprVerificationService
// In OprVerificationService::approve() add:
$this->pointEarningService->awardPoints(
    $verifier,
    'expert_verify_elo',
    ['request_id' => $request->id, 'user_id' => $request->user_id]
);

// 9. Event Check-in (QR code)
Event::listen(EventCheckedIn::class, function ($event) {
    $service->awardPoints($event->user, 'join_event', ['event_id' => $event->event->id]);
});
```

---

## 5. Admin Panel Design

### A. Routes

```php
// Admin routes
Route::prefix('admin/point-submissions')->group(function () {
    Route::get('/', [PointSubmissionController::class, 'index']);        // List pending
    Route::get('/{uuid}', [PointSubmissionController::class, 'show']);   // Detail
    Route::post('/{uuid}/approve', [PointSubmissionController::class, 'approve']);
    Route::post('/{uuid}/reject', [PointSubmissionController::class, 'reject']);
});

Route::prefix('admin/point-tasks')->group(function () {
    Route::get('/', [PointTaskController::class, 'index']);      // List all tasks
    Route::put('/{id}', [PointTaskController::class, 'update']); // Edit points/status
});

Route::prefix('admin/special-challenges')->group(function () {
    Route::get('/', [SpecialChallengeController::class, 'index']);
    Route::post('/', [SpecialChallengeController::class, 'store']);
    Route::put('/{id}', [SpecialChallengeController::class, 'update']);
    Route::delete('/{id}', [SpecialChallengeController::class, 'destroy']);
});
```

### B. Admin Views

1. **Submission Queue**: List pending submissions, filter by task type, bulk actions
2. **Task Management**: Edit points, enable/disable tasks
3. **Special Challenge**: Create/edit/delete pop-up challenges

---

## 6. User Flow

### A. Submit Proof (Social Tasks)

```
1. User opens "Earn Points" page
2. Sees available tasks for their role
3. Clicks "Join FB Group" task
4. System checks if already verified FB profile
   - NO: Show form to enter FB profile URL
   - YES: Skip to step 5
5. User submits proof (screenshot of joined group)
6. Submission queued for admin review
7. Admin approves/rejects
8. Points awarded, user notified
```

### B. Auto Points (No Approval)

```
1. User creates OCR match
2. Match completed & confirmed
3. System auto-awards 2 points
4. Transaction recorded in history
5. User sees notification
```

---

## 7. Implementation Approach

### Option A: Event-Driven (Recommended)

**Pros**:
- Clean separation of concerns
- Easy to add new point sources
- Existing events can be reused
- Loosely coupled

**Cons**:
- Need to create some new events
- Debugging event chains can be tricky

### Option B: Direct Integration

**Pros**:
- Simple, direct calls
- Easy to understand

**Cons**:
- Tight coupling
- Harder to maintain
- Code scattered across controllers

### Recommendation: Option A (Event-Driven)

---

## 8. Files to Create/Modify

### New Files

```
database/migrations/
├── xxxx_create_point_tasks_table.php
├── xxxx_create_point_submissions_table.php
├── xxxx_create_social_profile_verifications_table.php
└── xxxx_create_special_challenges_table.php

app/Models/
├── PointTask.php
├── PointSubmission.php
├── SocialProfileVerification.php
└── SpecialChallenge.php

app/Services/
├── PointEarningService.php
├── PointSubmissionService.php
└── SocialVerificationService.php

app/Events/
├── PointsEarned.php
├── SubmissionApproved.php
└── SubmissionRejected.php

app/Listeners/
├── AwardReferralPoints.php
├── AwardClubJoinPoints.php
├── AwardOcrMatchPoints.php
├── AwardStadiumUpdatePoints.php
├── AwardSocialCreatePoints.php
├── AwardTournamentCreatePoints.php
├── AwardRefereeScoringPoints.php
├── AwardExpertVerifyPoints.php
└── AwardEventCheckinPoints.php

app/Http/Controllers/Admin/
├── PointSubmissionController.php
├── PointTaskController.php
└── SpecialChallengeController.php

app/Http/Controllers/Front/
└── UserPointController.php  # User view tasks, submit proof, history

database/seeders/
└── PointTaskSeeder.php

resources/views/admin/
├── point-submissions/
├── point-tasks/
└── special-challenges/

resources/views/front/
└── points/  # User earn points page
```

### Modify Files

```
app/Services/OprVerificationService.php  # Add point award on verify
app/Http/Controllers/ (various)          # Add event dispatches
app/Providers/EventServiceProvider.php   # Register listeners
```

---

## 9. Estimated Scope

### Phase 1: Core Infrastructure
- Database migrations & models
- PointEarningService core logic
- Admin submission queue

### Phase 2: Auto-Award Integration
- Event listeners for auto-award tasks
- Hook into existing flows

### Phase 3: User Interface
- User "Earn Points" page
- Submission form with proof upload
- Point history page

### Phase 4: Admin Tools
- Task management
- Special challenge CRUD

---

## 10. Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Duplicate profile abuse | Medium | Unique constraint on profile_url |
| Point farming | Medium | Frequency limits, admin review |
| Missing events | Low | Audit existing events first |
| Performance | Low | Proper indexing, queue jobs |

---

## 11. Clarified Requirements

| Question | Answer |
|----------|--------|
| Event/Workshop | **Tao model Event moi** (khac voi Social), co QR check-in |
| Weekly 5 Matches | Chi dem confirmed OCR matches, **tu dong detect** (khong can submit) |
| Special Challenge | Hien thi tren Homepage (banner/modal) |
| Notifications | Khong can, user tu check point history |
| Check-in san tap | Chi can hinh (admin duyet), khong can GPS |
| Referral system | **Da co**, hook diem khi referred user **hoan thanh Skill Quiz** (khong phai luc register) |
| Home_yard "once" | **Once per stadium** (user co 3 san = duoc 3 lan) |
| API | **Web + REST API** cho mobile |
| Point expiry | **Khong het han** |
| Approval role | **Tat ca admin** co quyen duyet |

---

## 12. Conclusion

He thong point earning co the xay dung dua tren:
- **UserWallet/UserPointTransaction** hien co
- **Event-driven architecture** de decouple logic
- **Admin approval flow** tuong tu OprVerificationService

Estimated: 15-20 files moi, 5-10 file modify.

Next step: Xac nhan cac open questions truoc khi bat dau implement.
