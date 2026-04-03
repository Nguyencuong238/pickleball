# Laravel Pickleball Project - Architecture Overview

## Project Summary
A comprehensive Laravel 11 pickleball platform with **91 models**, **86 controllers**, **33 services**, and **195 migrations**. The application supports tournaments, leagues, clubs, instructors, stadiums, and player engagement systems (OCR ratings, OPRS challenges, skill quizzes, etc.).

---

## 1. MODELS (91 Total) - Grouped by Domain

### Core User & Auth
- **User** - Main authenticatable model (roles, ratings, stats, social logins)
- **Province** - Geographic locations

### Tournaments & Competition
- **Tournament** - Main tournament model (slug-based, has media, bracket data, referee support)
- **TournamentAthlete** - Athlete participation (partner_id for doubles)
- **TournamentCategory** - Tournament-specific categories
- **TournamentReferee** - Referee assignments
- **Round** - Tournament rounds
- **Group** - Group play structure
- **GroupStanding** - Group stage standings
- **MatchModel** - Match records
- **MatchEvent** - Detailed match events
- **Court** - Tournament courts (capacity, rental_price)
- **CourtPricing** - Court pricing configurations
- **Category** - Global skill/match categories
- **Tempo** - Tournament timing configurations

### Leagues
- **League** - League organization (club-based, has config, registration)
- **LeagueTeam** - Team membership in league
- **LeagueTeamPlayer** - Player registration per team
- **LeagueMatch** - Individual league matches
- **LeagueMatchGame** - Sets/games within a match
- **LeagueRound** - League round scheduling
- **LeagueStanding** - League standings/rankings
- **LeagueRegistration** - Registration with payment tracking
- **LeagueRegistrationPlayer** - Individual player registration entries

### Clubs & Club Activities
- **Club** - Club management (creator, members, provinces)
- **ClubActivity** - Club activities/events (extends to open play)
- **ClubActivityMatch** - Matches within activities
- **ClubActivityMatchRound** - Match rounds in activities
- **ClubActivityMatchStanding** - Standing tracking
- **ClubActivityParticipant** - Participant tracking
- **ClubMemberStat** - Member statistics tracking
- **ClubPost** - Club social posts
- **ClubPostComment** - Post comments
- **ClubPostReaction** - Post reactions
- **ClubPostMedia** - Post media attachments
- **ClubJoinRequest** - Membership requests
- **ClubCompetitionTeam** - Competition teams
- **ClubCompetitionMatch** - Competition matches
- **ClubCompetitionStanding** - Competition standings

### Instructors & Teaching
- **Instructor** - Instructor profiles (verified, certified, ratings)
- **InstructorExperience** - Experience entries
- **InstructorCertification** - Certifications
- **InstructorPackage** - Service packages
- **InstructorLocation** - Service locations
- **InstructorSchedule** - Availability schedules
- **InstructorReview** - Student reviews
- **InstructorTeachingMethod** - Teaching approaches
- **InstructorFavorite** - User favorites
- **BookingInstructor** - Booking records

### Videos & Educational
- **Video** - Instructional videos (chapters, instructor, duration, ratings)
- **VideoComment** - Comment threading (parent_id)
- **VideoLike** - Like tracking

### Rating & Ranking Systems
- **OcrMatch** - Online competitive rating matches
- **OcrMatchResult** - Match results (with category tracking)
- **EloHistory** - Elo rating history
- **OprsHistory** - OPRS rating history
- **UserBadge** - Achievement badges
- **EloVerified** - Verification status

### Challenges & Engagement
- **ChallengeResult** - Challenge participation
- **SpecialChallenge** - Special challenge configurations
- **CommunityActivity** - Community engagement tracking
- **PointSubmission** - User point submissions
- **PointTask** - Point earning tasks
- **PointEarning** - Point transaction records
- **UserPointTransaction** - Point history
- **UserWallet** - Wallet balances
- **Referral** - Referral tracking

### Skill & Quiz System
- **Quiz** - Quiz definitions
- **SkillDomain** - Skill domains
- **SkillQuestion** - Quiz questions
- **SkillQuizAttempt** - Attempt tracking
- **SkillQuizAnswer** - Answer submissions

