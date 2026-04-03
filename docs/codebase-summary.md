# Codebase Summary

**Last Updated**: 2026-04-03
**Project**: Pickleball Platform
**Framework**: Laravel 10.10+

## Overview

Laravel-based pickleball platform managing court bookings, tournaments, instructors, referees, social activities, and competitive ranking with OPRS (OnePickleball Rating Score). Multi-tenant architecture supporting stadium owners (Home Yard), tournament organizers, referees, instructors, and end users.

## Project Structure

**File Counts (Current - Apr 2026):**
- PHP files: 360+ (Controllers 116, Models 84, Services 34, Commands 22, Policies 8, Middleware 9, Events 12+, Listeners 9, Observers 6, Form Requests 6)
  - Front/Tournament/: 9 controllers + 5 traits
- Blade templates: 273 (Admin 54, Front 53, Home-yard 65, Clubs 50, Layouts 5, User/Auth/Referee 45)
  - home-yard/tournaments/: dashboard + 25+ partials
- JS modules: 24 files (Alpine.js components for tournament dashboard, bracket editor, club activities)
- CSS stylesheets: 34 files (20 feature-specific + 14 tournament-dashboard components)
- Database migrations: 195
- Database seeders: 20
- Routes: ~600 (web.php 430+, api.php 170+)

## Core Technologies

### Backend Stack
- **PHP**: 8.1+
- **Laravel**: 10.10+
- **Database**: MySQL

### Key Packages
- **laravel/sanctum**: 3.3+ (API authentication)
- **laravel/socialite**: 5.23+ (OAuth - Google, Facebook)
- **spatie/laravel-medialibrary**: 10 (Media management)
- **spatie/laravel-permission**: 6.23+ (Role-based access)
- **phpoffice/phpspreadsheet**: 5.2+ (Excel export)

### Frontend Stack
- **Blade**: Laravel templating
- **Vite**: 5.0+ (Asset bundling)
- **Axios**: 1.6+ (HTTP client)

## Models Overview (84 Models)

### User & Auth
- `User` - User accounts with OAuth, roles, Elo rating, OPRS fields, profile data (avatar, location, province, gender), referee fields
- `ActivityLog` - User activity tracking

### Stadium & Courts
- `Stadium` - Venue profiles
- `Court` - Individual courts
- `CourtPricing` - Time-based pricing tiers
- `Booking` - Court reservations with booking_code, confirmed_at, transfer_proof
- `Province` - Geographic regions

### Tournament System
- `Tournament` - Tournament configuration
- `TournamentCategory` - Skill/age categories (singles/doubles)
- `TournamentAthlete` - Registered participants with partner_id and draw_order for doubles
- `TournamentReferee` - Referee-tournament assignments
- `Round` - Tournament rounds
- `Group` - Group stage groupings
- `GroupStanding` - Group rankings
- `MatchModel` - Individual matches with referee assignment and pair support
- `MatchEvent` - Match event records

**Instructor**: `Instructor`, `InstructorCertification`, `InstructorExperience`, `InstructorLocation`, `InstructorPackage`, `InstructorReview`, `InstructorSchedule`, `InstructorTeachingMethod`, `InstructorFavorite`, `BookingInstructor`

**Content**: `News`, `Category`, `Page`, `Video`, `VideoComment`, `VideoLike`

**Social**: `Social`, `Favorite`, `Review`, `Payment`, `Tempo`, `Referral`

### Club System (2026-02+)
- `Club` - Club management
- `ClubActivity` - Activity tracking (recurring/one-off/competition types)
- `ClubActivityParticipant` - RSVP with status & auto-promotion
- `ClubActivityMatchRound` - Casual match rounds (singles/doubles)
- `ClubActivityMatch` - Individual casual matches with scores
- `ClubActivityMatchStanding` - Per-player standings & stats
- `ClubJoinRequest` - Join request management
- `ClubPost` - Discussion posts with media support
- `ClubPostComment`, `ClubPostMedia`, `ClubPostReaction` - Post engagement
- `ClubCompetitionTeam`, `ClubCompetitionMatch`, `ClubCompetitionStanding` - Competition system

**OCR (Elo-based)**: `OcrMatch` (singles/doubles), `EloHistory`, `UserBadge`

**OPRS**: `ChallengeResult`, `CommunityActivity`, `OprsHistory`, `OprVerificationRequest`, `PermissionRequest`, `PosStadiumSetting`

