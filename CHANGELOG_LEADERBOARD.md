# 📊 Bảng Xếp Hạng VĐV - Nhật Ký Thay Đổi

## ✨ Đã Thêm Ngày 21/01/2025

### 1. **UI/Frontend**
📁 `resources/views/home-yard/dashboard.blade.php`

#### Thêm Tab Mới
- ID: `rankings` (Tab 6)
- Tiêu đề: 🏅 Bảng Xếp Hạng
- Alert thông tin: Chi tiết xếp hạng theo thống kê
- Bộ lọc nội dung: Nút chọn category

#### Bảng Dữ Liệu Dynamic (10 cột)
```html
<table id="leaderboardBody">
  <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <!-- 10 cột với biểu tượng emoji -->
    🏆 | VĐV | Nội dung | 🎾 Trận | ✅ Thắng | ❌ Thua | 📊 Tỷ lệ | 🔤 Set | ➕ Hiệu số | ⭐ Điểm
  </thead>
  <tbody id="leaderboardBody">
    <!-- Dữ liệu được load động từ API -->
  </tbody>
</table>
```

#### JavaScript Functions
- `loadLeaderboard(categoryId='all')` - Load dữ liệu từ API
- `filterLeaderboard(categoryId)` - Lọc theo nội dung, cập nhật UI

#### Tính Năng
- ✅ Xếp hạng tự động (cao → thấp theo điểm)
- ✅ Tính toán tỷ lệ thắng (%)
- ✅ Hiệu số set (+/-)
- ✅ Màu sắc: xanh (thắng), đỏ (thua), vàng (điểm)
- ✅ Bộ lọc category động
- ✅ Responsive design (overflow-x: auto)

---

### 2. **Backend/Controller**
📁 `app/Http/Controllers/Front/HomeYardTournamentController.php`

#### Phương Thức Mới
```php
public function getLeaderboard(Request $request, Tournament $tournament)
{
    // Xác thực quyền truy cập
    $this->authorize('view', $tournament);
    
    // Lấy VĐV và lọc theo category (nếu có)
    // Tính thống kê từ bảng matches (chỉ trận completed)
    // Sắp xếp: điểm (DESC) → tỷ lệ (DESC) → trận (DESC) → hiệu số (DESC)
    // Return JSON response
}
```

#### Logic Tính Toán
```php
foreach ($athleteMatches as $match) {
    // Xác định người thắng (athlete1_score vs athlete2_score)
    // Cộng 3 điểm nếu thắng
    // Tính set_won, set_lost
    // Tính win_rate = (wins / total) * 100
    // Tính sets_differential = won - lost
}

// Sắp xếp theo:
sortByDesc([
    'total_points',           // 1. Điểm cao nhất
    'win_rate',               // 2. Tỷ lệ thắng cao nhất
    'matches_won',            // 3. Số trận thắng
    'sets_differential'       // 4. Hiệu số set
])
```

#### Response JSON
```json
{
  "success": true,
  "athletes": [
    {
      "id": 1,
      "athlete_name": "Nguyễn Văn An",
      "email": "...",
      "category_name": "Nam đơn 18+",
      "matches_played": 5,
      "matches_won": 5,
      "matches_lost": 0,
      "win_rate": 100.0,
      "sets_won": 10,
      "sets_lost": 0,
      "sets_differential": 10,
      "total_points": 15
    }
  ]
}
```

---

### 3. **Route/API**
📁 `routes/web.php`

#### Thêm Route
```php
Route::get('tournaments/{tournament}/leaderboard', 
    [HomeYardTournamentController::class, 'getLeaderboard'])
    ->name('tournaments.leaderboard');
```

#### Cách Gọi API
```javascript
// Tất cả VĐV
GET /homeyard/tournaments/1/leaderboard

// VĐV theo nội dung
GET /homeyard/tournaments/1/leaderboard?category_id=2
```

---

## 🎨 Style & Design

### Bảng Header
- Gradient: `#667eea` → `#764ba2` (tím)
- Chữ: Trắng, font-weight: bold
- Border: 2px solid #667eea

### Cột Dữ Liệu
- Padding: 12px
- Border-bottom: 1px solid #ddd
- Căn chỉnh: 
  - VĐV: left (trái)
  - Số liệu: center (giữa)

### Màu Sắc
- ✅ Thắng: #10B981 (xanh lá)
- ❌ Thua: #EF4444 (đỏ)
- 📊 Tỷ lệ: #667eea (tím)
- ⭐ Điểm: #fbbf24 (vàng)

### Bộ Lọc
- Border: 2px solid #e0e0e0
- Active: gradient #667eea → #764ba2 (trắng chữ)
- Inactive: white bg, #666 text
- Border-radius: 20px (pill shape)

---

## 📊 Xếp Hạng - Ví Dụ Cụ Thể

### Input: 4 VĐV trong giải
```
VĐV A (category: Nam đơn 18+)
├─ Trận 1: Thắng 11-7, 11-9 (2 set)
├─ Trận 2: Thắng 11-8, 11-10 (2 set)
└─ Kết quả: 2 trận, 4 set thắng, 0 set thua → 6 điểm, 100% tỷ lệ

VĐV B (category: Nam đơn 18+)
├─ Trận 1: Thua 7-11, 9-11 (0 set)
├─ Trận 2: Thắng 11-8, 11-9 (2 set)
└─ Kết quả: 1 trận, 2 set thắng, 2 set thua → 3 điểm, 50% tỷ lệ

VĐV C (category: Nữ đơn 18+)
├─ Trận 1: Thắng 11-7, 11-9 (2 set)
└─ Kết quả: 1 trận, 2 set thắng, 0 set thua → 3 điểm, 100% tỷ lệ

VĐV D (category: Nam đôi)
├─ Trận 1: Thua 8-11, 9-11 (0 set)
└─ Kết quả: 1 trận, 0 set thắng, 2 set thua → 0 điểm, 0% tỷ lệ
```

