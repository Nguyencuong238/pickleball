# Phase 03: Backend API

**Parent Plan**: [plan.md](./plan.md)
**Dependencies**: [Phase 01 - Database Schema](./phase-01-database-schema.md), [Phase 02 - Registration Form](./phase-02-registration-form.md)
**Date**: 2025-12-18
**Priority**: High
**Implementation Status**: Pending
**Review Status**: Pending

## Overview

Modify backend API to support doubles pair selection:
1. Add `isDoubles()` helper to TournamentCategory model
2. Modify `getCategoryAthletes()` to return pairs for doubles categories
3. Update `storeMatch()` validation for doubles matches

## Key Insights

1. **Category types**: `double_men`, `double_women`, `double_mixed` are doubles
2. **API response**: For doubles, return athletes who have partners as selectable pairs
3. **Match creation**: In doubles, `athlete1_id` = team1 primary, `athlete2_id` = team2 primary
4. **Partner info**: Partners are stored via `partner_id` on `tournament_athletes`

## Requirements

### Functional
- `getCategoryAthletes()` returns different format based on category type:
  - Singles: List of individual athletes
  - Doubles: List of pairs (athletes with partners)
- `storeMatch()` validates pairs for doubles categories
- API response includes `category_type` for frontend to know format

### Non-Functional
- Backward compatible - singles categories unchanged
- Response format consistent for frontend consumption

## Architecture

### API Response Format

**Singles Category Response:**
```json
{
  "success": true,
  "category_type": "single_men",
  "is_doubles": false,
  "athletes": [
    {"id": 1, "athlete_name": "Nguyen Van A"},
    {"id": 2, "athlete_name": "Tran Van B"}
  ]
}
```

**Doubles Category Response:**
```json
{
  "success": true,
  "category_type": "double_men",
  "is_doubles": true,
  "pairs": [
    {
      "primary_athlete_id": 1,
      "partner_id": 2,
      "pair_name": "Nguyen Van A / Tran Van B"
    },
    {
      "primary_athlete_id": 3,
      "partner_id": 4,
      "pair_name": "Le Van C / Pham Van D"
    }
  ]
}
```

## Related Code Files

### Files to Modify

1. **`app/Models/TournamentCategory.php`** (line ~87)
   - Add `isDoubles()` method
   - Add `DOUBLES_TYPES` constant

2. **`app/Http/Controllers/Front/HomeYardTournamentController.php`**
   - `getCategoryAthletes()` (line 3296) - Return pairs for doubles
   - `storeMatch()` (line 2332) - Add validation for doubles pairs

## Implementation Steps

### Step 1: Update TournamentCategory Model

```php
// app/Models/TournamentCategory.php

/**
 * Doubles category types constant
 */
public const DOUBLES_TYPES = ['double_men', 'double_women', 'double_mixed'];

/**
 * Check if category is a doubles format.
 */
public function isDoubles(): bool
{
    return in_array($this->category_type, self::DOUBLES_TYPES);
}

/**
 * Check if category is singles format.
 */
public function isSingles(): bool
{
    return !$this->isDoubles();
}
```

### Step 2: Modify getCategoryAthletes()

Location: `app/Http/Controllers/Front/HomeYardTournamentController.php:3296`

