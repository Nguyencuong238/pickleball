# Pickleball Platform - Project Roadmap

**Last Updated:** 2026-03-09
**Current Version:** 1.9.0
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

### Phase 3: Enhanced Features (COMPLETED - 2026-03-13)
**Status:** ✅ Complete
**Progress:** 100%

#### Completed Features (Abbreviated)
- [x] User profile management with avatar upload (Dec 7)
- [x] Referee system with match officiating (Dec 9)
- [x] Doubles pair selection for tournament categories (Dec 18)
- [x] Skill assessment quiz system (Jan 3, 2026)
- [x] Gender-aware skill level mapping (Jan 15, 2026)
- [x] Point earning system with 16 tasks (Jan 14, 2026)
- [x] Booking code system and history (Feb 7, 2026)
- [x] Club system with posts, comments, reactions (Feb 2026)
- [x] League Management System (Feb 25, 2026)
- [x] Club Activities ReClub-Style Upgrade (Feb 27, 2026)
- [x] Club Activity Casual Matches System (Mar 3, 2026)
- [x] MLP League Format with 6 Sub-Game Doubles Pairing (Mar 9, 2026)
- [x] Club Management API (Mar 9, 2026)
- [x] League Registration System (Mar 9, 2026)
- [x] **Tournament Management Rewrite - Phase 1 (Mar 13, 2026)** NEW
  - [x] Modular controller architecture (7 controllers + 6 traits)
  - [x] Service layer refactoring (6 services + 3 helpers)
  - [x] Alpine.js dashboard with responsive UI
  - [x] Dashboard sidebar with navigation
  - [x] Athletes management (CRUD, status, approval)
  - [x] Draw/seeding (auto-assign, manual mode, SortableJS integration)
  - [x] Group setup and management
  - [x] Match management and scoring
  - [x] Rankings and statistics display
  - [x] 20+ Blade partials for component modularity
  - [x] 8 JavaScript modules with mixin composition
  - [x] 11 CSS stylesheets for responsive design

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

### Recent Additions (Dec 2025 - Mar 2026)
- OCR Elo system with Elo history and badges (Dec 2, 2025)
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
- MLP league format with 6 sub-game doubles pairing (Mar 9, 2026)
- Club management API with auto-post creation (Mar 9, 2026)
- League registration system with payment proof and auto team generation (Mar 9, 2026)
- LeagueAutoTeamService with skill-ranked snake-draft and random modes (Mar 9, 2026)
- Pessimistic locking for race-condition safe team generation (Mar 9, 2026)

### v1.9.0 Changelog (2026-03-09)
- **New**: MLP league format with 6 sub-game doubles pairing structure (player pair support)
- **New**: Club management API for activities, competitions, posts with full CRUD
- **New**: League registration system with payment proof upload and admin approval
- **New**: Auto team generation (skill-ranked snake-draft and random pairing modes)
- **New**: LeagueAutoTeamService with race-condition safe DB operations
- **New**: LeagueRegistrationService for registration workflow management
- **New**: Auto-create club post when activity is created
- **Enhancement**: Added join_request_status to club show endpoint
- **Enhancement**: Activity detail page redesigned with modern tab-based UI
- **Enhancement**: Round editing in league management
- **Enhancement**: Phone number normalization in registration flow
- **Security**: Pessimistic locking (lockForUpdate) for team generation consistency

### In Development
- 🔄 Payment integration (MoMo, VNPay, ZaloPay research)
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

See [Code Standards](./code-standards.md) for detailed guidelines. See [System Architecture](./system-architecture.md) for technical stack.

---

## Change Log (Compact)

**v1.10.0 (2026-03-13)** - Tournament Management Rewrite: 7 controllers, 6 services, 6 traits, Alpine.js dashboard, draw/seeding, athletes, matches, rankings, 20+ partials, 8 JS modules, 11 CSS files, mixin composition pattern

**v1.9.0 (2026-03-09)** - League Registration: payment proof, phone normalization, admin approval, auto team generation (skill-ranked/random), race-condition safe, LeagueAutoTeamService, LeagueRegistrationService, 2 new tables

**v1.8.0 (2026-03-03)** - Club Activity Casual Matches: 3 algorithms (singles_rr, rotating_doubles, fixed_doubles), per-player standings, 7 AJAX endpoints, 3 new tables

**v1.7.0 (2026-02-25)** - League Management: CRUD, tab-based UI, team/player roster, auto-schedule, AJAX scoring, standings calc, MLP format, 7 tables

**v1.6.0 (2026-01-14)** - Point Earning: 16 tasks, wallet, submissions w/ image/link/QR, social verification, special challenges, events w/ QR check-in, 8 models

**v1.5.1 (2026-01-15)** - Gender-aware skill levels: females +0.5 level, 8 levels total, VN/EN names, aligned with Vietnam standards

**v1.5.0 (2026-01-03)** - Skill Quiz: 36 questions, 6 domains, auto-ELO (800-1400), fraud detection, cooldown policy, admin flagging

**v1.4.0 (2025-12-18)** - Doubles: Partner selection, pair linking, draw order, category detection, registration + match creation UIs

**v1.3.0 (2025-12-09)** - Referee: Role, profile, tournament/match assignment, score entry, dashboard, public directory, 5 DB changes

**v1.2.0 (2025-12-07)** - Profile: Avatar, location, email/password updates, ProfileService, 5 API endpoints

**v1.1.0 (2025-12-05)** - OPRS: 3-component (Elo 70% + Challenge 20% + Community 10%), 7 levels, OprsService, ChallengeService, CommunityService, 22 API routes

**v1.0.0 (2025-12-02)** - OCR: Elo system (100-3000+), 7 tiers, match challenges, badges, leaderboard, EloService

---

## Unresolved Questions

1. **Payment Gateway:** MoMo, VNPay, or ZaloPay priority?
2. **Mobile Strategy:** PWA first or native app?
3. **Redis Timeline:** Implementation and migration approach?
4. **Caching:** Leaderboard caching strategy for OPRS?
5. **Club Events:** Should clubs host events with tickets?

---

**Maintained By:** Development Team
**Last Review:** 2026-03-03
**Next Review Target:** 2026-03-31
