# Hướng Dẫn Kiểm Tra Hệ Thống Thưởng Điểm Referral

## 1. Test Tạo Referral & Cộng Điểm

### Bước 1: Tạo User A (Người Giới Thiệu)
```
URL: /register
- Name: "Người Giới Thiệu"
- Email: "introducer@example.com"
- Password: "password123"
- Chấp nhận Terms
```

**Kết quả mong đợi**:
- User A được tạo thành công
- Được gán referral code (ví dụ: ABC12345)
- Được redirect tới profile edit

### Bước 2: Lấy Referral Link
```
Truy cập: /user/referral
```

**Kết quả mong đợi**:
- Hiển thị mã referral code
- Hiển thị link: `http://localhost/register?ref=ABC12345`
- Có button "📊 Xem Lịch Sử Điểm"

### Bước 3: Sao Chép Link & Tạo User B (Người Được Giới Thiệu)
```
URL: /register?ref=ABC12345
- Name: "Người Được Giới Thiệu"
- Email: "referred@example.com"
- Password: "password123"
- Chấp nhận Terms
```

**Kết quả mong đợi**:
- User B được tạo thành công
- User B có `referred_by = User A's ID`
- User A nhận +10 điểm

### Bước 4: Kiểm Tra Database (Query)
```sql
-- Kiểm tra wallet của User A
SELECT * FROM user_wallets WHERE user_id = [User A ID];
-- Mong đợi: points = 10

-- Kiểm tra lịch sử giao dịch của User A
SELECT * FROM user_point_transactions WHERE user_id = [User A ID] AND type = 'referral';
-- Mong đợi: 1 bản ghi với points = 10, description = "Nhận thưởng từ giới thiệu người dùng mới"

-- Kiểm tra referral record
SELECT * FROM referrals WHERE referrer_id = [User A ID];
-- Mong đợi: 1 bản ghi với referred_user_id = [User B ID], status = 'completed'
```

### Bước 5: Xem Lịch Sử Điểm
```
Đăng nhập với User A
URL: /user/wallet/history
```

**Kết quả mong đợi**:
- Hiển thị "💰 Lịch Sử Điểm"
- Tổng Hợp Điểm:
  - Điểm Hiện Tại: 10
  - Điểm Kiếm Được: 10
  - Điểm Đã Dùng: 0
  - Từ Referral: 10
- Bảng giao dịch:
  - Loại: "Thưởng referral" (badge xanh)
  - Mô Tả: "Nhận thưởng từ giới thiệu người dùng mới"
  - Điểm: "+10"
  - Ngày: Hôm nay

## 2. Test Multiple Referrals

### Tạo User C từ referral của User A
```
URL: /register?ref=[User A's Code]
```

**Kiểm tra sau**:
- User A có 2 bản ghi trong referrals
- User A có 2 bản ghi trong user_point_transactions (type = 'referral')
- User A có tổng điểm = 20
- Lịch sử sắp xếp theo ngày mới nhất trước

## 3. Test Edge Cases

### Test Invalid Referral Code
```
URL: /register?ref=INVALID123
```

**Kết quả mong đợi**:
- User được tạo bình thường
- KHÔNG có bản ghi trong referrals
- KHÔNG có cộng điểm cho referrer

### Test Referral Code Không Tồn Tại
- Nếu xóa user có referral code
- Người đăng ký với code đó sẽ không được cộng điểm

### Test Không Có Referral Code
```
URL: /register
```

**Kết quả mong đợi**:
- User được tạo bình thường
- KHÔNG có bản ghi trong referrals

## 4. Test UI Responsiveness

### Desktop
- Mở `/user/wallet/history` trên desktop
- Kiểm tra hiển thị bảng

### Mobile
- Mở `/user/wallet/history` trên mobile
- Kiểm tra responsive design
- Bảng phải scroll hoặc collapse

## 5. Test Phân Trang

### Tạo 25+ referrals cho User A
- Tạo 25+ Users từ referral code của User A
- Mỗi lần sẽ cộng +10 điểm

### Kiểm tra `/user/wallet/history`
- Trang 1 hiển thị 20 items
- Có button "Tiếp →"
- Trang 2 hiển thị items còn lại
- Có button "← Trước"

## 6. Diagnostic Queries

Nếu có vấn đề, chạy các query này:

```sql
-- Kiểm tra toàn bộ users
SELECT id, name, email, referral_code FROM users LIMIT 10;

-- Kiểm tra user A
SELECT id, name, referred_by FROM users WHERE email = 'introducer@example.com';

-- Kiểm tra tất cả wallets
SELECT u.id, u.name, w.points FROM users u LEFT JOIN user_wallets w ON u.id = w.user_id;

-- Kiểm tra tất cả transactions
SELECT upt.id, u.name, upt.type, upt.points, upt.description, upt.created_at 
FROM user_point_transactions upt 
JOIN users u ON upt.user_id = u.id 
ORDER BY upt.created_at DESC;

-- Kiểm tra referrals
SELECT r.id, u1.name as referrer, u2.name as referred, r.status, r.created_at 
FROM referrals r 
JOIN users u1 ON r.referrer_id = u1.id 
JOIN users u2 ON r.referred_user_id = u2.id 
ORDER BY r.created_at DESC;
```

## 7. Troubleshooting

### Lỗi: User A không nhận điểm
1. Kiểm tra `app/Models/User.php` có method `addPoints()`
2. Kiểm tra `app/Models/UserWallet.php` có method `addPoints()`
3. Kiểm tra migrations đã chạy: `user_wallets`, `user_point_transactions`
4. Kiểm tra AuthController.php line 89 có gọi `addReferralPoints()`

### Lỗi: Lịch sử không hiển thị
1. Kiểm tra route `/user/wallet/history` tồn tại
2. Kiểm tra WalletController.php có method `history()`
3. Kiểm tra view `resources/views/user/wallet/history.blade.php` tồn tại

### Lỗi: Database error
1. Chạy: `php artisan migrate`
2. Kiểm tra `.env` database connection
3. Kiểm tra user có quyền truy cập database

## 8. Success Criteria

✅ Hệ thống thành công nếu:
- [ ] User A nhận +10 điểm khi User B đăng ký
- [ ] Điểm được lưu trong `user_wallets`
- [ ] Lịch sử được lưu trong `user_point_transactions`
- [ ] `/user/wallet/history` hiển thị đúng dữ liệu
- [ ] Phân trang hoạt động
- [ ] Responsive design OK
- [ ] Loại giao dịch hiển thị "Thưởng referral"
- [ ] Metadata chứa đúng thông tin

## 9. Performance Notes

- Lịch sử phân trang 20 items/page
- Index trên (user_id, created_at) giúp query nhanh
- Metadata là JSON - có thể mở rộng dễ dàng
