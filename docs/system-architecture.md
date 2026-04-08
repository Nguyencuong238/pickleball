# System Architecture

**Last Updated**: 2026-04-03
**Project**: Pickleball Platform
**Framework**: Laravel 10.10+

## Overview

The Pickleball Platform follows Laravel's Model-View-Controller (MVC) architecture pattern with role-based multi-tenant support. The system serves multiple user types through a unified codebase with distinct interfaces for each role.

## Architectural Pattern

### Pattern Classification
**Primary Pattern**: MVC (Model-View-Controller)
**Secondary Patterns**:
- Repository pattern (via Eloquent)
- Service layer (for complex business logic)
- Policy pattern (authorization)
- Observer pattern (events/listeners)

### Design Philosophy
- **Separation of Concerns**: Controllers, Models, Views isolated
- **Role-Based Access**: Different interfaces per user role
- **Convention over Configuration**: Laravel defaults
- **DRY Principle**: Shared components and services

## System Layers

```
┌─────────────────────────────────────────────────────────┐
│                    Presentation Layer                    │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────────────┐│
│  │   Public    │ │  Home Yard  │ │       Admin         ││
│  │  Frontend   │ │  Dashboard  │ │       Panel         ││
│  └─────────────┘ └─────────────┘ └─────────────────────┘│
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                   Application Layer                      │
│  ┌─────────────────────────────────────────────────────┐│
│  │                    Controllers                       ││
│  │  ┌─────────┐  ┌───────────┐  ┌─────────────────────┐││
│  │  │  Front  │  │ Home Yard │  │       Admin         │││
│  │  └─────────┘  └───────────┘  └─────────────────────┘││
│  └─────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────┐│
│  │              Middleware & Policies                   ││
│  └─────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                    Domain Layer                          │
│  ┌─────────────────────────────────────────────────────┐│
│  │                 Eloquent Models                      ││
│  │  User, Stadium, Court, Tournament, Instructor, etc. ││
│  └─────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────┐│
│  │                   Services                           ││
│  │  BookingService, TournamentService, etc.            ││
│  └─────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                 Infrastructure Layer                     │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────────────┐│
│  │   MySQL     │ │    File     │ │     External        ││
│  │  Database   │ │   Storage   │ │     Services        ││
│  └─────────────┘ └─────────────┘ └─────────────────────┘│
└─────────────────────────────────────────────────────────┘
```

## Component Architecture

### 1. Presentation Layer

#### Public Frontend (`resources/views/front/`)
- Homepage with featured content
- Court/stadium listing and detail
- Tournament listing and detail with tab-based schedule (group stage, standings, knockout bracket)
- Instructor profiles
- News and static pages

#### Home Yard Dashboard (`resources/views/home-yard/`)
- Stadium management
- Court and pricing configuration
- Tournament operations
- Athlete management
- Booking calendar

#### Admin Panel (`resources/views/admin/`)
- System-wide management
- User and role management
- Content moderation
- Platform settings

### 2. Application Layer

