# Pickleball Laravel Project - Codebase Structure Report

## Executive Summary

This is a comprehensive Laravel PHP project for a pickleball platform with extensive tournament, league, social, and rating systems. The codebase includes 120 controllers, 88 models, and 36 services totaling ~43K lines of code.

---

## 1. CONTROLLERS OVERVIEW

**Total Controllers: 120 files | 26,657 lines**

### Architecture
- **Admin (24 controllers)** - Administrative functions for system management
- **Api (32 controllers)** - RESTful API endpoints for mobile/external clients  
- **Front (34 controllers)** - Web interface controllers for main platform
- **Verifier (1 controller)** - Verification/moderation features
- **Root (25 controllers)** - Authentication, clubs, wallets, debug utilities

### 1.1 Admin Controllers (24 files)
Path: `/app/Http/Controllers/Admin/`

| Controller | Purpose |
|-----------|---------|
| DashboardController | Admin system dashboard |
| UserPermissionController | Role and permission management |
| UserImportController | Bulk user data import utilities |
| NewsController | News article content management |
| StadiumController | Stadium/venue management |
| TournamentController | Tournament CRUD and configuration |
| PageController | Static pages management |
| CategoryController | Sport category management |
| InstructorController | Instructor profile management |
| InstructorRegistrationController | Instructor registration workflows |
| VideoController | Video content management |
| OcrDisputeController | OnePickleball Championship Ranking dispute resolution |
| OcrBadgeController | Badge awards and criteria |
| QuizController | General quiz management |
| SkillQuizController | Skill assessment quiz admin |
| PointSubmissionController | Point submission validation |
| PointTaskController | Point earning task definitions |
| SpecialChallengeController | Special challenge events |
| LeagueController | League management and scheduling |
| OprsController | OnePickleball Rating Score admin |
| OprsActivityController | OPRS activity logging |
| OprsChallengeController | OPRS challenge management |

### 1.2 Api Controllers (32 files)
Path: `/app/Http/Controllers/Api/`

| Controller | Purpose |
|-----------|---------|
| AuthController | JWT authentication (login, register, refresh) |
| OcrMatchController | Match creation, results, disputes |
| OcrUserController | User ELO, badges, stats retrieval |
| OcrLeaderboardController | ELO leaderboard and rankings |
| OprsController | OPRS profile and breakdown |
| OprsLeaderboardController | OPRS rankings by level |
| MatchmakingController | Opponent suggestions, rating estimates |
| ChallengeController | Challenge submission and history |
| CommunityActivityController | Community engagement tracking |
| StadiumController | Stadium listing and details |
| LocationController | Province/location data |
| TournamentController | Tournament listing and registration |
| SocialController | Social event management |
| NewsController | News feed API |
| BookingController | Court booking management |
| RefereeController | Referee match assignments |
| RefereeProfileController | Public referee profiles |
| SkillQuizController | Skill quiz attempts and results |
| ProfileController | User profile updates |
| EventCheckinController | Event attendance tracking |
| PointController | Points balance and transactions |
| UserController | User management |
| ClubController | Club CRUD and member management |
| ClubActivityParticipantController | Activity participation (RSVP) |
| ClubCompetitionController | Club competition scheduling |
| ClubPostController | Club feed posts |
| LeagueApiController | League listings and standings |
| MediaUploadController | File/image uploads |
| OcrMatchController | Match-related API operations |
| GemController | Virtual currency wallet |

### 1.3 Front Controllers (34 files)
Path: `/app/Http/Controllers/Front/`

| Controller | Purpose |
|-----------|---------|
| HomeController | Main landing page |
| DashboardController | User dashboard |
| ProfileController | User profile management |
| NewsController | News browsing |
| AthleteManagementController | Athlete profile configuration |
| TournamentRegistrationController | Tournament sign-up workflow |
| CategoryController | Tournament category selection |
| RoundController | Tournament round navigation |
| GroupController | Tournament group/bracket viewing |
| BookingInstructorController | Instructor booking interface |
| OcrController | OCR dashboard and match history |
| RefereeController | Referee dashboard |
| RefereeProfileController | Referee profile display |
| ReferralController | Referral link generation |
| SkillQuizController | Skill assessment interface |
| QuizController | Quiz-taking interface |
| PermissionRequestController | User permission requests |
| OprVerificationController | OPRS verification workflow |
| HomeYardStadiumController | Stadium browsing and details |
| HomeYardClubController | Club discovery interface |
| HomeYardLeagueController | League discovery |
| LeagueTeamController | League team management |
| LeagueMatchController | League match viewing |
| LeagueRegistrationController | League registration |
| UserPointController | Points display and history |
| ClubPostController | Club post creation and viewing |
| ClubPostReactionController | Post reactions (likes) |
| ClubPostCommentController | Post comments |
| BookingHistoryController | Booking history and invoices |
| Tournament/TournamentController | Main tournament interface |
| Tournament/TournamentGroupController | Tournament bracket groups |
| Tournament/TournamentDrawController | Draw generation |
| Tournament/TournamentManualDrawController | Manual bracket creation |

