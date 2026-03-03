# Pickleball Platform - Project Roadmap

**Last Updated:** 2026-03-02
**Current Version:** 1.7.0
**Project:** Pickleball Platform

## Executive Summary

Pickleball Platform is a comprehensive web application built with Laravel 10 for managing pickleball courts, tournaments, instructors, referees, and competitive ranking. The project has successfully delivered core platform features, the OCR (OnePickleball Championship Ranking) system with Elo rating, the advanced OPRS (OnePickleball Rating Score) multi-component rating system, and the Referee System for match officiating.

---

## Development Phases

### Phase 1: Core Platform (COMPLETED - 2025 Q1-Q3)
**Status:** ✅ Complete
**Progress:** 100%

Core platform infrastructure and primary features.

**Completed Features:**
- [x] User authentication (email/password, OAuth - Google, Facebook)
- [x] Role-based access control (admin, home_yard, user)
- [x] Stadium and court management
- [x] Court booking system with dynamic pricing
- [x] Tournament system (creation, categories, athletes, matches)
- [x] Instructor platform (profiles, packages, reviews, booking)
- [x] Social activities and events
- [x] Video content library with engagement
- [x] News and CMS (articles, categories, static pages)
- [x] Media library integration (Spatie)
- [x] Responsive frontend interface

---

### Phase 2: OCR Ranking System (COMPLETED - 2025-12-02)
**Status:** ✅ Complete
**Progress:** 100%

Competitive ranking system with Elo rating and achievements.

**Completed Features:**
- [x] Elo rating system (100-3000+ range)
- [x] Seven rank tiers (Bronze to Grandmaster)
- [x] Match challenge system (singles/doubles)
- [x] Match workflow (challenge → accept → play → submit → confirm)
- [x] Global leaderboard with filtering
- [x] Achievement badge system
  - [x] First win badge
  - [x] Win streak badges (3, 5, 10)
  - [x] Match milestone badges (10, 50, 100 matches)
  - [x] Rank achievement badges
- [x] Elo history tracking
- [x] Admin dispute resolution
- [x] Evidence upload for matches
- [x] K-factor adjustment based on player experience
- [x] Team Elo calculation for doubles

**Technical Implementation:**
- `EloService` - Elo calculation engine with dynamic K-factors
- `BadgeService` - Achievement tracking and badge awarding
- `OcrMatch` model with state machine pattern
- API endpoints for match operations
- Frontend views for matches, leaderboard, and profiles
- Admin panel for dispute resolution

---

### Phase 2.5: OPRS Rating System (COMPLETED - 2025-12-05)
**Status:** ✅ Complete
**Progress:** 100%

Multi-component rating system extending OCR with technical skills and community engagement.

**Completed Features:**
- [x] Three-component scoring: Elo (70%) + Challenge (20%) + Community (10%)
- [x] Seven OPR Levels: 1.0 to 5.0+ (Beginner to Elite)
- [x] Challenge System:
  - [x] Five challenge types (serve, volley, dink, footwork, monthly test)
  - [x] Point-based scoring with pass/fail thresholds
  - [x] Admin verification workflow
  - [x] Monthly test limitation
  - [x] Challenge history and statistics
- [x] Community Activities:
  - [x] Stadium check-ins (daily per location)
  - [x] Event participation tracking
  - [x] Player referral system
  - [x] Weekly match bonus (5+ matches)
  - [x] Monthly challenge objectives
  - [x] Activity history and statistics
- [x] OPRS Features:
  - [x] Real-time OPRS calculation
  - [x] Change history audit log
  - [x] Level-based leaderboards with filtering
  - [x] Matchmaking with OPRS-similar opponents
  - [x] Score breakdown visualization
  - [x] Admin adjustment and management tools
- [x] API Endpoints (22 routes)
- [x] Admin Panel (dashboard, users, challenges, activities, reports)
- [x] Frontend Views (challenges, community, OPRS components)
- [x] Artisan Commands (recalculate, weekly bonus)

**Technical Implementation:**
- `OprsService` - Core OPRS calculation
- `ChallengeService` - Challenge submission and verification
- `CommunityService` - Activity tracking and bonuses
- Three new models: `ChallengeResult`, `CommunityActivity`, `OprsHistory`
- API controllers: `OprsController`, `OprsLeaderboardController`, `MatchmakingController`
- Admin controllers: `OprsController`, `OprsChallengeController`, `OprsActivityController`
- Blade components: OPRS score card, level badge, breakdown chart

