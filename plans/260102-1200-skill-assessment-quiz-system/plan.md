# Skill Assessment Quiz System - Implementation Plan

**Date**: 2026-01-02
**Status**: COMPLETED (All 6 Phases Done)
**Completed**: 2026-01-03
**Priority**: High

## Overview

Upgrade quiz system from simple knowledge test to comprehensive skill assessment quiz that calculates initial ELO/OPRS rating for Pickleball players. 36 questions across 6 domains, with anti-fraud measures and time validation.

## Current State Analysis

### Existing Quiz System
- Simple multiple choice (a/b/c/d) format
- Single `quizzes` table with `correct_answer` field
- No domain structure, no ELO calculation
- Frontend: category filter, question count, score percentage

### Existing OPRS System
- Formula: `OPRS = 0.7×Elo + 0.2×Challenge + 0.1×Community`
- 7 OPR levels (1.0-5.0+)
- `OprsService` handles calculation and history
- User fields: `elo_rating`, `total_oprs`, `opr_level`

## Key Changes

| Component | Current | New |
|-----------|---------|-----|
| Question Format | Multiple choice (a/b/c/d) | Self-assessment (0-3 scale) |
| Domains | Category (optional) | 6 fixed domains with weights |
| Scoring | Correct/Incorrect % | Weighted domain scores → ELO |
| Storage | Single quiz session | Persistent attempts with answers |
| Time Tracking | None | 3-20 min validation |
| Anti-fraud | None | Cross-validation, ELO caps |

## Implementation Phases

### Phase 1: Database Schema
File: `phase-01-database-schema.md`
- Create `skill_domains` table (6 domains)
- Create `skill_questions` table (36 questions)
- Create `skill_quiz_attempts` table
- Create `skill_quiz_answers` table
- Seed initial data

### Phase 2: Backend Services
File: `phase-02-backend-services.md`
- Create `SkillQuizService` for core logic
- Implement scoring algorithms
- Implement cross-validation logic
- Implement ELO caps
- Integrate with `OprsService`

### Phase 3: API Endpoints
File: `phase-03-api-endpoints.md`
- `POST /api/skill-quiz/start`
- `POST /api/skill-quiz/answer`
- `POST /api/skill-quiz/submit`
- `GET /api/skill-quiz/result/{attemptId}`
- `GET /api/skill-quiz/eligibility`

### Phase 4: Frontend Implementation
File: `phase-04-frontend-implementation.md`
- Quiz assessment page with timer
- Domain-based question display
- Answer scale (0-3) UI
- Result display with domain breakdown
- Re-quiz eligibility check

### Phase 5: Admin Panel
File: `phase-05-admin-panel.md`
- Quiz attempts management
- User skill assessment history
- Flag review interface
- Manual ELO adjustment

### Phase 6: Testing & Validation
File: `phase-06-testing-validation.md`
- Unit tests for scoring logic
- Integration tests for API
- Edge case testing
- Anti-fraud validation

## Architecture Decisions

### 1. Separate from existing Quiz
- Keep existing `quizzes` table for knowledge quiz
- New `skill_*` tables for assessment quiz
- Different purpose: knowledge vs skill level

### 2. ELO Integration
- Quiz calculates initial ELO only
- After quiz: `User.elo_rating = quiz_elo`
- OPRS auto-recalculates via existing `OprsService`
- `is_provisional = true` until 5+ matches played

### 3. Re-quiz Policy
- First time: immediate
- With flags: 7 days cooldown
- Normal: 30 days cooldown
- 20+ matches played: anytime

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| ELO inflation | High | Cross-validation + ELO caps |
| Too fast completion | Medium | Time validation flags |
| Cheating (shared answers) | Medium | Random question order |
| Poor UX | Low | Clear progress indicators |

## Dependencies

- `OprsService` for OPRS recalculation
- `EloService` patterns for consistency
- User model fields (existing)
- Auth middleware (existing)

## Success Criteria

- [ ] User can complete 36-question assessment
- [ ] ELO calculated correctly per spec
- [ ] Cross-validation flags detected
- [ ] Time validation working
- [ ] Re-quiz policy enforced
- [ ] OPRS auto-updates after quiz

## Unresolved Questions

None at this time.
