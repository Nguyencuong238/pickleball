# 🧪 Test Thêm VĐV Modal

## Steps to Test

### Prep
1. Đăng nhập với user `home_yard`
2. Vào Dashboard của giải đấu
3. Phải có ít nhất 1 Category trong tab "Nội dung thi đấu"

### Test
1. Scroll tới tab "👥 Quản lý VĐV"
2. Click nút "➕ Thêm VĐV"
3. Modal sẽ hiện lên
4. **Chọn category** từ dropdown
5. **Nhập tên VĐV**: "Test Athlete"
6. **Nhập email**: "test@example.com" (optional)
7. **Nhập phone**: "0123456789" (optional)
8. Click "Thêm VĐV"

### Expected Results
- ✅ Button "Thêm VĐV" disabled → "⏳ Đang thêm..."
- ✅ Request gửi tới server
- ✅ Alert thành công: "✅ Vận động viên đã được thêm thành công!"
- ✅ Modal đóng
- ✅ Trang reload (sau 500ms)
- ✅ VĐV mới xuất hiện trong danh sách
- ✅ VĐV có status: "✅ Đã phê duyệt"

### If Error

**Error**: `Unexpected token '<', "<!DOCTYPE "... is not valid JSON`

**Debug Steps**:
1. Mở **Developer Tools** (F12)
2. Vào tab **Console** → Xem error message
3. Vào tab **Network** → Click submit lại → Xem request/response:
   - Response Status? (200, 403, 422, 500, ...)
   - Response Type? (JSON hay HTML)
   - Response Body? (HTML hay JSON)

4. Khả năng:
   - **403 Forbidden** → Bạn không phải chủ giải
   - **422 Unprocessable** → Form validation failed
   - **500 Error** → Server error → Check logs
   - **HTML response** → Middleware failed → Check auth

5. Kiểm tra:
   ```
   - Đã đăng nhập? (check cookies)
   - User có role home_yard? (check user table)
   - User là chủ giải? (check tournament.user_id)
   - Category ID hợp lệ? (check tournament_categories)
   - Athlete name không để trống?
   ```

### Server Logs
```bash
tail -f storage/logs/laravel.log
```
Xem error message từ controller

### Video Test Flow
```
1. Dashboard → Tab Quản lý VĐV ✓
2. Click "Thêm VĐV" ✓
3. Modal hiện lên ✓
4. Chọn Category ✓
5. Nhập Athlete Name ✓
6. Click Submit ✓
7. Thông báo thành công ✓
8. Modal đóng ✓
9. Trang reload ✓
10. Danh sách update ✓
```

---

Nếu có vấn đề, xem [DEBUG_ATHLETE_MODAL.md](DEBUG_ATHLETE_MODAL.md)
