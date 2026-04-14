<?php

namespace App\Services\Tournament;

use App\Models\TournamentCategory;

class AthleteImportValidator
{
    /**
     * Run all hard-error rules against normalized rows.
     *
     * @param  array<int, array<string, mixed>>  $rows Normalized rows (from AthleteRowNormalizer)
     * @param  array<string, TournamentCategory>  $categoriesByName Keyed by lowercase name
     * @return array<int, array{row:int, field:string, message:string}>
     */
    public function validate(array $rows, array $categoriesByName): array
    {
        $errors = [];
        // Associative hash lookup → O(1) per row instead of in_array O(n)
        $seenPhones = [];
        $seenEmails = [];
        $seenNames = [];

        foreach ($rows as $row) {
            $rowNum = $row['_row_number'];

            if (empty($row['athlete_name']) || mb_strlen($row['athlete_name']) > 100) {
                $errors[] = ['row' => $rowNum, 'field' => 'athlete_name', 'message' => 'Tên vận động viên không hợp lệ (rỗng hoặc quá 100 ký tự).'];
            }
            if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = ['row' => $rowNum, 'field' => 'email', 'message' => 'Email không hợp lệ.'];
            }
            if (empty($row['phone']) || mb_strlen($row['phone']) > 20) {
                $errors[] = ['row' => $rowNum, 'field' => 'phone', 'message' => 'Số điện thoại không hợp lệ.'];
            }

            $catKey = mb_strtolower($row['category_name']);
            if ($row['category_name'] === '' || !isset($categoriesByName[$catKey])) {
                $errors[] = ['row' => $rowNum, 'field' => 'category_name', 'message' => "Hạng mục '{$row['category_name']}' không tồn tại trong giải."];
                continue;
            }

            $category = $categoriesByName[$catKey];
            if ($category->isDoubles() && $row['partner_name'] === '') {
                $errors[] = ['row' => $rowNum, 'field' => 'partner_name', 'message' => 'Hạng mục đôi phải có partner.'];
            }

            if ($row['phone'] !== '' && isset($seenPhones[$row['phone']])) {
                $errors[] = ['row' => $rowNum, 'field' => 'phone', 'message' => 'Trùng số điện thoại với dòng khác trong file.'];
            }
            if ($row['email'] !== '' && isset($seenEmails[$row['email']])) {
                $errors[] = ['row' => $rowNum, 'field' => 'email', 'message' => 'Trùng email với dòng khác trong file.'];
            }

            $nameKey = $category->id . '|' . mb_strtolower($row['athlete_name']);
            if (isset($seenNames[$nameKey])) {
                $errors[] = ['row' => $rowNum, 'field' => 'athlete_name', 'message' => 'Trùng tên vận động viên trong cùng hạng mục.'];
            }
            $seenNames[$nameKey] = true;
            if ($row['phone'] !== '') {
                $seenPhones[$row['phone']] = true;
            }
            if ($row['email'] !== '') {
                $seenEmails[$row['email']] = true;
            }
        }

        return array_merge($errors, $this->validatePartnerSymmetry($rows, $categoriesByName));
    }

    /**
     * Ensure doubles pairs reference each other bidirectionally within same category.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, TournamentCategory>  $categoriesByName
     * @return array<int, array{row:int, field:string, message:string}>
     */
    private function validatePartnerSymmetry(array $rows, array $categoriesByName): array
    {
        $errors = [];
        $byCategory = [];
        foreach ($rows as $row) {
            $catKey = mb_strtolower($row['category_name']);
            if (!isset($categoriesByName[$catKey])) {
                continue;
            }
            $cat = $categoriesByName[$catKey];
            if (!$cat->isDoubles()) {
                continue;
            }
            $nameKey = mb_strtolower($row['athlete_name']);
            $byCategory[$cat->id][$nameKey] = $row;
        }

        foreach ($byCategory as $athletes) {
            foreach ($athletes as $nameKey => $row) {
                if ($row['partner_name'] === '') {
                    continue;
                }
                $partnerKey = mb_strtolower($row['partner_name']);

                if ($partnerKey === $nameKey) {
                    $errors[] = ['row' => $row['_row_number'], 'field' => 'partner_name', 'message' => 'Partner không thể là chính mình.'];
                    continue;
                }

                if (!isset($athletes[$partnerKey])) {
                    $errors[] = ['row' => $row['_row_number'], 'field' => 'partner_name', 'message' => "Không tìm thấy partner '{$row['partner_name']}' trong cùng hạng mục."];
                    continue;
                }

                $partnerRow = $athletes[$partnerKey];
                $reverseKey = mb_strtolower($partnerRow['partner_name']);
                if ($reverseKey !== $nameKey) {
                    $errors[] = ['row' => $row['_row_number'], 'field' => 'partner_name', 'message' => 'Partner không khớp 2 chiều (A → B nhưng B không trỏ về A).'];
                }
            }
        }

        return $errors;
    }
}
