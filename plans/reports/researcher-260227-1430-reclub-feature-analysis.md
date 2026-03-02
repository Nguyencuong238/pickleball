# ReClub Pickleball App - Feature Analysis Report

**Date:** 2026-02-27 | **Research Duration:** Comprehensive web research | **Status:** Complete

---

## Executive Summary

ReClub is a free-to-use mobile app (iOS/Android) designed to help pickleball club organizers and players manage activities, competitions, and member engagement. The platform focuses on automating weekly activities, simplifying tournament management, and integrating with DUPR (global pickleball rating system). Key findings show ReClub emphasizes mobile-first delivery, automated workflows, and community engagement features.

---

## 1. RECURRING SCHEDULE FEATURE

### 1.1 Overview & Purpose
- **Use case:** Schedules designed for clubs with fixed weekly recurring activities
- **Automation benefit:** Upon creating a schedule once, meets are automatically published every week
- **Goal:** Eliminates repetitive manual event creation and notification management

### 1.2 How Recurring Schedules Work
- **Creation flow:** Organizer accesses club's Activities tab → taps blue "+" icon → selects "create recurring meet"
- **Trigger condition:** Selected if "activity is at the same date/time/location weekly"
- **Auto-creation:** System automatically creates the activity every week without organizer intervention
- **Auto-invitations:** Club members are automatically invited to recurring activities each week
- **Notification:** Automatic notifications sent to club members when recurring meets are published

### 1.3 Available Fields & Configuration Options
Based on research, the following fields are likely available when creating a recurring schedule:

**Core Fields:**
- Date & time (must be the same each week)
- Location (must be consistent)
- Activity name/title
- Description (optional)

**Advanced Options:**
- Skill level (organizers can sort/filter by skill)
- Court assignment (number of courts needed)
- Court assignment strategy (auto-assign by skill)
- Participant capacity (max players)
- Activity format selection (open play, round robin, etc.)

**Automation Settings:**
- Auto-create enable/disable toggle
- Auto-invite members toggle
- Notification frequency/timing
- Recurring schedule pattern (weekly implied, specific day selection needed)

### 1.4 How Meets Are Auto-Published
- System creates meet instances automatically on the scheduled day/time each week
- Auto-invitations are triggered upon creation
- Notifications dispatched to all club members
- No manual approval step required

### 1.5 Specific Days, Times, and Locations
- **Yes, supported:** Organizers can set specific days and times (same each week)
- **Yes, supported:** Organizers can set specific locations
- **Constraint:** These must be identical each week for automation to work effectively
- **Note:** ReClub is location-aware and allows proximity filters for discovery

### 1.6 Member RSVP System
- **RSVP action:** Members receive automatic invitations and can RSVP through the app
- **Join flow:** Players tap "join" to RSVP (simple one-tap action)
- **RSVP status:** Hosts can see confirmed vs. unconfirmed participants
- **Visibility:** Players can see who has already RSVPd before committing

### 1.7 Waitlist System
- **Availability:** Yes, automatic waitlist system implemented
- **Capacity management:** When a meet reaches max capacity, additional join requests go to waitlist
- **Auto-approval option:** Hosts can enable "auto-approve" toggle for waitlist
- **Auto-approval behavior:** If enabled, waitlist operates on first-come-first-serve basis with automatic additions when spots open
- **Manual approval:** If auto-approve is disabled, host must manually approve waitlist requests

### 1.8 Auto-Publishing Timeline
- **Advance notice:** Meets are created each week on the scheduled day/time
- **Notification timing:** If meet is within 36 hours, hosts have option to send promotional notification to community members
- **Player discovery:** Players can search/discover meets via Discover tab with time/distance filters

### 1.9 Key Workflow for Organizers
1. Create recurring schedule once (set day, time, location, capacity)
2. System auto-creates meet instances weekly
3. System auto-invites members
4. Automatic notifications dispatched
5. Members RSVP through app
6. Host can optionally manage waitlist or enable auto-approval
7. After RSVPs close: Host generates matches (if applicable)
8. System tracks attendance/scoring

---

## 2. CREATE ONE-OFF MEET FEATURE

### 2.1 Overview & Purpose
- **Use case:** Create a single, non-recurring activity for an ad-hoc event
- **Difference from recurring:** Does not repeat; stands alone as a single event
- **Typical use:** Special events, guest matches, makeup sessions, one-time tournaments

