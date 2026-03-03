# Reclub Matches Feature & Social Play Match Generation Research

**Date:** 2026-03-03 | **Researcher:** AI Researcher | **Status:** Complete

---

## 1. What is Reclub?

Reclub (reclub.co / pickleball.reclub.co) is a free iOS/Android app for social sports club management, with a dedicated pickleball vertical. Target users: club organizers and recreational players. Core value: simplifying recurring club meetups, match generation, scoring, and DUPR integration.

**Key differentiators:**
- Mobile-first club management (not a tournament bracket tool)
- DUPR-integrated: hosts can bulk-submit match results to DUPR in seconds
- "Street Cred" community reputation system (skill-based Kudos)
- Free tier + "Supporter" paid tier (payment tracking, advanced features)

Prior research on general Reclub features: `plans/reports/researcher-260227-1430-reclub-feature-analysis.md`

---

## 2. Reclub "Generate Matches" Feature

**Trigger flow:**
1. Club organizer creates a meet (one-off or recurring)
2. Players RSVP to the meet
3. Organizer opens the "Matches" tab on the meet
4. Taps "Generate Matches"
5. Selects format + number of courts
6. System auto-generates all match assignments

**Key behaviors:**
- Matches are randomized in order (no schedule optimization — known limitation per FAQ)
- Supports multiple courts; players sorted/assigned by skill level if host configures it
- After RSVPs, host can sort participants by skill, then create matches and assign courts by skill tier
- DUPR ratings visible on participant profiles for skill-based grouping
- Generates a complete session layout in "just a few taps"

**Scoring after generation:**
- Scores entered via phone (player, ref, or coach depending on permissions)
- Host toggles "allow players to score" in meet settings
- Standings update in real-time after each score entry
- Match history + stats persist (pairings, teammates, opponents)

---

## 3. Reclub "Create Custom Match" Feature

No dedicated "Create Custom Match" UI is explicitly documented. The custom workflow appears to be:
- Host manually assigns players to matches after generation
- Playoff seeding can be adjusted manually ("manual seeding" option in playoff tab)
- Coaches with coach role can "create and score matches" — implies ability to create ad-hoc matches

Custom match is more of an admin override than a distinct feature.

---

## 4. Match Formats Supported by Reclub

| Format | Description | Use Case |
|--------|-------------|----------|
| **Round Robin** | Each team plays every other once; no playoff | Casual socials, equal participation |
| **Pool Play + Playoff** | Round robin in groups → top teams enter elimination bracket | Larger events, skill separation |
| **Single Elimination** | Seeded bracket, losers out | Quick tournaments |
| **Rotating Partners** | Players rotate partners each round; individual point tracking | Social mixers, "dynamic doubles" |
| **Singles** | 1v1 format | Individual competition |
| **Set Teams (Fixed Doubles)** | Same partnership throughout | Traditional doubles |

**Dynamic Doubles** (observed in real Reclub events): DUPR-ranked players placed on courts, each player partners with everyone else on their court in a round robin for ~3 matches per session.

---

## 5. Scoring in Reclub Matches

- **Game scoring:** Play to 11 (standard) or timed cutoff (15-20 min)
- **Score entry:** In-app by player/ref/coach (permission-based)
- **Standings metric:** Win/loss record + points differential (format-dependent)
- **Tiebreakers:** Head-to-head, points scored (format-dependent)
- **Community metric:** "Street Cred" = Kudos received × unique Kudos-givers (cross-session skill reputation)
- **DUPR sync:** Bulk submit all session matches to DUPR in seconds after session ends

---

## 6. Player Pairing Logic

Reclub does **not** expose an algorithmic pairing engine. Pairing is:

1. **Skill-based sorting**: Host sorts RSVP'd players by DUPR rating → assigns courts by skill tier (e.g., Court 1 = top players, Court 2 = mid, etc.)
2. **Random match order**: Within a skill tier/court, match order is randomized (confirmed limitation)
3. **Rotating partners**: System rotates who plays with whom across rounds to maximize mixing
4. **No schedule optimization**: Known gap — back-to-back matches for same team can occur

---

## 7. General Best Practices: Social/Recreational Pickleball Match Generation

### 7.1 Common Social Formats

| Format | Aliases | How It Works | Best For |
|--------|---------|-------------|---------|
| **Rotating Partner Round Robin** | Mixer, Social RR | Every player partners with every other player once; individual points tracked | Equal skill groups, maximize mixing |
| **King of the Court** | Up/Down the River, Waterfall, Ladder | Winners move up a court, losers move down; partners split and reform after each game | Mixed skill groups, continuous play |
| **Popcorn** | - | Random unique matchups each round, optimized to mix everyone | Equal-ish skill, pure social |
| **Challenge Court** | Paddle Rack | Winners stay on court (up to N games), others wait; first-come serve | Limited courts, open play |
| **Numbering System** | - | Players number 1-6, rotate fixed positions | Very casual, quick rotation |