#### Controller Organization
```
app/Http/Controllers/
├── Controller.php              # Base controller
├── AuthController.php          # Authentication
├── FavoriteController.php      # Favorites
├── ReviewController.php        # Stadium reviews
├── SocialController.php        # Social events
├── ClubActivityController.php  # Club activities CRUD
├── ClubActivityParticipantController.php # RSVP management
├── ClubCompetitionController.php # Competition scheduling & scores
├── ClubMatchController.php      # Casual match generation & scoring
├── Admin/
│   ├── DashboardController.php
│   ├── CategoryController.php
│   ├── InstructorController.php
│   ├── NewsController.php
│   ├── PageController.php
│   ├── StadiumController.php
│   ├── TournamentController.php
│   ├── UserPermissionController.php
│   ├── VideoController.php
│   ├── OcrDisputeController.php
│   ├── OcrBadgeController.php
│   └── GemTopupController.php        # Gems wallet top-up approval
├── Api/
│   ├── MediaUploadController.php
│   ├── InstructorReviewController.php
│   ├── OcrMatchController.php
│   ├── OcrUserController.php
│   ├── OcrLeaderboardController.php
│   ├── RefereeApiController.php        # Referee API endpoints
│   └── RefereePublicController.php     # Public referee directory
└── Front/
    ├── HomeController.php              # Main frontend
    ├── DashboardController.php         # User dashboard
    ├── HomeYardStadiumController.php
    ├── HomeYardLeagueController.php    # League management
    ├── HomeYardTournamentController.php  # Legacy (deprecated)
    ├── Tournament/
    │   ├── TournamentController.php           # CRUD
    │   ├── TournamentAthleteController.php    # Athletes management
    │   ├── TournamentDrawController.php       # Draw/seeding
    │   ├── TournamentManualDrawController.php # Manual draw
    │   ├── TournamentGroupController.php      # Groups
    │   ├── TournamentMatchController.php      # Matches & scoring
    │   ├── TournamentRankingController.php    # Rankings
    │   ├── TournamentBracketController.php    # Knockout brackets
    │   ├── DrawAuthorizationTrait.php
    │   ├── MatchListFormatterTrait.php
    │   ├── MatchScheduleTrait.php
    │   ├── MatchScoreTrait.php
    │   ├── TournamentAthleteStatusTrait.php
    │   └── BracketAdvancementTrait.php
    ├── AthleteManagementController.php
    ├── TournamentRegistrationController.php
    ├── CategoryController.php
    ├── RoundController.php
    ├── GroupController.php
    ├── BookingInstructorController.php
    ├── NewsController.php
    ├── VideoCommentController.php
    ├── VideoLikeController.php
    ├── OcrController.php
    ├── RefereeController.php           # Referee dashboard & match officiating
    └── RefereeProfileController.php    # Public referee directory
```

#### Middleware Stack
```
HTTP Request
    │
    ▼
┌─────────────────────────┐
│ EncryptCookies          │
├─────────────────────────┤
│ VerifyCsrfToken         │
├─────────────────────────┤
│ Authenticate            │ ─── Redirects unauthenticated users
├─────────────────────────┤
│ Role Check              │ ─── Spatie Permission
├─────────────────────────┤
│ VerifySepayWebhook      │ ─── Validate SePay webhook IP (webhook routes)
└─────────────────────────┘
    │
    ▼
Controller Action
```

### 3. Domain Layer

#### Core Models

See [docs/codebase-summary.md](./codebase-summary.md) for complete relationships. Quick reference:

**User Management**
```
User ──┬── Stadium (1:N)
       ├── Tournament (1:N)
       ├── Booking (1:N)
       ├── Review (1:N)
       ├── Favorite (1:N)
       └── Province (N:1)
```

**Stadium System**
```
Stadium ──┬── Court (1:N) ──── CourtPricing (1:N)
          │                └── Booking (1:N)
          ├── Review (1:N)
          └── Media (Spatie)
```

**Tournament System**
```
Tournament ──┬── TournamentCategory (1:N) [singles/doubles]
             ├── TournamentAthlete (1:N)
             │   └── partner (self-reference for doubles)
             ├── Round (1:N) ──── Match (1:N) [pair support]
             ├── Group (1:N) ──── GroupStanding (1:N)
             └── Media (Spatie)
```

**Instructor System**
```
Instructor ──┬── InstructorCertification (1:N)
             ├── InstructorExperience (1:N)
             ├── InstructorPackage (1:N)
             ├── InstructorSchedule (1:N)
             ├── InstructorReview (1:N)
             ├── BookingInstructor (1:N)
             └── InstructorFavorite (1:N)
```

**OCR System**
```
User ──┬── OcrMatch (challenger/opponent/partners)
       ├── EloHistory (1:N) ──── OcrMatch (N:1)
       └── UserBadge (1:N)

OcrMatch ──┬── EloHistory (1:N)
           ├── Media (Spatie - evidence)
           └── Participants (challenger, opponent, partners)
```

**OPRS System**
```
User ──┬── ChallengeResult (1:N)
       ├── CommunityActivity (1:N)
       └── OprsHistory (1:N)

ChallengeResult ──── User (verifier)
CommunityActivity ── Reference (Stadium, Social, User)
OprsHistory ──────── Metadata (match/challenge/activity references)
```

