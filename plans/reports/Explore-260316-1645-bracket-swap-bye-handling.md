# Bracket Swap & Bye Handling Flow - Comprehensive Analysis

**Date:** 2026-03-16  
**Status:** Complete exploration  
**Purpose:** Full technical audit of swap and bye handling mechanisms

---

## CRITICAL FINDINGS & ISSUES

### Issue 1: Name Columns NOT SYNCED During Swap
**Severity:** HIGH  
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Services/Tournament/KnockoutBracketQuery.php` (lines 48-70)

```php
public function swapAthletes(int $matchId1, string $slot1, int $matchId2, string $slot2): void
{
    // ... validation ...
    $col1 = $slot1 . '_id';      // e.g., 'athlete1_id'
    $col2 = $slot2 . '_id';      // e.g., 'athlete2_id'
    
    $val1 = $match1->{$col1};
    $val2 = $match2->{$col2};
    
    $match1->update([$col1 => $val2]);    // Swaps IDs
    $match2->update([$col2 => $val1]);    // Swaps IDs
}
```

**PROBLEM:** Only athlete*_id columns are swapped. The cached name columns (`athlete1_name`, `athlete2_name`) are NOT swapped.

**Example Scenario:**
- Match 1: athlete1_id=5 (John), athlete1_name="John"
- Match 2: athlete2_id=10 (Jane), athlete2_name="Jane"
- After swap:
  - Match 1: athlete1_id=10, athlete1_name="John" ← NAME IS WRONG!
  - Match 2: athlete2_id=5, athlete2_name="Jane" ← NAME IS WRONG!

**Impact:**
- Display shows wrong names in bracket UI
- Search/filtering may use names and return incorrect results
- Confusion for tournament organizers

### Issue 2: handleBye() Does NOT Reload Match Before Processing
**Severity:** MEDIUM  
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Services/Tournament/KnockoutMatchBuilder.php` (lines 83-97)

```php
public function handleBye(MatchModel $match): void
{
    $winnerId = $match->athlete1_id ?? $match->athlete2_id;
    
    if ($winnerId === null) {
        return;
    }
    
    $match->update([
        'status'    => 'completed',
        'winner_id' => $winnerId,
    ]);
    
    $this->advanceWinner($match, $winnerId);
}
```

**PROBLEM:** The $match parameter is used as-is. If the match was recently modified in the same request (e.g., via swap), the in-memory model object may have stale data.

**Scenario:**
1. Swap athletes in a match
2. Later in same request, handleBye() is called
3. $match object still has old IDs in memory
4. handleBye() advances the wrong athlete

**Mitigation Needed:** Reload the match before processing:
```php
$match = MatchModel::findOrFail($match->id);
```

### Issue 3: advanceWinner() Does NOT Check Slot Occupancy Before Placing
**Severity:** MEDIUM  
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Services/Tournament/KnockoutMatchBuilder.php` (lines 102-121)

```php
public function advanceWinner(MatchModel $match, int $winnerId): void
{
    if (!$match->next_match_id) {
        return;
    }
    
    $nextMatch = MatchModel::find($match->next_match_id);
    if (!$nextMatch || $nextMatch->status !== 'scheduled') {
        return;
    }
    
    // ... fetch athlete name ...
    
    if ($nextMatch->athlete1_id === null) {
        $nextMatch->update(['athlete1_id' => $winnerId, 'athlete1_name' => $name]);
    } elseif ($nextMatch->athlete2_id === null) {
        $nextMatch->update(['athlete2_id' => $winnerId, 'athlete2_name' => $name]);
    }
    // ← NO ELSE: If both slots already occupied, winner is silently dropped!
}
```

**PROBLEM:** If both slots in next_match are already filled, the winner is NOT placed and no error is raised.

**Scenarios Where This Fails:**
1. Manual swap placed an athlete in next match
2. advanceWinner() is called for a bye from different match
3. Next match already full → winner not advanced
4. Tournament bracket becomes broken/incomplete

**Example Race Condition:**
```
Match A (bye): athlete1 advances to Match C
Match B (bye): athlete2 also should advance to Match C
Timeline:
  1. A.bye advances athlete1 → C.athlete1 = athlete1
  2. B.bye tries to advance athlete2 → C already full!
     → athlete2 is silently dropped
  3. Match C missing athlete2!
```

---

## FLOW ANALYSIS

### 1. Controller Entry Point
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/Tournament/TournamentBracketController.php`

