<?php

namespace Tests\Unit\Services\Tournament;

use App\Services\Tournament\CyclicPairingHelper;
use Tests\TestCase;

/**
 * Unit tests cho CyclicPairingHelper paired cross-group pairing (A↔B, C↔D, ...).
 *
 * Verify:
 * - Pairing formula `i XOR 1` (rank2 partner swap trong từng cặp adjacent group).
 * - Bit-reverse slot placement đảm bảo same-group separation đến Final.
 * - isEligible reject input không hợp lệ.
 */
class CyclicPairingHelperTest extends TestCase
{
    private CyclicPairingHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = new CyclicPairingHelper();
    }

    public function test_arrange_with_2_groups_returns_paired_slots(): void
    {
        // groups: A=[a1,a2], B=[b1,b2]
        $grouped = [
            [101, 102], // A1, A2
            [201, 202], // B1, B2
        ];

        // Expected pair[0]=(A1,B2), pair[1]=(B1,A2); bits=1, bitReverse identity
        // → slots: A1, B2, B1, A2
        $this->assertSame([101, 202, 201, 102], $this->helper->arrange($grouped));
    }

    public function test_arrange_with_4_groups_returns_paired_slots(): void
    {
        $grouped = [
            [101, 102], // A
            [201, 202], // B
            [301, 302], // C
            [401, 402], // D
        ];

        // pair[0]=(A1,B2), pair[1]=(B1,A2), pair[2]=(C1,D2), pair[3]=(D1,C2)
        // bitReverse(i,2): 0→0, 1→2, 2→1, 3→3
        // slots:
        //   pair[0] @ slot 0,1  → A1,B2
        //   pair[2] @ slot 2,3  → C1,D2
        //   pair[1] @ slot 4,5  → B1,A2
        //   pair[3] @ slot 6,7  → D1,C2
        $expected = [101, 202, 301, 402, 201, 102, 401, 302];
        $this->assertSame($expected, $this->helper->arrange($grouped));
    }

    public function test_arrange_with_8_groups_separates_same_group_until_final(): void
    {
        $grouped = [];
        for ($g = 0; $g < 8; $g++) {
            // group g: rank1 = g*100 + 1, rank2 = g*100 + 2
            $grouped[] = [$g * 100 + 1, $g * 100 + 2];
        }

        $slots = $this->helper->arrange($grouped);
        $this->assertCount(16, $slots);

        // Top half = slots 0-7, bottom half = slots 8-15.
        // Same-group rank1 và rank2 phải nằm khác half (chỉ gặp ở Final).
        $topHalf = array_slice($slots, 0, 8);
        $bottomHalf = array_slice($slots, 8, 8);

        for ($g = 0; $g < 8; $g++) {
            $r1 = $g * 100 + 1;
            $r2 = $g * 100 + 2;
            $r1Top = in_array($r1, $topHalf, true);
            $r2Top = in_array($r2, $topHalf, true);
            $this->assertNotSame(
                $r1Top,
                $r2Top,
                "Group {$g}: rank1 và rank2 phải khác half (separation đến Final)."
            );
        }
    }

    public function test_arrange_with_8_groups_pairs_adjacent_groups(): void
    {
        $grouped = [];
        for ($g = 0; $g < 8; $g++) {
            $grouped[] = [$g * 100 + 1, $g * 100 + 2];
        }

        $slots = $this->helper->arrange($grouped);

        // Adjacent group pairs (0↔1, 2↔3, 4↔5, 6↔7): R1 từ bảng này luôn match R2 từ bảng kia.
        // Tức trong các cặp slot (2k, 2k+1), nếu chứa rank1 của bảng g (g chẵn), partner = rank2 của bảng g+1.
        for ($i = 0; $i < 16; $i += 2) {
            [$a, $b] = [$slots[$i], $slots[$i + 1]];
            $groupA = intdiv($a, 100);
            $groupB = intdiv($b, 100);
            $this->assertSame(
                $groupA ^ 1,
                $groupB,
                "Slot pair ({$i},{$i}+1): groups phải là cặp XOR 1, got {$groupA} vs {$groupB}."
            );
        }
    }

    public function test_isEligible_rejects_non_power_of_2_group_count(): void
    {
        $this->assertFalse($this->helper->isEligible([])); // 0
        $this->assertFalse($this->helper->isEligible([[1, 2]])); // 1
        $this->assertFalse($this->helper->isEligible([[1, 2], [3, 4], [5, 6]])); // 3
        $this->assertFalse($this->helper->isEligible(array_fill(0, 5, [1, 2]))); // 5
        $this->assertFalse($this->helper->isEligible(array_fill(0, 32, [1, 2]))); // 32 (>16)
    }

    public function test_isEligible_rejects_when_any_group_missing_advancer(): void
    {
        $grouped = [
            [101, 102],
            [201, null],
            [301, 302],
            [401, 402],
        ];
        $this->assertFalse($this->helper->isEligible($grouped));

        $grouped[1] = [null, 202];
        $this->assertFalse($this->helper->isEligible($grouped));
    }

    public function test_isEligible_accepts_valid_power_of_2_with_full_advancers(): void
    {
        foreach ([2, 4, 8, 16] as $n) {
            $grouped = [];
            for ($i = 0; $i < $n; $i++) {
                $grouped[] = [$i * 10 + 1, $i * 10 + 2];
            }
            $this->assertTrue(
                $this->helper->isEligible($grouped),
                "Eligible with N={$n} groups full advancers."
            );
        }
    }
}
