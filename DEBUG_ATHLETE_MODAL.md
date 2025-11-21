# 🔍 Debug: Lỗi Thêm VĐV Modal

## Lỗi: `Unexpected token '<', "<!DOCTYPE "... is not valid JSON`

### Nguyên nhân
- Server trả về HTML response thay vì JSON
- Thường xảy ra khi:
  1. Middleware auth/role failed → redirect login page (HTML)
  2. Route không tồn tại (404) → HTML error page
  3. Server error (500) → HTML error page
  4. CSRF token không hợp lệ

### Cách Debug

#### 1. Kiểm tra Browser Console
```
F12 → Console → Xem error message
```
Expected: `Server error (HTTP 403). Check console.`
Hoặc: `Server error (HTTP 500). Check console.`

#### 2. Kiểm tra Network Tab
```
F12 → Network → Click submit → Xem request
```
- **URL**: `/homeyard/tournaments/{id}/athletes`
- **Method**: POST
- **Status**: 200, 403, 422, 500, etc.
- **Response Type**: 
  - ✅ JSON = "application/json"
  - ❌ HTML = "text/html"

#### 3. Kiểm tra Request Headers
Phải có:
```
Content-Type: application/json
X-CSRF-TOKEN: {token}
X-Requested-With: XMLHttpRequest
```

#### 4. Kiểm tra Server Logs
```bash
tail -f storage/logs/laravel.log
```
Xem có error message không

### Solutions

#### Solution 1: CSRF Token không hợp lệ
```html
<!-- Kiểm tra meta tag -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```
- Phải có trong `<head>`
- Token phải khác rỗng

#### Solution 2: Middleware Auth failed
- Đăng nhập lại
- Kiểm tra session/cookies

#### Solution 3: Middleware Role failed
- Phải là user có role `home_yard`
- Phải là chủ giải của tournament đó

#### Solution 4: Route sai
```bash
php artisan route:list | findstr athletes.add
```
Kiểm tra:
- Route tồn tại?
- Route là POST?
- Route controller là `HomeYardTournamentController@addAthlete`?

#### Solution 5: Controller không return JSON
- Kiểm tra controller có `response()->json()` không
- Kiểm tra có `$request->isJson()` không
- Kiểm tra có `Content-Type: application/json` header không

### Checklist để Fix

- [ ] CSRF token hợp lệ (check meta tag)
- [ ] Đã đăng nhập (check session)
- [ ] User có role `home_yard`
- [ ] User là chủ giải (check authorization)
- [ ] Route tồn tại (php artisan route:list)
- [ ] Tournament ID hợp lệ
- [ ] Category được chọn hợp lệ
- [ ] Athlete name không để trống
- [ ] Controller return `response()->json()`
- [ ] Không có lỗi validation
- [ ] Server logs không có error

### Test cURL

```bash
# Get CSRF token từ form
curl 'http://localhost/homeyard/dashboard/1' \
  -H 'Cookie: XSRF-TOKEN=...; laravel_session=...' | grep csrf-token

# Thêm VĐV
curl -X POST 'http://localhost/homeyard/tournaments/1/athletes' \
  -H 'Content-Type: application/json' \
  -H 'X-CSRF-TOKEN: YOUR_TOKEN' \
  -H 'X-Requested-With: XMLHttpRequest' \
  -H 'Cookie: XSRF-TOKEN=YOUR_TOKEN; laravel_session=YOUR_SESSION' \
  -d '{
    "category_id": 1,
    "athlete_name": "Test",
    "email": "test@test.com",
    "phone": "0123456789"
  }'
```

### Common Issues & Fixes

| Issue | Cause | Fix |
|-------|-------|-----|
| 403 Forbidden | Not tournament owner | Login as correct user |
| 422 Unprocessable | Validation error | Check form inputs |
| 500 Internal Server | Server error | Check logs |
| 404 Not Found | Route không tồn tại | Update route |
| JSON parse error | HTML response | Check middleware |

---

**Last updated**: Nov 21, 2025
