# Hướng dẫn: Thêm Vận Động Viên qua Modal

## Tổng quan tính năng

Tính năng cho phép chủ giải đấu thêm vận động viên trực tiếp từ dashboard mà không cần qua trang đăng ký công khai.

**Trạng thái VĐV**: Vận động viên được thêm bởi chủ giải sẽ tự động được duyệt (status = 'approved')

## Quy trình sử dụng

### Bước 1: Truy cập dashboard
1. Đăng nhập với tài khoản home_yard (chủ giải đấu)
2. Vào Dashboard của một giải đấu đã tạo
3. Nếu chưa có giải đấu, hãy [tạo giải đấu trước](TOURNAMENT_CREATION.md)

### Bước 2: Mở modal thêm VĐV
1. Scroll tới tab **"👥 Quản lý VĐV"**
2. Click nút **"➕ Thêm VĐV"** ở góc trên bên phải của card
3. Modal sẽ hiện lên

### Bước 3: Điền thông tin VĐV

Modal chứa các trường sau:

| Trường | Bắt buộc | Mô tả |
|--------|---------|-------|
| **Nội dung thi đấu** | ✅ Bắt buộc | Dropdown chọn category (nội dung) VĐV sẽ thi đấu. Giá trị được lấy từ các category đã tạo trong giải |
| **Tên vận động viên** | ✅ Bắt buộc | Nhập tên đầy đủ của VĐV (VD: "Nguyễn Văn A") |
| **Email** | ❌ Tùy chọn | Địa chỉ email để liên hệ với VĐV |
| **Số điện thoại** | ❌ Tùy chọn | Số điện thoại để liên hệ |

### Bước 4: Submit form
1. Kiểm tra lại thông tin nhập vào
2. Click nút **"Thêm VĐV"**
3. Chờ hệ thống xử lý (nút sẽ hiển thị "⏳ Đang thêm...")
4. Sau khi thành công:
   - Hiện thông báo "✅ Vận động viên đã được thêm thành công!"
   - Modal tự động đóng
   - Trang sẽ reload để hiển thị danh sách VĐV cập nhật

### Bước 5: Xem danh sách VĐV
Danh sách VĐV sẽ hiển thị dưới modal với các thông tin:
- Tên VĐV
- Email
- Số điện thoại
- Nội dung thi đấu
- Trạng thái (✅ Đã phê duyệt - vì được chủ giải thêm)
- Trạng thái thanh toán

## Validation và Error Handling

### Validation phía Client (JavaScript):
- **Nội dung thi đấu**: Phải chọn 1 category
- **Tên VĐV**: Không được để trống
- Nếu thiếu thông tin bắt buộc, sẽ có alert yêu cầu điền

### Validation phía Server (Laravel):
```
- athlete_name: required|string|max:255
- category_id: required|exists:tournament_categories,id
- email: nullable|email (nếu nhập phải đúng format email)
- phone: nullable|string|max:20
```

### Error Messages:
- Nếu category_id không hợp lệ: "Nội dung thi đấu không hợp lệ"
- Nếu lỗi server: "Lỗi khi thêm vận động viên: [chi tiết lỗi]"
- Nếu hết phiên đăng nhập: Sẽ redirect tới trang login

## Thông tin ghi vào Database

Khi VĐV được thêm thành công, bản ghi sau sẽ được tạo trong bảng `tournament_athletes`:

```sql
INSERT INTO tournament_athletes (
    tournament_id,
    category_id,
    user_id,
    athlete_name,
    email,
    phone,
    status,
    created_at,
    updated_at
) VALUES (
    123,              -- ID của giải đấu
    45,               -- ID của nội dung thi đấu
    67,               -- ID của user đã đăng nhập (chủ giải)
    'Nguyễn Văn A',   -- Tên VĐV
    'nguyena@example.com',  -- Email
    '0123456789',     -- Số điện thoại
    'approved',       -- ⭐ Trạng thái = approved (đã duyệt)
    NOW(),
    NOW()
);
```

## FAQ

**Q: Tại sao VĐV được thêm bởi chủ giải tự động được duyệt?**
A: Vì chủ giải là người tổ chức, họ biết VĐV của giải của mình, nên không cần duyệt. VĐV đăng ký qua trang công khai sẽ ở trạng thái "pending" cần chủ giải duyệt.

**Q: Tôi có thể thêm một VĐV vào nhiều nội dung thi đấu không?**
A: Không, mỗi khi thêm phải chọn 1 nội dung cụ thể. Nếu VĐV thi đấu nhiều nội dung, bạn cần thêm nhiều lần với category khác nhau.

**Q: Nếu nhập sai email/phone có sửa được không?**
A: Chưa có tính năng sửa trong modal. Bạn cần xóa VĐV đó và thêm lại. Có thể thêm tính năng "Edit" trong tương lai.

**Q: VĐV được thêm có thể đăng ký lại qua trang công khai không?**
A: Hệ thống hiện chưa có kiểm tra trùng lặp. Nên cẩn thận để không thêm trùng.

## Troubleshooting

**Problem**: Modal không hiện lên khi click nút
- **Solution**: Kiểm tra browser console (F12 → Console) xem có error không

**Problem**: Submit form không hoạt động
- **Solution**: 
  - Kiểm tra điều kiện validation (category, athlete_name)
  - Kiểm tra network tab xem request có gửi không
  - Kiểm tra browser console xem có error không

**Problem**: VĐV được thêm nhưng không hiển thị
- **Solution**: 
  - Trang có reload không? Kiểm tra browser
  - Nếu không reload, thử F5 refresh lại trang

**Problem**: Lỗi "Nội dung thi đấu không hợp lệ"
- **Solution**: 
  - Category ID không tồn tại
  - Hãy chắc chắn có tạo category trong tab "Nội dung thi đấu" trước
  - Refresh page và thử lại

## Liên kết liên quan
- [Tạo Category (Nội dung thi đấu)](TOURNAMENT_CREATION.md#nội-dung-thi-đấu)
- [Quản lý VĐV - Duyệt/Từ chối](TOURNAMENT_MANAGEMENT.md#duyệt-vđv)
- [Bốc thăm chia bảng VĐV](TOURNAMENT_MANAGEMENT.md#bốc-thăm)

---

*Last updated: Nov 21, 2025*
*Version: 1.0*
