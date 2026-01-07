# System Architecture

**Last Updated**: 2025-12-18
**Project**: Pickleball Platform
**Framework**: Laravel 10.10+

## Overview

The Pickleball Platform follows Laravel's Model-View-Controller (MVC) architecture pattern with role-based multi-tenant support. The system serves multiple user types through a unified codebase with distinct interfaces for each role.

## Architectural Pattern

### Pattern Classification
**Primary Pattern**: MVC (Model-View-Controller)
**Secondary Patterns**:
- Repository pattern (via Eloquent)
- Service layer (for complex business logic)
- Policy pattern (authorization)
- Observer pattern (events/listeners)

### Design Philosophy
- **Separation of Concerns**: Controllers, Models, Views isolated
- **Role-Based Access**: Different interfaces per user role
- **Convention over Configuration**: Laravel defaults
- **DRY Principle**: Shared components and services

## System Layers

```
┌─────────────────────────────────────────────────────────┐
│                    Presentation Layer                    │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────────────┐│
│  │   Public    │ │  Home Yard  │ │       Admin         ││
│  │  Frontend   │ │  Dashboard  │ │       Panel         ││
│  └─────────────┘ └─────────────┘ └─────────────────────┘│
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                   Application Layer                      │
│  ┌─────────────────────────────────────────────────────┐│
│  │                    Controllers                       ││
│  │  ┌─────────┐  ┌───────────┐  ┌─────────────────────┐││
│  │  │  Front  │  │ Home Yard │  │       Admin         │││
│  │  └─────────┘  └───────────┘  └─────────────────────┘││
│  └─────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────┐│
│  │              Middleware & Policies                   ││
│  └─────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                    Domain Layer                          │
│  ┌─────────────────────────────────────────────────────┐│
│  │                 Eloquent Models                      ││
│  │  User, Stadium, Court, Tournament, Instructor, etc. ││
│  └─────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────┐│
│  │                   Services                           ││
│  │  BookingService, TournamentService, etc.            ││
│  └─────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                 Infrastructure Layer                     │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────────────┐│
│  │   MySQL     │ │    File     │ │     External        ││
│  │  Database   │ │   Storage   │ │     Services        ││
│  └─────────────┘ └─────────────┘ └─────────────────────┘│
└─────────────────────────────────────────────────────────┘
```

## Component Architecture

### 1. Presentation Layer

#### Public Frontend (`resources/views/front/`)
- Homepage with featured content
- Court/stadium listing and detail
- Tournament listing and registration
- Instructor profiles
- News and static pages

#### Home Yard Dashboard (`resources/views/home-yard/`)
- Stadium management
- Court and pricing configuration
- Tournament operations
- Athlete management
- Booking calendar

#### Admin Panel (`resources/views/admin/`)
- System-wide management
- User and role management
- Content moderation
- Platform settings

### 2. Application Layer

#### Controller Organization
```
app/Http/Controllers/
├── Controller.php              # Base controller
├── AuthController.php          # Authentication
├── FavoriteController.php      # Favorites
├── ReviewController.php        # Stadium reviews
├── SocialController.php        # Social events
├── Admin/
│   ├── DashboardController.php
│   ├── CategoryController.php
│   ├── InstructorController.php
│   ├── NewsController.php
│   ├── PageController.php
│   ├── StadiumController.php
│   ├── TournamentController.php
│   ├── UserPermissionController.php
│   ├── VideoController.php
│   ├── OcrDisputeController.php
│   └── OcrBadgeController.php
├── Api/
│   ├── MediaUploadController.php
│   ├── InstructorReviewController.php
│   ├── OcrMatchController.php
│   ├── OcrUserController.php
│   ├── OcrLeaderboardController.php
│   ├── RefereeApiController.php        # Referee API endpoints
│   └── RefereePublicController.php     # Public referee directory
└── Front/
    ├── HomeController.php              # Main frontend
    ├── DashboardController.php         # User dashboard
    ├── HomeYardStadiumController.php
    ├── HomeYardTournamentController.php  # Includes referee assignment
    ├── AthleteManagementController.php
    ├── TournamentRegistrationController.php
    ├── CategoryController.php
    ├── RoundController.php
    ├── GroupController.php
    ├── BookingInstructorController.php
    ├── NewsController.php
    ├── VideoCommentController.php
    ├── VideoLikeController.php
    ├── OcrController.php
    ├── RefereeController.php           # Referee dashboard & match officiating
    └── RefereeProfileController.php    # Public referee directory
```

