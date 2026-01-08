# Tournament Config Page Scout Report

## Overview
Tournament configuration page handles tournament settings, athlete management, match creation, and rankings for categories including singles and doubles content types (double_mixed, double_men, double_women).

Route: `GET /homeyard/tournaments/{tournament_id}/config`

---

## 1. Route & Controller

### Main Route
- **Path**: `/homeyard/tournaments/{id}/config`
- **Method**: GET
- **Route Handler**: `HomeYardTournamentController@configTournament`
- **File**: `/Users/thaopv/Desktop/php/pickleball/routes/web.php` (line 183)

### Supporting API Routes
```
GET  /homeyard/tournaments/{tournament}/categories/{categoryId}/athletes
GET  /homeyard/tournaments/{tournament}/categories/{categoryId}/groups
POST /homeyard/tournaments/{tournamentId}/matches
PUT  /homeyard/tournaments/{tournamentId}/matches/{matchId}
```

---

## 2. Frontend Files

### Main View
**Path**: `/Users/thaopv/Desktop/php/pickleball/resources/views/home-yard/config.blade.php`

**Size**: ~2,400+ lines

**Key Sections**:
- Tab 1: Nội dung thi đấu (Content types) - lines 95-245
- Tab 2: Vòng đấu (Rounds) - lines 247-351
- Tab 3: Tạo bảng đấu (Brackets) - lines 353-514
- Tab 4: Quản lý VĐV (Athlete Management) - lines 516-707
- Tab 5: Tạo trận mới (Match Creation) - lines 709-804
- Tab 6: Bảng xếp hạng (Rankings) - lines 806-930+
- Create Match Modal - lines 1013-1126
- Edit Match Modal - lines 1129-1256
- JavaScript Functions - lines 1260+

---

## 3. Tournament Content Types (Categories)

### Category Types Enum
**File**: `/Users/thaopv/Desktop/php/pickleball/database/migrations/2025_11_19_000001_create_tournament_categories_table.php` (lines 18-24)

```php
'category_type', [
    'single_men',      // Đơn nam
    'single_women',    // Đơn nữ
    'double_men',      // Đôi nam
    'double_women',    // Đôi nữ
    'double_mixed'     // Đôi nam nữ
]
```

### How Category Types Are Used

**Display in Config Page** (lines 122-129):
```html
<select name="category_type" class="form-select" required>
    <option value="">-- Chọn loại --</option>
    <option value="single_men">Đơn nam</option>
    <option value="single_women">Đơn nữ</option>
    <option value="double_men">Đôi nam</option>
    <option value="double_women">Đôi nữ</option>
    <option value="double_mixed">Đôi nam nữ</option>
</select>
```

**Model**: `/Users/thaopv/Desktop/php/pickleball/app/Models/TournamentCategory.php`
- Has `category_type` field (fillable)
- No special handling for doubles in model (current implementation treats all as regular categories)

---

## 4. Match Creation Flow

### Match Creation Dialog (HTML)
**File**: `/Users/thaopv/Desktop/php/pickleball/resources/views/home-yard/config.blade.php`

#### Create Match Modal (lines 1013-1126)

**Step-by-step process**:
1. **Step 1**: Select content type (category) - lines 1026-1038
2. **Step 2**: Select Athlete 1 - lines 1042-1047
3. **Step 3**: Select Athlete 2 - lines 1049-1055
4. Optional: Select round - lines 1058-1068
5. Optional: Select group/bracket - lines 1071-1076
6. Required: Match date & time - lines 1079-1090
7. Optional: Match status - lines 1093-1104
8. Optional: Assign referee - lines 1107-1118

