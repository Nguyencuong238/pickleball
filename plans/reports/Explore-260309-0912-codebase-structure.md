# Codebase Structure Exploration Report

**Project:** Pickleball Laravel Application
**Date:** 2026-03-09
**Scope:** Routes, Views, Config, Database, Public Assets

---

## 1. ROUTES (routes/)

### File Summary
- **Total Files:** 6 PHP route files
- **Total LOC:** 1,221 lines

### Route Files Breakdown

| File | LOC | Purpose |
|------|-----|---------|
| `/routes/web.php` | 720 | Web routes (blade-rendered views, form submissions) |
| `/routes/api.php` | 422 | API routes (REST endpoints, JSON responses) |
| `/routes/channels.php` | 18 | Broadcasting channels (Laravel Echo) |
| `/routes/console.php` | 19 | Console commands |
| `/routes/debug.php` | 21 | Debug endpoints |
| `/routes/test.php` | 21 | Test endpoints |

### Key Route Groups

#### Web Routes (720 LOC)
**Authentication:**
- Login/Register/Logout (standard + Google + Facebook OAuth)
- Admin login
- Referral system debug endpoints

**Frontend Pages:**
- Home, FAQ, Terms, Privacy
- Courts listing/detail
- Tournaments listing/detail
- Social play/join
- News listing/detail
- Instructor listing/detail
- Academy/courses
- Booking system

**Authenticated User Routes:**
- Dashboard
- Profile management
- OPR verification
- OCR (OnePickleball Championship Ranking)
- Skill quizzes
- Point system
- Referee interface
- Verifier dashboard
- Wallet/transaction history

**Admin Routes:**
- User management + import
- Stadiums, Tournaments, News, Pages
- Categories, Quizzes
- OCR disputes/badges
- Skill quizzes
- Point tasks + submissions
- Permission requests
- OPR activities
- Instructor registrations/management
- Videos, Special challenges

**HomeYard (Stadium Owner) Routes:**
- Dashboard
- Stadium management
- Tournament management (create/edit, athletes, courts, rankings, matches)
- Bookings (view/search)
- Staff management
- Social play management
- Club management
- Leagues
- Athletes management

**Club Routes:**
- Club dashboard
- Activities (list, create, detail, participants, competitions)
- Posts (list, create, detail, reactions, comments)
- Join requests

#### API Routes (422 LOC)
**Public Endpoints (no auth required):**
- Authentication: register, login, refresh token
- Stadiums: list, detail, bank info
- Locations: provinces
- Tournaments: list, detail, standings, register
- Socials: list, detail, participants
- News: categories, list, detail
- OCR: leaderboard, user stats, elo, badges
- OPRS: levels, leaderboard by level/distribution, user profile, matchmaking suggestions
- Skill quizzes (eligibility, history)
- Referees: list, detail

**Protected Endpoints (auth:api):**
- OCR: match CRUD, accept/reject, submit/confirm results, dispute, evidence upload
- OPRS: user profile, breakdown, history, estimate rating change
- Challenges: available, submit, history, stats
- Community: check-in, events, referrals, social activity, history, stats
- Bookings: list, history, detail, update, delete, booking creation
- User profile: update profile/avatar/email/password
- Events: check-in, history
- Points: tasks, balance, history, submissions, challenges
- Clubs: CRUD, join requests, member management, activities (with participants + competitions), posts
- Users: delete account
- Leagues: list (authenticated), detail/standings/schedule (public)
- Skill quizzes: start, attempt, questions, answers, submit, result
- Referees: dashboard, match management, scoring, sync events

**Mixed Auth:**
- Bookings confirmation/rejection (web guard for HomeYard users)
- Booking slots calendar (public)

---

## 2. VIEWS (resources/views/)

### File Summary
- **Total Files:** 227 blade templates
- **Total LOC:** 72,976 lines
- **Directory Structure:** 45+ subdirectories organized by functional area

### Top-Level View Directories

| Directory | Purpose | Key Templates |
|-----------|---------|----------------|
| `/admin` | Admin control panel views | Dashboard, user mgmt, OCR disputes, OPRS activities, quizzes |
| `/front` | Public-facing frontend | Home, tournaments, courts, OCR, OPR, points, skill-quiz |
| `/home-yard` | Stadium owner dashboard | Stadiums, tournaments, athletes, bookings, leagues, staff, clubs |
| `/auth` | Authentication views | Login, register, admin-login |
| `/user` | User profile/account | Dashboard, profile edit, wallet history, referral |
| `/clubs` | Club management | Activities, posts, join-requests, tabs |
| `/referee` | Referee interface | Dashboard, matches |
| `/verifier` | Verifier dashboard | Match verification |
| `/layouts` | Shared layouts | Base templates for different sections |
| `/components` | Reusable components | OPRS badges, OCR badges, media uploader |
| `/pagination` | Pagination templates | Multiple pagination styles |
| `/vendor` | 3rd-party packages | Media library, pagination overrides |

### Major View Sections