**Skill Quiz (Jan 2026)**: `SkillDomain` (6 domains), `SkillQuestion` (36 questions), `SkillQuizAttempt`, `SkillQuizAnswer`, `Quiz`

**Point System (Jan 2026)**: `PointTask` (16 tasks), `PointSubmission`, `UserWallet`, `UserPointTransaction`, `SocialProfileVerification`, `SpecialChallenge`, `Event`, `EventCheckin`

**League (Feb 2026)**: `League`, `LeagueTeam`, `LeagueTeamPlayer`, `LeagueRound`, `LeagueMatch`, `LeagueMatchGame`, `LeagueStanding`, `LeagueRegistration`, `LeagueRegistrationPlayer`

**MLP League Format (Mar 2026)**: 6 sub-game doubles pairing support with enhanced round editing, player pair assignment (home_player_1/2_id, away_player_1/2_id)

**League Registration (Mar 2026)**: Payment proof upload, phone normalization, admin approval workflow, auto team generation (skill-ranked snake-draft and random modes), DB::transaction + lockForUpdate for race-condition safety

## Services Overview (34 Services)

Core (11): EloService, BadgeService, OprsService, OprVerificationService, ChallengeService, CommunityService, ProfileService, SkillQuizService, PointEarningService, PointSubmissionService, SocialVerificationService

Club & Social (8): ClubPostMediaService, ClubActivityService, ClubActivityMatchService, ClubCompetitionService, ClubMatchService, ClubMemberStatsService, ClubScoreService, WaitlistAutoPromotionService

League (5): LeagueService, LeagueScheduleService, LeagueStandingsService, LeagueAutoTeamService, LeagueRegistrationService

Tournament (11): TournamentCrudService, TournamentDrawService, TournamentMatchService, TournamentStandingService, KnockoutBracketService, KnockoutMatchBuilder, KnockoutBracketQuery, BracketSeedingHelper, DrawAssignmentHelper, MatchCreationHelper, RankingQueryHelper

Booking (1): BookingCodeService

## Controllers Overview

### Admin Controllers (23)
| Controller | Purpose |
|------------|---------|
| `DashboardController` | Admin dashboard |
| `CategoryController` | News categories |
| `InstructorController` | Instructor management |
| `InstructorRegistrationController` | Instructor approval |
| `NewsController` | News articles |
| `PageController` | Static pages |
| `StadiumController` | Stadium management |
| `TournamentController` | Tournament admin |
| `UserPermissionController` | User roles |
| `VideoController` | Video content |
| `OcrDisputeController` | OCR match dispute resolution |
| `OcrBadgeController` | OCR badge management |
| `OprsController` | OPRS admin dashboard, user management |
| `OprsChallengeController` | Challenge verification |
| `OprsActivityController` | Community activity management |
| `SkillQuizController` | Admin quiz attempt management, flag review |
| `PointTaskController` | Point task CRUD, activate/deactivate |
| `PointSubmissionController` | Submission review, approve/reject |
| `SpecialChallengeController` | Special challenge management |
| `PermissionRequestController` | Permission request management |
| `LeagueController` | League admin viewing and reporting |
| `UserImportController` | Bulk user import |
| `QuizController` | Quiz management |
| `OcrLeaderboardController` | OCR leaderboard admin |

### API Controllers (30)
| Controller | Purpose |
|------------|---------|
| `MediaUploadController` | Media file uploads |
| `InstructorReviewController` | Instructor reviews API |
| `OcrMatchController` | OCR match operations (challenge, accept, submit, confirm) |
| `OcrUserController` | OCR user profile and stats |
| `OcrLeaderboardController` | OCR leaderboard and rankings |
| `OprsController` | OPRS API (profile, breakdown, history, leaderboard) |
| `OprsLeaderboardController` | OPRS leaderboard with level filtering |
| `MatchmakingController` | Opponent suggestions based on OPRS |
| `RefereeController` | Referee API (dashboard, matches, start, score update) |
| `RefereeProfileController` | Referee profile and public directory |
| `SkillQuizController` | API quiz endpoints (domains, questions, submit) |
| `PointController` | Point earning API (tasks, balance, history, submissions, challenges) |
| `WalletController` | Wallet API (balance, transactions, formatted display) |
| `PointSubmissionController` | Submission API (create, list, filter by status) |
| `SpecialChallengeController` | Active challenges API |
| `EventCheckinController` | Event check-in API |
| `SocialController` | Social verification status and URLs |
| `ClubController` | Club CRUD, join requests, member management |
| `ClubActivityController` | Club activities API |
| `ClubActivityParticipantController` | Activity RSVP and participation |
| `ClubCompetitionController` | Club competitions API |
| `ClubPostController` | Club posts and engagement API |
| (Plus controllers for bookings, auth, leagues, etc.) |

