<?php

namespace App\Imports;

use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TournamentAthletesImporter
{
    private const EXPECTED_COLUMNS = [
        'athlete_name',
        'email',
        'phone',
        'category_name',
        'partner_name',
    ];

    /**
     * Parse uploaded xlsx/xls file → normalized rows with header mapping.
     *
     * @return array<int, array{athlete_name:string,email:string,phone:string,category_name:string,partner_name:string,_row_number:int}>
     */
    public function parse(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $raw = $sheet->toArray(null, true, true, false);

        if (empty($raw)) {
            return [];
        }

        $header = array_map(
            fn ($h) => strtolower(trim((string) $h)),
            $raw[0]
        );

        $indexMap = [];
        foreach (self::EXPECTED_COLUMNS as $col) {
            $idx = array_search($col, $header, true);
            if ($idx === false) {
                throw new InvalidArgumentException("Thiếu cột bắt buộc: {$col}");
            }
            $indexMap[$col] = $idx;
        }

        $rows = [];
        $rowCount = count($raw);
        for ($i = 1; $i < $rowCount; $i++) {
            $row = $raw[$i];
            // Skip fully empty rows
            $hasAnyValue = false;
            foreach ($row as $cell) {
                if ($cell !== null && $cell !== '') {
                    $hasAnyValue = true;
                    break;
                }
            }
            if (!$hasAnyValue) {
                continue;
            }

            $rows[] = [
                'athlete_name'  => (string) ($row[$indexMap['athlete_name']] ?? ''),
                'email'         => (string) ($row[$indexMap['email']] ?? ''),
                'phone'         => (string) ($row[$indexMap['phone']] ?? ''),
                'category_name' => (string) ($row[$indexMap['category_name']] ?? ''),
                'partner_name'  => (string) ($row[$indexMap['partner_name']] ?? ''),
                '_row_number'   => $i + 1,
            ];
        }

        return $rows;
    }
}
