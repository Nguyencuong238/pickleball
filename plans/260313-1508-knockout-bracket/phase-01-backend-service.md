# Phase 1: Migration + KnockoutBracketService

## Context Links
- [Codebase exploration report](/Users/thaopv/Desktop/php/pickleball/plans/reports/Explore-260313-1506-bracket-knockout-code.md)
- [ClubCompetitionService reference](/Users/thaopv/Desktop/php/pickleball/app/Services/ClubCompetitionService.php) (generateSingleElimination pattern)
- [TournamentStandingService](/Users/thaopv/Desktop/php/pickleball/app/Services/Tournament/TournamentStandingService.php) (recalculateGroupRankings, is_advanced logic)

## Overview
- **Priority**: P1 -- foundational backend, all other phases depend on it
- **Status**: completed
- **Description**: Create migration for `enable_third_place` + new `KnockoutBracketService` with bracket generation, seeding, bye handling, and winner advancement logic

## Key Insights
<!-- Updated: Validation Session 1 - doubles support + Vietnamese diacritics -->
- **Doubles support required**: collectSeededAthletes() must handle doubles pairs via partner_id. Display both athlete names in bracket.
- **Vietnamese diacritics**: ALL Vietnamese text in code (comments, strings, error messages) must have proper diacritics.
- `matches` table already has `next_match_id`, `winner_advances_to`, `bracket_position` columns
- `rounds` table round_type enum already includes: knockout, quarterfinal, semifinal, final, bronze
- `tournaments` table already has `format_type` (group_knockout), `bracket_data` JSON, `seeding_enabled`, `auto_bracket_generation`
- Only missing: `enable_third_place` boolean on tournaments table
- ClubCompetitionService::generateSingleElimination() has power-of-2 padding + bye auto-completion pattern to reuse
- `bracket_position` uses integer encoding: 1=final, 2-3=semifinal, 4-7=quarterfinal, 8-15=round_of_16, etc (heap-style binary tree indexing)

## Requirements

### Functional
- F1: Generate bracket from advanced athletes across all groups in a category
- F2: Seed athletes by group rank (1st seeds distributed, 2nd seeds distributed, etc)
- F3: Pad bracket to next power-of-2 with byes; auto-complete bye matches
- F4: Create Round records per knockout round (with correct round_type)
- F5: Wire `next_match_id` + `winner_advances_to` for full bracket tree
- F6: Optional third-place match between semifinal losers
- F7: Advance winner to next match when match completes
- F8: Handle category independently (each TournamentCategory gets own bracket)

### Non-Functional
- NF1: DB::transaction for all bracket generation
- NF2: Service under 200 LOC -- split helpers if needed
- NF3: Idempotent: regenerating bracket for same category replaces old knockout rounds/matches

## Architecture

### Bracket Position Encoding (Heap-style)
```
Position 1 = Final
Position 2, 3 = Semifinal
Position 4, 5, 6, 7 = Quarterfinal
Position 8-15 = Round of 16
...
Parent of position N = floor(N/2)
Children of position N = 2N and 2N+1
```

### Seeding Algorithm
Standard single-elimination seeding to avoid top seeds meeting early:
```
For 8 athletes: [1,8,5,4,3,6,7,2]
Seed placement formula for bracket size S:
  Position 1 → slot 0
  Position 2 → slot S-1
  Then recursively fill remaining positions
```

Simplified approach: use standard seeding map arrays for common sizes (4,8,16,32).

### Round Type Mapping
```php
$totalRounds = log2($bracketSize);
// Round number (from first round) → round_type mapping:
// If totalRounds=3 (8 athletes): round1=quarterfinal, round2=semifinal, round3=final
// If totalRounds=4 (16 athletes): round1=round_of_16, round2=quarterfinal, round3=semifinal, round4=final
```