```html
<!-- Create Match Form Structure -->
<form id="createMatchForm">
    <!-- Category Selection (Required) -->
    <select id="matchCategoryId" name="category_id" required></select>
    
    <!-- Athlete 1 Selection (Required) -->
    <select id="athlete1Select" name="athlete1_id" required disabled></select>
    
    <!-- Athlete 2 Selection (Required) -->
    <select id="athlete2Select" name="athlete2_id" required disabled></select>
    
    <!-- Round Selection (Optional) -->
    <select name="round_id"></select>
    
    <!-- Group Selection (Optional) -->
    <select id="matchGroupSelect" name="group_id" disabled></select>
    
    <!-- Date/Time (Required) -->
    <input type="date" name="match_date" required>
    <input type="time" name="match_time" required>
    
    <!-- Status -->
    <select name="status"></select>
    
    <!-- Referee Assignment (Optional) -->
    <select name="referee_id" id="matchRefereeId"></select>
</form>
```

### JavaScript: Match Creation Logic
**File**: `/Users/thaopv/Desktop/php/pickleball/resources/views/home-yard/config.blade.php`

#### Setup Category Selection Listener (lines 1903-1916)
```javascript
function setupCategorySelectListener() {
    const categorySelect = document.getElementById('matchCategoryId');
    const athlete1Select = document.getElementById('athlete1Select');
    const athlete2Select = document.getElementById('athlete2Select');
    const groupSelect = document.getElementById('matchGroupSelect');
    
    if (categorySelect) {
        categorySelect.removeEventListener('change', handleCategoryChange);
        categorySelect.addEventListener('change', handleCategoryChange);
    }
}
```

#### Handle Category Change (lines 1918-2016)

**On category selection, two API calls are made**:

**Call 1: Fetch athletes for category** (lines 1943-1980)
```javascript
fetch(`/homeyard/tournaments/${tournamentId}/categories/${categoryId}/athletes`, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
})
.then(response => response.json())
.then(data => {
    if (data.success && data.athletes) {
        const athletes = data.athletes;
        // Populate both athlete1Select and athlete2Select with SAME athletes
        athlete1Select.innerHTML = 
            `<option value="">-- Chọn VĐV 1 --</option>${athleteOptions}`;
        athlete2Select.innerHTML = 
            `<option value="">-- Chọn VĐV 2 --</option>${athleteOptions}`;
        athlete1Select.disabled = false;
        athlete2Select.disabled = false;
    }
})
```

**Key Point**: Currently, BOTH athlete dropdowns get the same athlete list, regardless of category type. No special handling for doubles to show athlete pairs.

**Call 2: Fetch groups for category** (lines 1984-2015)
```javascript
fetch(`/homeyard/tournaments/${tournamentId}/categories/${categoryId}/groups`, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
})
.then(data => {
    if (data.success && data.groups && data.groups.length > 0) {
        const groups = data.groups;
        // Populate groupSelect
        groupSelect.innerHTML = 
            `<option value="">-- Chọn bảng/nhóm (tuỳ chọn) --</option>${groupOptions}`;
        groupSelect.disabled = false;
    }
})
```

#### Submit Create Match Form (lines 2059-2146)

```javascript
function initializeCreateMatchForm() {
    const form = document.getElementById('createMatchForm');
    setupCategorySelectListener();
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const data = {
            athlete1_id: formData.get('athlete1_id'),      // Individual athlete
            athlete2_id: formData.get('athlete2_id'),      // Individual athlete
            category_id: formData.get('category_id'),
            round_id: roundId || null,
            match_date: matchDate || null,
            match_time: matchTime || null,
            group_id: groupId || null,
            status: formData.get('status'),
            referee_id: refereeId || null,
        };
        
        // Send to backend
        fetch(`/homeyard/tournaments/${tournamentId}/matches`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
    });
}
```

---

## 5. Backend Match Creation