### Output: Bảng Xếp Hạng
```
Lọc: "🏆 Tất cả" (tất cả VĐV)
┌─────┬──────────────┬──────────────────┬───────┬────────┬────────┬────────┬─────────┬─────────┬──────────┐
│ Hạng│ VĐV (Email)  │ Nội dung         │ Trận  │ Thắng  │ Thua   │ Tỷ lệ  │ Set     │ Hiệu số │ Điểm    │
├─────┼──────────────┼──────────────────┼───────┼────────┼────────┼────────┼─────────┼─────────┼──────────┤
│ 🥇  │ Nguyễn Văn A │ Nam đơn 18+       │ 2     │ 2 (🟢) │ 0 (🔴) │ 100%   │ 4 - 0   │ +4 (🟢) │ 6 (⭐) │
│ 🥈  │ Vũ Thị C     │ Nữ đơn 18+       │ 1     │ 1 (🟢) │ 0 (🔴) │ 100%   │ 2 - 0   │ +2 (🟢) │ 3 (⭐) │
│ 🥉  │ Bùi Văn B    │ Nam đơn 18+       │ 2     │ 1 (🟢) │ 1 (🔴) │ 50%    │ 2 - 2   │  0 (⚪) │ 3 (⭐) │
│ #4  │ Hà Văn D     │ Nam đôi          │ 1     │ 0 (🟢) │ 1 (🔴) │ 0%     │ 0 - 2   │ -2 (🔴) │ 0 (⭐) │
└─────┴──────────────┴──────────────────┴───────┴────────┴────────┴────────┴─────────┴─────────┴──────────┘
```

### Sắp Xếp Ưu Tiên
```
1️⃣  Điểm: 6 > 3 > 3 > 0
    → A đứng đầu (6 điểm)

2️⃣  Tỷ lệ: (nếu điểm bằng)
    → C (100%) > B (50%)

3️⃣  Trận thắng: (nếu cả 2 đều bằng)
    → (chọn ai nào trước tương ứng thứ tự ID)

4️⃣  Hiệu số: (nếu tất cả bằng)
    → (sử dụng làm tiêu chí cuối cùng)
```

---

## 🔧 Tech Stack

| Thành phần | Công nghệ | Chi tiết |
|-----------|-----------|---------|
| **View** | Blade Template | Laravel template engine |
| **JavaScript** | Vanilla JS (ES6) | Fetch API, DOM manipulation |
| **CSS** | Inline styles | Gradient, flexbox, grid |
| **Backend** | PHP + Laravel | Controller, Authorization |
| **Database** | Eloquent ORM | Query optimization |
| **API** | REST JSON | Standard HTTP GET |

---

## ✅ Kiểm Tra & Validation

### Frontend
- ✅ Bảng hiển thị 10 cột
- ✅ Dữ liệu load từ API
- ✅ Bộ lọc hoạt động
- ✅ Màu sắc đúng
- ✅ Responsive trên mobile
- ✅ Xếp hạng tính đúng

### Backend
- ✅ Authorize 'view' tournament
- ✅ Filter category_id
- ✅ Calculate stats từ matches
- ✅ Sort by multiple fields
- ✅ Return valid JSON

### API
- ✅ Status 200 thành công
- ✅ Status 403 nếu không quyền
- ✅ Status 500 nếu lỗi server

---

## 🚀 Cách Sử Dụng

### Cho Admin/Home Yard Owner:
1. Vào dashboard giải đấu
2. Click tab "🏅 Bảng xếp hạng"
3. Chọn nội dung hoặc "🏆 Tất cả"
4. Xem xếp hạng tự động cập nhật

### Điều kiện bắt buộc:
- ✅ Phải tạo giải đấu
- ✅ Phải đăng ký VĐV
- ✅ Phải tạo trận đấu
- ✅ Phải set trận thành `status='completed'`
- ✅ Phải có `athlete1_score`, `athlete2_score`

---

## 📝 Ghi Chú Quan Trọng

### ⚠️ Chỉ tính trận `completed`
```php
->where('status', 'completed')
```
Các trận `scheduled`, `in_progress`, `cancelled` được bỏ qua.

### ⚠️ Người thắng = điểm cao hơn
```php
if ($match->athlete1_score > $match->athlete2_score) {
    // athlete1 thắng
}
```
Dựa trên số set, không phải tổng điểm trong set.

### ⚠️ 3 điểm/trận thắng
```php
$totalPoints += 3; // per win
```
Hòa không tính, có thể mở rộng sau.

---

## 🎯 Hướng Phát Triển

- [ ] Cache kết quả (Redis)
- [ ] Xuất Excel bảng
- [ ] In bảng (print CSS)
- [ ] Xếp hạng theo thời gian (timeline)
- [ ] Head-to-head: VĐV A vs VĐV B
- [ ] Elo rating thay vì điểm
- [ ] Thống kê VĐV (biểu đồ)
- [ ] Công bố kết quả (email)

---

**Version:** 1.0  
**Status:** ✅ COMPLETED  
**Date:** 2025-01-21  
**Author:** AI Assistant
