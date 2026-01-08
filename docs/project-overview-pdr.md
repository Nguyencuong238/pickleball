# Project Overview & Product Development Requirements (PDR)

**Project Name**: Pickleball Platform
**Version**: 1.0.0
**Last Updated**: 2025-12-09
**Status**: Active Development
**Framework**: Laravel 10.10+

## Executive Summary

Pickleball Platform is a comprehensive web application for managing pickleball courts, tournaments, instructors, and social activities. Built with Laravel 10, it provides a multi-tenant system supporting stadium owners, tournament organizers, instructors, and end users.

## Project Purpose

### Vision
Create a centralized platform connecting pickleball players with courts, tournaments, instructors, and social activities in Vietnam and beyond.

### Mission
- Enable stadium owners to manage court bookings and pricing
- Allow tournament organizers to run complete tournament operations
- Connect instructors with students seeking coaching
- Build a community through social activities and video content

### Value Proposition
- **For Stadium Owners**: Complete court and booking management system
- **For Tournament Organizers**: Full tournament lifecycle management
- **For Instructors**: Profile, package, and booking management
- **For Players**: Easy discovery of courts, tournaments, and coaches

## Target Users

### Primary Users
1. **Stadium Owners (Home Yard)**: Manage stadiums, courts, and bookings
2. **Tournament Organizers**: Create and manage tournaments
3. **Instructors**: Offer coaching services
4. **Players/Athletes**: Book courts, join tournaments, find coaches
5. **Administrators**: System-wide management

### User Personas

**Persona 1: Stadium Owner**
- **Needs**: Manage multiple courts, set pricing, track bookings
- **Features**: Court management, dynamic pricing, booking calendar
- **Access**: Home Yard dashboard

**Persona 2: Tournament Organizer**
- **Needs**: Create tournaments, manage athletes, track matches
- **Features**: Tournament creation, athlete registration, bracket management
- **Access**: Home Yard tournament module

**Persona 3: Instructor**
- **Needs**: Showcase skills, offer packages, manage schedule
- **Features**: Profile, certifications, packages, reviews
- **Access**: Instructor dashboard

**Persona 4: Player**
- **Needs**: Find courts, join tournaments, book coaches
- **Features**: Search, booking, registration, favorites
- **Access**: Public frontend

## Key Features & Capabilities

### 1. Stadium & Court Management
- Stadium profiles with media gallery
- Multiple courts per stadium
- Dynamic court pricing (time-based tiers)
- Booking management with calendar view
- Reviews and ratings

### 2. Tournament System
- Tournament creation and configuration
- Category management (skill levels, age groups, singles/doubles)
- Athlete registration with partner selection for doubles categories
- Partner linking system for doubles pairs
- Round and group management
- Match scheduling with pair support
- Match results and rankings
- Excel export for athletes and rankings

### 3. Instructor Platform
- Instructor profiles with certifications
- Experience and teaching methods
- Package offerings with pricing
- Schedule availability
- Student bookings
- Reviews and ratings

### 4. Social Activities
- Social event creation
- Participant management
- Community engagement

### 5. Video Content
- Video library
- Comments and discussions
- Like system
- Course organization

### 6. OCR (OnePickleball Championship Ranking)
- Elo-based rating system (100-3000+)
- Ranked match challenges (singles/doubles)
- Match workflow (challenge, accept, play, submit, confirm)
- Seven rank tiers (Bronze to Grandmaster)
- Global leaderboard
- Achievement badge system (first win, streaks, milestones, ranks)
- Elo history tracking
- Admin dispute resolution
- Evidence upload for matches

### 7. OPRS (OnePickleball Rating Score)
- **Multi-component scoring system**: Elo (70%) + Challenge (20%) + Community (10%)
- **Seven OPR Levels**: 1.0 to 5.0+ (Beginner to Elite)
- **Challenge System**:
  - Five challenge types (serve, volley, dink, footwork, monthly test)
  - Point-based scoring with pass/fail thresholds
  - Admin verification workflow
  - Monthly test limitation (once per month)
- **Community Activities**:
  - Stadium check-ins (daily)
  - Event participation tracking
  - Player referral system
  - Weekly match bonus (5+ matches)
  - Monthly challenge objectives
- **OPRS Features**:
  - Real-time OPRS calculation and updates
  - Complete change history audit log
  - Level-based leaderboards and filtering
  - Matchmaking with skill-similar opponents
  - Score breakdown visualization
  - Admin adjustment and management tools

### 8. User Profile Management
- Profile editing (name, location, province)
- Avatar upload and management (JPEG, PNG, WebP, max 2MB)
- Email change with password verification
- Password update with current password check
- OAuth users can set initial password
- Province selection with relationship
- Avatar storage using Laravel Storage