#### Middleware Stack
```
HTTP Request
    │
    ▼
┌─────────────────┐
│ EncryptCookies  │
├─────────────────┤
│ VerifyCsrfToken │
├─────────────────┤
│  Authenticate   │ ─── Redirects unauthenticated users
├─────────────────┤
│  Role Check     │ ─── Spatie Permission
└─────────────────┘
    │
    ▼
Controller Action
```

### 3. Domain Layer

#### Core Models

**User Management**
```
User ──┬── Stadium (1:N)
       ├── Tournament (1:N)
       ├── Booking (1:N)
       ├── Review (1:N)
       ├── Favorite (1:N)
       └── Province (N:1)
```

**Stadium System**
```
Stadium ──┬── Court (1:N) ──── CourtPricing (1:N)
          │                └── Booking (1:N)
          ├── Review (1:N)
          └── Media (Spatie)
```

**Tournament System**
```
Tournament ──┬── TournamentCategory (1:N) [singles/doubles]
             ├── TournamentAthlete (1:N)
             │   └── partner (self-reference for doubles)
             ├── Round (1:N) ──── Match (1:N) [pair support]
             ├── Group (1:N) ──── GroupStanding (1:N)
             └── Media (Spatie)
```

**Instructor System**
```
Instructor ──┬── InstructorCertification (1:N)
             ├── InstructorExperience (1:N)
             ├── InstructorPackage (1:N)
             ├── InstructorSchedule (1:N)
             ├── InstructorReview (1:N)
             ├── BookingInstructor (1:N)
             └── InstructorFavorite (1:N)
```

**OCR System**
```
User ──┬── OcrMatch (challenger/opponent/partners)
       ├── EloHistory (1:N) ──── OcrMatch (N:1)
       └── UserBadge (1:N)

OcrMatch ──┬── EloHistory (1:N)
           ├── Media (Spatie - evidence)
           └── Participants (challenger, opponent, partners)
```

**OPRS System**
```
User ──┬── ChallengeResult (1:N)
       ├── CommunityActivity (1:N)
       └── OprsHistory (1:N)

ChallengeResult ──── User (verifier)
CommunityActivity ── Reference (Stadium, Social, User)
OprsHistory ──────── Metadata (match/challenge/activity references)
```

**Profile System**
```
User ──┬── Avatar (storage/app/public/avatars)
       └── Province (N:1)

Province ──── User (1:N)
```

**Referee System**
```
User (referee) ──┬── TournamentReferee (1:N) ──── Tournament (N:1)
                 └── MatchModel (1:N as referee_id)

TournamentReferee ──┬── Tournament (N:1)
                    ├── User (referee) (N:1)
                    └── User (assigned_by) (N:1)

MatchModel ──── User (referee_id, nullable)
```

**Skill Quiz System**
```
User ──── SkillQuizAttempt (1:N)

SkillQuizAttempt ──┬── SkillQuizAnswer (1:N)
                   └── User (N:1)

SkillQuizAnswer ──┬── SkillQuizAttempt (N:1)
                  └── SkillQuestion (N:1)

SkillQuestion ──── SkillDomain (N:1)
```

### 4. Infrastructure Layer

#### Database Schema

