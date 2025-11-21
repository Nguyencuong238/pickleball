# Bảng Xếp Hạng VĐV - Hướng Dẫn Triển Khai

## 📋 Tổng Quan
Bảng xếp hạng VĐV đã được thêm vào tab "🏅 Bảng xếp hạng" trong trang Cấu Hình Giải Đấu. Danh sách VĐV sẽ được xếp hạng tự động dựa trên thống kê thắng thua, tỷ lệ điểm, hiệu số set, và điểm tích lũy.

## 🎯 Tính Năng

### 1. **Xếp hạng VĐV**
- **Sắp xếp theo tiêu chí:**
  1. ⭐ **Điểm tích lũy** (cao nhất đứng đầu) - 3 điểm/trận thắng
  2. 📊 **Tỷ lệ thắng** (%)
  3. ✅ **Số trận thắng** (tuyệt đối)
  4. ➕ **Hiệu số set** (set thắng - set thua)

### 2. **Bộ lọc theo Nội dung**
- Nút "🏆 Tất cả" hiển thị toàn bộ VĐV
- Các nút riêng cho từng nội dung thi đấu (Nam đơn, Nữ đơn, etc.)
- Phân loại dữ liệu dựa trên `category_id`

### 3. **Các Cột Hiển Thị**
| Cột | Mô Tả | Ví Dụ |
|-----|-------|--------|
| 🏆 Hạng | Xếp hạng (1, 2, 3, ...) | 🥇, 🥈, 🥉, #4 |
| VĐV | Tên VĐV + Email | Nguyễn Văn An (email@...) |
| Nội dung | Loại thi đấu | Nam đơn 18+, Nữ đôi 35+ |
| 🎾 Trận | Tổng số trận đã thi đấu | 5 |
| ✅ Thắng | Số trận thắng (xanh lá) | 5 |
| ❌ Thua | Số trận thua (đỏ) | 0 |
| 📊 Tỷ lệ | Tỷ lệ thắng (%) | 100% |
| 🔤 Set | Set thắng - Set thua | 10 - 0 |
| ➕ Hiệu số | Hiệu số (xanh/đỏ) | +110 |
| ⭐ Điểm | Tổng điểm (vàng) | 15 |

## 🔧 Kiến Trúc Kỹ Thuật

### **Frontend (Blade Template)**
```
File: /resources/views/home-yard/dashboard.blade.php
```
- ID Tab: `rankings`
- Bảng HTML với `id="leaderboardBody"` (dữ liệu động)
- Bộ lọc với sự kiện `onclick="filterLeaderboard(categoryId)"`
- JavaScript tính toán:
  - Tỷ lệ thắng: `(wins / matches) * 100`
  - Hiệu số: `sets_won - sets_lost`
  - Xếp hạng tự động

### **Backend (Controller)**
```
File: /app/Http/Controllers/Front/HomeYardTournamentController.php
Method: getLeaderboard(Request, Tournament)
```
- Lấy tất cả VĐV của giải đấu
- Lọc theo category_id (nếu có)
- Tính thống kê từ bảng `matches`:
  - Đếm trận thắng/thua
  - Tính tổng set
  - Tính điểm (3 điểm/trận)
- Sắp xếp theo: điểm → tỷ lệ → trận thắng → hiệu số

### **Route API**
```
GET /homeyard/tournaments/{tournament}/leaderboard
```
- **Parameters:** `?category_id=X` (tuỳ chọn)
- **Response:** JSON
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
        "win_rate": 100,
        "sets_won": 10,
        "sets_lost": 0,
        "sets_differential": 10,
        "total_points": 15
      }
    ]
  }
  ```

## 📊 Ví Dụ Dữ Liệu

### Kịch bản: 3 VĐV thi đấu
```
VĐV A:
- Trận 1: A thắng 11-7, 11-9 → 2 set
- Trận 2: A thắng 11-8, 10-12, 11-6 → 2 set
- Tổng: 2 trận thắng, 4 set thắng, 0 set thua = +4, 6 điểm, 100% tỷ lệ
  → XẾP HẠNG 1 (6 điểm cao nhất)

VĐV B:
- Trận 1: B thua 7-11, 9-11 → 0 set
- Trận 2: B thắng 11-8, 11-9 → 2 set
- Tổng: 1 trận thắng, 2 set thắng, 2 set thua = 0, 3 điểm, 50% tỷ lệ
  → XẾP HẠNG 2 (3 điểm < 6 điểm)

VĐV C:
- Trận 1: C thắng 11-7, 11-9 → 2 set
- Trận 2: C thua 8-11, 9-11 → 0 set
- Tổng: 1 trận thắng, 2 set thắng, 2 set thua = 0, 3 điểm, 50% tỷ lệ
  → XẾP HẠNG 3 (3 điểm = VĐV B nhưng set lớn hơn)

** Nếu hiệu số set bằng nhau, xếp hạng sẽ dựa trên thứ tự ID **
```

## 🚀 Cách Sử Dụng

### 1. **Xem bảng xếp hạng chung**
- Click tab "🏅 Bảng xếp hạng"
- Nhấn nút "🏆 Tất cả" (mặc định)
- Bảng tự động tải dữ liệu

### 2. **Xem bảng xếp hạng theo nội dung**
- Click một ngoài các nội dung thi đấu (ví dụ: "Nam đơn 18+")
- Bảng cập nhật lại chỉ hiển thị VĐV của nội dung đó

### 3. **Dữ liệu được cập nhật khi nào?**
- Mỗi khi nhấp vào bộ lọc
- Chỉ tính các trận đã `status='completed'`
- Dữ liệu thực tế từ bảng `matches` (không cache)

## 📝 Ghi Chú

### Yêu cầu dữ liệu
- Cần có **trận đấu** đã hoàn thành (`status='completed'`)
- Mỗi trận cần có:
  - `athlete1_id`, `athlete2_id`
  - `athlete1_score`, `athlete2_score`
  - Người thắng = người có số set cao hơn

### Hiệu suất
- Mỗi lần lọc: 1 query lấy VĐV + 1 query/VĐV lấy trận đấu
- Có thể tối ưu bằng subquery nếu quá chậm
- Tính toán PHP (không SQL) để linh hoạt

### Mở rộng trong tương lai
- [ ] Cache kết quả xếp hạng
- [ ] Xử lý hòa (draw) - hiện tương đương thua
- [ ] Tính điểm theo Elo rating
- [ ] Xuất Excel bảng xếp hạng
- [ ] Đồ thị tiến trình VĐV
- [ ] Bảng xếp hạng theo thời gian

## ✅ Test Checklist

- [ ] Bảng hiển thị đúng thông tin VĐV
- [ ] Sắp xếp theo điểm (cao → thấp)
- [ ] Tỷ lệ% tính đúng
- [ ] Hiệu số set hiển thị +/- đúng
- [ ] Bộ lọc nội dung hoạt động
- [ ] API response JSON hợp lệ
- [ ] Style bảng đẹp trên mobile
- [ ] Performance tốt (< 1s load)

---

**Phiên bản:** 1.0  
**Ngày cập nhật:** 2025-01-21  
**Trạng thái:** ✅ Hoàn thành
