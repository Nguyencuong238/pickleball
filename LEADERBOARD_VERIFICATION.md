# Kiểm Tra Bảng Xếp Hạng

## ✅ Checklist Triển Khai

### Backend
- [x] Cập nhật route `/homeyard/tournaments/{tournament}/rankings`
- [x] Thêm method `getRankings()` trong HomeYardTournamentController
- [x] Sắp xếp theo: Points DESC > Wins DESC > Games Differential DESC
- [x] Hỗ trợ filter category_id và group_id
- [x] Trả về JSON với rankings, total_matches, total_athletes

### Frontend - Bảng Xếp Hạng
- [x] Cập nhật HTML bảng xếp hạng
- [x] Cột headers: XH, Tên, Nội Dung, Trận, Thắng, Thua, Điểm, Set, Hiệu Số, %
- [x] Hiển thị huy chương 🥇🥈🥉 cho top 3
- [x] Highlight điểm vàng, hiệu số xanh
- [x] Thống kê card (VĐV hạng 1, tổng trận, tổng VĐV)

### Frontend - Bộ Lọc
- [x] Dropdown Category - Nội dung thi đấu
- [x] Dropdown Group - Bảng đấu
- [x] Hàm `updateGroupFilter()` lọc bảng theo category
- [x] Thêm `data-category-id` attribute cho mỗi option bảng
- [x] Hiển thị nội dung trong tên bảng: `Bảng B (Nam đơn 18+)`
- [x] Reset bảng khi thay đổi category
- [x] Khởi tạo filter khi page load

### Frontend - JavaScript
- [x] `updateGroupFilter()` - Lọc dropdown bảng
- [x] `loadRankings()` - Load dữ liệu từ API
- [x] `renderRankingsTable()` - Render bảng
- [x] `updateRankingsStats()` - Cập nhật thống kê
- [x] `printRankings()` - In bảng
- [x] `exportRankingsCSV()` - Xuất CSV

### Frontend - Events
- [x] Category onChange → `updateGroupFilter(); loadRankings()`
- [x] Group onChange → `loadRankings()`
- [x] DOMContentLoaded → `updateGroupFilter(); loadRankings()`

---

## 🧪 Test Scenarios

### Scenario 1: Khởi Tạo Trang

**Steps:**
1. Tạo giải đấu với 2 nội dung: "Nam đơn 18+" và "Nữ đơn 18+"
2. Tạo 2 bảng cho Nam: Bảng A, B
3. Tạo 2 bảng cho Nữ: Bảng A, B
4. Load trang dashboard

**Kỳ vọng:**
```
✓ Dropdown Category hiển thị: 
  - -- Tất cả nội dung --
  - Nam đơn 18+
  - Nữ đơn 18+

✓ Dropdown Group hiển thị tất cả 4 bảng:
  - -- Tất cả bảng --
  - Bảng A (Nam đơn 18+)
  - Bảng B (Nam đơn 18+)
  - Bảng A (Nữ đơn 18+)
  - Bảng B (Nữ đơn 18+)

✓ Bảng xếp hạng hiển thị tất cả VĐV
```

---

### Scenario 2: Lọc Theo Category

**Steps:**
1. Từ Scenario 1
2. Chọn "Nam đơn 18+" từ dropdown Category

**Kỳ vọng:**
```
✓ Dropdown Group tự cập nhật hiển thị:
  - -- Tất cả bảng --
  - Bảng A (Nam đơn 18+)
  - Bảng B (Nam đơn 18+)
  
  (Ẩn: Bảng A (Nữ đơn 18+), Bảng B (Nữ đơn 18+))

✓ Bảng xếp hạng hiển thị CHỈ VĐV Nam đơn 18+

✓ Dropdown Group reset = "" (-- Tất cả bảng --)

✓ Thống kê cập nhật: Total Athletes = số VĐV Nam
```

---

### Scenario 3: Lọc Theo Group

**Steps:**
1. Từ Scenario 2 (đang chọn Nam đơn)
2. Chọn "Bảng B (Nam đơn 18+)" từ dropdown Group

**Kỳ vọng:**
```
✓ Bảng xếp hạng hiển thị CHỈ VĐV Bảng B Nam

✓ Dữ liệu chính xác:
  - Không có VĐV từ Bảng A
  - Không có VĐV từ Bảng C, D...
  - Không có VĐV từ Nữ đơn

✓ Xếp hạng trong bảng đúng thứ tự:
  1. Điểm cao nhất
  2. Nếu điểm bằng → Trận thắng nhiều
  3. Nếu vẫn bằng → Hiệu số game lớn
```

---

### Scenario 4: Thay Đổi Category

**Steps:**
1. Từ Scenario 3 (đang chọn Nam + Bảng B)
2. Thay đổi Category sang "Nữ đơn 18+"

