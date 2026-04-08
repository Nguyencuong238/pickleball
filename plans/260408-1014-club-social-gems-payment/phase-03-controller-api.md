# Phase 3: Controller & API Update

## Muc tieu
Cap nhat controller de xu ly fee validation, error handling, va response data.

## Thay doi

### ClubActivityController::store() + update()
File: `app/Http/Controllers/ClubActivityController.php`
- Them validation rule: `'fee_gems' => 'nullable|integer|min:1|max:10000'`
- Them vao $validated (store da tu dong truyen vao create)

### Api::ClubActivityController::store() + update()
File: `app/Http/Controllers/Api/ClubActivityController.php`
- Tuong tu: them validation `fee_gems`

### ClubActivityParticipantController::rsvp()
File: `app/Http/Controllers/ClubActivityParticipantController.php`
- Catch them `\RuntimeException` (tu GemWalletService khi insufficient balance)
- Tra ve response voi thong tin:
  ```json
  {
    "success": false,
    "message": "So du Gems khong du...",
    "insufficient_gems": true,
    "required": 50,
    "balance": 30
  }
  ```
- Khi thanh cong, tra ve them `gems_charged`:
  ```json
  {
    "success": true,
    "status": "confirmed",
    "message": "Dang ky thanh cong! Da tru 50 Gems.",
    "gems_charged": 50
  }
  ```

### ClubActivityParticipantController::cancelRsvp()
- Tra ve them `gems_refunded` khi co hoan:
  ```json
  {
    "success": true,
    "message": "Da huy dang ky. Hoan 50 Gems.",
    "gems_refunded": 50
  }
  ```

### Api controllers
- Tuong tu web controllers

### ClubActivityController::update() - khoa fee
- Kiem tra `isFeeEditable()` truoc khi cho phep thay doi `fee_gems`
- Neu da co confirmed participants va fee_gems thay doi -> reject voi message "Khong the thay doi phi khi da co nguoi dang ky"

### ClubActivityController::show()
- Truyen them `userGemBalance` vao view (de frontend check truoc khi RSVP)
- Truyen them `exchangeRate` de hien thi quy doi VND

### ClubCheckinController::confirm() + register()
- Ca 2 method deu goi `checkinByPhone()` -> ca 2 can catch `\RuntimeException`
- Catch insufficient balance -> tra ve:
  ```json
  {
    "success": false,
    "message": "Khong du Gems. Can nap them truoc khi check-in.",
    "insufficient_gems": true,
    "required": 50
  }
  ```
- `register()` (user moi, chua co wallet): `GemWalletService::deduct()` se throw "khong du" vi balance = 0
  -> Frontend hien thi "Vui long dang nhap va nap Gems truoc khi tham gia hoat dong co phi"

## Todo
- [ ] Update store/update validation (web + api) - them fee_gems
- [ ] Update update() - khoa fee khi da co confirmed participants
- [ ] Update rsvp() error handling + response (web + api)
- [ ] Update cancelRsvp() response (web + api)
- [ ] Update show() truyen gem balance + exchangeRate
- [ ] Update ClubCheckinController::confirm() + register() - xu ly fee khi check-in