**Method:** `swap()` (lines 73-98)

**Validation:**
- ✓ Both matches exist in tournament
- ✓ Slot names valid ('athlete1' or 'athlete2')
- ✓ Both matches status = 'scheduled'
- ✓ Authorization check (owner only)

**Flow:**
```
Controller.swap()
  ↓
  Request validation (lines 78-83)
  ↓
  BracketService.swapAthletes()
  ↓
  Error logged & returned as JSON
```

**Error Handling:** Generic 500 error with message "Đổi vị trí thất bại" (logs full exception)

---

### 2. Match Model Structure
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Models/MatchModel.php`

**Key Columns (from migration 2025_11_19_000004_create_matches_table.php):**
```php
'athlete1_id'      -> FK tournament_athletes
'athlete1_name'    -> String (CACHED for display)
'athlete2_id'      -> FK tournament_athletes  
'athlete2_name'    -> String (CACHED for display)
'winner_id'        -> FK tournament_athletes
'status'           -> Enum: scheduled, ready, in_progress, completed, cancelled, postponed, bye
'next_match_id'    -> FK matches (self-referencing)
'bracket_position' -> Integer (heap-style: 1=root, 2,3=next level, etc.)
```

**Events/Observers:**
- ✓ MatchObserver.saving() (lines 14-24) - syncs referee_name when referee_id changes
- ✗ NO observer for athlete*_id changes (names can get out of sync)

**No Direct Status Change Trigger:** The model itself doesn't trigger any observer when status changes to 'completed' or 'bye'

---

### 3. Bye Handling Flow

#### Creation Phase
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Services/Tournament/KnockoutMatchBuilder.php`

**createMatches()** (lines 19-78):
1. Creates matches from final backwards (heap-style positioning)
2. First round matches: check if athlete1 or athlete2 is null
3. If null → call handleBye() IMMEDIATELY
4. Bye is completed right after creation

**Current Behavior - CORRECT:**
- Byes auto-complete during bracket generation
- Winner advances to next match automatically
- Status='completed' set for bye matches
- Next match athlete slot filled with advancing athlete

#### handleBye() Details
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Services/Tournament/KnockoutMatchBuilder.php` (lines 83-97)

```php
public function handleBye(MatchModel $match): void
{
    $winnerId = $match->athlete1_id ?? $match->athlete2_id;  // Get non-null athlete
    
    if ($winnerId === null) {
        return;  // Both null = invalid bye
    }
    
    $match->update([
        'status'    => 'completed',
        'winner_id' => $winnerId,
    ]);
    
    $this->advanceWinner($match, $winnerId);  // Move to next match
}
```

**Called By:**
1. KnockoutMatchBuilder.createMatches() - during initial bracket generation
2. (No other explicit calls found)

---

### 4. Winner Advancement Flow

#### advanceWinner() Details
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Services/Tournament/KnockoutMatchBuilder.php` (lines 102-121)

```php
public function advanceWinner(MatchModel $match, int $winnerId): void
{
    // Exit early if no next match
    if (!$match->next_match_id) {
        return;
    }
    
    // Fetch next match (fresh from DB)
    $nextMatch = MatchModel::find($match->next_match_id);
    if (!$nextMatch || $nextMatch->status !== 'scheduled') {
        return;
    }
    
    // Get athlete name for display
    $athlete = TournamentAthlete::find($winnerId);
    $name = $athlete?->pair_name ?? 'Chưa xác định';
    
    // Place in first available slot (left-to-right)
    if ($nextMatch->athlete1_id === null) {
        $nextMatch->update(['athlete1_id' => $winnerId, 'athlete1_name' => $name]);
    } elseif ($nextMatch->athlete2_id === null) {
        $nextMatch->update(['athlete2_id' => $winnerId, 'athlete2_name' => $name]);
    }
    // SILENT FAIL if both slots occupied
}
```

