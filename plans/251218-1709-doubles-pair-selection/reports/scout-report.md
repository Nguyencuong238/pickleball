# Scout Report: Doubles Pair Selection

**Date**: 2025-12-18
**Task**: Fix doubles match creation to support athlete pair selection

## Current Implementation Analysis

### Route: `/homeyard/tournaments/:id/config`

**File**: `resources/views/home-yard/config.blade.php`

### Match Creation Modal (lines 1012-1126)

Current implementation:
- Step 1: Select category (dropdown with all categories)
- Step 2: Select VDV 1 and VDV 2 (individual athlete dropdowns)
- Additional fields: Round, Group, Date/Time, Status, Referee

**Problem**: Same UI for all category types - no distinction between singles and doubles.

### JavaScript Handler (lines 1918-1980)

`handleCategoryChange()` fetches athletes from API:
```javascript
fetch(`/homeyard/tournaments/${tournamentId}/categories/${categoryId}/athletes`)
```

Response format:
```json
{"success": true, "athletes": [{"id": 1, "athlete_name": "..."}]}
```

**Problem**: Returns individual athletes, no pair concept.

### Backend Controller

**File**: `app/Http/Controllers/Front/HomeYardTournamentController.php`

`getCategoryAthletes()` (line 3296):
- Queries `tournament_athletes` table
- Filters by `tournament_id`, `category_id`, `status='approved'`
- Returns `id` and `athlete_name` only

`storeMatch()` (line 2332):
- Validates `athlete1_id` and `athlete2_id`
- Checks both belong to tournament and category
- No doubles-specific validation

### Database Schema

**tournament_categories** table:
```
category_type ENUM:
- single_men
- single_women
- double_men
- double_women
- double_mixed
```

**tournament_athletes** table:
- No `partner_id` field
- Each athlete registered individually

**matches** table:
- `athlete1_id` - first athlete/team
- `athlete2_id` - second athlete/team
- `athlete1_name`, `athlete2_name` - cached names

## Key Findings

1. **No partner concept**: `tournament_athletes` has no way to link doubles partners
2. **Category type unused**: `category_type` exists but not used in UI/API logic
3. **UI not adaptive**: Same form regardless of singles/doubles
4. **No validation**: Doubles matches don't validate partner existence

## Proposed Solution

Add `partner_id` to `tournament_athletes`:
- Links two athletes as a pair
- API returns pairs for doubles categories
- UI shows pair names in dropdown

## Files to Modify

| File | Changes |
|------|---------|
| `database/migrations/new` | Add `partner_id` to `tournament_athletes` |
| `app/Models/TournamentAthlete.php` | Add partner relationship |
| `app/Models/TournamentCategory.php` | Add `isDoubles()` helper |
| `app/Http/Controllers/Front/HomeYardTournamentController.php` | Modify `getCategoryAthletes()`, `storeMatch()` |
| `resources/views/home-yard/config.blade.php` | Update modal HTML and JS |
