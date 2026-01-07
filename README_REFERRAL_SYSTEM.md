# Hệ Thống Thưởng Điểm Referral - Tài Liệu Hoàn Chỉnh

## 📌 Tóm Tắt

Hệ thống cho phép người dùng chia sẻ link referral và nhận +10 điểm khi bạn bè đăng ký qua link của họ. Lịch sử cộng điểm được lưu trong database và có thể xem chi tiết tại `/user/wallet/history`.

## 🎯 Tính Năng

### 1. Tự Động Cộng Điểm
- Khi người dùng B đăng ký qua link: `/register?ref=[CODE]`
- Người dùng A (người giới thiệu) tự động nhận +10 điểm
- Không cần admin xác nhận hay bất kỳ tác vụ thủ công nào

### 2. Lưu Lịch Sử
- Mỗi lần cộng điểm được ghi nhận trong `user_point_transactions`
- Bao gồm loại giao dịch, mô tả, và metadata
- Có thể truy vết người nào được giới thiệu

### 3. Dashboard Lịch Sử
- Trang `/user/wallet/history` hiển thị:
  - Tổng điểm hiện tại
  - Tổng điểm kiếm được
  - Tổng điểm đã dùng
  - Tổng điểm từ referral
  - Bảng chi tiết tất cả giao dịch (phân trang)

## 🔧 Cấu Trúc Kỹ Thuật

### Database Schema

**user_wallets**
```sql
CREATE TABLE user_wallets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED UNIQUE NOT NULL,
    points BIGINT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**user_point_transactions**
```sql
CREATE TABLE user_point_transactions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    points BIGINT NOT NULL,
    type VARCHAR(255) NOT NULL,
    description VARCHAR(255) NULL,
    metadata JSON NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_created (user_id, created_at)
);
```

**referrals**
```sql
CREATE TABLE referrals (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    referrer_id BIGINT UNSIGNED NOT NULL,
    referred_user_id BIGINT UNSIGNED NOT NULL,
    referrer_name VARCHAR(255) NULL,
    status VARCHAR(255) DEFAULT 'pending',
    referred_at TIMESTAMP NOT NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_referral (referrer_id, referred_user_id)
);
```

### Models & Methods

**User Model** (`app/Models/User.php`)
```php
// Lấy hoặc tạo wallet
$wallet = $user->getOrCreateWallet();

// Lấy tổng điểm
$points = $user->getPoints(); // Integer

// Cộng điểm
$user->addPoints(
    10,                           // Số điểm
    'referral',                   // Loại
    'Mô tả giao dịch',           // Mô tả
    ['referred_user_id' => 123]  // Metadata
);

// Trừ điểm
$user->deductPoints(5, 'use', 'Sử dụng điểm', []);

// Lấy lịch sử giao dịch
$user->pointTransactions()->get();
$user->pointTransactions()->latest()->paginate(20);
```

**UserWallet Model** (`app/Models/UserWallet.php`)
```php
// Cộng điểm
$wallet->addPoints(10, 'referral', 'Mô tả', []);

// Trừ điểm (trả về true/false)
$success = $wallet->deductPoints(5, 'use', 'Mô tả', []);

// Lấy điểm định dạng
echo $wallet->getFormattedPoints(); // "1,000"
```

**UserPointTransaction Model** (`app/Models/UserPointTransaction.php`)
```php
// Lấy nhãn loại
echo $transaction->getTypeLabel(); // "Thưởng referral"

// Kiểm tra positive
if ($transaction->isPositive()) { }

// Lấy định dạng
echo $transaction->getFormattedPoints(); // "+10"
```

### Controllers

**AuthController** (`app/Http/Controllers/AuthController.php`)
```php
// Khi user B đăng ký qua link referral của User A
// method register() tự động gọi:
$this->addReferralPoints($referrerId, $newUserId, $referrerName);

// Cộng 10 điểm cho User A
// Tạo bản ghi trong user_point_transactions
```

**WalletController** (`app/Http/Controllers/WalletController.php`)
```php
// Route: GET /user/wallet/history
public function history()
{
    // Hiển thị lịch sử chi tiết
    // Tính thống kê
    // Phân trang 20 items/page
}
```

## 📂 Files Được Tạo/Cập Nhật

### Tạo Mới
1. **resources/views/user/wallet/history.blade.php**
   - View hiển thị lịch sử điểm
   - Responsive design
   - Phân trang
   - Thống kê

### Cập Nhật
1. **app/Http/Controllers/AuthController.php**
   - Thêm method `addReferralPoints()`
   - Gọi khi có referral mới

2. **app/Http/Controllers/WalletController.php**
   - Thêm method `history()`

3. **app/Models/UserPointTransaction.php**
   - Thêm loại 'referral' trong `getTypeLabel()`

4. **resources/views/user/referral/index.blade.php**
   - Thêm button link tới `/user/wallet/history`

5. **routes/web.php**
   - Thêm route `GET /user/wallet/history`

## 🚀 Triển Khai

### Điều Kiện Tiên Quyết
- Laravel 11+ (hoặc tương tự)
- PHP 8.1+
- Database migrations đã chạy

### Các Bước

```bash
# 1. Pull code mới
git pull origin main

