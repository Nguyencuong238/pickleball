# Brainstorm: MLP League Management

## Problem Statement
Build MLP (Major League Pickleball) league management for existing pickleball platform. Target: Vietnamese market (PCL Vietnam style). Organizer-focused, MVP that runs immediately.

## Market Context
- Vietnam pickleball: +184% DUPR growth 2025, fastest in Asia
- PCL Vietnam: first MLP-style league Jun-Jul 2025, 3 cities
- Platforms: DUPR (rating), pickleballplus.vn, Sport Connect
- Gap: no Vietnamese platform with integrated league management

## Evaluated Approaches

### 1. Extend Tournament (Rejected)
- Pros: Fastest, least code
- Cons: Tournament & league are fundamentally different (event vs season, individual vs team). Mixing creates tech debt, hard to scale.

### 2. Separate Module (Rejected)
- Pros: Clean separation
- Cons: Too much new code, duplicates match/scoring logic, longer dev time.

### 3. Hybrid (Selected)
- Pros: League models separate + reuse Match/Standings patterns. Clean architecture, moderate effort.
- Cons: Slightly more setup than extending tournaments.

## Final Solution

### Architecture: Hybrid
- New `league_*` tables with own models/controllers
- Reuse scoring logic, user system, blade layouts from tournament module
- Home Yard dashboard integration (alongside tournaments)

### Core Schema
```
leagues, league_teams, league_team_players, league_rounds,
league_matches, league_match_games, league_standings
```

### MVP Features
1. League CRUD with flexible config (JSON)
2. Team management with fixed roster + captain
3. Match scheduling (auto round-robin or manual)
4. Score entry per game (WD/MD/MXD configurable)
5. Auto standings calculation
6. Home Yard dashboard

### Out of Scope (v2)
- Draft/bidding, trade windows, DreamBreaker
- Live scoreboard, public pages
- DUPR integration, payment

## Implementation Estimate
- MVP: ~2-3 weeks
- Complexity: Medium (reuses existing patterns)

## Success Criteria
- Organizer can create league, add teams, schedule matches, enter scores
- Standings auto-calculate and display correctly
- Works within existing Home Yard panel
- No breaking changes to tournament system

## Risks
- Schema collision -> prefix all tables `league_`
- Gender validation for mixed doubles -> require gender on roster
- Scope creep -> strict MVP boundary

## Next Steps
- Create detailed implementation plan with phases
- Phase 1: DB migrations + models
- Phase 2: Controllers + routes
- Phase 3: Blade views
- Phase 4: Business logic (scheduling, standings)
- Phase 5: Testing
