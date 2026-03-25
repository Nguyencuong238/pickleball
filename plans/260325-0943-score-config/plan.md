# Plan: Configurable Score Settings for Open Play Activities

**Date:** 2026-03-25
**Branch:** `feat/score-config`
**Status:** Draft

## Summary

Currently score entry is hardcoded: 3 sets (best of 3), 21 points max per set. User wants:
1. Default changed to **1 set** (instead of 3)
2. Admin can **configure points per set** when creating/editing open_play activity

## Current State Analysis

| Component | File | Issue |
|-----------|------|-------|
| JS logic | `public/assets/js/club-activity-score.js` | Hardcoded 3 sets, max 21 points |
| Blade view | `resources/views/front/clubs/score-submit.blade.php` | Hardcoded `:disabled="set.team1 >= 21"` |
| Submit API | `ClubOpenPlayController::submitScore` | Validates `min:2|max:3` sets, `max:21` points |
| Activity model | `ClubActivity` | No `best_of` or `points_per_set` fields |
| Create form | `_open-play-fields.blade.php` | No score config fields |
| Edit form | `edit.blade.php` | Missing open play fields section |
| DB schema | `club_activities` table | No score config columns |

## Phases

| # | Phase | Status | Effort |
|---|-------|--------|--------|
| 1 | [Database migration](phase-01-migration.md) | Pending | S |
| 2 | [Model + Controller updates](phase-02-backend.md) | Pending | M |
| 3 | [Frontend: form + score entry](phase-03-frontend.md) | Pending | M |

## Key Decisions

- Store `best_of` (1 or 3) and `points_per_set` (11, 15, 21, or custom) on `club_activities` table
- Default: `best_of = 1`, `points_per_set = 21`
- Pass config from activity to score entry page via Blade variables
- JS reads config dynamically instead of hardcoded values
