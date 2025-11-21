# Tóm tắt Thay đổi: Thêm Vận Động Viên qua Modal

**Ngày**: November 21, 2025
**Tính năng**: Thêm modal để chủ giải đấu thêm vận động viên trực tiếp
**Trạng thái**: ✅ HOÀN THÀNH

---

## 📝 Tổng quan

Khi chủ giải click nút "➕ Thêm VĐV" trong tab "Quản lý VĐV", sẽ mở modal để:
1. Chọn nội dung thi đấu (category) - **bắt buộc**
2. Nhập tên vận động viên - **bắt buộc**
3. Nhập email - **tùy chọn**
4. Nhập số điện thoại - **tùy chọn**

VĐV được thêm sẽ tự động có trạng thái `approved` (đã duyệt).

---

## 📦 Files Thay đổi

### 1. `/resources/views/home-yard/dashboard.blade.php`
**Dòng 577**: Button thêm onclick handler
```html
<button class="btn btn-primary btn-sm" id="addAthleteBtn" onclick="openAddAthleteModal()">➕ Thêm VĐV</button>
```

**Dòng 920-965**: Modal HTML với form
- Dropdown category
- Input athlete_name
- Input email
- Input phone
- Buttons (Hủy, Thêm VĐV)

**Dòng 1217-1303**: JavaScript functions
- `openAddAthleteModal()` - mở modal
- `closeAddAthleteModal()` - đóng modal
- Click outside modal để đóng
- Form submission handler với fetch API

### 2. `/app/Http/Controllers/Front/HomeYardTournamentController.php`
**Dòng 213-257**: Sửa phương thức `addAthlete()`
```php
public function addAthlete(Request $request, Tournament $tournament)
{
    // Validation
    $request->validate([
        'athlete_name' => 'required|string|max:255',
        'email' => 'nullable|email',
        'phone' => 'nullable|string|max:20',
        'category_id' => 'required|exists:tournament_categories,id',  // ⭐ Thêm
    ]);

    try {
        $athlete = TournamentAthlete::create([
            'tournament_id' => $tournament->id,
            'category_id' => $request->category_id,              // ⭐ Thêm
            'user_id' => auth()->id(),
            'athlete_name' => $request->athlete_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => 'approved',                              // ⭐ Set approved
        ]);

        // Handle JSON (AJAX)
        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Vận động viên đã được thêm thành công',
                'athlete' => $athlete
            ]);
        }

        return redirect()->back()->with('success', 'Vận động viên đã được thêm thành công.');
    } catch (\Exception $e) {
        Log::error('Add athlete error: ' . $e->getMessage());
        
        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thêm vận động viên: ' . $e->getMessage()
            ], 422);
        }

        return redirect()->back()->with('error', 'Lỗi khi thêm vận động viên.');
    }
}
```

**Thay đổi chính**:
1. ✅ Thêm validation `category_id` (bắt buộc)
2. ✅ Ghi `category_id` vào DB
3. ✅ Set `status = 'approved'` mặc định
4. ✅ Handle JSON responses cho AJAX
5. ✅ Error handling với try-catch

---

## 🔗 Route sử dụng

- **Method**: POST
- **Route**: `/homeyard/tournaments/{tournament}/athletes`
- **Name**: `homeyard.tournaments.athletes.add`
- **Middleware**: `auth`, `role:home_yard`

---

## 💾 Database

### Bảng: `tournament_athletes`
Các cột được ghi khi thêm VĐV:

| Cột | Kiểu | Giá trị | Ghi chú |
|-----|------|--------|--------|
| `tournament_id` | FK | `$tournament->id` | ID giải đấu |
| `category_id` | FK | `$request->category_id` | ⭐ YÊU CẦU (nội dung thi đấu) |
| `user_id` | FK | `auth()->id()` | ID chủ giải |
| `athlete_name` | string | `$request->athlete_name` | ⭐ YÊU CẦU |
| `email` | string | `$request->email` | Nullable |
| `phone` | string | `$request->phone` | Nullable |
| `status` | enum | `'approved'` | ⭐ Luôn = 'approved' |
| `created_at` | timestamp | NOW() | Tự động |
| `updated_at` | timestamp | NOW() | Tự động |

