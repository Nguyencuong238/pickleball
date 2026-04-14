<?php

namespace App\Services\Tournament;

class AthleteRowNormalizer
{
    /**
     * Trim, lowercase email, normalize phone → return normalized row with _row_number preserved.
     *
     * @param  array<string, mixed>  $row
     * @return array{athlete_name:string,email:string,phone:string,category_name:string,partner_name:string,_row_number:int}
     */
    public function normalize(array $row): array
    {
        return [
            'athlete_name'  => trim((string) ($row['athlete_name'] ?? '')),
            'email'         => mb_strtolower(trim((string) ($row['email'] ?? ''))),
            'phone'         => $this->normalizePhone((string) ($row['phone'] ?? '')),
            'category_name' => trim((string) ($row['category_name'] ?? '')),
            'partner_name'  => trim((string) ($row['partner_name'] ?? '')),
            '_row_number'   => (int) ($row['_row_number'] ?? 0),
        ];
    }

    /**
     * Strip non-digits, convert +84/84 prefix to 0 prefix (Vietnamese format).
     */
    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone) ?? '';
        // Vietnam mobile = 10 digits total (0XXXXXXXXX).
        // +84XXXXXXXXX (12 chars) or 84XXXXXXXXX (11 chars) → 0XXXXXXXXX.
        if (str_starts_with($phone, '+84') && strlen($phone) === 12) {
            return '0' . substr($phone, 3);
        }
        if (str_starts_with($phone, '84') && strlen($phone) === 11) {
            return '0' . substr($phone, 2);
        }
        return $phone;
    }
}