**Profile System**
```
User ──┬── Avatar (storage/app/public/avatars)
       └── Province (N:1)

Province ──── User (1:N)
```

**Referee System**
```
User (referee) ──┬── TournamentReferee (1:N) ──── Tournament (N:1)
                 └── MatchModel (1:N as referee_id)

TournamentReferee ──┬── Tournament (N:1)
                    ├── User (referee) (N:1)
                    └── User (assigned_by) (N:1)

MatchModel ──── User (referee_id, nullable)
```

**Skill Quiz System**
```
User ──── SkillQuizAttempt (1:N)
│
└── gender (enum: male/female, nullable) ─── eloToSkillLevel()

SkillQuizAttempt ──┬── SkillQuizAnswer (1:N)
                   └── User (N:1)

SkillQuizAnswer ──┬── SkillQuizAttempt (N:1)
                  └── SkillQuestion (N:1)

SkillQuestion ──── SkillDomain (N:1)
```

**Club System**
```
Club ──┬── ClubPost (1:N) ──── ClubPostComment/Reaction/Media
       ├── ClubActivity ──┬── ClubActivityParticipant (RSVP)
       │                 ├── ClubActivityMatchRound (rounds)
       │                 │   └── ClubActivityMatch (singles/doubles)
       │                 ├── ClubActivityMatchStanding (stats)
       │                 └── Competitions (teams, matches, standings)
       └── ClubJoinRequest (1:N)

User ──┬── Club (creator)
       └── Participations (posts, comments, reactions, activities, matches)
```

### 4. Infrastructure Layer

#### Database Schema

