<?php

namespace App\Exports;

use App\Models\Tournament;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TournamentAthleteTemplateBuilder
{
    private const MAX_ROWS = 501;
    private const CATEGORY_COLUMN = 'D';
    private const INLINE_FORMULA_LIMIT = 250;

    public function __construct(private Tournament $tournament)
    {
    }

    /**
     * Build template spreadsheet → return temp file path.
     */
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
            ->where('category_type', 'like', 'single_%')
            ->first();
        $doublesCat = $this->tournament->categories()
            ->where('category_type', 'like', 'double_%')
            ->first();

        $rows = [];
        if ($singleCat) {
            $rows[] = ['Nguyễn Văn A', 'a@example.com', '0901234567', $singleCat->category_name, ''];
        }
        if ($doublesCat) {
            $rows[] = ['Trần Văn B', 'b@example.com', '0907654321', $doublesCat->category_name, 'Lê Văn C'];
            $rows[] = ['Lê Văn C',   'c@example.com', '0909876543', $doublesCat->category_name, 'Trần Văn B'];
        }
        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A2');
        }
    }

    private function applyCategoryDropdown(Spreadsheet $spreadsheet, Worksheet $sheet): void
    {
        $names = $this->tournament->categories()->pluck('category_name')->toArray();
        if (empty($names)) {
            return;
        }

        // Strip characters that break inline CSV formula
        $cleanNames = array_map(
            fn ($n) => str_replace(['"', ','], '', (string) $n),
            $names
        );
        $csv = implode(',', $cleanNames);
        $useHiddenSheet = strlen($csv) > self::INLINE_FORMULA_LIMIT;

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

        for ($row = 2; $row <= self::MAX_ROWS; $row++) {
            $cell = self::CATEGORY_COLUMN . $row;
            $v = $sheet->getCell($cell)->getDataValidation();
            $v->setType(DataValidation::TYPE_LIST);
            $v->setErrorStyle(DataValidation::STYLE_STOP);
            $v->setAllowBlank(false);
            // OOXML semantics: showDropDown=false means the dropdown IS shown
            $v->setShowDropDown(false);
            $v->setShowErrorMessage(true);
            $v->setErrorTitle('Hạng mục không hợp lệ');
            $v->setError('Vui lòng chọn hạng mục từ danh sách.');
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
