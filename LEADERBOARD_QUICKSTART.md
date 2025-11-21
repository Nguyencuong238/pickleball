# 🏅 Bảng Xếp Hạng - Hướng Dẫn Nhanh

## 📋 Tóm Tắt 60 Giây

### ✅ Điều Gì Được Thêm
1. **Tab mới** trong Cấu Hình Giải Đấu: 🏅 Bảng xếp hạng
2. **Bảng dữ liệu** với 10 cột hiển thị VĐV xếp hạng
3. **Bộ lọc nội dung** để xem từng loại thi đấu
4. **API endpoint** để lấy dữ liệu xếp hạng

### 🎯 Xếp Hạng Theo Thứ Tự Ưu Tiên
```
1. ⭐ Điểm (3 điểm/trận thắng) - CAO NHẤT ĐỨNG ĐẦU
2. 📊 Tỷ lệ thắng (%) - Nếu điểm bằng nhau
3. ✅ Số trận thắng - Nếu tỷ lệ bằng nhau  
4. ➕ Hiệu số set - Nếu trận bằng nhau
```

### 📊 10 Cột Bảng
| # | Cột | Ý Nghĩa | Ví Dụ |
|---|-----|---------|-------|
| 1 | 🏆 Hạng | Xếp hạng | 🥇 1, 🥈 2, 🥉 3, #4 |
| 2 | VĐV | Tên + Email | Nguyễn Văn An |
| 3 | Nội dung | Loại thi đấu | Nam đơn 18+ |
| 4 | 🎾 Trận | Trận đã thi | 5 |
| 5 | ✅ Thắng | Trận thắng (xanh) | 5 |
| 6 | ❌ Thua | Trận thua (đỏ) | 0 |
| 7 | 📊 Tỷ lệ | % thắng | 100% |
| 8 | 🔤 Set | Thắng-Thua | 10-0 |
| 9 | ➕ Hiệu số | Chênh lệch (xanh/đỏ) | +10 |
| 10 | ⭐ Điểm | Tổng điểm (vàng) | 15 |

---

## 🚀 Cách Sử Dụng

### Bước 1: Truy Cập
```
Dashboard → Cấu Hình Giải Đấu → Tab "🏅 Bảng xếp hạng"
```

### Bước 2: Chọn Nội Dung (Tuỳ Chọn)
- **🏆 Tất cả** = Xem toàn bộ VĐV
- **Nam đơn 18+** = Chỉ VĐV thi đấu nội dung này
- **Nữ đôi 35+** = Chỉ VĐV thi đấu nội dung khác
- ...

### Bước 3: Xem Kết Quả
```
Bảng cập nhật tự động
→ Xếp hạng từ cao điểm xuống thấp
→ Hiển thị toàn bộ thống kê
```

---

## ⚙️ Yêu Cầu Để Hoạt Động

### ✅ Bắt Buộc
1. ✔️ Đã tạo **Giải đấu**
2. ✔️ Đã tạo **Nội dung thi đấu** (category)
3. ✔️ Đã **đăng ký VĐV** vào nội dung
4. ✔️ Đã **tạo trận đấu** giữa VĐV
5. ✔️ Đã **cập nhật kết quả** trận (`status='completed'`)
6. ✔️ Mỗi trận có **điểm số**: `athlete1_score`, `athlete2_score`

### ❌ Nếu Không Có
```
Trạng thái          | Kết Quả Bảng
────────────────────┼──────────────────
Chưa tạo VĐV        | "Chưa có dữ liệu"
Trận chưa hoàn thành| Không tính vào
Trận chưa có điểm   | Bỏ qua
```

---

## 🎨 Giao Diện

### Màu Sắc
```
Header Bảng:  Gradient tím (#667eea → #764ba2)
Thắng:        Xanh lá (#10B981)
Thua:         Đỏ (#EF4444)
Tỷ lệ:        Tím (#667eea)
Điểm:         Vàng (#fbbf24)
```

### Responsive
✅ Desktop: Bảng full width  
✅ Mobile: Scroll ngang (`overflow-x: auto`)

---

## 💡 Ví Dụ Thực Tế

