# Modal Testing Checklist

## 🎯 Mục Đích
Kiểm tra xem modal "🎾 Tạo Trận Đấu" có hiển thị khi click nút "➕ Tạo Trận Mới"

## ✅ Bước Test

### 1. Refresh Trang
- Mở link dashboard: `http://yourapp/homeyard/dashboard/1` (thay 1 bằng tournament ID)
- Refresh trang (F5)

### 2. Click Tab "🎾 QUẢN LÝ TRẬN ĐẤU"
- Tìm tab với icon 🎾
- Click vào
- Phải hiện bảng danh sách trận đấu (hiện tại trống)

### 3. Click Nút "➕ Tạo Trận Mới"
- Nút nằm bên phải tiêu đề tab
- Click nút
- **Kỳ Vọng:** Modal popup "🎾 Tạo Trận Đấu" hiện ra với form

### 4. Kiểm Tra Modal
Modal phải có:
- [ ] Tiêu đề: "🎾 Tạo Trận Đấu"
- [ ] Nút đóng (×) góc trên bên phải
- [ ] Form với các field:
  - [ ] Nội dung thi đấu (bắt buộc)
  - [ ] Vòng đấu (tuỳ chọn)
  - [ ] Sân thi đấu (tuỳ chọn)
  - [ ] VĐV 1 (bắt buộc)
  - [ ] VĐV 2 (bắt buộc)
  - [ ] Ngày thi đấu (bắt buộc)
  - [ ] Giờ thi đấu (tuỳ chọn)
  - [ ] Số set tối đa (tuỳ chọn)
  - [ ] Ghi chú (tuỳ chọn)
- [ ] Nút "➕ Tạo Trận"
- [ ] Nút "❌ Hủy"

### 5. Test Đóng Modal
- Click nút "❌ Hủy" → Modal phải đóng
- Hoặc click nút "×" góc trên phải → Modal phải đóng
- Hoặc click vùng tối ngoài modal → Modal phải đóng

### 6. Test Load VĐV
- Click nút "➕ Tạo Trận Mới" lại
- Chọn một Nội dung thi đấu
- [ ] Dropdown "VĐV 1" phải populate danh sách VĐV của nội dung đó
- [ ] Dropdown "VĐV 2" phải populate danh sách VĐV của nội dung đó

### 7. Test Form Validation
- Click "➕ Tạo Trận" mà không điền gì
- [ ] Phải hiện alert: "Vui lòng điền đầy đủ thông tin bắt buộc"

### 8. Test Tạo Trận Thành Công
- Điền đầy đủ:
  - Nội dung thi đấu
  - VĐV 1
  - VĐV 2
  - Ngày thi đấu (vd: 2025-01-20)
- Click "➕ Tạo Trận"
- [ ] Alert thành công: "✅ Trận đấu đã được tạo thành công!"
- [ ] Trang reload
- [ ] Trận đấu mới hiện trong bảng danh sách

## 🐛 Có Lỗi? Kiểm Tra:

### A. Modal Không Hiện
1. Mở DevTools (F12)
2. Tab Console
3. Gõ: `document.getElementById('createMatchModal')`
   - Nếu return `null` → modal HTML không render
   - Nếu return element → modal HTML OK, vấn đề ở JavaScript
4. Gõ: `typeof openCreateMatchModal`
   - Nếu return `"function"` → function OK
   - Nếu return `"undefined"` → function không được định nghĩa

### B. Click Nút Không Gì Xảy Ra
1. Mở Console (F12)
2. Tab Network
3. Click nút
4. Xem có request nào được gửi không
5. Nếu không có → event listener không hoạt động
6. Check Console tab có lỗi không

### C. VĐV Không Load
1. Click nút "Tạo Trận Mới"
2. Chọn nội dung
3. Mở Console
4. Check xem có request `/homeyard/tournaments/{id}/athletes?category_id=...` không
5. Check response status code

## 📝 Fix Nhanh Chóng

Nếu modal không hiện, thêm vào Console:
```javascript
document.getElementById('createMatchModal').style.display = 'block';
```

Nếu hiện = HTML OK, vấn đề ở JS
Nếu không hiện = HTML không render hoặc element ID sai

## ✨ Expected Result

Sau khi hoàn thành test, bạn phải:
1. Có thể click nút "➕ Tạo Trận Mới"
2. Modal popup hiện ra
3. Chọn nội dung, VĐV, ngày thi đấu
4. Click "Tạo Trận"
5. Trận đấu được lưu vào database
6. Trận hiện trong bảng danh sách

Nếu tất cả OK → ✅ Complete!
