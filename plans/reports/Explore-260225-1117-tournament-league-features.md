# Pickleball Project Exploration Report
**Tournament & League Features Analysis**

**Date**: 2026-02-25
**Explored By**: Claude Code (Explore Agent)
**Work Context**: /Users/thaopv/Desktop/php/pickleball

---

## Executive Summary

The Pickleball Platform is a comprehensive Laravel 10 web application with well-established tournament management, instructor, OCR/OPRS ranking, and social features. The project has robust infrastructure for tournament operations with tournament categories, athletes, matches, groups, and standings already implemented. Admin panel supports full tournament CRUD operations; Home Yard dashboard provides stadium owner interface for tournament management.

---

## 1. Current Features & Modules

### 1.1 Controllers Overview

#### Admin Controllers (22 total)
Located: `/app/Http/Controllers/Admin/`

**Tournament & Athlete Management**
- `TournamentController` - Admin tournament CRUD, category creation, athlete registration
- Base path: `/admin/tournaments/*`
- Features: Index (paginated), Create, Show (with athletes list), Edit, Update, Delete
- Supports: Tournament types (beginner/intermediate/advanced/professional), OCR flag, featured flag, images, gallery

#### Front/Public Controllers (26+ total)
Located: `/app/Http/Controllers/Front/`

**Key Tournament Controllers**
1. `HomeYardTournamentController` - Stadium owner tournament management
   - Index, Store, Show, Edit, Update, Destroy
   - Athlete search and management
   - Booking management for tournaments
   - Match and standings views
   
2. `TournamentRegistrationController` - Athlete registration workflow
   
3. `AthleteManagementController` - Tournament athlete CRUD
   
4. `CategoryController` - Category management
   
5. `RoundController` - Round creation and management
   
6. `GroupController` - Group stage management

#### API Controllers (24+ total)
Located: `/app/Http/Controllers/Api/`

**Tournament API**
- `Api/TournamentController` - API endpoints for tournament operations
- Full REST support for programmatic access

#### Referee System
- `Front/RefereeController` - Referee dashboard with match officiating
- `Api/RefereeController` - Referee API endpoints
- `Front/RefereeProfileController` - Public referee profiles
- `Api/RefereeProfileController` - Referee directory API

---

### 1.2 Models & Data Structure

**Core Tournament Models** (Located: `/app/Models/`)

| Model | Purpose | Key Fields |
|-------|---------|-----------|
| `Tournament` | Main tournament entity | id, user_id, name, slug, description, start_date, end_date, registration_deadline, location, max_participants, price, prizes, status (boolean), is_watch, is_ocr, is_featured, image, gallery (json), banner |
| `TournamentCategory` | Singles/doubles categories | id, tournament_id, category_type (enum), category_name, status |
| `TournamentAthlete` | Registered participants | id, tournament_id, user_id, category_id, partner_id (for doubles), status, draw_order (for draws) |
| `TournamentReferee` | Referee assignments | id, tournament_id, user_id, assigned_at, assigned_by, status |
| `Round` | Tournament rounds | id, tournament_id, name, status |
| `Group` | Group stage groupings | id, tournament_id, category_id, round_id, group_name, group_code, max_participants, current_participants, advancing_count, status |
| `GroupStanding` | Group rankings | id, group_id, athlete_id, rank_position, matches_played, wins, losses, points, is_advanced |
| `MatchModel` | Individual matches | id, tournament_id, group_id, category_id, challenger_id, opponent_id (singles) or pair_1/pair_2 (doubles), referee_id, set scores, winner_id, status, result_submitted_at, confirmed_at |
| `MatchEvent` | Match event records | id, match_id, event_type, timestamp |

**Related Models**
- `Category` - News categories (separate from tournament categories)
- `TournamentAthlete` relationships include self-reference for partner selection
- Media support via Spatie MediaLibrary (banner, gallery images)

---

### 1.3 Database Migrations

**Tournament-Related Migrations** (in `/database/migrations/`)

```
2025_11_17_000001_create_tournaments_table
2025_11_17_000002_create_tournament_athletes_table
2025_11_17_000003_create_tournament_categories_table
2025_11_17_000004_create_rounds_table
2025_11_17_000005_create_courts_table (stadium context)
2025_11_17_000006_create_matches_table
2025_11_17_000007_create_groups_table
2025_11_17_000008_create_group_standings_table
2025_11_17_000009_add_tournament_management_columns_to_tournaments_table
2025_11_17_000010_add_tournament_management_columns_to_tournament_athletes_table
2025_11_17_000011_add_group_foreign_key_to_matches_table
2025_11_19_000001_create_tournament_categories_table (duplicate/legacy)
2026_01_12_add_is_featured_to_tournaments_table
2026_02_02_113709_add_draw_order_to_tournament_athletes_table
```

