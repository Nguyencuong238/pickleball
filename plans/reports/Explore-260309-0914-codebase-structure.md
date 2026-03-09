# LARAVEL CODEBASE STRUCTURE - PICKLEBALL PROJECT

## OVERVIEW
- Framework: Laravel 10, PHP 8.1+
- Database: MySQL
- Key Packages: Spatie (permissions, media), JWT Auth, Sanctum
- Total Controllers: 91 (Admin: 24, Api: 28, Front: 35, Other: 4)
- Total Models: 85+
- Total Services: 18
- Events: 12
- Listeners: 9
- Middleware: 9
- Policies: 8

---

## 1. CONTROLLERS (app/Http/Controllers/)

### ADMIN CONTROLLERS (24 files)

CategoryController (98 LOC)
  - Manage tournament categories
  
DashboardController (35 LOC)
  - Admin dashboard overview
  
InstructorController (398 LOC)
  - CRUD operations for instructors
  - Manage instructor profiles, certifications, schedules
  
InstructorRegistrationController (32 LOC)
  - Handle instructor registration process
  
LeagueController (32 LOC)
  - League management (basic CRUD)
  
NewsController (99 LOC)
  - Manage news/announcements
  
OcrBadgeController (95 LOC)
  - Manage OCR match badges and achievements
  
OcrDisputeController (200 LOC)
  - Handle OCR match disputes and resolutions
  
OprsActivityController (122 LOC)
  - Manage OPRS activity records
  
OprsChallengeController (135 LOC)
  - Manage OPRS challenges
  
OprsController (142 LOC)
  - OPRS (skill rating system) management
  
PageController (108 LOC)
  - Manage static pages
  
PermissionRequestController (93 LOC)
  - Handle permission requests from users
  
PointSubmissionController (137 LOC)
  - Manage point submissions and approvals
  
PointTaskController (55 LOC)
  - Create and manage point earning tasks
  
QuizController (134 LOC)
  - Manage quizzes for skill assessment
  
SkillQuizController (192 LOC)
  - Advanced skill quiz management
  
SpecialChallengeController (110 LOC)
  - Manage special challenges/tournaments
  
StadiumController (90 LOC)
  - Manage stadium information
  
TournamentController (343 LOC)
  - Full tournament CRUD and management
  
UserImportController (184 LOC)
  - Bulk import users (CSV/batch)
  
UserPermissionController (122 LOC)
  - Manage user role permissions
  
VideoController (126 LOC)
  - Manage educational/tutorial videos

### API CONTROLLERS (28 files)

AuthController (152 LOC)
  - Methods: register, login, refreshToken
  - JWT-based authentication
  
BookingController (918 LOC)
  - Methods: index, history, store, bookingCourt, transfer, cancel
  - 3 creation paths: Api, HomeController, HomeYardTournamentController
  - Handles court booking with payment/transfer/cancellation
  - Complex booking logic including pricing, time slots, cancellation
  
ChallengeController (112 LOC)
  - Methods: types, available
  - Challenge management and filtering
  
ClubActivityController (192 LOC)
  - Methods: index, store, show
  - Manage club activities/events
  
ClubActivityParticipantController (95 LOC)
  - Methods: index, store
  - Manage participant RSVPs for club activities
  
ClubCompetitionController (186 LOC)
  - Methods: teams, addTeam
  - Manage competition teams within club activities
  
ClubController (384 LOC)
  - Methods: index, store, show, update, destroy
  - Full club management (create, list, details, update)
  
ClubPostController (211 LOC)
  - Methods: index, show, store, update, destroy
  - Club activity posts/announcements
  
CommunityActivityController (198 LOC)
  - Methods: types, checkIn
  - Community engagement check-ins
  
EventCheckinController (196 LOC)
  - Methods: getEvents, checkin, history
  - Event attendance tracking
  
InstructorReviewController (255 LOC)
  - Methods: store, update, destroy
  - Manage instructor ratings/reviews
  
LeagueApiController (99 LOC)
  - Methods: index, show, standings
  - League information and standings
  
LocationController (28 LOC)
  - Methods: getProvinces
  - Location/province data
  
MatchmakingController (90 LOC)
  - Methods: suggest, estimateChange
  - AI matchmaking suggestions and ELO estimation
  
MediaUploadController (86 LOC)
  - Methods: uploadMedia, deleteMedia
  - File upload/management
  