**Database Changes:**
- Added OPRS fields to users: `challenge_score`, `community_score`, `total_oprs`, `opr_level`
- Created `challenge_results` table
- Created `community_activities` table
- Created `oprs_histories` table
- Updated `ocr_matches` with `match_category`

---

### Phase 2.6: Referee System (COMPLETED - 2025-12-09)
**Status:** Complete
**Progress:** 100%

Referee management system for tournament match officiating.

**Completed Features:**
- [x] Referee role with Spatie Permission
- [x] Referee profile fields (bio, status, rating, matches count)
- [x] Tournament referee assignment by Home Yard
- [x] Referee dashboard with statistics
- [x] Match assignment and filtering
- [x] Match score entry (set-by-set)
- [x] Auto-calculate winner from sets
- [x] Match status transitions (scheduled -> in_progress -> completed)
- [x] Public referee directory and profiles
- [x] Dedicated referee layout and views

**Technical Implementation:**
- `RefereeController` - Dashboard, match list, score entry
- `RefereeProfileController` - Public referee directory
- `TournamentReferee` model - Assignment pivot table
- User model extensions: referee relationships and fields
- MatchModel extensions: referee assignment and helper methods
- 4 database migrations for referee tables and fields
- Referee dashboard layout and views

**Database Changes:**
- Added `referee_bio`, `referee_status`, `matches_officiated`, `referee_rating` to users table
- Created `tournament_referees` table for assignments
- Added `referee_id`, `referee_name` to matches table

---

### Phase 3: Enhanced Features (COMPLETED - 2026-02-27)
**Status:** ✅ Complete
**Progress:** 100%

#### Completed Features
- [x] User profile management with avatar upload (Dec 7)
- [x] Email and password update functionality (Dec 7)
- [x] Referee system with match officiating (Dec 9)
- [x] Doubles pair selection for tournament categories (Dec 18)
- [x] Skill assessment quiz system (Jan 3, 2026)
- [x] Gender-aware skill level mapping (Jan 15, 2026)
- [x] Point earning system with 16 tasks (Jan 14, 2026)
  - [x] 4 role-based task categories (user, home_yard, referee, expert_host)
  - [x] Wallet system with transaction history
  - [x] Social platform verification (Facebook, YouTube, TikTok)
  - [x] Special challenges with time limits
  - [x] Event check-in system with QR codes
  - [x] Admin approval workflow for submissions
  - [x] 3 proof types: Image, Link, QR Code
- [x] Booking code system and history (Feb 7, 2026)
  - [x] Booking code generation: BK{courtId:3+}{date:YYMMDD}{seq:3}
  - [x] Booking confirmation tracking (confirmed_at)
  - [x] Transfer proof system for booking transfers
  - [x] Booking history and cancellation management
- [x] Club system with posts, comments, reactions (Feb 2026)
  - [x] Club creation and management
  - [x] Club posts with media support
  - [x] Comments and reactions on posts
  - [x] Club activity tracking
- [x] League Management System - Phase 4 (Feb 25, 2026)
  - [x] 10 Blade views for league CRUD operations
  - [x] Tab system for league overview, teams, schedule, standings
  - [x] URL hash persistence for navigation
  - [x] Team management with player roster
  - [x] Match scheduling with AJAX score entry
  - [x] League standings calculation and display
  - [x] User search for player management
  - [x] XSS prevention: @json() escaping, textContent DOM manipulation
  - [x] Vanilla JS modals and fetch() API integration
  - [x] Toastr notifications for user feedback
  - [x] Vietnamese UI text with diacritics
- [x] Club Activities ReClub-Style Upgrade (Complete - Feb 27, 2026)
  - [x] Phase 1: Database migrations - Activity types, participants, competition tables (Complete)
  - [x] Phase 2: Models & Services - ClubActivity, Participant, Competition models + services (Complete)
  - [x] Phase 3: Controllers & Routes - RSVP, competition, participant management endpoints (Complete)
  - [x] Phase 4: Views & UI - 12 partials, type selector, RSVP panel, competition panel (Complete - Feb 27)
  - [x] Phase 5: Scheduled Command - Auto-generate recurring activity instances (Complete)
  - [x] Phase 6: Testing - Unit/integration tests for all phases (Complete - 25 tests passing)