```
┌─────────────────────────────────────────────────────────┐
│                     Core Tables                          │
├─────────────────────────────────────────────────────────┤
│ users          │ User accounts with OAuth               │
│ stadiums       │ Venue profiles                         │
│ courts         │ Individual courts                      │
│ court_pricings │ Time-based pricing                     │
│ bookings       │ Court reservations                     │
├─────────────────────────────────────────────────────────┤
│                   Tournament Tables                      │
├─────────────────────────────────────────────────────────┤
│ tournaments           │ Tournament config               │
│ tournament_categories │ Skill/age groups (singles/doubles) │
│ tournament_athletes   │ Registered athletes + partner_id│
│ rounds               │ Tournament rounds                │
│ groups               │ Group stage                      │
│ group_standings      │ Rankings                         │
│ matches              │ Match records with pair support  │
├─────────────────────────────────────────────────────────┤
│                   Instructor Tables                      │
├─────────────────────────────────────────────────────────┤
│ instructors                 │ Coach profiles            │
│ instructor_certifications   │ Credentials               │
│ instructor_packages         │ Service offerings         │
│ instructor_reviews          │ Feedback                  │
│ booking_instructors         │ Coaching bookings         │
├─────────────────────────────────────────────────────────┤
│                   Content Tables                         │
├─────────────────────────────────────────────────────────┤
│ news           │ Articles                               │
│ categories     │ News categories                        │
│ pages          │ Static content                         │
│ videos         │ Video library                          │
│ video_comments │ User comments                          │
│ video_likes    │ Engagement                             │
├─────────────────────────────────────────────────────────┤
│                   OCR Tables                             │
├─────────────────────────────────────────────────────────┤
│ ocr_matches    │ Ranked matches (singles/doubles)       │
│ elo_histories  │ Elo rating changes                     │
│ user_badges    │ Achievement badges                     │
├─────────────────────────────────────────────────────────┤
│                   OPRS Tables                            │
├─────────────────────────────────────────────────────────┤
│ users          │ Added OPRS fields (scores, level)      │
│ challenge_results     │ Skill challenge records         │
│ community_activities  │ Community engagement tracking   │
│ oprs_histories        │ OPRS change audit log           │
├─────────────────────────────────────────────────────────┤
│                   Profile Tables                         │
├─────────────────────────────────────────────────────────┤
│ users          │ Added profile fields (avatar, location) │
│ provinces      │ Geographic provinces                    │
├─────────────────────────────────────────────────────────┤
│                   Referee Tables                         │
├─────────────────────────────────────────────────────────┤
│ users          │ Added referee fields (bio, status, etc) │
│ tournament_referees │ Referee-tournament assignments    │
│ matches        │ Added referee_id and referee_name       │
├─────────────────────────────────────────────────────────┤
│                   Skill Quiz Tables                      │
├─────────────────────────────────────────────────────────┤
│ skill_domains  │ 6 fixed domains with weights            │
│ skill_questions│ 36 questions across domains             │
│ skill_quiz_attempts │ User attempts with ELO, flags     │
│ skill_quiz_answers  │ Individual question responses     │
│ users          │ Added quiz tracking fields              │
└─────────────────────────────────────────────────────────┘
```

## Data Flow

### Court Booking Flow

```
User Request: Book Court
        │
        ▼
┌───────────────────┐
│   HomeController  │
│ getAvailableSlots │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│   Court Model     │
│   with Pricing    │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Check Existing    │
│    Bookings       │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│ Calculate Price   │
│ Based on Time     │
└─────────┬─────────┘
          │
          ▼
┌───────────────────┐
│  Create Booking   │
│     Record        │
└───────────────────┘
```

### Tournament Registration Flow

```
User Request: Register for Tournament
        │
        ▼
┌─────────────────────────────┐
│ TournamentRegistrationCtrl  │
│         register()          │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│    Validate Category        │
│    Check if Doubles         │
│    Check Capacity           │
└───────────────┬─────────────┘
         ┌──────┴──────┐
         │             │
    [Singles]     [Doubles]
         │             │
         ▼             ▼
┌─────────────┐  ┌──────────────┐
│Create       │  │Validate      │
│Athlete      │  │Partner Info  │
│             │  │Create Pair   │
└──────┬──────┘  └──────┬───────┘
       │                │
       └────────┬───────┘
                ▼
┌─────────────────────────────┐
│  Create TournamentAthlete   │
│  Status: 'pending'          │
│  partner_id (if doubles)    │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│   Await Organizer Approval  │
└─────────────────────────────┘
```

### OCR Match Flow