---

## 🧪 Testing

### Manual Test Steps:
1. Đăng nhập với tài khoản home_yard
2. Vào dashboard một giải đấu (phải có categories)
3. Scroll tới tab "Quản lý VĐV"
4. Click nút "➕ Thêm VĐV"
5. Modal sẽ hiện lên
6. Chọn category, nhập tên VĐV
7. Click "Thêm VĐV"
8. Kiểm tra:
   - ✅ Modal đóng
   - ✅ Thông báo thành công
   - ✅ Trang reload
   - ✅ VĐV hiển thị trong danh sách với status "Đã phê duyệt"

### API Test:
```bash
curl -X POST \
  'http://localhost/homeyard/tournaments/1/athletes' \
  -H 'Content-Type: application/json' \
  -H 'X-CSRF-TOKEN: YOUR_TOKEN' \
  -H 'X-Requested-With: XMLHttpRequest' \
  -d '{
    "category_id": 1,
    "athlete_name": "Nguyễn Văn A",
    "email": "nguyena@example.com",
    "phone": "0123456789"
  }'
```

Expected Response:
```json
{
  "success": true,
  "message": "Vận động viên đã được thêm thành công",
  "athlete": {
    "id": 123,
    "tournament_id": 1,
    "category_id": 1,
    "athlete_name": "Nguyễn Văn A",
    "email": "nguyena@example.com",
    "phone": "0123456789",
    "status": "approved",
    "created_at": "2025-11-21T10:30:00.000000Z",
    "updated_at": "2025-11-21T10:30:00.000000Z"
  }
}
```

---

## ⚠️ Lưu ý quan trọng

1. **Category là bắt buộc**: VĐV PHẢI được gán vào 1 nội dung thi đấu cụ thể. Nếu giải chưa có category, hãy tạo ở tab "Nội dung thi đấu" trước.

2. **Status tự động approved**: Vì chủ giải thêm, nên status luôn là 'approved'. Điều này khác với VĐV đăng ký qua trang công khai (status = 'pending').

3. **Không kiểm tra trùng lặp**: Hệ thống hiện chưa kiểm tra nếu thêm trùng VĐV. Nên cẩn thận.

4. **Email/Phone không unique**: Nhiều VĐV có thể cùng email/phone.

---

## 🔄 Workflow tương tác

```
User Click "➕ Thêm VĐV"
        ↓
JavaScript: openAddAthleteModal()
        ↓
Modal hiển thị + Blur background
        ↓
User chọn category + nhập info
        ↓
User click "Thêm VĐV" → Form submit
        ↓
JavaScript: Validate + Fetch POST
        ↓
Server: HomeYardTournamentController@addAthlete
        ├─ Validate request
        ├─ Create TournamentAthlete (status='approved')
        └─ Return JSON response
        ↓
JavaScript: 
├─ Success → Alert + Close Modal + Reload Page
└─ Error → Alert error message + Keep Modal open
        ↓
DashboardController: homeYardDashboard()
        ├─ Fetch lại athletes
        └─ Render view với danh sách cập nhật
        ↓
Page hiển thị danh sách VĐV mới
```

---

## 📚 Tài liệu liên quan

- [ATHLETE_MODAL_USAGE.md](ATHLETE_MODAL_USAGE.md) - Hướng dẫn sử dụng chi tiết
- [ADD_ATHLETE_IMPLEMENTATION.md](ADD_ATHLETE_IMPLEMENTATION.md) - Chi tiết triển khai
- Routes config: `routes/web.php` (dòng 96)
- Model: `app/Models/TournamentAthlete.php`
- Layout: `resources/views/layouts/homeyard.blade.php`

---

## ✅ Checklist

- [x] Modal HTML
- [x] Modal styling (sử dụng CSS variables)
- [x] JavaScript functions (open/close/submit)
- [x] Validation client-side
- [x] Form submission với fetch API
- [x] Controller update (category_id, status='approved')
- [x] Server validation
- [x] Error handling
- [x] JSON response handling
- [x] Page reload
- [x] Documentation

---

**Status**: READY FOR PRODUCTION ✅

---

*Tạo bởi: Amp AI*
*Thời gian: November 21, 2025*