- [x] Club Activity Casual Matches System (Complete - Mar 3, 2026)
  - [x] Database: 3 new tables (match_rounds, matches, standings)
  - [x] Models: ClubActivityMatchRound, ClubActivityMatch, ClubActivityMatchStanding
  - [x] Service: ClubMatchService with 3 match generation algorithms (singles_rr, rotating_doubles, fixed_doubles)
  - [x] Controller: ClubMatchController with 7 AJAX endpoints
  - [x] UI: Matches tab with generate/custom modals, standings display, score entry
  - [x] Features: Round-robin singles, rotating partner doubles, fixed pair doubles

#### Planned Features
- [ ] Online payment integration (MoMo, VNPay, ZaloPay)
- [ ] Real-time notifications
  - [ ] Match invitations
  - [ ] Booking confirmations
  - [ ] Tournament updates
- [ ] Advanced analytics dashboard
  - [ ] User engagement metrics
  - [ ] Revenue tracking
  - [ ] Popular time slots
- [ ] Email notifications
- [ ] Mobile-responsive optimizations

#### Current Focus
- Payment gateway research and integration planning
- Notification system architecture design
- Analytics dashboard requirements gathering

---

### Phase 4: Mobile & Performance (PLANNED - 2026 Q2)
**Status:** 📋 Planned
**Target:** 2026 Q3

#### Mobile Development
- [ ] Progressive Web App (PWA) support
- [ ] Native mobile app (React Native or Flutter)
- [ ] Mobile-optimized booking flow
- [ ] Push notifications

#### Performance Optimization
- [ ] Redis caching implementation
- [ ] Database query optimization
- [ ] CDN for media delivery
- [ ] Lazy loading and code splitting
- [ ] API response optimization

---

### Phase 5: Platform Expansion (FUTURE - 2026 Q3+)
**Status:** 📋 Future
**Target:** 2026 Q4

#### Advanced Features
- [ ] Multi-region support
- [ ] Equipment marketplace
- [ ] Community forums
- [ ] Live match streaming
- [ ] OCR team rankings
- [ ] Professional tournament integration
- [ ] Coaching certification programs
- [ ] Video analysis tools

#### Enterprise Features
- [ ] White-label solution
- [ ] API for third-party integrations
- [ ] Advanced reporting and exports
- [ ] Multi-language support

---

## Feature Inventory

### Core Features (Completed)
- ✅ User Management & Authentication
- ✅ Stadium & Court Management
- ✅ Court Booking System
- ✅ Tournament Management
- ✅ Instructor Platform
- ✅ Social Activities
- ✅ Video Content Library
- ✅ News & CMS
- ✅ OCR Ranking System

### Recent Additions (Dec 2025 - Feb 2026)
- OCR Elo system with Elo history and badges (Dec 2)
- OPRS multi-component rating with challenges & community (Dec 5)
- User profile management with avatar and location (Dec 7)
- Referee system with match officiating (Dec 9)
- Doubles pair selection for tournaments (Dec 18)
- Skill assessment quiz system with gender-aware mapping (Jan 2026)
- Point earning system (16 tasks, wallet, verification) (Jan 2026)
- Booking code system with confirmation & transfer proof (Feb 2026)
- Club system (posts, comments, reactions, activities) (Feb 2026)
- League management with AJAX scoring and standings (Feb 25, 2026)
- Club activity RSVP with auto-promotion, competitions (Feb 27, 2026)
- Club activity casual matches with 3 generation algorithms (Mar 3, 2026)

### In Development
- 🔄 Payment integration
- 🔄 Notification system
- 🔄 Analytics dashboard

### Planned
- 📋 Mobile applications
- 📋 Performance optimizations
- 📋 Platform expansion features

---

## Technology Stack

### Backend
- **Framework:** Laravel 10.10+
- **Language:** PHP 8.1+
- **Database:** MySQL 8.0+
- **Authentication:** Laravel Sanctum, Socialite
- **Media:** Spatie Media Library
- **Permissions:** Spatie Laravel Permission
- **Export:** PhpSpreadsheet

### Frontend
- **Views:** Blade Templates
- **Build:** Vite 5
- **HTTP Client:** Axios

### Infrastructure
- **Web Server:** Nginx/Apache
- **Queue:** Laravel Queue (planned: Redis)
- **Cache:** File-based (planned: Redis)
- **Storage:** Local (planned: S3/CDN)

