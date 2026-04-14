# Phase 03: Controller Methods + Routes

## Context Links
- Brainstorm: `../reports/brainstorm-260414-1618-import-excel-athletes.md` (Section 3.2)
- Routes: `routes/web.php:620-635`
- Controller: `app/Http/Controllers/Front/Tournament/TournamentAthleteController.php`

## Overview
- **Priority:** High
- **Status:** complete
- **Description:** Thêm 2 routes + 2 controller methods: `importExcel()` và `downloadTemplate()`. Controller mỏng, delegate logic xuống service/export class.

## Key Insights
- Existing route group đã có `auth` middleware + slug binding → chỉ cần thêm 2 dòng
- `authorizeOwner()` reuse cho cả 2 methods
- File upload validation: `file|mimes:xlsx,xls|max:2048`

## Requirements
- Route `POST .../athletes/import` return JSON `{ created, skipped, errors }`
- Route `GET .../athletes/import-template` return xlsx download
- Status codes: 200 success, 422 validation errors, 403 unauthorized, 413 file too large

## Architecture
Routes registered trong existing group at `routes/web.php:620+`. Controller methods trả JSON (import) hoặc streamed response (template).

## Related Code Files
**Modify:**
- `routes/web.php` (add 2 routes)
- `app/Http/Controllers/Front/Tournament/TournamentAthleteController.php` (add 2 methods)

## Implementation Steps

### 1. Add routes
Trong group `tournament-manage` tại `routes/web.php`, sau line route bulkApprove:
```php
Route::get('{tournament}/athletes/import-template', [TournamentAthleteController::class, 'downloadTemplate'])
    ->name('tournament-manage.athletes.import-template');
Route::post('{tournament}/athletes/import', [TournamentAthleteController::class, 'importExcel'])
    ->name('tournament-manage.athletes.import');
```

### 2. Add `importExcel()` method (raw phpspreadsheet)
```php
public function importExcel(Request $request, Tournament $tournament): JsonResponse
{
    $this->authorizeOwner($tournament);

    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls|max:2048',
    ]);

    try {
        $rows = (new \App\Imports\TournamentAthletesImporter())
            ->parse($request->file('file')->getPathname());

        if (empty($rows)) {
            return response()->json(['message' => 'File không có dữ liệu.'], 422);
        }
        if (count($rows) > 500) {
            return response()->json(['message' => 'File quá lớn (>500 VĐV).'], 422);
        }

        $result = (new \App\Services\Tournament\AthleteImportService())
            ->execute($rows, $tournament);

        if (!empty($result['errors'])) {
            return response()->json([
                'message' => 'File có lỗi, không import.',
                'errors' => $result['errors'],
            ], 422);
        }

        return response()->json([
            'message' => "Đã import {$result['created']} VĐV. Bỏ qua " . count($result['skipped']) . ' VĐV đã tồn tại.',
            'created' => $result['created'],
            'skipped' => $result['skipped'],
        ], 200);
    } catch (\InvalidArgumentException $e) {
        // Missing header column
        return response()->json(['message' => $e->getMessage()], 422);
    } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
        return response()->json(['message' => 'File Excel không hợp lệ: ' . $e->getMessage()], 422);
    } catch (\Throwable $e) {
        report($e);
        return response()->json(['message' => 'Lỗi server, thử lại sau.'], 500);
    }
}
```

### 3. Add `downloadTemplate()` method (raw phpspreadsheet)
```php
public function downloadTemplate(Tournament $tournament)
{
    $this->authorizeOwner($tournament);

    if ($tournament->categories()->count() === 0) {
        return response()->json(['message' => 'Giải chưa có hạng mục nào.'], 400);
    }

    $builder = new \App\Exports\TournamentAthleteTemplateBuilder($tournament);
    $path = $builder->build();
    $filename = 'template-athletes-' . $tournament->slug . '.xlsx';

    return response()->download($path, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ])->deleteFileAfterSend(true);
}
```

### 4. Compile check
```bash
php -l app/Http/Controllers/Front/Tournament/TournamentAthleteController.php
php artisan route:list | grep athletes
```

## Todo List
- [x] Add 2 routes trong `routes/web.php`
- [x] Implement `importExcel()` method
- [x] Implement `downloadTemplate()` method
- [x] Add row count + empty file check
- [x] Try/catch PhpSpreadsheet exceptions
- [x] Run `php -l` + `php artisan route:list` verify
- [x] Test manual với curl/Postman upload file mẫu nhỏ

## Success Criteria
- `php artisan route:list | grep import` hiện 2 routes mới
- Upload file valid → trả JSON 200 với `created` count
- Upload file invalid → trả JSON 422 với errors array
- Unauthorized user → 403
- File > 2MB → 422 "file too large"

## Risk Assessment
- **Risk:** Service instance có thể cần DI thay vì `new`. **Mitigation:** Dùng `app(AthleteImportService::class)` nếu có constructor dependencies
- **Risk:** Large file OOM. **Mitigation:** Row limit 500 + file size 2MB đã cover

## Security Considerations
- `authorizeOwner()` check ở đầu mỗi method
- CSRF token auto-verified via Laravel middleware
- File mime validation strict
- Không expose server error message ra response (catch Throwable → generic message)

## Next Steps
- Depends on: Phase 02 (service), Phase 01 (Import class)
- Blocks: Phase 04 (UI call routes), Phase 05 (template export class)