```
User A: Challenge User B
        │
        ▼
┌─────────────────────────────┐
│  OcrMatchController@store   │
│  Create Match (pending)     │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│ User B: Accept or Reject    │
└───────────────┬─────────────┘
                │
         ┌──────┴──────┐
         │             │
    [Accept]      [Reject]
         │             │
         ▼             ▼
┌─────────────┐  ┌─────────┐
│In Progress  │  │Cancelled│
└──────┬──────┘  └─────────┘
       │
       ▼
┌─────────────────────────────┐
│  Play Match                 │
│  User A: Submit Result      │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│ User B: Confirm or Dispute  │
└───────────────┬─────────────┘
         ┌──────┴──────┐
         │             │
    [Confirm]     [Dispute]
         │             │
         ▼             ▼
┌─────────────┐  ┌──────────┐
│EloService   │  │Admin     │
│Process Elo  │  │Review    │
└──────┬──────┘  └──────────┘
       │
       ▼
┌─────────────────────────────┐
│ BadgeService                │
│ Check & Award Badges        │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│ Match Completed             │
│ Elo Updated, Badges Awarded │
└─────────────────────────────┘
```

### OPRS Calculation Flow

```
User Action (Match/Challenge/Activity)
        │
        ▼
┌─────────────────────────────┐
│  Component Service          │
│  (Elo/Challenge/Community)  │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Update Component Score     │
│  (elo_rating, challenge_    │
│   score, community_score)   │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  OprsService                │
│  calculateOprs()            │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Calculate Total OPRS       │
│  (0.7*Elo + 0.2*Challenge   │
│   + 0.1*Community)          │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Determine OPR Level        │
│  (1.0 to 5.0+ based on      │
│   threshold mapping)        │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Record OprsHistory         │
│  (audit trail with reason   │
│   and metadata)             │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Update User Record         │
│  (total_oprs, opr_level)    │
└─────────────────────────────┘
```

### Challenge Submission Flow

```
User: Submit Challenge
        │
        ▼
┌─────────────────────────────┐
│  ChallengeService           │
│  submitChallenge()          │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Validate Challenge Type    │
│  Check Monthly Limit        │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Create ChallengeResult     │
│  (score, type, timestamp)   │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Check Pass Threshold       │
│  Calculate Points Earned    │
└───────────────┬─────────────┘
         ┌──────┴──────┐
         │             │
    [Passed]      [Failed]
         │             │
         ▼             ▼
┌─────────────┐  ┌─────────┐
│Award Points │  │No Points│
│Update Score │  │Record   │
└──────┬──────┘  └─────────┘
       │
       ▼
┌─────────────────────────────┐
│  OprsService                │
│  recalculateAfterChallenge()│
└─────────────────────────────┘
```

### Community Activity Flow

```
User Action (Check-in/Event/Referral)
        │
        ▼
┌─────────────────────────────┐
│  CommunityService           │
│  (checkIn/recordEvent/etc)  │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Validate Eligibility       │
│  Check Daily/Monthly Limits │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Create CommunityActivity   │
│  (type, points, reference)  │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Award Points               │
│  Update community_score     │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  OprsService                │
│  recalculateAfterActivity() │
└─────────────────────────────┘
```

### Referee Match Officiating Flow

```
Referee: View Assigned Match
        │
        ▼
┌─────────────────────────────┐
│  RefereeController@show     │
│  Check isAssignedToReferee  │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Match Status: scheduled    │
│  Referee clicks "Start"     │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  RefereeController@start    │
│  Status: in_progress        │
│  Record actual_start_time   │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Play Match                 │
│  Referee enters set scores  │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  RefereeController@update   │
│  Validate set_scores array  │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Calculate Winner           │
│  From set scores            │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Update Match               │
│  - set_scores (JSON)        │
│  - final_score (string)     │
│  - winner_id                │
│  - status: completed        │
│  - actual_end_time          │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Log Activity               │
│  Match completed            │
└─────────────────────────────┘
```

### Profile Management Flow

