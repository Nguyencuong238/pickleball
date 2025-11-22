# Bảng Xếp Hạng VĐV - Leaderboard Implementation

## Tổng Quan
Đã triển khai bảng xếp hạng động cho giải pickleball với các chức năng:
- Xếp hạng theo: **Điểm > Trận thắng > Hiệu số game**
- Lọc theo nội dung thi đấu (category)
- Lọc theo bảng đấu (group)
- In bảng xếp hạng
- Xuất CSV

## Các File Đã Cập Nhật

### 1. Frontend - Views
**File:** `/resources/views/home-yard/dashboard.blade.php`

**Thay đổi:**
- Cập nhật tab "🏅 Bảng xếp hạng VĐV" (TAB 6)
- Thay thế dữ liệu tĩnh bằng dữ liệu động
- Thêm bộ lọc thông minh theo nội dung và bảng đấu
  - Dropdown bảng tự cập nhật khi thay đổi nội dung
  - Mỗi bảng hiển thị tên nội dung: `Bảng B (Nam đơn 18+)`
  - Tự động reset bảng = "-- Tất cả bảng --" khi thay đổi nội dung
- Thêm hiển thị thống kê (VĐV hạng 1, tổng trận, tổng VĐV)
- Bảng xếp hạng hiển thị:
  - Xếp Hạng (với huy chương 🥇🥈🥉 cho top 3)
  - Tên VĐV
  - Nội Dung thi đấu
  - Số Trận
  - Trận Thắng (💚 xanh)
  - Trận Thua (❌ đỏ)
  - Điểm (⭐ vàng nổi bật)
  - Set W/L
  - Hiệu Số Game (💙 xanh nhạt)
  - % Thắng

**JavaScript Functions:**
- `updateGroupFilter()` - Cập nhật dropdown bảng dựa trên category (MỚI)
- `loadRankings()` - Load dữ liệu từ API
- `renderRankingsTable(rankings)` - Render bảng xếp hạng
- `updateRankingsStats(data)` - Cập nhật thống kê
- `printRankings()` - In bảng
- `exportRankingsCSV()` - Xuất CSV

**HTML Improvements:**
- Mỗi option group có attribute: `data-category-id="{{ $group->category_id }}`
- Dropdown group hiển thị: `{{ $group->group_name }} ({{ $group->category->category_name }})`

### 2. Routes
**File:** `/routes/web.php`

**Thêm:**
```php
Route::get('tournaments/{tournament}/rankings', [HomeYardTournamentController::class, 'getRankings'])
    ->name('tournaments.rankings.api');
```

### 3. Controller
**File:** `/app/Http/Controllers/Front/HomeYardTournamentController.php`

**Thêm method:**
```php
public function getRankings(Tournament $tournament, Request $request)
```

**Chức năng:**
- Lấy dữ liệu standings từ `group_standings` table
- Sắp xếp theo: Điểm (DESC) > Trận thắng (DESC) > Hiệu số game (DESC)
- Hỗ trợ lọc theo category_id và group_id
- Trả về JSON với:
  - `rankings` - Mảng dữ liệu VĐV xếp hạng
  - `total_matches` - Số trận đã hoàn thành
  - `total_athletes` - Tổng số VĐV

## Cấu Trúc Dữ Liệu Trả Về

```json
{
  "success": true,
  "rankings": [
    {
      "rank": 1,
      "athlete_id": 123,
      "athlete_name": "Nguyễn Văn An",
      "category_name": "Nam đơn 18+",
      "matches_played": 5,
      "matches_won": 5,
      "matches_lost": 0,
      "points": 15,
      "win_rate": 100.0,
      "sets_won": 10,
      "sets_lost": 0,
      "sets_differential": 10,
      "games_won": 110,
      "games_lost": 0,
      "games_differential": 110,
      "is_advanced": true
    },
    ...
  ],
  "total_matches": 10,
  "total_athletes": 32,
  "filter": {
    "category_id": null,
    "group_id": null
  }
}
```

