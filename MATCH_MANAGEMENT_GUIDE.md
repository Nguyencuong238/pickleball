# Hướng Dẫn Quản Lý Trận Đấu

## Tóm Tắt

Đã thêm tab **Quản Lý Trận Đấu** vào dashboard để chủ giải có thể tạo, xem, và xóa trận đấu.

## Các Thành Phần

### 1. Frontend (Blade Template)

**File:** `/resources/views/home-yard/dashboard.blade.php`

- **Tab:** Thêm tab "🎾 QUẢN LÝ TRẬN ĐẤU" ở phần config-tabs
- **UI:**
  - Nút "➕ Tạo Trận Mới" - mở modal tạo trận
  - Bộ lọc theo nội dung thi đấu
  - Bảng danh sách trận đấu với các cột:
    - Trận (match_number)
    - Nội dung
    - VĐV 1
    - VĐV 2
    - Ngày thi đấu
    - Trạng thái
    - Hành động (Sửa/Xóa)

### 2. Modal Tạo Trận

Form Modal có các trường:

**Bắt buộc:**
- Nội dung thi đấu (category_id)
- VĐV 1 (athlete1_id)
- VĐV 2 (athlete2_id)
- Ngày thi đấu (match_date)

**Tuỳ chọn:**
- Vòng đấu (round_id)
- Sân thi đấu (court_id)
- Giờ thi đấu (match_time)
- Số set tối đa (best_of: 1, 3, hoặc 5)
- Ghi chú (notes)

**Tính năng:**
- Khi chọn nội dung, dropdown VĐV 1 & VĐV 2 tự động load danh sách VĐV đã duyệt
- Submit form gửi AJAX request tới `/homeyard/tournaments/{id}/matches`

### 3. Backend Routes

**File:** `/routes/web.php`

```php
// Athletes by Category (AJAX)
Route::get('tournaments/{tournament}/athletes', [HomeYardTournamentController::class, 'getAthletesByCategory'])->name('tournaments.athletes.bycategory');

// Matches Management
Route::get('tournaments/{tournament}/matches', [HomeYardTournamentController::class, 'listMatches'])->name('tournaments.matches.index');
Route::post('tournaments/{tournament}/matches', [HomeYardTournamentController::class, 'createMatch'])->name('tournaments.matches.store');
Route::delete('tournaments/{tournament}/matches/{match}', [HomeYardTournamentController::class, 'deleteMatch'])->name('tournaments.matches.destroy');
```

### 4. Controller Methods

**File:** `/app/Http/Controllers/Front/HomeYardTournamentController.php`

#### `getAthletesByCategory(Request $request, Tournament $tournament)`
- Lấy danh sách VĐV theo nội dung (category)
- Filter: status = 'approved' (tuỳ chọn)
- Return: JSON

#### `listMatches(Request $request, Tournament $tournament)`
- Danh sách trận đấu của giải
- Filter: category_id (tuỳ chọn)
- Sắp xếp: match_date ASC
- Return: JSON hoặc View

#### `createMatch(Request $request, Tournament $tournament)`
- Validate input
- Tạo record trong bảng `matches`
- Tự động generate match_number (M1, M2, ...)
- Cache tên vận động viên (athlete1_name, athlete2_name)
- Status mặc định: 'scheduled'
- Return: JSON response

#### `deleteMatch(Request $request, Tournament $tournament, MatchModel $match)`
- Kiểm tra authorization
- Xóa trận đấu
- Return: JSON response

### 5. Database - Bảng Matches

**Schema:**
```
id (Primary Key)
tournament_id (FK)
category_id (FK) - nullable
round_id (FK) - nullable
court_id (FK) - nullable
group_id (nullable)

match_number (string) - VD: M1, M2
bracket_position (integer) - nullable

-- Player 1
athlete1_id (FK) - nullable
athlete1_name (string) - cached
athlete1_score (integer) - default 0

-- Player 2
athlete2_id (FK) - nullable
athlete2_name (string) - cached
athlete2_score (integer) - default 0

-- Match Info
winner_id (FK) - nullable
match_date (date)
match_time (time) - nullable
actual_start_time (datetime) - nullable
actual_end_time (datetime) - nullable

-- Status
status (enum): scheduled, ready, in_progress, completed, cancelled, postponed, bye
default: 'scheduled'

-- Scoring
best_of (integer) - default 3 (options: 1, 3, 5)
set_scores (json) - null
final_score (string) - null

-- Navigation
notes (text) - nullable
next_match_id (FK) - nullable
winner_advances_to (enum) - nullable

timestamps
```

## Quy Trình Tạo Trận Đấu