**Positives:**
- ✓ Fetches fresh next_match from DB (not stale)
- ✓ Checks status = 'scheduled' (won't overwrite in-progress matches)
- ✓ Gets athlete.pair_name for name caching
- ✓ Updates both ID and name together (at least within this call)

**Negatives:**
- ✗ Silent failure if next match both slots occupied
- ✗ No logging of placement
- ✗ No validation that $winnerId belongs to current match

**Called By:**
1. KnockoutMatchBuilder.handleBye()
2. BracketAdvancementTrait.handleBracketAdvancement() - when match completed during play

---

### 5. Bracket Advancement After Match Completion
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/Tournament/Traits/BracketAdvancementTrait.php`

**When Called:**
- After a knockout match is completed (score entered)

**handleBracketAdvancement()** (lines 15-30):
```php
protected function handleBracketAdvancement(MatchModel $match): void
{
    if (!$match->winner_id) {
        return;
    }
    
    // Advance to next match
    if ($match->next_match_id) {
        $bracketService = app(KnockoutBracketService::class);
        $bracketService->advanceWinner($match, $match->winner_id);  // ← passes $match object
    }
    
    updateRoundCompletionStatus($match);
    handleThirdPlaceRouting($match);
}
```

**CONCERN:** Passes the $match object which may have stale state if not fresh from DB.

**handleThirdPlaceRouting()** (lines 56-94):
- Only for semifinal losers
- Finds bronze round match
- Places loser in first available slot
- Also gets fresh pair_name and updates both ID + name

---

### 6. Swap Implementation Details

**File:** `/Users/thaopv/Desktop/php/pickleball/app/Services/Tournament/KnockoutBracketQuery.php` (lines 48-70)

**swapAthletes():**
```php
public function swapAthletes(int $matchId1, string $slot1, int $matchId2, string $slot2): void
{
    $match1 = MatchModel::findOrFail($matchId1);
    $match2 = MatchModel::findOrFail($matchId2);
    
    // Validation
    if ($match1->status !== 'scheduled' || $match2->status !== 'scheduled') {
        throw new InvalidArgumentException('Chỉ có thể hoán đổi VĐV trong các trận chưa diễn ra.');
    }
    
    $allowed = ['athlete1_id', 'athlete2_id'];
    $col1 = $slot1 . '_id';      // e.g., 'athlete1_id'
    $col2 = $slot2 . '_id';
    
    if (!in_array($col1, $allowed) || !in_array($col2, $allowed)) {
        throw new InvalidArgumentException('Slot không hợp lệ. Chỉ chấp nhận athlete1 hoặc athlete2.');
    }
    
    $val1 = $match1->{$col1};
    $val2 = $match2->{$col2};
    
    $match1->update([$col1 => $val2]);
    $match2->update([$col2 => $val1]);
    
    // NO NAME SYNC HERE!
}
```

**Validation:**
- ✓ Both matches exist in tournament
- ✓ Both matches status='scheduled'
- ✓ Slot names are valid

**Data Swapped:**
- ✓ athlete1_id (or athlete2_id)
- ✗ athlete1_name (or athlete2_name) NOT SWAPPED

**Database Transaction:**
- No explicit DB::transaction() wrapper
- But update() calls are separate
- No atomicity guarantees

---

### 7. Data Consistency Issues

#### Name Sync Gaps
| Operation | athlete*_id | athlete*_name | Status |
|-----------|-------------|---------------|--------|
| createMatches() | ✓ Set | ✓ Set | Synchronized |
| advanceWinner() | ✓ Set | ✓ Set | Synchronized |
| handleThirdPlaceRouting() | ✓ Set | ✓ Set | Synchronized |
| swapAthletes() | ✓ Swap | ✗ NOT SWAPPED | BROKEN |
| MatchObserver.saving() | - | ✓ Syncs referee_name on referee_id change | Only referee |

**Conclusion:** Only swapAthletes() creates desynchronization

#### Related Helper Classes
**TournamentMatchService.php:**
- `recordMatchResult()` - sets status='in_progress', updates set_scores
- `completeMatch()` - sets status='completed', calls handleBracketAdvancement()

**No automatic cascade of handlebyes or advanceWinner when status changes to bye**

---

### 8. Third Place Match Routing
**File:** `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/Tournament/Traits/BracketAdvancementTrait.php` (lines 56-94)

**When:** After any semifinal match completes

**Logic:**
1. Gets loser_id from match (computed property: `getLoserIdAttribute()`)
2. Finds bronze round
3. Places loser in first available slot (athlete1 or athlete2)
4. Gets fresh pair_name and sets both ID + name

**Same Issue as advanceWinner():**
- Silent fail if both slots occupied
- But in practice, bronze match starts empty

---

### 9. Bye Status in Match Model
**Migration:** `2025_11_19_000004_create_matches_table.php` (line 50)

**Status enum includes:** 'bye' (separate from 'completed')

**Current Code Usage:**
- handleBye() sets status='completed' (NOT 'bye')
- checkCategoryCompletion() treats status='bye' as completed equivalent
- No code actually sets status='bye'

**Inconsistency:** The 'bye' enum value exists but is never used

---

## EDGE CASES & POTENTIAL BUGS

### Edge Case 1: Multiple Byes in Same Round
**Scenario:** 3 athletes, first round has 2 matches:
- Match 1: athlete1 vs null (bye) → athlete1 advances
- Match 2: athlete2 vs athlete3 (real match)

**Concern:** Both byes call advanceWinner() in same createMatches() transaction
- Match 1 advances athlete1 to semifinal
- If next_match already occupied, athlete1 silently dropped

**Current Behavior:** Both matches created and byes processed in order. If they share same next_match, second bye fails silently.

### Edge Case 2: Swap Then Manual Bracket Modification
**Scenario:**
1. Swap athlete1 and athlete2 in Match A (names out of sync)
2. Later, admin manually edits the match through some other interface
3. Old name is still in database
4. Confusion about what athlete is actually in the match

### Edge Case 3: Swap In Match That Would Create Bye
**Scenario:**
1. Match has athlete1_id=5, athlete2_id=null (bye match)
2. Swap athlete1 with athlete from another match
3. Result: Match now has athlete1_id=10, athlete2_id=null (still bye)
4. But name columns not synced

**Question:** Should bye handling be re-triggered after swap? Currently NO.

### Edge Case 4: Both Slots Occupied But Match Not Started
**Scenario:** advanceWinner() checks `status !== 'scheduled'` → returns early
- But what if status='ready'?
- Does 'ready' status exist? Yes, in enum
- Would advanceWinner() still skip placement?

### Edge Case 5: Multiple advanceWinner() Calls Same Request
**Scenario:**
1. Bye handler calls advanceWinner($match1, athlete1)
2. Places athlete1 in nextMatch.athlete1
3. Same request, another bye calls advanceWinner($match2, athlete2)
4. advanceWinner() fetches fresh nextMatch from DB
5. Places athlete2 in nextMatch.athlete2
6. Both updates succeed

**Positive:** Fresh DB fetch in advanceWinner() prevents stale data issues

---

## MISSING SAFEGUARDS

1. **No transaction wrapper for swapAthletes()**
   - If first update succeeds, second fails → data inconsistency
   - Recommendation: Wrap in `DB::transaction()`

2. **No name sync after swap**
   - athlete*_name columns become stale
   - Recommendation: Also swap name columns

3. **No error on full next_match in advanceWinner()**
   - Silent failure when both slots occupied
   - Recommendation: Throw exception or log warning

4. **No reload in handleBye()**
   - Uses stale $match object if modified earlier in request
   - Recommendation: Reload before processing

5. **No observer for bye-triggering conditions**
   - Swap could create new bye but doesn't trigger bye handling
   - Recommendation: Consider if swap should auto-handle new byes

6. **Status='bye' enum never used**
   - Code sets 'completed' for byes, not 'bye'
   - Causes inconsistency in checkCategoryCompletion()
   - Recommendation: Standardize on one status

---

## CALL CHAIN SUMMARY

```
Controller.swap()
  ↓
  BracketService.swapAthletes()
    ↓
    BracketQuery.swapAthletes()
      ├─ findOrFail(match1)
      ├─ findOrFail(match2)
      ├─ validate status
      ├─ swap athlete*_id
      └─ NO: swap athlete*_name
           └─ ISSUE: Names out of sync!

Controller.complete_match()
  ↓
  TournamentMatchService.completeMatch()
    ↓
    match.update(['status' => 'completed', 'winner_id' => $winnerId])
      ↓
      BracketAdvancementTrait.handleBracketAdvancement()
        ├─ BracketService.advanceWinner()
        │   ├─ get fresh nextMatch from DB (GOOD)
        │   ├─ check status='scheduled' (good check)
        │   ├─ get athlete.pair_name
        │   └─ place in first available slot
        │       └─ ISSUE: Silent fail if full
        │
        └─ BracketAdvancementTrait.handleThirdPlaceRouting()
            └─ same pattern as advanceWinner()

Bracket generation:
  ↓
  KnockoutBracketService.generateBracket()
    ↓
    KnockoutMatchBuilder.createMatches()
      └─ For each first-round match:
          └─ if athlete1 or athlete2 is null:
              └─ handleBye()
                  ├─ update match status='completed', winner_id
                  └─ advanceWinner()
```

---

## RECOMMENDATIONS

### Priority 1 (Critical)
1. **Fix swapAthletes() to sync names**
   ```php
   $nameCol1 = $slot1 . '_name';
   $nameCol2 = $slot2 . '_name';
   $name1 = $match1->{$nameCol1};
   $name2 = $match2->{$nameCol2};
   
   $match1->update([$col1 => $val2, $nameCol1 => $name2]);
   $match2->update([$col2 => $val1, $nameCol2 => $name1]);
   ```

2. **Add transaction wrapper to swapAthletes()**
   ```php
   DB::transaction(function() {
       // existing swap logic
   });
   ```

### Priority 2 (High)
3. **Add error handling in advanceWinner()**
   - Log warning or throw if next match both slots occupied
   - Or validate that winner isn't already in next match

4. **Reload match in handleBye()**
   ```php
   $match = MatchModel::findOrFail($match->id);
   $winnerId = $match->athlete1_id ?? $match->athlete2_id;
   ```

### Priority 3 (Medium)
5. **Standardize bye status**
   - Decide: use 'bye' or 'completed' for bye matches
   - Currently 'bye' enum unused

6. **Add MatchObserver for athlete*_id changes**
   - Similar to referee_name sync
   - Auto-sync athlete*_name when athlete*_id changes

### Priority 4 (Low)
7. **Consider bye re-triggering after swap**
   - If swap creates new bye condition, should it auto-handle?
   - Or should swap prevent null-to-null scenarios?

8. **Add swap logging**
   - Who swapped, when, which athletes
   - Helpful for audit trail

---

## FILES INVOLVED (Complete List)

| File | Lines | Purpose |
|------|-------|---------|
| `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/Tournament/TournamentBracketController.php` | 73-98 | Swap endpoint, validation |
| `/Users/thaopv/Desktop/php/pickleball/app/Services/Tournament/KnockoutBracketService.php` | 128-131 | Proxy to query |
| `/Users/thaopv/Desktop/php/pickleball/app/Services/Tournament/KnockoutBracketQuery.php` | 48-70 | ACTUAL swap logic |
| `/Users/thaopv/Desktop/php/pickleball/app/Services/Tournament/KnockoutMatchBuilder.php` | 83-121 | handleBye() & advanceWinner() |
| `/Users/thaopv/Desktop/php/pickleball/app/Models/MatchModel.php` | 1-389 | Match model, no observers for athlete changes |
| `/Users/thaopv/Desktop/php/pickleball/app/Observers/MatchObserver.php` | 1-25 | Only referee sync, not athlete |
| `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/Tournament/Traits/BracketAdvancementTrait.php` | 15-94 | Third place routing, called after completion |
| `/Users/thaopv/Desktop/php/pickleball/database/migrations/2025_11_19_000004_create_matches_table.php` | 1-89 | Schema definition |

---

## TEST RECOMMENDATIONS

1. **Swap then check names**
   - Swap athlete A ↔ athlete B
   - Verify both athlete*_id AND athlete*_name swapped

2. **Swap creating bye then check auto-completion**
   - Swap athlete with null
   - Verify bye handling doesn't trigger post-swap

3. **Multiple byes advancing to same match**
   - Create bracket with multiple first-round byes
   - All should advance correctly to second round

4. **Swap in running tournament**
   - After some matches completed
   - Verify no data corruption

5. **Status='bye' enum test**
   - Verify matches are marked correctly
   - Check checkCategoryCompletion() logic

---

## UNRESOLVED QUESTIONS

1. **Should bye status be 'bye' or 'completed'?**
   - Enum has both, code uses 'completed', checkCategoryCompletion() treats as equivalent
   - What's the intent of the 'bye' enum value?

2. **Should swap validate that it doesn't create logical contradictions?**
   - E.g., prevent swapping a completed athlete with pending match?
   - Currently only checks status='scheduled'

3. **Is there API documentation for the swap endpoint?**
   - What does frontend expect in response?
   - Error messages in Vietnamese only?

4. **What happens if swap is called with same athlete twice?**
   - Swap athlete 5 in match1 with athlete 5 in match2?
   - Both slots become 5?

5. **Is transaction isolation sufficient for concurrent swaps?**
   - Two simultaneous swaps on same bracket?
   - Database-level race conditions?

---

