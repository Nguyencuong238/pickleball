# Phase 2: Backend - Model + Controller Updates

**Priority:** High | **Status:** Pending | **Effort:** M

## Overview

Add `best_of` and `points_per_set` to model fillable/casts, update validation in controllers, and make `submitScore` use dynamic config.

## Implementation Steps

### 2.1 ClubActivity Model
- Add `best_of`, `points_per_set` to `$fillable`
- Add casts: `'best_of' => 'integer'`, `'points_per_set' => 'integer'`

### 2.2 ClubActivityController (Web) - store()
- Add validation rules:
  - `'best_of' => 'nullable|integer|in:1,3'`
  - `'points_per_set' => 'nullable|integer|min:1|max:30'`

### 2.3 ClubActivityController (Web) - update()
- Same validation rules as store

### 2.4 ClubActivityController (API) - store() & update()
- Same validation rules

### 2.5 ClubOpenPlayController::submitScore()
- Read `$activity->best_of` and `$activity->points_per_set`
- Dynamic validation: `min:1|max:{best_of}` for set count, `max:{points_per_set}` for scores

### 2.6 ClubOpenPlayController::scoreForm()
- Pass `best_of` and `points_per_set` to view

## Related Files

- `app/Models/ClubActivity.php` (edit)
- `app/Http/Controllers/ClubActivityController.php` (edit)
- `app/Http/Controllers/Api/ClubActivityController.php` (edit)
- `app/Http/Controllers/ClubOpenPlayController.php` (edit)

## Todo

- [ ] Update ClubActivity model
- [ ] Update ClubActivityController store/update validation (web + API)
- [ ] Update ClubOpenPlayController submitScore dynamic validation
- [ ] Update ClubOpenPlayController scoreForm to pass config
