# Laravel Pickleball Project - Codebase Structure Exploration Report

**Date**: 2026-03-09 | **Thoroughness**: Medium | **Scope**: Controllers, Services, Models, Routes

---

## PROJECT OVERVIEW
- **Framework**: Laravel 10 with PHP 8.1+
- **Database**: MySQL
- **Authentication**: JWT (PHPOpenSourceSaver/JWTAuth), Sanctum, Social Auth (Google, Facebook)
- **Frontend**: Blade templates
- **API**: RESTful with 400+ endpoints (public + protected)
- **Specialized Systems**: ELO ratings, OPRS (OnePickleball Rating System), Challenges, Community engagement

---

## 1. CONTROLLERS STRUCTURE (98 Total)

### A. ROOT CONTROLLERS (11 files)
Located at `app/Http/Controllers/`

| Controller | Purpose |
|---|---|
| **AuthController** | User registration, login, JWT token management |
| **ReviewController** | Reviews for instructors/facilities |
| **SocialController** | Social network integration |
| **ClubController** | Club management root ops |
| **ClubActivityController** | Club activity CRUD |
| **ClubActivityParticipantController** | Participant management |
| **ClubCompetitionController** | Club competition operations |
| **ClubMatchController** | Club match scoring |
| **WalletController** | User wallet/payments |
| **FavoriteController** | User favorites (instructors, stadiums) |
| **DebugController** | Development debugging |

### B. ADMIN CONTROLLERS (21 files)
Located at `app/Http/Controllers/Admin/` - Dashboard & management interface

| Controller | Purpose |
|---|---|
| **DashboardController** | Admin overview |
| **StadiumController** | Stadium CRUD & configuration |
| **LeagueController** | League management |
| **TournamentController** | Tournament administration |
| **NewsController** | News/announcements |
| **InstructorController** | Instructor account management |
| **InstructorRegistrationController** | Instructor onboarding |
| **VideoController** | Video content management |
| **PageController** | CMS page management |
| **CategoryController** | Taxonomies/categories |
| **UserPermissionController** | Role & permission management |
| **UserImportController** | Bulk user uploads |
| **OcrDisputeController** | OCR match dispute resolution |
| **OcrBadgeController** | Badge/achievement administration |
| **OprsController** | OPRS system admin panel |
| **OprsChallengeController** | Challenge system management |
| **OprsActivityController** | Activity tracking admin |
| **PointTaskController** | Point earning task definition |
| **PointSubmissionController** | Point submission approvals |
| **SkillQuizController** | Skill assessment quiz admin |
| **QuizController** | General quiz management |
| **PermissionRequestController** | Permission request handling |
| **SpecialChallengeController** | Special event challenges |

### C. API CONTROLLERS (32 files)
Located at `app/Http/Controllers/Api/` - RESTful JSON endpoints

| Controller | Purpose |
|---|---|
| **AuthController** | API auth (register, login, refresh, logout, me) |
| **BookingController** | Court booking API (CRUD + confirmBooking, rejectBooking, getAvailableSlotsForAllCourts) |
| **StadiumController** | Stadium listing & bank info |
| **TournamentController** | Tournament CRUD & standings |
| **LocationController** | Province/location data |
| **SocialController** | Social features API |
| **NewsController** | News listing API |
| **RefereeController** | Match management (start, updateScore, syncEvents, endMatch) |
| **RefereeProfileController** | Public referee profiles |
| **UserController** | User management (destroy) |
| **ProfileController** | User profile updates (avatar, email, password) |
| **OcrMatchController** | OCR matches (store, accept, reject, submitResult, confirmResult, dispute, uploadEvidence) |
| **OcrUserController** | OCR user stats (elo, badges, stats) |
| **OcrLeaderboardController** | OCR public leaderboard |
| **OprsController** | OPRS user profile (profile, breakdown, history, userProfile) |
| **OprsLeaderboardController** | OPRS leaderboard (by level, distribution) |
| **MatchmakingController** | Suggest opponents & estimate ELO changes |
| **ChallengeController** | Challenges (available, submit, history, stats, types) |
| **CommunityActivityController** | Community engagement (checkIn, recordEvent, recordReferral, recordSocialActivity) |
| **SkillQuizController** | Skill quiz (eligibility, start, attempt, questions, answer, submit, result, history) |
| **EventCheckinController** | Event check-in for point earning |
| **PointController** | Point balance, history, submissions, challenges, tasks |
| **ClubController** | Club CRUD, join requests, member management, activities, posts |
| **ClubActivityController** | Activity management & participants |
| **ClubActivityParticipantController** | RSVP & participant tracking |
| **ClubCompetitionController** | Club competition schedule & standings |
| **ClubPostController** | Club post/feed management |
| **LeagueApiController** | League listing, standings, schedule |
| **MediaUploadController** | File upload handling |

