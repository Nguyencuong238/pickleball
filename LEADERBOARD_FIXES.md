# Bảng Xếp Hạng - Tóm Tắt Sửa Đổi

## 🐛 Vấn Đề Đã Sửa

**Lỗi:** Khi lọc theo bảng (B, C), dữ liệu bị gộp lại không phân biệt
```
Chọn Nội dung: Nam đơn 18+
Chọn Bảng: B
Kết quả: VĐV từ bảng B, C... bị trộn lẫn
```

**Nguyên Nhân:** Dropdown bảng hiển thị tất cả bảng từ tất cả nội dung, không phân biệt

---

## ✅ Giải Pháp Triển Khai

### 1. Cập Nhật HTML
**File:** `/resources/views/home-yard/dashboard.blade.php` (Line 903-924)

```blade
<!-- TRƯỚC -->
<option value="{{ $group->id }}">{{ $group->group_name }}</option>

<!-- SAU -->
<option value="{{ $group->id }}" data-category-id="{{ $group->category_id }}">
  {{ $group->group_name }} ({{ $group->category->category_name ?? 'N/A' }})
</option>
```

**Thay đổi:**
- Thêm `data-category-id` attribute để lưu category ID
- Hiển thị nội dung tương ứng trong tên: `Bảng B (Nam đơn 18+)`

### 2. Thêm JavaScript Function
**File:** `/resources/views/home-yard/dashboard.blade.php` (Line 2229-2270)

```javascript
function updateGroupFilter() {
    const categorySelect = document.getElementById('filterCategory');
    const groupSelect = document.getElementById('filterGroup');
    const selectedCategoryId = categorySelect.value;
    
    // Lặp qua tất cả option bảng
    const allOptions = groupSelect.querySelectorAll('option');
    
    allOptions.forEach((option, index) => {
        if (index === 0) {
            // Luôn hiển thị "-- Tất cả bảng --"
            option.style.display = '';
            return;
        }
        
        const optionCategoryId = option.getAttribute('data-category-id');
        
        // Nếu không chọn category → hiển thị tất cả bảng
        if (!selectedCategoryId) {
            option.style.display = '';
        } 
        // Nếu bảng thuộc category được chọn → hiển thị
        else if (optionCategoryId === selectedCategoryId) {
            option.style.display = '';
        } 
        // Ngược lại → ẩn
        else {
            option.style.display = 'none';
        }
    });
    
    // Reset bảng khi thay đổi category
    if (selectedCategoryId) {
        groupSelect.value = '';
    }
}
```

**Chức năng:**
- Lọc hiển thị option bảng dựa trên category được chọn
- Reset giá trị bảng = "" (Tất cả bảng)
- Tránh hiển thị bảng từ các category khác

### 3. Cập Nhật Event Handler
**File:** `/resources/views/home-yard/dashboard.blade.php` (Line 905)

```blade
<!-- TRƯỚC -->
<select id="filterCategory" class="form-select" onchange="loadRankings()">

<!-- SAU -->
<select id="filterCategory" class="form-select" onchange="updateGroupFilter(); loadRankings()">
```

### 4. Khởi Tạo Filter Khi Load
**File:** `/resources/views/home-yard/dashboard.blade.php` (Line 2222-2227)

```javascript
// TRƯỚC
document.addEventListener('DOMContentLoaded', function() {
    initializeCreateMatchForm();
    initializeEditMatchForm();
    loadRankings();
});

// SAU
document.addEventListener('DOMContentLoaded', function() {
    initializeCreateMatchForm();
    initializeEditMatchForm();
    updateGroupFilter();  // ← THÊM DÒNG NÀY
    loadRankings();
});
```

---

## 📊 Kết Quả Sau Sửa

### Trước
```
Nội dung: Nam đơn 18+
Bảng: [▼] 
      - Bảng A (Nam đơn)
      - Bảng B (Nam đơn) ← Chọn
      - Bảng A (Nữ đơn)  ← Không phải Nam
      - Bảng B (Nữ đơn)  ← Không phải Nam
      - Bảng C (Đôi nam) ← Không phải Nam

Chọn Bảng B → Kết quả trộn lẫn tất cả Bảng B
```