### Front Controllers (46)
Core: HomeController, DashboardController, ProfileController, BookingHistoryController
Homeyard: HomeYardStadiumController, HomeYardTournamentController, HomeYardClubController, HomeYardLeagueController
League: LeagueTeamController, LeagueMatchController, LeagueRegistrationController
Club: ClubMatchController, ClubPostController, ClubPostCommentController, ClubPostReactionController, ClubActivityController, ClubCheckinController, ClubDashboardController, ClubLeaderboardController, ClubOpenPlayController
Tournament: CategoryController, RoundController, GroupController, AthleteManagementController, TournamentRegistrationController
Booking & Instructor: BookingInstructorController
Content: NewsController, VideoCommentController, VideoLikeController
OCR/OPRS: OcrController, OprVerificationController
Referee: RefereeController, RefereeProfileController
Points & Wallet: UserPointController, SkillQuizController, PointController, PointSubmissionController, SpecialChallengeController, WalletController
Social: ReferralController

#### Tournament Rewrite Controllers (New - Mar 2026)
| Controller | Purpose |
|------------|---------|
| `Tournament/TournamentController` | CRUD (index, create, store, edit, update, destroy, show) |
| `Tournament/TournamentAthleteController` | Athletes (index, store, update, destroy, updateStatus, approve, reject, listByStatus, bulkApprove) |
| `Tournament/TournamentDrawController` | Draw/seeding (index, draw, getResults, reset) |
| `Tournament/TournamentManualDrawController` | Manual draw (getManualDraw, saveManualDraw) |
| `Tournament/TournamentGroupController` | Groups (index, setup) |
| `Tournament/TournamentMatchController` | Matches & scoring (index, store, show, updateScore, destroy, updateSchedule, createForGroups) |
| `Tournament/TournamentRankingController` | Rankings (index, getCategoryRankings, getCategoryGroups) |
| `Tournament/TournamentBracketController` | Knockout brackets (index, getData, generate, swap) |

#### Traits (Supporting Tournament Controllers)
| Trait | Purpose |
|-------|---------|
| `DrawAuthorizationTrait` | Authorization for draw operations |
| `MatchListFormatterTrait` | Format match data for display |
| `MatchScheduleTrait` | Schedule match operations |
| `MatchScoreTrait` | Score entry and validation |
| `TournamentAthleteStatusTrait` | Athlete status management |
| `BracketAdvancementTrait` | Winner advancement in knockout brackets |
| Additional Traits | Supporting reusable functionality |

#### Other Front Controllers
| Controller | Purpose |
|------------|---------|
| `AthleteManagementController` | Athlete operations |
| `TournamentRegistrationController` | Registration flow |
| `CategoryController` | Tournament categories |
| `RoundController` | Tournament rounds |
| `GroupController` | Group management |
| `BookingInstructorController` | Instructor bookings |
| `NewsController` | News display |
| `VideoCommentController` | Video comments |
| `VideoLikeController` | Video likes |
| `OcrController` | OCR/OPRS frontend (matches, leaderboard, profile, challenges, community) |
| `RefereeController` | Referee dashboard, match officiating, score entry |
| `RefereeProfileController` | Public referee directory and profiles |
| `ReferralController` | Referral system frontend |
| `UserPointController` | User point dashboard |
| `OprVerificationController` | OPRS verification requests |
| `SkillQuizController` | Frontend quiz flow (index, start, quiz, result) |
| `PointController` | Point earning frontend (tasks, wallet, submissions) |
| `PointSubmissionController` | Submission form, history, status tracking |
| `SpecialChallengeController` | Challenge listing, details, participation |
| `WalletController` | Wallet dashboard, transaction history |
| `ClubPostController` | Club post CRUD operations |
| `ClubPostCommentController` | Club post comments |
| `ClubPostReactionController` | Club post reactions/likes |
| `ClubActivityController` | Club activity CRUD and management |

### Root Controllers (16)
AuthController, FavoriteController, ReviewController, SocialController, ClubActivityController, ClubActivityParticipantController, ClubCompetitionController, ClubMatchController, ClubOpenPlayController, WalletController, DebugController, Controller, ClubCheckinController, ClubDashboardController, ClubLeaderboardController, VerifierDashboardController

