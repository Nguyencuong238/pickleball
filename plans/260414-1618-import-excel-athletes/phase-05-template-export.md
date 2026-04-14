# Phase 05: Dynamic Template Builder (raw phpspreadsheet)

## Context Links
- Brainstorm: `../reports/brainstorm-260414-1618-import-excel-athletes.md` (Section 3.5)
- Skeleton created in Phase 01: `app/Exports/TournamentAthleteTemplateBuilder.php`

## Overview
- **Priority:** Medium
- **Status:** complete
- **Description:** Fill `TournamentAthleteTemplateBuilder::build()` để tạo file .xlsx với header, example rows, DataValidation dropdown cho cột `category_name`. Dùng raw `Spreadsheet` + `Writer\Xlsx`.

## Key Insights
- phpspreadsheet 5.x `Cell\DataValidation::TYPE_LIST` + `setFormula1()` chính thức support
- Excel data validation formula1 giới hạn ~255 chars → fallback hidden sheet khi > 255
- Dropdown apply cho range A2:D501 (giới hạn 500 row)
- Cột `category_name` nằm ở index D (cột thứ 4: athlete_name, email, phone, category_name, partner_name)

## Requirements
- File name: `template-athletes-{slug}.xlsx`
- Sheet 1 "Athletes": header + 2 example rows (1 single, 1 doubles cặp)
- Dropdown `category_name` list từ tournament categories
- Header bold, auto-width
- Trả về temp file path → controller `deleteFileAfterSend(true)`

## Architecture
```
TournamentAthleteTemplateBuilder::build()
├── new Spreadsheet()
├── getActiveSheet() → 'Athletes'
├── set header row A1:E1 (bold)
├── set example rows A2:E2, A3:E3, A4:E4
├── build category name list
├── if len > 255 → createSheet('_cats') hidden, reference by range
├── else → comma-separated formula1
├── apply DataValidation cho D2:D501
├── auto-size columns A-E
└── save tempnam → return path
```

## Related Code Files
**Modify:**
- `app/Exports/TournamentAthleteTemplateBuilder.php` (từ skeleton Phase 01)

## Implementation Steps

### 1. Full implementation
```php
<?php

namespace App\Exports;

use App\Models\Tournament;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TournamentAthleteTemplateBuilder
{
    public function __construct(private Tournament $tournament) {}

    public function build(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Athletes');

        $this->writeHeader($sheet);
        $this->writeExamples($sheet);
        $this->applyCategoryDropdown($spreadsheet, $sheet);
        $this->styleSheet($sheet);

        $path = tempnam(sys_get_temp_dir(), 'athlete-template-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        return $path;
    }

    private function writeHeader(Worksheet $sheet): void
    {
        $headers = ['athlete_name', 'email', 'phone', 'category_name', 'partner_name'];
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
    }

    private function writeExamples(Worksheet $sheet): void
    {
        $singleCat = $this->tournament->categories()
            ->where('category_type', 'like', 'single_%')->first();
        $doublesCat = $this->tournament->categories()
            ->where('category_type', 'like', 'double_%')->first();

        $rows = [];
        if ($singleCat) {
            $rows[] = ['Nguyen Van A', 'a@example.com', '0901234567', $singleCat->category_name, ''];
        }
        if ($doublesCat) {
            $rows[] = ['Tran Van B', 'b@example.com', '0907654321', $doublesCat->category_name, 'Le Van C'];
            $rows[] = ['Le Van C',   'c@example.com', '0909876543', $doublesCat->category_name, 'Tran Van B'];
        }
        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A2');
        }
    }

    private function applyCategoryDropdown(Spreadsheet $spreadsheet, Worksheet $sheet): void
    {
        $names = $this->tournament->categories()->pluck('category_name')->toArray();
        if (empty($names)) return;

        // Choose strategy based on formula1 length
        $csv = implode(',', array_map(fn($n) => str_replace(['"', ','], '', $n), $names));
        $useHiddenSheet = strlen($csv) > 250; // buffer under 255

        if ($useHiddenSheet) {
            $hidden = $spreadsheet->createSheet();
            $hidden->setTitle('_cats');
            foreach ($names as $i => $name) {
                $hidden->setCellValue('A' . ($i + 1), $name);
            }
            $hidden->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
            $formula1 = "'_cats'!\$A\$1:\$A\$" . count($names);
        } else {
            $formula1 = '"' . $csv . '"';
        }

        for ($row = 2; $row <= 501; $row++) {
            $v = $sheet->getCell("D{$row}")->getDataValidation();
            $v->setType(DataValidation::TYPE_LIST);
            $v->setErrorStyle(DataValidation::STYLE_STOP);
            $v->setAllowBlank(false);
            $v->setShowDropDown(true);
            $v->setShowErrorMessage(true);
            $v->setErrorTitle('Hạng mục không hợp lệ');
            $v->setError('Chọn từ danh sách.');
            $v->setFormula1($formula1);
        }
    }

    private function styleSheet(Worksheet $sheet): void
    {
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}
```

### 2. Compile check
```bash
php -l app/Exports/TournamentAthleteTemplateBuilder.php
```

### 3. Manual test
```bash
# Login owner, visit route → file downloads
curl -o /tmp/test.xlsx -H "Cookie: laravel_session=..." \
  http://localhost/tournament-manage/{slug}/athletes/import-template
open /tmp/test.xlsx  # verify dropdown + example rows
```

## Todo List
- [x] Implement `writeHeader()`
- [x] Implement `writeExamples()` (single + doubles)
- [x] Implement `applyCategoryDropdown()` với csv/hidden sheet fallback
- [x] Implement `styleSheet()` (auto-width)
- [x] `php -l` syntax check
- [x] Manual test download + mở file trong Excel/Numbers
- [x] Verify dropdown category khớp tournament

## Success Criteria
- Download file → mở được trong Excel
- Click cell D2 → dropdown hiện list categories
- Chọn ngoài list → Excel cảnh báo
- Example rows hợp lệ (2-3 row mẫu)
- Header row bold
- Tournament không category → controller trả 400

## Risk Assessment
- **Risk:** Ký tự "," hoặc `"` trong category_name làm vỡ CSV formula1. **Mitigation:** Strip các ký tự này hoặc luôn fallback hidden sheet
- **Risk:** tempnam collision. **Mitigation:** tempnam tự unique, controller `deleteFileAfterSend(true)`
- **Risk:** Memory với nhiều row validation. **Mitigation:** 500 row OK, phpspreadsheet handle được

## Security Considerations
- `authorizeOwner()` check ở controller
- Không expose category tournament khác

## Next Steps
- Depends on: Phase 01 (skeleton), Phase 03 (route + download)
- Blocks: Phase 06 (test)