### Data Flow
```
Group::advancedAthletes() per group in category
    → Collect all advanced athletes with rank_position
    → Sort/seed by rank across groups
    → Pad to power-of-2
    → Create Round records (one per knockout round)
    → Create MatchModel records with bracket_position, next_match_id, winner_advances_to
    → Auto-complete bye matches
    → (Optional) Create third-place Round + Match
```

## Related Code Files

### Files to Create
- `app/Services/Tournament/KnockoutBracketService.php` -- main service
- `app/Services/Tournament/BracketSeedingHelper.php` -- seeding logic (split for 200 LOC limit)
- `database/migrations/2026_03_13_add_enable_third_place_to_tournaments_table.php`

### Files to Modify
- None in this phase (integration in phase 4+6)

### Reference Files (read-only)
- `app/Services/ClubCompetitionService.php:156-221` -- pattern reference
- `app/Services/Tournament/TournamentStandingService.php:133-160` -- recalculateGroupRankings
- `app/Models/Group.php:108-114` -- advancedAthletes()
- `app/Models/MatchModel.php` -- fillable fields, nextMatch(), end()
- `app/Models/Round.php` -- fillable fields

## Implementation Steps

### Step 1: Migration
```php
// database/migrations/2026_03_13_add_enable_third_place_to_tournaments_table.php
Schema::table('tournaments', function (Blueprint $table) {
    $table->boolean('enable_third_place')->default(false)->after('auto_bracket_generation');
});
```

### Step 2: BracketSeedingHelper (~80 LOC)
```php
// app/Services/Tournament/BracketSeedingHelper.php
class BracketSeedingHelper
{
    /**
     * Thu thap VDV da vuot qua vong bang theo category, sap xep theo seed.
     * Seed logic: Group 1st places get top seeds, 2nd places get next seeds, etc.
     * Cross-group seeding: rank by group standing stats (points, game diff).
     */
    public function collectSeededAthletes(int $tournamentId, int $categoryId): array
    {
        // 1. Get all groups for this category
        // 2. For each group, get advancedAthletes() with standings
        // 3. Group by rank_position (all 1st-place, all 2nd-place, etc)
        // 4. Within same rank, sort by points DESC, games_differential DESC
        // 5. Assign seed numbers 1..N
        // Return: [['athlete_id' => X, 'seed' => 1, 'name' => '...'], ...]
    }

    /**
     * Sap xep seed vao bracket theo standard single-elimination placement.
     * Ensures top seeds don't meet until later rounds.
     */
    public function arrangeSeedsIntoBracket(array $seededAthletes, int $bracketSize): array
    {
        // Standard seeding: [1, bracketSize, ...] pattern
        // Returns ordered array of athlete_ids (null for byes)
    }

    /**
     * Pad to next power of 2.
     */
    public function calculateBracketSize(int $athleteCount): int
    {
        $size = 1;
        while ($size < $athleteCount) {
            $size *= 2;
        }
        return $size;
    }

    /**
     * Map round number to round_type based on total rounds.
     */
    public function getRoundType(int $roundFromFinal, int $totalRounds): string
    {
        // roundFromFinal: 1=final, 2=semi, 3=quarter, etc.
        return match($roundFromFinal) {
            1 => 'final',
            2 => 'semifinal',
            3 => 'quarterfinal',
            default => 'knockout', // round_of_16 etc mapped to 'knockout'
        };
    }

    /**
     * Map round_type to Vietnamese display name.
     */
    public function getRoundName(string $roundType, int $roundFromFinal, int $totalRounds): string
    {
        return match($roundType) {
            'final' => 'Chung ket',
            'semifinal' => 'Ban ket',
            'quarterfinal' => 'Tu ket',
            'knockout' => 'Vong 1/' . pow(2, $roundFromFinal),
            'bronze' => 'Tranh hang ba',
            default => 'Vong knockout',
        };
    }
}
```