### Bookings & Venues
- **Booking** - Court bookings (with service_fee)
- **Stadium** - Venue management (verified, premium, pricing, amenities)
- **PosStadiumSetting** - POS/billing settings for stadiums

### Social & Verification
- **Social** - Social activities/groups
- **SocialProfileVerification** - Social profile verification
- **Review** - Reviews/ratings
- **Favorite** - Favorites (polymorphic)
- **ActivityLog** - Audit logging

### Permissions & Admin
- **PermissionRequest** - Permission requests
- **OprVerificationRequest** - OPR verification requests
- **Event** - General events
- **EventCheckin** - Event attendance
- **News** - News/announcements (categories, featured)
- **Page** - Static pages
- **Payment** - Payment records

---

## 2. CONTROLLERS (86 Total) - Grouped by Namespace

### **Admin Controllers (23)**
`app/Http/Controllers/Admin/`

- **Dashboard** - Admin dashboard
- **User** (UserPermissionController, UserImportController) - User management & bulk import
- **Content** (NewsController, PageController, VideoController, CategoryController) - Content management
- **Competition** (TournamentController, LeagueController) - Tournament/League admin
- **Venue** (StadiumController) - Stadium management
- **Instructor** (InstructorController, InstructorRegistrationController) - Instructor admin
- **Points** (PointTaskController, PointSubmissionController) - Point system admin
- **OCR/OPRS** (OcrBadgeController, OcrDisputeController, OprsController, OprsChallengeController, OprsActivityController) - Rating systems
- **Quiz** (QuizController, SkillQuizController) - Assessment management
- **Permissions** (PermissionRequestController) - Permission approvals
- **Special** (SpecialChallengeController) - Special events

### **Front/Web Controllers (33)**
`app/Http/Controllers/Front/`

- **Navigation** (HomeController, DashboardController) - Dashboard & home
- **Tournament** (TournamentController, TournamentRegistrationController, TournamentGroupController, TournamentDrawController, TournamentManualDrawController)
  - Traits: TournamentAthleteStatusTrait, DrawAuthorizationTrait, MatchScheduleTrait, MatchScoreTrait, BracketAdvancementTrait
- **League** (LeagueMatchController, LeagueTeamController) - League participation
- **Club** (HomeYardClubController, ClubPostController, ClubPostCommentController, ClubPostReactionController) - Club features
- **Instruction** (BookingInstructorController, VideoCommentController, VideoLikeController) - Instructor booking & videos
- **OCR/Rating** (OcrController, OprVerificationController) - Online rating features
- **Referee** (RefereeController, RefereeProfileController) - Referee management
- **Stadium** (HomeYardStadiumController, CategoryController) - Venue browsing
- **User Profile** (ProfileController, ReferralController, UserPointController, AthleteManagementController) - Personal features
- **Skill** (SkillQuizController) - Learning assessments
- **Quiz** (QuizController) - General quizzes
- **Round** (RoundController) - Tournament rounds

### **API Controllers (30)**
`app/Http/Controllers/Api/`

- **Auth** (AuthController) - API authentication
- **Core** (UserController, ProfileController, LocationController) - User data
- **Competition** (TournamentController, OcrMatchController, ChallengeController, MatchmakingController) - Tournament APIs
- **League** (LeagueApiController) - League data
- **Club** (ClubController, ClubActivityParticipantController, ClubActivityController, ClubPostController) - Club APIs
- **Instructor** (InstructorReviewController) - Teaching APIs
- **Rating Systems** (OprsController, OprsLeaderboardController, OcrUserController, OcrLeaderboardController) - Leaderboard APIs
- **Engagement** (PointController, EventCheckinController, CommunityActivityController, SkillQuizController) - Engagement APIs
- **Referee** (RefereeController, RefereeProfileController) - Referee APIs
- **Venue** (StadiumController, BookingController) - Stadium & booking APIs
- **Social** (SocialController) - Social APIs
- **News** (NewsController) - News APIs
- **Media** (MediaUploadController) - File uploads

