# OPRS Implementation Planning Summary

**Date**: 2025-12-05
**Status**: Planning Complete
**Author**: Claude Code

## Executive Summary

Created comprehensive 9-phase implementation plan for OPRS (OnePickleball Rating Score) system. Plan extends existing OCR ranking system with three-component scoring: Elo (70%), Challenge (20%), Community (10%).

## Key Decisions

### Architecture Choice: Extend OCR System
- Reuse existing Elo infrastructure
- Add OPRS as wrapper around existing components
- Maintain backward compatibility
- Minimal disruption to current users

### OPRS Formula
```
OPRS = (0.7 * Elo) + (0.2 * Challenge) + (0.1 * Community)
```

### OPR Level Mapping
| Level | OPRS Range | Name |
|-------|------------|------|
| 1.0 | 0-599 | Beginner |
| 2.0 | 600-899 | Novice |
| 3.0 | 900-1099 | Intermediate |
| 3.5 | 1100-1349 | Upper Intermediate |
| 4.0 | 1350-1599 | Advanced |
| 4.5 | 1600-1849 | Pro |
| 5.0+ | 1850+ | Elite |

## Implementation Phases

1. **Database Schema** - New tables for challenges, activities, OPRS history
2. **Models** - ChallengeResult, CommunityActivity, OprsHistory models
3. **OprsService** - Core calculation logic and history tracking
4. **Challenge System** - 4 challenge types with pass/fail logic
5. **Community System** - 5 activity types with frequency limits
6. **API Endpoints** - Profile, leaderboard, challenge, community APIs
7. **Frontend Views** - Profile cards, challenge center, community hub
8. **Admin Panel** - Management dashboard, verification workflows
9. **Testing** - Unit, feature, integration tests

## New Components Summary

### Services
- `OprsService` - OPRS calculation, history, level management
- `ChallengeService` - Challenge submission, verification
- `CommunityService` - Activity tracking, bonus processing

### Models
- `ChallengeResult` - Challenge test results
- `CommunityActivity` - Community activity records
- `OprsHistory` - OPRS change audit trail

### Controllers
- `OprsController` - OPRS profile API
- `OprsLeaderboardController` - Leaderboard API
- `ChallengeController` - Challenge API
- `CommunityController` - Community API
- `Admin\OprsController` - Admin management
- `Admin\OprsChallengeController` - Challenge admin
- `Admin\OprsActivityController` - Activity admin

### Database Tables
- `challenge_results` - Challenge test records
- `community_activities` - Activity records
- `oprs_histories` - OPRS change history
- Users table extended with OPRS fields

## Dependencies on Existing Code

- EloService - Trigger OPRS recalc after match
- User model - Add OPRS fields and relationships
- OcrMatch model - Existing match tracking
- Stadium model - Check-in references
- Social model - Event participation

## Estimated Files

| Category | Count |
|----------|-------|
| Migrations | 5 |
| Models | 3 |
| Services | 3 |
| Controllers | 7 |
| Blade Views | 15+ |
| Tests | 6+ |

## Risk Summary

| Risk | Level | Mitigation |
|------|-------|------------|
| OPRS formula mismatch | Medium | Unit tests verify spec |
| Performance on leaderboard | Medium | Indexed queries, caching |
| Challenge verification | Medium | Admin workflow |
| Point manipulation | High | Server-side validation |

## Integration Points

1. **EloService** - Call OprsService after Elo update
2. **Scheduled Job** - Weekly match bonus processing
3. **Existing Views** - Add OPRS to OCR profile/leaderboard

## Unresolved Questions

1. Cache strategy for leaderboard performance?
2. Load testing for concurrent submissions?
3. Notification system for level changes?

## Next Actions

1. Review plan with stakeholder
2. Begin Phase 1 (Database Schema)
3. Sequential implementation through phases
4. Testing at each phase completion

---

*Plan created by Claude Code on 2025-12-05*
