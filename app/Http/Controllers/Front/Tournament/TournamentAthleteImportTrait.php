<?php

namespace App\Http\Controllers\Front\Tournament;

use App\Exports\TournamentAthleteTemplateBuilder;
use App\Imports\TournamentAthletesImporter;
use App\Models\Tournament;
use App\Services\Tournament\AthleteImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

trait TournamentAthleteImportTrait
{
    private const MAX_IMPORT_ROWS = 500;

    /**
     * Bulk import athletes from an uploaded xlsx/xls file.
     */
    public function importExcel(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorizeOwner($tournament);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        try {
            $rows = (new TournamentAthletesImporter())
                ->parse($request->file('file')->getPathname());

            if (empty($rows)) {
                return response()->json(['message' => 'File không có dữ liệu.'], 422);
            }
            if (count($rows) > self::MAX_IMPORT_ROWS) {
                return response()->json([
                    'message' => 'File quá lớn (giới hạn ' . self::MAX_IMPORT_ROWS . ' vận động viên).',
                ], 422);
            }

            $result = (new AthleteImportService())->execute($rows, $tournament);

            if (!empty($result['errors'])) {
                return response()->json([
                    'message' => 'File có lỗi, không import được.',
                    'errors'  => $result['errors'],
                ], 422);
            }

            $skippedCount = count($result['skipped']);
            $message = "Đã import {$result['created']} vận động viên."
                . ($skippedCount > 0 ? " Bỏ qua {$skippedCount} vận động viên đã tồn tại." : '');

            return response()->json([
                'message' => $message,
                'created' => $result['created'],
                'skipped' => $result['skipped'],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (PhpSpreadsheetException $e) {
            return response()->json(['message' => 'File Excel không hợp lệ.'], 422);
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => 'Lỗi máy chủ, vui lòng thử lại sau.'], 500);
        }
    }

    /**
     * Download dynamic xlsx template with this tournament's categories as dropdown.
     */
    public function downloadTemplate(Tournament $tournament): JsonResponse|BinaryFileResponse
    {
        $this->authorizeOwner($tournament);

        if ($tournament->categories()->count() === 0) {
            return response()->json(['message' => 'Giải đấu chưa có hạng mục nào.'], 400);
        }

        $path = (new TournamentAthleteTemplateBuilder($tournament))->build();
        $filename = 'template-athletes-' . $tournament->slug . '.xlsx';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
