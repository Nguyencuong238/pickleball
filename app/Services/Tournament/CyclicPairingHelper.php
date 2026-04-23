<?php

namespace App\Services\Tournament;

use App\Models\Group;

/**
 * Paired cross-group pairing (A↔B, C↔D, ...) cho knockout bracket.
 *
 * Dùng khi số bảng là power of 2 ∈ {2,4,8,16} và mọi bảng đều có đúng 2 VĐV advance.
 * Ghép các bảng theo từng cặp: bảng 0↔1, 2↔3, 4↔5, ... bằng công thức `i XOR 1`.
 * Guarantee 2 VĐV cùng bảng không gặp nhau trước Final (same-group separation).
 *
 * Tham khảo: plans/reports/researcher-260422-1420-vn-tournament-bracket-pairing.md
 */
class CyclicPairingHelper
{
    /**
     * Thu thập VĐV advance theo từng bảng, giữ group identity.
     *
     * @return array<int, array{0: int|null, 1: int|null}>
     *         Index theo group.id ASC; mỗi phần tử [rank1_athlete_id, rank2_athlete_id].
     */
    public function collectGroupedAdvancers(int $tournamentId, int $categoryId): array
    {
        // Note: không dùng limit() trong eager-load vì Laravel áp global cho query
        // ngoài (không per-parent). Filter is_advanced + orderBy rank_position đã
        // đủ cung cấp top-2 theo thứ tự cho mỗi group.
        $groups = Group::where('tournament_id', $tournamentId)
            ->where('category_id', $categoryId)
            ->orderBy('id')
            ->with(['standings' => function ($query) {
                $query->where('is_advanced', true)
                    ->orderBy('rank_position');
            }])
            ->get();

        // Chỉ lấy rank 1 và rank 2; rank 3+ bỏ qua (paired pairing chỉ hỗ trợ top-2 advance).
        return $groups->map(function (Group $group): array {
            $advanced = $group->standings->values();
            return [
                $advanced[0]->athlete_id ?? null,
                $advanced[1]->athlete_id ?? null,
            ];
        })->values()->toArray();
    }

    /**
     * Kiểm tra điều kiện dùng paired cross-group pairing:
     * - Số bảng ∈ {2, 4, 8, 16}
     * - Mọi bảng đều có đủ 2 VĐV advance
     *
     * @param array<int, array{0: int|null, 1: int|null}> $grouped
     */
    public function isEligible(array $grouped): bool
    {
        $n = count($grouped);

        if (!in_array($n, [2, 4, 8, 16], true)) {
            return false;
        }

        foreach ($grouped as $pair) {
            if ($pair[0] === null || $pair[1] === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sắp xếp slot theo paired cross-group pairing (A↔B, C↔D, ...).
     *
     * Công thức:
     *   pair[i] = (group[i].rank1, group[i XOR 1].rank2)  với i = 0..N-1
     *   (tức cặp 0↔1, 2↔3, 4↔5, ... swap rank2 partner)
     * Slot placement:
     *   slots[bitReverse(i) * 2]     = pair[i].0
     *   slots[bitReverse(i) * 2 + 1] = pair[i].1
     *
     * Bit-reverse giúp đặt pairs sao cho cùng-bảng không gặp nhau trước Final.
     *
     * Ví dụ N=4: slot[0,1]=A1-B2, slot[2,3]=C1-D2, slot[4,5]=B1-A2, slot[6,7]=D1-C2
     * Ví dụ N=8: pair theo thứ tự (A1-B2, B1-A2, C1-D2, D1-C2, E1-F2, F1-E2, G1-H2, H1-G2),
     *           sau đó đặt vào slot bằng bitReverse.
     *
     * @param array<int, array{0: int|null, 1: int|null}> $grouped
     * @return array<int, int|null>
     */
    public function arrange(array $grouped): array
    {
        $n = count($grouped);
        $bracketSize = $n * 2;
        // round() tránh floating-point truncate với log(16,2) = 3.9999...
        $bits = (int) round(\log($n, 2));
        $slots = array_fill(0, $bracketSize, null);

        for ($i = 0; $i < $n; $i++) {
            $rank1 = $grouped[$i][0] ?? null;
            $rank2 = $grouped[$i ^ 1][1] ?? null;

            $slotPairIdx = $this->bitReverse($i, $bits);
            $slots[$slotPairIdx * 2]     = $rank1;
            $slots[$slotPairIdx * 2 + 1] = $rank2;
        }

        return $slots;
    }

    /**
     * Đảo ngược các bit của số nguyên trong phạm vi `$bits` bit.
     * Ví dụ bitReverse(1, 3) = 4 (001 -> 100).
     */
    private function bitReverse(int $num, int $bits): int
    {
        $result = 0;
        for ($i = 0; $i < $bits; $i++) {
            $result = ($result << 1) | (($num >> $i) & 1);
        }
        return $result;
    }
}