---

## Success Metrics

### User Engagement
- Monthly active users
- Court booking conversion rate
- Tournament participation rate
- OCR match completion rate
- Average session duration

### Business Metrics
- Total bookings per month
- Revenue per stadium
- Instructor booking rate
- Tournament registrations
- OCR system adoption rate

### Quality Metrics
- Average stadium rating: > 4.0/5.0
- Instructor satisfaction: > 4.5/5.0
- System uptime: > 99.5%
- Page load time: < 3 seconds
- Mobile responsiveness score: > 90

### OCR Metrics
- Total ranked matches played
- Average matches per active user
- Elo rating distribution
- Badge achievement rate
- Dispute resolution time: < 24 hours

### OPRS Metrics (New)
- Total OPRS calculations performed
- Average OPRS score by level
- Challenge completion rate
- Community activity engagement rate
- Weekly bonus claim rate
- Level distribution balance

---

## Technical Debt & Maintenance

### Current Technical Debt
1. **Database Optimization:** Add missing indexes for frequent queries
2. **Code Coverage:** Increase test coverage from ~40% to 80%
3. **API Documentation:** Generate comprehensive API documentation
4. **Error Handling:** Standardize error responses across API endpoints

### Planned Refactoring
1. **Service Layer:** Extract complex business logic from controllers
2. **Repository Pattern:** Implement repository pattern for data access
3. **Event System:** Add event listeners for notifications
4. **Queue Jobs:** Move time-consuming tasks to queue

---

## Risk Management

| Risk | Impact | Likelihood | Mitigation |
|------|--------|-----------|-----------|
| Payment Gateway Integration Issues | High | Medium | Thorough testing, sandbox environment, fallback options |
| Scalability Bottlenecks | High | Medium | Performance monitoring, caching strategy, database optimization |
| Security Vulnerabilities | Critical | Low | Regular security audits, dependency updates, penetration testing |
| Data Loss | Critical | Low | Regular backups, database replication, disaster recovery plan |
| Third-party Service Outages | Medium | Medium | Graceful degradation, service monitoring, alternative providers |

---

## Milestones

### Q4 2025 (Completed)
| Milestone | Status | Due Date | Progress |
|-----------|--------|----------|----------|
| OCR System Launch | Complete | 2025-12-02 | 100% |
| OPRS System Launch | Complete | 2025-12-05 | 100% |
| User Profile Management | Complete | 2025-12-07 | 100% |
| Referee System | Complete | 2025-12-09 | 100% |
| Doubles Pair Selection | Complete | 2025-12-18 | 100% |

### Q1 2026 (Current)
| Milestone | Status | Due Date | Progress |
|-----------|--------|----------|----------|
| Skill Assessment Quiz | Complete | 2026-01-03 | 100% |
| Gender-Aware Skill Levels | Complete | 2026-01-15 | 100% |
| Point Earning System | Complete | 2026-01-14 | 100% |
| Booking Code System | Complete | 2026-02-07 | 100% |
| Club Management System | Complete | 2026-02-14 | 100% |
| League Management (Phase 4) | Complete | 2026-02-25 | 100% |
| Club Activity Casual Matches | Complete | 2026-03-03 | 100% |
| Payment Gateway Integration | 🔄 In Progress | 2026-03-31 | 30% |
| Notification System Design | Planned | 2026-03-15 | 0% |


### Q2 2026
| Milestone | Status | Due Date | Progress |
|-----------|--------|----------|----------|
| Native Mobile App Beta | 📋 Planned | 2026-06-30 | 0% |
| Redis Caching | 📋 Planned | 2026-05-31 | 0% |
| CDN Integration | 📋 Planned | 2026-06-15 | 0% |

---

## Compliance & Standards

### Code Standards
- Laravel best practices and conventions
- PSR-12 coding style
- Type declarations required
- PHPDoc comments for public methods
- Service layer for complex business logic

### Security Standards
- CSRF protection enabled
- XSS prevention (Blade escaping)
- SQL injection prevention (Eloquent ORM)
- Input validation on all forms
- Role-based access control

### Testing Standards
- Unit tests for services and models
- Feature tests for controllers
- Integration tests for workflows
- Target coverage: 80%+

---

## Dependencies

### Required
- PHP 8.1+
- Composer
- MySQL 8.0+
- Node.js 18+
- Web server (Nginx/Apache)