### 7.2 Pairing Algorithm Principles (Best Practice)

For balanced social sessions, a good algorithm should:
1. **No repeated partners** within a session if possible
2. **No repeated opponents** within a session if possible
3. **Equal rest time** — no player sits out twice in a row
4. **Skill grouping first** → random within group (avoids mismatches in mixed-level sessions)
5. **Handle odd numbers** automatically — assign byes equally across players

Mathematical basis: "Round Robin scheduling problem" — use the polygon/circle rotation method or pre-computed tables (e.g., printyourbrackets.com publishes static tables for 4-25 players).

### 7.3 Scoring Conventions for Social Play

| Scoring Mode | Details |
|-------------|---------|
| Play to 11, win by 2 | Standard casual format |
| Play to 15/21 | Used in more structured sessions |
| Timed (15-20 min) | Good for keeping sessions on schedule |
| Rally scoring | Every serve = point (faster games) |
| Individual points | Track per-player not per-team (for rotating partners) |

---

## 8. Competitor Apps: How They Handle Social Match Scheduling

### Pickleheads (pickleheads.com)
- 11 built-in formats: Popcorn, Gauntlet, Up & Down the River, Claim the Throne, Cream of the Crop, mixed doubles, fixed partner, rotating partner, etc.
- Algorithm ensures: no duplicate pairings, balanced rest, equal mix
- Syncs to DUPR
- Strongest format library of any pickleball app

### DUPR Sessions (dupr.com)
- 2-hour round-robin sessions
- Games to 11
- Randomly assigned partners OR partnered (sign up together)
- Results auto-update DUPR rating

### MatchUp Tennis & Pickleball (matchuptennis.app)
- Round robin generator
- Handles odd player counts, byes
- Court allocation by skill
- Focused on league management

### PlayTime Scheduler (pickleballsplay.com)
- Free platform, 13,000+ venues globally
- Streamlined scheduling, less format sophistication

### RoundRobinly (roundrobinly.com)
- Pure scheduling tool
- Algorithm: no repeated partners, balanced rest, auto court allocation
- Supports all racquet sports
- No scoring/DUPR integration

---

## 9. Key Insights for Implementation

**For a "social meetup matches" feature (our use case):**

1. **Generate Matches** = take list of checked-in participants → output a round-robin schedule with balanced pairings across available courts
2. **Core input**: player list + court count → output: N rounds of matches
3. **Pairing algorithm**: standard round-robin rotation (polygon method), constraint: no repeat partner in same session
4. **Skill sorting**: optional pre-step — sort players by ELO/OPRS, then group by court, then generate within group
5. **Formats needed (minimum viable)**:
   - Rotating doubles round robin (most common for social play)
   - Fixed doubles round robin
   - King of the Court (court ladder)
6. **Scoring**: play to 11 OR timed; individual points for rotating format, team points for fixed
7. **No schedule optimization required for MVP** — Reclub themselves ship randomized order

**Schema hint**: A session's matches need: `match_id, session_id, round_number, court_id, team1_player_ids[], team2_player_ids[], score_team1, score_team2, status`

---

## Unresolved Questions

1. Does Reclub expose the exact rotation algorithm (polygon method, pre-computed table, or custom)? Not documented publicly.
2. Does "Generate Matches" in Reclub create all rounds at once or one round at a time? FAQ implies all rounds, but behavior after score entry is unclear.
3. For our platform: should match generation be per-club-activity, or a standalone feature? (Context: we have `club_activities` table already)
4. Should skill grouping use `elo_rating` field or `opr_level` (OPRS)? Both exist in our user model.
5. Does the club activity detail page already have a "Matches" tab to attach this feature to?

---

## Sources

- [Reclub For Organizers](https://pickleball.reclub.co/for-organizers)
- [Reclub FAQ](https://pickleball.reclub.co/faq)
- [Reclub Round Robin Video](https://pickleball.reclub.co/videos/v/create-casual-round-robins)
- [Reclub Rotating Partners Video](https://pickleball.reclub.co/videos/v/how-to-rotating-partners-round-robins-kudos-street-cred)
- [DUPR & Reclub Partnership](https://pickleball.reclub.co/news/official-partnership-announcement-dupr-and-reclub-join-forces)
- [Pickleheads Round Robin](https://www.pickleheads.com/round-robin)
- [Types of Pickleball Rec Play - PlayPickleball](https://www.playpickleball.com/types-of-pickleball-rec-play/)
- [Rotating Partners Schedules - PrintYourBrackets](https://www.printyourbrackets.com/rotating-doubles-round-robin-schedules.html)
- [RoundRobinly](https://roundrobinly.com/)
- [All-Play-All App](https://www.allplayall.app/)
- [Round Robin Pickleball Guide - FLPAC](https://playatpac.com/round-robin-pickleball-guide/)