**Recent Tournament Enhancements**
- Draw order support for tournament bracket generation (2026-02-02)
- Featured tournaments flag (2026-01-12)
- Tournament management column additions (2025-11-17)

---

### 1.4 Views & UI

**Admin Tournament Views** (`/resources/views/admin/tournaments/`)
- `index.blade.php` - Tournament list with search/filter
- `form.blade.php` - Create/edit form with extensive fields
- `edit.blade.php` - Edit view wrapper

**Home Yard Tournament Views** (`/resources/views/home-yard/tournaments/`)
- `index.blade.php` - Tournament list dashboard
- `tournaments.blade.php` - Main tournaments view (11KB)
- `show.blade.php` - Tournament detail view
- `form.blade.php` - Create/edit form (31KB)
- `edit.blade.php` - Edit wrapper
- `create.blade.php` - Create wrapper
- `overview.blade.php` - Tournament overview/dashboard
- `athletes.blade.php` - Athlete management (41KB)
- `courts.blade.php` - Court assignment (64KB)
- `matches.blade.php` - Match management (69KB)
- `rankings.blade.php` - Standings/rankings (47KB)
- `bookings.blade.php` - Tournament booking management (84KB)

**Public Tournament Views** (`/resources/views/front/tournaments/`)
- Tournament listing and detail pages

---

### 1.5 User Roles & Permissions

**Role Hierarchy** (via Spatie Laravel Permission)

```
admin
  - Full system access via /admin
  - Manages all tournaments, users, content
  
home_yard
  - Stadium/tournament management via /homeyard
  - Create/manage own tournaments
  - Can see only their own tournaments (enforced in TournamentController)
  - Assign referees to tournaments

user
  - Public features, bookings, registrations
  - Can register as tournament athlete
  
referee
  - Match officiating via /referee
  - Dashboard with assigned matches
  - Score entry for matches
  - View match details
```

**Permission Examples**
- Role checks in controllers: `middleware(['auth', 'role:admin|home_yard'])`
- Authorization policies in place for tournament operations
- Tournament ownership check: `Tournament::where('user_id', $user->id)`

---

## 2. Existing Tournament Features

### 2.1 Tournament Creation & Configuration

**Supported Features**
- Single/Double categories (singles_men, singles_women, doubles_men, doubles_women, doubles_mixed)
- Tournament ranking: beginner, intermediate, advanced, professional
- OCR integration flag (is_ocr boolean)
- Featured tournament flag (is_featured boolean)
- Watch tournament flag (is_watch boolean) 
- Registration deadline with datetime support
- Banner and gallery image uploads
- Detailed fields: description, location, max_participants, price, prizes
- Rich text fields: rules, competition_rules, event_timeline, social_information, registration_benefits
- Organizer contact info: email, hotline
- Competition schedule and results tracking

### 2.2 Athlete Management

**Features**
- Athlete registration workflow
- Partner selection for doubles categories
- Draw order tracking for bracket generation
- Athlete status tracking (registered, grouped, eliminated, etc.)
- Excel export for athlete lists
- Bulk operations support

### 2.3 Match Management

**Match Features**
- Supports singles and doubles (via pair fields)
- Referee assignment to matches
- Set-by-set score tracking
- Automatic winner calculation
- Match status: scheduled, in_progress, completed
- Result submission and confirmation workflow
- Evidence upload for disputed matches

### 2.4 Group Stage & Rankings

**Features**
- Group creation for group stages
- Automatic standing calculations
- Points tracking (wins/losses/points)
- Advancement determination
- Group status tracking
- Ranking queries by position

### 2.5 Referee System

**Referee Features**
- Dedicated referee role with Spatie Permission
- Referee profile fields: bio, status, rating, matches_officiated_count
- Tournament referee assignment by Home Yard
- Referee dashboard with stats
- Match assignment
- Score entry
- Match status transitions
- Public referee directory
- Rating/review system

---

## 3. Admin Panel Capabilities

**Admin Dashboard** (`Admin/DashboardController`)
- System overview