### D. FRONT CONTROLLERS (34+ files)
Located at `app/Http/Controllers/Front/` - Web interface user-facing views

**Dashboard & Profile**
- HomeController, DashboardController, ProfileController

**Booking & Instructors**
- BookingHistoryController, BookingInstructorController

**Search & Browse**
- HomeYardStadiumController, HomeYardTournamentController, HomeYardClubController, HomeYardLeagueController

**Tournament System**
- TournamentRegistrationController, CategoryController, RoundController, GroupController

**League System**
- LeagueRegistrationController, LeagueMatchController, LeagueTeamController

**Referee & Scoring**
- RefereeController, RefereeProfileController

**Community & Gamification**
- OcrController, OprVerificationController, SkillQuizController, QuizController, UserPointController, ReferralController

**Athlete & Permissions**
- AthleteManagementController, PermissionRequestController

**Club Features**
- ClubPostController, ClubPostCommentController, ClubPostReactionController

**Content**
- NewsController, VideoLikeController, VideoCommentController

### E. VERIFIER CONTROLLERS (1 file)
- **VerifierDashboardController** - Specialized verification/referee dashboard

---

## 2. SERVICES LAYER (20 Services)

### League & Team Services
- **LeagueService**: League CRUD, status transitions (draft → registration → active → completed)
- **LeagueScheduleService**: Match schedule generation & management
- **LeagueStandingsService**: Standings calculation & updates
- **LeagueRegistrationService**: Registration flows & payment collection
- **LeagueAutoTeamService**: Auto-team generation with skill-ranked and random pairing modes

### Rating & Scoring Systems
- **EloService**: ELO rating calculations for competitive matches
- **OprsService**: OPRS = 70% ELO + 20% Challenge + 10% Community scores
- **ChallengeService**: Challenge submission & result verification
- **PointEarningService**: Point system for community activities
- **PointSubmissionService**: Point submission validation & approval

### Club & Community Services
- **ClubActivityService**: RSVP, waitlisting (auto-waitlist when full), participant management
- **ClubCompetitionService**: Competition scheduling & team management
- **ClubMatchService**: Match result recording
- **ClubPostMediaService**: Media handling for club posts
- **CommunityService**: General community features
- **SocialVerificationService**: Social account verification

### Achievement & Assessment Services
- **BadgeService**: Achievement/badge award system
- **OprVerificationService**: OPRS rating verification process
- **SkillQuizService**: Skill quiz administration & scoring
- **ProfileService**: User profile operations

---

## 3. MODELS STRUCTURE (85+ Models)

### User & Authentication (5 models)
- **User**: elo_rating, challenge_score, community_score, total_oprs, opr_level, referral_code, athlete_types
- **UserBadge**: Achievement tracking
- **UserWallet**: Payment wallet
- **UserPointTransaction**: Point ledger
- **SocialProfileVerification**: Social media verification