### 9. Skill Assessment Quiz System
- **Initial Assessment**: 36-question self-assessment quiz across 6 domains
- **Skill Domains**: Technical Skills, Strategy, Physical Conditioning, Mental Game, Experience, Game Situations
- **Rating Scale**: 0-3 scale (Never/Rarely/Sometimes/Often-Always)
- **ELO Calculation**: Converts quiz score to initial ELO rating (800-1400 range)
- **Anti-Fraud Measures**:
  - Cross-validation score checking for consistency
  - Time validation (3-20 minutes completion window)
  - ELO caps (1100 for new players, 1200 for experienced)
  - Admin flagging for suspicious attempts
- **Re-Quiz Policy**: Cooldown periods based on ELO accuracy (30-90 days)
- **Guest Mode**: Preview quiz without account
- **Admin Panel**: Attempt management, flag review, statistics

### 10. Referee System
- **Referee Role**: Dedicated role for match officiating
- **Referee Profiles**: Bio, status, rating, matches officiated count
- **Tournament Assignment**: Home Yard can assign referees to tournaments
- **Referee Dashboard**: Overview with stats and upcoming matches
- **Match Officiating**:
  - View assigned matches with filters (tournament, status, date)
  - Start matches (scheduled -> in_progress)
  - Enter set-by-set scores
  - Calculate winner automatically
  - Complete matches with final scores
- **Public Referee Directory**: Browse and view referee profiles

### 11. News & CMS
- News articles with categories
- Featured content
- Static pages (About, Contact, etc.)

### 12. User Authentication
- Email/password registration
- OAuth (Google, Facebook)
- Role-based access control
- Admin separate login

## Technical Requirements

### Functional Requirements

**FR1: User Management**
- User registration and authentication
- OAuth integration (Google, Facebook)
- Role-based permissions (admin, home_yard, user)
- Profile management

**FR2: Court Booking**
- Available slot calculation
- Dynamic pricing by time
- Booking creation and management
- Booking cancellation

**FR3: Tournament Management**
- CRUD operations for tournaments
- Category configuration (singles/doubles)
- Athlete registration workflow with partner selection
- Doubles pair management with partner linking
- Match management with pair support
- Standings calculation

**FR4: Instructor Services**
- Instructor profile management
- Package and pricing
- Booking system
- Review system

**FR5: Content Management**
- News article CRUD
- Category management
- Page management
- Media library

**FR6: OCR Ranking System**
- Match challenge creation and acceptance
- Elo calculation and rating updates
- Badge awarding based on achievements
- Leaderboard with filtering
- Match dispute resolution
- Elo history tracking

**FR7: OPRS Rating System**
- Multi-component score calculation (Elo 70%, Challenge 20%, Community 10%)
- OPR Level determination and mapping
- Challenge submission and verification
- Community activity tracking and point awarding
- OPRS history recording and audit trail
- Level-based leaderboards and matchmaking
- Admin score adjustments and management

**FR8: User Profile Management**
- Profile information editing (name, location, province)
- Avatar upload with validation (type, size, dimensions)
- Avatar removal functionality
- Email update with password verification
- Password change with current password validation
- OAuth users password initialization

**FR9: Referee System**
- Referee role management with Spatie Permission
- Referee profile fields (bio, status, rating, matches count)
- Tournament referee assignment and removal
- Referee dashboard with statistics
- Match assignment to referees
- Match score entry and management
- Match status transitions (scheduled -> in_progress -> completed)
- Public referee directory and profile viewing

**FR10: Skill Assessment Quiz System**
- 36-question quiz across 6 skill domains
- Self-assessment rating scale (0-3)
- Initial ELO calculation from quiz score
- Cross-validation fraud detection
- Time-based validation (3-20 min)
- ELO capping based on experience level
- Re-quiz cooldown policy enforcement
- Guest preview mode
- Admin attempt flagging and review

### Non-Functional Requirements

**NFR1: Performance**
- Page load < 3 seconds
- Support 1000+ concurrent users
- Optimized database queries

**NFR2: Security**
- CSRF protection
- XSS prevention
- SQL injection prevention
- Role-based access control

**NFR3: Usability**
- Responsive design
- Intuitive navigation
- Vietnamese language support

**NFR4: Maintainability**
- MVC architecture
- Code documentation
- Version control

**NFR5: Scalability**
- Database optimization
- Caching strategies
- CDN for media

## Technology Stack

### Backend
- **Framework**: Laravel 10.10+
- **Language**: PHP 8.1+
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **OAuth**: Laravel Socialite
- **Media**: Spatie Media Library
- **Permissions**: Spatie Laravel Permission
- **Spreadsheet**: PhpSpreadsheet

### Frontend
- **Views**: Blade Templates
- **Build**: Vite 5
- **HTTP Client**: Axios

### Development
- **Testing**: PHPUnit
- **Linting**: Laravel Pint

