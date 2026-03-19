# Phase 6: Auto-Trigger Integration

## Context Links
- [TournamentStandingService](/Users/thaopv/Desktop/php/pickleball/app/Services/Tournament/TournamentStandingService.php) -- recalculateGroupRankings (marks is_advanced)
- [MatchScoreTrait](/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/Tournament/Traits/MatchScoreTrait.php) -- processScoreUpdate
- [Group model](/Users/thaopv/Desktop/php/pickleball/app/Models/Group.php) -- isCompleted()

## Overview
- **Priority**: P2
- **Status**: completed
- **Description**: Detect when ALL groups in a category complete their matches, then auto-generate knockout bracket (or notify admin). Wire into existing match completion flow.

## Requirements
- R1: After group stage match completes, check if ALL groups in that category are done
- R2: When all groups complete: if `auto_bracket_generation` is true, generate bracket automatically
- R3: If `auto_bracket_generation` is false, show notification/button prompting admin to generate
- R4: Update `tournament_stage` to 'finals' when bracket generated
- R5: Prevent duplicate bracket generation (idempotent check)

## Key Insights
<!-- Updated: Validation Session 1 - sidebar badge + toast notification -->
- **Sidebar badge**: When category groups complete, show red badge on "Bracket" sidebar item
- **Toast notification**: Show toastr success when last group match completes ("Vong bang da hoan tat!")

## Architecture

### Detection Point
After `recalculateGroupRankings()` in `MatchScoreTrait::processScoreUpdate()`, check category completion.

### Completion Check Logic
```
1. Match completes in group G, category C
2. Standings recalculated for group G
3. Check: are ALL groups in category C completed?
   - For each group in category: group.matches.where(status != completed).count() === 0
4. If yes + auto_bracket_generation: call KnockoutBracketService::generateBracket
5. If yes + !auto_bracket_generation: mark category as "ready for bracket"
```

### "Ready for bracket" indicator
Store in `bracket_data` JSON on Tournament model (already exists):
```json
{
  "category_42_ready": true,
  "category_42_generated": false
}
```

## Related Code Files

### Files to Modify
- `app/Http/Controllers/Front/Tournament/Traits/MatchScoreTrait.php` -- add category completion check
- `app/Services/Tournament/KnockoutBracketService.php` -- add `checkCategoryCompletion()` + `isBracketGenerated()` methods (~30 LOC)
- `resources/views/home-yard/tournaments/partials/_bracket-tree.blade.php` -- show "Groups complete, generate bracket" notification
- `public/assets/js/bracket-manager.js` -- add categoryReady state

## Implementation Steps

### Step 1: Add completion check methods to KnockoutBracketService
```php
public function checkCategoryCompletion(int $tournamentId, int $categoryId): bool
{
    $groups = Group::where('tournament_id', $tournamentId)
        ->where('category_id', $categoryId)
        ->get();

    if ($groups->isEmpty()) return false;

    foreach ($groups as $group) {
        $pending = $group->matches()
            ->whereNotIn('status', ['completed', 'bye'])
            ->count();
        if ($pending > 0) return false;
    }

    return true;
}

public function isBracketGenerated(int $tournamentId, int $categoryId): bool
{
    return Round::where('tournament_id', $tournamentId)
        ->where('category_id', $categoryId)
        ->whereIn('round_type', ['knockout', 'quarterfinal', 'semifinal', 'final'])
        ->exists();
}
```

### Step 2: Hook into MatchScoreTrait
After standings update block (~line 87), add:
```php
// Check category completion for auto-bracket
if ($match->group_id && $match->status === 'completed') {
    try {
        $bracketService = app(\App\Services\Tournament\KnockoutBracketService::class);
        $categoryId = $match->category_id;
        $tournamentId = $match->tournament_id;

        if ($bracketService->checkCategoryCompletion($tournamentId, $categoryId)
            && !$bracketService->isBracketGenerated($tournamentId, $categoryId)) {

            $tournament = $match->tournament;
            if ($tournament->auto_bracket_generation) {
                $bracketService->generateBracket(
                    $tournament,
                    $categoryId,
                    (bool) $tournament->enable_third_place
                );
                // Update stage
                $tournament->update(['tournament_stage' => 'finals']);
            }
            // Mark category ready in bracket_data
            $bracketData = $tournament->bracket_data ?? [];
            $bracketData["category_{$categoryId}_ready"] = true;
            $tournament->update(['bracket_data' => $bracketData]);
        }
    } catch (\Exception $e) {
        \Log::warning('Auto bracket check failed: ' . $e->getMessage());
    }
}
```

### Step 3: Update bracket UI to show readiness
In bracket-manager.js `fetchBracket()`, also check readiness:
```javascript
// After fetching bracket data, if empty, check if category is ready
if (!this.hasBracket) {
    const tournament = this.tournamentData; // pass from blade
    const key = `category_${this.activeCategoryId}_ready`;
    this.categoryReady = tournament?.bracket_data?.[key] ?? false;
}
```

In _bracket-tree.blade.php, update the "no bracket" section:
```blade
<template x-if="activeCategoryId && !hasBracket && !loading">
    <div class="td-card" style="text-align:center;padding:40px;">
        <template x-if="categoryReady">
            <div>
                <p style="color:#059669;font-weight:600;margin-bottom:8px;">
                    Vong bang da hoan tat! San sang tao bracket.
                </p>
            </div>
        </template>
        <template x-if="!categoryReady">
            <p style="color:#64748b;margin-bottom:16px;">
                Vong bang chua hoan tat. Hoan thanh tat ca tran dau vong bang truoc.
            </p>
        </template>
        <!-- generate button shown regardless, admin can force-generate -->
        <button class="td-btn td-btn-primary" @click="generateBracket()">Tao Bracket</button>
    </div>
</template>
```

## Todo List
- [ ] Add checkCategoryCompletion() to KnockoutBracketService
- [ ] Add isBracketGenerated() to KnockoutBracketService
- [ ] Hook category completion check into MatchScoreTrait after standings update
- [ ] Auto-generate bracket when auto_bracket_generation=true + all groups done
- [ ] Store category readiness in bracket_data JSON
- [ ] Update bracket UI to show "groups complete" notification
- [ ] Allow manual generate even if groups not complete (admin override)
- [ ] Test: complete last group match → bracket auto-generates (when setting enabled)
- [ ] Test: complete last group match → readiness indicator shown (when setting disabled)

## Success Criteria
1. Completing last group stage match in a category triggers completion check
2. With auto_bracket_generation=true: bracket auto-generated, tournament_stage='finals'
3. With auto_bracket_generation=false: bracket_data updated, UI shows readiness prompt
4. No duplicate bracket generation on repeated calls
5. Admin can manually generate bracket at any time (override)

## Risk Assessment
| Risk | Impact | Mitigation |
|------|--------|------------|
| Performance: checking all groups on every match complete | Low | Only runs when match has group_id; groups per category typically 2-8 |
| Auto-generation while admin is editing groups | Medium | isBracketGenerated() prevents duplicates; admin can regenerate |
| bracket_data JSON concurrent writes | Low | Single field update, last-write-wins acceptable |

## Security Considerations
- Auto-generation respects tournament ownership (runs in context of authenticated match update)
- Manual generate still gated by authorizeOwner in controller