```
┌─────────────────────────────────────────────────────────┐
│                     Core Tables                          │
├─────────────────────────────────────────────────────────┤
│ users          │ User accounts with OAuth               │
│ stadiums       │ Venue profiles                         │
│ courts         │ Individual courts                      │
│ court_pricings │ Time-based pricing                     │
│ bookings       │ Court reservations                     │
├─────────────────────────────────────────────────────────┤
│                   Tournament Tables                      │
├─────────────────────────────────────────────────────────┤
│ tournaments           │ Tournament config               │
│ tournament_categories │ Skill/age groups (singles/doubles) │
│ tournament_athletes   │ Registered athletes + partner_id│
│ rounds               │ Tournament rounds                │
│ groups               │ Group stage                      │
│ group_standings      │ Rankings                         │
│ matches              │ Match records with pair support  │
├─────────────────────────────────────────────────────────┤
│                   Instructor Tables                      │
├─────────────────────────────────────────────────────────┤
│ instructors                 │ Coach profiles            │
│ instructor_certifications   │ Credentials               │
│ instructor_packages         │ Service offerings         │
│ instructor_reviews          │ Feedback                  │
│ booking_instructors         │ Coaching bookings         │
├─────────────────────────────────────────────────────────┤
│                   Content Tables                         │
├─────────────────────────────────────────────────────────┤
│ news           │ Articles                               │
│ categories     │ News categories                        │
│ pages          │ Static content                         │
│ videos         │ Video library                          │
│ video_comments │ User comments                          │
│ video_likes    │ Engagement                             │
├─────────────────────────────────────────────────────────┤
│                   OCR Tables                             │
├─────────────────────────────────────────────────────────┤
│ ocr_matches    │ Ranked matches (singles/doubles)       │
│ elo_histories  │ Elo rating changes                     │
│ user_badges    │ Achievement badges                     │
├─────────────────────────────────────────────────────────┤
│                   OPRS Tables                            │
├─────────────────────────────────────────────────────────┤
│ users          │ Added OPRS fields (scores, level)      │
│ challenge_results     │ Skill challenge records         │
│ community_activities  │ Community engagement tracking   │
│ oprs_histories        │ OPRS change audit log           │
├─────────────────────────────────────────────────────────┤
│                   Profile Tables                         │
├─────────────────────────────────────────────────────────┤
│ users          │ Added profile fields (avatar, location) │
│ provinces      │ Geographic provinces                    │
├─────────────────────────────────────────────────────────┤
│                   Referee Tables                         │
├─────────────────────────────────────────────────────────┤
│ users          │ Added referee fields (bio, status, etc) │
│ tournament_referees │ Referee-tournament assignments    │
│ matches        │ Added referee_id and referee_name       │
├─────────────────────────────────────────────────────────┤
│                   Skill Quiz Tables                      │
├─────────────────────────────────────────────────────────┤
│ skill_domains  │ 6 fixed domains with weights            │
│ skill_questions│ 36 questions across domains             │
│ skill_quiz_attempts │ User attempts with ELO, flags     │
│ skill_quiz_answers  │ Individual question responses     │
│ users          │ Added quiz tracking fields + gender     │
├─────────────────────────────────────────────────────────┤
│                     Club System Tables                   │
├─────────────────────────────────────────────────────────┤
│ clubs          │ Club management and configuration       │
│ club_activities │ Club activity tracking (type, fee_gems for payments) │
│ club_activity_participants │ RSVP/participation with gem_transaction_id (FK) │
│ club_activity_match_rounds │ Match rounds with status     │
│ club_activity_matches │ Matches (singles/doubles)        │
│ club_activity_match_standings │ Per-player standings      │
│ club_join_requests │ Club join request management       │
│ club_posts     │ Club discussion posts                   │
│ club_post_comments │ Comments on club posts             │
│ club_post_media  │ Media in club posts                  │
│ club_post_reactions │ Reactions/likes on posts         │
│ club_competition_teams │ Teams in competitions           │
│ club_competition_matches │ Competition matches with scores │
│ club_competition_standings │ Competition standings         │
├─────────────────────────────────────────────────────────┤
│                Point Earning Tables                      │
├─────────────────────────────────────────────────────────┤
│ point_tasks    │ 16 tasks with roles, frequency, proof   │
│ point_submissions │ Proof submissions with admin review │
│ user_wallets   │ User point balance                      │
│ user_point_transactions │ Transaction history           │
│ social_profile_verifications │ Social platform records  │
│ special_challenges │ Time-limited challenges             │
│ events         │ Workshop/event system with QR           │
│ event_checkins │ User event attendance                   │
├─────────────────────────────────────────────────────────┤
│             Gems Wallet Tables (Apr 2026)                │
├─────────────────────────────────────────────────────────┤
│ gem_wallets    │ User gems balance (balance field)      │
│ gem_transactions │ Gems ledger (type: topup, payment,  │
│                │ cashback; status: pending, completed) │
├─────────────────────────────────────────────────────────┤
│              Booking Enhancement Tables                  │
├─────────────────────────────────────────────────────────┤
│ bookings       │ Added booking_code, confirmed_at,      │
│                │ transfer_proof, payment_method         │
├─────────────────────────────────────────────────────────┤
│               League Management Tables                   │
├─────────────────────────────────────────────────────────┤
│ leagues        │ League configuration and management     │
│ league_teams   │ Team enrollment with seed position     │
│ league_team_players │ Player roster assignment          │
│ league_rounds  │ Tournament rounds with status           │
│ league_matches │ Match records per round                │
│ league_match_games │ Game-by-game scores (MLP format)  │
│ league_standings │ Team standings calculation           │
├─────────────────────────────────────────────────────────┤
│             League Registration Tables                  │
├─────────────────────────────────────────────────────────┤
│ league_registrations │ User registrations w/ approval  │
│ league_registration_players │ Player assignments       │
└─────────────────────────────────────────────────────────┘
```

## Data Flow

Key flows summarized (detailed diagrams available per section):

### Court Booking Flow
User request → HomeController checks availability → Validates pricing → Creates booking record

### Tournament Registration Flow
Register → Validate category (singles/doubles) → Create TournamentAthlete → Link partner (if doubles)


### OCR Match Flow
Challenge → Accept/Reject → Play → Submit result → Confirm/Dispute → EloService calculates Elo → BadgeService awards badges

### OPRS Calculation Flow
Action triggers → Component service updates score (Elo/Challenge/Community) → OprsService.calculateOprs() (0.7*Elo + 0.2*Challenge + 0.1*Community) → Determine OPR Level → Record OprsHistory → Update User

