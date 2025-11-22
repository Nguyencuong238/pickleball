# Hướng Dẫn Bộ Lọc Bảng Xếp Hạng

## Vấn Đề Được Sửa
**Trước:** Khi lọc theo bảng (B, C), dữ liệu bị gộp lại không phân biệt
**Sau:** Bảng B, C được tách biệt rõ ràng dựa theo nội dung thi đấu

## Cách Hoạt Động

### 1. Lọc Theo Nội Dung (Category)
```
Chọn: "Nam đơn 18+"
↓
- Hiển thị: Bảng A, B, C, D (chỉ của nội dung "Nam đơn 18+")
- Ẩn: Bảng của các nội dung khác (Nữ đơn, Đôi nam, etc)
```

### 2. Lọc Theo Bảng (Group)
```
Chọn: "Bảng B"
↓
- Hiển thị: Chỉ VĐV của Bảng B
- Ẩn: VĐV của Bảng A, C, D
```

### 3. Cách Sử Dụng Tối Ưu
```
Bước 1: Chọn Nội dung thi đấu
        ↓ (Tự động reset bảng = "-- Tất cả bảng --")
Bước 2: Chọn Bảng (nếu cần)
        ↓
Bước 3: Xem kết quả xếp hạng
```

## Ví Dụ

### Scenario 1: Xem tất cả VĐV
```
Nội dung: -- Tất cả nội dung --
Bảng:    -- Tất cả bảng --
```
**Kết quả:** Toàn bộ VĐV của giải

### Scenario 2: Xem Bảng B của "Nam đơn 18+"
```
Nội dung: Nam đơn 18+
Bảng:    Bảng B (Nam đơn 18+)
```
**Kết quả:** Chỉ VĐV bảng B - Nam đơn 18+

### Scenario 3: Xem tất cả bảng của "Nữ đơn 18+"
```
Nội dung: Nữ đơn 18+
Bảng:    -- Tất cả bảng --
```
**Kết quả:** Tất cả bảng (A, B, C...) của "Nữ đơn 18+"

## Tính Năng

### Dropdown Bảng Hiển Thị
- **Tên bảng:** Bảng A, Bảng B, Bảng C...
- **Nội dung:** Hiển thị nội dung tương ứng trong ngoặc
  ```
  Ví dụ: Bảng A (Nam đơn 18+)
  ```

### Tự Động Cập Nhật
- Khi thay đổi nội dung → Dropdown bảng tự cập nhật
- Dropdown bảng reset = "-- Tất cả bảng --"
- Sau khi chọn bảng → Tự động load kết quả

## JavaScript Function

```javascript
function updateGroupFilter() {
  // Lấy category được chọn
  // Hiển thị chỉ các group có category_id khớp
  // Reset group filter khi category thay đổi
}
```

## HTML Attributes

Mỗi option bảng có attribute:
```html
<option value="5" data-category-id="2">
  Bảng B (Nam đôi 18+)
</option>
```

- `value="5"` - ID của bảng
- `data-category-id="2"` - ID của nội dung tương ứng

## API Query Params

Khi gửi request tới API:
```
GET /homeyard/tournaments/1/rankings?category_id=2&group_id=5
```

- `category_id=2` - Lọc VĐV của nội dung ID=2
- `group_id=5` - Lọc VĐV của bảng ID=5

## Xử Lý Khi Không Có Dữ Liệu

```
Nội dung: Nam đơn 18+
Bảng:    Bảng A (không có VĐV)
↓
Kết quả: "Chưa có dữ liệu xếp hạng"
```

## Lợi Ích Của Cách Lọc Này

✅ **Rõ ràng:** Bảng B từ nội dung A khác vs Bảng B từ nội dung B
✅ **Tránh nhầm lẫn:** Hiển thị nội dung ngay tên bảng  
✅ **Dễ sử dụng:** Tự động reset khi thay đổi category
✅ **Chính xác:** Chỉ lọc đúng dữ liệu phù hợp

## Testing Checklist

- [ ] Chọn Category → Bảng dropdown update
- [ ] Lọc Category → Bảng reset
- [ ] Chọn Bảng B → Chỉ VĐV bảng B hiển thị
- [ ] Chọn Category + Bảng → Kết quả chính xác
- [ ] Reset filter → Hiển thị toàn bộ
- [ ] In/Xuất CSV → Đúng dữ liệu được lọc