**Tournament Management** (`Admin/TournamentController`)
- View all tournaments (paginated)
- Create new tournaments
- Edit tournament details
- Delete tournaments
- Category management (create/update)
- Search functionality

**Admin Controls**
- User management with roles
- Content moderation
- System settings
- Activity logging

---

## 4. Database Structure Insights

### Table Schema Summary

**tournaments table**
```
- id (PK)
- user_id (FK) - stadium owner
- slug (unique identifier)
- name, description
- start_date, end_date, registration_deadline
- location, max_participants
- price (decimal), prizes
- status (boolean), is_watch, is_ocr, is_featured
- rules, competition_rules, event_timeline
- image, gallery (JSON), banner
- Timestamps
```

**tournament_athletes table**
```
- id (PK)
- tournament_id (FK)
- user_id (FK)
- category_id (FK)
- partner_id (nullable FK) - for doubles
- draw_order (nullable)
- status
- Timestamps
```

**tournament_categories table**
```
- id (PK)
- tournament_id (FK)
- category_type (enum)
- category_name
- status
- Timestamps
```

**matches table**
```
- id (PK)
- tournament_id (FK)
- group_id (FK)
- category_id (FK)
- challenger_id (FK) - singles
- opponent_id (FK) - singles or pair_1/pair_2 for doubles
- referee_id (nullable FK)
- Set scores (set1_p1, set1_p2, set2_p1, etc.)
- winner_id (nullable FK)
- status
- result_submitted_at, confirmed_at
- Timestamps
```

---

## 5. Existing Services & Business Logic

**Service Classes** (Located: `/app/Services/`)

| Service | Purpose |
|---------|---------|
| `EloService` | Elo rating calculations for OCR system |
| `BadgeService` | Achievement badge awarding |
| `OprsService` | OPRS score calculation (Elo 70%, Challenge 20%, Community 10%) |
| `OprVerificationService` | OPRS verification requests |
| `ChallengeService` | Challenge submissions and verification |
| `CommunityService` | Activity tracking and bonuses |
| `SkillQuizService` | Quiz scoring and fraud detection |
| `PointEarningService` | Point task eligibility and auto-award |
| `PointSubmissionService` | Submission workflow and admin review |
| `SocialVerificationService` | Social platform verification |
| `ClubPostMediaService` | Club post media management |

---

## 6. API Endpoints

**Tournament API Routes** (in `/routes/api.php`)

```
GET    /api/tournaments              - List tournaments
GET    /api/tournaments/{id}         - Get tournament detail
POST   /api/tournaments              - Create tournament
PUT    /api/tournaments/{id}         - Update tournament
DELETE /api/tournaments/{id}         - Delete tournament

GET    /api/tournaments/{id}/athletes - List athletes
POST   /api/tournaments/{id}/athletes - Register athlete
GET    /api/tournaments/{id}/matches  - List matches
POST   /api/tournaments/{id}/matches  - Create match

GET    /api/tournaments/{id}/standings - Get standings
GET    /api/tournaments/{id}/groups    - List groups
```

**Referee API Routes**
```
GET    /api/referee/matches         - Assigned matches
GET    /api/referee/matches/{id}    - Match details
POST   /api/referee/matches/{id}/score - Submit score
GET    /api/referee/profile         - Referee info
```

---

## 7. Key Integration Points

### Tournament <-> Booking Integration
- Tournament bookings tracked in `bookings.blade.php` (84KB view)
- Booking code system: BK{courtId:3+}{date:YYMMDD}{seq:3}
- Transfer proof tracking for tournament transfers

### Tournament <-> OCR Integration
- Tournaments can be flagged as OCR events (is_ocr flag)
- OCR matches can be created within tournaments
- Elo ratings updated based on tournament results

### Tournament <-> Referee Integration
- Home Yard assigns referees to tournaments
- Matches can have referee assignments
- Referee dashboard shows assigned matches

### Tournament <-> Points System
- Point task: "Create Tournament" (100 points)
- Points awarded to Home Yard users for tournament creation

---

## 8. What's NOT Yet Implemented (League-Specific)

Based on exploration, these league-specific features are NOT yet implemented:

1. **League Management**
   - No League model
   - No multi-tournament league grouping
   - No inter-tournament standings/rankings

2. **League Standings**
   - No league-wide point accumulation
   - No cross-tournament athlete rankings
   - No league leaderboards