### Booking & Court System (5 models)
- **Booking**: booking_code format BK{courtId:3+}{date:YYMMDD}{seq:3}, status: pending|confirmed|rejected
- **Court**: Court definition (belongs_to Stadium)
- **CourtPricing**: Hourly pricing rules
- **BookingInstructor**: Instructor lesson bookings
- **Stadium**: Venue/facility
- **PosStadiumSetting**: POS configuration

### Tournament System (7 models)
- **Tournament**: Main tournament
- **TournamentAthlete**: Athlete registration
- **TournamentCategory**: Skill/age categories
- **TournamentReferee**: Assigned referees
- **Category**: Category definitions
- **Round**: Tournament round
- **Group**: Tournament groups/pools

### League System (9 models)
- **League**: Status flow: draft → registration → active → completed | config: JSON (points_for_win, max_teams, max_players_per_team, match_format, scoring_type)
- **LeagueTeam**: Teams in league
- **LeagueTeamPlayer**: Player assignments
- **LeagueMatch**: Individual matches
- **LeagueMatchGame**: Individual games
- **LeagueRound**: Rounds
- **LeagueStanding**: Standings calculation
- **LeagueRegistration**: Registration requests
- **LeagueRegistrationPlayer**: Players in registration

### Rating Systems (2 models)
- **EloHistory**: ELO change log
- **OprsHistory**: OPRS change tracking

### Club System (14 models)
- **Club**: belongs_to User
- **ClubJoinRequest**: Join request management
- **ClubActivity**: Club events/activities
- **ClubActivityParticipant**: RSVP with waitlist (status: confirmed|waitlisted, waitlist_position tracking)
- **ClubActivityMatch**: Matches (format: WD, MD, MXD)
- **ClubActivityMatchRound**: Round tracking
- **ClubActivityMatchStanding**: Standings
- **ClubCompetitionTeam**: Teams in competition
- **ClubCompetitionMatch**: Competition matches
- **ClubCompetitionStanding**: Competition standings
- **ClubPost**: Posts/feed items
- **ClubPostComment**: Post comments
- **ClubPostReaction**: Like/reaction system
- **ClubPostMedia**: Media attachments

### OCR System (2 models)
- **OcrMatch**: OCR match records with dispute tracking & evidence
- **OprVerificationRequest**: Verification requests

### Challenge & Community (3 models)
- **SpecialChallenge**: Special event challenges
- **ChallengeResult**: Challenge submissions & results
- **CommunityActivity**: Activity log

### Content & Features (13 models)
- **News**: News/announcements
- **Video**: Video content
- **VideoComment, VideoLike**: Video engagement
- **Review**: Reviews (instructors/facilities)
- **Instructor**: Profiles
- **InstructorSchedule, InstructorPackage, InstructorReview, InstructorCertification, InstructorExperience, InstructorLocation, InstructorTeachingMethod, InstructorFavorite**: Instructor ecosystem

### Skill & Assessment (4 models)
- **Quiz**: Quiz definitions
- **SkillDomain**: Skill categories
- **SkillQuestion**: Quiz questions
- **SkillQuizAttempt, SkillQuizAnswer**: Quiz responses

### Miscellaneous (13 models)
- **Event, EventCheckin**: Events & check-ins
- **Favorite**: User favorites
- **Province**: Geographic locations
- **Page**: CMS pages
- **Payment**: Payment records
- **PermissionRequest**: Permission requests
- **PointTask, PointSubmission**: Point system
- **MatchEvent**: Match scoring plays
- **ActivityLog**: Audit logging
- **Tempo**: Pacing/timing data
- **Referral**: Referral tracking

### Key Model Relationships Summary
- User → has many: UserBadge, UserWallet, UserPointTransaction, Club, League, Tournament, ClubActivity, ClubPost, etc.
- Stadium → has many: Court, Tournament, Booking
- Club → has many: ClubActivity, ClubPost, ClubMatch, ClubCompetition, League
- League → has many: LeagueTeam, LeagueMatch, LeagueRound, LeagueStanding, LeagueRegistration
- ClubActivity → has many: ClubActivityParticipant, ClubActivityMatch with auto-waitlisting