```
User: Update Profile
        │
        ▼
┌─────────────────────────────┐
│  ProfileController          │
│  (updateProfile/Avatar/     │
│   Email/Password)           │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Validate Input             │
│  (name, location, avatar,   │
│   email, password)          │
└───────────────┬─────────────┘
                │
         ┌──────┴──────┐
         │             │
    [Avatar]      [Email/Password]
         │             │
         ▼             ▼
┌─────────────┐  ┌──────────────┐
│ProfileSvc   │  │Verify        │
│Upload/      │  │Password      │
│Delete       │  └──────┬───────┘
│Avatar       │         │
└──────┬──────┘         │
       │                │
       ▼                ▼
┌─────────────────────────────┐
│  Update User Record         │
│  (avatar path, email, etc)  │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Return Success Message     │
└─────────────────────────────┘
```

### Skill Quiz Flow

```
User: Start Quiz
        │
        ▼
┌─────────────────────────────┐
│  SkillQuizController@start  │
│  Check eligibility          │
└───────────────┬─────────────┘
                │
         ┌──────┴──────┐
         │             │
    [Eligible]   [Ineligible]
         │             │
         ▼             ▼
┌─────────────┐  ┌─────────┐
│Display Quiz │  │Redirect │
│36 Questions │  │w/ Error │
└──────┬──────┘  └─────────┘
       │
       ▼
┌─────────────────────────────┐
│  User answers (0-3 scale)   │
│  Record start time          │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  SkillQuizService@submit    │
│  Validate completion time   │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Calculate total score      │
│  Cross-validate answers     │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Calculate initial ELO      │
│  Apply ELO caps             │
└───────────────┬─────────────┘
                │
         ┌──────┴──────┐
         │             │
   [Consistent]  [Suspicious]
         │             │
         ▼             ▼
┌─────────────┐  ┌──────────────┐
│Assign ELO   │  │Flag attempt  │
│Update User  │  │Admin review  │
└──────┬──────┘  └──────┬───────┘
       │                │
       └────────┬───────┘
                ▼
┌─────────────────────────────┐
│  Set re-quiz cooldown       │
│  (30-90 days based on ELO)  │
└───────────────┬─────────────┘
                │
                ▼
┌─────────────────────────────┐
│  Display results & ELO      │
│  Show recommendations       │
└─────────────────────────────┘
```

### Authentication Flow

```
┌─────────────────────────────────────────────────────────┐
│                  Authentication Flows                    │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Standard Login:                                        │
│  ┌─────────┐    ┌────────────┐    ┌──────────────┐    │
│  │  Login  │ -> │ Validate   │ -> │   Session    │    │
│  │  Form   │    │ Credentials│    │   Created    │    │
│  └─────────┘    └────────────┘    └──────────────┘    │
│                                                         │
│  OAuth (Google/Facebook):                               │
│  ┌─────────┐    ┌────────────┐    ┌──────────────┐    │
│  │ Redirect│ -> │ Callback   │ -> │ Find/Create  │    │
│  │ to OAuth│    │ Handler    │    │    User      │    │
│  └─────────┘    └────────────┘    └──────────────┘    │
│                                                         │
│  Admin Login:                                           │
│  ┌─────────┐    ┌────────────┐    ┌──────────────┐    │
│  │  Admin  │ -> │ Check Role │ -> │ Admin Session│    │
│  │  Login  │    │  'admin'   │    │   Created    │    │
│  └─────────┘    └────────────┘    └──────────────┘    │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

## Security Architecture

### Authentication

```
┌─────────────────────────────────────────────────────────┐
│                 Authentication Layer                     │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Web Authentication (Session-based):                    │
│  ├── Laravel's built-in auth                           │
│  ├── CSRF token protection                             │
│  └── Encrypted cookies                                  │
│                                                         │
│  API Authentication:                                    │
│  ├── Laravel Sanctum                                   │
│  └── Personal access tokens                             │
│                                                         │
│  OAuth Providers:                                       │
│  ├── Google (via Socialite)                            │
│  └── Facebook (via Socialite)                          │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### Authorization