```php
public function getCategoryAthletes(Tournament $tournament, $categoryId)
{
    try {
        $this->authorize('update', $tournament);

        // Get category to check type
        $category = TournamentCategory::findOrFail($categoryId);

        if ($category->isDoubles()) {
            // For doubles: return pairs (athletes with partners)
            $pairs = TournamentAthlete::where('tournament_id', $tournament->id)
                ->where('category_id', $categoryId)
                ->where('status', 'approved')
                ->whereNotNull('partner_id')
                ->with('partner:id,athlete_name')
                ->orderBy('athlete_name')
                ->get()
                ->map(function ($athlete) {
                    return [
                        'primary_athlete_id' => $athlete->id,
                        'partner_id' => $athlete->partner_id,
                        'pair_name' => $athlete->pair_name,
                    ];
                })
                // Filter to avoid duplicates (A/B and B/A)
                ->filter(function ($pair) {
                    return $pair['primary_athlete_id'] < $pair['partner_id'];
                })
                ->values();

            return response()->json([
                'success' => true,
                'category_type' => $category->category_type,
                'is_doubles' => true,
                'pairs' => $pairs
            ]);
        }

        // For singles: return individual athletes
        $athletes = TournamentAthlete::where('tournament_id', $tournament->id)
            ->where('category_id', $categoryId)
            ->where('status', 'approved')
            ->orderBy('athlete_name')
            ->get(['id', 'athlete_name']);

        return response()->json([
            'success' => true,
            'category_type' => $category->category_type,
            'is_doubles' => false,
            'athletes' => $athletes
        ]);
    } catch (\Exception $e) {
        Log::error('Get category athletes error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Loi: ' . $e->getMessage()
        ], 500);
    }
}
```

### Step 3: Update storeMatch() Validation

Location: `app/Http/Controllers/Front/HomeYardTournamentController.php:2332`

Add after existing validation (around line 2387):

```php
// Get category to check if doubles
$category = TournamentCategory::find($validated['category_id']);

if ($category && $category->isDoubles()) {
    // For doubles: validate that selected athletes have partners
    if (!$athlete1->hasPartner()) {
        return response()->json([
            'success' => false,
            'message' => 'VDV 1 chua co doi partner cho noi dung doi'
        ], 422);
    }

    if (!$athlete2->hasPartner()) {
        return response()->json([
            'success' => false,
            'message' => 'VDV 2 chua co doi partner cho noi dung doi'
        ], 422);
    }

    // Validate pairs don't overlap (athlete from pair1 shouldn't be in pair2)
    $pair1Ids = [$athlete1->id, $athlete1->partner_id];
    $pair2Ids = [$athlete2->id, $athlete2->partner_id];

    if (array_intersect($pair1Ids, $pair2Ids)) {
        return response()->json([
            'success' => false,
            'message' => 'Hai cap dau khong duoc co chung VDV'
        ], 422);
    }
}
```

### Step 4: Update Match Name Storage (Optional Enhancement)

For doubles matches, store pair names instead of individual names:

```php
// In storeMatch(), modify name retrieval:
if ($category && $category->isDoubles()) {
    $athlete1Name = $athlete1->pair_name;
    $athlete2Name = $athlete2->pair_name;
} else {
    $athlete1Name = $athlete1->athlete_name ?? ($athlete1->user ? $athlete1->user->name : 'Unknown');
    $athlete2Name = $athlete2->athlete_name ?? ($athlete2->user ? $athlete2->user->name : 'Unknown');
}
```

## Todo List

- [ ] Add `DOUBLES_TYPES` constant to TournamentCategory
- [ ] Add `isDoubles()` method to TournamentCategory
- [ ] Modify `getCategoryAthletes()` to return pairs for doubles
- [ ] Add doubles validation in `storeMatch()`
- [ ] Update match name storage for doubles
- [ ] Test API responses for both singles and doubles categories

## Success Criteria

1. `GET /homeyard/tournaments/{id}/categories/{categoryId}/athletes` returns:
   - `is_doubles: false` + `athletes` array for singles
   - `is_doubles: true` + `pairs` array for doubles
2. `POST /homeyard/tournaments/{id}/matches` validates:
   - Partners exist for doubles categories
   - No overlapping athletes between pairs
3. Match names show pair format for doubles (e.g., "A/B vs C/D")

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| No pairs registered yet | Medium | Show helpful message in UI |
| Unpaired athletes exist | Low | Filter them out from selection |

## Security Considerations

- Only tournament owner can access these endpoints (existing `authorize('update', $tournament)`)
- Validate category belongs to tournament
- Validate athletes belong to category

## Next Steps

After completing this phase:
1. Proceed to Phase 04: Match Creation UI
2. Update JavaScript to handle both response formats
