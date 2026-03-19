# Phase 4: Score Entry + Auto-Advancement

## Context Links
- [MatchScoreTrait](/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/Tournament/Traits/MatchScoreTrait.php) -- hook point
- [KnockoutBracketService](phase-01-backend-service.md) -- advanceWinner method
- [TournamentMatchService](/Users/thaopv/Desktop/php/pickleball/app/Services/Tournament/TournamentMatchService.php) -- handleEndMatch

## Overview
- **Priority**: P1
- **Status**: completed
- **Description**: Hook match completion into bracket advancement. When a knockout match completes, auto-place winner in next match. For semifinal losses with third-place enabled, place losers in bronze match.

## Requirements
- R1: When knockout match completes (has winner_id + next_match_id), auto-advance winner
- R2: When semifinal match completes + third_place enabled, place loser in bronze match
- R3: Inline score entry on bracket view (expand match card, enter set scores, save)
- R4: Real-time bracket update after score save (re-fetch bracket data via Alpine.js)
- R5: Update Round completed_matches count after each match completion

## Architecture

### Auto-Advancement Hook
The cleanest integration point is `MatchScoreTrait::processScoreUpdate()`. After saving score + determining winner, check if match has `next_match_id` and call `advanceWinner()`.

**Alternative considered**: Event/listener pattern. Rejected -- adds complexity for single use case, YAGNI.

### Third-Place Loser Routing
When a semifinal match completes:
1. Check if tournament has `enable_third_place`
2. Find bronze round match for this category
3. Place loser in appropriate athlete slot (first semifinal loser → athlete1, second → athlete2)

### Score Entry UI
Reuse existing score entry pattern from `_matches-row.blade.php` -- inline expandable card with set score inputs. Add to bracket match cards.

## Related Code Files

### Files to Modify
- `app/Http/Controllers/Front/Tournament/Traits/MatchScoreTrait.php` -- add auto-advance call after score save
- `public/assets/js/bracket-manager.js` -- add score entry + save + re-fetch
- `resources/views/home-yard/tournaments/partials/_bracket-match.blade.php` -- add expandable score form

### Files to Create
- `app/Http/Controllers/Front/Tournament/Traits/BracketAdvancementTrait.php` (~60 LOC)

### Reference Files
- `app/Services/Tournament/KnockoutBracketService.php` -- advanceWinner()
- `resources/views/home-yard/tournaments/partials/_matches-row.blade.php` -- score UI pattern

## Implementation Steps

### Step 1: BracketAdvancementTrait (~60 LOC)
```php
// app/Http/Controllers/Front/Tournament/Traits/BracketAdvancementTrait.php
namespace App\Http\Controllers\Front\Tournament\Traits;

use App\Models\MatchModel;
use App\Models\Round;
use App\Services\Tournament\KnockoutBracketService;

trait BracketAdvancementTrait
{
    /**
     * Xu ly thang thua sau khi tran knockout ket thuc.
     */
    protected function handleBracketAdvancement(MatchModel $match): void
    {
        if (!$match->winner_id || !$match->next_match_id) return;

        $bracketService = app(KnockoutBracketService::class);
        $bracketService->advanceWinner($match, $match->winner_id);

        // Update round completed_matches
        $round = $match->round;
        if ($round) {
            $round->increment('completed_matches');
            $completed = $round->matches()->where('status', 'completed')->count();
            if ($completed >= $round->total_matches) {
                $round->update(['status' => 'completed']);
            }
        }

        // Third-place: route semifinal losers to bronze match
        $this->handleThirdPlaceRouting($match);
    }

    protected function handleThirdPlaceRouting(MatchModel $match): void
    {
        $round = $match->round;
        if (!$round || $round->round_type !== 'semifinal') return;

        $tournament = $match->tournament;
        if (!$tournament->enable_third_place) return;

        $loserId = $match->loser_id;
        if (!$loserId) return;

        // Find bronze match for this category
        $bronzeRound = Round::where('tournament_id', $tournament->id)
            ->where('category_id', $match->category_id)
            ->where('round_type', 'bronze')
            ->first();

        if (!$bronzeRound) return;

        $bronzeMatch = MatchModel::where('round_id', $bronzeRound->id)->first();
        if (!$bronzeMatch) return;

        // Place loser: first semifinal fills athlete1, second fills athlete2
        if (!$bronzeMatch->athlete1_id) {
            $bronzeMatch->update(['athlete1_id' => $loserId]);
        } elseif (!$bronzeMatch->athlete2_id) {
            $bronzeMatch->update(['athlete2_id' => $loserId]);
        }
    }
}
```