### 2.2 Available Fields
Expected fields based on research and comparison to recurring meets:

**Core Fields:**
- Meet name/title (required)
- Date (required, single date)
- Time (required, specific start time)
- Location (required)
- Description/notes (optional)

**Capacity & Settings:**
- Max participants/capacity
- Participant limit per court
- Skill level designation (e.g., "ALL SKILL LEVEL", specific rating range)

**Activity Type/Format:**
- Open play (unstructured)
- Round robin (structured tournament)
- Competition (tournament with bracket/scoring)

**Advanced Options:**
- Auto-assign courts by skill
- Allow players to self-score (toggle)
- Enable/disable notifications

### 2.3 RSVP Handling
- **RSVP method:** Players discover meet via Discover tab and tap "join" to RSVP
- **Confirmation:** RSVP creates participant entry in the meet
- **Player visibility:** Can see who else is attending, check skill levels before committing
- **Host visibility:** Organizer sees confirmed RSVPs and waitlist in real-time

### 2.4 Differences from Recurring Meets
| Aspect | One-Off Meet | Recurring Schedule |
|--------|-------------|-------------------|
| **Frequency** | Single instance | Repeats every week automatically |
| **Creation effort** | Create each time | Create once, auto-generates weekly |
| **Notifications** | Manual promotion option | Automatic weekly invites |
| **Use case** | Special events, ad-hoc | Regular club activities |
| **Planning** | Flexible dates/times | Fixed day/time/location |

### 2.5 Creation Flow for Organizers
1. Navigate to club Activities tab
2. Tap blue "+" icon
3. Select "create one-off meet" (not recurring)
4. Enter meet details (date, time, location, capacity)
5. Set format (open play, round robin, etc.)
6. Publish (meets available for discovery immediately)
7. (Optional) Send promotional notification to community members

---

## 3. CREATE COMPETITION FEATURE

### 3.1 Overview & Purpose
- **Use case:** Create club-level competitions accessible to all club members
- **Goal:** Structured tournament management with automated match generation, scoring, and standings
- **Scope:** Ranges from casual round robins to full tournament brackets

### 3.2 How the Competition System Works

**Process Flow:**
1. Organizer taps blue "+" icon on club Activities tab
2. Selects "create competition"
3. Chooses preferred format (round robin, pool, single elimination)
4. Enters competition details (name, teams, number of courts, etc.)
5. System automatically generates matches based on specifications
6. Players update scores on their phones during/after matches
7. Live standings updated in real-time

**Key Workflow After Creation:**
1. Members RSVP to competition
2. Host navigates to "Matches" tab
3. Taps "Generate Matches" button
4. Selects format and court count
5. System generates match assignments
6. Players/refs update scores (if permitted)
7. Standings automatically calculated

### 3.3 Supported Formats

**Format 1: Round Robin**
- Each team plays every other team once
- No single elimination phase
- All teams complete full schedule
- Ideal for: Casual socials, equal participation
- Standalone format

**Format 2: Pool Play with Playoff**
- Multiple groups play round-robin in pool phase
- Top teams from each pool advance to playoff bracket
- Playoff bracket can be single or double elimination
- Ideal for: Larger competitions, skill separation
- Two-phase format

**Format 3: Single Elimination**
- Teams seeded in bracket with assigned matchups
- Winners advance to next round
- Losers eliminated
- Accessed via "Matches" tab → "Playoff" button → "Bracket Settings"
- Ideal for: Quick tournaments, time-limited events
- Single-phase format

**Additional Flexibility:**
- Supports multiple team structures: Singles, Rotating Partners, Set Teams
- Configurable number of courts (handles large groups across multiple courts)
- Each format can be customized within the few taps required

### 3.4 How Scores Are Tracked

**Scoring Input Methods:**
- Players can update scores on their phones (if allowed)
- Refs/coaches can score matches (have permission)
- Host can enable "allow players to score" toggle for open scoring

**Scoring Workflow:**
1. Match occurs during competition
2. Designated scorer (player, ref, or coach) opens match in app
3. Enters match result/score
4. Score submitted to system
5. System automatically updates standings
6. Live standings reflect change in real-time

**Role-Based Scoring Permissions:**
- **Ref role:** Can score matches (must be assigned by host)
- **Coach role:** Can create and score matches
- **Players:** Can score if host enables "allow players to score" toggle
- **Host:** Always has full scoring access

