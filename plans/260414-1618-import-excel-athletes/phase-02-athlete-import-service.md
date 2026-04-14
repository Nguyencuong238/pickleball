# Phase 02: AthleteImportService — Validate + Persist + Partner Linking

## Context Links
- Brainstorm: `../reports/brainstorm-260414-1618-import-excel-athletes.md` (Section 3.3, 3.4, 4)
- Existing controller store: `app/Http/Controllers/Front/Tournament/TournamentAthleteController.php:82-146`

## Overview
- **Priority:** High (core logic)
- **Status:** complete
- **Description:** Tạo `AthleteImportService` xử lý normalize → validate → persist 2-pass + link partners. Extract user lookup/create thành method dùng chung.

## Key Insights
- All-or-nothing: validate toàn file TRƯỚC khi persist. Hard errors → return errors, không touch DB
- Soft-skip duplicates: pre-query existing athletes theo phone+email trong tournament
- Partner linking 2-pass: Pass A tạo athletes, Pass B update `partner_id` bidirectional
- Reuse user resolve logic từ controller line 94-105 → extract thành method
- **REVISED**: Service nhận `array` rows từ `TournamentAthletesImporter::parse()` (raw phpspreadsheet), không phải Collection

## Requirements
- Input: `array $rows` (normalized rows with `_row_number`) + `Tournament` model
- Output: `['created' => int, 'skipped' => array, 'errors' => array]`
- Transaction wrap persist phase
- Không duplicate logic với controller store

## Architecture
```
AthleteImportService
├── execute(array $rows, Tournament $tournament): array
├── normalize(array $row): array                         // trim, lowercase email, normalize phone
├── validateAll(array $rows, array $categoriesByName): array  // return hard errors
├── detectDuplicates(array $rows, array $existing): array     // return skipped indices
├── persistAthletes(array $validRows, array $categoriesByName, Tournament $t): array  // Pass A
├── linkPartners(array $createdMap): void                     // Pass B
└── resolveOrCreateUser(string $email, string $phone, string $name): User
```
Input rows shape (each): `['athlete_name', 'email', 'phone', 'category_name', 'partner_name', '_row_number']`
Use `_row_number` for error row references (đã set ở Phase 01 `parse()`).

## Related Code Files
**Create:**
- `app/Services/Tournament/AthleteImportService.php`

**Modify:**
- `app/Http/Controllers/Front/Tournament/TournamentAthleteController.php` (extract user resolve helper, reuse từ service)

## Implementation Steps

### 1. Create service class
`app/Services/Tournament/AthleteImportService.php` — stateless, không cần constructor. Controller gọi `(new AthleteImportService())->execute($rows, $tournament)`.

```php
public function execute(array $rows, Tournament $tournament): array {
    $categoriesByName = $tournament->categories()
        ->get()
        ->keyBy(fn($c) => mb_strtolower($c->category_name))
        ->all();

    $errors = $this->validateAll($rows, $categoriesByName);
    if (!empty($errors)) {
        return ['created' => 0, 'skipped' => [], 'errors' => $errors];
    }

    [$toPersist, $skipped] = $this->detectDuplicates($rows, $tournament);

    $created = 0;
    DB::transaction(function() use ($toPersist, $categoriesByName, $tournament, &$created) {
        $created = $this->persistAthletes($toPersist, $categoriesByName, $tournament);
    });

    return ['created' => $created, 'skipped' => $skipped, 'errors' => []];
}
```

### 2. Normalize helper
```php
private function normalize(array $row): array {
    return [
        'athlete_name'  => trim($row['athlete_name'] ?? ''),
        'email'         => strtolower(trim($row['email'] ?? '')),
        'phone'         => $this->normalizePhone($row['phone'] ?? ''),
        'category_name' => trim($row['category_name'] ?? ''),
        'partner_name'  => trim($row['partner_name'] ?? ''),
    ];
}

private function normalizePhone(string $phone): string {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    if (str_starts_with($phone, '+84')) return '0' . substr($phone, 3);
    if (str_starts_with($phone, '84'))  return '0' . substr($phone, 2);
    return $phone;
}
```

