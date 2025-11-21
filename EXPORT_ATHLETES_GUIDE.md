# Hướng Dẫn Xuất Excel Danh Sách VĐV

## Tính năng
Nút "📊 Xuất Excel" trong tab "Quản lý VĐV" cho phép tải xuống danh sách các vận động viên tham gia giải đấu với các thông tin:
- STT (Số thứ tự)
- Tên Vận Động Viên
- Email
- Số Điện Thoại
- Nội Dung Thi Đấu
- Trạng Thái (Chờ phê duyệt / Đã phê duyệt / Từ chối)
- Trạng Thái Thanh Toán (Chờ thanh toán / Đã thanh toán / Chưa thanh toán)
- Ngày Đăng Ký

## Cách hoạt động

### 1. Frontend (View)
**File**: `resources/views/home-yard/dashboard.blade.php`
- Nút "Xuất Excel" là một link `<a>` trỏ đến route `homeyard.tournaments.athletes.export`
- Khi nhấp, nó sẽ gọi route để tải xuống file CSV

### 2. Routes
**File**: `routes/web.php`
```php
Route::get('tournaments/{tournament}/athletes/export', [HomeYardTournamentController::class, 'exportAthletes'])->name('tournaments.athletes.export');
```

### 3. Controller
**File**: `app/Http/Controllers/Front/HomeYardTournamentController.php`

Phương thức `exportAthletes()` thực hiện:
1. Kiểm tra quyền truy cập (chỉ chủ giải mới có quyền xuất)
2. Lấy danh sách tất cả VĐV của giải đấu
3. Kiểm tra nếu không có VĐV, trả về thông báo lỗi
4. Tạo file CSV với:
   - BOM (Byte Order Mark) cho hỗ trợ UTF-8 đúng trong Excel
   - Header cột
   - Dữ liệu các VĐV
5. Gửi file để tải xuống

## Thông tin file xuất

**Tên file**: `VDV_TênGiải_YYYY-MM-DD_HH-MM-SS.csv`

Ví dụ: `VDV_Giải_Pickleball_HCM_2025_2024-12-15_14-30-45.csv`

**Định dạng**: CSV (Comma-Separated Values)
- Có thể mở bằng Excel, Google Sheets, LibreOffice Calc
- Hỗ trợ tiếng Việt (UTF-8 BOM)

## Quyền truy cập

Chỉ những người dùng có:
- Vai trò `home_yard` (chủ sân)
- Đang đăng nhập
- Là chủ giải đấu

mới có thể xuất dữ liệu.

## Thử nghiệm

1. Đăng nhập với tài khoản chủ sân
2. Vào "Cấu Hình Giải Đấu"
3. Chọn tab "👥 Quản lý VĐV"
4. Nhấn nút "📊 Xuất Excel"
5. File sẽ được tải xuống tự động

## Ghi chú

- Nếu giải đấu không có VĐV nào, sẽ hiển thị thông báo lỗi
- Dữ liệu được xuất bao gồm tất cả VĐV (bất kể trạng thái)
- Thông tin email và số điện thoại sẽ hiển thị "-" nếu không có
- Ngày đăng ký hiển thị theo định dạng: `dd/mm/yyyy hh:mm`