**Admin (25+ templates):**
- Skill quizzes (index, show, dashboard)
- OCR: disputes, matches, badges
- OPRS: users detail, activities, challenges, reports, dashboard
- Permission requests
- Instructor registrations, videos, news, pages
- Categories, quizzes
- Stadiums, tournaments
- Point submissions, tasks
- Special challenges

**Front (60+ templates):**
- Skill quizzes (index, start, quiz)
- OCR (index, matches, challenges, community, leaderboard)
- OPR (verification form, status)
- Tournaments (list, detail, tabs-section)
- Courts (detail)
- Social play
- Points (dashboard)
- Booking history
- Instructors/courses
- Referees
- Quiz
- Page content

**HomeYard (20+ templates):**
- Stadium management (form, list, edit, create)
- Tournament management (create, edit, athletes, courts, rankings, matches)
- Athletes management
- Bookings management
- Staff + shifts
- Socials detail
- Clubs management
- Leagues dashboard

**Clubs (10+ templates):**
- Activities (index, detail, partials)
- Posts (_editor, _action-buttons)
- Join requests
- Tabs/sections

---

## 3. CONFIG (config/)

### File Summary
- **Total Files:** 20 config files
- **Purpose:** Application configuration, services, guards, databases, caching, queues

### Config Files

| File | Purpose |
|------|---------|
| `app.php` | Core app config (name, timezone, locale, aliases, providers) |
| `auth.php` | Authentication guards (web, api), password reset |
| `database.php` | Database connections (MySQL default) |
| `cache.php` | Caching backends (file, redis, memcached) |
| `session.php` | Session configuration |
| `queue.php` | Job queues (database, redis, sync) |
| `mail.php` | Email configuration |
| `broadcasting.php` | WebSocket broadcasting |
| `jwt.php` | JWT authentication (likely for API guards) |
| `sanctum.php` | API token authentication |
| `cors.php` | CORS middleware configuration |
| `hashing.php` | Password hashing algorithm |
| `logging.php` | Log channels and formatting |
| `filesystems.php` | File storage (local, S3, etc.) |
| `media-library.php` | Media/image library config |
| `permission.php` | Permission/role system |
| `purifier.php` | HTML purification config |
| `view.php` | View paths and caching |
| `services.php` | Third-party service credentials (Google, Facebook, etc.) |
| `club_posts.php` | Custom: Club posts feature configuration |

---

## 4. DATABASE (database/)

### File Summary
- **Total Migrations:** 184 files
- **Total Seeders:** 20 seeders
- **Total Factories:** 7 factories
- **Total LOC (Database):** 9,973 lines

### Recent Migrations (Last 10)
1. `2026_03_06_add_mlp_league_format_to_leagues_table.php` - MLP league format
2. `2026_02_25_add_join_request_status_to_clubs_table.php` - Club join status
3. `2026_02_07_add_booking_code_to_bookings_table.php` - Booking codes
4. `2026_01_20_create_club_post_comments_table.php` - Club post comments
5. `2026_01_20_create_club_post_reactions_table.php` - Club post reactions
6. `2026_01_15_201900_add_gender_to_users_table.php` - User gender field
7. `2026_01_14_160200_create_point_submissions_table.php` - Point submission system
8. `2026_01_07_105839_create_club_join_requests_table.php` - Club join requests
9. `2026_01_02_120004_create_skill_quiz_answers_table.php` - Quiz answers
10. `2025_12_23_add_referral_code_to_users_table.php` - Referral system

### Major Database Features
- **Authentication:** Users with roles (admin, referee, verifier, homeyard)
- **Tournaments:** Categories, athletes, referees, rankings, rounds, groups
- **Stadiums:** Courts, pricing, booking system with codes
- **OCR System:** Matches, elo ratings, badges, disputes
- **OPRS System:** User ratings, skill domains, verification requests
- **Clubs:** Members, activities, posts, competitions, join requests
- **Instructors:** Certifications, experiences, locations, packages, schedules
- **Bookings:** Court bookings with timestamps and confirmation status
- **Points System:** Tasks, submissions, transactions, earning system
- **Skill Quizzes:** Attempts, questions, answers, domains
- **Leagues:** Teams, matches, games, standings, rounds (with MLP format)
- **Social Play:** Events with check-in system
- **Media:** Spatie Media Library integration

### Seeders (20 files)
- User seeding (roles, data)
- Tournament/Category/Round seeding
- Stadium/Court seeding
- News/Page seeding
- Instructor seeding
- Quiz/Question seeding
- Permission seeding
- Media seeders

### Factories (7 files)
- User factory
- Tournament factory
- Stadium factory
- News factory
- Booking factory
- Quiz factory
- Other model factories

---

## 5. PUBLIC ASSETS (public/)

### File Summary
- **Total Assets:** 22 files
- **CSS Files:** 17 (17,392 LOC)
- **JavaScript Files:** 3 (1,139 LOC)
- **Images:** 6 files
- **Others:** .htaccess, robots.txt, index.php, favicon.ico

### CSS Files (17 stylesheets, 17,392 LOC)

