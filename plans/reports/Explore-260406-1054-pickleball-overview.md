# Laravel Pickleball Platform - Codebase Exploration Report

**Date:** April 6, 2026  
**Project:** OnePickleball  
**Framework:** Laravel 11 PHP  
**Type:** Community pickleball platform with tournaments, clubs, leagues, and match management

---

## 1. BLADE TEMPLATES STRUCTURE (`resources/views/`)

### Total: 318 blade template files organized into 10 main modules

#### **Core Layouts (5 files)**
- `layouts/app.blade.php` - Admin dashboard layout (Bootstrap 5, sidebar nav, dark theme)
- `layouts/front.blade.php` - Public frontend layout (responsive, SEO meta tags, Vietnamese localization)
- `layouts/homeyard.blade.php` - Club/league management dashboard layout
- `layouts/referee.blade.php` - Match referee view layout
- `layouts/verifier.blade.php` - Verification/OCR role layout

#### **Admin Module (60+ files)**
**Directories:**
- `admin/` - Main dashboard
- `admin/categories/` - CRUD operations (create, edit, index)
- `admin/gem-topups/` - In-app currency management
- `admin/instructors/` - Coach registration and management
- `admin/news/` - News article management
- `admin/ocr/` - Output Certification Rating system
  - `ocr/badges/` - Badge management
  - `ocr/disputes/` - Dispute resolution
  - `ocr/matches/` - Match records
- `admin/oprs/` - Skill rating system dashboard
  - `oprs/activities/`, `oprs/challenges/`, `oprs/users/`, `oprs/reports/`
- `admin/pages/` - CMS pages
- `admin/permission-requests/` - User permission approvals
- `admin/point-submissions/` - Player point tracking
- `admin/quizzes/` - Skill quiz management
- `admin/skill-quiz/` - Quiz delivery interface
- `admin/special-challenges/` - Special event challenges
- `admin/stadiums/` - Court/venue management
- `admin/tournaments/` - Tournament CRUD and management
- `admin/users/` - User administration (includes import)
- `admin/videos/` - Video content management

#### **Club Module (43 files)**
**Main features:**
- `clubs/` - Club index, create, edit, join-requests, show, newpage
- `clubs/activities/` - Club activities/events (4 files: create, edit, index, show)
- `clubs/activities/partials/` - 24 reusable component templates:
  - Form controls: type-selector, skill-level-fields, open-play-fields, recurring-fields, competition-fields
  - Panels: rsvp-panel, matches-panel, competition-panel
  - Display: header-banner, participant-list, detail-tab, participants-tab, tab-navigation
  - Modals & scripts: matches-generate-modal, matches-custom-modal, competition-scripts, matches-scripts, tab-scripts
  - Styling: form-styles, show-styles, matches-styles, index-styles, competition-styles
- `clubs/posts/` - Club social feed (9 files)
  - Components: _create-card, _create-modal, _editor, _feed, _comments, _action-buttons, _sidebar, _scripts
  - Display: show.blade.php
- `clubs/tabs/` - Club detail tabs: _about, _events, _members

#### **Home-yard (Organizer Dashboard) - 68 files**
**Manages clubs, leagues, tournaments, stadiums, socials:**
- `home-yard/clubs/` - Club creation/management (5 files)
- `home-yard/leagues/` - League management (11 files)
  - Tabs: overview, matches, standings, teams, registrations
  - Forms and show views
- `home-yard/stadiums/` - Venue management (4 files)
- `home-yard/tournaments/` - Tournament management (20 files + 18 partials)
  - Complex tournament builder with:
    - Core: create, edit, show, overview, dashboard, form
    - Features: athletes, bookings, bracket, courts, draw, matches, rankings
    - Partials: bracket-tree, draw-manual, draw-seeding, category-editor, athletes-modal
    - Match management: matches-row, matches-empty-generate
    - Mobile components: mobile-tabs, athletes-mobile-cards
- `home-yard/socials/` - Social club features (3 files)

#### **Front-end (Public-facing) - 80+ files**
**User-facing modules:**
- `front/` - Homepage, news, courses, instructors, courts, tournaments, FAQ, privacy/terms
- `front/booking-history/` - User booking records
- `front/clubs/` - Player club views: checkin, leaderboard, queue, score-submit
- `front/courses/` - Educational content
- `front/courts/` - Venue listing and details
- `front/gems/` - In-game currency system
  - Partials: balance-card, topup-modal, transaction-list
- `front/instructors/` - Coach profiles
- `front/leagues/` - Player league participation
  - Tabs: info-register, schedule, standings
