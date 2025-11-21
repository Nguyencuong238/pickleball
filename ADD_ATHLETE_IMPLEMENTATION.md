# Thêm VĐV Modal - Tài liệu Triển khai

## Tính năng đã thêm:
Khi chủ giải đấu click nút "➕ Thêm VĐV" trong tab "Quản lý VĐV", sẽ:
1. Mở modal để nhập thông tin vận động viên
2. Yêu cầu chọn nội dung thi đấu (category) 
3. Yêu cầu nhập tên vận động viên
4. Tùy chọn: nhập email và số điện thoại
5. Khi submit, ghi vào DB bảng `tournament_athletes` với trạng thái `approved` (đã duyệt)

## Thay đổi được thực hiện:

### 1. View: `/resources/views/home-yard/dashboard.blade.php`

#### a) Button để mở modal (dòng 577)
```html
<button class="btn btn-primary btn-sm" id="addAthleteBtn" onclick="openAddAthleteModal()">➕ Thêm VĐV</button>
```

#### b) HTML Modal (dòng 920-964)
- Modal form với các field:
  - `category_id` - dropdown chọn nội dung thi đấu (bắt buộc)
  - `athlete_name` - tên VĐV (bắt buộc)
  - `email` - email (tùy chọn)
  - `phone` - số điện thoại (tùy chọn)
- Styling inline với background overlay

#### c) JavaScript Functions (dòng 1217-1308)
- `openAddAthleteModal()` - mở modal
- `closeAddAthleteModal()` - đóng modal
- Event listener cho click ngoài modal
- Form submission handler:
  - Validation client-side
  - Fetch POST request tới API endpoint
  - Refresh page sau khi thành công
  - Error handling

### 2. Controller: `/app/Http/Controllers/Front/HomeYardTournamentController.php`

#### Sửa phương thức `addAthlete()` (dòng 213-254)
```php
public function addAthlete(Request $request, Tournament $tournament)
{
    // Validation bắt buộc category_id
    $request->validate([
        'athlete_name' => 'required|string|max:255',
        'email' => 'nullable|email',
        'phone' => 'nullable|string|max:20',
        'category_id' => 'required|exists:tournament_categories,id',
    ]);

    // Tạo TournamentAthlete với status = 'approved' mặc định
    $athlete = TournamentAthlete::create([
        'tournament_id' => $tournament->id,
        'category_id' => $request->category_id,
        'user_id' => auth()->id(),
        'athlete_name' => $request->athlete_name,
        'email' => $request->email,
        'phone' => $request->phone,
        'status' => 'approved', // Chủ giải thêm mặc định được duyệt
    ]);

    // Handle JSON responses cho AJAX
    if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
        return response()->json([
            'success' => true,
            'message' => 'Vận động viên đã được thêm thành công',
            'athlete' => $athlete
        ]);
    }

    return redirect()->back()->with('success', 'Vận động viên đã được thêm thành công.');
}
```

## Route
- **Route**: `POST /homeyard/tournaments/{tournament}/athletes`
- **Name**: `homeyard.tournaments.athletes.add`
- **Middleware**: `auth`, `role:home_yard`
- **Controller**: `HomeYardTournamentController@addAthlete`

## Database
Các cột được ghi vào bảng `tournament_athletes`:
- `tournament_id` - ID của giải đấu (FK)
- `category_id` - ID của nội dung thi đấu (FK) ⭐ YÊU CẦU
- `user_id` - ID của user hiện tại (FK)
- `athlete_name` - Tên vận động viên ⭐ YÊU CẦU
- `email` - Email VĐV (nullable)
- `phone` - Số điện thoại VĐV (nullable)
- `status` - Trạng thái = 'approved' (luôn được set)
- `created_at`, `updated_at` - Timestamps tự động

## Testing Instructions

### Manual Test:
1. Đăng nhập với tài khoản home_yard
2. Vào dashboard của một giải đấu đã có categories
3. Click nút "➕ Thêm VĐV" trong tab "Quản lý VĐV"
4. Modal sẽ hiện lên
5. Chọn nội dung thi đấu từ dropdown
6. Nhập tên VĐV
7. (Optional) Nhập email và phone
8. Click "Thêm VĐV"
9. VĐV sẽ được thêm vào DB với status = 'approved'
10. Page sẽ reload để hiển thị danh sách cập nhật

### API Test:
```bash
POST /homeyard/tournaments/{tournament_id}/athletes
Content-Type: application/json
X-CSRF-TOKEN: {token}
X-Requested-With: XMLHttpRequest

{
    "category_id": 1,
    "athlete_name": "Nguyễn Văn A",
    "email": "nguyena@example.com",
    "phone": "0123456789"
}
```

## Khả năng mở rộng:
- Có thể thêm validation cho email/phone uniqueness nếu cần
- Có thể thêm batch import athletes từ file CSV
- Có thể thêm trigger email gửi VĐV khi được thêm vào giải
- Có thể integrate với payment system

## Lưu ý:
- Status của VĐV được set là 'approved' vì chủ giải thêm mặc định được duyệt
- Category_id là bắt buộc để xác định VĐV dự thi nội dung nào
- Email và phone là optional nhưng nên được điền để liên hệ VĐV
