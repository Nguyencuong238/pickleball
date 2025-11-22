# Bảng Xếp Hạng - Quick Reference

## 🚀 Bắt Đầu Nhanh

### Kiểm Tra Dữ Liệu
```php
// Kiểm tra GroupStanding
$ php artisan tinker
>>> $standings = \App\Models\GroupStanding::with('athlete', 'group')->get();
>>> $standings->each(fn($s) => echo "$s->athlete->athlete_name: {$s->points}pts\n");
```

### Test API
```bash
# Tất cả VĐV
curl http://localhost/homeyard/tournaments/1/rankings

# Lọc category
curl "http://localhost/homeyard/tournaments/1/rankings?category_id=2"

# Lọc group
curl "http://localhost/homeyard/tournaments/1/rankings?category_id=2&group_id=5"
```

---

## 📁 File Structure

```
pickleball_booking/
├── LEADERBOARD_IMPLEMENTATION.md    ← Tài liệu chính
├── LEADERBOARD_FILTER_GUIDE.md      ← Hướng dẫn bộ lọc
├── LEADERBOARD_FIXES.md             ← Chi tiết sửa đổi
├── LEADERBOARD_VERIFICATION.md      ← Test cases
├── LEADERBOARD_QUICK_REFERENCE.md   ← File này
│
├── app/Http/Controllers/Front/
│   └── HomeYardTournamentController.php    ← getRankings() method
│
├── routes/
│   └── web.php                       ← rankings route
│
└── resources/views/home-yard/
    └── dashboard.blade.php           ← UI + JavaScript
```

---

## 🔧 Development Workflow

### Khi Thêm Feature Mới
```
1. Update Controller (Backend)
   - getRankings() method
   - Validation & Authorization

2. Test API
   - curl hoặc Postman
   - Kiểm tra JSON response

3. Update View (Frontend)
   - HTML layout
   - JavaScript functions

4. Test UI
   - Filter, render, print, export
   - Responsive design

5. Documentation
   - Update .md files
   - Code comments
```

### Khi Debug
```
1. Kiểm tra Console (F12)
   - Network tab → API response
   - Console tab → JavaScript errors

2. Kiểm tra Database
   - group_standings records
   - Data integrity

3. Kiểm tra Server Logs
   - storage/logs/laravel.log
   - API errors

4. Use Tinker
   - php artisan tinker
   - Query database directly
```

---

## 💡 Common Issues

| Issue | Giải Pháp |
|-------|----------|
| Dropdown bảng không cập nhật | Gọi `updateGroupFilter()` |
| Dữ liệu trộn lẫn | Kiểm tra `data-category-id` attribute |
| API trả về lỗi 500 | Kiểm tra server logs |
| Xếp hạng sai | Verify sorting logic (Points > Wins > GameDiff) |
| Print không hoạt động | Check browser print settings |
| CSV trống | Kiểm tra tableBody có data không |

---

## 🎨 UI Components

### Dropdown Filter
```blade
<select id="filterCategory" onchange="updateGroupFilter(); loadRankings()">
  <option value="">-- Tất cả nội dung --</option>
  @foreach($tournament->categories as $cat)
    <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
  @endforeach
</select>
```

### Statistics Cards
```blade
<div style="background: linear-gradient(...); color: white;">
  <div>🥇 VĐV Hạng 1</div>
  <div id="topAthlete">-</div>
</div>
```

### Rankings Table
```blade
<table>
  <thead>...</thead>
  <tbody id="rankingsTableBody">
    <!-- Render by JavaScript -->
  </tbody>
</table>
```

---

## 🔗 API Endpoints

| Endpoint | Method | Params | Returns |
|----------|--------|--------|---------|
| `/homeyard/tournaments/{id}/rankings` | GET | `category_id`, `group_id` | `{success, rankings, total_matches, total_athletes}` |

### Response Format
```json
{
  "success": true,
  "rankings": [
    {
      "rank": 1,
      "athlete_name": "Nguyễn Văn A",
      "category_name": "Nam đơn 18+",
      "matches_played": 5,
      "matches_won": 5,
      "points": 15,
      "games_differential": 110,
      ...
    }
  ],
  "total_matches": 10,
  "total_athletes": 32
}
```