### Store Match Method
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/HomeYardTournamentController.php`
**Lines**: 2332-2452

#### Validation (lines 2337-2347)
```php
$validated = $request->validate([
    'athlete1_id' => 'required|exists:tournament_athletes,id',
    'athlete2_id' => 'required|exists:tournament_athletes,id',
    'category_id' => 'required|exists:tournament_categories,id',
    'round_id' => 'nullable|exists:rounds,id',
    'group_id' => 'nullable|exists:groups,id',
    'match_date' => 'nullable|date_format:Y-m-d',
    'match_time' => 'nullable|date_format:H:i',
    'status' => 'nullable|in:scheduled,ready,in_progress,completed,cancelled,postponed,bye',
    'referee_id' => 'nullable|exists:users,id',
]);
```

**Important Validations** (lines 2349-2387):
```php
// Verify both athletes are from this tournament
$athlete1 = TournamentAthlete::where('id', $validated['athlete1_id'])
    ->where('tournament_id', $tournament->id)
    ->firstOrFail();

$athlete2 = TournamentAthlete::where('id', $validated['athlete2_id'])
    ->where('tournament_id', $tournament->id)
    ->firstOrFail();

// Verify both athletes belong to selected category
if ($athlete1->category_id != $validated['category_id']) {
    return error: 'VDV 1 khong thuoc noi dung thi dau da chon';
}
if ($athlete2->category_id != $validated['category_id']) {
    return error: 'VDV 2 khong thuoc noi dung thi dau da chon';
}

// Verify athletes are different
if ($validated['athlete1_id'] == $validated['athlete2_id']) {
    return error: 'VDV 1 va VDV 2 phai khac nhau';
}

// Important for OCR tournaments
if ($tournament->is_ocr && $athlete1->user_id === $athlete2->user_id) {
    return error: 'Hai VDV phai la nhung nguoi dung khac nhau (OCR)';
}
```

#### Create Match (lines 2414-2429)
```php
$match = MatchModel::create([
    'tournament_id' => $tournament->id,
    'athlete1_id' => $validated['athlete1_id'],      // Single athlete (TournamentAthlete)
    'athlete1_name' => $athlete1Name,
    'athlete2_id' => $validated['athlete2_id'],      // Single athlete (TournamentAthlete)
    'athlete2_name' => $athlete2Name,
    'category_id' => $validated['category_id'],      // Category with type (single/double/mixed)
    'round_id' => $validated['round_id'],
    'group_id' => $validated['group_id'] ?? null,
    'match_number' => $matchNumber,                  // M1, M2, etc.
    'status' => $validated['status'] ?? 'scheduled',
    'match_date' => $validated['match_date'],
    'match_time' => $validated['match_time'],
    'referee_id' => $refereeId,
    'referee_name' => $refereeName,
]);
```

---

## 6. Athlete Selection for Doubles

### Current Implementation
**Problem**: Currently, NO special handling for doubles content types.

For both `double_mixed`, `double_men`, `double_women` categories:
- Backend treats athlete1_id & athlete2_id as individual athletes
- Same dropdown list shown for both selections
- No validation that ensures proper doubles pairings
- No UI to select pairs or combinations

**Line 1951-1959** (JavaScript - athlete dropdown population):
```javascript
const athletes = data.athletes;  // All athletes in category
const athleteOptions = athletes.map(athlete =>
    `<option value="${athlete.id}">${athlete.athlete_name}</option>`
).join('');

athlete1Select.innerHTML =
    `<option value="">-- Chọn VĐV 1 --</option>${athleteOptions}`;
athlete2Select.innerHTML =
    `<option value="">-- Chọn VĐV 2 --</option>${athleteOptions}`;  // SAME LIST
```

### Backend: Get Category Athletes
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Http/Controllers/Front/HomeYardTournamentController.php`
**Lines**: 3296-3319

```php
public function getCategoryAthletes(Tournament $tournament, $categoryId)
{
    $this->authorize('update', $tournament);
    
    // Get approved athletes for this category
    $athletes = TournamentAthlete::where('tournament_id', $tournament->id)
        ->where('category_id', $categoryId)
        ->where('status', 'approved')
        ->orderBy('athlete_name')
        ->get(['id', 'athlete_name']);
    
    return response()->json([
        'success' => true,
        'athletes' => $athletes
    ]);
}
```

