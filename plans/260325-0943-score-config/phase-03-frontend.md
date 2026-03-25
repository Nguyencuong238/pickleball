# Phase 3: Frontend - Form + Score Entry

**Priority:** High | **Status:** Pending | **Effort:** M

## Overview

Add score config fields to activity create/edit forms. Update score entry page to use dynamic config.

## Implementation Steps

### 3.1 Open Play Fields Partial (`_open-play-fields.blade.php`)
Add 2 new fields:
- **So set (Best of):** Select with options 1, 3. Default 1.
- **Diem toi da moi set:** Select with options 11, 15, 21. Default 21.

### 3.2 Edit Page (`edit.blade.php`)
- Include `_open-play-fields.blade.php` for `open_play` type (currently missing)
- Show open play fields block when `$activity->type === 'open_play'`

### 3.3 Score Submit View (`score-submit.blade.php`)
- Pass `best_of` and `points_per_set` from activity to Alpine.js config
- Update `:disabled` bindings to use dynamic `pointsPerSet` instead of hardcoded 21

### 3.4 Score JS (`club-activity-score.js`)
- Accept `bestOf` and `pointsPerSet` in config param
- Dynamic `sets` array: generate based on `bestOf` (1 or 3 set objects)
- Replace hardcoded `21` with `config.pointsPerSet`
- Update `showSet3` logic: only relevant when `bestOf === 3`
- Update `winner` logic: for `bestOf === 1`, winner = set 1 winner; for `bestOf === 3`, best of 3
- Update `isValid` logic accordingly

## Related Files

- `resources/views/clubs/activities/partials/_open-play-fields.blade.php` (edit)
- `resources/views/clubs/activities/edit.blade.php` (edit)
- `resources/views/front/clubs/score-submit.blade.php` (edit)
- `public/assets/js/club-activity-score.js` (edit)

## Todo

- [ ] Add score config fields to _open-play-fields.blade.php
- [ ] Add open play fields to edit.blade.php
- [ ] Update score-submit.blade.php with dynamic config
- [ ] Update club-activity-score.js with dynamic logic