## Routes Summary

### Web Routes (`routes/web.php`)
- **Public**: Home, courts, tournaments, news, instructors, referees
- **Auth**: Login, register, OAuth (Google, Facebook)
- **User**: Dashboard, profile management, reviews, favorites
- **OCR/OPRS**: Matches, challenges, community activities
- **Home Yard**: Stadium/tournament/referee management, league management (role-protected)
- **League Management**: CRUD, team/player management, match scheduling, score entry (9 routes + actions)
- **Referee**: Dashboard, match officiating, score entry (role-protected)
- **Admin**: Full CMS access + OPRS management + league viewing (role-protected)

### API Routes (`routes/api.php`)
- **Booking**: Operations and availability
- **Instructor**: Reviews
- **Media**: Uploads
- **Video**: Interactions
- **OCR**: Match operations
- **OPRS**: Profile, breakdown, history, leaderboard (22 routes)
- **Matchmaking**: Opponent suggestions
- **Referee**: Dashboard, matches, start, score update (5 protected routes)
- **Referee Public**: List referees, profile (2 public routes)
- **Skill Quiz**: Domains, questions, submit (3 routes)
- **Point Earning**: Tasks, balance, history, submissions, challenges (6 protected routes)

## Key Features by Module

### 1. Court Booking
- Stadium listing with filters
- Court availability calendar
- Dynamic pricing tiers
- Booking creation/cancellation
- Booking stats and search

### 2. Tournament Management
- Tournament CRUD with media
- Category/round/group configuration (singles/doubles support)
- Athlete registration workflow with partner selection for doubles
- Match scheduling with pair support
- Match results and rankings with Excel export
- Doubles pair management with partner linking

### 3. Instructor Platform
- Profile with certifications
- Package management
- Review system
- Booking integration

### 4. Content Management
- News with categories
- Featured articles
- Static page builder
- Video library with engagement

### 5. OCR Ranking System (Elo-based)
- Elo-based competitive ranking (100-3000+)
- Match challenges (singles/doubles)
- Seven rank tiers (Bronze to Grandmaster)
- Achievement badge system
- Global leaderboard
- Match dispute resolution
- Evidence upload (Spatie Media)

### 6. OPRS Rating System (Multi-component)
- **Three-component scoring**:
  - Elo Rating (70% weight) - Match performance
  - Challenge Score (20% weight) - Technical skills
  - Community Score (10% weight) - Engagement
- **Seven OPR Levels**: 1.0 to 5.0+ (Beginner to Elite)
- **Challenge System** (4 types):
  - dinking_rally (10pts), drop_shot (8pts), serve_accuracy (6pts)
  - monthly_test (30-50pts, score>=70)
  - Point-based scoring with pass/fail thresholds
  - Admin verification system
- **Community Activities**:
  - Stadium check-ins (daily)
  - Event participation
  - Player referrals
  - Weekly match bonus (5+ matches)
  - Monthly challenges
- **OPRS Features**:
  - Real-time OPRS calculation
  - Change history audit log
  - Level-based leaderboards
  - Matchmaking suggestions
  - Score breakdown visualization
  - Admin adjustment tools

### 7. Referee System
- **Referee Role**: Dedicated `referee` role via Spatie Permission
- **Referee Profiles**:
  - Bio, status (active/inactive), rating
  - Matches officiated count
  - Tournament assignments history
- **Tournament Assignment**:
  - Home Yard can add/remove referees from tournaments
  - `TournamentReferee` pivot model with status tracking
- **Match-Level Assignment**:
  - Assign referees to individual matches via dropdown in match details modal
  - Display assigned referee name on match cards (all tabs)
  - `HomeYardTournamentController.getMatch()` returns tournament referees for selection
  - `HomeYardTournamentController.updateMatch()` validates and saves referee_id
- **Referee Dashboard**:
  - Stats (total/completed/upcoming matches, tournaments)
  - Upcoming matches list
- **Match Officiating**:
  - View assigned matches with filters
  - Start match (scheduled -> in_progress)
  - Enter set-by-set scores
  - Auto-calculate winner from sets
  - Complete match with final score
- **Public Directory**:
  - Browse active referees
  - View referee profiles with stats

