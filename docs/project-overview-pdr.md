# Project Overview & Product Development Requirements (PDR)

**Project Name**: Pickleball Platform
**Version**: 1.12.0
**Last Updated**: 2026-04-03
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
- Booking management with calendar view, history, and cancellation
- Booking code system: BK{courtId:3+}{date:YYMMDD}{seq:3}
- Booking confirmation tracking with confirmed_at timestamp
- Transfer proof system for booking transfers
- Reviews and ratings

### 2. Tournament System (Rewritten - Mar 2026)
- Tournament creation and configuration
- Category management with new fields: `competition_rules` (renamed from rules) and `event_timeline`
- Athlete registration with partner selection for doubles categories
- Partner linking system for doubles pairs with draw_order for tournament draws
- User search by email or phone in tournament athlete management
- Round and group management
- Match scheduling with pair support and match date field
- Match results and rankings with Excel export
- Cumulative game scores in tournament rankings
- Bracket editor with athlete reassignment and cascade warning
- Bracket slot swapping with null athletes and bye matches
- **NEW (Mar 2026)**: Modular Alpine.js dashboard with sidebar navigation
- **NEW (Mar 2026)**: Draw/seeding with auto-assign and manual modes
- **NEW (Mar 2026)**: Group setup and management UI
- **NEW (Mar 2026)**: Real-time match tracking and score entry
- **NEW (Mar 2026)**: Category-based rankings and statistics

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
- **Gender-Aware Skill Level Mapping**:
  - Female players receive +0.5 level at same ELO (aligned with Vietnam tournament standards)
  - 8 skill levels: 2.0, 2.5, 3.0, 3.5, 4.0, 4.5, 5.0, 5.5+
  - Vietnamese and English level names
  - Nullable gender field (defaults to male for backward compatibility)
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

### 11. Point Earning System
- **16 Point Tasks** across 4 user roles (user, home_yard, referee, expert_host)
- **Task Categories**: Daily, Social, Event, Tournament
- **Frequency Types**: Unlimited, Daily, Weekly, Monthly, Once
- **Proof Types**: None (auto-award), Image, Link, QR Code
- **User Wallet System**: Track point balance and transaction history
- **Point Tasks**:
  - User tasks: Referral (100 pts), Check-in Stadium (10 pts daily), Weekly 5 Matches (30 pts), Join Event (50 pts), Join Club (50 pts), Create OCR Match (20 pts)
  - Social tasks: Join FB Group (50 pts), Follow FB Page (30 pts), Subscribe YouTube (30 pts), Follow TikTok (30 pts)
  - HomeYard tasks: Update Stadium Info (50 pts), Create Social Schedule (40 pts), Create Tournament (100 pts)
  - Referee task: Score Match (30 pts per match)
  - Expert task: Verify ELO (50 pts)
- **Special Challenges**: Time-limited challenges with max participants
- **Events System**: Workshop/event QR check-in with point rewards
- **Social Profile Verification**: One-time verification for Facebook, YouTube, TikTok
- **Submission Workflow**: User submits proof → Admin reviews → Approve/Reject → Points awarded

### 12. Club Activity Casual Match System
- **3 Match Generation Algorithms**:
  - Singles Round-Robin: Each player vs each other player once
  - Rotating Doubles: Partners rotate each round (no duplicate pairings)
  - Fixed Doubles: Pre-defined team pairs with court/time assignment
- **Match Management**: Round-based scheduling, score entry, auto-standings
- **Score Confirmation Workflow**: Pending confirmation → Confirmed/Rejected → Admin confirmed
- **Standings Calculation**: Per-player stats (wins, losses, points_for, points_against)
- **Match Format Options**: Best of 1, 3, or 5 sets; points per set (default: 21)
- **Waitlist Auto-Promotion**: Automatic participant promotion from waitlist when spots open
- **UI**: Modal-based match generation, custom match creation, standings display
- **API**: 7+ AJAX endpoints for matches/rounds/standings operations

### 13. Club Check-in & Leaderboard [NEW - Mar 2026]
- Real-time check-in tracking with timestamp and status
- Per-activity leaderboard with player rankings
- Stats calculation: wins, losses, points_for, points_against
- Leaderboard filtering and sorting options
- Dashboard tabs: Check-in tab shows check-in list with status, Leaderboard tab shows standings
- Admin controls for check-in management
- Integration with club activity lifecycle

### 14. League Management (MLP Format) [NEW - Mar 2026]
- MLP league format with 6 sub-game doubles pairing
- League creation and status tracking
- Team management and roster assignment
- Match round generation and scheduling
- Game-by-game score tracking with player pair support
- Automatic standings calculation
- Player assignment interface with user search
- Round editing and modification capabilities
- League association with clubs

### 15. League Registration System [NEW - Mar 2026]
- User self-registration with phone number normalization
- Payment proof upload for verification
- Admin approval workflow for league registration
- Auto team generation with two modes:
  - **Skill-Ranked (Snake-Draft)**: Fair distribution based on ELO
  - **Random Pairing**: Equal random team assignment
- Race-condition safe with DB::transaction + lockForUpdate
- Email notification on registration status changes

### 16. News & CMS
- News articles with categories
- Featured content
- Static pages (About, Contact, etc.)