NewsController (68 LOC)
  - Methods: index, show, categories
  - News retrieval
  
OcrLeaderboardController (118 LOC)
  - Methods: index, byRank, distribution
  - OCR match rankings and statistics
  
OcrMatchController (546 LOC)
  - Methods: index, store, show, accept, reject, submitResult
  - Core OCR match creation and result submission
  
OcrUserController (103 LOC)
  - Methods: elo, badges, stats
  - User OCR statistics
  
OprsController (144 LOC)
  - Methods: levels, profile
  - OPRS rating system (skill rating)
  
OprsLeaderboardController (142 LOC)
  - Methods: index, byLevel
  - OPRS rankings
  
PointController (426 LOC)
  - Methods: tasks, balance, submit, claim
  - Point system management
  
ProfileController (245 LOC)
  - Methods: show, updateProfile, updateAvatar, updateSocial
  - User profile data
  
RefereeController (748 LOC)
  - Methods: dashboard, matches, showMatch, submit, accept, reject
  - Referee match scoring and management
  
RefereeProfileController (110 LOC)
  - Methods: index, show
  - Referee profile information
  
SkillQuizController (365 LOC)
  - Methods: eligibility, start, submit, history
  - Skill quiz execution and scoring
  
SocialController (70 LOC)
  - Methods: index, show, participants
  - Social activities
  
StadiumController (104 LOC)
  - Methods: index, show, getBankInfo
  - Stadium information and details
  
TournamentController (248 LOC)
  - Methods: index, show, standings, register
  - Tournament information and registration
  
UserController (54 LOC)
  - Methods: destroy
  - User management

### FRONT CONTROLLERS (35 files)

AthleteManagementController (388 LOC)
  - Manage tournament athletes, filtering, stats
  
BookingHistoryController (42 LOC)
  - Display user booking history
  
BookingInstructorController (45 LOC)
  - Handle instructor booking creation
  
CategoryController (159 LOC)
  - Tournament category management UI
  
ClubPostCommentController (114 LOC)
  - Comment management for club posts
  
ClubPostController (213 LOC)
  - Front-end club post creation/viewing
  
ClubPostReactionController (80 LOC)
  - Like/reaction toggling on posts
  
DashboardController (25 LOC)
  - User dashboard
  
GroupController (177 LOC)
  - Tournament group management
  
HomeController (1007 LOC) - LARGE FILE
  - Home page, search, filtering, FAQ, terms
  - Booking creation via bookingCourt method
  
HomeYardClubController (174 LOC)
  - Club creation and listing
  
HomeYardLeagueController (228 LOC)
  - League creation and management
  
HomeYardStadiumController (166 LOC)
  - Stadium creation and listing
  
HomeYardTournamentController (5169 LOC) - VERY LARGE FILE
  - Tournament creation, management, bracket generation
  - Complex tournament logic including:
    * searchBookings, getBookingDetails
    * Bracket generation for various formats
    * Match scheduling and round management
    * Booking management within tournaments
  
LeagueMatchController (79 LOC)
  - League match updates
  
LeagueTeamController (175 LOC)
  - League team management
  
NewsController (18 LOC)
  - News display
  
OcrController (529 LOC)
  - OCR match listing and management UI
  
OprVerificationController (128 LOC)
  - OPRS verification submission UI
  
PermissionRequestController (44 LOC)
  - User permission requests
  
ProfileController (185 LOC)
  - User profile edit UI
  
QuizController (218 LOC)
  - Quiz UI and submission
  
RefereeController (666 LOC)
  - Referee dashboard, match scoring UI
  
RefereeProfileController (95 LOC)
  - Referee profile display
  
ReferralController (30 LOC)
  - Referral program
  
RoundController (157 LOC)
  - Tournament round management
  
SkillQuizController (195 LOC)
  - Skill quiz UI
  
TournamentRegistrationController (239 LOC)
  - Tournament registration UI
  
UserPointController (215 LOC)
  - User points and tasks UI
  
VideoCommentController (74 LOC)
  - Video comments
  
VideoLikeController (40 LOC)
  - Video likes

### OTHER CONTROLLERS (4 files)

AuthController (342 LOC) - Front auth
  - Guest registration, login, OAuth
  
ClubActivityController (214 LOC)
  - Club activity management
  
ClubActivityParticipantController (93 LOC)
  - Participant management
  
ClubCompetitionController (186 LOC)
  - Club competition setup
  