---

## 4. ROUTE STRUCTURE

### Web Routes (Authentication + Blade Views)
- Auth routes (login, register, logout, profile updates)
- Admin panel (all CRUD operations for stadium, tournament, league, users, permissions, news, videos, etc.)
- User dashboard (profile, bookings, leagues, tournaments, points, referrals)
- Frontend pages (stadium search, tournament registration, league management, referee UI)
- Verification workflows (OPRS verification, permission requests)

### API Routes Structure (400+ Endpoints)

#### Public Routes (No Auth Required)
- Stadium: listing, detail, bank info
- Location: provinces
- Tournament: listing, detail, registration, standings
- News: categories, listing, detail
- Socials: listing, detail, participants
- OCR Leaderboard: index, distribution, byRank
- OPRS Leaderboard: levels, byLevel, distribution
- Referees: listing, detail (public profiles)
- Booking: public creation endpoint
- Referee profiles: public listing

#### Protected Routes (JWT + Sanctum Auth)

**Auth Group** (`/api/auth`)
- POST register, login, refresh-token
- POST logout
- GET me, user

**OCR Group** (`/api/ocr`)
- Matches: GET index/{id}, POST store, PATCH accept/reject, POST submitResult/confirmResult/dispute/uploadEvidence
- User stats: GET users/{user}/elo, /badges, /stats
- Leaderboard: GET index, distribution, {rank}

**OPRS Group** (`/api/oprs`)
- User: GET profile, breakdown, history (personal), userProfile (public)
- Leaderboard: GET index (public), levels (public), level/{level}, distribution
- Matchmaking: GET suggest/{user}, POST estimate

**Challenge Group** (`/api/challenges`)
- GET available, history, stats, types
- POST submit

**Community Group** (`/api/community`)
- POST check-in, event, referral, social-activity (throttled 10/min for check-in)
- GET history, stats, types

**Booking Group** (`/api/bookings`)
- GET list, history, {id}
- PATCH {id}
- DELETE {id}
- POST booking (new booking), {bookingId}/confirm, {bookingId}/reject
- GET stadium/{stadiumId}/slots-all (calendar grid)

**Referee Group** (`/api/referee`)
- GET dashboard, matches, matches/{match}, matches/{match}/state
- POST matches/{match}/start, matches/{match}/sync-events, matches/{match}/end
- PUT matches/{match}/score

**Skill Quiz Group** (`/api/skill-quiz`)
- GET eligibility, attempt/{id}, attempt/{id}/questions, result/{id}, history
- POST start, answers, submit-quiz

**Profile Group** (`/api/user/profile`)
- POST /, /avatar, /email, /password

**Events Group** (`/api/events`)
- GET (public), checkin/history
- POST checkin (throttled 10/min)

**Points Group** (`/api/points`) - all throttled 60/min
- GET tasks, balance, history, submissions, challenges
- POST submissions (stricter 10/min throttle)

**Clubs Group** (`/api/clubs`)
- CRUD: GET / {club}, POST /, PUT {club}, DELETE {club}
- Join: GET {club}/join-request-status, POST {club}/request-join, GET {club}/join-requests, POST/reject approve
- Members: PUT role, DELETE members
- Activities (nested): CRUD {club}/activities, GET/POST rsvp, DELETE (unrsvp)
  - Competition: GET teams/standings/matches, POST teams, DELETE teams/{team}, POST generate-schedule, PUT score
- Posts (nested): CRUD {club}/posts, POST {post}/pin

**Users Group** (`/api/users`)
- DELETE {id}

