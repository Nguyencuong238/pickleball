# Codebase Summary

**Last Updated**: 2025-12-18
**Project**: Pickleball Platform
**Framework**: Laravel 10.10+

## Overview

Laravel-based pickleball platform managing court bookings, tournaments, instructors, referees, social activities, and competitive ranking with OPRS (OnePickleball Rating Score). Multi-tenant architecture supporting stadium owners (Home Yard), tournament organizers, referees, instructors, and end users.

## Project Structure

```
pickleball/
├── app/
│   ├── Console/Commands/       # Artisan commands (4 commands)
│   ├── Exceptions/             # Exception handlers
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Admin panel controllers (15)
│   │   │   ├── Api/            # API endpoints (8)
│   │   │   └── Front/          # Public frontend controllers (16)
│   │   ├── Middleware/         # HTTP middleware
│   │   └── Kernel.php          # HTTP kernel
│   ├── Models/                 # Eloquent models (42)
│   ├── Policies/               # Authorization policies
│   ├── Services/               # Business logic (6 services)
│   └── Providers/              # Service providers
├── bootstrap/                  # Framework bootstrap
├── config/                     # Configuration files
├── database/
│   ├── factories/              # Model factories
│   ├── migrations/             # Database migrations (90+)
│   └── seeders/                # Database seeders
├── docs/                       # Project documentation
├── public/                     # Public assets
├── resources/
│   └── views/
│       ├── admin/              # Admin panel views (13 subdirs)
│       ├── auth/               # Authentication views
│       ├── components/         # Blade components (OPRS components)
│       ├── front/              # Public frontend views (19 subdirs)
│       ├── home-yard/          # Stadium owner views (7 subdirs)
│       ├── referee/            # Referee dashboard views (2 subdirs)
│       ├── layouts/            # Layout templates (includes referee layout)
│       └── vendor/             # Third-party views
├── routes/
│   ├── api.php                 # API routes (22 OPRS routes)
│   └── web.php                 # Web routes (11 admin OPRS routes)
├── storage/                    # File storage
└── tests/                      # Test suites
```

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

## Models Overview (42 Models)

### User & Auth
- `User` - User accounts with OAuth, roles, Elo rating, OPRS fields, profile data, referee fields
- `ActivityLog` - User activity tracking

### Stadium & Courts
- `Stadium` - Venue profiles
- `Court` - Individual courts
- `CourtPricing` - Time-based pricing tiers
- `Booking` - Court reservations
- `Province` - Geographic regions

### Tournament System
- `Tournament` - Tournament configuration
- `TournamentCategory` - Skill/age categories (singles/doubles)
- `TournamentAthlete` - Registered participants with partner_id for doubles
- `TournamentReferee` - Referee-tournament assignments
- `Round` - Tournament rounds
- `Group` - Group stage groupings
- `GroupStanding` - Group rankings
- `MatchModel` - Individual matches with referee assignment and pair support

### Instructor System
- `Instructor` - Coach profiles
- `InstructorCertification` - Credentials
- `InstructorExperience` - Work history
- `InstructorLocation` - Service areas
- `InstructorPackage` - Service offerings
- `InstructorReview` - Student feedback
- `InstructorSchedule` - Availability
- `InstructorTeachingMethod` - Teaching styles
- `InstructorFavorite` - User favorites
- `BookingInstructor` - Coaching bookings

### Content System
- `News` - Articles
- `Category` - News categories
- `Page` - Static pages
- `Video` - Video content
- `VideoComment` - User comments
- `VideoLike` - Video engagement

### Social & Engagement
- `Social` - Social events
- `Favorite` - User bookmarks
- `Review` - Venue reviews
- `Payment` - Payment records
- `Tempo` - Temporary data

### OCR System (Elo-based)
- `OcrMatch` - Ranked matches (singles/doubles)
- `EloHistory` - Elo rating change records
- `UserBadge` - Achievement badges

### OPRS System (New)
- `ChallengeResult` - Technical skill challenge records
- `CommunityActivity` - Community engagement activities
- `OprsHistory` - OPRS change audit log

### Skill Quiz System (New)
- `SkillQuestion` - Quiz questions with domain, scale, weight
- `SkillQuizAttempt` - User attempts with scores, ELO, completion time, flags
- `SkillQuizAnswer` - Individual answers with rating (0-3)

## Services Overview

### Business Logic Services
- `EloService` - Elo rating calculations, K-factor management, match processing
- `BadgeService` - Badge awarding, streak tracking, progress calculations
- `OprsService` - OPRS calculation (Elo 70% + Challenge 20% + Community 10%), level mapping
- `ChallengeService` - Challenge submission, verification, point awarding
- `CommunityService` - Activity tracking, check-ins, weekly bonuses
- `ProfileService` - Profile updates, avatar management, email/password changes
- `SkillQuizService` - Quiz logic, scoring, cross-validation, ELO calculation

