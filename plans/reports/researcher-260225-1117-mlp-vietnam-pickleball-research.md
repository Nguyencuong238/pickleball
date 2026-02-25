# MLP & Vietnam Pickleball Market Research Report

**Date:** 2026-02-25
**Researcher:** researcher agent
**Purpose:** Research MLP format, Vietnam pickleball market, and platform feature requirements for MLP-style feature in Laravel pickleball platform

---

## 1. Major League Pickleball (MLP) — How It Works

### Overview
MLP is a professional team-based pickleball league. In 2026 it will have **20 teams** across Premier and Challenger levels. Currently (2025): 16 Premier + 6 Challenger teams.

### Team Structure
- Each team: **6 players** — 3 men + 3 women (2025 expansion from 4)
- Only **4 players** compete per match (2M + 2F)
- Teams are "hometown" franchises with geographic identity

### Match Format (per team vs team encounter)
Each match = **4 games** in fixed order:
1. Women's Doubles (WD)
2. Men's Doubles (MD)
3. Mixed Doubles #1 (XD)
4. Mixed Doubles #2 (XD)

Tiebreaker (tied 2-2): **DreamBreaker** — singles relay format
- Rally scoring to 21, switch players every 4 points
- Each player on roster plays; order is strategic

### Scoring
- **Doubles games**: Side-out scoring to 11, win by 2 (changed from rally scoring in 2025)
- **DreamBreaker**: Rally scoring to 21
- **Match result**: Win/loss (team that wins 3+ games wins the match)
- **Season points**: Earned only via Sunday head-to-head matches (not group play)