### Challenge Submission Flow
Submit → Validate type & monthly limit → Create ChallengeResult → Check pass threshold → Award points (if passed) or record failure → Recalculate OPRS

### Community Activity Flow
User action → CommunityService validates eligibility & limits → Create CommunityActivity → Award points → Recalculate OPRS

### Referee Match Officiating Flow
View assigned match → Start match (status: in_progress) → Enter set scores → Calculate winner → Update match (set_scores, final_score, winner_id, status: completed)

### Profile Management Flow
Edit profile → Validate input (name, location, avatar, email, password) → Update avatar or verify password → Update User record

### Point Earning Flow
View tasks → Get eligible tasks by role → Auto-award or require proof submission → Admin review → Award/reject points → Update UserWallet & create PointTransaction

### Skill Quiz Flow
Start quiz → Check eligibility → Answer 36 questions (0-3 scale) → Validate time (3-20 min) → Calculate score & ELO → Cross-validate answers → Gender-aware skill level mapping → Check consistency → Assign ELO or flag for admin → Set re-quiz cooldown → Display results

### Club Activity RSVP Flow (All Types)
Show page loaded → User clicks RSVP button (AJAX) → Check spots available vs max_participants → Check user gem balance if activity has fee → If confirmed: charge gems (if hasFee) → create ClubActivityParticipant with status='confirmed' + gem_transaction_id → If waitlisted: create with status='waitlisted' + position (no charge) → Update participant count & avatars on frontend → Enable cancel button. On cancel: refund gems if confirmed + not started

### Club Activity Gems Payment Flow (Apr 2026)
RSVP confirmed (has fee) → ClubActivityService.rsvp() calls chargeGems() → GemWalletService.deduct() atomically reduces gem_wallet.balance → GemTransaction created with type='payment', status='completed' → GemCashbackService awards 5% to points wallet → gem_transaction_id stored on ClubActivityParticipant. On cancel: ClubActivityService.cancelRsvp() refunds if confirmed + before activity_date → GemWalletService.refund() creates refund transaction → gems restored to wallet. On waitlist promotion: promoteFromWaitlist() loops through waitlist, skips users with insufficient gems (auto-cancels), promotes first user with sufficient balance

### Club Activity Competition Flow (Competition Type)
RSVP phase complete → Management assigns RSVPd players to teams (AJAX) → Click "Tao lich thi dau" → Select format (round_robin|pool_play|single_elimination) → ClubCompetitionService generates matches grouped by round → View schedule matrix → Enter scores per match (AJAX PUT) → Recalculate standings (wins, losses, points) → Display real-time standings

### Club Activity Casual Matches Flow (Matches Tab)
Activity loaded → View existing matches tab (AJAX GET) → No matches yet? → Click "Tao tran dau" → Select format (singles_rr|rotating_doubles|fixed_doubles) → Select court count → ClubMatchService generates match rounds → View rounds matrix with player assignments → Enter scores per match (AJAX PUT) → Service recalculates standings → View real-time standings with win/loss/points

### Club Activity Match End + Score Flow (Mar 2026)
1. **Match End (Player)**:
   - Player finishes playing → POST `/clubs/{club}/activities/{activity}/player-end-match/{match}`
   - `playerEndMatch()` marks match with `ended_at` timestamp
   - Match status: `in_progress` → `pending_score` for player confirmation

2. **Score Submission**:
   - **Admin Path**: Admin submits scores → `ClubScoreService.adminSubmitScore()` → status: `admin_confirmed` (immediate completion)
   - **Player Path**: Player submits scores → `ClubScoreService.playerSubmitScore()` → status: `pending_confirmation` (awaits confirmation)

3. **Score Confirmation**:
   - Opposing team players see pending score in `getMyStatus()`
   - POST `/clubs/{club}/activities/{activity}/matches/{match}/confirm-score` with decision: confirm/reject
   - `ClubScoreService.confirmScore()` → status: `confirmed`/`admin_confirmed` → match complete
   - If rejected: `rejectScore()` → status: `rejected`, scores cleared for resubmission