### Step 2: Hook into MatchScoreTrait
Add `use BracketAdvancementTrait;` to TournamentMatchController and call after score save.

In `MatchScoreTrait::processScoreUpdate()`, after line 87 (after standings update block), add:
```php
// Auto-advance winner in knockout bracket
if ($match->next_match_id && $match->status === 'completed' && $match->winner_id) {
    if (method_exists($this, 'handleBracketAdvancement')) {
        $this->handleBracketAdvancement($match);
    }
}
```

### Step 3: Add score entry to bracket match card
Extend `_bracket-match.blade.php` with expandable score form:
```blade
{{-- Score entry (click to expand) --}}
<template x-if="match.status !== 'completed' && match.status !== 'bye' && match.athlete1_id && match.athlete2_id">
    <div>
        <button class="bracket-score-btn" @click="$dispatch('open-score', { matchId: match.id })">
            Nhap ti so
        </button>
    </div>
</template>
```

### Step 4: Score entry Alpine.js logic in bracket-manager.js
Add methods:
```javascript
// Add to bracketManager
scoreMatchId: null,
scoreSets: [{ s1: 0, s2: 0 }],
scoreSaving: false,

openScore(matchId) {
    this.scoreMatchId = matchId;
    this.scoreSets = [{ s1: 0, s2: 0 }];
},

addSet() {
    if (this.scoreSets.length < 5) {
        this.scoreSets.push({ s1: 0, s2: 0 });
    }
},

removeSet() {
    if (this.scoreSets.length > 1) this.scoreSets.pop();
},

async saveScore() {
    this.scoreSaving = true;
    try {
        const url = `/tournament-manage/${config.tournamentSlug}/matches/${this.scoreMatchId}/score`;
        const res = await fetch(url, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrf },
            body: JSON.stringify({ sets: this.scoreSets, status: 'completed' }),
        });
        const json = await res.json();
        if (json.success) {
            this.scoreMatchId = null;
            await this.fetchBracket(); // Re-render bracket with advancement
        } else {
            alert(json.message || 'Luu ti so that bai');
        }
    } finally {
        this.scoreSaving = false;
    }
},
```

## Todo List
- [ ] Create BracketAdvancementTrait with handleBracketAdvancement + handleThirdPlaceRouting
- [ ] Add `use BracketAdvancementTrait` to TournamentMatchController
- [ ] Hook auto-advance call into MatchScoreTrait::processScoreUpdate
- [ ] Add score entry button to _bracket-match.blade.php
- [ ] Add inline score form (expandable) to bracket view
- [ ] Add score save + re-fetch logic to bracket-manager.js
- [ ] Update Round completed_matches after match completion
- [ ] Test: complete match → winner appears in next round match
- [ ] Test: complete both semifinals → losers appear in bronze match

## Success Criteria
1. Completing a knockout match auto-places winner in next match's correct slot (athlete1/athlete2)
2. Round status updates to 'completed' when all matches done
3. Semifinal losers auto-placed in bronze match when enable_third_place=true
4. Score entry form works inline on bracket view
5. Bracket re-renders after score save showing advancement

## Risk Assessment
| Risk | Impact | Mitigation |
|------|--------|------------|
| MatchScoreTrait used by multiple controllers | Medium | Use `method_exists` guard so non-bracket controllers unaffected |
| Race condition: two semifinals complete simultaneously | Low | DB::transaction in advanceWinner; bronze match uses separate athlete1/athlete2 columns |
| Score update on existing matches routes (not bracket route) | Low | Advancement hooks on trait level, works regardless of which controller calls it |
