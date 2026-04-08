# Phase 4: Frontend UI Update

## Muc tieu
Hien thi phi Gems tren giao dien va xu ly UX thanh toan.

## Thay doi

### Activity Create/Edit form
File: `resources/views/clubs/activities/create.blade.php` + `edit.blade.php`
- Them input `fee_gems` (optional number field)
- Label: "Phi tham gia (Gems)" - placeholder: "De trong neu mien phi"
- Hien thi quy doi VND ben canh (fee_gems * exchange_rate)

### Activity Show page
File: `resources/views/clubs/activities/show.blade.php`
- Hien thi badge phi: "50 Gems" hoac "Mien phi"
- Khi user chua dang ky + activity co phi:
  - Hien thi so du Gems hien tai
  - Nut "Dang ky (50 Gems)" thay vi "Dang ky"
  - Neu khong du Gems: disable nut, hien thi "Nap Gems" link
- Khi user da dang ky (confirmed):
  - Hien thi "Da thanh toan 50 Gems"
  - Nut huy: "Huy dang ky (hoan 50 Gems)" neu chua bat dau

### Activity Index/List page
File: `resources/views/clubs/activities/index.blade.php`
- Hien thi badge phi ben canh tieu de: "50 Gems" (mau vang) hoac khong hien gi neu mien phi
- Giup user biet truoc activity nao co phi tu danh sach

### Waitlisted user view (trong show page)
- Khi user dang waitlisted + activity co phi:
  - Hien thi "Ban dang trong danh sach cho. Se bi tru X Gems khi co cho."
  - Nut huy: "Huy cho" (khong de cap hoan vi chua thu)

### Check-in page (QR open_play)
File: `resources/views/front/clubs/checkin.blade.php`
- Hien thi thong tin phi tren trang check-in: "Hoat dong nay co phi: 50 Gems"
- Khi confirm/register fail (insufficient_gems):
  - Hien thi thong bao "Khong du Gems" + link dang nhap/nap Gems
- User moi (register flow) + activity co phi:
  - Hien thi "Vui long dang nhap va nap Gems truoc khi tham gia"
- User da RSVP + da thanh toan: check-in binh thuong

### Edit form - khoa fee
File: `resources/views/clubs/activities/edit.blade.php`
- Neu da co confirmed participants: disable input fee_gems + hien thi note "Khong the thay doi phi khi da co nguoi dang ky"

### RSVP JavaScript
- Xu ly response `insufficient_gems`: hien thi thong bao + link nap Gems
- Xu ly response `gems_charged`: hien thi confirm message
- Xu ly response `gems_refunded`: hien thi hoan tien message

## Todo
- [ ] Update create/edit form - them fee_gems input + khoa khi co nguoi dang ky
- [ ] Update index/list - hien thi fee badge
- [ ] Update show page - hien thi phi, so du, nut dang ky co gia
- [ ] Update show page - waitlisted user message ("Se bi tru X Gems khi co cho")
- [ ] Update check-in page - hien thi phi + xu ly insufficient Gems
- [ ] Update RSVP JS - xu ly insufficient_gems + gems_charged
- [ ] Update cancel JS - xu ly gems_refunded