```
┌─────────────────────────────────────────────────────────┐
│                 Authorization Layer                      │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Role-Based Access (Spatie Permission):                │
│  ┌─────────────────────────────────────────────────────┐│
│  │ Roles:                                              ││
│  │ ├── admin     -> Full system access                 ││
│  │ ├── home_yard -> Stadium/tournament management      ││
│  │ ├── referee   -> Match officiating                  ││
│  │ └── user      -> Basic user features                ││
│  └─────────────────────────────────────────────────────┘│
│                                                         │
│  Middleware Protection:                                 │
│  ├── role:admin     -> Admin routes                    │
│  ├── role:home_yard -> Home Yard routes                │
│  ├── role:referee   -> Referee routes                  │
│  └── auth           -> Authenticated routes             │
│                                                         │
│  Policy-Based Authorization:                            │
│  ├── TournamentPolicy                                  │
│  └── Custom policies for resource ownership            │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### Security Measures

| Layer | Protection |
|-------|-----------|
| Transport | HTTPS enforcement |
| Session | Encrypted cookies, CSRF tokens |
| Input | Request validation, Eloquent ORM |
| Output | Blade escaping, XSS prevention |
| Database | Parameterized queries |
| Files | Storage access control |

## File Storage Architecture

```
storage/
├── app/
│   └── public/              # Public uploads
│       ├── stadiums/        # Stadium images
│       ├── tournaments/     # Tournament media
│       ├── instructors/     # Instructor photos
│       └── videos/          # Video thumbnails
├── framework/
│   ├── cache/
│   ├── sessions/
│   └── views/
└── logs/
    └── laravel.log

public/
└── storage -> ../storage/app/public  # Symlink
```

### Media Management (Spatie)

```php
// Media collections per model
Stadium::class
├── 'images'     # Stadium photos
└── 'gallery'    # Additional images

Tournament::class
├── 'thumbnail'  # Main image
└── 'gallery'    # Event photos

Instructor::class
├── 'avatar'     # Profile photo
└── 'portfolio'  # Work samples
```

## API Architecture

### Internal API Endpoints

```
/api/
├── bookings                    # Booking operations
├── courts/{id}/available-slots # Availability check
├── instructor-booking          # Instructor bookings
├── instructor-review           # Instructor reviews
├── videos/{id}/comments        # Video comments
├── videos/{id}/like            # Video likes
├── upload-media                # Media uploads
├── ocr/
│   ├── matches                 # OCR match operations
│   ├── matches/{id}/accept     # Accept challenge
│   ├── matches/{id}/result     # Submit result
│   ├── matches/{id}/confirm    # Confirm result
│   ├── leaderboard             # Global rankings
│   └── users/{id}              # User OCR profile
├── oprs/
│   ├── profile                 # Current user OPRS profile
│   ├── breakdown               # OPRS score breakdown
│   ├── history                 # OPRS change history
│   ├── levels                  # All OPR levels
│   ├── leaderboard             # OPRS leaderboard
│   ├── leaderboard/levels      # Available levels
│   ├── leaderboard/level/{level}  # Level-specific leaderboard
│   ├── leaderboard/distribution   # Level distribution stats
│   ├── users/{user}            # User OPRS profile
│   ├── challenges              # Challenge operations
│   ├── challenges/{id}         # Challenge detail
│   ├── community               # Community activities
│   └── matchmaking             # Opponent suggestions
├── referees                    # Public: List active referees
├── referees/{referee}          # Public: Referee profile with stats
└── referee/                    # Protected (auth:api + referee role)
    ├── dashboard               # Dashboard stats + upcoming matches
    ├── matches                 # List assigned matches (filterable)
    ├── matches/{match}         # Match detail
    ├── matches/{match}/start   # Start match
    └── matches/{match}/score   # Update match scores