## Controllers Overview

### Admin Controllers (15)
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

### API Controllers (10)
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
| `RefereeApiController` | Referee API (dashboard, matches, start, score update) |
| `RefereePublicController` | Public referee directory and profiles |
| `SkillQuizController` | API quiz endpoints (domains, questions, submit) |

### Front Controllers (17)
| Controller | Purpose |
|------------|---------|
| `HomeController` | Homepage, listings, booking |
| `DashboardController` | User/Home Yard dashboard |
| `ProfileController` | Profile management (edit, avatar, email, password) |
| `HomeYardStadiumController` | Stadium owner CRUD |
| `HomeYardTournamentController` | Tournament + referee management, match-level referee assignment |
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
| `SkillQuizController` | Frontend quiz flow (index, start, quiz, result) |

### Root Controllers (5)
| Controller | Purpose |
|------------|---------|
| `AuthController` | Authentication (login, register, OAuth) |
| `FavoriteController` | Favorites toggle |
| `ReviewController` | Stadium reviews |
| `SocialController` | Social events |
| `Controller` | Base controller |

## Routes Summary

### Web Routes (`routes/web.php`)
- **Public**: Home, courts, tournaments, news, instructors, referees
- **Auth**: Login, register, OAuth (Google, Facebook)
- **User**: Dashboard, profile management, reviews, favorites
- **OCR/OPRS**: Matches, challenges, community activities
- **Home Yard**: Stadium/tournament/referee management (role-protected)
- **Referee**: Dashboard, match officiating, score entry (role-protected)
- **Admin**: Full CMS access + OPRS management (role-protected)

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
- **Challenge System**:
  - Skill tests (serve, volley, dink, etc.)
  - Monthly comprehensive test
  - Point-based scoring with thresholds
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

## Database Migrations (90+ files)

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

### OPRS Tables (2025-12-05)
- `users` - Added OPRS fields (challenge_score, community_score, total_oprs, opr_level)
- `challenge_results` - Challenge submission records
- `community_activities` - Community engagement tracking
- `oprs_histories` - OPRS change audit log
- `ocr_matches` - Added match_category field for matchmaking

### Profile Tables (2025-12-07)
- `users` - Added profile fields (avatar, location, province_id)
- Foreign key: province_id references provinces.id

### Referee Tables (2025-12-09)
- `users` - Added referee fields (referee_bio, referee_status, matches_officiated, referee_rating)
- `tournament_referees` - Referee-tournament assignments with status
- `matches` - Added referee_id and referee_name columns

### Doubles Support Tables (2025-12-18)
- `tournament_athletes` - Added partner_id column for doubles pair linking

### Skill Quiz Tables (2026-01-03)
- `skill_domains` - 6 fixed domains (Technical Skills, Strategy, Physical, Mental, Experience, Situations)
- `skill_questions` - 36 questions with domain_id, text, description, scale (0-3), weight
- `skill_quiz_attempts` - User attempts with total_score, elo_assigned, completion_time, is_flagged
- `skill_quiz_answers` - Individual answers with question_id, rating (0-3)
- `users` - Added quiz_completed_at, quiz_elo_assigned, can_retake_quiz_at

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
- Tournaments, athletes (includes referee assignment)
- Bookings, courts

### Referee (`resources/views/referee/`)
- Dashboard with stats and upcoming matches
- Matches index with filters
- Match detail with score entry form

### Layouts (`resources/views/layouts/`)
- Admin layout, frontend layout
- Home Yard layout
- Referee layout

## Artisan Commands

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

## Authentication Flow

1. **Standard Auth**: Email/password via `AuthController`
2. **OAuth**: Google/Facebook via Laravel Socialite
3. **Role Check**: Spatie Permission middleware
4. **Admin**: Separate login route with admin role check

## File Storage

### Media Library (Spatie)
- Stadium images
- Tournament galleries
- Instructor photos
- Video thumbnails
- OCR match evidence

### Storage Paths
- `storage/app/public` - Public uploads
- `public/storage` - Symlinked public access

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

## Related Documentation

- [Project Overview PDR](./project-overview-pdr.md)
- [Code Standards](./code-standards.md)
- [System Architecture](./system-architecture.md)
- [Project Roadmap](./project-roadmap.md)
- [Referee API Documentation](./api-referee.md)

## Unresolved Questions

None. Codebase structure documented with OPRS and Referee systems.