ClubController (351 LOC)
  - Club CRUD operations
  
ClubMatchController (198 LOC)
  - Club match scheduling
  
Controller (12 LOC)
  - Base controller
  
DebugController (35 LOC)
  - Debug endpoints
  
FavoriteController (167 LOC)
  - Favorite stadium/instructor management
  
ReviewController (246 LOC)
  - Stadium and instructor reviews
  
SocialController (173 LOC)
  - Social features (posts, interactions)
  
Verifier/VerifierDashboardController (132 LOC)
  - Verification admin dashboard
  
WalletController (58 LOC)
  - User wallet/payment management

---

## 2. MODELS (app/Models/) - 85+ files

### Core User Models
User (761 LOC)
  Relationships:
    - hasMany: bookings, payments, badges, points, referrals
    - HasOne: wallet, instructor
    - BelongsToMany: clubs, favorites, reviews
    - HasMany: ocrMatches (as challenger/opponent), tournaments
  Key Attributes:
    - elo_rating, elo_rank, ocr_wins, ocr_losses
    - challenge_score, community_score, opr_level
    - referral_code, athlete_types, is_elo_verified

Club (131 LOC)
  Relationships:
    - BelongsTo: creator (User)
    - BelongsToMany: members, provinces
    - HasMany: activities, joinRequests, posts
  Methods: getMemberRole, isManagement, isAdmin, isMember

### Booking Models
Booking (187 LOC)
  Relationships:
    - BelongsTo: court, user
  Key: booking_code format = BK{courtId:3+}{date:YYMMDD}{seq:3}
  Status: pending, confirmed, completed, cancelled

Court (151 LOC)
  Relationships:
    - BelongsTo: stadium
    - HasMany: bookings, pricing
  Contains: court name, capacity, features

CourtPricing (139 LOC)
  Relationships:
    - BelongsTo: court
  Pricing time slots and rates

BookingInstructor (30 LOC)
  Links instructor to bookings

### Tournament Models
Tournament (153 LOC)
  Relationships:
    - BelongsTo: user, club
    - HasMany: athletes, categories, rounds, groups, matches
  Key Attributes: start_date, end_date, is_ocr, is_watch, status

TournamentAthlete (81 LOC)
  Relationships:
    - BelongsTo: tournament, user
    - HasOne: result
  Status: registered, paired, completed

TournamentCategory (95 LOC)
  Relationships:
    - BelongsTo: tournament
    - HasMany: athletes
  Division/category tracking

TournamentReferee (81 LOC)
  Link between referee and tournament

Round (89 LOC)
  Relationships:
    - BelongsTo: tournament
    - HasMany: matches, groups
  Round structure

Group (115 LOC)
  Relationships:
    - BelongsTo: tournament, round
    - HasMany: athletes
  Grouping for round-robin

### OCR Match Models
OcrMatch (332 LOC) - CORE MATCH MODEL
  Relationships:
    - BelongsTo: challenger, opponent, resultSubmitter, referee
    - HasMany: games, events
  Status: pending, confirmed, scored, completed, disputed
  Contains: ELO change, set scores, match metadata

MatchModel (389 LOC)
  Relationships:
    - BelongsTo: tournament, user, referee
    - HasMany: refereeMatches, events
  Tournament match tracking

MatchEvent (150 LOC)
  Relationships:
    - BelongsTo: match, ocrMatch
  Match event logging

LeagueMatchGame (61 LOC)
  Relationships:
    - BelongsTo: leagueMatch
  Game score within league match

### Club Activity Models
ClubActivity (182 LOC)
  Relationships:
    - BelongsTo: club, creator, parent (self-referential)
    - HasMany: participants, matches, roundMatches, standings, competitionTeams, posts
  Types: meetup, tournament, league, competition

ClubActivityParticipant (35 LOC)
  Relationships:
    - BelongsTo: activity, user
  Status: confirmed, waitlisted

ClubActivityMatch (55 LOC)
  Relationships:
    - BelongsTo: round
    - BelongsTo: player1, player2, player3, player4 (Users)
  4-player match tracking

ClubActivityMatchRound (29 LOC)
  Relationships:
    - BelongsTo: activity
    - HasMany: matches

ClubActivityMatchStanding (37 LOC)
  Relationships:
    - BelongsTo: activity, user
  Standings/rankings