### 8. User Profile Management
- Profile editing (name, location, province)
- Avatar upload and management
  - Supported formats: JPEG, PNG, WebP
  - Max size: 2MB, max dimensions: 2000x2000px
  - Storage: Laravel Storage (public disk)
- Email change with password verification
- Password update with current password validation
- OAuth users can set initial password
- Province relationship for location data

### 9. League Management System (Complete)
- **League Creation & Management:**
  - CRUD operations for leagues (create, read, update, delete)
  - League status tracking (draft, active, completed)
  - Sport and format configuration
  - League description and configuration
- **Team Management:**
  - Add/remove teams from leagues
  - Seed position assignment
  - Team status tracking
- **Player Roster Management:**
  - Add players to team roster
  - User search interface for player assignment
  - Player removal from roster
  - Roster size validation
- **Match Scheduling:**
  - Automatic round and match generation
  - Schedule generation based on league format
  - Round-robin or bracket scheduling
  - Round editing and modification
- **Score Tracking:**
  - Game-by-game score entry
  - Match result recording
  - Winner calculation from games
- **Standings Display:**
  - Automatic standings calculation
  - Wins, losses, and points tracking
  - Real-time updates after score entry
- **User Interface:**
  - Tab-based league detail page (Overview, Teams, Schedule, Standings)
  - URL hash persistence for tab navigation
  - AJAX-powered team/player management
  - Vanilla JS modals for operations
  - Fetch API for asynchronous updates
  - Toastr notifications for feedback
  - Vietnamese localization with proper diacritics

### 10. MLP League Format (NEW - Mar 2026)
- 6 sub-game doubles pairing format
- League association with clubs
- Enhanced round editing for format support
- Full game-by-game tracking

## Database Migrations (193 files)

### Core Tables (2014-2019)
- `users`, `password_reset_tokens`, `failed_jobs`, `personal_access_tokens`

### Platform Tables (2025)
- Permission system (Spatie)
- News, stadiums, pages
- Tournaments, athletes, categories
- Courts, bookings, pricing
- Matches, rounds, groups
- Instructors, packages, reviews
- Videos, comments, likes
- Social activities
- OCR system (matches, elo_histories, user_badges)

### OPRS Tables (2025-12-05+)
- `users` - Added OPRS fields (challenge_score, community_score, total_oprs, opr_level)
- `challenge_results` - Challenge submission records
- `community_activities` - Community engagement tracking
- `oprs_histories` - OPRS change audit log
- `ocr_matches` - Added match_category field for matchmaking
- `opr_verification_requests` - OPRS verification tracking
- `permission_requests` - User permission request management

### Profile Tables (2025-12-07)
- `users` - Added profile fields (avatar, location, province_id)
- Foreign key: province_id references provinces.id

### Referee Tables (2025-12-09)
- `users` - Added referee fields (referee_bio, referee_status, matches_officiated, referee_rating)
- `tournament_referees` - Referee-tournament assignments with status
- `matches` - Added referee_id and referee_name columns

### Doubles Support Tables (2025-12-18)
- `tournament_athletes` - Added partner_id and draw_order columns for doubles pair linking and drawing

### Club System Tables (2026-02+)
- `clubs` - Club configuration and management
- `club_activities` - Club activity tracking
- `club_join_requests` - Club join request management
- `club_posts` - Club discussion posts
- `club_post_comments` - Comments on club posts
- `club_post_media` - Media attachments in club posts
- `club_post_reactions` - Reactions/likes on posts

### Skill Quiz Tables (2026-01-03+)
- `skill_domains` - 6 fixed domains (Technical Skills, Strategy, Physical, Mental, Experience, Situations)
- `skill_questions` - 36 questions with domain_id, text, description, scale (0-3), weight
- `skill_quiz_attempts` - User attempts with total_score, elo_assigned, completion_time, is_flagged
- `skill_quiz_answers` - Individual answers with question_id, rating (0-3)
- `users` - Added quiz_completed_at, quiz_elo_assigned, can_retake_quiz_at, gender (enum: male/female, nullable)
- `quizzes` - Quiz configuration and management