### Optional
- Redis (for caching and queues)
- S3-compatible storage (for media)
- CDN (for performance)

---

## Documentation

### Available Documentation
- [Project Overview & PDR](./project-overview-pdr.md)
- [Codebase Summary](./codebase-summary.md)
- [Code Standards](./code-standards.md)
- [System Architecture](./system-architecture.md)

### Planned Documentation
- API Reference (Postman collection)
- Deployment Guide
- Administrator Manual
- User Guide

---

## Change Log

### Version 1.8.0 (2026-03-03)

#### Major Features Added
- **Club Activity Casual Matches:** Lightweight match system for non-competition club activities
  - 3 match generation algorithms: Singles Round-Robin, Rotating Doubles, Fixed Doubles
  - Automatic player pairing with duplicate prevention
  - Multi-round tournament scheduling with court distribution
  - Score tracking per match with automatic standings calculation
  - Per-player statistics: matches played, wins, losses, point differential

#### Technical Implementation
- 3 new models: `ClubActivityMatchRound`, `ClubActivityMatch`, `ClubActivityMatchStanding`
- `ClubMatchService` with match generation logic for all 3 formats
- `ClubMatchController` with 7 AJAX endpoints:
  - GET index - Load all rounds and matches
  - POST generate - Generate match schedule
  - PUT completeRound - Mark round as finished
  - DELETE reset - Clear all matches
  - PUT score - Save match score and recalculate standings
  - GET standings - Get current standings
  - POST customMatch - Create ad-hoc match
- New Blade partials: generate modal, custom match modal, standings display
- Matches tab added to club activity detail page

#### Database Changes (3 tables)
- `club_activity_match_rounds` - Round tracking with status
- `club_activity_matches` - Match records (singles/doubles) with player IDs, scores
- `club_activity_match_standings` - Per-player standings with win/loss/point tracking

#### Routes
- POST /clubs/{club}/activities/{activity}/matches/generate
- PUT /clubs/{club}/activities/{activity}/matches/rounds/{round}/complete
- DELETE /clubs/{club}/activities/{activity}/matches/reset
- PUT /clubs/{club}/activities/{activity}/matches/{match}/score
- GET /clubs/{club}/activities/{activity}/matches/standings
- POST /clubs/{club}/activities/{activity}/matches/custom
- GET /clubs/{club}/activities/{activity}/matches (index)

---

### Version 1.7.0 (2026-02-25)

#### Major Features Added
- **League Management System:** Complete league management for Home Yard operators
  - League CRUD operations: create, list, edit, delete
  - League status transitions (draft, active, completed)
  - Team management: add teams, manage roster, assign players
  - Match scheduling: automatic round/match generation
  - Score tracking: AJAX score entry for matches and games
  - League standings: automatic calculation and display
  - Tab-based UI: Overview, Teams, Schedule (Matches), Standings
  - URL hash persistence for tab navigation
  - User search interface for player/team member assignment

#### Technical Implementation
- 3 new models: `League`, `LeagueTeam`, `LeagueRound`, `LeagueMatch`, `LeagueMatchGame`, `LeagueTeamPlayer`, `LeagueStanding`
- 3 services: `LeagueService`, `LeagueScheduleService`, `LeagueStandingsService`
- 3 controllers: `HomeYardLeagueController` (Front), `LeagueTeamController` (Front), `LeagueMatchController` (Front), `LeagueController` (Admin)
- 10 Blade views in `resources/views/home-yard/leagues/`
  - index.blade.php - League listing
  - create.blade.php - League creation form
  - edit.blade.php - League editing
  - _form.blade.php - Shared league form component
  - show.blade.php - League detail page with tabs
  - _tab-overview.blade.php - Overview tab
  - _tab-teams.blade.php - Teams management tab
  - _tab-matches.blade.php - Match scheduling tab
  - _tab-standings.blade.php - Standings display tab
  - matches.blade.php - Detailed matches view
- UI Features:
  - Vanilla JS modals for team/player operations
  - fetch() API for AJAX operations
  - Toastr.js notifications for user feedback
  - XSS prevention with @json() for JS params, textContent for DOM
  - Vietnamese UI with proper diacritics