### Club Competition Models
ClubCompetitionTeam (51 LOC)
  Relationships:
    - BelongsTo: activity, captain (User)
    - HasMany: homeMatches, awayMatches, standing

ClubCompetitionMatch (53 LOC)
  Relationships:
    - BelongsTo: activity, homeTeam, awayTeam, winnerTeam

ClubCompetitionStanding (47 LOC)
  Relationships:
    - BelongsTo: activity, team

### Club Post Models
ClubPost (129 LOC)
  Relationships:
    - BelongsTo: club, author, activity, pinnedByUser
    - HasMany: media, reactions, comments, allComments
  Activity/match announcements

ClubPostMedia (112 LOC)
  Media files for posts

ClubPostComment (53 LOC)
  Relationships:
    - BelongsTo: post, user, parent (self-referential)
    - HasMany: replies
  Comment threading

ClubPostReaction (39 LOC)
  Like/reaction tracking

ClubJoinRequest (28 LOC)
  Relationships:
    - BelongsTo: club, user
  Request to join club

### League Models
League (108 LOC)
  Relationships:
    - HasMany: teams, rounds, matches, standings
  League structure

LeagueTeam (58 LOC)
  Relationships:
    - BelongsTo: league
    - HasMany: players, standings
  Team in league

LeagueTeamPlayer (30 LOC)
  Link players to league teams

LeagueMatch (67 LOC)
  Relationships:
    - BelongsTo: league
    - HasMany: games
  Match between teams

LeagueRound (37 LOC)
  Season/round structure

LeagueStanding (75 LOC)
  Relationships:
    - BelongsTo: league, team
  Team standings

### Event Models
Event (206 LOC)
  Relationships:
    - BelongsTo: stadium
    - HasMany: checkins, referrals
  Stadium events/activities

EventCheckin (78 LOC)
  Relationships:
    - BelongsTo: event, user
  Event attendance tracking

### Rating & Points Models
OprVerificationRequest (178 LOC)
  OPRS rating verification requests

OprsHistory (95 LOC)
  Relationships:
    - BelongsTo: user
  OPRS rating history tracking

EloHistory (77 LOC)
  Relationships:
    - BelongsTo: user
  ELO rating change history

UserBadge (177 LOC)
  Relationships:
    - BelongsTo: user
  Achievement badges (FIRST_WIN, STREAK, etc.)

PointSubmission (136 LOC)
  Relationships:
    - BelongsTo: user, task, verifier
  Point earning submissions

PointTask (189 LOC)
  Relationships:
    - HasMany: submissions
  Earning tasks (join club, check in, etc.)

UserPointTransaction (65 LOC)
  Point balance tracking

UserWallet (79 LOC)
  Relationships:
    - BelongsTo: user
  Payment wallet for bookings

### Instructor Models
Instructor (121 LOC)
  Relationships:
    - BelongsTo: user
    - HasMany: reviews, certifications, schedules, packages, locations, experiences
  Instructor profiles

InstructorCertification (31 LOC)
  Certifications/credentials

InstructorSchedule (28 LOC)
  Teaching availability

InstructorPackage (42 LOC)
  Lesson packages

InstructorLocation (29 LOC)
  Teaching locations

InstructorExperience (35 LOC)
  Experience levels

InstructorTeachingMethod (29 LOC)
  Teaching methodologies

InstructorReview (37 LOC)
  Relationships:
    - BelongsTo: user, instructor
  Instructor ratings

InstructorFavorite (33 LOC)
  User favorite instructors

### Quiz Models
Quiz (27 LOC)
  Basic quiz model

SkillDomain (74 LOC)
  Skill assessment domains

SkillQuestion (68 LOC)
  Quiz questions

SkillQuizAnswer (60 LOC)
  Answer tracking

SkillQuizAttempt (123 LOC)
  Relationships:
    - BelongsTo: user
    - HasMany: answers
  Quiz attempt history

### Other Models
Challenge (implicit from ChallengeService)
ChallengeResult (175 LOC)
  Relationships:
    - BelongsTo: user, challenger, opponent, verifier

Community Activity (179 LOC)
  Check-in and community engagement records

Social (49 LOC)
  Social activity posts

Payment (165 LOC)
  Relationships:
    - BelongsTo: user
  Booking payment records

Review (53 LOC)
  Relationships:
    - BelongsTo: user
  Stadium/venue reviews

Referral (56 LOC)
  Relationships:
    - BelongsTo: referrer, referredUser
  Referral tracking