3. **League Divisions**
   - No division system within leagues
   - No promotion/relegation mechanics

4. **League Scheduling**
   - No automated schedule generation
   - No round-robin league scheduling

5. **League Registration**
   - No league registration workflow
   - No team/club league participation

6. **League Payouts**
   - No league prize pool management
   - No automatic payout calculation

7. **League Notifications**
   - No league-specific notifications
   - No league update emails

---

## 9. Current Code Quality & Standards

**Code Organization**
- Clear separation: Admin/Api/Front controllers
- Service layer for business logic (12 services)
- Model relationships well-defined
- Trait usage for shared functionality (SyncMediaCollection, Sluggable)

**Database**
- 165+ migrations tracking complete evolution
- Proper foreign key constraints
- JSON field support for flexible data (gallery, athlete_types)
- Timestamps on all models

**Security**
- Role-based access control via Spatie Permission
- Authorization policies enforced
- CSRF token protection
- Middleware stack in place

**Storage**
- Spatie MediaLibrary for tournament media
- File storage for images (tournament_images, tournament_banner, tournament_gallery)
- Storage facade for proper handling

---

## 10. File Organization

```
app/
├── Http/Controllers/
│   ├── Admin/TournamentController.php (290 lines)
│   └── Front/HomeYardTournamentController.php (600+ lines)
├── Models/
│   ├── Tournament.php (149 lines)
│   ├── TournamentAthlete.php
│   ├── TournamentCategory.php
│   ├── TournamentReferee.php
│   ├── Group.php (116 lines)
│   ├── GroupStanding.php
│   ├── Round.php
│   └── MatchModel.php (300+ lines)
└── Services/
    ├── EloService.php
    └── OprsService.php

database/migrations/
├── 2025_11_17_000001_create_tournaments_table.php
├── 2025_11_17_000002_create_tournament_athletes_table.php
├── 2025_11_17_000003_create_tournament_categories_table.php
├── 2025_11_17_000006_create_matches_table.php
├── 2025_11_17_000007_create_groups_table.php
├── 2025_11_17_000008_create_group_standings_table.php
└── [more tournament-related migrations]

resources/views/
├── admin/tournaments/
│   ├── index.blade.php
│   ├── form.blade.php
│   └── edit.blade.php
└── home-yard/tournaments/
    ├── tournaments.blade.php (main view, 11KB)
    ├── athletes.blade.php (41KB)
    ├── courts.blade.php (64KB)
    ├── matches.blade.php (69KB)
    ├── rankings.blade.php (47KB)
    └── bookings.blade.php (84KB)
```

---

## 11. Performance Considerations

**Current Implementation**
- Eager loading in place for relationships
- Pagination on tournament lists (10 per page in admin, 12 in home-yard)
- Group standing queries with ordering
- Match status scoping

**Potential Optimizations Noted**
- Large match management view (69KB) could be componentized
- Athlete list operations might benefit from batch processing
- League queries (if added) could need indexing on tournament_id + status

---

## 12. Testing & Quality

**Test Coverage**
- Repository: `/tests/` directory exists
- Feature tests likely in place for tournament operations
- Database seeders available

---

## Key Findings Summary

### Strengths
1. **Robust Foundation**: Complete tournament system with athletes, matches, and standings
2. **Multi-Role Support**: Admin, Home Yard, Referee, User roles properly implemented
3. **Extensible Architecture**: Service layer ready for additional league logic
4. **Modern Stack**: Laravel 10, Spatie packages, proper OOP structure
5. **UI Maturity**: Comprehensive views for tournament management across all roles
6. **API Ready**: RESTful endpoints for programmatic access

### Ready for League Extension
- Tournament model has slug and user_id for grouping
- Category system can support league structures
- Standings calculation framework in place
- Match status and scoring infrastructure present
- Media and gallery support for league branding

### Gap Areas for League Features
- No league-level grouping model
- No cross-tournament point accumulation
- No league-wide scheduling
- No league registration/team management
- No league-specific permissions/roles

---

## Unresolved Questions

1. What specific league workflow is needed? (single-elimination vs round-robin vs season-based?)
2. Should leagues support multiple concurrent tournaments or sequential seasons?
3. How should league standings aggregate from multiple tournaments?
4. Should leagues have separate roles (league_organizer) or use existing home_yard role?
5. Do teams need separate entity modeling or use TournamentAthlete grouping?
6. Should league payouts be implemented or is this out of scope?