1. Chủ giải truy cập tab "🎾 Quản Lý Trận Đấu"
2. Nhấn nút "➕ Tạo Trận Mới"
3. Modal tương ứng hiện ra
4. Điền thông tin:
   - Chọn Nội dung thi đấu
   - Danh sách VĐV tự động load
   - Chọn VĐV 1 & VĐV 2
   - Chọn Ngày thi đấu
   - (Tuỳ chọn) Chọn vòng, sân, giờ, số set
5. Nhấn "➕ Tạo Trận"
6. Form gửi AJAX request
7. Backend xác thực & tạo record
8. Thành công → reload trang & lưu tab hiện tại
9. Trận đấu hiện trong bảng danh sách

## Danh Sách Trận Đấu

- Bảng hiển thị tất cả trận đấu của giải
- Có thể lọc theo nội dung
- Load dữ liệu via AJAX khi trang load hoặc thay đổi filter
- Từng trận có nút:
  - "✏️ Sửa" - chưa implement (placeholder)
  - "🗑️ Xóa" - xóa trận, confirm trước

## Statuses

- **scheduled** - 📋 Lên lịch
- **ready** - ✅ Sẵn sàng
- **in_progress** - ⏱️ Đang diễn ra
- **completed** - 🏁 Hoàn thành
- **cancelled** - ❌ Hủy
- **postponed** - ⏸️ Hoãn lại
- **bye** - 👋 Bye

## Validation

**Bắt buộc:**
- `category_id`: exists:tournament_categories,id
- `athlete1_id`: exists:tournament_athletes,id
- `athlete2_id`: exists:tournament_athletes,id
- `match_date`: date format

**Tuỳ chọn:**
- `round_id`: exists:rounds,id
- `court_id`: exists:courts,id
- `match_time`: H:i format
- `best_of`: 1, 3, hoặc 5
- `notes`: string

## JavaScript Functions

### Modal Control
- `openCreateMatchModal()` - Mở modal tạo trận
- `closeCreateMatchModal()` - Đóng modal

### Match List
- `filterMatches()` - Load trận theo category filter
- `loadMatches(matches)` - Render bảng trận đấu
- `deleteMatch(matchId)` - Xóa trận

### Athletes Loading
- `loadAthletesForCategory()` - Load VĐV theo category, gọi AJAX
- Event listener trên `matchCategoryId` select để tự động load khi chọn category

## Cải Tiến Tương Lai

1. **Edit Match** - Chỉnh sửa chi tiết trận đấu
2. **Score Entry** - Nhập kết quả set, tính điểm
3. **Match Status Update** - Thay đổi status (scheduled → ready → in_progress → completed)
4. **Bracket Auto-Generate** - Tự động tạo trận dựa trên vòng đấu
5. **Match Draw/Randomize** - Bốc thăm VĐV tạo trận
6. **Export Matches** - Xuất danh sách trận đấu

## Testing

### API Endpoints

```bash
# Get athletes by category
GET /homeyard/tournaments/1/athletes?category_id=1&approved=1

# List matches (AJAX)
GET /homeyard/tournaments/1/matches?category_id=1

# Create match
POST /homeyard/tournaments/1/matches
Content-Type: application/json
{
  "category_id": 1,
  "athlete1_id": 5,
  "athlete2_id": 6,
  "match_date": "2025-01-20",
  "round_id": 1,
  "court_id": 3,
  "match_time": "14:00",
  "best_of": 3,
  "notes": "Semi-final match"
}

# Delete match
DELETE /homeyard/tournaments/1/matches/10
```

## Files Modified

1. `/resources/views/home-yard/dashboard.blade.php` - Tab UI + Modal + JavaScript
2. `/app/Http/Controllers/Front/HomeYardTournamentController.php` - 3 phương thức mới
3. `/routes/web.php` - 4 route mới

## Model: MatchModel

**File:** `/app/Models/MatchModel.php`

```php
// Fillable
'tournament_id', 'category_id', 'round_id', 'court_id', 'group_id',
'match_number', 'bracket_position',
'athlete1_id', 'athlete1_name', 'athlete1_score',
'athlete2_id', 'athlete2_name', 'athlete2_score',
'winner_id', 'match_date', 'match_time', 'actual_start_time', 'actual_end_time',
'status', 'best_of', 'set_scores', 'final_score', 'notes',
'next_match_id', 'winner_advances_to'

// Relationships
tournament(), category(), round(), court(), group(),
athlete1(), athlete2(), winner(), nextMatch()

// Methods
isCompleted(), isLive(), isScheduled(), getLoserIdAttribute(), start(), end()
```