#### Web Routes (17 total)
- CRUD: GET|POST /homeyard/leagues/{id}, PUT|DELETE for updates
- Teams: POST|PUT|DELETE /homeyard/leagues/{id}/teams/{team}
- Players: POST|DELETE /homeyard/leagues/{id}/teams/{team}/players
- Matches: GET /matches, PUT /matches/{match}/score

#### Database Changes (7 tables)
- leagues, league_teams, league_team_players
- league_rounds, league_matches, league_match_games
- league_standings

#### Frontend Patterns
- Tab navigation, AJAX team/player CRUD, user search dropdown
- Real-time standings recalculation, match score entry per game
- Status badges, modals, vanilla JS fetch API

---

### Version 1.6.0 (2026-01-14)

#### Major Features Added
- **Point Earning System:** Complete gamification system with 16 tasks across 4 user roles
  - User tasks (6): Referral, Check-in Stadium, Weekly 5 Matches, Join Event, Join Club, Create OCR Match
  - Social tasks (4): Join FB Group, Follow FB Page, Subscribe YouTube, Follow TikTok
  - HomeYard tasks (3): Update Stadium Info, Create Social Schedule, Create Tournament
  - Referee task (1): Score Match
  - Expert task (1): Verify ELO
  - Task categories: Daily, Social, Event, Tournament
  - Frequency types: Unlimited, Daily, Weekly, Monthly, Once
  - Proof types: None (auto-award), Image, Link, QR Code

- **Wallet System:** User point balance and transaction management
  - `UserWallet` model with add/deduct methods
  - `UserPointTransaction` history with type, description, metadata
  - Formatted point display

- **Submission Workflow:** Proof submission and admin approval system
  - `PointSubmission` model with UUID, status (pending/approved/rejected)
  - Image upload with base64 encoding, sanitization, EXIF stripping
  - Link validation with social platform uniqueness checking
  - QR code validation
  - Admin review with notes and point award tracking
  - Composite indexes for performance

- **Social Platform Verification:** One-time verification system
  - Facebook, YouTube, TikTok profile verification
  - URL uniqueness enforcement across users
  - `SocialProfileVerification` model with platform tracking

- **Special Challenges:** Time-limited challenges with participant limits
  - `SpecialChallenge` model with start/end dates
  - Max participant enforcement
  - Cached participant counts (5-min TTL)
  - Status tracking (upcoming/ongoing/ended)

- **Event System:** Workshop/event management with QR check-in
  - `Event` model with UUID, location, stadium, datetime, points
  - Auto-generated QR codes for check-in
  - `EventCheckin` model with check-in method tracking
  - Max attendee limits
  - Point rewards for attendance

#### Technical Implementation
- `PointEarningService` - Task eligibility checking, frequency validation, auto-award logic
- `PointSubmissionService` - Submission creation, proof validation, admin review workflow
- `SocialVerificationService` - Social platform verification, URL uniqueness checking
- `PointController` (API) - 6 endpoints: tasks, balance, history, submissions (GET/POST), challenges
- 8 new models: PointTask, PointSubmission, UserWallet, UserPointTransaction, SocialProfileVerification, SpecialChallenge, Event, EventCheckin
- Image security: DoS protection, image bomb detection, EXIF stripping, dimension limits (4000x4000)
- Proof validation: Base64 decoding, MIME type checking, size limits (5MB)

#### Database Changes
- `point_tasks` table - 16 tasks with code, points, role, category, frequency, proof_type, is_active
- `point_submissions` table - UUID, user_id, point_task_id, status, proof_data (JSON), admin review fields, composite indexes
- `user_wallets` table - user_id, points balance
- `user_point_transactions` table - user_id, points, type, description, metadata (JSON)
- `social_profile_verifications` table - user_id, platform, profile_url, verified_at
- `special_challenges` table - title, description, points, start_date, end_date, max_participants, is_active
- `events` table - UUID, title, description, location, stadium_id, datetimes, points, max_attendees, qr_code_data, created_by
- `event_checkins` table - event_id, user_id, checked_in_at, check_in_method, points_awarded
- `matches` table - Added points_per_set column

#### API Endpoints (New)
- `GET /api/points/tasks` - Get available tasks with eligibility
- `GET /api/points/balance` - Get wallet balance
- `GET /api/points/history` - Get transaction history (pagination)
- `GET /api/points/submissions` - Get user submissions (status filter)
- `POST /api/points/submissions` - Submit proof for task
- `GET /api/points/challenges` - Get active special challenges

---

### Version 1.5.1 (2026-01-15)