### 3.5 Standings & Leaderboard Display

**Live Standings Features:**
- Real-time leaderboard display showing current standings
- Updated immediately after each match score entry
- Accessible in-app during competition
- Shows all competitors' current positions and records

**Standing Calculation Basis:**
- Win/loss record
- Head-to-head results
- Points scored/differential (varies by format)
- Format-dependent tiebreaker rules

**Community Leaderboards (Beyond Single Competitions):**
- Community leaderboards show best players in the city/region
- "Street Cred" leaderboards based on Kudos received
- Street Cred = (number of Kudos received) × (unique players giving Kudos)
- Long-term ranking across all activities

**Match History & Stats:**
- Players can view past competition results
- Access pairings showing which players they compete with
- Identify teammates, best opponents, worst matchups
- Track "Chart Toppers" (most wins), "Teammates" (frequent partners), "Opponents" (frequent foes)

### 3.6 Member Signup/Registration
- **Discovery:** Members browse club competitions via Activities tab or Discover tab
- **Signup method:** Tap "join" to RSVP to competition
- **Capacity limits:** Can reach max capacity (subject to waitlist if full)
- **Team assignment:** Organizer assigns teams/pairings, or system auto-generates based on format
- **Skill-based assignment:** Organizer can sort by skill and assign courts/teams accordingly
- **One-tap simplicity:** ReClub emphasizes "one-tap registration" for member signup

### 3.7 Competition Workflow Summary

```
Create Competition
    ↓
Select Format (RR, Pool, SE)
    ↓
Configure Details (name, teams, courts)
    ↓
Publish (accessible to club members)
    ↓
Members RSVP (one-tap join)
    ↓
Host Generates Matches (tap "Generate Matches")
    ↓
Play Matches (with score tracking)
    ↓
View Live Standings (real-time updates)
    ↓
View Match History & Stats (post-competition)
```

