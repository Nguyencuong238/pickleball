# ReClub App Feature Analysis Report

**Date:** Feb 25, 2026 | **Research Scope:** ReClub Club Management & Social Features

---

## 1. ReClub Overview

ReClub is a free-to-use mobile app (iOS/Android) that powers sports communities globally, with focus on pickleball. Operates in 35+ countries, supporting 59+ sports. No web version yet; mobile-first platform. Free tier covers all major features; premium "ReClub Supporter" ($2-3/mo or $20-30/yr) adds ad-free + stats analytics.

---

## 2. Club Management Features

### 2.1 Club Creation & Membership
- **Instant creation**: No complex setup required
- **Member invitation**: Invite players directly to club
- **Centralized hub**: Single location for all club operations
- **Role-based access**: Not explicitly detailed in sources, but admin capabilities implied
- **Member tracking**: View members, manage roster

### 2.2 Club Activity Organization
- **One-off events**: Single-time meetups, socials, tournaments
- **Recurring activities**: Auto-create weekly/scheduled events with automatic member notifications
- **Activity types**:
  - Round robins (flexible formats: singles, rotating partners, set teams)
  - Leagues (multi-week competition)
  - Social drop-ins (casual play)
  - Tournaments (brackets, registration, scheduling)
- **Auto-notification**: Members notified automatically of recurring meets (eliminates manual group chat coordination)

### 2.3 Match & Court Management
- **Match generation**: Auto-generate matches after RSVPs collected
- **Court assignment**: Assign courts by skill level
- **Round robin features**:
  - Flexible tournament format selection
  - Automatic standings calculation
  - Simple score input UI
- **Score tracking**: Organizers/ref can input match scores post-play
- **RSVP system**:
  - Automatic waitlist management
  - Sort participants by skill level
  - Attendance confirmation (paid/checked-in flags)

### 2.4 Member Management
- **Payment tracking**: Mark players as paid/unpaid
- **Attendance tracking**: Check-in/check-out functionality
- **Skill-based organization**: Sort, filter, assign by rating level
- **Communication**: Centralized messaging with club members (no group chat fragmentation)
- **Team assignment**: Auto-assign or manually create teams for events

### 2.5 Tournament & League Management
- **Registration handling**: Streamline player signups
- **Bracket management**:
  - Round robin brackets
  - Pool play brackets
  - Single elimination brackets
- **Scheduling**: Auto-generate match schedules
- **Standings**: Real-time standings calculation/updates
- **Score submission**: Simplified interface vs. spreadsheets
- **Limitations**: Limited playoff editing after bracket starts

---

## 3. Social & Community Features

### 3.1 Player Discovery & Connection
- **Activity discovery**: Find socials, round robins, leagues, tournaments locally and worldwide
- **Search & filter**: By time, distance, location, sport (59 sports)
- **Club discovery**: Browse local clubs, view club profiles
- **Free agent matching**: Players without teams matched with other free agents

### 3.2 Social Engagement
- **Kudos system**: Players can give kudos/recognition to each other
- **Street Cred leaderboards**: Reputation/engagement rankings within club community
- **Player-to-player rankings**: See how you rank against specific opponents
- **Community leaderboards**: Global/club-wide rankings to drive engagement
- **Match history**: View past matches, statistics, pairings
- **Pairings analytics**: See who you play best with, play most often, play worst against

### 3.3 Communication
- **In-app chat**: Event-based chat for activity discussion
- **Event notifications**: Auto-notify on activity updates, RSVPs, schedule changes
- **Centralized updates**: Prevent message fragmentation across platforms
- **Note**: Some users report difficulties loading event chat pages (potential UX issue)

### 3.4 Content & Moments
- **Moments sharing**: Players can share victories, highlights, memories
- **Community content**: Built-in platform for sharing within club/global community

---

## 4. Organizer-Specific Features

### 4.1 Administrative Capabilities
- **One-click creation**: Round robins, leagues, tournaments in few taps
- **Batch operations**: Bulk invite members, bulk mark paid/checked-in
- **Attendance management**: Track who attended, who paid
- **Role-based permissions**: Grant ref/coach access for score submission

### 4.2 Data & Analytics
- **Match history**: View all club matches, results, scores
- **Standing generation**: Automatic calculation & display
- **Player statistics**: (Premium) detailed performance analytics
- **Bulk DUPR submission**: (see DUPR Integration section)

### 4.3 Time-Saving Automation
- **Recurring meet automation**: Weekly activities auto-created, members auto-invited
- **Eliminates**: Copy/paste group chats, manual scheduling, spreadsheet management
- **Auto-notifications**: Eliminates manual reminder messages
- **Time saving**: Estimated significant reduction in admin workload

---

## 5. DUPR Integration (Pickleball-Specific)

### 5.1 DUPR Connectivity
- **Player rating sync**: Connect player DUPR ratings to ReClub profiles
- **Skill verification**: Verify ratings for proper skill divisions
- **Rating-based organization**: Use ratings for team assignment, court allocation

### 5.2 Match Submission to DUPR
- **Bulk submission**: Submit multiple match results to DUPR in seconds
- **Process**:
  1. Generate matches in ReClub after RSVPs
  2. Input scores via "DUPR Manager" interface
  3. Bulk submit to DUPR with one action
- **Benefit**: Eliminates manual DUPR submission workflow, auto-updates player ratings

---

## 6. Booking & Payment Features

### 6.1 Booking/Reservation
- **Implicit booking**: Activity RSVP system serves as booking mechanism
- **Waitlist management**: Auto-managed when event full
- **Check-in system**: Confirm attendance on day of event