### 3. Validate all rows (hard errors)
```php
public function validateAll(array $rows, array $categoriesByName): array {
    $errors = [];
    $seenPhones = [];
    $seenEmails = [];

    foreach ($rows as $raw) {
        $rowNum = $raw['_row_number']; // set bởi parser ở Phase 01
        $row = $this->normalize($raw);

        if (empty($row['athlete_name']) || mb_strlen($row['athlete_name']) > 100) {
            $errors[] = ['row' => $rowNum, 'field' => 'athlete_name', 'message' => 'Tên không hợp lệ'];
        }
        if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = ['row' => $rowNum, 'field' => 'email', 'message' => 'Email không hợp lệ'];
        }
        if (empty($row['phone']) || mb_strlen($row['phone']) > 20) {
            $errors[] = ['row' => $rowNum, 'field' => 'phone', 'message' => 'Số điện thoại không hợp lệ'];
        }

        // Category exists
        $catKey = mb_strtolower($row['category_name']);
        if (!isset($categoriesByName[$catKey])) {
            $errors[] = ['row' => $rowNum, 'field' => 'category_name', 'message' => "Hạng mục '{$row['category_name']}' không tồn tại"];
            continue;
        }

        $category = $categoriesByName[$catKey];
        if ($category->isDoubles() && empty($row['partner_name'])) {
            $errors[] = ['row' => $rowNum, 'field' => 'partner_name', 'message' => 'Hạng mục đôi phải có partner'];
        }

        // In-file duplicate
        if (in_array($row['phone'], $seenPhones, true)) {
            $errors[] = ['row' => $rowNum, 'field' => 'phone', 'message' => 'Trùng số điện thoại với row khác'];
        }
        if (in_array($row['email'], $seenEmails, true)) {
            $errors[] = ['row' => $rowNum, 'field' => 'email', 'message' => 'Trùng email với row khác'];
        }
        // Name uniqueness per category (cần cho partner linking by name)
        $nameKey = $category->id . '|' . mb_strtolower($row['athlete_name']);
        if (in_array($nameKey, $seenNames ?? [], true)) {
            $errors[] = ['row' => $rowNum, 'field' => 'athlete_name', 'message' => 'Trùng tên VĐV trong cùng hạng mục'];
        }
        $seenNames[] = $nameKey;
        $seenPhones[] = $row['phone'];
        $seenEmails[] = $row['email'];
    }

    // Partner symmetry check (after basic validation)
    $errors = array_merge($errors, $this->validatePartnerSymmetry($rows, $categoriesByName));

    return $errors;
}
```

### 4. Partner symmetry validation
- Build map `[category_name => [athlete_name => partner_name]]`
- Mỗi row doubles: `A.partner = B`, check `B.partner = A`, không được là chính mình
- Nếu asymmetric → hard error

### 5. Detect duplicates (soft skip)
Pre-query existing athletes trong tournament, build 2 flat lookup sets:
```php
private function detectDuplicates(array $rows, Tournament $tournament): array {
    $existing = TournamentAthlete::where('tournament_id', $tournament->id)
        ->get(['phone', 'email']);
    $existingPhones = $existing->pluck('phone')->filter()->all();
    $existingEmails = $existing->pluck('email')
        ->filter()
        ->map(fn($e) => strtolower((string) $e))
        ->all();

    $toPersist = [];
    $skipped = [];
    foreach ($rows as $raw) {
        $row = $this->normalize($raw);
        if (in_array($row['phone'], $existingPhones, true) ||
            in_array($row['email'], $existingEmails, true)) {
            $skipped[] = [
                'row' => $raw['_row_number'],
                'athlete_name' => $row['athlete_name'],
                'reason' => 'Đã tồn tại trong giải',
            ];
            continue;
        }
        $toPersist[] = $row;
    }
    return [$toPersist, $skipped];
}
```
Return `[toPersist, skipped]` tuple dùng trong `execute()`.

