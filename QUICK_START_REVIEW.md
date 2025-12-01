# Quick Start - Instructor Review Feature

## 📦 What Was Created

A complete instructor review system allowing users to rate and review instructors with:
- ⭐ 1-5 star ratings
- 📝 Written reviews
- 🏷️ Strength tags
- 💾 Automatic database storage
- 📊 Automatic rating calculation

## 🚀 Getting Started (5 Minutes)

### 1. Verify Installation
```bash
# Check if table exists
php artisan tinker
>>> DB::table('instructor_reviews')->count()
// Should return 0 or a number, not an error
```

### 2. Test the Feature
1. Open: `http://localhost/instructors/1` (any instructor)
2. Login with any user account
3. Scroll down to "Để lại đánh giá của bạn" section
4. Submit a review with stars
5. Should see success message

### 3. Verify in Database
```bash
php artisan tinker
>>> App\Models\InstructorReview::all()
>>> App\Models\Instructor::find(1)->reviews
```

## 📁 Files Created/Modified

| File | Type | Purpose |
|------|------|---------|
| `app/Http/Controllers/Api/InstructorReviewController.php` | Controller | Handle review operations |
| `routes/api.php` | Routes | API endpoints |
| `resources/views/front/instructors/instructor_detail.blade.php` | View | Review form UI |

## 🔌 API Endpoints

```
POST   /api/instructor-review              Create review
PUT    /api/instructor-review/{id}         Update review
DELETE /api/instructor-review/{id}         Delete review
GET    /api/instructor/{id}/reviews        List reviews
```

## 💾 Database Structure

Table: `instructor_reviews`
```
id (int, PK)
instructor_id (int, FK) → instructors
user_id (int, FK) → users
rating (1-5)
content (text, max 1000)
tags (JSON array)
is_approved (boolean, default: true)
created_at, updated_at
```

## 🎯 Key Features

### For Users:
- Can leave 1 review per instructor
- Can rate 1-5 stars
- Can add written review
- Can select multiple strength tags
- See real-time feedback on rating

### For System:
- Auto-calculates instructor average rating
- Auto-updates review count
- Prevents duplicate reviews
- Validates all input
- Stores tags as JSON

## 🔐 Security

- ✅ Requires authentication
- ✅ CSRF protection
- ✅ Input validation
- ✅ SQL injection prevention
- ✅ One review per user per instructor

## 📊 Example Usage

### Step 1: User clicks "Để lại đánh giá"
```
Form appears with:
- 5 stars to click
- Textarea for review
- 6 checkbox tags
- Submit button
```

### Step 2: User fills form
```
Rating: 5 stars
Content: "Giảng viên rất tuyệt vời!"
Tags: [✓] Thân thiện [✓] Chuyên nghiệp
```

### Step 3: Submit review
```
POST /api/instructor-review
{
  "instructor_id": 1,
  "rating": 5,
  "content": "Giảng viên rất tuyệt vời!",
  "tags": "Thân thiện,Chuyên nghiệp"
}
```

### Step 4: Success
```
✅ Message: "Đánh giá của bạn đã được gửi thành công!"
🔄 Page reloads
📊 Instructor rating updated
```

## ⚙️ Configuration Options

### Change default approval status:
```php
// In InstructorReviewController.php store() method
'is_approved' => true, // Change to false for manual approval
```

### Change max review length:
```php
// In view file, in textarea
maxlength="1000" // Change to desired length
```

### Add more tags:
```html
<!-- In instructor_detail.blade.php -->
<label class="tag-option">
    <input type="checkbox" name="tags" value="Your New Tag">
    <span>Your New Tag</span>
</label>
```

## 🧪 Test Cases

### Test 1: Successful Review
```
1. Go to instructor page
2. Login
3. Fill form with rating + content
4. Click submit
Expected: Success message, page reloads, review appears
```

### Test 2: Duplicate Review
```
1. Submit review
2. Try to submit another for same instructor
Expected: Error "Bạn đã đánh giá giảng viên này rồi"
```

### Test 3: Unauthenticated
```
1. Logout
2. Try to submit review
Expected: Error "Vui lòng đăng nhập"
```

### Test 4: Rating Update
```
1. Instructor has 0 reviews, rating = 0
2. Add review with 5 stars
3. Check instructor rating
Expected: instructor.rating = 5, reviews_count = 1
```

## 🎨 UI Elements

### Star Rating
- Interactive hover effect
- Yellow (#ffc107) when selected
- Shows feedback text below

### Rating Feedback
- 5 stars: "Tuyệt vời!" (green)
- 4 stars: "Rất tốt" (green)
- 3 stars: "Bình thường" (yellow)
- 2 stars: "Chưa tốt" (red)
- 1 star: "Không hài lòng" (red)

### Tags
- Styled as pills/badges
- Highlight on hover
- Show selected state with green

## 📱 Responsive Design

- ✅ Works on desktop
- ✅ Works on tablet
- ✅ Works on mobile
- ✅ Touch-friendly stars
- ✅ Full-width form

## 🔄 Auto-Updates

When a review is created/updated/deleted:
```
Instructor fields updated:
- rating = AVG(all reviews.rating)
- reviews_count = COUNT(all reviews)
```

## 🐛 Common Issues & Fixes

| Issue | Cause | Fix |
|-------|-------|-----|
| 404 on submit | Route not found | Run `php artisan cache:clear` |
| 500 error | Controller not found | Check file path and namespace |
| No DB updates | Table doesn't exist | Run `php artisan migrate` |
| CSRF error | Token missing | Ensure `@csrf` in form |
| Stars not working | CSS/JS issue | Check console for errors |

## 📚 Learning Resources

- **View**: `resources/views/front/instructors/instructor_detail.blade.php` - Form & styling
- **Controller**: `app/Http/Controllers/Api/InstructorReviewController.php` - Business logic
- **Model**: `app/Models/InstructorReview.php` - Data structure
- **Routes**: `routes/api.php` - API endpoints

## ✅ Done!

The review system is fully implemented and ready to use. Just:
1. Start your server
2. Login
3. Go to any instructor page
4. Start reviewing!

For detailed documentation, see:
- `INSTRUCTOR_REVIEW_GUIDE.md` - Complete guide
- `INSTRUCTOR_REVIEW_CHECKLIST.md` - Implementation checklist

---

**Implemented by**: Amp
**Date**: 2025-12-01
**Status**: ✅ Ready for Production
