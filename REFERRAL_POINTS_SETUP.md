# Hệ Thống Thưởng Điểm Referral

## Tính Năng
- Khi người dùng mới đăng ký qua link referral, người giới thiệu sẽ nhận được +10 điểm
- Lịch sử cộng điểm được lưu trong bảng `user_point_transactions`
- Người dùng có thể xem chi tiết lịch sử điểm tại `/user/wallet/history`

## Các Tệp Được Cập Nhật

### 1. AuthController.php
- Thêm import cho UserWallet và UserPointTransaction models
- Thêm method `addReferralPoints()` để cộng điểm khi có referral mới
- Gọi method này trong `register()` khi người dùng mới đăng ký qua referral

### 2. WalletController.php
- Thêm method `history()` để hiển thị lịch sử điểm chi tiết
- Tính toán thống kê:
  - Tổng điểm hiện tại
  - Điểm kiếm được (positive transactions)
  - Điểm đã dùng (negative transactions)
  - Điểm từ referral (type = 'referral')

### 3. UserPointTransaction.php (Model)
- Thêm loại giao dịch 'referral' vào `getTypeLabel()` method

### 4. resources/views/user/wallet/history.blade.php (Mới)
- Tạo view hiển thị lịch sử giao dịch
- Hiển thị thống kê tổng hợp
- Bảng chi tiết giao dịch với phân trang
- Hỗ trợ responsive design

### 5. resources/views/user/referral/index.blade.php
- Thêm button link tới trang lịch sử điểm

### 6. routes/web.php
- Thêm route `/user/wallet/history` gọi tới `WalletController@history`

## Cấu Trúc Bảng Database

### user_wallets
```sql
CREATE TABLE user_wallets (
    id BIGINT PRIMARY KEY,
    user_id BIGINT UNSIGNED UNIQUE,
    points BIGINT DEFAULT 0,
    timestamps
);
```

### user_point_transactions
```sql
CREATE TABLE user_point_transactions (
    id BIGINT PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    points BIGINT,
    type VARCHAR (VARCHAR_LIMIT),  -- earn, use, refund, admin, referral
    description VARCHAR,
    metadata JSON,
    timestamps,
    INDEX user_created
);
```

## Quy Trình Hoạt Động

1. Người dùng A chia sẻ link referral: `/register?ref={REFERRAL_CODE}`
2. Người dùng B đăng ký qua link
3. AuthController.register() xác nhận referral code
4. Nếu hợp lệ:
   - Tạo bản ghi trong bảng `referrals`
   - Gọi `addReferralPoints()` để cộng điểm cho người dùng A
5. `addReferralPoints()` thực hiện:
   - Tạo hoặc cập nhật wallet của người dùng A
   - Cộng 10 điểm vào wallet
   - Tạo bản ghi transaction với type = 'referral'

## User Methods

User model có các method tiện lợi:
```php
$user->addPoints(10, 'referral', 'Description', ['metadata' => 'value']);
$user->getPoints(); // Lấy tổng điểm
$user->pointTransactions(); // Lấy tất cả transaction
$user->getOrCreateWallet(); // Lấy hoặc tạo wallet
```

## Xem Lịch Sử

Người dùng có thể xem lịch sử điểm tại:
- `/user/wallet/history` - Lịch sử chi tiết với thống kê
- `/user/wallet` - Dashboard wallet (nếu có)

## Test

Để kiểm tra hệ thống:
1. Tạo người dùng A với referral code
2. Dùng link referral để tạo người dùng B
3. Kiểm tra bảng `user_point_transactions` của người dùng A
4. Kiểm tra điểm trong bảng `user_wallets` của người dùng A
5. Truy cập `/user/wallet/history` của người dùng A để xem lịch sử