Video (98 LOC)
  Educational videos

VideoComment (50 LOC)
  Video comments

VideoLike (28 LOC)
  Video likes

News (47 LOC)
  News/announcements

Category (56 LOC)
  Post categories

Stadium (97 LOC)
  Relationships:
    - HasMany: courts, events, reviews, favorites
  Venue/court facility

Province (11 LOC)
  Provinces/locations

Page (31 LOC)
  CMS pages

Tempo (21 LOC)
  Tempo/time management

PermissionRequest (29 LOC)
  User permission requests

SocialProfileVerification (105 LOC)
  Social media verification

Favorite (23 LOC)
  User favorites (stadium/instructor)

ActivityLog (31 LOC)
  Action logging

PosStadiumSetting (50 LOC)
  POS/payment settings

---

## 3. SERVICES (app/Services/) - 18 files

BadgeService (310 LOC)
  Purpose: Award achievement badges to users
  Key Methods:
    - checkBadgesAfterMatch(User, OcrMatch, bool)
    - checkStreakBadges(User)
    - awardBadgeIfNotExists(User, string)
  Handles: First win, win streaks, match count milestones

ChallengeService (273 LOC)
  Purpose: Manage challenges/competitions
  Key Methods:
    - Create, update, list challenges
    - Calculate challenge results
    - Track challenge progress

ClubActivityService (118 LOC)
  Purpose: Club activity management
  Key Methods:
    - rsvp(ClubActivity, User)
    - Managing participants and waitlists

ClubCompetitionService (343 LOC)
  Purpose: Club competition bracket generation
  Key Methods:
    - generateBracket, generateStandings
    - Team pairing and match scheduling

ClubMatchService (407 LOC)
  Purpose: Club match generation and management
  Key Methods:
    - generateMatches(ClubActivity, string format, int courtCount)
    - Match format handling (round-robin, single-elim, doubles)
    - Court/time scheduling

ClubPostMediaService (105 LOC)
  Purpose: Media file handling for club posts
  Key Methods:
    - Upload, delete, manage media attachments

CommunityService (462 LOC)
  Purpose: Community engagement (check-ins, social)
  Key Methods:
    - checkIn(User, Stadium) -> CommunityActivity
    - canCheckInToday(User, Stadium)
    - Tracks community scores and engagement

EloService (292 LOC)
  Purpose: ELO rating calculation and updates
  Key Methods:
    - calculateEloChange(int old, int opp, bool won)
    - updateEloRating(User, OcrMatch)
    - Badge eligibility based on ELO

LeagueScheduleService (155 LOC)
  Purpose: League match scheduling
  Key Methods:
    - generateRoundRobin(League)
    - Match scheduling for league rounds

LeagueService (231 LOC)
  Purpose: League management
  Key Methods:
    - Create, manage, track leagues
    - Team management and registration

LeagueStandingsService (210 LOC)
  Purpose: League standings calculations
  Key Methods:
    - initializeStandings(League)
    - updateStandings(LeagueMatch)
    - Sort and rank teams

OprVerificationService (235 LOC)
  Purpose: OPRS rating verification
  Key Methods:
    - createRequest(User, array data)
    - verifySubmission(OprVerificationRequest)
    - Manage verification workflow

OprsService (312 LOC)
  Purpose: OPRS rating system (skill-based rating)
  Key Methods:
    - calculateOprChange(User, array context)
    - updateOprRating(User)
    - Level assignments (beginner, intermediate, advanced)
    - Different from ELO: based on skill assessment

PointEarningService (309 LOC)
  Purpose: Point system for gamification
  Key Methods:
    - awardPoints(User, string taskCode, array metadata)
    - Track point balance and transactions
    - Task types: join club, check-in, referee, submit results

PointSubmissionService (369 LOC)
  Purpose: Manage user point submissions
  Key Methods:
    - Submit point earning requests
    - Verify and approve submissions
    - Reject with reasons

ProfileService (110 LOC)
  Purpose: User profile operations
  Key Methods:
    - updateBasicInfo(User, array data)
    - Avatar, social link updates