- `front/ocr/` - Player-facing rating system (6 files)
  - Views: index, leaderboard, athlete-leaderboard, profile, matches-list, ocr-matches-list
  - Challenges: index, submit
  - Community: index, checkin
  - Matches: create, index, show
- `front/opr/` - Verification interface
- `front/partials/` - 10 homepage component templates
  - Sections: hero, features, cta, community, courts, tournaments, news, stats, special-challenge-banner
  - Scripts: home-scroll-reveal-script
- `front/points/` - Player points tracking (4 files)
- `front/quiz/` - Quiz interface
- `front/referees/` - Referee profiles
- `front/skill-quiz/` - Skill assessment (4 files)
- `front/tournaments/` - Tournament details and participation

#### **User Profiles (4 files)**
- `user/dashboard.blade.php` - User dashboard
- `user/profile/edit.blade.php` - Profile editing
- `user/referral/index.blade.php` - Referral program
- `user/wallet/history.blade.php` - Wallet/balance history

#### **Other Modules**
- `auth/` - Login, register, admin-login (3 files)
- `referee/` - Referee views: dashboard, matches index/show (3 files)
- `verifier/` - Verification dashboard and requests (3 files)
- `wallet/` - Wallet display (2 files)
- `pagination/` - Custom pagination (1 file)
- `components/` - Reusable components (6 files)
  - media-uploader.blade.php
  - ocr-badge.blade.php
  - oprs/ (skill-level-badge, level-badge, breakdown-chart, score-card)
- `vendor/` - Third-party package views (pagination, media-library)

---

## 2. PUBLIC ASSETS (`public/assets/`)

### CSS Files (34 files, ~200KB total)

**Core & Layout:**
- `styles.css` - Main stylesheet (reset, colors, typography, grid)
- `style.css` - Alternative styling
- `styles-extended.css` - Extended utilities

**Feature-specific CSS:**
- `club-activity-*.css` (7 files) - Club activity styling
  - checkin, dashboard, index, leaderboard, members, queue, score
- `tournament-*.css` - Tournament styling
  - detail, styles, dashboard, and tournament-dashboard/ subfolder with modular components
- `instructor-review.css` - Coach ratings UI
- `homepage.css` - Landing page
- `booking.css` - Court booking interface
- `courts.css` - Venue listing/details
- `tournaments.css` - Tournament listings
- `styles-coaches.css` - Coach-specific styling
- `styles-courses.css` - Educational content styling
- `styles-club.css` - Club-specific styling
- `styles-news-simple.css` - News article styling
- `court-detail.css` - Venue detail page
- `gallery-lightbox.css` - Image gallery UI
- `purifier.css` - HTML sanitization styles (inferred)

**Tournament Dashboard Submodule (`tournament-dashboard/`):**
- `layout-sidebar.css`
- `bracket-tree.css`
- `components-*.css` (8 files): rankings-table, draw, buttons-alerts, cards, rankings, matches, athletes, rankings-row-states, forms

### JavaScript Files (24 files, ~150KB total)

**Tournament Management:**
- `tournament-dashboard.js` - Main tournament manager
- `tournament-detail.js` - Single tournament view
- `tournament-draw.js` - Draw/bracket logic
- `tournament-matches.js` - Match scheduling
- `tournament-athletes.js` - Athlete registration
- `tournaments.js` - Tournament listings
- `tournament-rankings.js` - Rankings display
- `tournament-matches-api.js` - API integration for matches

**Tournament Mixins (utility functions):**
- `tournament-draw-reset-mixin.js` - Bracket reset logic
- `tournament-draw-manual-sortable-mixin.js` - Manual bracket editing
- `tournament-draw-group-setup-mixin.js` - Group round setup
- `tournament-matches-schedule-mixin.js` - Match scheduling utilities

**Bracket/Match Editing:**
- `bracket-manager.js` - Overall bracket state management
- `bracket-data-fetcher.js` - API data loading
- `bracket-match-editor.js` - Edit single match
- `bracket-score-entry.js` - Score input UI
- `bracket-swap-editor.js` - Swap player/team positions

**Club Activities:**
- `club-activity-dashboard.js` - Activity overview
- `club-activity-checkin.js` - Check-in system
- `club-activity-members.js` - Member management
- `club-activity-leaderboard.js` - Rankings display
- `club-activity-queue.js` - Rotation queue system
- `club-activity-score.js` - Score tracking

**General:**
- `script.js` - Global utility functions

---