**What it returns**:
```json
{
    "success": true,
    "athletes": [
        {"id": 1, "athlete_name": "Nguyễn Văn A"},
        {"id": 2, "athlete_name": "Trần Thị B"},
        {"id": 3, "athlete_name": "Phạm Văn C"}
    ]
}
```

Same list used for athlete1 & athlete2 selection, regardless of category type.

---

## 7. Category Model Structure

**File**: `/Users/thaopv/Desktop/php/pickleball/app/Models/TournamentCategory.php`

**Fillable Fields**:
```php
protected $fillable = [
    'tournament_id',
    'category_name',        // e.g., "Nam đôi 18+"
    'category_type',        // 'single_men', 'double_men', 'double_mixed', etc.
    'age_group',           // 'open', 'u18', '18+', '35+', '45+', '55+'
    'max_participants',
    'prize_money',
    'description',
    'status',
    'current_participants',
];
```

**Relations**:
- `tournament()` - BelongsTo Tournament
- `athletes()` - HasMany TournamentAthlete
- `matches()` - HasMany MatchModel
- `groups()` - HasMany Group

**Category type is stored but NOT used in match creation logic for special handling**.

---

## 8. TournamentAthlete Model

**File**: `/Users/thaopv/Desktop/php/pickleball/app/Models/TournamentAthlete.php` (assumed)

**Key fields**:
- `tournament_id`
- `category_id` - Links athlete to specific category
- `user_id` - Links to User account
- `athlete_name` - Athlete display name
- `email`, `phone`
- `status` - 'approved', 'pending', 'rejected'
- `payment_status` - 'pending', 'paid', 'refunded'

Currently, one athlete can only be in ONE category. No pair/team concept exists.

---

## 9. Edit Match Modal (Existing Match Modification)

**File**: `/Users/thaopv/Desktop/php/pickleball/resources/views/home-yard/config.blade.php`
**Lines**: 1129-1256

**Key Difference from Create**: 
- Shows ALL tournament athletes in both dropdowns (not filtered by category)
- Allows changing match assignments

**Line 1146-1152**:
```html
<select id="editAthlete1" name="athlete1_id" class="form-select" required>
    <option value="">-- Chọn VĐV --</option>
    @if ($tournament && $tournament->athletes)
        @foreach ($tournament->athletes as $athlete)
            <option value="{{ $athlete->id }}">{{ $athlete->athlete_name }}</option>
        @endforeach
    @endif
</select>
```

Shows ALL tournament athletes, not filtered by category (unlike create form).

---

## 10. Database Schema

### TournamentCategory Table
**File**: `/Users/thaopv/Desktop/php/pickleball/database/migrations/2025_11_19_000001_create_tournament_categories_table.php`

```sql
CREATE TABLE tournament_categories (
    id: PRIMARY KEY
    tournament_id: FOREIGN KEY -> tournaments
    category_name: VARCHAR      -- "Nam đôi 18+"
    category_type: ENUM        -- single_men|single_women|double_men|double_women|double_mixed
    age_group: VARCHAR         -- open|u18|18+|35+|45+|55+
    max_participants: INTEGER  -- max athletes in category
    prize_money: DECIMAL       -- prize pool
    description: TEXT
    status: ENUM               -- draft|open|closed|ongoing|completed
    current_participants: INTEGER
    timestamps
)
```

### MatchModel Table Structure
**File**: `/Users/thaopv/Desktop/php/pickleball/app/Models/MatchModel.php`