**Kỳ vọng:**
```
✓ Dropdown Group tự cập nhật:
  - -- Tất cả bảng --
  - Bảng A (Nữ đơn 18+)
  - Bảng B (Nữ đơn 18+)

✓ Dropdown Group reset = "" (Tất cả bảng)

✓ Bảng xếp hạng load lại hiển thị Nữ đơn 18+

✓ Không còn dữ liệu Nam

✓ Thống kê cập nhật: VĐV hạng 1 Nữ
```

---

### Scenario 5: Reset Filter

**Steps:**
1. Từ bất kỳ scenario nào đó
2. Đặt lại Category = "-- Tất cả nội dung --"
3. Đặt lại Group = "-- Tất cả bảng --"

**Kỳ vọng:**
```
✓ Dropdown Group hiển thị tất cả bảng từ tất cả nội dung

✓ Bảng xếp hạng hiển thị TẤT CẢ VĐV

✓ Thống kê cập nhật:
  - VĐV hạng 1 toàn giải
  - Total Matches = tất cả trận
  - Total Athletes = tất cả VĐV
```

---

### Scenario 6: In Bảng

**Steps:**
1. Lọc dữ liệu (VD: Nam đơn - Bảng B)
2. Nhấn nút "📄 In bảng"

**Kỳ vọng:**
```
✓ Cửa sổ print mới mở

✓ Hiển thị tiêu đề: "Bảng Xếp Hạng Vận Động Viên"

✓ Bảng hiển thị dữ liệu đã lọc (Bảng B Nam)

✓ Định dạng đẹp, dễ in
```

---

### Scenario 7: Xuất CSV

**Steps:**
1. Lọc dữ liệu (VD: Nữ đơn - Bảng A)
2. Nhấn nút "📊 Xuất CSV"

**Kỳ vọng:**
```
✓ File CSV tải xuống

✓ Tên file: BangXepHang_[timestamp].csv

✓ Nội dung CSV:
  - Header: Xếp Hạng,Tên VĐV,Nội Dung,Trận,Thắng,...
  - Data: Chỉ VĐV Nữ đơn - Bảng A
  - Không có emoji (🥇🥈🥉 bị xóa)

✓ Có thể mở bằng Excel/Google Sheets
```

---

### Scenario 8: Không Có Dữ Liệu

**Steps:**
1. Tạo giải đấu nhưng chưa tạo bảng hoặc VĐV
2. Load tab xếp hạng

**Kỳ vọng:**
```
✓ Hiển thị: "Chưa có dữ liệu xếp hạng"

✓ Thống kê hiển thị:
  - VĐV hạng 1: -
  - Tổng trận: 0
  - Tổng VĐV: 0

✓ Bảng trống, không lỗi
```

---

## 🔍 Kiểm Tra Chi Tiết

### API Response
```bash
# Test endpoint
GET /homeyard/tournaments/1/rankings?category_id=2&group_id=5
```

**Kiểm tra:**
- [x] Status code 200 OK
- [x] JSON response có `success: true`
- [x] Mảng `rankings` trả về đúng thứ tự
- [x] `total_matches`, `total_athletes` chính xác
- [x] Filter object có category_id, group_id

---

### Database
- [x] GroupStanding records tồn tại
- [x] athlete_id link đúng TournamentAthlete
- [x] Group link đúng Tournament
- [x] Dữ liệu points, matches_won, games_won chính xác

---

### Performance
- [x] Load ranking < 500ms (không quá chậm)
- [x] Filter không lag
- [x] Print/Export không crash

---

## 🎯 Expected Behavior

| Action | Trước | Sau |
|--------|-------|-----|
| Chọn Category | Dropdown Group không đổi | Dropdown Group tự cập nhật |
| Bảng từ các Category | Gộp lẫn | Tách biệt rõ ràng |
| Dropdown Group | Không hiển thị Category | Hiển thị Category trong tên |
| Chuyển Category | Giữ Group cũ | Reset Group = "Tất cả" |
| Dữ liệu | Trộn từ các bảng khác | Chính xác theo filter |

---

## 📋 Bug Report Template

Nếu gặp lỗi, báo cáo theo format:

```
**Title:** [BUG] Tên lỗi

**Scenario:** 
Bước 1: ...
Bước 2: ...
Bước 3: ...

**Expected:**
Kỳ vọng hiển thị...

**Actual:**
Thực tế hiển thị...

**Screenshot:**
[Đính kèm ảnh]

**Browser:** Chrome/Firefox/Safari

**Data:**
- Tournament: ID?
- Category: ID?
- Group: ID?
```

---

## ✨ Sign-Off

- [ ] Tất cả scenarios pass
- [ ] Không lỗi console
- [ ] Performance OK
- [ ] Documentation cập nhật
- [ ] Code review pass
- [ ] Ready for production

**Date:** ___________
**Tested By:** ___________
