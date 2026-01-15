# Services & Integration Points Research
**Date**: 2026-01-14
**Focus**: OprVerificationService, service patterns, Club/ClubMember models, and hook points

---

## 1. OprVerificationService Approval Flow

**Location**: `app/Services/OprVerificationService.php`

### Approval Process
- **createRequest()**: User submits verification with media, links, notes
  - Checks `User::canRequestVerification()` before allowing
  - Stores: `media_paths`, `links`, `notes`, status=PENDING

- **approve()**: Expert verifies and approves
  - Verifier must have `canVerifyElo()` permission
  - Transaction updates:
    1. Sets `request.status = APPROVED`
    2. Records `verifier_id`, `verified_at`, verifier notes
    3. Sets `user.is_elo_verified = true`
    4. Clears `user.elo_is_provisional = false`
    5. Creates `OprsHistory` record with reason: `REASON_ELO_VERIFIED`

- **reject()**: Expert rejects with reason
  - Requires `canVerifyElo()` permission + non-empty rejection notes
  - Sets status=REJECTED, stored verifier info

### Query Methods
- `getPendingRequests()`: Fetch by offset/limit
- `getRequests()`: Paginated with optional status filter
- `getStats()`: Count by status
- `getRequest()`: Single fetch with relations
- `getUserRequests()`: User's submission history
- `isInCooldownPeriod()`: 7-day cooldown after rejection

---

## 2. Service Patterns & Architecture

### Service Dependencies & Responsibilities

**OprsService** (`app/Services/OprsService.php`)
- Calculates total OPRS: `(0.7 × Elo) + (0.2 × Challenge) + (0.1 × Community)`
- Maps OPRS to OPR Levels (1.0 Beginner → 5.0+ Elite)
- **Key methods**:
  - `calculateOprs()`: Weighted calculation
  - `calculateOprLevel()`: Score → Level mapping
  - `updateUserOprs()`: Records history + updates user
  - `recalculateAfter*()`: Triggers for Match, Challenge, Activity, Skill Quiz
  - `estimateOprsChange()`: Preview impact before action
  - `adminAdjustment()`: Modify Challenge/Community components only
  - `getLeaderboard()`: With level filtering
  - `getLevelDistribution()`: Stats by level

**ChallengeService** (`app/Services/ChallengeService.php`)
- Injects `OprsService` as dependency
- **Workflow**:
  - `submitChallenge()`: Create + validate + award points
  - Checks monthly limit for TYPE_MONTHLY_TEST
  - Calls `oprsService->recalculateAfterChallenge()` on pass
  - `verifyChallenge()`: Admin marks verified
  - `revokeChallenge()`: Deduct points + recalculate OPRS
- **Metrics**:
  - `getChallengeStats()`: By type breakdown
  - `getAvailableChallenges()`: With eligibility reasons
  - `getPendingVerification()`: Admin queue
  - `getBestScores()`: Per challenge type

**CommunityService** (`app/Services/CommunityService.php`)
- Injects `OprsService` as dependency
- **Activity Recording** (all DB transactions):
  - `checkIn()`: Daily per-stadium limit + MetaData storage
  - `recordEventParticipation()`: Duplicates prevented
  - `recordReferral()`: Validates new user (7-day window)
  - `recordSocialActivity()`: One-time only (Join, FB, YouTube, TikTok)
  - `checkWeeklyMatchBonus()`: 5+ confirmed OCR matches/week
  - `recordMonthlyChallenge()`: Once per month
- **All call** `awardPoints()` → `oprsService->recalculateAfterActivity()`
- `processWeeklyBonuses()`: Batch job for eligible users
- `getAvailableActivities()`: With next-available dates

---

## 3. Club & ClubMember Models

**Club Model** (`app/Models/Club.php`)
- Route key: `slug` (friendly URLs)
- Relationships:
  - `creator()`: BelongsTo User
  - `members()`: BelongsToMany (pivot: role, joined_at)
  - `provinces()`: BelongsToMany (geographic scope)
  - `activities()`: HasMany ClubActivity
  - `joinRequests()`: HasMany ClubJoinRequest
  - `posts()`: HasMany ClubPost

- Member roles: 'creator', 'admin', 'moderator', (implied: member)
- Methods: `getMemberRole()`, `isManagement()`, `isAdmin()`, `isMember()`

**ClubJoinRequest** exists (separate model)
- Join flow: User requests → Approval → Member added to pivot

---

## 4. Integration Points for Auto-Award Hooks

### Current Hook Points (Where OPRS recalculation triggered)

| Event | Service | Method | Metadata Stored |
|-------|---------|--------|-----------------|
| Match result | OprsService | `recalculateAfterMatch()` | ocr_match_id |
| Challenge pass | ChallengeService | Calls OprsService | challenge_result_id |
| Community activity | CommunityService | Calls OprsService | activity_id |
| Skill quiz submit | OprsService | `recalculateAfterSkillQuiz()` | skill_quiz_attempt_id |
| ELO verification | OprVerificationService | `approve()` | verification_request_id |
| Admin adjustment | OprsService | `adminAdjustment()` | component, amount, reason |

### Database Transaction Pattern
All services wrap operations in `DB::transaction()`:
- Changes user columns (elo_rating, challenge_score, community_score, total_oprs, opr_level)
- Creates OprsHistory record with reason enum
- Metadata JSON field for context

---

## 5. HomeYard Controller Hook Points

**Controllers Found**:
- `HomeYardStadiumController` - Stadium management
- `HomeYardTournamentController` - Tournament management
- Additional: RoundController, CategoryController, GroupController, SocialController

### Likely Hook Points (to investigate)
- Stadium creation/update → Could trigger community activity or home_yard task completion
- Tournament creation → Home Yard task check
- Social event creation (SocialController) → Community activity reference
- Group creation (GroupController) → Community activity type

---

## 6. Key Findings & Integration Strategy

### For Point Earning System
1. **OprVerificationService is read-only for OPRS** - Approval just sets verification flags, OprsService handles recalc
2. **All services follow pattern**: Validate → DB::transaction → Record history → Trigger recalc
3. **Metadata is crucial**: All OPRS history records include metadata with reference IDs
4. **No auto-awards yet**: Services only create activities when explicitly called (must call from controllers)
5. **Club model ready**: Has join requests + member roles for potential club-based activities
6. **Community activity types** already include all expected activities (check-in, event, referral, social, weekly, monthly)

### Unresolved Questions
1. Where is `User::canRequestVerification()` defined? (Method lookup needed)
2. Where is `User::canVerifyElo()` defined? (Permission/role check)
3. Are HomeYard tasks stored in separate table or as ClubActivity records?
4. How are OCR matches confirmed? (CommunityService references `OcrMatch::STATUS_CONFIRMED`)
5. Is SkillQuizService used for auto-awarding upon quiz submit?