```

### Response Format

```json
{
  "success": true,
  "data": {
    // Response data
  },
  "message": "Operation successful"
}
```

### Error Response

```json
{
  "success": false,
  "error": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

## Caching Strategy

### Cache Layers

| Layer | Tool | Purpose |
|-------|------|---------|
| Config | File | Configuration caching |
| Route | File | Route caching |
| View | File | Compiled Blade views |
| Query | Database/Redis | Frequent queries |
| Session | File/Database | User sessions |

### Cacheable Data

- Featured stadiums list
- Tournament listings
- Instructor directory
- News articles
- Static pages

## Deployment Architecture

### Development Environment

```
┌─────────────────────────────────────────────────────────┐
│                    Development                           │
├─────────────────────────────────────────────────────────┤
│  Local Machine                                          │
│  ├── PHP 8.1+ with extensions                          │
│  ├── MySQL 8.0+                                        │
│  ├── Node.js 18+ (for Vite)                            │
│  └── Composer                                           │
│                                                         │
│  Commands:                                              │
│  ├── php artisan serve    (Backend)                    │
│  └── npm run dev          (Frontend assets)            │
└─────────────────────────────────────────────────────────┘
```

### Production Environment

```
┌─────────────────────────────────────────────────────────┐
│                    Production                            │
├─────────────────────────────────────────────────────────┤
│  Web Server (Nginx/Apache)                              │
│  ├── PHP-FPM 8.1+                                      │
│  ├── MySQL 8.0+                                        │
│  ├── Redis (optional, for caching)                     │
│  └── SSL Certificate                                    │
│                                                         │
│  Optimization:                                          │
│  ├── php artisan config:cache                          │
│  ├── php artisan route:cache                           │
│  ├── php artisan view:cache                            │
│  └── npm run build                                      │
└─────────────────────────────────────────────────────────┘
```

## Scalability Considerations

### Horizontal Scaling

- Stateless application design
- Session storage in database/Redis
- Shared file storage (S3 compatible)
- Load balancer ready

### Vertical Scaling

- Query optimization with indexes
- Eager loading relationships
- Pagination for large datasets
- Media processing optimization

### Performance Optimization

| Area | Strategy |
|------|----------|
| Database | Indexing, query optimization |
| Assets | Vite bundling, minification |
| Images | Spatie conversions, WebP |
| Caching | Query caching, page caching |

## Monitoring & Logging

### Log Channels

```php
// config/logging.php
'channels' => [
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'days' => 14,
    ],
]
```

### Key Metrics

- Request latency
- Error rates
- Database query time
- Memory usage
- Booking success rate

## Future Architecture Considerations

### Planned Enhancements

1. **Queue System**: Background job processing
2. **Real-time**: WebSocket for live updates
3. **API Gateway**: Public API for third parties
4. **Microservices**: Separate booking/tournament services

### Technology Upgrades

1. **Redis**: For caching and sessions
2. **Elasticsearch**: For advanced search
3. **CDN**: For media delivery
4. **Message Queue**: For async processing

## Related Documentation

- [Project Overview PDR](./project-overview-pdr.md)
- [Codebase Summary](./codebase-summary.md)
- [Code Standards](./code-standards.md)
- [Referee API Documentation](./api-referee.md)

## OPRS System Architecture

### Service Layer Components

```
OprsService (Core)
├── calculateOprs()              # Calculate total OPRS from components
├── calculateOprLevel()          # Map OPRS to OPR level
├── updateUserOprs()             # Update and record history
├── recalculateAfterMatch()      # Triggered by match result
├── recalculateAfterChallenge()  # Triggered by challenge completion
├── recalculateAfterActivity()   # Triggered by community activity
├── getOprsBreakdown()           # Component breakdown for display
├── getLeaderboard()             # OPRS-based leaderboard
└── adminAdjustment()            # Manual score adjustment

ChallengeService
├── submitChallenge()            # User challenge submission
├── verifyChallenge()            # Admin verification
├── revokeChallenge()            # Admin revocation
├── canSubmitMonthlyTest()       # Monthly limit check
├── getChallengeHistory()        # User history
└── getChallengeStats()          # User statistics

CommunityService
├── checkIn()                    # Stadium check-in
├── recordEventParticipation()   # Event attendance
├── recordReferral()             # Player referral
├── checkWeeklyMatchBonus()      # Weekly bonus check
├── recordMonthlyChallenge()     # Monthly challenge
├── processWeeklyBonuses()       # Batch processing (scheduled)
└── getActivityStats()           # User statistics

ProfileService
├── updateBasicInfo()            # Update name, location, province
├── updateAvatar()               # Upload/remove avatar
├── deleteCurrentAvatar()        # Remove existing avatar file
├── updateEmail()                # Change email (with password)
├── updatePassword()             # Change password
├── verifyPassword()             # Verify current password
└── hasPassword()                # Check if user has password (OAuth)

SkillQuizService
├── calculateElo()               # Convert quiz score to ELO
├── calculateTotalScore()        # Sum weighted question scores
├── crossValidate()              # Check answer consistency
├── validateCompletionTime()     # Check 3-20 min window
├── applyEloCap()               # Apply caps (1100/1200)
├── canRetakeQuiz()             # Check cooldown eligibility
├── calculateRetakeCooldown()   # Determine next retake date
├── flagSuspiciousAttempt()     # Mark for admin review
└── getAttemptStatistics()      # Admin statistics
```

### Component Weights and Levels

```php
// Component Weights
OPRS = (0.7 × Elo) + (0.2 × Challenge) + (0.1 × Community)

// OPR Levels
1.0 (Beginner)          0-599
2.0 (Novice)            600-899
3.0 (Intermediate)      900-1099
3.5 (Upper Intermediate) 1100-1349
4.0 (Advanced)          1350-1599
4.5 (Pro)               1600-1849
5.0+ (Elite)            1850+

// Challenge Types
- serve_accuracy: 50 points (70% threshold)
- volley_control: 60 points (75% threshold)
- dink_precision: 55 points (70% threshold)
- footwork_drill: 45 points (65% threshold)
- monthly_test: 200 points (80% threshold, 1/month)

// Community Activities
- check_in: 10 points (daily per stadium)
- event_participation: 50 points (per event)
- player_referral: 100 points (per referral)
- weekly_matches: 30 points (5+ matches/week)
- monthly_challenge: 150 points (1/month)
```

### OPRS Data Dependencies

```
User Model Fields:
├── elo_rating          (from OCR system)
├── challenge_score     (from ChallengeService)
├── community_score     (from CommunityService)
├── total_oprs          (calculated by OprsService)
└── opr_level           (determined by OprsService)

OprsHistory Record:
├── user_id
├── elo_score           (snapshot)
├── challenge_score     (snapshot)
├── community_score     (snapshot)
├── total_oprs          (calculated)
├── opr_level           (determined)
├── change_reason       (enum)
└── metadata            (JSON - references)

SkillQuizAttempt Record:
├── user_id
├── total_score         (raw score)
├── max_possible_score  (for percentage)
├── elo_assigned        (calculated ELO)
├── completion_time     (seconds)
├── is_flagged          (fraud detection)
├── flag_reason         (if flagged)
├── started_at          (timestamp)
└── completed_at        (timestamp)
```

### Skill Quiz Configuration

```php
// Domain Weights (6 domains)
Technical Skills:      weight = 1.2
Strategy & Tactics:    weight = 1.0
Physical Conditioning: weight = 0.9
Mental Game:           weight = 1.0
Experience Level:      weight = 1.1
Game Situations:       weight = 1.0

// Rating Scale (per question)
0 = Never/Not at all
1 = Rarely/Beginner level
2 = Sometimes/Intermediate level
3 = Often or Always/Advanced level

// ELO Calculation
Base ELO = 800
Max ELO from quiz = 1400
Formula: 800 + (percentage_score * 6)

// ELO Caps
New players (< 5 matches): 1100 max
Experienced (5+ matches): 1200 max

// Cooldown Periods
ELO < 900: 30 days
ELO 900-1100: 60 days
ELO > 1100: 90 days

// Anti-Fraud Thresholds
Min completion time: 3 minutes (180 sec)
Max completion time: 20 minutes (1200 sec)
Max inconsistency score: 3 (cross-validation)
```

## Unresolved Questions

1. **Queue Strategy**: Which queue driver for production (Redis, SQS)?
2. **CDN Choice**: Which CDN for media (Cloudflare, AWS CloudFront)?
3. **Monitoring**: Which monitoring stack (New Relic, Sentry)?
4. **Search**: Elasticsearch vs Algolia for search functionality?
5. **OPRS Scaling**: Caching strategy for leaderboards and level distributions?
6. **Challenge Automation**: Integration with video analysis for automatic verification?