### **Root Controllers (9)**
- **AuthController** - Web auth (separate from API auth)
- **ClubController** - Club CRUD
- **ClubActivityController** - Club activity management
- **ClubActivityParticipantController** - Participant tracking
- **ClubCompetitionController** - Competitions
- **ClubMatchController** - Match management
- **ClubCheckinController** - Check-in system
- **ClubOpenPlayController** - Open play mechanics
- **ClubDashboardController** - Club dashboard
- **ClubLeaderboardController** - Club rankings
- **ReviewController** - Reviews/ratings
- **FavoriteController** - Favorite management
- **SocialController** - Social features
- **WalletController** - Wallet operations
- **DebugController** - Debug utilities

### **Other Namespaces**
- **Verifier** (VerifierDashboardController) - Verification role dashboard

---

## 3. SERVICES (33 Total)

### Tournament Services
- **TournamentCrudService** - Create/read/update/delete tournaments
- **TournamentDrawService** - Bracket draw generation
- **TournamentStandingService** - Standing calculations
- **TournamentMatchService** - Match management
- **KnockoutBracketService** - Knockout bracket logic
- **KnockoutBracketQuery** - Bracket queries
- **KnockoutMatchBuilder** - Match construction
- **DrawAssignmentHelper** - Seeding assignments
- **BracketSeedingHelper** - Seeding calculations
- **RankingQueryHelper** - Ranking queries
- **MatchCreationHelper** - Match creation logic

### League Services
- **LeagueService** - League management
- **LeagueRegistrationService** - Registration handling
- **LeagueScheduleService** - Match scheduling
- **LeagueStandingsService** - Standings calculation
- **LeagueAutoTeamService** - Auto team creation

### Club Services
- **ClubActivityService** - Activity management
- **ClubCompetitionService** - Competition logic
- **ClubMatchService** - Match mechanics
- **ClubMatchmakingService** - Auto-pairing system
- **ClubScoreService** - Score calculations
- **ClubMemberStatsService** - Member statistics

### Rating & Engagement Services
- **OprsService** - OPRS rating system
- **EloService** - ELO rating calculations
- **BadgeService** - Badge award system
- **ChallengeService** - Challenge management
- **CommunityService** - Community engagement

### Point System Services
- **PointEarningService** - Point calculations
- **PointSubmissionService** - Submission processing

### Educational Services
- **SkillQuizService** - Quiz logic

### Verification Services
- **OprVerificationService** - OPR verification
- **SocialVerificationService** - Social profile verification

### Media Services
- **ClubPostMediaService** - Media handling for posts
- **ProfileService** - Profile management

---

## 4. ROUTE FILES (6 Total)

| File | Purpose |
|------|---------|
| **web.php** | Web routes (Front, Admin, Auth controllers) |
| **api.php** | RESTful API routes (Api namespace controllers) |
| **channels.php** | Broadcasting channels (WebSocket) |
| **console.php** | Artisan commands |
| **test.php** | Testing routes |
| **debug.php** | Debug utilities |

**Key API Groups:**
- `/api/auth` - Authentication endpoints
- `/api/tournaments` - Tournament CRUD & participation
- `/api/ocr` - Online competitive rating
- `/api/oprs` - OPRS rating system
- `/api/challenges` - Challenge endpoints
- `/api/leagues` - League management
- `/api/clubs` - Club CRUD & activities
- `/api/stadiums` - Venue browsing & booking
- `/api/referees` - Referee endpoints
- `/api/points` - Point system

---

## 5. MIGRATIONS (195 Total)

### Core Infrastructure
- `2014_10_12_000000_create_users_table` - Users (with OAuth IDs)
- `2014_10_12_100000_create_password_reset_tokens_table` - Password reset
- `2019_08_19_000000_create_failed_jobs_table` - Job tracking
- `2019_12_14_000001_create_personal_access_tokens_table` - API tokens
- `2025_11_14_053847_create_permission_tables` - Spatie permissions

### Venue Management
- `2025_11_14_153140_create_stadiums_table` - Stadium records
- `2025_11_19_000003_create_courts_table` - Court resources
- `2025_11_24_022812_create_court_pricing_table` - Pricing configurations
- `2026_03_03_093533_add_club_id_to_tournaments_table` - Club tournaments