### 1.4 Root & Other Controllers (25 files)

| Controller | Purpose |
|-----------|---------|
| AuthController | Web authentication (login/register) |
| ClubController | Club main CRUD |
| ClubActivityController | Club activity management |
| ClubActivityParticipantController | Activity participant management |
| ClubCompetitionController | Club competitions |
| ClubMatchController | Club match scheduling |
| ClubDashboardController | Club admin dashboard |
| ClubCheckinController | Club event check-in |
| ClubOpenPlayController | Club open play sessions |
| ClubLeaderboardController | Club member rankings |
| ReviewController | Stadium reviews |
| FavoriteController | Favorite stadiums/instructors |
| SocialController | Social events |
| WalletController | User wallet/payments |
| DebugController | Development debug endpoints |
| Controller | Base controller class |
| Verifier/VerifierDashboardController | Verification moderator dashboard |

---

## 2. MODELS OVERVIEW

**Total Models: 88 files | 8,183 lines**

### Core Domain Models

#### User Management
- **User** (773 lines) - Central user model; auth, roles, ratings (ELO, OPRS)
  - Relations: province, favoriteStadiums, favoriteInstructors, socialParticipants, reviews, referrals
  - Traits: HasRoles, HasApiTokens, Sluggable, SoftDeletes, JWTSubject

- **OprVerificationRequest** (178 lines) - OPRS level verification requests
- **Permission** - User permissions/authorization
- **UserBadge** (177 lines) - Achievement badges tracking
- **EloHistory** - ELO rating change history

#### Tournament System
- **Tournament** (161 lines) - Tournament events with registration
  - Relations: user, club, stadium, athletes, categories, rounds, groups, matches, referees
  - Supports: knockout brackets, third-place matches, seeding

- **TournamentAthlete** - Athlete registration for tournaments
- **TournamentCategory** - Tournament divisions/divisions
- **TournamentReferee** - Referee assignments
- **Round** - Tournament rounds
- **Group** - Tournament bracket groups
- **MatchModel** (389 lines) - Core match data structure
- **MatchEvent** (150 lines) - Match game events (scores, timeouts)

#### League System
- **League** - League/season management
  - Relations: user, club, teams, rounds
  - Supports: multiple seasons, registration workflows

- **LeagueTeam** - Team registry in leagues
- **LeagueTeamPlayer** - Team roster
- **LeagueMatch** - League matches
- **LeagueMatchGame** - Individual games within matches
- **LeagueRound** - League match rounds/weeks
- **LeagueStanding** - Team standings
- **LeagueRegistration** - League registration records
- **LeagueRegistrationPlayer** - Player registrations

#### Club System
- **Club** (131 lines) - Club/organization profiles
  - Relations: creator (User), members, provinces, activities, posts, joinRequests

- **ClubActivity** (221 lines) - Club events/activities
- **ClubActivityMatch** - Matches within activities
- **ClubActivityMatchRound** - Activity match rounds
- **ClubActivityParticipant** - Activity RSVPs
- **ClubCompetitionMatch** - Competition matches
- **ClubCompetitionTeam** - Competition teams
- **ClubCompetitionStanding** - Competition standings
- **ClubPost** - Social feed posts
- **ClubPostMedia** - Post attachments
- **ClubPostComment** - Post comments
- **ClubPostReaction** - Post reactions (likes)
- **ClubJoinRequest** - Club membership requests
- **ClubMemberStat** - Member statistics

#### Rating/Ranking Systems
- **OcrMatch** (332 lines) - OnePickleball Championship Ranking matches
  - ELO rating updates, dispute tracking, evidence uploads

- **OprsHistory** - OPRS rating change log
- **ChallengeResult** (175 lines) - Challenge submission results
- **EloService** - ELO calculation logic

#### Court/Booking System
- **Stadium** - Venue information
  - Relations: courts, bookings, pricing
  
