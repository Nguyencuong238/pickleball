# Brainstorm: League Registration Flow

## Problem Statement
Hien tai admin them VDV vao team bang cach tim user (name/email). Can tao flow de VDV tu dang ky vao league qua trang public, admin duyet, roi phan bo vao team.

## Agreed Solution

### Flow tong quan
```
VDV truy cap link public league
→ Nhap form dang ky (N VDV theo yeu cau) + upload anh chuyen khoan
→ Auto-create user neu chua co (match by SDT)
→ Admin duyet trong tab "Dang ky" cua league management
→ VDV approved vao pool available
→ Admin mo modal "Them VDV" → 2 tab:
  - Tab 1: Chon tu pool (hien nhom, ho tro "Them ca nhom")
  - Tab 2: Tim user (giu nguyen)
→ Chon VDV → Phan bo vao team → Xoa khoi pool
```

### Database Changes

**Bang moi: `league_registrations`** (nhom dang ky)
| Column | Type | Note |
|--------|------|------|
| id | bigint PK | |
| league_id | FK leagues | |
| payment_proof | varchar | Anh chuyen khoan (1 anh/nhom) |
| status | enum | pending, approved, rejected |
| admin_note | text nullable | Ghi chu khi duyet |
| created_at, updated_at | timestamps | |

**Bang moi: `league_registration_players`** (VDV trong nhom)
| Column | Type | Note |
|--------|------|------|
| id | bigint PK | |
| league_registration_id | FK | |
| user_id | FK users nullable | Link sau khi match/create user |
| phone | varchar | Uu tien match user by SDT |
| name | varchar | |
| skill_level | varchar | Diem trinh |
| province | varchar | Tinh thanh |
| gender | enum male/female | |
| birthday | date | |
| photo | varchar nullable | Anh VDV |
| message | text nullable | Loi nhan |
| created_at | timestamp | |

**Cot moi trong `leagues`:**
| Column | Type | Note |
|--------|------|------|
| required_players_per_registration | tinyint default 1 | So VDV bat buoc/lan DK (1,2,4) |
| registration_fee | decimal nullable | Phi dat coc |
| registration_deadline | datetime nullable | Han chot dang ky |

### UI Changes

**1. Tao league form** - them 3 field moi (required_players, fee, deadline)

**2. Trang public dang ky league** (route: `/leagues/{slug}/register`)
- Form dong: render N player form theo `required_players_per_registration`
- Upload 1 anh chuyen khoan
- Check deadline truoc khi cho dang ky
- Auto-create user by SDT neu chua ton tai

**3. Tab "Dang ky" trong league management (admin)**
- Danh sach nhom dang ky voi status badge
- Xem anh chuyen khoan
- Nut Duyet / Tu choi + admin_note
- Filter: pending / approved / rejected

**4. Modal "Them VDV vao team" - them tab**
- Tab "VDV da duyet": hien nhom dang ky, nut "Them ca nhom" + chon tung nguoi
- Tab "Tim user": giu nguyen flow hien tai
- VDV da vao team thi an khoi list
- Khi "Them ca nhom": auto set captain = nguoi dau tien

### Business Rules
- 1 VDV chi duoc 1 team trong cung league (constraint hien tai giu nguyen)
- Match user by SDT - uu tien SDT hon email
- Admin duyet 1 lan cho ca nhom (khong duyet tung VDV)
- Registration dong sau deadline
- VDV da phan bo vao team → xoa khoi pool available

### Approach: 2 bang (registration + registration_players)
**Ly do chon:**
- 1 anh CK cho 1 nhom (khong duplicate)
- Giu thong tin nhom dang ky (biet ai DK cung ai)
- Admin duyet 1 lan cho ca nhom
- Tach biet concern: dang ky vs thanh vien team
- Khong break code hien tai

### Risk Assessment
- **SDT trung**: Can validate unique SDT per league (1 SDT khong DK 2 lan)
- **User matching**: Neu SDT da co user nhung ten khac → dung user_id co san, giu ten DK de reference
- **Deadline timezone**: Dung timezone server (Asia/Saigon)
- **File upload**: Reuse upload logic hien tai, luu vao storage/league-registrations/

### Success Criteria
- VDV dang ky duoc qua link public
- Admin duyet/reject dang ky voi anh CK
- Modal them VDV hien pool VDV da duyet theo nhom
- "Them ca nhom" hoat dong dung
- Flow hien tai (tim user) khong bi anh huong