### Tournament System
- `2025_11_17_000001_create_tournaments_table` - Base tournament
- `2025_11_17_000002_create_tournament_athletes_table` - Athlete participation
- `2025_11_19_000001_create_tournament_categories_table` - Categories
- `2025_11_19_000002_create_rounds_table` - Rounds
- `2025_11_19_000004_create_matches_table` - Matches
- `2025_11_19_000006_create_groups_table` - Groups
- `2025_11_19_000007_create_group_standings_table` - Standings
- `2025_12_09_000002_create_tournament_referees_table` - Referee assignment
- `2025_12_16_create_tournament_tournament_category_table` - Many-to-many categories
- `2025_12_18_203455_add_partner_id_to_tournament_athletes_table` - Doubles support
- `2025_12_19_000001_create_match_events_table` - Detailed match events

### League System (Added Mar 2026)
- `2026_03_09_001_create_league_registrations_table` - Registration records
- `2026_03_09_002_create_league_registration_players_table` - Player entries
- `2026_03_09_003_add_registration_fields_to_leagues_table` - Registration config
- `2025_11_27_105141_modify_stadiums_province_to_foreign_key` - Venue locations

### Club System (Added Feb 2026)
- `2026_02_27_000001_create_club_competition_teams_table` - Teams
- `2026_02_27_000002_create_club_competition_matches_table` - Matches
- `2026_02_27_000003_create_club_competition_standings_table` - Standings
- `2026_03_23_000001_extend_club_activity_for_open_play` - Open play support
- `2026_03_23_000002_create_club_member_stats_table` - Statistics
- `2026_03_25_add_score_config_to_club_activities_table` - Score configuration

### Instructor System
- `2025_11_27_134645_create_instructors_table` - Instructor profiles
- `2025_11_28_100001_add_columns_to_instructors_table` - Additional fields
- `2025_11_28_100003_create_instructor_certifications_table` - Certifications
- `2025_11_28_100004_create_instructor_reviews_table` - Reviews
- `2025_11_28_100005_create_instructor_packages_table` - Service packages
- `2025_11_28_100006_create_instructor_locations_table` - Service areas
- `2025_11_28_100007_create_instructor_schedules_table` - Availability
- `2025_11_28_100008_create_instructor_teaching_methods_table` - Methods
- `2025_11_28_100009_create_instructor_favorites_table` - Student favorites
- `2025_11_28_141913_create_booking_instructors_table` - Bookings

### Video & Learning
- `2025_11_27_135743_create_videos_table` - Video records
- `2025_11_28_135406_add_instructor_and_fields_to_videos_table` - Instructor link
- `2025_11_28_135745_add_chapters_to_videos_table` - Video chapters
- `2025_11_28_150000_create_video_comments_table` - Comments
- `2025_11_28_150001_create_video_likes_table` - Likes

### Rating Systems (OCR/OPRS/Elo)
- `2025_12_02_170002_create_ocr_matches_table` - Online competitive rating
- `2025_12_05_100005_add_match_category_to_ocr_matches_table` - Match categories
- `2025_12_02_170003_create_elo_histories_table` - Elo tracking
- `2025_12_05_100001_add_oprs_fields_to_users_table` - OPRS ratings
- `2025_12_05_100004_create_oprs_histories_table` - OPRS history

### Engagement Systems
- `2025_12_05_100002_create_challenge_results_table` - Challenges
- `2025_12_05_100003_create_community_activities_table` - Community engagement
- `2025_12_02_170004_create_user_badges_table` - Achievements
- `2025_12_10_091833_create_permission_requests_table` - Permission requests

### Content Management
- `2025_11_14_074007_create_news_table` - News/announcements
- `2025_11_18_042751_add_category_id_to_news_table` - News categories
- `2025_11_14_223000_create_pages_table` - Static pages

### Utility Systems
- `2025_11_25_000000_create_activity_logs_table` - Audit trail
- `2025_11_18_075830_create_favorites_table` - Favorites
- `2025_11_18_084957_create_reviews_table` - Reviews
- `2025_11_19_000008_create_payments_table` - Payment records
- `2025_11_20_040554_create_tempos_table` - Timing configurations
- `2025_11_25_152755_create_socials_table` - Social groups
- `2025_11_26_163130_create_social_participants_table` - Social members