- **Court** (151 lines) - Individual courts within stadiums
- **CourtPricing** (139 lines) - Court pricing schedules
- **Booking** (187 lines) - Court reservations
- **PosStadiumSetting** - Stadium POS configuration

#### Skills & Assessment
- **SkillDomain** - Skill assessment categories
- **SkillQuestion** - Quiz questions
- **SkillQuizAttempt** - Quiz attempt records
- **SkillQuizAnswer** - User answers
- **Quiz** - Legacy quiz system
- **SpecialChallenge** (162 lines) - Special challenge events

#### Points & Rewards
- **PointTask** (189 lines) - Point earning task definitions
- **PointSubmission** (136 lines) - User point submissions
- **UserPointTransaction** - Point transaction log
- **GemWallet** - Virtual currency wallets
- **GemTransaction** - Currency transactions

#### Community & Social
- **Social** - Social events
- **CommunityActivity** (179 lines) - Community engagement events
- **Referral** - Referral tracking
- **SocialProfileVerification** - Social account verification

#### Instructor System
- **Instructor** - Instructor profiles
- **InstructorReview** - Instructor reviews
- **InstructorCertification** - Teaching certifications
- **InstructorExperience** - Experience records
- **InstructorLocation** - Service locations
- **InstructorSchedule** - Availability
- **InstructorTeachingMethod** - Teaching approaches
- **InstructorFavorite** - User favorites
- **InstructorPackage** - Lesson packages
- **BookingInstructor** - Instructor booking records

#### Content & References
- **News** - News articles
- **Page** - Static pages
- **Video** - Video content
- **VideoComment** - Video comments
- **VideoLike** - Video engagement
- **Category** - Sport categories
- **Province** - Geographic locations

#### Other
- **ActivityLog** - Audit logging
- **EventCheckin** - Event attendance
- **Payment** - Payment records
- **Tempo** - Game/pace reference

---

## 3. SERVICES OVERVIEW

**Total Services: 36 files | 8,214 lines**

### Service Organization

#### Rating & Ranking Services
- **BadgeService** - Badge award logic
- **EloService** - ELO rating calculations
- **OprsService** - OPRS scoring system
- **ChallengeService** - Challenge submission logic

#### Tournament Services (10+ files in `/Tournament/`)
- **TournamentCrudService** - Tournament creation/editing
- **TournamentDrawService** - Bracket draw generation
- **TournamentMatchService** - Match scheduling
- **TournamentStandingService** - Ranking calculations
- **KnockoutBracketService** - Knockout bracket logic
- **BracketSeedingHelper** - Seeding algorithms
- **DrawAssignmentHelper** - Draw assignment utilities
- **MatchCreationHelper** - Match creation workflows
- **RankingQueryHelper** - Ranking query optimization
- **KnockoutBracketQuery** - Bracket queries

#### League Services
- **LeagueService** - League CRUD
- **LeagueScheduleService** - Match schedule generation
- **LeagueStandingsService** - Standing calculations
- **LeagueRegistrationService** - Registration workflows
- **LeagueAutoTeamService** - Automatic team formation

#### Club Services
- **ClubService** - Club management (implied from controllers)
- **ClubActivityService** - Activity management
- **ClubCompetitionService** - Competition scheduling
- **ClubMatchService** - Match management
- **ClubMatchmakingService** - Opponent suggestions
- **ClubMemberStatsService** - Member statistics
- **ClubScoreService** - Score calculations

#### Community & Engagement
- **CommunityService** - Community activity tracking
- **PointEarningService** - Point calculation logic
- **PointSubmissionService** - Submission validation
- **SkillQuizService** - Quiz attempt management
- **OprVerificationService** - Verification workflow

#### System Services
- **ProfileService** - User profile operations
- **SocialVerificationService** - Social account verification
- **ClubPostMediaService** - Media attachment handling
- **GemCashbackService** - Virtual currency cashback
- **GemWalletService** - Wallet operations
- **SepayService** - Payment integration
- **MediaUploadController** - File upload handling (in Controllers)

---

## 4. ROUTES OVERVIEW

### API Routes (`/routes/api.php`)

**Structure**: Modular prefix-based groups with middleware

#### Authentication Routes (lines 51-65)
```
POST   /api/auth/register          (public)
POST   /api/auth/login             (public)
POST   /api/auth/refresh-token     (public)
POST   /api/auth/logout            (protected)
GET    /api/auth/me                (protected)
GET    /api/auth/user              (protected)
```