### Step 3: KnockoutBracketService (~150 LOC)
```php
// app/Services/Tournament/KnockoutBracketService.php
class KnockoutBracketService
{
    public function __construct(
        private BracketSeedingHelper $seedingHelper
    ) {}

    /**
     * Tao bracket loai truc tiep cho 1 category.
     */
    public function generateBracket(
        Tournament $tournament,
        int $categoryId,
        bool $enableThirdPlace = false
    ): void {
        DB::transaction(function () use ($tournament, $categoryId, $enableThirdPlace) {
            // 1. Clear existing knockout rounds/matches for this category
            $this->clearExistingBracket($tournament->id, $categoryId);

            // 2. Collect seeded athletes
            $seeded = $this->seedingHelper->collectSeededAthletes($tournament->id, $categoryId);
            if (count($seeded) < 2) {
                throw new InvalidArgumentException('Can toi thieu 2 VDV de tao bracket.');
            }

            // 3. Calculate bracket size & arrange
            $bracketSize = $this->seedingHelper->calculateBracketSize(count($seeded));
            $slots = $this->seedingHelper->arrangeSeedsIntoBracket($seeded, $bracketSize);

            // 4. Determine rounds
            $totalRounds = (int) log2($bracketSize);

            // 5. Create rounds (from first round to final)
            $rounds = $this->createRounds($tournament, $categoryId, $totalRounds);

            // 6. Create matches with bracket tree wiring
            $this->createMatches($tournament, $categoryId, $slots, $rounds, $totalRounds);

            // 7. Optional: third-place match
            if ($enableThirdPlace && $totalRounds >= 2) {
                $this->createThirdPlaceMatch($tournament, $categoryId, $rounds);
            }
        });
    }

    private function clearExistingBracket(int $tournamentId, int $categoryId): void
    {
        // Delete matches in knockout rounds for this category
        $knockoutRoundIds = Round::where('tournament_id', $tournamentId)
            ->where('category_id', $categoryId)
            ->whereIn('round_type', ['knockout', 'quarterfinal', 'semifinal', 'final', 'bronze'])
            ->pluck('id');

        MatchModel::whereIn('round_id', $knockoutRoundIds)->delete();
        Round::whereIn('id', $knockoutRoundIds)->delete();
    }

    private function createRounds(Tournament $tournament, int $categoryId, int $totalRounds): array
    {
        $rounds = [];
        for ($r = $totalRounds; $r >= 1; $r--) {
            $roundType = $this->seedingHelper->getRoundType($r, $totalRounds);
            $roundName = $this->seedingHelper->getRoundName($roundType, $r, $totalRounds);
            $roundNumber = $totalRounds - $r + 1; // 1 = first round played

            $rounds[$r] = Round::create([
                'tournament_id' => $tournament->id,
                'category_id'   => $categoryId,
                'round_name'    => $roundName,
                'round_number'  => $roundNumber,
                'round_type'    => $roundType,
                'start_date'    => $tournament->start_date,
                'status'        => 'pending',
                'total_matches' => (int) pow(2, $r - 1),
            ]);
        }
        return $rounds; // keyed by roundFromFinal
    }

    /**
     * Create matches with bracket_position wiring.
     * bracket_position uses heap indexing: 1=final, 2-3=semi, etc.
     * First round positions: bracketSize/2 .. bracketSize-1
     */
    private function createMatches(
        Tournament $tournament,
        int $categoryId,
        array $slots,
        array $rounds,
        int $totalRounds
    ): void {
        $matchesByPosition = [];
        $firstRoundStart = (int) (count($slots) / 2); // e.g., 4 for 8-slot bracket

        // Create all matches from final backwards
        for ($roundFromFinal = $totalRounds; $roundFromFinal >= 1; $roundFromFinal--) {
            $matchesInRound = (int) pow(2, $roundFromFinal - 1);
            $positionStart = (int) pow(2, $roundFromFinal - 1); // heap positions for this round

            for ($m = 0; $m < $matchesInRound; $m++) {
                $bracketPos = $positionStart + $m;
                $round = $rounds[$roundFromFinal];

                // Determine next_match_id and winner_advances_to
                $nextMatchId = null;
                $advancesTo = null;
                if ($roundFromFinal > 1) {
                    $parentPos = intdiv($bracketPos, 2);
                    $nextMatchId = $matchesByPosition[$parentPos]->id ?? null;
                    $advancesTo = ($bracketPos % 2 === 0) ? 'athlete1' : 'athlete2';
                }

                // For first round, assign athletes from slots
                $athlete1Id = null;
                $athlete2Id = null;
                $status = 'scheduled';

                if ($roundFromFinal === $totalRounds) {
                    $slotIdx1 = $m * 2;
                    $slotIdx2 = $m * 2 + 1;
                    $athlete1Id = $slots[$slotIdx1] ?? null;
                    $athlete2Id = $slots[$slotIdx2] ?? null;
                }

                $match = MatchModel::create([
                    'tournament_id'      => $tournament->id,
                    'category_id'        => $categoryId,
                    'round_id'           => $round->id,
                    'bracket_position'   => $bracketPos,
                    'athlete1_id'        => $athlete1Id,
                    'athlete2_id'        => $athlete2Id,
                    'next_match_id'      => $nextMatchId,
                    'winner_advances_to' => $advancesTo,
                    'match_number'       => $bracketPos,
                    'status'             => $status,
                    'best_of'            => 3,
                ]);

                $matchesByPosition[$bracketPos] = $match;

                // Auto-complete bye matches
                if ($roundFromFinal === $totalRounds) {
                    $this->handleBye($match);
                }
            }
        }
    }

    private function handleBye(MatchModel $match): void
    {
        if ($match->athlete1_id && $match->athlete2_id) return;
        if (!$match->athlete1_id && !$match->athlete2_id) return;

        $winnerId = $match->athlete1_id ?? $match->athlete2_id;
        $match->update([
            'winner_id' => $winnerId,
            'status'    => 'bye',
        ]);

        // Advance winner to next match
        $this->advanceWinner($match, $winnerId);
    }

    /**
     * Chuyen nguoi thang vao tran tiep theo.
     */
    public function advanceWinner(MatchModel $match, int $winnerId): void
    {
        if (!$match->next_match_id || !$match->winner_advances_to) return;

        $nextMatch = MatchModel::find($match->next_match_id);
        if (!$nextMatch) return;

        $column = $match->winner_advances_to === 'athlete1' ? 'athlete1_id' : 'athlete2_id';
        $nextMatch->update([$column => $winnerId]);
    }

    private function createThirdPlaceMatch(Tournament $tournament, int $categoryId, array $rounds): void
    {
        $thirdPlaceRound = Round::create([
            'tournament_id' => $tournament->id,
            'category_id'   => $categoryId,
            'round_name'    => 'Tranh hang ba',
            'round_number'  => 99, // special
            'round_type'    => 'bronze',
            'start_date'    => $tournament->start_date,
            'status'        => 'pending',
            'total_matches' => 1,
        ]);

        MatchModel::create([
            'tournament_id'    => $tournament->id,
            'category_id'      => $categoryId,
            'round_id'         => $thirdPlaceRound->id,
            'bracket_position' => 0, // special: not part of main bracket tree
            'match_number'     => 0,
            'status'           => 'scheduled',
            'best_of'          => 3,
            'notes'            => 'third_place_match',
        ]);
        // Athletes assigned when semifinals complete (phase 4)
    }

    /**
     * Swap 2 athletes' bracket positions (pre-match only).
     */
    public function swapAthletes(int $matchId1, string $slot1, int $matchId2, string $slot2): void
    {
        DB::transaction(function () use ($matchId1, $slot1, $matchId2, $slot2) {
            $match1 = MatchModel::findOrFail($matchId1);
            $match2 = MatchModel::findOrFail($matchId2);

            // Only allow swaps on scheduled matches
            if ($match1->status !== 'scheduled' || $match2->status !== 'scheduled') {
                throw new InvalidArgumentException('Chi co the doi vi tri tran chua dien ra.');
            }

            $col1 = $slot1 === 'athlete1' ? 'athlete1_id' : 'athlete2_id';
            $col2 = $slot2 === 'athlete1' ? 'athlete1_id' : 'athlete2_id';

            $temp = $match1->$col1;
            $match1->update([$col1 => $match2->$col2]);
            $match2->update([$col2 => $temp]);
        });
    }

    /**
     * Get bracket data for display (all rounds + matches for a category).
     */
    public function getBracketData(int $tournamentId, int $categoryId): array
    {
        $rounds = Round::where('tournament_id', $tournamentId)
            ->where('category_id', $categoryId)
            ->whereIn('round_type', ['knockout', 'quarterfinal', 'semifinal', 'final', 'bronze'])
            ->with(['matches' => fn($q) => $q->with(['athlete1', 'athlete2', 'winner'])
                ->orderBy('bracket_position')])
            ->orderBy('round_number')
            ->get();

        return $rounds->map(fn($round) => [
            'id'          => $round->id,
            'round_name'  => $round->round_name,
            'round_type'  => $round->round_type,
            'round_number'=> $round->round_number,
            'status'      => $round->status,
            'matches'     => $round->matches->map(fn($m) => [
                'id'               => $m->id,
                'bracket_position' => $m->bracket_position,
                'athlete1_id'      => $m->athlete1_id,
                'athlete1_name'    => $m->athlete1?->athlete_name ?? 'TBD',
                'athlete2_id'      => $m->athlete2_id,
                'athlete2_name'    => $m->athlete2?->athlete_name ?? 'TBD',
                'winner_id'        => $m->winner_id,
                'status'           => $m->status,
                'final_score'      => $m->final_score,
                'set_scores'       => $m->set_scores ?? [],
                'next_match_id'    => $m->next_match_id,
            ]),
        ])->toArray();
    }
}
```