4. **Match Completion**:
   - Trigger ELO/OPRS processing (if `oprs_weight > 0`)
   - Update member stats via `ClubMemberStatsService`
   - Update standings and leaderboard

5. **Score Settings** (per activity):
   - `best_of`: Match format (1, 3, or 5 sets)
   - `points_per_set`: Points to win a set (default: 21)

### Casual Match Generation Algorithms
1. **Singles RR (Round-Robin)**: Polygon rotation on singles players, handles odd byes
2. **Rotating Doubles**: Dynamic partner pairing each round, avoids repeated partnerships (3+ rounds)
3. **Fixed Doubles**: Permanent pairs play round-robin format

### League Registration Flow
User register → Validate phone (normalize) → Upload payment proof → Admin review → Approve/Reject → If approved: LeagueAutoTeamService generates teams (skill-ranked or random) → Create LeagueTeams + LeagueTeamPlayers → Email notification → Ready for league matches

### Recurring Activity Generation Flow (Scheduled Command)
Daily 06:00 → Query active recurring templates (status=upcoming, type=recurring) → For each template: iterate 7 days ahead → Check recurrence day of week match → Skip if instance already exists for target date → Create instance via ClubActivityService.createRecurringInstance() → Log output → Idempotent: safe to run multiple times

### Knockout Bracket Flow
Tournament admin requests bracket → TournamentBracketController.generate() → KnockoutBracketService seeds athletes per BracketSeedingHelper → KnockoutMatchBuilder creates bracket rounds → First round matches from seeded athletes → Subsequent rounds populated as winners advance via BracketAdvancementTrait → Admin enters match scores → Winners auto-advance to next round → Optional third-place match if enable_third_place=true → Bracket data formatted by KnockoutBracketQuery

### Authentication Flow
Standard: Login form → Validate credentials → Create session
OAuth: Redirect to provider → Callback → Find/Create user
Admin: Admin login → Check role 'admin' → Create admin session

## Security Architecture

### Authentication & Authorization

**Authentication:** Laravel session-based + CSRF protection + encrypted cookies. API: Laravel Sanctum + personal tokens. OAuth: Google, Facebook via Socialite.

**Authorization:** Spatie Permissions with roles: admin (full access), home_yard (stadium/tournament), referee (officiating), user (basic features). Middleware: role:admin, role:home_yard, role:referee, auth. Policies for resource ownership.

### Security Measures

| Layer | Protection |
|-------|-----------|
| Transport | HTTPS enforcement |
| Session | Encrypted cookies, CSRF tokens |
| Input | Request validation, Eloquent ORM |
| Output | Blade escaping, XSS prevention |
| Database | Parameterized queries |
| Files | Storage access control |

## File Storage & Media

**Structure:** `storage/app/public/` (stadiums, tournaments, instructors, videos) + symlink to `public/storage`

**Media Collections (Spatie):** Stadium (images, gallery), Tournament (thumbnail, gallery), Instructor (avatar, portfolio)

## API Architecture

### Internal API Endpoints