#### OCR (OnePickleball Championship Ranking) Routes (lines 73-95)
```
# Protected
GET    /api/ocr/matches            (auth required)
POST   /api/ocr/matches
GET    /api/ocr/matches/{match}
PATCH  /api/ocr/matches/{match}/accept|reject|confirm
POST   /api/ocr/matches/{match}/result|dispute|evidence

# Public
GET    /api/ocr/users/{user}/elo|badges|stats
GET    /api/ocr/leaderboard[/distribution|/{rank}]
```

#### OPRS (OnePickleball Rating Score) Routes (lines 104-125)
```
# Protected
GET    /api/oprs/profile|breakdown|history

# Public
GET    /api/oprs/levels
GET    /api/oprs/leaderboard[/levels|/level/{level}|/distribution]
GET    /api/oprs/users/{user}
GET    /api/oprs/matchmaking/suggest/{user}

# Protected (matchmaking)
POST   /api/oprs/estimate
```

#### Challenge System Routes (lines 134-144)
```
GET    /api/challenges/available|types|history|stats
POST   /api/challenges/submit
```

#### Community Activity Routes (lines 153-165)
```
POST   /api/community/check-in|event|referral|social-activity
GET    /api/community/history|types|stats
```

#### Public Data Routes (lines 174-214)
```
GET    /api/stadiums[/{id}]
GET    /api/locations/provinces
GET    /api/tournaments[/{id}][/standings|/register]
GET    /api/socials[/{id}][/participants]
GET    /api/news[/categories|/{id}]
GET    /api/stadiums/{stadiumId}/bank-info
```

#### Booking Routes (lines 217-233)
```
POST   /api/bookings                         (public)
GET    /api/bookings/list|history|{id}      (protected)
PATCH  /api/bookings/{id}                    (protected)
DELETE /api/bookings/{id}                    (protected)
POST   /api/bookings/booking                 (protected)
POST   /api/bookings/{bookingId}/confirm|reject (auth)
GET    /api/bookings/stadium/{stadiumId}/slots-all
```

#### Referee Routes (lines 242-257)
```
GET    /api/referee/dashboard|matches|matches/{match}|matches/{match}/state
POST   /api/referee/matches/{match}/start|sync-events|end
PUT    /api/referee/matches/{match}/score

GET    /api/referees[/{referee}]
```

#### Skill Quiz Routes (lines 266-276)
```
GET    /api/skill-quiz/eligibility
POST   /api/skill-quiz/start
GET    /api/skill-quiz/attempt/{id}[/questions]
POST   /api/skill-quiz/answers|submit-quiz
GET    /api/skill-quiz/result/{id}|history
```

#### User Profile Routes (lines 284-289)
```
POST   /api/user/profile[/avatar|/email|/password]
```

#### Points System Routes (lines 316-325)
```
GET    /api/points/tasks|balance|history|submissions|challenges
POST   /api/points/submissions
```

#### Club Routes (lines 333-390)
```
GET    /api/clubs[/{club}]
POST   /api/clubs                           (protected)
PUT    /api/clubs/{club}                     (protected)
DELETE /api/clubs/{club}                     (protected)

# Member management
GET    /api/clubs/{club}/join-request-status
POST   /api/clubs/{club}/request-join|join-requests/{jr}/approve|reject
PUT    /api/clubs/{club}/members/role
DELETE /api/clubs/{club}/members

# Activities
GET|POST /api/clubs/{club}/activities[/{activity}]
PUT|DELETE /api/clubs/{club}/activities/{activity}
GET|POST|DELETE /api/clubs/{club}/activities/{activity}/rsvp

# Competition
GET|POST /api/clubs/{club}/activities/{activity}/competition/teams
DELETE /api/clubs/{club}/activities/{activity}/competition/teams/{team}
POST /api/clubs/{club}/activities/{activity}/competition/generate-schedule
PUT /api/clubs/{club}/activities/{activity}/competition/matches/{match}/score
GET /api/clubs/{club}/activities/{activity}/competition/standings|matches

# Posts
GET|POST /api/clubs/{club}/posts[/{post}]
PUT|DELETE /api/clubs/{club}/posts/{post}
POST /api/clubs/{club}/posts/{post}/pin
```

#### League Routes (lines 411-416)
```
GET    /api/leagues[/{league}]                (/{league} is public)
GET    /api/leagues/{league}/standings|schedule
```