| File | Purpose |
|------|---------|
| `style.css` | Main stylesheet |
| `styles.css` | Secondary styles |
| `styles-extended.css` | Extended styling |
| `styles-coaches.css` | Coaches section styling |
| `styles-courses.css` | Course pages styling |
| `styles-news-simple.css` | News pages styling |
| `styles-club.css` | Club section styling |
| `booking.css` | Booking interface styling |
| `courts.css` | Courts listing/detail styling |
| `court-detail.css` | Court detail page styling |
| `tournaments.css` | Tournament pages styling |
| `tournament-detail.css` | Tournament detail styling |
| `tournament-styles.css` | Additional tournament styles |
| `instructor-review.css` | Instructor review styling |
| `gallery-lightbox.css` | Image gallery styling |
| `referee.css` | Referee interface styling (also at `/public/css/`) |
| Other CSS overrides and vendor files |

### JavaScript Files (3 files, 1,139 LOC)
- `script.js` - Main application script
- `tournament-detail.js` - Tournament detail page logic
- `tournaments.js` - Tournament listing page logic

### Images (6 files)
- `banner.jpeg` - Main banner
- `court_default.svg` - Default court icon
- `logo.png` - Logo (PNG format)
- `logo.jpeg` - Logo (JPEG format)
- Plus storage symlink: `/public/storage -> /storage/app/public`

### Root Files
- `index.php` - Laravel entry point
- `.htaccess` - Apache rewrite rules
- `robots.txt` - SEO robots directives
- `favicon.ico` - Browser favicon

---

## 6. APP STRUCTURE (app/)

### Directory Summary
| Directory | Files | Purpose |
|-----------|-------|---------|
| `/Http/Controllers` | 97 | Request handlers |
| ├─ `/Admin` | 23 | Admin panel controllers |
| ├─ `/Api` | 30 | API controllers |
| ├─ `/Front` | 31 | Frontend controllers |
| ├─ `/Verifier` | 13 | Verifier controllers |
| `/Models` | 81 | Eloquent models |
| `/Services` | 18 | Business logic services |
| `/Http/Requests` | ~40 | Request validation |
| `/Http/Resources` | ~30 | API response formatting |
| `/Jobs` | 2 | Queued jobs |
| `/Events` | 12 | Application events |
| `/Observers` | 6 | Model observers (lifecycle hooks) |
| `/Listeners` | ~15 | Event listeners |
| `/Traits` | ~10 | Reusable code traits |
| `/Policies` | ~15 | Authorization policies |
| `/Middleware` | ~8 | HTTP middleware |
| `/Console/Commands` | ~5 | Artisan commands |
| `/Helpers` | ~3 | Helper functions |
| `/Exceptions` | ~2 | Custom exceptions |
| `/Notifications` | ~5 | Email notifications |
| `/Providers` | ~6 | Service providers |

### Total App Code
- **Total PHP Files:** 297 files
- **Total LOC:** 41,385 lines

### Key Relationships
- **Controllers:** 97 controllers for handling 50+ feature areas
- **Models:** 81 models representing database entities
- **API:** Comprehensive REST API (422 LOC routes)
- **Admin:** 23 admin controllers for management
- **Frontend:** 31 controllers for public-facing features

---

## 7. KEY FEATURES (by LOC & Complexity)

### Highest Impact Systems
1. **Booking System** - Courts, pricing, confirmation workflow
2. **Tournament Management** - Categories, rounds, groups, rankings, bracket generation
3. **OCR System** - Elo ratings, badge system, match disputes, leaderboard
4. **OPRS System** - User skill rating, verification, leaderboard by level
5. **Club System** - Member management, activities, posts, competitions
6. **Leagues** - Teams, matches, standings, schedule with MLP format
7. **Points/Rewards** - Task earning, submission verification, point transactions
8. **Instructor Management** - Packages, certifications, schedules, reviews
9. **Skill Quizzes** - Question banks, attempts, grading system
10. **Auth & Roles** - Multi-guard authentication, role-based access, OAuth integration

---

## 8. SUMMARY STATISTICS

```
┌─────────────────────────────────────────┐
│ CODEBASE METRICS                        │
├─────────────────────────────────────────┤
│ Total App Code:          41,385 LOC      │
│ Total Blade Views:       72,976 LOC      │
│ CSS Stylesheets:         17,392 LOC      │
│ JavaScript:               1,139 LOC      │
│ Route Definitions:        1,221 LOC      │
│ Database Files:           9,973 LOC      │
├─────────────────────────────────────────┤
│ GRAND TOTAL:            144,086 LOC     │
├─────────────────────────────────────────┤
│ Controllers:                  97         │
│ Models:                       81         │
│ Migrations:                  184         │
│ Route Files:                  6          │
│ View Templates:             227          │
│ Config Files:               20           │
│ CSS Files:                  17           │
│ JS Files:                    3           │
│ Seeders:                    20           │
│ Factories:                   7           │
│ Services:                   18           │
│ Events:                     12           │
│ Observers:                   6           │
│ Jobs:                        2           │
└─────────────────────────────────────────┘
```

---

## Unresolved Questions
None at this time. Exploration comprehensive.
