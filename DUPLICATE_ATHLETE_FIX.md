# ✅ Fix: Duplicate Athlete Check

## Issue
```
❌ Lỗi khi thêm vận động viên: SQLSTATE[23000]: Integrity constraint violation: 1062
Duplicate entry '19-Kỳ Danh' for key 'tournament_athletes.tournament_athletes_tournament_id_athlete_name_unique'
```

## Root Cause
- Database có UNIQUE constraint: `(tournament_id, athlete_name)`
- Đang cố thêm vận động viên với tên giống lần trước
- VĐV "Kỳ Danh" đã tồn tại trong giải 19

## Fix Applied

**File**: `app/Http/Controllers/Front/HomeYardTournamentController.php` (dòng 223-237)

```php
// Check if athlete already exists in this tournament
$existingAthlete = TournamentAthlete::where('tournament_id', $tournament->id)
    ->where('athlete_name', $request->athlete_name)
    ->first();

if ($existingAthlete) {
    if ($request->wantsJson() || ...) {
        return response()->json([
            'success' => false,
            'message' => "Vận động viên '{$request->athlete_name}' đã tồn tại trong giải đấu này!"
        ], 422);
    }
    return redirect()->back()->with('error', '...');
}
```

## Behavior

### Before (Old):
```
User submit → Database error → HTML error page → JSON parse fail
```

### After (New):
```
User submit → Check if exists → If yes: Alert "đã tồn tại" → Modal stays open
            → If no: Create → Success
```

## User Experience

### Scenario 1: VĐV chưa tồn tại
```
1. Click "Thêm VĐV"
2. Enter: "Nguyễn Văn A"
3. Submit
4. ✅ Success: "Vận động viên đã được thêm thành công!"
```

### Scenario 2: VĐV đã tồn tại
```
1. Click "Thêm VĐV"
2. Enter: "Kỳ Danh" (đã tồn tại)
3. Submit
4. ❌ Error: "Vận động viên 'Kỳ Danh' đã tồn tại trong giải đấu này!"
5. Modal vẫn mở → User có thể thử tên khác
```

## Database

### Unique Constraint
```sql
-- Bảng tournament_athletes
ALTER TABLE tournament_athletes ADD UNIQUE KEY `tournament_athletes_tournament_id_athlete_name_unique` (`tournament_id`, `athlete_name`);
```

Meaning:
- Có thể có nhiều "Kỳ Danh" trong các giải khác nhau ✅
- Nhưng trong 1 giải (tournament_id) chỉ có 1 người tên "Kỳ Danh" ❌

## Rules

| Scenario | Allowed? | Why |
|----------|----------|-----|
| Kỳ Danh in tournament 19 | ❌ No | Already exists |
| Kỳ Danh in tournament 20 | ✅ Yes | Different tournament |
| Kỳ Danh (cách viết khác) | ✅ Yes | Different name |
| Nguyễn Văn A in tournament 19 | ✅ Yes | Doesn't exist |

## Testing

### Test 1: Add new athlete
```
1. Modal: Chọn category + "Test User 1"
2. Submit
3. ✅ Success
```

### Test 2: Add duplicate
```
1. Modal: Chọn category + "Test User 1" (cùng tên)
2. Submit
3. ❌ Alert: "đã tồn tại"
4. Modal vẫn mở
5. Đổi tên: "Test User 2"
6. Submit lại
7. ✅ Success
```

---

**Fix Applied**: Nov 21, 2025
**Status**: ✅ RESOLVED
