# Quick Reference: Tournament & League Architecture
**Pickleball Platform** | Updated: 2026-02-25

## Key File Locations

### Controllers
- **Admin Tournament**: `/app/Http/Controllers/Admin/TournamentController.php` (290 lines)
- **Home Yard Tournament**: `/app/Http/Controllers/Front/HomeYardTournamentController.php` (600+ lines)
- **Referee**: `/app/Http/Controllers/Front/RefereeController.php`
- **Tournament Registration**: `/app/Http/Controllers/Front/TournamentRegistrationController.php`
- **Athlete Management**: `/app/Http/Controllers/Front/AthleteManagementController.php`

### Models
```
/app/Models/
├── Tournament.php             # Main tournament entity
├── TournamentAthlete.php      # Registered athletes with partner support
├── TournamentCategory.php     # Singles/doubles categories
├── TournamentReferee.php      # Referee assignments
├── Round.php                  # Tournament rounds
├── Group.php                  # Group stage groupings
├── GroupStanding.php          # Group rankings
├── MatchModel.php             # Match records (300+ lines)
└── MatchEvent.php             # Match events
```

### Views
```
Home Yard Dashboard:
/resources/views/home-yard/tournaments/
├── tournaments.blade.php      # Main list (11 KB)
├── athletes.blade.php         # Athlete management (41 KB)
├── courts.blade.php           # Court assignment (64 KB)
├── matches.blade.php          # Match management (69 KB)
├── rankings.blade.php         # Standings (47 KB)
└── bookings.blade.php         # Booking management (84 KB)

Admin Panel:
/resources/views/admin/tournaments/
├── index.blade.php            # Tournament list
├── form.blade.php             # Create/edit form (22 KB)
└── edit.blade.php             # Edit wrapper
```

### Migrations
```
/database/migrations/
Core Tournament Tables:
- 2025_11_17_000001_create_tournaments_table
- 2025_11_17_000002_create_tournament_athletes_table
- 2025_11_17_000003_create_tournament_categories_table
- 2025_11_17_000004_create_rounds_table
- 2025_11_17_000006_create_matches_table
- 2025_11_17_000007_create_groups_table
- 2025_11_17_000008_create_group_standings_table

Recent Enhancements:
- 2026_02_02_113709_add_draw_order_to_tournament_athletes_table
- 2026_01_12_add_is_featured_to_tournaments_table
```

### Routes
- **Web Routes**: `/routes/web.php`
- **API Routes**: `/routes/api.php`

## Core Models Summary

| Model | PK | FKs | Key Fields |
|-------|----|----|-----------|
| `Tournament` | id | user_id | name, slug, start_date, end_date, status, is_ocr, is_featured |
| `TournamentAthlete` | id | tournament_id, user_id, category_id, partner_id | status, draw_order |
| `TournamentCategory` | id | tournament_id | category_type (enum), category_name |
| `TournamentReferee` | id | tournament_id, user_id, assigned_by | status, assigned_at |
| `Group` | id | tournament_id, category_id, round_id | group_name, max_participants, current_participants |
| `GroupStanding` | id | group_id, athlete_id | rank_position, wins, losses, points, is_advanced |
| `MatchModel` | id | tournament_id, group_id, category_id, referee_id | challenger_id, opponent_id, set scores, status |
| `Round` | id | tournament_id | name, status |

## User Roles

```
admin                 -> /admin/* (view all tournaments, manage system)
home_yard             -> /homeyard/* (manage own tournaments)
user                  -> public features, athlete registration
referee               -> /referee/* (match officiating)
```

## Tournament Categories (Enums)

```
- single_men
- single_women
- double_men
- double_women
- double_mixed
```

## Key Features Currently Implemented

[TOURNAMENT]
- Create/edit tournament with categories
- Athlete registration with partner selection
- Group stage management
- Match scheduling with pair support
- Standings calculation
- Referee assignment

[ADMIN]
- Full CRUD for tournaments
- User permission management
- Tournament search/filter

[HOME YARD]
- Tournament dashboard
- Athlete management
- Court assignment
- Match management
- Rankings/standings view
- Booking management
- Bracket generation (via draw_order)

[REFEREE]
- Assigned match viewing
- Score entry
- Match officiating workflow

## League Features NOT Yet Implemented

```
[MISSING]
- League model/entity
- League standings (cross-tournament)
- League registration workflow
- League scheduling
- League divisions
- League payouts
- Team management
```

## Service Classes Available

```
/app/Services/
├── EloService              # Elo calculations
├── OprsService             # OPRS scoring (Elo 70% + Challenge 20% + Community 10%)
├── BadgeService            # Achievement badges
├── ChallengeService        # Challenge verification
├── CommunityService        # Activity tracking
├── SkillQuizService        # Quiz scoring
├── PointEarningService     # Point tasks
└── [11 more services]
```

## Database Relationships

```
Tournament (1) ──┬──(N) TournamentAthlete
                 ├──(N) TournamentCategory
                 ├──(N) Round──(N) Group──(N) GroupStanding
                 ├──(N) TournamentReferee──(N) User
                 └──(N) MatchModel

TournamentAthlete (N:N) ──partner── TournamentAthlete (doubles pairs)
```

## API Endpoints Available

```
GET    /api/tournaments
GET    /api/tournaments/{id}
POST   /api/tournaments
PUT    /api/tournaments/{id}
DELETE /api/tournaments/{id}

GET    /api/tournaments/{id}/athletes
GET    /api/tournaments/{id}/matches
GET    /api/tournaments/{id}/standings
GET    /api/tournaments/{id}/groups

GET    /api/referee/matches
POST   /api/referee/matches/{id}/score
```

## Performance Notes

- Pagination: 10 per page (admin), 12 per page (home-yard)
- Eager loading used for relationships
- Group standing queries ordered by rank_position
- Large views: matches (69KB), bookings (84KB), athletes (41KB)

## Integration Points

| System | Integration |
|--------|-------------|
| Booking | Tournament bookings tracked separately |
| OCR | Tournaments flagged with is_ocr boolean |
| Points | 100 points awarded for tournament creation |
| Referee | Tournament referee assignment and match officiating |
| OPRS | Community activities linked to tournaments |

## Recommended Next Steps for League Implementation

1. **Create League Model** - relationship to multiple tournaments
2. **League Standings Service** - aggregate cross-tournament scores
3. **League Controller** - CRUD operations for leagues
4. **League Views** - admin and home-yard dashboards
5. **League Routes** - API and web routes
6. **League Registration** - athlete/team registration workflow
7. **League Notifications** - updates and announcements

## Key Documentation Files

- `/docs/project-overview-pdr.md` - Feature specifications
- `/docs/system-architecture.md` - System design
- `/docs/codebase-summary.md` - Complete model inventory
- `/docs/code-standards.md` - Development standards

## Testing

- Test directory: `/tests/`
- Database seeders: `/database/seeders/`
- Feature tests for tournament operations exist

---

**Total Models**: 67+
**Total Migrations**: 165+
**Admin Controllers**: 22
**Frontend Controllers**: 26+
**API Controllers**: 24+
**Service Classes**: 12+
**Tournament-specific Files**: 30+