---

## 6. EVENT SYSTEM COMPONENTS

### Events (12 Total)
Located in `app/Events/`

| Event | Trigger | Purpose |
|-------|---------|---------|
| **OcrMatchCreated** | New OCR match | Match creation notification |
| **OcrMatchAccepted** | Match acceptance | Player acceptance event |
| **OcrMatchConfirmed** | Match confirmation | Confirmation trigger |
| **OcrMatchResultSubmitted** | Result submission | Score recorded event |
| **MatchScored** | Match completion | General match scoring |
| **TournamentCreated** | Tournament creation | Tournament launch |
| **ClubMemberAdded** | Member join | Membership event |
| **EloVerified** | Elo verification | Verification completion |
| **EventCheckedIn** | Check-in action | Attendance recorded |
| **SkillQuizCompleted** | Quiz completion | Assessment finish |
| **SocialCreated** | Group creation | Social group launch |
| **StadiumUpdated** | Stadium change | Venue update notification |

### Listeners (9 Total - Points System)
Located in `app/Listeners/Points/`

All listeners award points on specific actions:

| Listener | Trigger Event | Points Awarded For |
|----------|---------------|--------------------|
| **AwardOcrMatchPoints** | OcrMatchResultSubmitted | Completing OCR matches |
| **AwardTournamentCreatePoints** | TournamentCreated | Creating tournaments |
| **AwardRefereeScoringPoints** | MatchScored | Officiating matches |
| **AwardExpertVerifyPoints** | EloVerified | Elo verification role |
| **AwardEventCheckinPoints** | EventCheckedIn | Event attendance |
| **AwardSocialCreatePoints** | SocialCreated | Creating social groups |
| **AwardStadiumUpdatePoints** | StadiumUpdated | Venue contributions |
| **AwardReferralPoints** | [Custom trigger] | Successful referrals |
| **AwardClubJoinPoints** | ClubMemberAdded | Club membership |

### Observers (6 Total - Model Hooks)
Located in `app/Observers/`

| Observer | Model | Hooks |
|----------|-------|-------|
| **UserObserver** | User | created, updated, deleted - Profile updates, status changes |
| **TournamentObserver** | Tournament | created, updated, deleted - Tournament lifecycle |
| **MatchObserver** | MatchModel | created, updated, deleted - Match state transitions |
| **StadiumObserver** | Stadium | created, updated - Venue updates |
| **ClubObserver** | Club | created, updated, deleted - Club management |
| **InstructorObserver** | Instructor | created, updated - Instructor profile changes |

---

## Key Architectural Patterns

### 1. **Domain Separation**
- **Tournaments**: Competition management with bracket systems
- **Leagues**: Season-based team competitions
- **Clubs**: Community organization with activities & competitions
- **Rating Systems**: OCR (online), OPRS (challenge-based), ELO (classical)
- **Engagement**: Points, badges, challenges, community activities
- **Learning**: Skill quizzes, videos, instructor bookings

### 2. **Media Handling**
- Uses Spatie MediaLibrary for tournaments, stadiums, clubs, videos
- Media collections for galleries, banners, images

### 3. **Authorization**
- Spatie Laravel Permissions package for RBAC
- JWT Auth for API authentication
- OAuth (Google, Facebook) for social login

### 4. **Timestamps & Soft Deletes**
- User model uses soft deletes
- Standard timestamps on most models

### 5. **Polymorphic Relationships**
- Favorites model (stadiums, videos, instructors, etc.)

---

## Recent Additions (Mar 2026)
- League registration system with payment tracking
- Club activity enhancement for open play
- Club member statistics tracking
- Score configuration for activities
- Match status tracking improvements

---

## Summary Statistics
- **91 Models** covering 8+ domains
- **86 Controllers** across 4 namespaces (Admin, Api, Front, Root)
- **33 Services** for business logic encapsulation
- **6 Route files** organizing web, API, and testing
- **195 Migrations** with recent focus on league/club systems
- **12 Events** driving engagement through listeners
- **9 Listeners** primarily focused on point system
- **6 Observers** monitoring model lifecycle

