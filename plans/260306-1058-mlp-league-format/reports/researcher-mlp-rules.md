# MLP Pickleball Format & Scoring Rules Research
**Date:** 2026-03-06 | **Status:** Complete

---

## Executive Summary

MLP (Major League Pickleball) is a **team-based coed format** designed for professional/competitive play. Standard format consists of **4 doubles games + optional DreamBreaker singles tiebreaker**. Vietnam has adopted MLP format with minimal local adaptations; standard international MLP rules apply.

---

## 1. MLP Match Format (Standard)

### Team Composition
- **4 players minimum** (ideally 2 men, 2 women; gender balance mandatory)
- Teams of 4-6 are permissible for larger rosters

### Match Structure
Each match = 4 games decided sequentially:
1. **Women's Doubles** (WD)
2. **Men's Doubles** (MD)
3. **Mixed Doubles Game 1** (MXD-1)
4. **Mixed Doubles Game 2** (MXD-2)

**Match Winner:** First team to win 3 of 4 games wins the match.

### Player Pairing Strategy
- **Gender Doubles** (WD, MD): Players of same gender pair together
- **Mixed Doubles** (2 games): One man + one woman per pairing
- **Flexibility:** Teams select which players compete in which game (within gender constraints)
- **Strategic Advantage:** After coin toss, winning team can choose pairing order/lineup to optimize matchups

---

## 2. MLP Scoring System (2025-2026)

### Regular Games (Doubles)
- **Format:** Side-out scoring (only serving team scores)
- **Win Condition:** First to 11 points, must win by 2
- **Scoring Pace:** Slower than rally scoring; emphasizes serve importance

**2024-2025 Change:** MLP switched from rally scoring → side-out scoring for consistency with USA Pickleball standards.

### Match Outcome Determination
- **Regular Match Win:** First team to win 3 games wins (best-of-4)
- **If tied 2-2 after 4 games → DreamBreaker tiebreaker played**

---

## 3. DreamBreaker Tiebreaker Format

**When Activated:** Played only if match is 2-2 after 4 doubles games.

### DreamBreaker Rules
- **Game Format:** Singles rotation to 21 points (win by 2)
- **Scoring System:** Rally scoring (both teams score each point, faster pace)
- **Player Rotation:** All 4 team members MUST participate
  - Each player serves 4 consecutive points
  - After 4 points, player swaps to next in rotation
  - Order: Player 1 → Player 2 → Player 3 → Player 4 → repeat

- **Serve Position:**
  - Right side: When team score is even
  - Left side: When team score is odd

- **Selection Process:**
  - Team that "reacted" in mixed doubles selects 1st player (1 min decision)
  - Opponent selects their 1st player (1 min decision)
  - Process repeats until all 4 players declared per team

- **Court Changes:** Teams change ends when one team reaches 11 points

- **Resources per Team:**
  - 1 free challenge (serve/boundary calls)
  - 1 timeout

### Strategic Impact
- Forces all 4 players to be competitive in singles
- Prevents single-player dominance
- High-pressure format rewards mental resilience
- Comeback potential exists until 21-point threshold

---

## 4. Vietnam-Specific Adaptations

**Finding:** Vietnam has **NO documented local rule modifications** to MLP format.

### Vietnam Pickleball Context
- Vietnam uses **standard international MLP rules** directly
- Recent team tournaments (VPC Team Tournament) apply standard MLP format
- Vietnamese pickleball community adopts MLP as the professional competitive standard
- Some Vietnamese sources translate MLP rules into Vietnamese, but content matches international rules exactly

### Vietnam Sources Reviewed
- phiten.vn: Confirms MLP team format (4 players, gender balance, 4 games + DreamBreaker)
- prokennexpickleball.vn: Documents MLP vs USA Pickleball vs PPA rule differences
- vnpickleball.tv: Luật thi đấu pickleball 2025 guide
- Vietnamese tournament announcements (Vietnam Pickleball Open Cup): Uses standard MLP format

---

## 5. MLP vs Other Formats (Comparison)

| Aspect | MLP | USA Pickleball | PPA |
|--------|-----|-----------------|-----|
| **Scoring** | Side-out (doubles), Rally (tiebreaker) | Side-out | Rally |
| **Win Condition** | 11 points (win by 2) | 11 points (win by 2) | 25 points (win by 2) |
| **Team Format** | Coed 4v4 doubles | Individual | Individual |
| **Net Contact on Serve** | Fault | Play-on if in bounds | Fault |
| **Serve Type** | Drop or overhead (no spin) | Drop or overhead | Overhead only |
| **Serve Challenges** | 1 per player per match | Not allowed | Before 3rd shot |
| **Boundary Calls** | Deemed "in" unless called "out" | Standard | Standard |
| **Tiebreaker** | DreamBreaker (singles rotation) | N/A | N/A |

