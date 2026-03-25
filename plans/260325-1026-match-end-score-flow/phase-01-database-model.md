# Phase 1: Database + Model Changes

**Status**: Complete

## Overview
Add `score_status` and `score_confirmed_by` columns to `club_activity_matches` table.

## Migration

**File**: `database/migrations/2026_03_25_add_score_status_to_club_activity_matches.php`

```php
// Add to club_activity_matches table
$table->string('score_status', 20)->nullable()->after('result_confirmed');
// Values: null (no score), 'pending_confirmation', 'confirmed', 'admin_confirmed', 'rejected'
$table->unsignedBigInteger('score_confirmed_by')->nullable()->after('score_status');
$table->foreign('score_confirmed_by')->references('id')->on('users')->nullOnDelete();
```

## Model Changes

**File**: `app/Models/ClubActivityMatch.php`
- Add `score_status`, `score_confirmed_by` to `$fillable`
- No new casts needed (string + integer)
- Add helper methods:

```php
public function isPendingConfirmation(): bool
{
    return $this->score_status === 'pending_confirmation';
}

public function isScoreConfirmed(): bool
{
    return in_array($this->score_status, ['confirmed', 'admin_confirmed']);
}

public function getOpposingTeamPlayerIds(int $submitterId): array
{
    $team1 = [$this->player1_id, $this->player2_id];
    $team2 = [$this->player3_id, $this->player4_id];
    if (in_array($submitterId, $team1)) {
        return array_filter($team2);
    }
    return array_filter($team1);
}
```

## Note on `result_confirmed` column
`result_confirmed` (boolean) overlaps with `score_status` (enum). Plan:
- Keep `result_confirmed` for backward compatibility with existing queries
- `score_status` is the source of truth going forward
- Both are set together to stay in sync (e.g., `score_status = 'confirmed'` + `result_confirmed = true`)
- Future cleanup: deprecate `result_confirmed` once all queries use `score_status`

## Success Criteria
- Migration runs without error
- Model fillable/methods updated
- Existing matches unaffected (null score_status = legacy behavior)