### 6.2 Payment Handling
- **Payment flags**: Mark players paid/unpaid for event
- **Receipts**: Available in premium ReClub Supporter tier
- **Limitation**: **No in-app payment processing yet** - payment collection still requires external methods
- **Club responsibility**: Clubs must handle payment collection separately (cash, Venmo, etc.)

---

## 7. Club Discovery Features

### 7.1 Discovery Interface
- **Browse clubs**: View list of clubs by sport/location
- **Club profiles**: View club details, upcoming events, member count
- **Example clubs shown**: Vietnam Sports Social Club, Bay Area Open Gym, Plug and Play Badminton
- **Search capability**: Search clubs by name/location

### 7.2 Activity Discovery
- **Global activity feed**: See all activities worldwide
- **Advanced filtering**:
  - By sport (59 sports supported)
  - By time (upcoming events)
  - By distance (location-based)
  - By format (round robin, league, casual)
- **Activity details**: View roster, skill levels, location, time, format

---

## 8. Current Limitations & Gaps

### 8.1 Known Limitations
- **No web access**: Mobile-only (web planned but not yet launched)
- **No in-app payments**: Payment must be collected externally
- **No waiver system**: Legal waivers must be handled separately
- **Limited playoff editing**: Once tournament bracket starts, limited ability to modify
- **Chat loading issues**: Some users report slow/problematic event chat page loading
- **Incomplete functionality**: Some users note room for improvement in overall functionality

### 8.2 What's NOT Included
- Court/venue booking system (no facility integration)
- Professional coaching tools or lesson scheduling
- Video tutorials or training content library
- Advanced analytics (except in premium tier)
- Integration with payment processors
- Calendar sync with external calendars
- API for third-party integrations (not mentioned)

---

## 9. Comparative Analysis vs. Expected Club Platform

### Features ReClub Excels At
✓ Match generation & round robin automation
✓ DUPR rating integration (pickleball-specific)
✓ Recurring event automation
✓ Social engagement (kudos, leaderboards, moments)
✓ Simple UI for organizers (low barrier to entry)
✓ Community-driven features (player matching, discovery)
✓ Mobile-first, no setup friction

### Gaps for Full Club Management Platform
✗ Payment processing (in-app checkout needed)
✗ Court/facility booking (if multiple venues)
✗ Waiver/liability management
✗ Coaching/lesson scheduling
✗ Advanced member profiles (certification, history)
✗ Financial reporting (revenue tracking)
✗ Scheduling beyond events (instructor availability, court maintenance)
✗ Staff management (coaches, referees, instructors)
✗ Web dashboard for organizers

---

## 10. Architecture Observations

### 10.1 Platform Design
- **Modular features**: Clubs, meets, competitions, leaderboards are separate systems
- **Sports-agnostic**: 59 sports supported with common infrastructure
- **Community-centric**: Social engagement deeply integrated (not bolted-on)
- **Mobile prioritization**: App-first design, web planned later

### 10.2 Data Model Implications
- Clubs likely have: members, activities, roles, statistics
- Activities linked to: participants, matches, scores, DUPR submissions
- Players: profiles, ratings (DUPR sync), match history, statistics, social metrics (kudos, street cred)
- Matches: results, pairings, participants, scores, timestamps

---

## 11. Key Insights for Your Platform

**ReClub Strengths to Match:**
1. Zero friction club creation & invitation
2. Automation of recurring activities (eliminates manual scheduling)
3. Social engagement as core feature (not afterthought)
4. DUPR integration for rating management
5. Simple, intuitive UI for organizers

**Unique Opportunities (ReClub Gaps):**
1. **In-app payment processing** - ReClub hasn't solved this; opportunity for differentiation
2. **Facility/court booking system** - For clubs managing multiple courts
3. **Staff/instructor management** - Schedule coaches, track teaching, certification
4. **Financial dashboards** - Revenue tracking, expense management, fee collection
5. **Advanced member profiles** - Certifications, coaching badges, experience history
6. **Legal/waiver management** - Built-in liability waiver system
7. **Web dashboard** - Organizers need desktop access for complex scheduling/reporting
8. **Calendar sync** - Export to Google Calendar, Outlook, etc.

---

## 12. Unresolved Questions

1. Does ReClub have admin roles with permission levels, or just single club owner?
2. How does ReClub handle venue/court booking conflicts (if club has multiple courts)?
3. What is the member invite/approval workflow (instant join vs. admin approval)?
4. How are DUPR ratings kept in sync (real-time vs. periodic sync)?
5. Does ReClub support multiple organizers per club, or single owner?
6. Are there any integrations with court booking systems (e.g., TeamSnap, GameChanger)?
7. Does ReClub have an API for third-party integrations?
8. What analytics are available to club organizers (member growth, retention, etc.)?
9. How does ReClub handle time zones for recurring events and international clubs?
10. Is there a mobile web PWA alternative, or strictly native apps?

---

## Summary

ReClub is a **community-first, mobile-first** sports platform specializing in activity organization, social engagement, and (for pickleball) DUPR rating management. It excels at **automating recurring events** and **fostering community engagement** but lacks payment processing, facility booking, and web access. The platform fills the gap between simple group chat coordination and complex facility management systems, making it ideal for casual-to-serious club organizers who prioritize player engagement over infrastructure.

For a feature-rich club management platform, ReClub provides excellent UX patterns (auto-generation, recurring automation, social engagement) while revealing market gaps in payments, facility management, and staff coordination.

