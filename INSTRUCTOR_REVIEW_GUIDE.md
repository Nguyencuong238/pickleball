# Instructor Review System - Implementation Guide

## Tính Năng Được Thêm
Hệ thống review giảng viên cho phép người dùng đánh giá giảng viên với các tính năng:

- ⭐ Đánh giá từ 1-5 sao
- 📝 Nội dung review (tùy chọn)
- 🏷️ Tags/Điểm mạnh (thân thiện, chuyên nghiệp, kiên nhẫn, v.v.)
- 🔐 Xác thực người dùng bắt buộc
- 💾 Lưu vào bảng `instructor_reviews`
- 📊 Cập nhật rating và review count tự động

## File Được Thêm/Sửa

### 1. Controller - API
**File:** `app/Http/Controllers/Api/InstructorReviewController.php`
- `store()` - Lưu review mới
- `update()` - Cập nhật review
- `destroy()` - Xóa review
- `getByInstructor()` - Lấy danh sách review
- `updateInstructorRating()` - Cập nhật rating giảng viên

### 2. Routes - API
**File:** `routes/api.php`
```php
POST   /api/instructor-review              # Tạo review
PUT    /api/instructor-review/{review}     # Cập nhật review
DELETE /api/instructor-review/{review}     # Xóa review
GET    /api/instructor/{instructorId}/reviews # Lấy danh sách review
```

### 3. View - Form & CSS & JavaScript
**File:** `resources/views/front/instructors/instructor_detail.blade.php`

#### Form Input:
- Rating Stars (1-5 sao)
- Textarea cho nội dung review
- Checkbox cho tags (Thân thiện, Chuyên nghiệp, Kiên nhẫn, Dễ hiểu, Truyền cảm hứng, Linh hoạt)

#### CSS Classes:
- `.star-rating-input` - Styling cho star rating
- `.tags-checkbox` - Styling cho tags
- `.review-form-section` - Container cho form

#### JavaScript:
- Xử lý click trên stars
- Hiển thị text feedback dựa vào rating
- Update character count
- Submit form bằng fetch API
- Hiển thị alert message
- Reload page khi thành công

## Cách Sử Dụng

### Từ Frontend:
1. Người dùng điều hướng đến trang chi tiết giảng viên `/instructors/{id}`
2. Scroll xuống phần "Đánh giá từ học viên"
3. Tìm form "Để lại đánh giá của bạn"
4. Chọn số sao (bắt buộc)
5. Nhập nội dung review (tùy chọn)
6. Chọn điểm mạnh (tùy chọn)
7. Click "Gửi đánh giá"

### Từ API:
```bash
# Tạo review mới
POST /api/instructor-review
Content-Type: application/json
X-CSRF-TOKEN: {token}

{
  "instructor_id": 1,
  "rating": 5,
  "content": "Giảng viên rất tốt!",
  "tags": "Thân thiện,Chuyên nghiệp"
}

# Response
{
  "success": true,
  "message": "Đánh giá của bạn đã được gửi thành công!",
  "review": {
    "id": 1,
    "rating": 5,
    "content": "Giảng viên rất tốt!",
    "user_name": "Nguyễn Văn A",
    "created_at": "1 hour ago"
  }
}
```

## Database Schema

Bảng `instructor_reviews`:
```sql
- id (Primary Key)
- instructor_id (Foreign Key → instructors)
- user_id (Foreign Key → users)
- rating (1-5)
- content (text)
- tags (JSON array)
- is_approved (boolean, default: true)
- created_at, updated_at
```

## Validation Rules

### Review Submission:
- `instructor_id`: required, exists in instructors table
- `rating`: required, integer, between 1-5
- `content`: optional, string, max 1000 characters
- `tags`: optional, string

### Constraints:
- Người dùng phải đăng nhập
- Mỗi user chỉ được review 1 lần cho mỗi giảng viên
- Review được auto-approve (có thể thay đổi)

## Features Detail

### Auto-Update Rating:
Khi review được thêm/xóa, rating của giảng viên tự động cập nhật:
```php
- rating = average của tất cả reviews
- reviews_count = số lượng reviews
```

### Tags System:
Tags được lưu dưới dạng JSON array:
```json
["Thân thiện", "Chuyên nghiệp", "Kiên nhẫn"]
```

### UI Feedback:
- 5 sao: "Tuyệt vời!" (xanh lá)
- 4 sao: "Rất tốt" (xanh lá)
- 3 sao: "Bình thường" (vàng)
- 2 sao: "Chưa tốt" (đỏ)
- 1 sao: "Không hài lòng" (đỏ)

## Error Handling

1. **Chưa đăng nhập**: Yêu cầu đăng nhập (401)
2. **Đã review rồi**: Thông báo lỗi (422)
3. **Invalid data**: Validation error
4. **Server error**: Hiển thị error message

## Future Enhancements

- [ ] Admin approval trước khi công khai
- [ ] Reply từ giảng viên
- [ ] Like/helpful votes
- [ ] Report inappropriate reviews
- [ ] Image upload in reviews
- [ ] Verified purchase badge
- [ ] Review statistics dashboard

## Testing

```php
// Tạo test review
POST /api/instructor-review
{
  "instructor_id": 1,
  "rating": 5,
  "content": "Test review",
  "tags": "Thân thiện"
}

// Kiểm tra instructor rating được update
GET /instructors/1
// Check: rating, reviews_count

// Kiểm tra user chỉ review 1 lần
POST /api/instructor-review (lần 2)
// Expect: 422 error "Bạn đã đánh giá giảng viên này rồi"
```
