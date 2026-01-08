# Pickleball Platform - Project Roadmap

**Last Updated:** 2025-12-18
**Current Version:** 1.4.0
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

### Phase 3: Enhanced Features (IN PROGRESS - 2025 Q4)
**Status:** 🔄 In Progress
**Target:** 2026 Q1
**Progress:** 30%

#### Completed Features
- [x] User profile management with avatar upload (Dec 7)
- [x] Email and password update functionality (Dec 7)
- [x] Referee system with match officiating (Dec 9)
- [x] Doubles pair selection for tournament categories (Dec 18)
- [x] Skill assessment quiz system (Jan 3, 2026)

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
- [ ] OCR season/league system
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

### Recent Additions (December 2025)
- OCR Elo rating system (Dec 2)
- Match challenge workflow (Dec 2)
- Achievement badges (Dec 2)
- Global leaderboard (Dec 2)
- Admin dispute resolution (Dec 2)
- Elo history tracking (Dec 2)
- OPRS multi-component rating system (Dec 5)
- Challenge system (skill tests) (Dec 5)
- Community activity tracking (Dec 5)
- Level-based leaderboards (Dec 5)
- User profile management (Dec 7)
- Avatar upload and management (Dec 7)
- Email and password update (Dec 7)
- Referee system with match officiating (Dec 9)
- Tournament referee assignment (Dec 9)
- Public referee directory (Dec 9)
- Doubles pair selection for tournaments (Dec 18)
- Partner linking system for doubles categories (Dec 18)
- Skill assessment quiz system (Jan 3, 2026)
- Initial ELO calculation from 36-question quiz (Jan 3, 2026)
- Anti-fraud measures with cross-validation (Jan 3, 2026)
- Guest quiz preview mode (Jan 3, 2026)

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
| Payment Gateway Integration | 🔄 In Progress | 2026-03-31 | 30% |
| Notification System Design | Planned | 2026-03-15 | 0% |

### Q1 2026 (Continued)
| Milestone | Status | Due Date | Progress |
|-----------|--------|----------|----------|
| Payment Integration Complete | 📋 Planned | 2026-03-31 | 0% |
| Email Notifications | 📋 Planned | 2026-03-15 | 0% |
| Analytics Dashboard | 📋 Planned | 2026-03-31 | 0% |
| Mobile PWA | 📋 Planned | 2026-03-31 | 0% |

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

#### Features Added
- **User Profile Management:** Complete profile editing system
  - Profile information editing (name, location, province)
  - Avatar upload and management with validation
  - Email change with password verification
  - Password update with current password check
  - OAuth users can set initial password
  - Province relationship for location data

#### Technical Implementation
- `ProfileService` - Profile update business logic
- `ProfileController` - 5 endpoints (edit, update, avatar, email, password)
- Profile edit view at `resources/views/user/profile/edit.blade.php`
- User model methods: `province()`, `getAvatarUrl()`

#### Database Changes
- Added `avatar`, `location`, `province_id` to users table
- Foreign key: province_id references provinces.id

#### Routes Added
- `GET /user/profile/edit` - user.profile.edit
- `PUT /user/profile` - user.profile.update
- `PUT /user/profile/avatar` - user.profile.avatar
- `PUT /user/profile/email` - user.profile.email
- `PUT /user/profile/password` - user.profile.password

---

### Version 1.1.0 (2025-12-05)

#### Features Added
- **OPRS Rating System:** Multi-component rating extending OCR
  - Three-component scoring: Elo (70%) + Challenge (20%) + Community (10%)
  - Seven OPR Levels: 1.0 to 5.0+ (Beginner to Elite)
  - Challenge system with 5 challenge types
  - Community activity tracking with 5 activity types
  - Real-time OPRS calculation and history
  - Level-based leaderboards and matchmaking
  - Admin management and verification tools

#### Technical Implementation
- `OprsService` - Core OPRS calculation service
- `ChallengeService` - Challenge submission and verification
- `CommunityService` - Community activity tracking
- 3 new models: `ChallengeResult`, `CommunityActivity`, `OprsHistory`
- 3 new API controllers (OPRS, Leaderboard, Matchmaking)
- 3 new admin controllers (OPRS, Challenge, Activity)
- Frontend views for challenges and community
- Blade components for OPRS visualization
- 2 Artisan commands for batch operations

#### Database Changes
- Added `challenge_score`, `community_score`, `total_oprs`, `opr_level` to users table
- Created `challenge_results` table
- Created `community_activities` table
- Created `oprs_histories` table
- Added `match_category` to `ocr_matches` table

#### API Changes
- 22 new OPRS API endpoints
- 11 new admin OPRS routes
- Enhanced user profile with OPRS breakdown

---

### Version 1.0.0 (2025-12-02)

#### Features Added
- **OCR Ranking System:** Complete competitive ranking system
  - Elo rating (100-3000+) with seven rank tiers
  - Match challenge workflow (singles/doubles)
  - Achievement badge system (12+ badge types)
  - Global leaderboard with filtering
  - Admin dispute resolution
  - Elo history tracking

#### Technical Implementation
- `EloService` with dynamic K-factor adjustment
- `BadgeService` with automated badge awarding
- `OcrMatch` model with state transitions
- 3 new API controllers (Match, User, Leaderboard)
- 2 new admin controllers (Dispute, Badge)
- Frontend views for OCR system
- Database migrations for OCR tables

#### Database Changes
- Added `elo_rating`, `elo_rank`, `total_ocr_matches`, `ocr_wins`, `ocr_losses` to users table
- Created `ocr_matches` table
- Created `elo_histories` table
- Created `user_badges` table

---

## Unresolved Questions

1. **Payment Gateway:** Which payment gateway to prioritize (MoMo, VNPay, or ZaloPay)?
2. **Mobile Strategy:** PWA first or native app development?
3. **Caching Strategy:** Redis implementation timeline and migration approach?
4. **OCR Seasons:** Should we implement seasonal rankings with resets?
5. **Multi-language:** Timeline for English language support?
6. **API Access:** Should we provide public API for third-party integrations?

---

**Maintained By:** Development Team
**Last Review:** 2025-12-09
**Next Review Target:** 2026-01-09