### Point Earning Tables (2026-01-14+)
- `point_tasks` - 16 tasks across 4 roles with code, points, role, category, frequency, proof_type, is_active
- `point_submissions` - User proof submissions with UUID, user_id, point_task_id, status, proof_data, admin_id, admin_notes, reviewed_at, points_awarded
- `user_wallets` - User point balance with user_id, points
- `user_point_transactions` - Transaction history with user_id, points, type, description, metadata
- `social_profile_verifications` - Social platform verification with user_id, platform, profile_url, verified_at
- `special_challenges` - Time-limited challenges with title, description, points, start_date, end_date, max_participants, is_active
- `events` - Workshop/event system with UUID, title, description, location, stadium_id, start_datetime, end_datetime, points, max_attendees, is_active, qr_code_data, created_by
- `event_checkins` - User event attendance with event_id, user_id, checked_in_at, check_in_method, points_awarded

### Booking Enhancement Tables (2026-02-02+)
- `bookings` - Added booking_code (BK{courtId:3+}{date:YYMMDD}{seq:3}), confirmed_at, transfer_proof
- `users` - Added soft delete support

### Club Activity Casual Match Tables (2026-03-03)
- `club_activity_match_rounds` - Round tracking (round_number, status: pending/in_progress/completed)
- `club_activity_matches` - Match records (singles/doubles, player IDs, scores, court_number)
- `club_activity_match_standings` - Per-player stats (wins, losses, points_for, points_against)

### Club Activity Score Configuration & Status (2026-03-25)
- `club_activities` - Added `best_of` (match format: 1/3/5 sets) and `points_per_set` (default: 21)
- `club_activity_matches` - Added `score_status` (pending_confirmation/confirmed/rejected/admin_confirmed) and `score_confirmed_by` (user_id)

### Club Management API Tables (2026-03-09)
- Auto-create club post when activity is created
- Club show endpoint with join_request_status
- Full CRUD API for club activities, competitions, posts

### League Registration Tables (2026-03-09)
- `league_registrations` - Registration records with league_id, user_id, phone (normalized), payment_proof, status, approved_by, approved_at
- `league_registration_players` - Player roster from registration with league_registration_id, player_id

### League Management Tables (2026-02-25+)
- `leagues` - League configuration (name, description, sport, format, status, stadium_id, created_by)
- `league_teams` - Team enrollment with league_id, team_id, team_name, seed_position
- `league_team_players` - Player roster with team_id, player_id, player_name
- `league_rounds` - Tournament rounds with league_id, round_number, is_finished
- `league_matches` - Match records with league_id, round_id, team_1_id, team_2_id, status
- `league_match_games` - Game-by-game scores with match_id, game_number, team_1_score, team_2_score, winner_id
- `league_standings` - Calculated standings with league_id, team_id, wins, losses, points

### Knockout Bracket Tables (2026-03-13)
- `tournaments` - Added enable_third_place boolean for third-place match generation

## View Structure

### Admin (`resources/views/admin/`)
- Dashboard, categories, news, pages
- Stadiums, tournaments, users
- Instructors, videos
- OCR (disputes, badges)
- OPRS (dashboard, users, challenges, activities, reports)
- Skill Quiz (dashboard, index, show)

### Frontend (`resources/views/front/`)
- Home, courts, tournaments
- Instructors, courses
- News, social, videos
- OCR (matches, leaderboard, profile)
- Challenges (index, submit)
- Community (index, check-in)
- Skill Quiz (index, start, quiz, result)

### Components (`resources/views/components/`)
- OPRS score card
- OPRS level badge
- OPRS breakdown chart

### Home Yard (`resources/views/home-yard/`)
- Dashboard, stadiums
- Bookings, courts

#### Tournament Rewrite Views (New - Mar 2026)
- `dashboard.blade.php`, `draw.blade.php`, `bracket.blade.php` - Admin dashboard, draw/seeding, bracket display
- `tournaments_detail.blade.php`, `tabs-section.blade.php` - Public tournament detail with tabs (schedule/standings/bracket)
- Partials (20+): `_sidebar.blade.php`, `_overview.blade.php`, `_athletes*.blade.php`, `_draw*.blade.php`, `_matches*.blade.php`, `_rankings*.blade.php`, `_bracket*.blade.php`, `_mobile-tabs.blade.php`
- Front-end Partials: `_front-bracket-match.blade.php` - Read-only bracket match card (See [tournament-views-structure.md](./tournament-views-structure.md))

#### Other Home Yard Views
- Tournaments (legacy views being replaced)
- Athletes, matches, rankings (legacy views)
- Leagues (CRUD, tab-based detail view, teams, matches, standings, MLP format support)
- League Registration (form, admin approval, auto team generation)