---

## 📊 Sorting Logic

```javascript
// Sắp xếp theo:
1. Points (Descending)      // Cao nhất trước
2. Wins (Descending)        // Nếu điểm bằng
3. Games Diff (Descending)  // Nếu vẫn bằng

// Example:
Athlete A: 12pts, 4 wins, +50 games
Athlete B: 12pts, 4 wins, +45 games
→ A lên, B xuống (vì +50 > +45)
```

---

## 🧮 Points System

```
Win:  +3 points
Loss: +0 points
Draw: +0 points (nếu có)

Ví dụ:
5 trận thắng = 15 điểm
4 trận thắng, 1 thua = 12 điểm
```

---

## 🔄 Data Flow

```
┌─────────────────┐
│ User selects    │
│ Category/Group  │
└────────┬────────┘
         │
         ↓
┌─────────────────┐
│ updateGroupFilter()  │ ← Lọc dropdown
│ loadRankings()  │ ← Load API
└────────┬────────┘
         │
         ↓
┌──────────────────────┐
│ GET /rankings?...    │
│ (API call)           │
└────────┬─────────────┘
         │
         ↓
┌──────────────────────┐
│ JSON Response        │
│ {rankings: [...]}    │
└────────┬─────────────┘
         │
         ↓
┌──────────────────────┐
│ renderRankingsTable()│
│ updateRankingsStats()│
└────────┬─────────────┘
         │
         ↓
┌──────────────────────┐
│ Display Table        │
│ Update Statistics    │
└──────────────────────┘
```

---

## ✨ Key Functions

### JavaScript
```javascript
updateGroupFilter()      // Lọc dropdown bảng
loadRankings()          // Load data từ API
renderRankingsTable()   // Render HTML table
updateRankingsStats()   // Cập nhật stats cards
printRankings()         // In bảng
exportRankingsCSV()     // Xuất CSV
```

### PHP (Controller)
```php
getRankings()           // API method
// Returns JSON with rankings sorted by:
// 1. Points DESC
// 2. Wins DESC
// 3. Games Differential DESC
```

---

## 📝 Notes

- **Tournament ID**: Lấy từ URL hoặc session
- **Authorization**: Kiểm tra role `home_yard`
- **Database**: GroupStanding model dùng soft delete? Không
- **Performance**: Có pagination? Không, load tất cả (có thể optimize later)
- **Caching**: Không dùng cache (realtime data)

---

## 🐛 Debug Tips

```javascript
// Kiểm tra data
console.log('Rankings:', rankings);
console.log('Category ID:', document.getElementById('filterCategory').value);
console.log('Group ID:', document.getElementById('filterGroup').value);

// Kiểm tra function
console.log('updateGroupFilter:', typeof updateGroupFilter);
console.log('loadRankings:', typeof loadRankings);
```

```bash
# Server logs
tail -f storage/logs/laravel.log

# Database
php artisan tinker
>>> DB::table('group_standings')->where('group_id', 5)->get();
```

---

## 🚀 Performance Tips

- Lọc phía client (dropdown) → Nhanh
- Load API khi thay đổi → 1 request
- Render JS table → Không reload trang
- Export CSV → Client-side, không hit server

---

## 📱 Mobile Support

- Responsive table (overflow-x: auto)
- Touch-friendly selects
- Print-optimized
- Works on iOS/Android

---

## 🔐 Security

- Authorization check: `$this->authorize('view', $tournament)`
- Input validation: category_id, group_id
- No SQL injection (using Eloquent)
- CSRF token: Implicit (GET request)

---

## 📞 Support

Xem thêm tại:
- `LEADERBOARD_IMPLEMENTATION.md` - Full documentation
- `LEADERBOARD_FILTER_GUIDE.md` - Filter guide
- `LEADERBOARD_FIXES.md` - Fix details
- `LEADERBOARD_VERIFICATION.md` - Test cases

---

**Last Updated:** 2025-11-22
**Version:** 1.1