#### Features Added
- **Gender-Aware Skill Level Mapping:** Female players receive +0.5 skill level at same ELO
  - 8 skill levels: 2.0, 2.5, 3.0, 3.5, 4.0, 4.5, 5.0, 5.5+
  - Aligned with Vietnam tournament standards (Male amateur <4.0, Female <3.5)
  - Vietnamese and English level names
  - Backward compatible (defaults to male if gender not set)

#### Technical Implementation
- `SkillQuizService.eloToSkillLevel($elo, $gender)` - Gender-aware mapping with optional gender parameter
- `SkillQuizService.getSkillLevelName($level, $locale)` - Localized level names (VN/EN)
- `SkillQuizService::ELO_THRESHOLDS_MALE` - Male ELO threshold constants
- `SkillQuizService::ELO_THRESHOLDS_FEMALE` - Female ELO threshold constants (+0.5 level)
- `SkillQuizService::SKILL_LEVEL_NAMES` - Multilingual level name constants
- Updated all callers to pass user gender (lines 380, 649, 736 in SkillQuizService.php)

#### Database Changes
- Added `gender` column to `users` table (enum: 'male', 'female', nullable)
- Migration: `2026_01_15_201900_add_gender_to_users_table.php`

#### View Updates
- `resources/views/front/skill-quiz/result.blade.php` - Display level with VN name
- `resources/views/verifier/requests/show.blade.php` - Display level with VN name
- Skill level badges show gender-appropriate level throughout system

#### Test Updates
- `SkillQuizServiceTest` - Added gender-aware test cases
  - Male ELO mapping tests
  - Female ELO mapping tests (+0.5 level verification)
  - Default to male when gender null
  - Localized level name tests

---

### Version 1.5.0 (2026-01-03)

#### Features Added
- **Skill Assessment Quiz System:** 36-question self-assessment quiz for initial ELO rating
  - 6 skill domains with weighted scoring (Technical, Strategy, Physical, Mental, Experience, Situations)
  - Rating scale: 0-3 per question (Never to Always)
  - Initial ELO calculation (800-1400 range)
  - Cross-validation fraud detection
  - Time validation window (3-20 minutes)
  - ELO caps based on experience (1100 for new, 1200 for experienced)
  - Re-quiz cooldown policy (30-90 days based on ELO)
  - Guest preview mode without account
  - Admin flagging and review system

#### Technical Implementation
- `SkillQuizService` - Quiz logic, scoring, validation, ELO calculation
- 3 new models: `SkillQuestion`, `SkillQuizAttempt`, `SkillQuizAnswer`
- 3 controllers: Admin, API, Frontend quiz controllers
- Frontend quiz flow views (index, start, quiz, result)
- Admin panel (dashboard, attempts list, attempt detail)
- 2 seeders: `SkillDomainSeeder`, `SkillQuestionSeeder`

#### Database Changes
- Created `skill_domains` table (6 fixed domains)
- Created `skill_questions` table (36 questions)
- Created `skill_quiz_attempts` table (user attempts)
- Created `skill_quiz_answers` table (individual answers)
- Added `quiz_completed_at`, `quiz_elo_assigned`, `can_retake_quiz_at` to users table

#### Routes Added
- `GET /skill-quiz` - skill-quiz.index
- `GET /skill-quiz/start` - skill-quiz.start
- `GET /skill-quiz/quiz` - skill-quiz.quiz
- `GET /skill-quiz/result/{id}` - skill-quiz.result
- `GET /api/skill-quiz/domains` - API domains endpoint
- `GET /api/skill-quiz/questions` - API questions endpoint
- `POST /api/skill-quiz/submit` - API submit endpoint
- `GET /admin/skill-quiz` - admin.skill-quiz.index
- `GET /admin/skill-quiz/{id}` - admin.skill-quiz.show
- `PUT /admin/skill-quiz/{id}/flag` - admin.skill-quiz.flag

---

### Version 1.4.0 (2025-12-18)

#### Features Added
- **Doubles Pair Selection:** Complete doubles support for tournament categories
  - Partner selection in tournament registration form
  - Partner linking system with partner_id in tournament_athletes table
  - Pair name display (Athlete A / Athlete B format)
  - Doubles validation in match creation
  - Category type detection (isDoubles method)
  - Automatic pair loading for doubles categories in match creation UI