### 17. User Authentication
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
- Gender-aware skill level mapping (+0.5 for female players)
- 8-level skill system with Vietnamese and English names
- Cross-validation fraud detection
- Time-based validation (3-20 min)
- ELO capping based on experience level
- Re-quiz cooldown policy enforcement
- Guest preview mode
- Admin attempt flagging and review

**FR11: Point Earning System**
- 16 point tasks with role-based access control
- Task eligibility checking (frequency, completion status)
- Proof submission with validation (image, link, QR code)
- Admin approval workflow for submissions
- Wallet system with point balance tracking
- Transaction history with metadata
- Social profile verification (Facebook, YouTube, TikTok)
- Special challenges with time limits and participant caps
- Event check-in system with QR code validation
- Automated point awarding for system-triggered tasks

**FR12: League Management System (MLP Format)**
- MLP league format with 6 sub-game doubles pairing
- League CRUD operations with status tracking
- Team and player roster management with player pair support
- Match round generation and scheduling
- Game-by-game score entry with auto winner calculation
- Standings calculation and real-time updates
- User search interface for player assignment
- Round editing and modification capabilities
- League-club association for organization

**FR13: League Registration System**
- User self-registration with phone number normalization
- Payment proof upload with admin verification
- Admin approval workflow for registration acceptance
- Auto team generation with skill-ranked (snake-draft) mode
- Auto team generation with random pairing mode
- Race-condition safe DB operations with pessimistic locking
- Email notifications for registration status changes

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
- **users**: User accounts with OAuth support, soft deletes
- **stadiums**: Venue information
- **courts**: Individual courts within stadiums
- **court_pricings**: Time-based pricing tiers
- **bookings**: Court reservations with booking_code, confirmed_at, transfer_proof

### Tournament Entities
- **tournaments**: Tournament configuration
- **tournament_categories**: Skill/age categories (singles/doubles)
- **tournament_athletes**: Registered participants with partner_id and draw_order for doubles
- **rounds**: Tournament rounds
- **groups**: Group stage groupings
- **group_standings**: Group rankings
- **matches**: Individual matches with pair support and referee assignment

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
- **users**: Added profile fields (avatar, location, province_id, gender)
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
- **users**: Added quiz tracking fields (quiz_completed_at, quiz_elo_assigned, can_retake_quiz_at) and gender field for skill level mapping

### Club System Entities
- **clubs**: Club management and configuration
- **club_activities**: Club activity tracking (type: one_off/recurring/competition)
- **club_activity_participants**: RSVP participants with status
- **club_activity_match_rounds**: Match rounds with status
- **club_activity_matches**: Individual matches (singles/doubles) with player IDs
- **club_activity_match_standings**: Per-player standings with win/loss tracking
- **club_join_requests**: Club join request management
- **club_posts**: Club discussion posts
- **club_post_comments**: Comments on club posts
- **club_post_media**: Media in club posts
- **club_post_reactions**: Reactions/likes on posts
- **club_competition_teams**: Teams in competitions
- **club_competition_matches**: Competition matches with scores
- **club_competition_standings**: Competition standings

### Point Earning Entities
- **point_tasks**: 16 tasks with code, points, role, category, frequency, proof_type
- **point_submissions**: Proof submissions with status, proof_data, admin review
- **user_wallets**: User point balance
- **user_point_transactions**: Transaction history with type, description, metadata
- **social_profile_verifications**: Social platform verification records
- **special_challenges**: Time-limited challenges with max participants
- **events**: Workshop/event system with QR check-in
- **event_checkins**: User event attendance records

### League Management Entities (2026-02-25+)
- **leagues**: League configuration (name, description, sport, format, status, stadium_id, created_by)
- **league_teams**: Team enrollment with league_id, team_id, team_name, seed_position
- **league_team_players**: Player roster with team_id, player_id, player_name
- **league_rounds**: Tournament rounds with league_id, round_number, is_finished
- **league_matches**: Match records with league_id, round_id, team_1_id, team_2_id, status
- **league_match_games**: Game-by-game scores with match_id, game_number, team_1_score, team_2_score, winner_id, MLP player pair support (home_player_1/2_id, away_player_1/2_id)
- **league_standings**: Calculated standings with league_id, team_id, wins, losses, points

### League Registration Entities (2026-03-09)
- **league_registrations**: Registration records with league_id, user_id, phone (normalized), payment_proof, status (pending/approved/rejected), approved_by, approved_at
- **league_registration_players**: Player roster assignments from registration with league_registration_id, player_id

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

### Phase 2: Enhanced Features (Complete - 100%)
- [x] User profile management with avatar upload
- [x] Referee system with match officiating
- [x] Doubles pair selection for tournament categories
- [x] Skill assessment quiz system (260103-1200)
- [x] Gender-aware skill level mapping (260115-2019)
- [x] Point earning system with 16 tasks (260114-1601)
- [x] Booking history and management
- [x] Club system with posts, comments, reactions
- [x] Booking code generation and confirmation tracking
- [x] Transfer proof system for booking transfers
- [x] League management with MLP format (260309)
- [x] Club management API endpoints
- [ ] Online payment integration
- [ ] Real-time notifications for match invites and activities
- [ ] Mobile app with OPRS integration
- [ ] Advanced analytics dashboard

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