### Dữ Liệu Input
```
Giải: "Pickleball TP.HCM"
Nội dung: "Nam đơn 18+"

VĐV A: 2 trận thắng, 4 set, 6 điểm → Xếp hạng 1 (🥇)
VĐV B: 1 trận thắng, 2 set, 3 điểm → Xếp hạng 3 (🥉)
VĐV C: 1 trận thắng, 2 set, 3 điểm → Xếp hạng 2 (🥈)
VĐV D: 0 trận thắng, 0 set, 0 điểm → Xếp hạng 4 (#4)
```

### Kết Quả Bảng
```
🏆 │ VĐV      │ Nội dung     │ Trận │ Thắng │ Thua │ Tỷ lệ │ Set   │ Hiệu  │ Điểm
───┼──────────┼──────────────┼──────┼───────┼──────┼───────┼───────┼───────┼─────
🥇 │ Nguyễn A │ Nam đơn 18+  │ 2    │ 2🟢  │ 0🔴 │ 100%  │ 4-0   │ +4🟢  │ 6⭐
🥈 │ Vũ C     │ Nam đơn 18+  │ 1    │ 1🟢  │ 0🔴 │ 100%  │ 2-0   │ +2🟢  │ 3⭐
🥉 │ Bùi B    │ Nam đơn 18+  │ 2    │ 1🟢  │ 1🔴 │ 50%   │ 2-2   │  0⚪  │ 3⭐
#4 │ Hà D     │ Nam đơn 18+  │ 1    │ 0🟢  │ 1🔴 │ 0%    │ 0-2   │ -2🔴  │ 0⭐
```

---

## 🔧 Công Nghệ

### Files Thay Đổi
```
1. resources/views/home-yard/dashboard.blade.php
   ├─ Thêm tab rankings
   ├─ Thêm bảng HTML
   └─ Thêm JavaScript load/filter

2. app/Http/Controllers/Front/HomeYardTournamentController.php
   └─ Thêm method getLeaderboard()

3. routes/web.php
   └─ Thêm route /tournaments/{tournament}/leaderboard
```

### API Endpoint
```
GET /homeyard/tournaments/{id}/leaderboard
GET /homeyard/tournaments/{id}/leaderboard?category_id=2
```

### Response Format
```json
{
  "success": true,
  "athletes": [
    {
      "id": 1,
      "athlete_name": "Nguyễn Văn An",
      "category_name": "Nam đơn 18+",
      "matches_played": 2,
      "matches_won": 2,
      "win_rate": 100,
      "sets_won": 4,
      "sets_differential": 4,
      "total_points": 6
    }
  ]
}
```

---

## ❓ Thường Gặp

### Q: Tại sao không hiển thị VĐV?
**A:** Kiểm tra:
- [ ] Giải đấu đã tạo?
- [ ] VĐV đã đăng ký?
- [ ] Trận đã tạo?
- [ ] Trận đã hoàn thành (`status='completed'`)?
- [ ] Trận có điểm số?

### Q: Xếp hạng không đúng?
**A:** Kiểm tra:
- [ ] Điểm số của trận có đúng?
- [ ] Người thắng = điểm set cao hơn?
- [ ] Trạng thái trận = 'completed'?

### Q: Tỷ lệ tính sai?
**A:** Công thức: `(trận thắng / tổng trận) × 100`
```
Ví dụ: 2 thắng, 1 thua = (2 / 3) × 100 = 66.7%
```

### Q: Có thể xuất Excel không?
**A:** Hiện chưa có. Sẽ được thêm sau. Tạm thời có thể:
- Copy bảng → Paste vào Excel
- Print bảng → Save as PDF

---

## 📱 Test Checklist

- [ ] Bảng hiển thị đúng trên desktop
- [ ] Bảng responsive trên mobile
- [ ] Xếp hạng tính đúng (cao điểm trước)
- [ ] Tỷ lệ % tính đúng
- [ ] Bộ lọc nội dung hoạt động
- [ ] Màu sắc dễ nhìn
- [ ] Load nhanh (< 1 giây)

---

## 📞 Support

**Lỗi gì không?** Kiểm tra:
1. Console browser (F12 → Console)
2. Network tab (F12 → Network)
3. Server logs: `storage/logs/laravel.log`

---

**Version:** 1.0  
**Last Updated:** 2025-01-21  
**Status:** ✅ Ready to Use