#### Technical Implementation
- `TournamentAthlete.partner()` - BelongsTo relationship for partner linking
- `TournamentAthlete.hasPartner()` - Check if athlete has partner
- `TournamentAthlete.getPairNameAttribute()` - Get formatted pair name
- `TournamentCategory.isDoubles()` - Detect doubles category types
- `TournamentRegistrationController` - Handle partner data in registration
- `HomeYardTournamentController.getCategoryAthletes()` - Return pairs for doubles
- `HomeYardTournamentController.storeMatch()` - Validate doubles pairs
- Registration form with partner fields (name, email, phone)
- Match creation UI with pair selection dropdown

#### Database Changes
- Added `partner_id` column to `tournament_athletes` table
- Foreign key constraint: partner_id references tournament_athletes.id
- Self-referencing relationship for doubles pair linking

#### Routes
- No new routes (enhanced existing tournament registration and match creation)

---

### Version 1.3.0 (2025-12-09)

#### Features Added
- **Referee System:** Complete referee management for tournament matches
  - Referee role with dedicated permissions
  - Referee profile fields (bio, status, rating, matches officiated)
  - Tournament referee assignment by Home Yard
  - **Match-level referee assignment** with dropdown selection in match details
  - Referee dashboard with stats and upcoming matches
  - Match officiating with set-by-set score entry
  - Automatic winner calculation from set scores
  - Match status transitions (scheduled -> in_progress -> completed)
  - Public referee directory and profile viewing
  - Display assigned referee name on match cards (all tabs)

#### Technical Implementation
- `RefereeController` - Dashboard, match list, score entry, match control
- `RefereeProfileController` - Public referee directory and profiles
- `HomeYardTournamentController.getMatch()` - Returns tournament referees list for match assignment
- `HomeYardTournamentController.updateMatch()` - Accepts referee_id with validation
- `TournamentReferee` model - Referee-tournament assignment pivot
- User model extensions: `refereeTournaments()`, `refereeMatches()`
- MatchModel extensions: `isAssignedToReferee()`, `canEditScores()`
- Referee layout at `resources/views/layouts/referee.blade.php`
- Referee views in `resources/views/referee/`
- Match detail modal with referee assignment UI in `matches.blade.php`

#### Database Changes
- Added `referee_bio`, `referee_status`, `matches_officiated`, `referee_rating` to users table
- Created `tournament_referees` table for referee-tournament assignments
- Added `referee_id`, `referee_name` to matches table

#### Routes Added
- `GET /referee/dashboard` - referee.dashboard
- `GET /referee/matches` - referee.matches.index
- `GET /referee/matches/{id}` - referee.matches.show
- `POST /referee/matches/{id}/start` - referee.matches.start
- `POST /referee/matches/{id}/score` - referee.matches.score
- `GET /academy/referees` - academy.referees.index
- `GET /academy/referees/{id}` - academy.referees.show
- `POST /homeyard/tournaments/{id}/referees/add` - homeyard.tournaments.referees.add
- `DELETE /homeyard/tournaments/{id}/referees/{id}` - homeyard.tournaments.referees.remove

---

### Version 1.2.0 (2025-12-07)
- User profile management (avatar, email/password updates, location)
- Added `ProfileService`, 5 endpoints, profile views
- Added `avatar`, `location`, `province_id` to users table

### Version 1.1.0 (2025-12-05)
- OPRS multi-component rating (Elo 70% + Challenge 20% + Community 10%)
- Added `OprsService`, `ChallengeService`, `CommunityService`
- New models: `ChallengeResult`, `CommunityActivity`, `OprsHistory`
- 22 new API endpoints, 11 admin routes

### Version 1.0.0 (2025-12-02)
- OCR Elo ranking system (100-3000+, 7 tiers), match challenges, badges, leaderboard
- `EloService`, `BadgeService`, `OcrMatch` model
- New tables: `ocr_matches`, `elo_histories`, `user_badges`

---

## Unresolved Questions

1. **Payment Gateway:** MoMo, VNPay, or ZaloPay priority?
2. **Mobile Strategy:** PWA first or native app?
3. **Redis Timeline:** Implementation and migration approach?
4. **Caching:** Leaderboard caching strategy for OPRS?
5. **Club Events:** Should clubs host events with tickets?

---

**Maintained By:** Development Team
**Last Review:** 2026-02-25
**Next Review Target:** 2026-03-25