SkillQuizService (742 LOC) - LARGEST SERVICE
  Purpose: Skill assessment quiz system
  Key Methods:
    - eligibilityCheck(User) -> bool
    - startQuiz(User, SkillDomain) -> SkillQuizAttempt
    - submitAnswers(SkillQuizAttempt, array answers)
    - calculateScore, getRecommendedElo
  Constants:
    - MIN_TIME: 3 min, RECOMMENDED: 8-10 min, MAX: 15 min
    - ELO range: 650-1500
    - Score range: 25%-95%
  Complexity: Question selection, scoring, ELO correlation

SocialVerificationService (137 LOC)
  Purpose: Verify social media profiles
  Key Methods:
    - Verify social account links
    - Track verification status

---

## 4. EVENTS (app/Events/) - 12 files

ClubMemberAdded (18 LOC)
  Triggered: When user joins club
  Listeners: Award points

EloVerified (19 LOC)
  Triggered: When ELO rating is confirmed
  Listeners: Grant verification badge

EventCheckedIn (18 LOC)
  Triggered: User checks into stadium event
  Listeners: Award community points

MatchScored (18 LOC)
  Triggered: OCR match result submitted
  Listeners: Update ELO, badges, points

OcrMatchAccepted (20 LOC)
  Triggered: Match opponent accepts challenge
  Listeners: Notify players

OcrMatchConfirmed (20 LOC)
  Triggered: Both players confirm match result
  Listeners: Process result, update ratings

OcrMatchCreated (20 LOC)
  Triggered: New OCR match initiated
  Listeners: Notifications

OcrMatchResultSubmitted (20 LOC)
  Triggered: Result submitted by referee
  Listeners: Validate, process scores

SkillQuizCompleted (18 LOC)
  Triggered: User completes skill quiz
  Listeners: Award completion points, update recommendations

SocialCreated (18 LOC)
  Triggered: Social activity posted
  Listeners: Award social points

StadiumUpdated (18 LOC)
  Triggered: Stadium information changed
  Listeners: Award manager points

TournamentCreated (18 LOC)
  Triggered: New tournament created
  Listeners: Award tournament points

---

## 5. LISTENERS (app/Listeners/) - 9 files

All listeners in Points/ subdirectory:

AwardClubJoinPoints (25 LOC)
  Listens: ClubMemberAdded
  Awards: Points for joining club

AwardEventCheckinPoints (24 LOC)
  Listens: EventCheckedIn
  Awards: Community engagement points

AwardExpertVerifyPoints (27 LOC)
  Listens: EloVerified
  Awards: Verification badge points

AwardOcrMatchPoints (30 LOC)
  Listens: MatchScored
  Awards: Points for OCR match completion

AwardRefereeScoringPoints (24 LOC)
  Listens: MatchScored
  Awards: Referee scoring points

AwardReferralPoints (48 LOC)
  Listens: User registration via referral
  Awards: Referral bonus points

AwardSocialCreatePoints (27 LOC)
  Listens: SocialCreated
  Awards: Social activity points

AwardStadiumUpdatePoints (25 LOC)
  Listens: StadiumUpdated
  Awards: Stadium update points

AwardTournamentCreatePoints (27 LOC)
  Listens: TournamentCreated
  Awards: Tournament creation points

---

## 6. MIDDLEWARE (app/Http/Middleware/) - 9 files

Authenticate (17 LOC)
  Redirect unauthenticated to login

EncryptCookies (17 LOC)
  Standard Laravel cookie encryption

PreventRequestsDuringMaintenance (17 LOC)
  Block requests during maintenance mode

RedirectIfAuthenticated (30 LOC)
  Redirect authenticated users away from auth pages

TrimStrings (19 LOC)
  Standard string trimming

TrustHosts (20 LOC)
  Trust specific hosts for proxies

TrustProxies (28 LOC)
  Trust proxy headers (X-Forwarded-For, etc.)

ValidateSignature (22 LOC)
  Validate signed URLs

VerifyCsrfToken (17 LOC)
  CSRF protection

---

## 7. POLICIES (app/Policies/) - 8 files

ClubPolicy (82 LOC)
  Methods:
    - view, create, update, delete
    - Checks user is member/admin

ClubPostCommentPolicy (32 LOC)
  Methods:
    - view, update, delete
    - Checks user is author or moderator

ClubPostPolicy (92 LOC)
  Methods:
    - view, create, update, delete
    - Checks user is member and has posting rights

PointSubmissionPolicy (63 LOC)
  Methods:
    - view, verify, reject
    - Admin verification of point claims

PointTaskPolicy (63 LOC)
  Methods:
    - view, create, update, delete
    - Admin task management