```
/api/
├── bookings                    # Booking operations
├── courts/{id}/available-slots # Availability check
├── instructor-booking          # Instructor bookings
├── instructor-review           # Instructor reviews
├── videos/{id}/comments        # Video comments
├── videos/{id}/like            # Video likes
├── upload-media                # Media uploads
├── ocr/
│   ├── matches                 # OCR match operations
│   ├── matches/{id}/accept     # Accept challenge
│   ├── matches/{id}/result     # Submit result
│   ├── matches/{id}/confirm    # Confirm result
│   ├── leaderboard             # Global rankings
│   └── users/{id}              # User OCR profile
├── oprs/
│   ├── profile                 # Current user OPRS profile
│   ├── breakdown               # OPRS score breakdown
│   ├── history                 # OPRS change history
│   ├── levels                  # All OPR levels
│   ├── leaderboard             # OPRS leaderboard
│   ├── leaderboard/levels      # Available levels
│   ├── leaderboard/level/{level}  # Level-specific leaderboard
│   ├── leaderboard/distribution   # Level distribution stats
│   ├── users/{user}            # User OPRS profile
│   ├── challenges              # Challenge operations
│   ├── challenges/{id}         # Challenge detail
│   ├── community               # Community activities
│   └── matchmaking             # Opponent suggestions
├── referees                    # Public: List active referees
├── referees/{referee}          # Public: Referee profile with stats
├── referee/                    # Protected (auth:api + referee role)
│   ├── dashboard               # Dashboard stats + upcoming matches
│   ├── matches                 # List assigned matches (filterable)
│   ├── matches/{match}         # Match detail
│   ├── matches/{match}/start   # Start match
│   └── matches/{match}/score   # Update match scores
├── skill-quiz/
│   ├── domains                 # Get all 6 skill domains
│   ├── questions               # Get 36 quiz questions
│   └── submit                  # Submit quiz answers
├── gems/
│   ├── balance                 # Get gems wallet balance
│   ├── history                 # Get gems transaction history
│   ├── topup                   # Request top-up (redirects to SePay)
│   └── transactions            # Get filtered transactions
├── points/
│   ├── tasks                   # Get available tasks with eligibility
│   ├── balance                 # Get wallet balance
│   ├── history                 # Get transaction history
│   ├── submissions             # Get/create submissions
│   └── challenges              # Get active special challenges
├── clubs/
│   ├── {club}/show             # Club details with join_request_status
│   ├── activities              # Club activities CRUD
│   ├── competitions            # Club competitions CRUD
│   ├── {club}/activities/{activity}/player-end-match/{match} # Player end match
│   ├── {club}/activities/{activity}/confirm-score/{match}    # Confirm match score
│   └── posts                   # Club posts CRUD
└── leagues/
    ├── {league}                # League detail with MLP format
    ├── {league}/teams          # Team roster management
    ├── {league}/matches        # Match listing and scoring
    └── {league}/standings      # Real-time standings

### Webhook Routes

```
/webhook/sepay                 POST   # SePay VietQR top-up webhook (verify.sepay.webhook)
```

### Knockout Bracket Routes (Web)

```
/tournament-manage/{tournament}/bracket              GET  # Bracket display
/tournament-manage/{tournament}/bracket/data         GET  # Bracket data (JSON)
/tournament-manage/{tournament}/bracket/generate     POST # Generate bracket
/tournament-manage/{tournament}/bracket/swap         POST # Swap bracket placement
```

### Response Format

```json
{
  "success": true,
  "data": {
    // Response data
  },
  "message": "Operation successful"
}
```

### Error Response

```json
{
  "success": false,
  "error": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

## Caching Strategy

### Cache Layers

| Layer | Tool | Purpose |
|-------|------|---------|
| Config | File | Configuration caching |
| Route | File | Route caching |
| View | File | Compiled Blade views |
| Query | Database/Redis | Frequent queries |
| Session | File/Database | User sessions |

### Cacheable Data

- Featured stadiums list
- Tournament listings
- Instructor directory
- News articles
- Static pages

## Deployment

**Dev:** PHP 8.1+, MySQL 8.0+, Node.js 18+, Composer. Commands: `php artisan serve` + `npm run dev`

**Prod:** Nginx/Apache, PHP-FPM 8.1+, MySQL 8.0+, Redis (optional), SSL. Optimize: config/route/view cache, `npm run build`

## Scalability Considerations

- Stateless application design with session storage in database/Redis
- Query optimization with indexes and eager loading
- Pagination for large datasets
- Media processing optimization (Spatie conversions, WebP)

## Monitoring & Logging

**Key Metrics:** Request latency, error rates, query time, memory usage, booking success rate


## Related Documentation

- [Project Overview PDR](./project-overview-pdr.md)
- [Codebase Summary](./codebase-summary.md)
- [Code Standards](./code-standards.md)
- [Referee API Documentation](./api-referee.md)

## OPRS System Architecture

### Service Layer Components

```
OprsService (Core)
├── calculateOprs()              # Calculate total OPRS from components
├── calculateOprLevel()          # Map OPRS to OPR level
├── updateUserOprs()             # Update and record history
├── recalculateAfterMatch()      # Triggered by match result
├── recalculateAfterChallenge()  # Triggered by challenge completion
├── recalculateAfterActivity()   # Triggered by community activity
├── getOprsBreakdown()           # Component breakdown for display
├── getLeaderboard()             # OPRS-based leaderboard
└── adminAdjustment()            # Manual score adjustment

ChallengeService
├── submitChallenge()            # User challenge submission
├── verifyChallenge()            # Admin verification
├── revokeChallenge()            # Admin revocation
├── canSubmitMonthlyTest()       # Monthly limit check
├── getChallengeHistory()        # User history
└── getChallengeStats()          # User statistics

CommunityService
├── checkIn()                    # Stadium check-in
├── recordEventParticipation()   # Event attendance
├── recordReferral()             # Player referral
├── checkWeeklyMatchBonus()      # Weekly bonus check
├── recordMonthlyChallenge()     # Monthly challenge
├── processWeeklyBonuses()       # Batch processing (scheduled)
└── getActivityStats()           # User statistics

ProfileService
├── updateBasicInfo()            # Update name, location, province
├── updateAvatar()               # Upload/remove avatar
├── deleteCurrentAvatar()        # Remove existing avatar file
├── updateEmail()                # Change email (with password)
├── updatePassword()             # Change password
├── verifyPassword()             # Verify current password
└── hasPassword()                # Check if user has password (OAuth)

SkillQuizService
├── calculateElo()               # Convert quiz score to ELO
├── calculateTotalScore()        # Sum weighted question scores
├── crossValidate()              # Check answer consistency
├── validateCompletionTime()     # Check 3-20 min window
├── applyEloCap()                # Apply caps (1100/1200)
├── eloToSkillLevel($elo, $gender) # Gender-aware skill level mapping
├── getSkillLevelName($level, $locale) # Localized level names
├── canRetakeQuiz()              # Check cooldown eligibility
├── calculateRetakeCooldown()    # Determine next retake date
├── flagSuspiciousAttempt()      # Mark for admin review
└── getAttemptStatistics()       # Admin statistics
```

### Component Weights and Levels

```php
// Component Weights
OPRS = (0.7 × Elo) + (0.2 × Challenge) + (0.1 × Community)

// OPR Levels
1.0 (Beginner)          0-599
2.0 (Novice)            600-899
3.0 (Intermediate)      900-1099
3.5 (Upper Intermediate) 1100-1349
4.0 (Advanced)          1350-1599
4.5 (Pro)               1600-1849
5.0+ (Elite)            1850+

// Challenge Types: dinking_rally (10pts, rallies>=20), drop_shot (8pts, success>=5/10), serve_accuracy (6pts, success>=7/10), monthly_test (30-50pts, score>=70)
// Community Activities: check_in (10pts daily), event_participation (50pts), player_referral (100pts), weekly_matches (30pts, 5+/week), monthly_challenge (150pts)
```

### OPRS Data Dependencies

```
User Model Fields:
├── elo_rating          (from OCR system)
├── challenge_score     (from ChallengeService)
├── community_score     (from CommunityService)
├── total_oprs          (calculated by OprsService)
└── opr_level           (determined by OprsService)

OprsHistory Record:
├── user_id, elo_score, challenge_score, community_score (snapshots)
├── total_oprs (calculated), opr_level (determined)
└── change_reason (enum), metadata (JSON)

SkillQuizAttempt Record:
├── user_id, total_score, max_possible_score, elo_assigned
├── completion_time, is_flagged, flag_reason
└── started_at, completed_at

User Gender: enum('male','female', nullable), defaults 'male'
```

### Skill Quiz Config
See `code-standards.md`. Base ELO=800, Max=1400, anti-fraud, gender-aware, 30-90 day cooldowns.

### Club Activity Match System (Mar 2026)
3 algorithms: Singles RR, Rotating Doubles, Fixed Doubles. Service: ClubMatchService. 7 AJAX endpoints.

### Tournament Rewrite Architecture (Mar 2026)
**Pattern:** Controller → Service → Model | **Frontend:** Alpine.js mixins | **Route:** tournament-manage | **Assets:** 8 JS + 11 CSS files