### 3.8 DUPR Integration
- Matches can be submitted to DUPR (pickleball's global rating system)
- Bulk submission: Hosts can submit all matches to DUPR in seconds
- Rating verification: Hosts can verify player DUPR ratings to ensure correct skill divisions
- Automatic sync: Match results submitted through ReClub can directly update player DUPR ratings
- Player profiles: Players can connect DUPR accounts to have ReClub matches reflected in global rating

---

## 4. GENERAL CLUB MANAGEMENT & UI/UX

### 4.1 Club Setup & Navigation
- **Entry point:** Tap blue "+" button on home screen
- **Club creation:** Follow guided prompts to create a new club
- **Main navigation:** Home, Activities, Discover, Profile/Settings tabs
- **Club management:** Access via Club Menu or Share Icon

### 4.2 Activities Tab (Club Hub)
- **Primary control center:** All club activities organized here
- **Quick actions:** Tap blue "+" icon to create:
  - One-off meet
  - Recurring schedule
  - Competition
- **Activity view:** Lists all upcoming activities with key details
- **Management tabs:** Activities, Members, Matches (post-RSVP)

### 4.3 Member Management Features

**Invitation Methods:**
- **Method 1 (New players):** Generate unique club code, share via text/link, new players join via code
- **Method 2 (Existing users):** Navigate to club's Members screen, tap "Invite Friends" to add users already on ReClub
- **Club code:** Unique identifier displayed after club creation, accessible via Club Menu or Share Icon
- **Share mechanism:** Copiable message with club code and invite link

**Member Roles & Permissions:**
- **Ref:** Can score matches only
- **Coach:** Can create and score matches
- **Regular member:** Can RSVP and participate
- **Admin/Host:** Full control over club, activities, and member roles
- **Role assignment:** Tap member's profile in Participants tab, select appropriate role

**Member Visibility:**
- See all active club members
- View member DUPR ratings (if connected)
- Check member skill levels before competition
- See who has RSVPd to specific activities

### 4.4 Notification System

**Automatic Notifications Triggered By:**
- Recurring meet auto-created (weekly invitations)
- One-off meet published (sent to relevant members)
- Competition created (sent to club members)
- Match score updated (stakeholders notified)
- Waitlist spots open (waitlist members notified)
- RSVP reminders (if configured)

**Notification Content:**
- Activity details (date, time, location, format)
- RSVP status changes
- Match results
- Leaderboard updates
- Community-wide promotions

**Notification Controls:**
- Optional promotional notification: If meet is within 36 hours, host can send community-wide promotion
- App-level notification settings likely available (not explicitly documented)
- Users note excessive promotional notifications as a pain point

**User Experience Note:** Some users report notification fatigue from club promotions, suggesting notification tuning is important.

### 4.5 Payment & Fee Collection

**Current State:**
- ReClub does NOT support direct in-app payments
- Payment processing noted as "in the roadmap" for future development
- Workaround: External payment collection via off-platform methods

**Payment Receipt Tracking (Supporter Feature):**
- Available only to ReClub Supporter subscribers (paid tier)
- Workflow:
  1. Host provides banking details in Profile settings
  2. Payment details auto-populate in "Payments" tab for hosted meets
  3. Players upload payment proof (screenshot) when RSVPing
  4. Players must be ReClub Supporters to upload receipts
  5. Host/admin reviews uploaded images and tags player as "Paid"
- Supported proof types: Bank transfer screenshots, Venmo receipts, Cash payment notes
- Purpose: Off-platform transaction record-keeping, not actual payment processing

**Fee Examples:**
- Varies by club; example: 100 PHP (~$2-3 USD) per open play session in one club

**Implications:**
- Organizers must use external systems (Venmo, bank transfer, cash) for actual payment collection
- ReClub is a record-keeping tool, not a payment processor (currently)
- Supporter tier adds convenience but doesn't enable payments

### 4.6 Check-In System

**Limited Documentation Found:**
- Explicit check-in feature not clearly documented in available sources
- "Mark paid/checked-in" status mentioned in general feature list
- Likely implementation: Host/admin toggles attendance status for RSVPd members

**Likely Workflow:**
1. Before/during event, host opens meet participant list
2. Host marks members as "checked in" or "attended"
3. Used to track actual attendance vs. RSVP
4. May be combined with payment status for completeness
5. Data may inform future stats or leaderboard calculations

**Note:** Requires direct feature testing in app for precise behavior.

### 4.7 Club Discover Feature

**Discovery Mechanism:**
- Discover tab shows activities from other clubs
- Location-aware: Shows clubs and activities nearby
- Proximity filters: Players can filter by distance/location
- Flexibility: Players can change location to discover activities abroad

**Activity Search Filters:**
- By day of week
- By time range
- By proximity/distance
- By skill level (implied)
- By format (open play, round robin, competition)

---

## 5. PLAYER EXPERIENCE FEATURES

### 5.1 Finding & Joining Activities
- **Discovery interface:** Tap Discover tab
- **Information density:** Key details listed in one place (format, location, time, participant count)
- **Skill assessment:** Players can check skill of confirmed RSVPd players before joining
- **One-tap join:** Single button press to RSVP to activity
- **Before committing:** Review full activity details including:
  - Format/activity type
  - Location and directions
  - Start time and duration
  - Current player list and skill levels
  - Capacity and waitlist status

### 5.2 Match History & Stats

**Stats Section (ReClub Supporter feature):**
- Requires "ReClub Supporter" paid subscription to unlock
- Shows comprehensive match history with statistics
- Displays: Win/loss record, tournament results, skills progression
- "Chart Toppers" metric: Tracks most frequent wins
- "Teammates" metric: Shows most-played partners
- "Opponents" metric: Identifies most-played adversaries
- "Best against" / "Who beats you" analysis

**Match Pairings Visibility:**
- See historical pairings with specific players
- Identify best teams (frequent partners)
- Identify tough matchups (frequent opponents)
- Long-term stat tracking across all matches

### 5.3 Community Engagement Features

**Kudos System:**
- Players can give "Kudos" (recognition) to other players
- Social recognition mechanism
- Encourages community engagement

**Street Cred Leaderboard:**
- Calculated based on Kudos received
- Formula: (Kudos count) × (Unique players giving Kudos)
- Recognizes active, respected community members
- Long-term community standing metric

**Leaderboards:**
- Community leaderboards by city/region
- Shows top players in your area
- Integrated with DUPR ratings for skill context
- Encourages competitive engagement

---

## 6. PLATFORM INTEGRATIONS

### 6.1 DUPR (Pickleball Unified Play Rating)
- **What it is:** World's most accurate global pickleball rating system
- **Rating scale:** 2.000 (beginner) to 8.000 (professional)
- **Universal scale:** All players rated on same 2.00-8.00 scale regardless of age, gender, location
- **Rating calculation:** Based on margin of victory, play type, outcome

**ReClub-DUPR Integration:**
- Players can connect DUPR account to ReClub profile
- Match results from ReClub auto-sync to DUPR ratings
- Hosts can bulk submit matches to DUPR in seconds
- Hosts can verify player DUPR ratings in-app
- Hosts can assign skill divisions based on verified DUPR ratings
- Eliminates manual DUPR submission (streamlines workflow)

**Benefits:**
- Accurate skill-based team assignment
- Prevents skill sandbagging
- Gives players incentive to play (DUPR rating growth)
- Connects local play to global rating system

---

## 7. PLATFORM OVERVIEW

### 7.1 Availability & Download
- **Platforms:** iOS (Apple App Store) and Android (Google Play Store)
- **Status:** Mobile-first, mobile-only platform
- **Download status:** 100K+ downloads on Android
- **Availability:** Global (supports multiple countries and languages based on references)

### 7.2 Pricing Model
- **Base app:** Free to use (free tier)
- **ReClub Supporter subscription:** Optional paid tier
  - **Pricing:** $2-3 USD/month or $20-30 USD/year (2-month discount)
  - **Benefits:** Payment receipt tracking, stats access (Chart Toppers, Teammates, Opponents), leaderboards, ad-free experience
- **No transaction fees:** ReClub doesn't take cuts from off-platform payments

### 7.3 App Store Ratings
- **Google Play:** 4.6 stars from 7,290 reviews (100K+ downloads)
- **Known issues:** Some users report event chat loading delays, notification spam from promotions
- **Overall sentiment:** Solid core functionality with room for UX polish

### 7.4 Primary Use Cases
- Open play facilitation (regular socials)
- Round robin tournaments (casual competitions)
- League management (recurring structures)
- Tournament bracket management (single/double elimination)
- Community building (leaderboards, kudos, stats)
- DUPR integration (rating tracking)

---

## 8. KEY INSIGHTS & PATTERNS

### 8.1 Design Philosophy
1. **Mobile-first:** App-only (no web), optimized for quick actions
2. **Simplicity:** "Just a few taps" emphasis throughout marketing
3. **Automation:** Reduce organizer workload (auto-creation, auto-invites, auto-matches)
4. **Community:** Leaderboards, kudos, stats to keep players engaged
5. **Integration:** DUPR partnership for credibility and rating accuracy

### 8.2 Organizer-Focused Features
- Emphasis on saving time (no more Excel, Google Docs, group chats)
- Automated workflows reduce manual effort
- Role-based permissions (ref, coach, member) delegate tasks
- Bulk DUPR submission streamlines rating management
- Single platform for registration, scheduling, scoring, standings

### 8.3 Player-Focused Features
- One-tap join (discovery and RSVP combined)
- See skill of participants before committing
- Live standings to track competition
- Match history and stats (with Supporter)
- Kudos and Street Cred for recognition
- Global DUPR rating integration

### 8.4 Missing or Underdocumented Features
- Direct in-app payment processing (roadmap item)
- Detailed check-in mechanism specifics
- Fine-grained tournament configuration options
- Custom scoring rules or tie-breakers
- Fixture/schedule planning tools (beyond auto-generation)
- Communication features (messaging, announcements) - basic chat exists but reported as unreliable
- API documentation or third-party integration options

### 8.5 Recurring Schedule vs. One-Off vs. Competition

| Feature | Recurring | One-Off | Competition |
|---------|-----------|---------|------------|
| **Frequency** | Weekly auto-repeat | Single event | Single/series |
| **Purpose** | Club socials, regular meets | Special events | Structured tournaments |
| **Auto-creation** | Yes | No | No |
| **Match generation** | After RSVP (optional) | Optional | Automatic |
| **Scoring** | Optional | Optional | Required |
| **Standings** | Optional | Optional | Automatic/Live |
| **DUPR submission** | Per-match | Per-match | Bulk option |
| **Use case** | Monday 6pm open play | Guest tournament | Club championship |

---

## 9. UNRESOLVED QUESTIONS

1. **Exact API/Form Fields:** What are the precise required/optional form fields when creating each activity type? Only high-level fields documented.

2. **Recurring Schedule Publishing Timing:** How many days in advance are recurring meets published for discovery? Is it weekly? Can organizers configure this?

3. **Waitlist Mechanics:** When auto-approve is enabled, how are spots filled? First RSVP? RSVP timestamp ordering? Priority system?

4. **Court Assignment Algorithm:** How does "sort by skill and assign courts" work algorithmically? Equal distribution? Balanced skill levels? Specific DUPR thresholds?

5. **Match Generation Algorithm:** How does the round-robin generator decide team pairings? Skill balance? Player preferences? Rotation algorithm?

6. **Check-In Feature Details:** What is the exact check-in UI/flow? Before event? During? After? Is it mobile/web only?

7. **Notification Customization:** Can players/organizers customize notification preferences per activity? Global muting options?

8. **Score Update Permissions:** After a match, who can edit scores? Can scores be contested? Is there an approval workflow?

9. **Mobile App Screenshots:** The actual UI/flow would be best understood via app store screenshots or in-app testing.

10. **Competitor Analysis:** How do round-robin opponents get selected in the first place? Random? Skill-based? Balanced pairings?

11. **Draw Size Limitations:** Do round-robin and bracket tournaments have player count limits? What's the max recommended?

12. **Tournament Payouts/Prizes:** Does ReClub track prize pools, payouts, or rankings for official tournaments?

---

## 10. RECOMMENDATIONS FOR IMPLEMENTATION

### For Your Pickleball Platform

**Feature Priority (based on ReClub's success):**
1. **Recurring Schedule automation** - High ROI, reduces organizer workload significantly
2. **One-off meet creation** - Table stakes, quick implementation
3. **Round-robin competition** - Most popular format, feature-complete
4. **RSVP + waitlist** - Core engagement mechanic
5. **Live standings** - Drives engagement and competition
6. **DUPR integration** - Credibility and user acquisition (but backend heavy)

**UI/UX Lessons:**
- Mobile-first design with quick action flows (blue "+" icon)
- Skill-level visibility before RSVPing (builds confidence)
- Live standings during competitions (engagement hook)
- Role-based permissions (delegate scoring)
- One-tap actions (join, score, submit)

**Business Model Lessons:**
- Free base tier with optional premium (Supporter)
- Tier gates convenience features, not core functionality
- No direct payment processing (leave to external systems initially)
- Receipt tracking for transaction proof (useful for clubs with fees)

---

## Sources

- [ReClub Pickleball](https://pickleball.reclub.co/)
- [For Organizers — Reclub Pickleball](https://pickleball.reclub.co/for-organizers)
- [FAQ — Reclub Pickleball](https://pickleball.reclub.co/faq)
- [Best Pickleball App For Players — Reclub Pickleball](https://pickleball.reclub.co/for-players)
- [Automate Weekly Activities — Reclub Pickleball](https://pickleball.reclub.co/videos/v/automate-weekly-activities)
- [Create Casual Round Robins — Reclub Pickleball](https://pickleball.reclub.co/videos/v/create-casual-round-robins)
- [Join Pickleball Competition — Reclub Pickleball](https://pickleball.reclub.co/videos/v/join-pickleball-competition)
- [Pickleball Round Robin App — Reclub Pickleball](https://pickleball.reclub.co/videos/v/create-casual-round-robins)
- [Match History, Pairings & Stats — Reclub Pickleball](https://pickleball.reclub.co/videos/v/match-history-pairings-stats-features)
- [How do I invite members to my club? - Reclub Help Center](https://help.reclub.co/hc/reclub-help/articles/1765845171-how-do-i-invite-members-to-my-club)
- [How do I manage payment methods and receipts? - Reclub Help Center](https://help.reclub.co/hc/reclub-help/articles/1765846532-how-do-i-manage-payment-methods-and-receipts)
- [Reclub - Social Sports Nearby - Google Play Store](https://play.google.com/store/apps/details?id=co.reclub&hl=en_US)
- [Reclub - Social Sports Nearby - Apple App Store](https://apps.apple.com/us/app/reclub-social-sports-nearby/id1323924315)
- [DUPR & Reclub Join Forces — Official Partnership](https://pickleball.reclub.co/news/official-partnership-announcement-dupr-and-reclub-join-forces)
- [DUPR - The World's Most Accurate Pickleball Rating](https://www.dupr.com/)