SpecialChallengePolicy (49 LOC)
  Methods:
    - view, manage
    - Challenge participation control

TournamentPolicy (59 LOC)
  Methods:
    - view, create, register, manage
    - Tournament access control

VideoCommentPolicy (14 LOC)
  Methods:
    - delete
    - Comment moderation

---

## 8. OBSERVERS (app/Observers/) - 6 files

ClubObserver (72 LOC)
  Hooks:
    - creating, created, updating, deleting
  Actions: Generate slug, cache invalidation, logging

InstructorObserver (72 LOC)
  Hooks: Profile sync, rating updates

MatchObserver (25 LOC)
  Hooks: Match status updates, logging

StadiumObserver (72 LOC)
  Hooks: Stadium data sync, event dispatch

TournamentObserver (72 LOC)
  Hooks: Tournament lifecycle, bracket generation

UserObserver (32 LOC)
  Hooks: User lifecycle, wallet creation

---

## 9. PROVIDERS (app/Providers/) - 6 files

AppServiceProvider (92 LOC)
  Registers: Services, bindings, macros
  Key: Sets up helper functions, storage URL helper

AuthServiceProvider (46 LOC)
  Registers: Policies, gates, permissions
  Uses: Spatie Permission package

BroadcastServiceProvider (19 LOC)
  WebSocket/broadcast configuration

EventServiceProvider (103 LOC)
  Event-listener mappings
  Registers: All 12 events with 9+ listeners

HelperServiceProvider (34 LOC)
  Helper function registration

RouteServiceProvider (61 LOC)
  Route registration and middleware setup

---

## 10. CONSOLE COMMANDS (app/Console/Commands/) - 20 files

CheckPermissionRequests (28 LOC)
  Processes pending permission requests

CheckWeeklyMatchBonusCommand (112 LOC)
  Weekly bonus point calculations

CreateAdminUser (52 LOC)
  Bootstrap admin user creation

DeleteOldTempFiles (49 LOC)
  Cleanup temporary files

FixMissingReferralCodes (43 LOC)
  Data migration for referral codes

FormatBookingTimes (77 LOC)
  Data migration for time formatting

FormatCourtPricingTimes (77 LOC)
  Data migration for pricing times

GenerateRecurringMeets (67 LOC)
  Generate recurring club events

GenerateReferralCodes (36 LOC)
  Generate missing referral codes

GenerateTournamentSlugs (65 LOC)
  Generate URL slugs for tournaments

GenerateUserSlugs (66 LOC)
  Generate URL slugs for users

LinkInstructorsToUsers (136 LOC)
  Link instructor records to user accounts

MigrateOpeningHoursCommand (83 LOC)
  Data migration for opening hours format

MigrateStorageToSpaces (348 LOC)
  Migrate files from storage to DigitalOcean Spaces

OcrAutoConfirmCommand (100 LOC)
  Auto-confirm OCR matches after timeout

OprsRecalculateCommand (142 LOC)
  Recalculate OPRS ratings for all users

ProcessWeeklyBonusCommand (50 LOC)
  Weekly point bonus processing

ScheduleTasksSetup (42 LOC)
  Scheduler configuration

TestTournamentOcr (377 LOC)
  Test/debug OCR tournament generation

UpdateMatchStatus (92 LOC)
  Update pending match statuses

UpdateOldMatchesStatus (50 LOC)
  Historical data status updates

UpdateStadiumSlugs (58 LOC)
  Generate URL slugs for stadiums

---

## 11. HTTP RESOURCES (app/Http/Resources/) - 14 files

API response transformers using Laravel Resource pattern:

BookingResource (46 LOC)
  Transforms: Booking model to API response
  Includes: Court, user, pricing details

CourtResource (37 LOC)
  Transforms: Court data with availability

NewsResource (32 LOC)
  Transforms: News model with images

PointSubmissionResource (54 LOC)
  Transforms: Point submission with task info

PointTaskResource (46 LOC)
  Transforms: Task with submission count

PointTransactionResource (29 LOC)
  Transforms: Point balance changes

ProvinceResource (25 LOC)
  Transforms: Province/location data

ReviewResource (30 LOC)
  Transforms: Review with ratings

SocialResource (35 LOC)
  Transforms: Social activity data

SpecialChallengeResource (34 LOC)
  Transforms: Challenge details

StadiumBookingResource (26 LOC)
  Transforms: Stadium with booking info