---

## 6. Implementation Recommendations for Pickleball App

### Database Schema Implications
1. **Match Model:** Must support 4-game structure (WD, MD, MXD-1, MXD-2)
2. **Game Model:** Store individual game results + scoring type (side-out vs rally)
3. **DreamBreaker Model:** Separate entity for tiebreaker with:
   - Player rotation order (4 players sequenced)
   - Rally scores (not points; tracks all points)
   - Team scores at 11-point mark (end change)
   - Challenge/timeout tracking per team

4. **Player Pairing:** Store team roster + game assignments:
   ```
   Match → Team → Roster (4 players)
   Game (WD/MD/MXD) → Selected Players (2 per game)
   DreamBreaker → Rotation Order (4 players per team, sequential)
   ```

### Scoring Logic
- **Regular Games:** Side-out logic (only serving team scores)
  - Track: Serve side, current score, server
  - No score increments on receiving team until they win serve

- **DreamBreaker:** Rally scoring logic
  - Both teams score every point
  - Player rotation every 4 points
  - End change at 11 points

### UI/UX Considerations
- **Match Status:** Show 4-game progress (0-4 games, winner = 3)
- **DreamBreaker Trigger:** Auto-detect 2-2 tie, prompt DreamBreaker creation
- **Score Entry:**
  - Double: Left/Right court display
  - DreamBreaker: Highlight current rotating player
  - Show player rotation countdown (4, 3, 2, 1 points remaining)
- **Pairing Display:** Show team roster with game assignments (WD, MD, MXD-1, MXD-2)

### Rules Engine
- **Match Winner Logic:** First to 3 game wins = match win
- **DreamBreaker Trigger:** IF match games = 2-2 AND all 4 games completed → activate DreamBreaker
- **DreamBreaker Winner:** First to 21 with 2-point margin = match winner
- **End Change Logic:** DreamBreaker → swap ends at team score = 11

---

## 7. Key Differences from Standard Pickleball

1. **Coed Requirement:** MLP mandates mixed-gender teams (not individual play)
2. **Multiple Games per Match:** 4 games determine 1 match (not single-game matches)
3. **Strategic Pairing:** Teams have agency in player selection/lineup per game
4. **Hybrid Scoring:** Two scoring systems in one match (side-out + rally)
5. **DreamBreaker Mechanic:** Unique rotation format not seen in USA Pickleball or PPA
6. **Serve Validity:** Net contact during serve = fault (stricter than USA Pickleball)

---

## 8. Unresolved Questions

1. **Relay Tournaments:** Do Vietnam tournaments use MLP relay format (team relay across multiple matches)? Or pure MLP head-to-head?
2. **Gender Balance Enforcement:** Does Vietnam strictly enforce 2M/2W minimum, or allow variations?
3. **Pro vs Amateur Rules:** Are there simplified MLP variants for recreational/club play in Vietnam?
4. **DreamBreaker Variants:** Do Vietnam clubs ever use different tiebreaker formats (e.g., extended DreamBreaker beyond 21)?
5. **Historical Data:** Existing tournament records in app—are they MLP format or older USA Pickleball formats?

---

## Sources

- [ABC's of MLP - Major League Pickleball](https://majorleaguepickleball.co/abcs-of-mlp/)
- [FAQ - Major League Pickleball](https://majorleaguepickleball.co/faq/)
- [2024 MLP Rules Guide](https://majorleaguepickleball.co/wp-content/uploads/2024-MLP-Rules-Guide-6.4.24.pdf)
- [Side out scoring, player eligibility headline changes for 2025 MLP](https://pickleball.com/news/side-out-scoring-player-eligibility-headline-changes-for-2025-mlp-competition-structure)
- [MLP Scoring Format – PickleballMAX](https://www.pickleballmax.com/2024/05/mlp-scoring-format/)
- [MLP (Major League Pickleball) - Phiten Vietnam](https://phiten.vn/news/mlp-major-league-pickleball-the-thuc-dong-doi-day-chien-thuat-cho-nguoi-choi-pickleball)
- [Pickleball Rule Differences - ProKennex Vietnam](https://prokennexpickleball.vn/su-khac-biet-giua-quy-tac-cua-usa-pickleball-ppa-va-mlp/)
- [Minor League Pickleball Rules - DUPR](https://www.dupr.com/minorleague/rules)
- [MLPLAY Teams & Scoring — Pickles](https://www.picklesne.com/mlplay-teams-scoring)
