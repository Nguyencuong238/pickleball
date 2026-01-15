# Gender-Aware Skill Level System

**Plan ID**: 260115-2019
**Created**: 2026-01-15
**Status**: Completed
**Priority**: High

## Overview

Implement gender-differentiated skill level mapping for Vietnam pickleball market alignment. Female players receive +0.5 level at same ELO (matching Vietnam tournament standard: Male amateur <4.0, Female <3.5).

## Current State

- `eloToSkillLevel()` returns range strings: "3.5 - 4.0"
- No gender field in User model
- Single ELO threshold table for all users

## Target State

- Clean single number display: "3.5"
- Gender-aware mapping: Female +0.5 at same ELO
- 8 levels: 2.0, 2.5, 3.0, 3.5, 4.0, 4.5, 5.0, 5.5+
- Vietnamese level names for UI

## Implementation Phases

| Phase | Name | Status | Progress |
|-------|------|--------|----------|
| 01 | [Database Migration](./phase-01-database-migration.md) | Completed | 100% |
| 02 | [Service Update](./phase-02-service-update.md) | Completed | 100% |
| 03 | [View & Test Updates](./phase-03-views-tests.md) | Completed | 100% |

## Files Affected

### Core Changes
- `database/migrations/2026_01_15_xxx_add_gender_to_users_table.php` (new)
- `app/Models/User.php`
- `app/Services/SkillQuizService.php`

### View Updates
- `resources/views/front/skill-quiz/result.blade.php`
- `resources/views/front/skill-quiz/index.blade.php`
- `resources/views/verifier/requests/show.blade.php`

### Test Updates
- `tests/Unit/Services/SkillQuizServiceTest.php`

## ELO → Skill Level Mapping

| ELO Range | Male | Female |
|-----------|------|--------|
| < 700 | 2.0 | 2.5 |
| 700-799 | 2.5 | 3.0 |
| 800-899 | 3.0 | 3.5 |
| 900-999 | 3.5 | 4.0 |
| 1000-1099 | 4.0 | 4.5 |
| 1100-1199 | 4.5 | 5.0 |
| 1200-1299 | 5.0 | 5.5 |
| >= 1300 | 5.5+ | 5.5+ |

## Level Names

| Level | EN | VN |
|-------|----|----|
| 2.0 | Beginner | Mới chơi |
| 2.5 | Novice | Tập sự |
| 3.0 | Intermediate | Sơ cấp |
| 3.5 | Upper Int. | Trung cấp |
| 4.0 | Advanced | Cao cấp |
| 4.5 | Semi-Pro | Bán chuyên |
| 5.0 | Pro | Chuyên nghiệp |
| 5.5+ | Elite | Đỉnh cao |

## Dependencies

- None (self-contained feature)

## Breaking Changes

**IMPORTANT**: New ELO thresholds differ from current system:

| ELO | Current Output | New Male | New Female |
|-----|----------------|----------|------------|
| 850 | "2.5" | "3.0" | "3.5" |
| 1000 | "2.8 - 3.0" | "3.5" | "4.0" |
| 1200 | "3.8 - 4.0" | "4.5" | "5.0" |

Users will see higher skill levels after update. This aligns with Vietnam tournament standards.

## Risk Assessment

- **Low**: Backward compatible (gender=null defaults to male)
- **Low**: Migration adds nullable column
- **Medium**: Existing users need gender data collection
- **Medium**: Skill level display changes (intentional, for VN market alignment)

## Related Documentation

- [Codebase Summary](../../docs/codebase-summary.md)
- [Code Standards](../../docs/code-standards.md)