### 6. Persist Pass A + Pass B (called inside `execute()` transaction)
```php
private function persistAthletes(array $validRows, array $categoriesByName, Tournament $tournament): int {
    $created = [];
    foreach ($validRows as $row) {
        $user = $this->resolveOrCreateUser($row['email'], $row['phone'], $row['athlete_name']);
        $category = $categoriesByName[mb_strtolower($row['category_name'])];

        $athlete = TournamentAthlete::create([
            'tournament_id'  => $tournament->id,
            'category_id'    => $category->id,
            'athlete_name'   => $row['athlete_name'],
            'email'          => $row['email'],
            'phone'          => $row['phone'],
            'user_id'        => $user->id,
            'status'         => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $created[$category->id][mb_strtolower($row['athlete_name'])] = [
            'id' => $athlete->id,
            'partner_name' => $row['partner_name'],
        ];
    }

    // Pass B: link partners (bidirectional achieved via iterating all rows)
    $total = 0;
    foreach ($created as $catId => $athletes) {
        $total += count($athletes);
        foreach ($athletes as $name => $data) {
            if (empty($data['partner_name'])) continue;
            $partnerKey = mb_strtolower($data['partner_name']);
            if (!isset($athletes[$partnerKey])) continue;
            $partnerId = $athletes[$partnerKey]['id'];
            TournamentAthlete::where('id', $data['id'])->update(['partner_id' => $partnerId]);
        }
    }
    return $total;
}
```

### 7. resolveOrCreateUser helper
Extract từ controller line 94-105:
```php
private function resolveOrCreateUser(string $email, string $phone, string $name): User {
    $user = User::where('phone', $phone)->first() ?? User::where('email', $email)->first();
    if (!$user) {
        $user = User::create([
            'name' => $name, 'email' => $email, 'phone' => $phone,
            'password' => bcrypt(Str::random(16)),
        ]);
    }
    return $user;
}
```

### 8. Refactor controller store để dùng chung helper (optional, DRY)
Update `TournamentAthleteController@store` gọi `AthleteImportService::resolveOrCreateUser()` thay vì inline logic.

## Todo List
- [x] Create `app/Services/Tournament/AthleteImportService.php`
- [x] Implement `execute()` orchestrator (build categoriesByName map + transaction)
- [x] Implement `normalize()` + `normalizePhone()`
- [x] Implement `validateAll()` full rules (name + email + phone + category + doubles + in-file dup phone/email/name-per-category)
- [x] Implement `validatePartnerSymmetry()`
- [x] Implement `detectDuplicates()` với pre-query (return [toPersist, skipped] tuple)
- [x] Implement `persistAthletes()` với Pass A + Pass B bidirectional (return int count)
- [x] Implement `resolveOrCreateUser()` helper
- [x] Refactor controller `store()` dùng chung helper
- [x] Run `php -l app/Services/Tournament/AthleteImportService.php`

## Success Criteria
- Service compile sạch
- Unit test manual (tinker): cho 5 row test → nhận đúng `created`/`skipped`/`errors`
- Validate đủ rules trong brainstorm section 4
- Không duplicate logic user lookup với controller

## Risk Assessment
- **Risk:** Row order không deterministic → partner lookup fail. **Mitigation:** Pass A tạo tất cả TRƯỚC, Pass B link sau
- **Risk:** User table có unique constraint → race condition. **Mitigation:** try/catch QueryException trong resolveOrCreateUser, retry 1 lần
- **Risk:** Large file memory. **Mitigation:** Phase 01 limit 500 rows, Collection đủ dùng

## Security Considerations
- `tournament_id` luôn lấy từ route model binding, không trust input
- User auto-create vẫn dùng bcrypt random password (consistent)

## Next Steps
- Depends on: Phase 01
- Blocks: Phase 03 (controller call service)