### Sau
```
Nội dung: Nam đơn 18+ (Chọn)
Bảng: [▼] 
      - -- Tất cả bảng --
      - Bảng A (Nam đơn 18+)
      - Bảng B (Nam đơn 18+) ← Chọn
      - Bảng C (Nam đơn 18+)
      
      (Ẩn bảng từ các nội dung khác)

Chọn Bảng B (Nam đơn 18+) → Kết quả CHÍNH XÁC chỉ Bảng B Nam
```

---

## 🔄 Quy Trình Hoạt Động

```
┌─────────────────────────────────┐
│ Người dùng chọn Category        │
├─────────────────────────────────┤
│ onChange event tích kích         │
│ → updateGroupFilter()          │
│   └─ Lọc dropdown bảng         │
│   └─ Reset giá trị bảng = ""   │
│ → loadRankings()               │
│   └─ Load dữ liệu từ API       │
│   └─ Render bảng xếp hạng      │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ Người dùng chọn Group           │
├─────────────────────────────────┤
│ onChange event tích kích         │
│ → loadRankings()               │
│   └─ Load dữ liệu với filter   │
│   └─ Render bảng xếp hạng      │
└─────────────────────────────────┘
```

---

## 🧪 Test Cases

### Test 1: Lọc Category
```
Bước: Chọn "Nam đơn 18+" từ dropdown Nội dung
Kỳ vọng: Dropdown bảng chỉ hiển thị bảng Nam đơn
         Dữ liệu xếp hạng load chỉ Nam đơn
Kết quả: ✅ PASS
```

### Test 2: Lọc Group
```
Bước: Chọn Category "Nam đơn 18+"
      Chọn "Bảng B (Nam đơn 18+)"
Kỳ vọng: Kết quả xếp hạng chỉ hiển thị VĐV Bảng B Nam
         Không có VĐV từ Bảng A, C
Kết quả: ✅ PASS
```

### Test 3: Reset Filter
```
Bước: Đưa cả filter về "-- Tất cả --"
Kỳ vọng: Dropdown bảng hiển thị tất cả bảng
         Kết quả xếp hạng hiển thị toàn bộ VĐV
Kết quả: ✅ PASS
```

### Test 4: Thay Đổi Category
```
Bước: Chọn "Nam đơn 18+"
      Chọn "Bảng B"
      Thay đổi thành "Nữ đơn 18+"
Kỳ vọng: Dropdown bảng tự reset = "-- Tất cả bảng --"
         Dropdown bảng hiển thị bảng Nữ đơn
         Kết quả load bảng Nữ đơn
Kết quả: ✅ PASS
```

---

## 📝 Files Thay Đổi

| File | Dòng | Thay Đổi |
|------|------|----------|
| `/resources/views/home-yard/dashboard.blade.php` | 905 | Thêm `onchange="updateGroupFilter(); loadRankings()"` |
| `/resources/views/home-yard/dashboard.blade.php` | 920 | Thêm `data-category-id` + nội dung vào option |
| `/resources/views/home-yard/dashboard.blade.php` | 2229-2270 | Thêm function `updateGroupFilter()` |
| `/resources/views/home-yard/dashboard.blade.php` | 2225 | Thêm gọi `updateGroupFilter()` |

---

## 🎯 Hưởng Lợi

✅ **Rõ ràng:** Phân biệt Bảng B từ các nội dung khác nhau
✅ **Chính xác:** Lọc dữ liệu đúng theo category + group
✅ **Dễ dùng:** Dropdown tự cập nhật, không cần bấm gì
✅ **An toàn:** Không hiển thị dữ liệu không phù hợp
✅ **Nhanh:** Lọc phía client, không cần API call thêm

---

## 🔗 Liên Quan

- `LEADERBOARD_IMPLEMENTATION.md` - Tài liệu chính
- `LEADERBOARD_FILTER_GUIDE.md` - Hướng dẫn sử dụng bộ lọc
