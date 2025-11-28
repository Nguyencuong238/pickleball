# KIỂM TRA TƯƠNG ỨNG: DATABASE COLUMNS vs FORM INPUT NAMES

## 1. TABLE: instructors
| Database Column | Form Input Name | Status |
|---|---|---|
| name | name | ✅ KHỚP |
| bio | bio | ✅ KHỚP |
| description | description | ✅ KHỚP |
| image | image | ✅ KHỚP |
| experience_years | experience_years | ✅ KHỚP |
| student_count | student_count | ✅ KHỚP |
| total_hours | total_hours | ✅ KHỚP |
| price_per_session | price_per_session | ✅ KHỚP |
| ward | ward | ✅ KHỚP |
| phone | phone | ✅ KHỚP |
| email | email | ✅ KHỚP |
| zalo | zalo | ✅ KHỚP |
| province_id | province_id | ✅ KHỚP |

## 2. TABLE: instructor_experiences
Form input structure: `experiences[0][field]`
| Database Column | Form Input Name | Status |
|---|---|---|
| title | experiences[0][title] | ✅ KHỚP |
| organization | experiences[0][organization] | ✅ KHỚP |
| start_year | experiences[0][start_year] | ✅ KHỚP |
| end_year | experiences[0][end_year] | ✅ KHỚP |
| description | experiences[0][description] | ✅ KHỚP |

## 3. TABLE: instructor_certifications
Form input structure: `certifications[0][field]`
| Database Column | Form Input Name | Status |
|---|---|---|
| title | certifications[0][title] | ✅ KHỚP |
| issuer | certifications[0][issuer] | ✅ KHỚP |
| year | certifications[0][year] | ✅ KHỚP |
| **type** | **certifications[0][is_award]** | ❌ KHÔNG KHỚP |

⚠️ **ISSUE 1**: Database có column `type` (enum: certificate/award) nhưng form gửi `is_award` (checkbox boolean).
Controller xử lý: Nếu `is_award` được check → type = 'award', ngược lại = 'certificate'. **OK**

## 4. TABLE: instructor_teaching_methods
Form input structure: `teaching_methods[0][field]`
| Database Column | Form Input Name | Status |
|---|---|---|
| title | teaching_methods[0][title] | ✅ KHỚP |
| description | teaching_methods[0][description] | ✅ KHỚP |
| icon | teaching_methods[0][icon] | ❌ KHÔNG CÓ TRONG FORM |

⚠️ **ISSUE 2**: Database có column `icon` nhưng form không có input cho nó. Controller không xử lý.
**Giải pháp**: Thêm input cho `icon` hoặc bỏ qua (để NULL)

## 5. TABLE: instructor_packages
Form input structure: `packages[0][field]`
| Database Column | Form Input Name | Status |
|---|---|---|
| name | packages[0][name] | ✅ KHỚP |
| description | packages[0][description] | ✅ KHỚP |
| price | packages[0][price] | ✅ KHỚP |
| discount_percent | packages[0][discount_percent] | ✅ KHỚP |
| sessions_count | packages[0][sessions_count] | ✅ KHỚP |
| **is_group** | ❌ KHÔNG CÓ | ❌ THIẾU |
| **max_group_size** | ❌ KHÔNG CÓ | ❌ THIẾU |
| **is_popular** | ❌ KHÔNG CÓ | ❌ THIẾU |
| is_active | packages[0][is_active] | ✅ KHỚP |

⚠️ **ISSUE 3**: Database có 3 columns nhưng form không có: `is_group`, `max_group_size`, `is_popular`

## 6. TABLE: instructor_locations
Form input structure: `locations[0][field]`
| Database Column | Form Input Name | Status |
|---|---|---|
| district | locations[0][district] | ✅ KHỚP |
| city | locations[0][city] | ✅ KHỚP |
| venues | locations[0][venues] | ✅ KHỚP |

## 7. TABLE: instructor_schedules
Form input structure: `schedules[0][field]`
| Database Column | Form Input Name | Status |
|---|---|---|
| days | schedules[0][days] | ✅ KHỚP |
| time_slots | schedules[0][time_slots] | ✅ KHỚP |

## SUMMARY

✅ **OK**: 
- instructors (tất cả)
- instructor_experiences (tất cả)
- instructor_locations (tất cả)
- instructor_schedules (tất cả)

⚠️ **CẦN FIX**:
1. `instructor_certifications`: form gửi `is_award` → controller convert thành `type` = 'award'/'certificate'
2. `instructor_teaching_methods`: DATABASE CÓ `icon` nhưng form không có → để NULL
3. `instructor_packages`: DATABASE CÓ `is_group`, `max_group_size`, `is_popular` nhưng form không gửi