StadiumResource (54 LOC)
  Transforms: Stadium with details, ratings, reviews

TournamentResource (42 LOC)
  Transforms: Tournament with categories, athletes

UserResource (40 LOC)
  Transforms: User profile with stats

---

## 12. HTTP REQUESTS (app/Http/Requests/) - 6 files

Form request validation classes:

ChallengeSubmitRequest (62 LOC)
  Validates: Challenge submission data

OcrMatchResultRequest (49 LOC)
  Validates: OCR match result submission

OcrMatchStoreRequest (56 LOC)
  Validates: OCR match creation

SkillQuizAnswerRequest (75 LOC)
  Validates: Quiz answer submission

StoreClubPostRequest (41 LOC)
  Validates: Club post creation

UpdateClubPostRequest (31 LOC)
  Validates: Club post update

---

## 13. TRAITS (app/Traits/) - 2 files

SyncMediaCollection (157 LOC)
  Purpose: Manage media files with Spatie MediaLibrary
  Methods: Sync media collections on model updates
  Usage: Tournament, Club, User avatar/banner

Sluggable (56 LOC)
  Purpose: Auto-generate URL slugs
  Methods: Generate and maintain unique slugs
  Usage: User, Tournament, Club, Stadium

---

## 14. JOBS (app/Jobs/) - 2 files

CancelExpiredTransferBookings (46 LOC)
  Purpose: Auto-cancel expired transfer bookings
  Trigger: Scheduled via cron

CancelUnpaidBooking (45 LOC)
  Purpose: Auto-cancel unpaid bookings after timeout
  Trigger: Scheduled via queue

---

## 15. NOTIFICATIONS (app/Notifications/) - 1 file

OcrMatchNotification (100 LOC)
  Purpose: Notify users of OCR match events
  Channels: Email, database, SMS
  Events:
    - Match created
    - Match confirmed
    - Result submitted
    - Dispute raised

---

## 16. HELPERS (app/Helpers/) - 1 file

helpers.php (21 LOC)
  Key Functions:
    - storage_url(path) - Generate S3/storage URLs
    - format_duration(hours) - Format duration display

---

## 17. EXCEPTIONS (app/Exceptions/) - 1 file

Handler (30 LOC)
  Purpose: Exception handling and rendering
  Configuration: Error responses, logging

---

## KEY ARCHITECTURAL PATTERNS

1. **Three-Tier Architecture**
   - Controllers: Request handling
   - Services: Business logic
   - Models: Data access

2. **Event-Driven Architecture**
   - Events dispatched on model changes
   - Listeners handle side effects (points, badges, notifications)

3. **Policy-Based Authorization**
   - Policies control access to resources
   - Checked in controllers via authorize()

4. **Repository Pattern**
   - Models use Eloquent directly
   - Services encapsulate business logic

5. **Observer Pattern**
   - Model observers for lifecycle hooks
   - Auto-generation of slugs, logging, cache invalidation

6. **Resource Pattern**
   - API Resources transform models to JSON
   - Consistent API response format

7. **Form Request Validation**
   - Centralized validation rules
   - Reusable across controllers

---

## CRITICAL DEPENDENCIES & RELATIONSHIPS

Booking System:
  - 3 creation paths: Api/BookingController, Front/HomeController, Front/HomeYardTournamentController
  - booking_code format: BK{courtId:3+}{date:YYMMDD}{seq:3}
  - Uses DB::transaction + lockForUpdate for sequence generation

Match System:
  - OcrMatch (competitive 1v1 matches)
  - MatchModel (tournament matches)
  - LeagueMatch (league competition)
  - ClubActivityMatch (club internal matches)

Rating Systems:
  - ELO: Competitive 1v1 match rating (650-1500 range)
  - OPRS: Skill-based rating (separate system)
  - Community Score: Engagement metrics

Point System:
  - Point tasks with various earning methods
  - Point submissions for approval
  - Point transactions tracking
  - Listeners award points on various events

---

## SUMMARY STATISTICS

- Total PHP files in app/: ~180+
- Largest controller: HomeYardTournamentController (5169 LOC)
- Largest service: SkillQuizService (742 LOC)
- Largest model: User (761 LOC)
- Total models: 85+
- Total services: 18
- Total controllers: 91
- Event-listener pairs: 12 events + 9 listeners
- Policies: 8
- Observers: 6
- Middleware: 9

