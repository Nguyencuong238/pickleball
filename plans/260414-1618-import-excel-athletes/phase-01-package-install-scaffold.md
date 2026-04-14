# Phase 01: Scaffold Import/Export Classes (raw phpspreadsheet)

## Context Links
- Brainstorm: `../reports/brainstorm-260414-1618-import-excel-athletes.md`
- Reference: `app/Http/Controllers/Admin/UserImportController.php` (existing raw phpspreadsheet usage)

## Overview
- **Priority:** High (blocker)
- **Status:** complete
- **Description:** KHÔNG install package mới. Dùng `phpoffice/phpspreadsheet ^5.3` đã có. Tạo skeleton classes cho parser + template export.

## Key Insights (REVISED)
- `maatwebsite/excel` **không tương thích** phpspreadsheet 5.x → bỏ hướng này
- `UserImportController` đã dùng raw `PhpOffice\PhpSpreadsheet\IOFactory::load()` → mirror pattern
- 2 classes mới: parser service + template builder (không cần interface chuẩn, là plain PHP classes)

## Requirements
- Không modify composer.json
- Skeleton classes compile sạch
- Directory `app/Imports/` và `app/Exports/` tạo mới

## Architecture
```
app/
├── Imports/
│   └── TournamentAthletesImporter.php    ← skeleton: parse file → array of normalized rows
└── Exports/
    └── TournamentAthleteTemplateBuilder.php ← skeleton: build Spreadsheet object
```

## Related Code Files
**Create:**
- `app/Imports/TournamentAthletesImporter.php`
- `app/Exports/TournamentAthleteTemplateBuilder.php`

**Modify:** none

## Implementation Steps

### 1. Create parser skeleton
`app/Imports/TournamentAthletesImporter.php`:
```php
<?php

namespace App\Imports;

use PhpOffice\PhpSpreadsheet\IOFactory;

class TournamentAthletesImporter
{
    /**
     * Parse uploaded file → array rows with header mapping
     * @return array<int, array{athlete_name:string,email:string,phone:string,category_name:string,partner_name:string}>
     */
    public function parse(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $raw = $sheet->toArray(null, true, true, false);

        if (empty($raw)) {
            return [];
        }

        $header = array_map(fn($h) => strtolower(trim((string) $h)), $raw[0]);
        $expected = ['athlete_name', 'email', 'phone', 'category_name', 'partner_name'];

        $indexMap = [];
        foreach ($expected as $col) {
            $idx = array_search($col, $header, true);
            if ($idx === false) {
                throw new \InvalidArgumentException("Thiếu cột: {$col}");
            }
            $indexMap[$col] = $idx;
        }

        $rows = [];
        for ($i = 1; $i < count($raw); $i++) {
            $row = $raw[$i];
            // Skip fully empty rows
            if (count(array_filter($row, fn($c) => $c !== null && $c !== '')) === 0) {
                continue;
            }
            $rows[] = [
                'athlete_name'  => (string) ($row[$indexMap['athlete_name']] ?? ''),
                'email'         => (string) ($row[$indexMap['email']] ?? ''),
                'phone'         => (string) ($row[$indexMap['phone']] ?? ''),
                'category_name' => (string) ($row[$indexMap['category_name']] ?? ''),
                'partner_name'  => (string) ($row[$indexMap['partner_name']] ?? ''),
                '_row_number'   => $i + 1, // Excel row (1-indexed, header = row 1)
            ];
        }

        return $rows;
    }
}
```

### 2. Create template builder skeleton
`app/Exports/TournamentAthleteTemplateBuilder.php`:
```php
<?php

namespace App\Exports;

use App\Models\Tournament;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TournamentAthleteTemplateBuilder
{
    public function __construct(private Tournament $tournament) {}

    /** Build Spreadsheet object → return file path (temp) */
    public function build(): string
    {
        $spreadsheet = new Spreadsheet();
        // Phase 05 fills actual content
        $path = tempnam(sys_get_temp_dir(), 'athlete-template-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        return $path;
    }
}
```

### 3. Compile check
```bash
php -l app/Imports/TournamentAthletesImporter.php
php -l app/Exports/TournamentAthleteTemplateBuilder.php
composer dump-autoload
```

## Todo List
- [x] Create `app/Imports/TournamentAthletesImporter.php` với `parse()` method
- [x] Create `app/Exports/TournamentAthleteTemplateBuilder.php` skeleton
- [x] Run `php -l` cả 2 file
- [x] Run `composer dump-autoload`

## Success Criteria
- Syntax check pass
- `php artisan tinker` → `new App\Imports\TournamentAthletesImporter()` không error
- Autoload đúng namespace

## Risk Assessment
- **Risk:** Namespace conflict nếu có folder cùng tên. **Mitigation:** Check `ls app/Imports app/Exports` trước — hiện không tồn tại
- **Risk:** `toArray()` với large file memory OOM. **Mitigation:** Row limit enforce ở phase 03

## Security Considerations
- Không (pure scaffold)

## Next Steps
- Depends on: none
- Blocks: Phase 02, 03, 05