## 3. DATABASE MIGRATIONS (195+ files)

### Recent Migrations (Last 20 - Last 4 weeks from Apr 3, 2026)

#### **Gem System (In-app Currency) - 2 migrations**
- `2026_04_03_01_create_gem_wallets_table.php`
  - **Table:** gem_wallets
  - **Fields:** id, user_id (unique, FK), balance (unsigned int, default 0), timestamps
  - **Purpose:** User wallet storage for virtual currency

- `2026_04_03_02_create_gem_transactions_table.php`
  - **Table:** gem_transactions
  - **Fields:** id, user_id (FK), gem_wallet_id (FK), type (enum: top_up, payment, refund, admin_adjust), amount (bigint), balance_after, reference_type/id, description, metadata (json), status (enum: pending, completed, failed, cancelled), timestamps
  - **Indexes:** user_id+created_at, status, reference_type+reference_id
  - **Purpose:** Track all gem transactions and wallet history

#### **Club Activities - Open Play Feature - 2 migrations**
- `2026_03_25_add_score_status_to_club_activity_matches.php`
  - Adds score status tracking to matches

- `2026_03_25_add_score_config_to_club_activities_table.php`
  - Adds score configuration to activities

- `2026_03_23_000001_extend_club_activity_for_open_play.php` (Major)
  - **Adds to club_activities:**
    - `type` enum: adds 'open_play' to existing options
    - `qr_code` (unique string), `courts_count`, `avg_match_duration`, `rotation_mode` (enum: round_robin, oprs_based, random)
    - `gender_preference_enabled`, `oprs_weight` (decimal), `allow_guests`, `started_at`, `ended_at`
  - **Adds to club_activity_participants:**
    - `checked_in_at` (datetime), `gender_preference`, `current_status` (idle, queued, playing, left)
    - `queue_position`, `matches_played_count`, `last_match_ended_at`
  - **Adds to club_activity_matches:**
    - `club_activity_id` (FK), `match_number`, `scheduled_court`, `started_at`, `ended_at`
    - `result_submitted_by` (FK to users), `result_confirmed`, `oprs_processed`, `set_scores` (json)
    - Makes `round_id` nullable for open_play

- `2026_03_23_000002_create_club_member_stats_table.php`
  - **Table:** club_member_stats
  - **Fields:** id, club_id (FK), user_id (FK), total_matches/wins/losses/points_scored/points_against, activities_participated, current_oprs, last_played_at, timestamps
  - **Indexes:** unique(club_id, user_id), FKs
  - **Adds to club_members:** initial_oprs, notes, member_status (active, inactive, suspended)
  - **Purpose:** Track per-club player statistics and rating

#### **Tournament & League Features - 4 migrations**
- `2026_03_13_add_enable_third_place_to_tournaments_table.php`
  - Adds 3rd place match support for tournaments

- `2026_03_10_164129_make_payment_proof_nullable_in_league_registrations_table.php`
  - Makes payment_proof optional (allows free registration)

- `2026_03_10_141029_add_qr_code_image_to_leagues_table.php`
  - Adds QR code for league check-in/sharing

- `2026_03_09_001_create_league_registrations_table.php`
  - **Table:** league_registrations
  - **Fields:** id, league_id (FK), payment_proof, status (pending/approved/rejected), admin_note, timestamps
  - **Purpose:** Player registration for leagues with approval workflow

- `2026_03_09_002_create_league_registration_players_table.php`
  - Links individual players to league registrations

- `2026_03_09_003_add_registration_fields_to_leagues_table.php`
  - Adds registration configuration to leagues

#### **Other Recent Changes**
- `2026_03_07_113719_add_stadium_id_to_tournaments_table.php` - Link tournament to venue
- `2026_03_06_add_mlp_player_pairs_to_league_match_games.php` - Track player pairings in league
- `2026_03_06_add_club_activity_id_to_club_posts_table.php` - Link posts to club activities
- `2026_03_03_add_league_ba_feedback_columns.php` - League feedback tracking
- `2026_03_03_000003_create_club_activity_match_standings_table.php` - Track standings
- `2026_03_03_000002_create_club_activity_matches_table.php` - Match records
- `2026_03_03_000001_create_club_activity_match_rounds_table.php` - Round management

#### **Earlier Key Migrations (Feb-Mar)**
- League feature: 7 migrations (teams, rounds, matches, games, standings)
- Club competitions: 5 migrations (teams, matches, standings)
- Club activity participants: 1 migration

---

## 4. CUSTOM CONFIGURATION FILES (`config/`)