### Season Structure (2025)
- 10 regular season events (each in a team's home city)
- Each team participates in **5 of 10 events**, plays **25 total matches**
- 1 mid-season tournament
- Playoffs: top 10 teams qualify; single elimination in San Diego → Finals

### 2026 Changes
- Expanding to **20 teams**
- 9 regular season events + 1 mid-season + 3 playoff weekends
- May through August season window
- Seeds 1-4 get first-round byes; quarters in San Diego, semis+finals in NYC

### Draft & Roster Management System
**Draft (Free Agency Draft)**
- Teams bid for **draft slots** (dynamic bidding — not fixed order)
- Teams given a pool of "draft points" (currency)
- First 4 picks (2M, 2F starters): min bid 10,000 pts
- Final 2 picks (bench): min bid 1,000 pts
- No salary cap

**Player Keepers**
- Max 3 keepers from prior season roster
- 2 players: can keep up to 3 years
- 1 player: can keep up to 2 years
- Cost: pay MLP 50% of original draft capital spent

**In-Season Transactions**
- Waiver Wire: 1 period (after event #5), only UPA-contracted players eligible
- Trade Window: teams can exchange players + cash up to $200k
- Trade Deadline: mid-July
- Lineup submission required day before each match (can change daily, not mid-match)

---

## 2. Vietnam Pickleball Market 2025–2026

### Market Scale & Growth
- **+184% DUPR player growth** projected in 2025 (fastest in Asia after Malaysia)
- **+30% DUPR player growth** in 90 days (Q1 2025)
- Geographic spread: HCMC, Hanoi, Da Nang, Nha Trang, coastal cities
- 13+ listed courts in HCMC alone (Pickleheads directory)

### Major Tournaments & Events (2025)
| Tournament | Scale | Notes |
|---|---|---|
| PPA Tour Asia MB Vietnam Open | International, HCMC Sep 4-7 | Biggest event yet, Global City Sports Park |
| Vietnam Pickleball Open Cup (VPC) | National multi-stage | HCMC + regional stages |
| National Young Entrepreneurs PB Tournament | 1,000+ athletes | Largest domestic event |
| VTVcab OPEN CUP 2025 – Jogarbola Cup | 800+ athletes | |
| Hanoi Television PB Tournament | 600+ athletes | |
| Dan Tri 20th Anniversary Tournament | 600+ athletes | |
| Facolos Champion 2025 (Jan 19) | 300+ players | First large DUPR-submitted event |

### PCL Vietnam (Pickleball Champions League)
- **First season**: June 7–28, 2025 — Hanoi, Da Nang, HCMC
- MLP-style **team league format** at domestic level
- Each city hosts group stage; city champions advance to national finals (HCMC, July 12-13)
- Organized by PCL Asia + SP3 (Vietnamese sports community org)
- Connects local clubs to national then Asian competition (PCL Asia finals in Shenzhen, Aug 9-10)

### Key Organizations
- **Pickleball Vietnam Association (VNA)**: `pickleballvna.org` — national governing body
- **New Sports JSC**: Organizer building "comprehensive pickleball ecosystem" in VN
- **SP3**: Local partner for PCL Asia in Vietnam
- **UPA Asia**: Player rankings and recruitment in Southeast Asia
- **MB Bank**: Major sponsor (MB Vietnam Open branding)

### Digital Platforms Used in Vietnam
- **DUPR**: Universal rating system used across Vietnam; gold standard for player rankings
- **pickleballplus.vn**: Vietnamese-language tournament registration platform (VPC organizer)
- **Sport Connect**: Vietnamese platform for sports league management (digitizing tournaments)
- **Global Pickleball Network**: Used by HCMC clubs for community/open play
- **Pickleheads**: Court discovery (international, used by VN players)

### Market Characteristics
- Clubs run both recreational open play and competitive formats
- DUPR rating submission becoming standard for credibility
- Large corporate-sponsored tournaments (banks, media companies)
- Growing pathway: local club → provincial → national → international (PCL Asia)
- Vietnam is part of the 3-week PPA Asia circuit (after HK and Fukuoka)

---

## 3. MLP-Style Feature Requirements for a Web Platform

### What Platforms Like MatchTime, Swish, Tournated Offer
| Feature | MatchTime | Swish | Tournated | DUPR |
|---|---|---|---|---|
| Team/League management | Yes | Yes | Yes (automated) | No |
| Round-robin scheduling | Yes | Yes | Auto-generate | No |
| Standings auto-calc | Yes | No | Yes | Ratings only |
| Live scoring | No | Yes | Yes | Via API |
| DUPR integration | No | Yes | Yes | Native |
| Free Agent matching | Yes | No | No | No |
| Mobile app | Yes | Yes | No | Yes |
| White label | No | No | Yes | No |

### Core Features Needed for MLP-Style Module

#### A. League & Season Management
- Create league seasons with defined schedule windows
- Configure event/stage locations and dates
- Season standings tracking (points accumulation)
- Playoff bracket generation (top N qualify)
- Mid-season special events

#### B. Team Management
- Team CRUD with home city, logo, colors
- Team roster management (owner/captain assigns players)
- Gender-balanced roster enforcement (N men + N women)
- Active lineup configuration per match day
- Player status tracking (active, injured, suspended)

#### C. Draft / Recruitment System
**Simplified approach for Vietnam context:**
- Free Agent pool: players who opt in as "draftable"
- Draft event creation (date/time, which teams participate)
- Bidding system OR simpler: team invite + player accept
- Keeper designation (retain players season-to-season)

**OR simpler MVP**: Direct team assignment by captain/admin + transfer window

#### D. Match Management
- Schedule team vs team matches within events
- Support MLP match structure: WD + MD + XD1 + XD2 + DreamBreaker
- Lineup submission (pre-match, by captain)
- Score entry per game within match
- Auto-calculate match winner (3+ games)
- DreamBreaker trigger on 2-2 tie

#### E. Standings & Points
- Points awarded per match win
- Season standings table with win/loss record
- Playoff qualification threshold config
- Historical standings per season

#### F. Event/Stage Management
- Multiple events per season
- Teams assigned to specific events (not all attend all)
- Group play within events (round-robin subset)
- Event-level standings → season points for Sunday matches only

### Integration with Existing Platform
The existing platform already has:
- `tournaments` table — can extend for league context
- `matches` table with referee_id, pair support
- `users` with Elo/OPRS ratings — useful for team building
- `clubs` system — clubs could become teams

**Key gaps to fill:**
- `leagues` entity (season-level container)
- `league_teams` (team rosters per season)
- `league_events` (event/stage within a season)
- `league_matches` (match within event, team vs team)
- `league_standings` (computed points per team per season)
- Draft/recruitment workflow (if full MLP parity needed)
- Lineup submission per match day

### Recommended Simplified Schema for Vietnam MLP Feature

```
leagues
  id, name, season_year, status, config_json

league_teams
  id, league_id, name, city, logo, captain_user_id

league_team_players
  id, team_id, user_id, role(captain/player), gender, status, season

league_events
  id, league_id, name, location, start_date, end_date

league_event_teams
  id, event_id, team_id  (which teams attend this event)

league_matches
  id, event_id, team_a_id, team_b_id, scheduled_at, status
  team_a_points, team_b_points (games won)

league_match_games
  id, match_id, game_type(WD/MD/XD1/XD2/DREAMBREAKER)
  team_a_score, team_b_score, winner_team_id

league_standings
  id, league_id, team_id, wins, losses, points, rank
```

### What to Reuse from Existing Codebase
- `Club` model → maps cleanly to `LeagueTeam` concept
- `Tournament` match scheduling logic → reuse for league matches
- `User` Elo/OPRS → display in team roster views for player quality
- `TournamentReferee` pattern → reuse for league match officials
- `EloService` → can compute DreamBreaker match results

---

## 4. Strategic Fit Analysis

### Why MLP-Style Feature Makes Sense for This Platform

1. **PCL Vietnam is already happening** — real demand exists for team league management in VN
2. **Platform already has clubs** — natural bridge from club to league team
3. **OPRS/Elo system** — unique differentiator for team building (draft by skill rating)
4. **Vietnam market gap** — no Vietnamese platform offers full MLP-style management (Sport Connect is generic)
5. **Competitive positioning** — would be the only VN-native platform with team league + individual rating integration

### Complexity Tiers

| Tier | Scope | Effort |
|---|---|---|
| MVP | Season + teams + matches + standings | ~2-3 weeks |
| Standard | + Event stages + lineup submission + draft invites | ~4-6 weeks |
| Full MLP Parity | + Bidding draft + trade window + DreamBreaker logic | ~8-10 weeks |

**Recommendation**: Start with Standard tier. MVP is viable for first season PCL-style leagues. Full MLP parity adds draft complexity that Vietnamese clubs may not need initially.

---

## Unresolved Questions

1. **Draft complexity**: Does the client want real bidding/auction draft or just team captain invites players? (auction = much more complex)
2. **Season count**: Single active season or multi-season with historical archives?
3. **DUPR integration**: Should league matches submit results to DUPR automatically? (requires DUPR API access)
4. **Mobile vs web**: League management via web admin panel only, or do players need mobile-friendly team views?
5. **PCL format parity**: Is the goal to mirror PCL Vietnam format exactly, or an adapted version?
6. **Payment**: Do teams pay entry fees? If so, ties into unresolved payment gateway question from PDR.
7. **Broadcast/spectator features**: Scoreboard view for live events? (out of scope for v1?)

---

## Sources

- [MLP ABC's Guide](https://majorleaguepickleball.co/abcs-of-mlp/)
- [2025 MLP Competition Structure Overview](https://majorleaguepickleball.co/wp-content/uploads/2025-MLP-Competition-Structure-Overview.pdf)
- [MLP Competition Structure Updates 2025](https://majorleaguepickleball.co/news/major-league-pickleball-announces-competition-structure-updates-and-2025-league-calendar-for-player-keepers-free-agency-waiver-wire-and-trade-deadline/)
- [MLP 2026 20-Team Format](https://www.thedinkpickleball.com/major-league-pickleball-announces-new-20-team-format-end-of-challenger-level-in-2026/)
- [2025 MLP Free Agency Draft Results](https://majorleaguepickleball.co/news/2025-mlp-premier-level-free-agency-draft-results/)
- [Side-out Scoring 2025 Changes](https://pickleball.com/news/side-out-scoring-player-eligibility-headline-changes-for-2025-mlp-competition-structure)
- [DUPR: Vietnam Leading Asia Market](https://www.dupr.com/post/malaysia-and-vietnam-are-leading-asia-pickleball-market)
- [Vietnam First DUPR Tournament](https://www.dupr.com/post/vietnams-first-dupr-tournament-an-international-pickleball-milestone)
- [PPA Tour Asia MB Vietnam Open 2025](https://ppatour-asia.com/tournament/2025/mb-vietnam-open/)
- [Vietnam Hosts Biggest Pickleball Event](https://pickleball.com/news/vietnam-prepares-to-host-its-biggest-pickleball-event-yet)
- [PCL Asia Vietnam Championship](https://pickleballnewsasia.com/pcl-asia-kicks-off-2025-asian-semi-professional-pickleball-championship-to-be-held-in-vietnam/)
- [Vietnam Pickleball Open Cup 2025](https://pickleballplus.vn/form-dang-ki-giai-dau/vietnam-pickleball-open-cup-2025-hcm-stage?lang=en)
- [Sport Connect Vietnam](https://www.vietnam.vn/en/sport-connect-nen-tang-dinh-hinh-tuong-lai-giai-dau-the-thao-viet-nam)
- [Tournated Platform Comparison](https://www.tournated.com/blog/article?slug=top-6-pickleball-tournament-and-league-management-softwares)
- [MatchTime League Management](https://www.matchtime.com/product/league-management-software)
- [PicklePlay Club Management](https://pickleplay.com/clubs/)
- [Swish Tournaments](https://swishsportsapp.com/)