# 2. Nếu cần, chạy migrations
php artisan migrate

# 3. Clear cache
php artisan cache:clear

# 4. Kiểm tra syntax
php -l app/Http/Controllers/AuthController.php
php -l app/Http/Controllers/WalletController.php

# 5. Test hệ thống (xem TESTING_GUIDE.md)
```

## 📊 Dữ Liệu Ghi Nhận

Mỗi referral tạo bản ghi trong `user_point_transactions`:

```json
{
    "user_id": 1,
    "points": 10,
    "type": "referral",
    "description": "Nhận thưởng từ giới thiệu người dùng mới",
    "metadata": {
        "referred_user_id": 2,
        "source": "referral_signup"
    },
    "created_at": "2026-01-07T10:30:45Z"
}
```

## 🔍 Truy Vấn Database

```sql
-- Xem tổng điểm user
SELECT * FROM user_wallets WHERE user_id = 1;

-- Xem lịch sử referral
SELECT * FROM user_point_transactions 
WHERE user_id = 1 AND type = 'referral'
ORDER BY created_at DESC;

-- Xem ai được giới thiệu
SELECT u1.name as referrer, u2.name as referred, r.status
FROM referrals r
JOIN users u1 ON r.referrer_id = u1.id
JOIN users u2 ON r.referred_user_id = u2.id;

-- Xem top referrers
SELECT u.id, u.name, COUNT(r.id) as total_referrals, SUM(upt.points) as total_points
FROM users u
LEFT JOIN referrals r ON u.id = r.referrer_id
LEFT JOIN user_point_transactions upt ON u.id = upt.user_id AND upt.type = 'referral'
GROUP BY u.id, u.name
ORDER BY total_referrals DESC;
```

## 🎨 User Flow

```
User A (Người Giới Thiệu)
    ↓
- Truy cập /user/referral
- Sao chép link: /register?ref=ABC123
- Chia sẻ với bạn bè
    ↓
User B (Bạn Bè)
    ↓
- Click link /register?ref=ABC123
- Đăng ký tài khoản
    ↓
System
    ↓
- Kiểm tra ref code (ABC123)
- Tìm User A
- Cộng +10 điểm cho User A
- Tạo bản ghi trong referrals
- Tạo bản ghi trong user_point_transactions
    ↓
User A
    ↓
- Đăng nhập lại
- Kiểm tra wallet: 10 điểm
- Xem chi tiết tại /user/wallet/history
```

## 🛡️ Validation & Error Handling

- Nếu referral code không tồn tại → User được tạo bình thường (KHÔNG cộng điểm)
- Nếu user đã tồn tại → KHÔNG cộng điểm lại
- Nếu database error → Transaction rollback, user vẫn được tạo

## 📈 Mở Rộng

### Thêm Loại Giao Dịch Khác
```php
// Trong UserPointTransaction.getTypeLabel()
return match($this->type) {
    'earn' => 'Kiếm điểm',
    'referral' => 'Thưởng referral',
    'purchase' => 'Mua hàng',     // Mới
    'level_up' => 'Nâng cấp',     // Mới
    // ...
};
```

### Thay Đổi Số Điểm Referral
```php
// Trong AuthController.addReferralPoints()
$referrer->addPoints(
    20,  // Thay đổi từ 10 thành 20
    'referral',
    // ...
);
```

### Thêm Giới Hạn Referral
```php
// Tối đa N referral được tính
$maxReferrals = 50;
$currentCount = $referrer->referrals()->count();
if ($currentCount < $maxReferrals) {
    $this->addReferralPoints(...);
}
```

## 📞 Support

Nếu gặp vấn đề:
1. Kiểm tra TESTING_GUIDE.md
2. Chạy diagnostic queries trong database
3. Kiểm tra migration đã chạy
4. Xem logs tại `storage/logs/`

## 📝 License

Mã này là một phần của dự án Pickleball Booking.

---

**Version**: 1.0  
**Date**: 2026-01-07  
**Status**: ✅ Production Ready