### Custom Configs (2 files beyond Laravel defaults)

#### **1. `club_posts.php`**
Controls user-generated content in clubs:
```php
[
    'disk' => 'public' (configurable via env),
    'content' => [
        'max_length' => 5000 chars,
        'allowed_tags' => '<p><br><strong><em><s><a><ul><ol><li>'
    ],
    'images' => [
        'max_count' => 10,
        'max_size' => 5 MB,
        'allowed_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
    ],
    'videos' => [
        'max_count' => 1,
        'max_size' => 50 MB,
        'allowed_mimes' => ['mp4', 'mov', 'webm']
    ],
    'feed' => ['per_page' => 10]
]
```

#### **2. `gems.php`** (In-app currency system)
Manages virtual currency and payments:
```php
[
    'exchange_rate' => 1000 (VND per gem, configurable),
    'cashback_percent' => 5 (default),
    'min_topup_vnd' => 50000,
    'max_topup_vnd' => 5000000,
    'bank' => [
        'account_number' => env(...),
        'bin' => env(...),
        'name/account_name' => env(...)
    ],
    'sepay' => [
        'account_number' => env(...),
        'bank_code' => env(...),
        'api_key' => env(...),
        'allowed_ips' => '14.225.204.68,103.163.218.2,...'
    ]
]
```
**Purpose:** Configure Vietnamese payment gateway (SePay) integration for gem top-ups

### Standard Laravel Configs Used
- `app.php` - Application config
- `auth.php` - Authentication (JWT + Laravel defaults)
- `database.php` - Database connections
- `filesystems.php` - Storage disks
- `jwt.php` - JWT token configuration
- `permission.php` - Spatie permission roles/gates
- `media-library.php` - Spatie media library
- `purifier.php` - HTML sanitization
- `sanctum.php`, `session.php`, `cache.php`, `queue.php`, `mail.php`, `logging.php`, `broadcasting.php`, `cors.php`, `hashing.php`, `view.php`, `services.php`

---

## 5. ARCHITECTURE OVERVIEW

### Module Structure
```
OnePickleball Platform
├── Public Frontend (front/) - 80+ views
│   ├── Booking system (courts, lessons)
│   ├── Tournament participation
│   ├── League registration
│   ├── Player ratings (OCR/OPR systems)
│   ├── Club membership
│   └── In-game currency (Gems)
├── Admin Dashboard (admin/) - 60+ views
│   ├── User & permission management
│   ├── Content (news, videos, pages)
│   ├── Events (tournaments, stadiums, courses)
│   ├── Skill system (quizzes, OCR disputes)
│   └── Financial (gem top-ups)
├── Club/League Management (home-yard/) - 68 views
│   ├── Club creation & member management
│   ├── League setup & team management
│   ├── Tournament creation & bracket management
│   ├── Stadium/court management
│   └── Social features
├── Club Features (clubs/) - 43 views
│   ├── Club posts & social feed
│   ├── Club activities (events/sessions)
│   └── Club member profiles & tabs
└── Specialized Roles (referee/, verifier/, user/)
    ├── Match officiating
    ├── Rating verification
    └── User profile management
```

### Key Features Identified
1. **Tournament Management** - Complex bracket system, draws, seeding, manual editing
2. **Club Activities** - Events with RSVP, competitions, open-play rotation
3. **Skill Rating Systems** - OCR (Output Certification Rating) and OPR (player ratings)
4. **League System** - Team competitions with registrations, payments, standings
5. **In-app Currency** - Gem system with VND exchange rates, SePay payment integration
6. **User Roles** - Admin, organizer, referee, verifier, player, coach, instructor
7. **Social Features** - Club posts, comments, member networking
8. **Point/Badge System** - Achievement tracking, skill verification

### Technology Stack
- **Backend:** Laravel 11, PHP
- **Frontend:** Blade templates, Bootstrap 5, jQuery, vanilla JS
- **Storage:** Media Library (Spatie), file uploads
- **Auth:** JWT + Laravel Sanctum
- **Permissions:** Spatie Permissions package
- **Localization:** Vietnamese language (vi_VN)
- **Payment:** SePay Vietnamese payment gateway

---

## Summary Statistics

| Metric | Count |
|--------|-------|
| Blade Templates | 318 |
| CSS Files | 34 |
| JavaScript Files | 24 |
| Database Migrations | 195+ |
| Custom Config Files | 2 |
| View Directories | 35+ |
| Admin Features | 15+ modules |
| Main Feature Areas | 8 |