#### Gems/Currency Routes (lines 424-429)
```
GET    /api/gems/wallet|transactions[/{transaction}]
POST   /api/gems/topup
```

### Web Routes (`/routes/web.php`)

**Key Groups** (partial - file is 18K+ lines):
- Debug endpoints (`/debug/*`)
- Frontend authentication flows
- Admin panel routes (with admin middleware)
- Tournament management interfaces
- League management
- Club management
- Referee interfaces
- Instructor booking
- Point/reward system
- News and content management

---

## 5. KEY STATISTICS

| Metric | Count | Lines |
|--------|-------|-------|
| **Controllers** | 120 | 26,657 |
| **Models** | 88 | 8,183 |
| **Services** | 36 | 8,214 |
| **API Routes** | 100+ endpoints | N/A |
| **Web Routes** | 200+ endpoints | ~10K |

### Controller Distribution
- Admin: 24 (20%)
- Api: 32 (27%)
- Front: 34 (28%)
- Root/Other: 25 (21%)
- Verifier: 1 (1%)

---

## 6. ARCHITECTURAL PATTERNS

### Authentication
- JWT-based API authentication (Sanctum/Laravel Passport style)
- Web session-based authentication for admin
- Multiple guard types: `auth:api`, `auth`, `auth:sanctum`

### Authorization
- Role-Based Access Control (RBAC) via Spatie permissions
- Admin, Referee, Verifier, User roles
- Middleware-protected routes

### Business Logic Organization
- **Service Layer**: Encapsulates complex domain logic
- **Controller Layer**: HTTP request handling only
- **Model Layer**: Data relationships and basic mutations

### Domain Areas
1. **OCR System**: ELO-based ranking with badges and disputes
2. **OPRS System**: Alternative skill-based rating with levels
3. **Tournament Management**: Bracket generation, scheduling, refereeing
4. **League Management**: Season-based team competition
5. **Club System**: Community groups with activities and competitions
6. **Instructor System**: Lesson booking and scheduling
7. **Point/Reward System**: Community engagement gamification
8. **Social Features**: Events, posts, comments, reactions

---

## 7. KEY RELATIONSHIPS

### User-Centric
```
User
├── hasMany: reviews, OcrMatches, Tournaments, Clubs (as creator)
├── belongsToMany: favoriteStadiums, favoriteInstructors, socialParticipants
├── hasOne: referralCode, wallets (UserWallet, GemWallet)
└── hasMany: tournaments, leagues, clubs
```

### Tournament Flow
```
Tournament
├── hasMany: athletes (TournamentAthlete), categories, rounds, groups
├── hasMany: matches (MatchModel), referees (TournamentReferee)
├── belongsTo: user, club, stadium
└── hasMany: results via matches
```

### Club Ecosystem
```
Club
├── hasMany: activities (ClubActivity), posts (ClubPost), joinRequests
├── belongsToMany: members (User with pivot role)
├── hasMany: matches, competitions, standings
└── belongsTo: creator (User)
```

### League Structure
```
League
├── hasMany: teams (LeagueTeam), rounds (LeagueRound)
├── hasMany: standings (via teams)
├── belongsTo: user, club
└── association with: matches, players
```

---

## File Paths Summary

### Controllers
- `/app/Http/Controllers/Admin/` (24 files)
- `/app/Http/Controllers/Api/` (32 files)  
- `/app/Http/Controllers/Front/` (34 files)
- `/app/Http/Controllers/Verifier/` (1 file)
- `/app/Http/Controllers/` (root - 25 files)

### Models
- `/app/Models/` (88 files)

### Services
- `/app/Services/` (26 files)
- `/app/Services/Tournament/` (10 files)

### Routes
- `/routes/api.php` (437 lines)
- `/routes/web.php` (18K+ lines)

---

## Notable Observations

1. **Large User Model** (773 lines) - Consider breaking into smaller modules
2. **Tournament Complexity** - Extensive tournament service layer with multiple helpers
3. **Gamification** - Strong points, badges, and challenge systems
4. **Multi-Rating Systems** - Both ELO and OPRS coexist
5. **Community Focus** - Clubs, activities, social features are extensive
6. **Admin Panel** - Significant administrative functionality
7. **Flexible Booking** - Court booking with instructors and stadiums
8. **API-First Design** - Comprehensive API with authentication
9. **Media Support** - Spatie MediaLibrary integration for files/images
10. **Soft Deletes** - SoftDeletes trait used for data retention