## Điểm Scoring System
- **Trận thắng:** +3 điểm
- **Trận thua:** +0 điểm
- Tính từ: `group_standings.points` (được cập nhật khi match hoàn thành)

## Xếp Hạng (Ranking)
Ưu tiên:
1. **Điểm** (Descending) - Cao nhất
2. **Trận Thắng** (Descending) - Nếu điểm bằng nhau
3. **Hiệu Số Game** (Descending) - Nếu vẫn bằng nhau

Công thức Hiệu Số Game: `games_won - games_lost`

## Chế Độ Xem & Bộ Lọc

### Lọc Theo Nội Dung (Category)
- Chọn nội dung → Dropdown bảng tự cập nhật chỉ hiện bảng của nội dung đó
- Dropdown bảng tự reset = "-- Tất cả bảng --"
- Giúp tách biệt dữ liệu: Bảng B của "Nam đơn" khác Bảng B của "Nữ đơn"

### Lọc Theo Bảng (Group)
- Chỉ hiển thị VĐV của bảng được chọn
- Danh sách bảng hiển thị nội dung: `Bảng A (Nam đơn 18+)`
- Tránh nhầm lẫn giữa các bảng từ nội dung khác

### Sử Dụng Kết Hợp
```
Chọn "Nam đơn 18+" → Bảng dropdown hiển thị Bảng A, B, C (nam đơn)
Chọn "Bảng B" → Xem kết quả xếp hạng Bảng B của "Nam đơn 18+"
```

## Hỗ Trợ

### Điều kiện sử dụng:
1. User phải đăng nhập với role `home_yard`
2. Giải đấu phải tồn tại
3. Phải có quyền xem giải (authorization)

### Dữ liệu cần có:
- Ít nhất 1 match đã hoàn thành
- GroupStanding records (tạo khi bốc thăm)
- TournamentAthlete, TournamentCategory, Group

## API Endpoint

**URL:** `/homeyard/tournaments/{tournament}/rankings`
**Method:** GET
**Params:**
- `category_id` (optional) - ID nội dung thi đấu
- `group_id` (optional) - ID bảng đấu

**Response:** JSON

## Changelog

### v1.1 - Cải Tiến Bộ Lọc (Latest)
- ✅ Thêm hàm `updateGroupFilter()` để lọc dropdown bảng theo category
- ✅ Dropdown bảng hiển thị nội dung tương ứng: `Bảng B (Nam đơn 18+)`
- ✅ Tự động reset bảng khi thay đổi category
- ✅ Tránh hiện tượng "Bảng B vs C bị gộp lại"
- ✅ Khởi tạo filter khi page load

### v1.0 - Phiên Bản Ban Đầu
- ✅ Triển khai bảng xếp hạng động
- ✅ Xếp hạng theo: Điểm > Trận thắng > Hiệu số game
- ✅ Lọc theo category, group
- ✅ In bảng, Xuất CSV
- ✅ Hiển thị thống kê

## Testing
1. Tạo giải đấu
2. Thêm nội dung thi đấu (VD: Nam đơn, Nữ đơn)
3. Thêm VĐV vào các nội dung khác nhau
4. Tạo bảng đấu và bốc thăm
   - VD: Tạo Bảng A, B (Nam đơn) và Bảng A, B (Nữ đơn)
5. Tạo và hoàn thành các trận đấu
6. Vào tab "🏅 Bảng xếp hạng VĐV" để xem kết quả

### Test Lọc
```
✓ Chọn "Nam đơn" → Dropdown bảng hiển thị A, B (Nam đơn)
✓ Chọn "Nữ đơn" → Dropdown bảng tự cập nhật hiển thị A, B (Nữ đơn)
✓ Chọn "Bảng B (Nam đơn)" → Kết quả chỉ hiển thị VĐV bảng B Nam
✓ Reset filter → Hiển thị tất cả VĐV
```
