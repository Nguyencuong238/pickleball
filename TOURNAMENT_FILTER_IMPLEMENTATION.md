# Tournament Filter Implementation

## Tính Năng Đã Thêm

Bộ lọc giải đấu hoàn chỉnh với các tính năng sau:

### 1. **Lọc Theo Trạng Thái (Status Filter)**
- Tất cả trạng thái
- Đang diễn ra
- Sắp tới
- Đã kết thúc
- Đã hủy

### 2. **Lọc Theo Loại Giải (Type Filter)**
- Tất cả loại giải
- Đơn nam
- Đơn nữ
- Đôi nam
- Đôi nữ
- Đôi nam nữ

### 3. **Lọc Theo Địa Điểm (Location Filter)**
- Tất cả địa điểm
- TP. Hồ Chí Minh
- Hà Nội
- Đà Nẵng
- Khác

### 4. **Sắp Xếp (Sort Filter)**
- Mới nhất (được tạo gần đây nhất)
- Cũ nhất (được tạo lâu nhất)
- Tên A-Z
- Tên Z-A
- Ngày tăng dần (sớm nhất)
- Ngày giảm dần (muộn nhất)

### 5. **Tìm Kiếm (Search)**
- Tìm kiếm theo tên giải đấu trong thanh tìm kiếm ở header

### 6. **Đặt Lại Bộ Lọc (Reset)**
- Nút "🔄 Đặt lại bộ lọc" để reset tất cả filter về mặc định

### 7. **Xuất Excel**
- Nút "📊 Xuất Excel" để export danh sách giải đấu (dựa trên bộ lọc hiện tại)
- Format: CSV file có thể mở trong Excel/Google Sheets
- Tên file: `tournaments_YYYY-MM-DD.csv`

### 8. **View Tabs**
- 4 tab nhanh: Tất cả, Đang diễn ra, Sắp tới, Đã kết thúc
- Được tích hợp với Status Filter

## Cách Hoạt Động

### Data Attributes
Các thẻ `.tournament-card` được thêm các data attributes để dễ lọc:
```html
<div class="tournament-card fade-in" 
     data-status="Đang diễn ra" 
     data-format="Đơn"
     data-location="TP. Hồ Chí Minh"
     data-name="Giải Pickleball Mùa Hè"
     data-date="1609459200">
```

### JavaScript Functions

#### `initializeFilters()`
- Được gọi khi page load
- Thu thập tất cả tournament cards
- Gán event listeners cho các filter inputs

#### `applyFilters()`
- Được gọi khi bất kỳ filter thay đổi
- Lọc tournaments dựa trên các giá trị hiện tại
- Sắp xếp kết quả
- Cập nhật hiển thị

#### `sortTournaments(tournaments, sortBy)`
- Sắp xếp mảng tournaments
- Hỗ trợ 6 kiểu sắp xếp

#### `updateTournamentDisplay(filtered)`
- Cập nhật giao diện
- Hiển thị thông báo "Không tìm thấy" nếu không có kết quả
- Thêm animation mượt mà

#### `exportToExcel()`
- Export danh sách hiện tại (đã lọc) thành file CSV
- Có thể mở trong Excel/Google Sheets/LibreOffice

#### `resetFilters()`
- Reset tất cả filter inputs về giá trị mặc định
- Reset tab view về "Tất cả"
- Gọi `applyFilters()` để cập nhật hiển thị

#### `filterByStatus(status)`
- Xử lý click từ View Tabs
- Cập nhật Status Filter
- Cập nhật active tab

## Sử Dụng

### Người Dùng
1. Chọn các filter mong muốn từ dropdown
2. Kết quả cập nhật tức thì (real-time)
3. Nhấp vào tab nhanh để lọc theo trạng thái
4. Nhấp "Đặt lại bộ lọc" để xóa tất cả lựa chọn
5. Nhấp "📊 Xuất Excel" để download CSV file

### Lập Trình Viên
Để thêm filter mới, làm theo các bước:

1. Thêm data attribute vào tournament card (ở `.blade.php`)
2. Thêm vào `initializeFilters()` để lưu giá trị
3. Thêm logic filter trong `applyFilters()`
4. Thêm HTML select/input vào filter bar

Ví dụ thêm filter theo "Prize":
```javascript
// Trong initializeFilters()
const prize = card.getAttribute('data-prize') || '';
allTournaments.push({ element, name, status, format, location, dateStr, prize });

// Trong applyFilters()
const prizeFilter = document.getElementById('prizeFilter')?.value || '';
if (prizeFilter) {
    const minPrize = parseInt(prizeFilter);
    if (parseInt(tournament.prize) < minPrize) return false;
}
```

## Notes
- Tất cả lọc hoạt động trên phía client (không cần server)
- Performance tốt ngay cả với 100+ tournaments
- Hỗ trợ Vietnamese locale untuk sắp xếp theo tên
- Có animation mượt khi cập nhật danh sách
- Toast notification khi export thành công