## Todo List

- [ ] Create migration: `enable_third_place` boolean on tournaments table
- [ ] Create `BracketSeedingHelper` with collectSeededAthletes, arrangeSeedsIntoBracket, calculateBracketSize, getRoundType, getRoundName
- [ ] Create `KnockoutBracketService` with generateBracket (main method)
- [ ] Implement clearExistingBracket (idempotent regeneration)
- [ ] Implement createRounds (correct round_type mapping)
- [ ] Implement createMatches with heap-style bracket_position + next_match_id wiring
- [ ] Implement handleBye (auto-advance single athlete)
- [ ] Implement advanceWinner (place winner in next match)
- [ ] Implement createThirdPlaceMatch
- [ ] Implement swapAthletes (pre-match position swap)
- [ ] Implement getBracketData (structured data for frontend)
- [ ] Run migration: `php artisan migrate`

## Success Criteria

1. Migration runs cleanly, `enable_third_place` column exists
2. `generateBracket()` creates correct number of rounds and matches for 4, 8, 16 athletes
3. Bracket tree is properly wired: every non-final match has `next_match_id` pointing to parent
4. Bye matches auto-completed and winners advanced to next round
5. Third-place match created when enabled, with bronze round_type
6. `swapAthletes()` correctly swaps positions between scheduled matches
7. `getBracketData()` returns complete bracket structure for frontend rendering

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Round_type enum mismatch | High | Current enum has 'knockout', 'quarterfinal', 'semifinal', 'final', 'bronze' -- all needed values exist |
| Seeding complexity for uneven groups | Medium | Fallback: sort all advanced athletes by points/diff globally |
| Power-of-2 padding with many byes | Low | Max bracket = 32, acceptable bye count |
| Match creation order matters (need parent before child for next_match_id) | High | Create from final backwards so parent match exists when child references it |

## Security Considerations

- All operations gated by tournament owner check (`abort_unless($tournament->user_id === auth()->id())`)
- DB::transaction prevents partial bracket state
- Swap only allowed on 'scheduled' matches (prevents tampering mid-tournament)