## Database Schema Overview

### Core Entities
- **users**: User accounts with OAuth support
- **stadiums**: Venue information
- **courts**: Individual courts within stadiums
- **court_pricings**: Time-based pricing tiers
- **bookings**: Court reservations

### Tournament Entities
- **tournaments**: Tournament configuration
- **tournament_categories**: Skill/age categories (singles/doubles)
- **tournament_athletes**: Registered participants with partner_id for doubles
- **rounds**: Tournament rounds
- **groups**: Group stage groupings
- **group_standings**: Group rankings
- **matches**: Individual matches with pair support

### Instructor Entities
- **instructors**: Coach profiles
- **instructor_certifications**: Credentials
- **instructor_experiences**: Work history
- **instructor_packages**: Service offerings
- **instructor_reviews**: Student feedback
- **instructor_schedules**: Availability
- **booking_instructors**: Coaching bookings

### Content Entities
- **news**: Articles
- **categories**: News categories
- **pages**: Static content
- **videos**: Video content
- **video_comments**: User comments
- **video_likes**: Engagement

### Social Entities
- **socials**: Social events
- **favorites**: User bookmarks
- **reviews**: Venue reviews

### OCR Entities
- **ocr_matches**: Ranked matches (singles/doubles)
- **elo_histories**: Rating change records
- **user_badges**: Achievement badges

### OPRS Entities
- **users**: Added OPRS fields (challenge_score, community_score, total_oprs, opr_level)
- **challenge_results**: Skill challenge records
- **community_activities**: Community engagement tracking
- **oprs_histories**: OPRS change audit log

### Profile Entities
- **users**: Added profile fields (avatar, location, province_id)
- **provinces**: Geographic provinces for location data

### Referee Entities
- **users**: Added referee fields (referee_bio, referee_status, matches_officiated, referee_rating)
- **tournament_referees**: Referee-tournament assignment pivot table
- **matches**: Added referee_id and referee_name columns

### Skill Quiz Entities
- **skill_domains**: 6 fixed domains with weights
- **skill_questions**: 36 questions across domains
- **skill_quiz_attempts**: User attempts with scores, ELO, flags
- **skill_quiz_answers**: Individual question responses
- **users**: Added quiz tracking fields (quiz_completed_at, quiz_elo_assigned, can_retake_quiz_at)

## Success Metrics

### User Metrics
- Monthly active users
- User registration rate
- Retention rate

### Business Metrics
- Court booking volume
- Tournament registrations
- Instructor bookings
- Revenue per venue

### Quality Metrics
- Average rating per stadium
- Instructor satisfaction scores
- User feedback sentiment

## Constraints & Limitations

### Technical Constraints
- PHP 8.1+ required
- MySQL database required
- Server with sufficient storage for media

### Operational Constraints
- Vietnamese market focus initially
- Single timezone support
- Manual payment verification (no online payment gateway yet)

## Future Roadmap

### Phase 1: Core Platform (Completed)
- [x] Stadium and court management
- [x] Basic tournament system
- [x] Instructor profiles
- [x] User authentication
- [x] OCR ranking system with Elo rating
- [x] Match challenges and dispute resolution
- [x] Achievement badge system
- [x] OPRS multi-component rating system
- [x] Challenge and community activity systems

### Phase 2: Enhanced Features (In Progress)
- [x] User profile management with avatar upload
- [x] Referee system with match officiating
- [x] Doubles pair selection for tournament categories
- [x] Skill assessment quiz system (260102-1200)
- [ ] Online payment integration
- [ ] Real-time notifications for match invites and activities
- [ ] Mobile app with OPRS integration
- [ ] Advanced analytics dashboard
- [ ] OCR/OPRS season/league system

### Phase 3: Expansion (Planned)
- [ ] Multi-region support
- [ ] Equipment marketplace
- [ ] Community forums
- [ ] Live streaming of matches
- [ ] OCR/OPRS team rankings
- [ ] Professional tournament integration
- [ ] Advanced challenge types and AI-powered skill assessment
- [ ] Gamification enhancements for community engagement

## Related Documentation

- [Codebase Summary](./codebase-summary.md)
- [Code Standards](./code-standards.md)
- [System Architecture](./system-architecture.md)

## Unresolved Questions

1. **Payment Integration**: Which payment gateway to integrate (MoMo, VNPay, ZaloPay)?
2. **Mobile Strategy**: Native app or PWA?
3. **Notification System**: Real-time vs batch notifications?
4. **Multi-language**: Timeline for English language support?
5. **API Strategy**: Public API for third-party integrations?
6. **OPRS Scaling**: How to handle OPRS calculations at scale (10,000+ users)?
7. **Challenge Verification**: Should challenge verification be automated with video evidence analysis?
8. **Community Gamification**: What additional engagement mechanics should be added?