**Leagues Group** (`/api/leagues`)
- GET (auth: user's leagues), {league} (public), {league}/standings (public), {league}/schedule (public)

---

## 5. KEY ARCHITECTURAL PATTERNS

### ELO/OPRS Skill Rating System
- **ELO**: Tracks competitive win/loss record
- **OPRS Formula**: (0.7 × ELO) + (0.2 × Challenge Score) + (0.1 × Community Score)
- **OPRS Levels**:
  - 1.0: Beginner (0-599)
  - 2.0: Novice (600-899)
  - 3.0: Intermediate (900-1099)
  - 3.5: Upper Intermediate (1100-1349)
  - 4.0: Advanced (1350-1599)
  - 4.5: Pro (1600-1849)
  - 5.0+: Elite (1850+)

### Booking System Pattern
- **Code Format**: BK{courtId:3+}{date:YYMMDD}{seq:3}
- **Sequence Generation**: Uses DB::transaction + lockForUpdate to prevent race conditions
- **Status Flow**: pending → confirmed → (completed/cancelled/rejected)
- **Fallback**: `$booking->formatted_booking_code ?: ('BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT))`

### League Configuration Pattern
- **Status Transitions**: draft → registration → active → completed
- **Configurable Parameters**: points_for_win, points_for_loss, max_teams, max_players_per_team, match_format, scoring_type
- **Match Formats**: WD (Women's Doubles), MD (Men's Doubles), MXD (Mixed Doubles), Singles (implied)
- **MLP Preset**: [MD, WD, MXD, MXD]

### Club Activity Waitlisting Pattern
- **Auto-Waitlisting**: When activity reaches max_participants, new RSVPs are waitlisted
- **Status Values**: confirmed, waitlisted
- **Race Condition Prevention**: Uses lockForUpdate on participant count
- **Waitlist Position**: Auto-calculated with position tracking for fairness

### Match Event & Scoring Pattern
- **Event Types**: Scoring plays in matches (used for referee sync)
- **Match States**: not_started → in_progress → completed
- **Referee Sync**: POST matches/{match}/sync-events to update scoreline

### Authentication Pattern
- **API**: JWT (PHPOpenSourceSaver/JWTAuth) for token generation
- **Token Refresh**: POST /api/auth/refresh-token
- **Social Auth**: Google & Facebook OAuth integration
- **Authorization**: Spatie Permission package for role-based access control

---

## 6. INTEGRATIONS & DEPENDENCIES
- **Spatie Media Library**: File/media handling
- **PHPOpenSourceSaver/JWTAuth**: JWT token generation & validation
- **Spatie Permission**: Role & permission management
- **Laravel Sanctum**: Token-based authentication (alternative)
- **Social Auth**: Google & Facebook OAuth
- **Blade Templates**: Server-side rendering

---

## CODEBASE STATISTICS

| Metric | Count |
|---|---|
| **Total Controllers** | 98 |
| - Admin | 21 |
| - API | 32 |
| - Front | 34+ |
| - Root/Other | 11 |
| **Total Services** | 20 |
| **Total Models** | 85+ |
| **API Endpoints** | 400+ |
| **Route Groups** | 15+ major groups |

---

## KEY OBSERVATIONS

1. **Architecture**: Clean separation between Admin, API, and Front controllers with shared service layer
2. **API-First Design**: RESTful API routes extensively documented with mixed authentication (public + protected)
3. **Specialized Systems**: 
   - Complex ELO/OPRS rating system for skill tracking
   - Comprehensive point earning & gamification system
   - Club features with competition & activity management
   - Referee match management system
   - Instructor booking & review system
4. **Concurrency Handling**: Uses transaction + lockForUpdate for booking codes & activity seats
5. **Rate Limiting**: Throttling applied to high-volume endpoints (check-ins, point submissions)
6. **Waitlisting Logic**: Smart auto-waitlisting for club activities with position tracking

---

## UNRESOLVED QUESTIONS
- None identified at medium thoroughness level. Full implementation details (method bodies, specific business logic) would require deeper code review.