### Club Activities (`resources/views/clubs/activities/`) [Mar 2026 Complete]
- Index: Activity listing with type badges, participant counts
- Create/Edit: Type selector, conditional field sections, form validation
- Show: Activity detail card with tabs (RSVP, Matches, Standings, Competition, Check-in, Leaderboard)
- Check-in: Real-time check-in tracking with timestamp, status management
- Leaderboard: Per-player stats (wins, losses, points) with rankings and filtering
- Partials: 12+ modular partials for type selection, RSVP, teams, schedule, standings, matches
- Matches tab: Generate modal, custom match modal, standings display, score entry
- See [club-activities-feature.md](./club-activities-feature.md) for detailed architecture

### Referee (`resources/views/referee/`)
- Dashboard with stats and upcoming matches
- Matches index with filters
- Match detail with score entry form

### Layouts (`resources/views/layouts/`)
- Admin layout, frontend layout
- Home Yard layout
- Referee layout

## Artisan Commands

### Club Activities Commands (New)
```bash
# Generate recurring meet instances (auto-generated daily at 06:00)
php artisan clubs:generate-recurring-meets [--days=7]
```

### OPRS Commands (New)
```bash
# Recalculate OPRS for all users or specific user
php artisan oprs:recalculate [--user=ID] [--dry-run]

# Process weekly match bonus for eligible users
php artisan oprs:weekly-bonus
```

### Existing Commands
```bash
# Create admin user
php artisan app:create-admin-user

# Skill quiz seeders
php artisan db:seed --class=SkillDomainSeeder
php artisan db:seed --class=SkillQuestionSeeder
```

## Test Factories (2026-02-27)

### Club Activity Factories (in tests/)
- Club, ClubActivity, ClubActivityParticipant, ClubCompetitionTeam factories

### Test Coverage (25 tests, all passing)
- **ClubActivityServiceTest** (6 tests) - RSVP confirmation, waitlist, promotion, skill validation, duplicates, instance creation
- **ClubCompetitionServiceTest** (5 tests) - Round-robin generation, odd teams, score updates, standings calculation, initialization
- **ClubActivityRsvpTest** (4 tests) - Member RSVP, non-member rejection, cancel RSVP, participant data
- **ClubCompetitionTest** (5 tests) - Team management, schedule generation, score entry, standings
- **GenerateRecurringMeetsTest** (3 tests) - Correct day generation, idempotency, inactive templates

## Entry Points

### For Users
- `/` - Homepage
- `/courts` - Court listing
- `/tournaments` - Tournament listing
- `/instructors` - Instructor listing
- `/user/profile/edit` - Profile management
- `/ocr` - OCR/OPRS system

### For Stadium Owners
- `/homeyard/dashboard` - Home Yard dashboard
- `/homeyard/stadiums` - Stadium management
- `/homeyard/tournaments` - Tournament management
- `/homeyard/tournaments/{id}/referees` - Referee assignment
- `/homeyard/leagues` - League listing and management
- `/homeyard/leagues/create` - Create new league
- `/homeyard/leagues/{league}` - League detail with tabs
- `/homeyard/leagues/{league}/edit` - Edit league

### For Referees
- `/referee/dashboard` - Referee overview and stats
- `/referee/matches` - Assigned matches list
- `/referee/matches/{id}` - Match detail and score entry

### For Admins
- `/admin/login` - Admin login
- `/admin/dashboard` - Admin panel
- `/admin/ocr/disputes` - OCR dispute management
- `/admin/ocr/badges` - Badge management
- `/admin/oprs` - OPRS dashboard
- `/admin/oprs/users` - OPRS user management
- `/admin/oprs/challenges` - Challenge verification
- `/admin/oprs/activities` - Community activity management

### For OCR/OPRS Users
- `/ocr` - OCR/OPRS home
- `/ocr/leaderboard` - Global rankings
- `/ocr/profile/{id}` - User OCR/OPRS profile
- `/ocr/challenges` - Challenge system
- `/ocr/community` - Community activities

### API Endpoints for Mobile
- `/api/points/tasks` - Get available point tasks with eligibility
- `/api/points/balance` - Get wallet balance
- `/api/points/history` - Get transaction history
- `/api/points/submissions` - Get/create submissions
- `/api/points/challenges` - Get active special challenges

## Frontend Assets (Tournament Rewrite - Mar 2026)

### JavaScript Modules (`public/assets/js/`) - 18 files
Tournament core (12): dashboard, athletes, draw (+3 mixins), matches (+api, schedule-mixin), rankings. Bracket (5): manager, data-fetcher, match-editor, score-entry, swap-editor. Utility: script.js