**Key fields for match creation**:
```php
protected $fillable = [
    'tournament_id',
    'category_id',        // Links to TournamentCategory
    'round_id',
    'court_id',
    'group_id',
    'match_number',       // M1, M2, etc.
    'athlete1_id',        // TournamentAthlete ID
    'athlete1_name',
    'athlete1_score',
    'athlete2_id',        // TournamentAthlete ID
    'athlete2_name',
    'athlete2_score',
    'winner_id',
    'referee_id',
    'referee_name',
    'match_date',
    'match_time',
    'status',             // scheduled|ready|in_progress|completed|cancelled|postponed|bye
    'best_of',
    'set_scores',         // JSON array: [{"set": 1, "athlete1": 11, "athlete2": 7}, ...]
    'final_score',
    'notes',
    'next_match_id',
    'winner_advances_to',
];
```

No fields for team pairs or secondary athletes in doubles.

---

## 11. Key API Endpoints

### Get Athletes for Category
```
GET /homeyard/tournaments/{tournament}/categories/{categoryId}/athletes
```

**Response**:
```json
{
    "success": true,
    "athletes": [
        {"id": 1, "athlete_name": "Nguyễn Văn A"},
        {"id": 2, "athlete_name": "Trần Thị B"}
    ]
}
```

**File**: HomeYardTournamentController.php, lines 3296-3319

### Get Groups for Category
```
GET /homeyard/tournaments/{tournament}/categories/{categoryId}/groups
```

**Response**:
```json
{
    "success": true,
    "groups": [
        {"id": 1, "group_name": "Bảng A"},
        {"id": 2, "group_name": "Bảng B"}
    ]
}
```

**File**: HomeYardTournamentController.php, lines 3324-3346

### Store Match
```
POST /homeyard/tournaments/{tournamentId}/matches
```

**Request Body**:
```json
{
    "athlete1_id": 1,
    "athlete2_id": 2,
    "category_id": 3,
    "round_id": 4,
    "group_id": 5,
    "match_date": "2025-12-25",
    "match_time": "14:00",
    "status": "scheduled",
    "referee_id": null
}
```

**File**: HomeYardTournamentController.php, lines 2332-2452

### Update Match
```
PUT /homeyard/tournaments/{tournamentId}/matches/{matchId}
```

**File**: HomeYardTournamentController.php, lines 2489-2549

---

## 12. Gaps & Limitations for Doubles

### Issue 1: No Pair Selection UI
- Currently shows individual athletes from category for both athlete1 & athlete2
- No UI to select athlete pairs or combinations
- User must manually verify correct pairings

### Issue 2: No Validation of Doubles Pairings
- Backend doesn't validate doubles pair combinations
- No rules for mixed doubles (e.g., must have 1 male + 1 female)
- No validation for proper gender pairings in double_men/double_women

### Issue 3: No Doubles-Specific Logic in Category
- Category model doesn't differentiate handling based on category_type
- Same getCategoryAthletes() returns all athletes regardless of type
- No logic to filter/organize pairs

### Issue 4: Database Schema
- MatchModel only has athlete1_id & athlete2_id
- No concept of "team" or "pair" entities
- Athletes could be any combination, regardless of category type

### Issue 5: Category Type Unused in Frontend
- Category type (double_mixed, double_men, etc.) shown in dropdown
- But NOT used to modify athlete selection logic
- Edit match form shows ALL athletes regardless of category

---

## Summary

### Current State
- Tournament config page supports 5 category types: single_men, single_women, double_men, double_women, double_mixed
- Match creation allows selecting 2 athletes from approved athletes in category
- No validation that pairings match category requirements
- No pair/team concept in database
- Backend stores just athlete1_id & athlete2_id in matches table
- JavaScript filters athletes by category when creating match

### For Doubles Implementation Needed
1. Athlete pair management (either as records or through complex logic)
2. Validation of gender combinations for each category type
3. Updated UI to show pairs or enforce pairing rules
4. Backend pair validation before match creation
5. Database schema consideration (add team_ids or similar)
6. Update getCategoryAthletes() to return pairs for doubles categories
7. Update match creation form to handle pair selection