### CSS Stylesheets (`public/assets/css/`) - 26 files
Feature-specific (15): style, tournaments, courts, bookings, clubs, coaches, news, galleries, instructor-review. Tournament dashboard (11): layout-sidebar, components (athletes, buttons, cards, draw, forms, matches, rankings, rankings-table, rankings-row-states), bracket-tree.

## Authentication Flow

1. **Standard Auth**: Email/password via `AuthController`
2. **OAuth**: Google/Facebook via Laravel Socialite
3. **Role Check**: Spatie Permission middleware
4. **Admin**: Separate login route with admin role check

## File Storage

### Media Library (Spatie)
- Stadium images, Tournament galleries, Instructor photos, Video thumbnails, OCR match evidence

### Storage Paths
- `storage/app/public` - Public uploads, `public/storage` - Symlinked public access

## Configuration

### Key Config Files
- `config/auth.php` - Authentication guards
- `config/services.php` - OAuth credentials
- `config/permission.php` - Spatie permissions
- `config/media-library.php` - Media settings
- `config/sanctum.php` - API auth

## Development Commands

```bash
# Install dependencies
composer install
npm install

# Setup database
php artisan migrate
php artisan db:seed

# Create admin user
php artisan app:create-admin-user

# OPRS initial calculation
php artisan oprs:recalculate

# Dev server
php artisan serve
npm run dev

# Build assets
npm run build
```

## OPRS System Details

### Score Calculation
```
OPRS = (0.7 × Elo) + (0.2 × Challenge) + (0.1 × Community)
```

### OPR Levels
| Level | Name | OPRS Range |
|-------|------|------------|
| 1.0 | Beginner | 0-599 |
| 2.0 | Novice | 600-899 |
| 3.0 | Intermediate | 900-1099 |
| 3.5 | Upper Intermediate | 1100-1349 |
| 4.0 | Advanced | 1350-1599 |
| 4.5 | Pro | 1600-1849 |
| 5.0+ | Elite | 1850+ |

### Challenge Types
- **Serve Accuracy**: Target accuracy test
- **Volley Control**: Net play assessment
- **Dink Precision**: Soft game evaluation
- **Footwork Drill**: Movement assessment
- **Monthly Test**: Comprehensive skill evaluation (once per month)

### Community Activities
- **Check-in**: Daily stadium check-ins (10 points)
- **Event Participation**: Social event attendance (50 points)
- **Referral**: Player referral (100 points)
- **Weekly Matches**: 5+ matches in a week (30 points)
- **Monthly Challenge**: Special monthly objective (150 points)

## Gender-Aware Skill Level System (2026-01-15)

The skill quiz system implements gender-differentiated skill level mapping aligned with Vietnam tournament standards.

### Key Features
- Female players receive +0.5 level at same ELO
- 8 skill levels: 2.0, 2.5, 3.0, 3.5, 4.0, 4.5, 5.0, 5.5+
- Vietnamese and English level names
- Backward compatible (gender defaults to male if null)

### Implementation
- `User.gender` - enum('male', 'female'), nullable
- `SkillQuizService.eloToSkillLevel($elo, $gender)` - Gender-aware mapping with localized names
- Constants: `ELO_THRESHOLDS_MALE`, `ELO_THRESHOLDS_FEMALE`, `SKILL_LEVEL_NAMES`
### ELO Mapping
| ELO | Male | Female | VN Male | VN Female |
|-----|------|--------|---------|-----------|
| <700 | 2.0 | 2.5 | Moi choi | Tap su |
| 700-899 | 2.5-3.0 | 3.0-3.5 | Tap su-So cap | So cap-Trung cap |
| 900-1099 | 3.5-4.0 | 4.0-4.5 | Trung cap-Cao cap | Cao cap-Ban chuyen |
| 1100-1299 | 4.5-5.0 | 5.0-5.5 | Ban chuyen-Chuyen nghiep | Chuyen nghiep-Dinh cao |
| >=1300 | 5.5+ | 5.5+ | Dinh cao | Dinh cao |
## Related Docs
[PDR](./project-overview-pdr.md) | [Code Standards](./code-standards.md) | [Architecture](./system-architecture.md) | [Tournament Views](./tournament-views-structure.md) | [Roadmap](./project-roadmap.md) | [Referee API](./api-referee.md)
