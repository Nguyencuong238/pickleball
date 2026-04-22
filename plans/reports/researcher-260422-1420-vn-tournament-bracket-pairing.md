# Tournament Bracket Cross-Group Pairing Research
**Date:** 2026-04-22 | **Scope:** Pickleball VN + international sports tournament bracket design

---

## Executive Summary

Pattern user proposes: `A1 vs B2, B1 vs C2, C1 vs D2, ... Xn1 vs ((Xn+1) mod N)2` — i.e. each group winner faces runner-up from the *next* adjacent group, with last group wrapping to first.

No official name found in literature. Closest terms: "cross-group pairing", "adjacent group cross-seeding", "shifted pairing" (informal). The FIFA/UEFA model uses "paired groups" (A+B, C+D), not cyclic rotation. Pickleball VN tournaments do not publish detailed pairing regulation docs publicly — PVNA/PPA Tour Asia use TD discretion. For projects with small N (3–8 groups), the proposed cyclic pattern is functionally sound and group-separation-safe, but inferior to proper seed-based placement for competitive fairness.

---

## 1. Pattern Naming

**No official universally-accepted term found.**

| Informal name | Source context |
|---|---|
| "Cross-group pairing" | BracketsNinja, Score7 guides — generic descriptor |
| "Cross-seeding" | Challonge feature request threads |
| "Adjacent group pairing" | Search synthesis — A1 vs B2, B1 vs A2 pattern |
| "Intergroup seeding" | Academic papers on tournament fairness |
| "Shifted pairing" / "Cyclic rotation" | No documented use — researcher's synthesis label |

The pattern user describes is a **cyclic shift variant** of cross-group pairing:
- FIFA/UEFA: uses **paired-group** model — A+B paired, C+D paired (adjacent pairs, not cyclic)
- User pattern: fully cyclic — last group wraps to first (A wraps: last group's winner faces A's runner-up)

The cyclic rotation property means no "orphan" group — every group winner faces a runner-up from a distinct adjacent group. This is more systematic than FIFA for odd-N scenarios.

---

## 2. VN Tournament Practice

**Finding: No formal public pairing regulation documents found for any VN pickleball organization.**

Sources checked:
- **PVNA (pickleballvna.org):** tournament listings confirm "chia bảng thi đấu vòng tròn, nhất nhì mỗi bảng vào vòng knock-out" — but no pairing methodology specified
- **Vietnam Pickleball Championship 2025:** confirms group round-robin + direct elimination, no specific seeding formula
- **PPA Tour Asia MB Vietnam Open/Cup 2025:** shows Round of 16 brackets, TD discretion on seeding (references `ppatour-asia.com/selection-seeding` page, content not accessible)
- **AOPC 2024 (Asia Open Pickleball):** "Round Robin follow by Single Elimination" — champions skip to Round 2 of KO if 2+ groups, no cross-group pairing formula documented
- **Vietnam Pickleball Open Cup (VPC):** promotional only, no regulations available

**Practical inference from PVNA data:** Vietnamese amateur tournaments with 3–6 groups typically use one of:
1. Manual draw by TD after group stage (most common, unregulated)
2. Seed by total points across all groups → standard 1-vs-N bracket
3. No documented case of explicit cyclic cross-group pairing found

---

## 3. International Standards

### FIFA World Cup

| Edition | Groups | Advance | KO Pairing Model |
|---|---|---|---|
| 2022 (32 teams) | 8 groups of 4 | Top 2 (16 teams) | **Paired groups**: A1 vs B2, B1 vs A2 — then C1 vs D2, D1 vs C2 etc. |
| 2026 (48 teams) | 12 groups of 4 | Top 2 + 8 best 3rd (32 teams) | **Complex**: winners vs 3rd-placers, runners-up vs runners-up; 495-combination table for 3rd-place allocation |

2022 pattern: `(A1 vs B2), (B1 vs A2)` then `(C1 vs D2), (D1 vs C2)` etc. — **paired groups**, not cyclic rotation.
Key constraint: same-group teams can't meet before QF.

### UEFA Euro 2024 (24 teams, 6 groups)

- Top 2 per group (12 teams) + 4 best 3rd-placed (16 total)
- Pairing: fixed bracket positions, some group winners face runners-up, some runners-up face each other, some face 3rd-placed
- 3rd-place placement: 15 pre-defined combination tables (ABCD, ABCE, ... CDEF) → allocates 3rd-placers to specific bracket slots based on which groups' 3rd-placers qualified
- NOT a simple A1-vs-B2 pattern for all matches

### BWF Badminton (Olympics 2024)

- Paris 2024: men's singles 41 players → 13 groups of 3–4; **only group winner** advances to KO
- Doubles: top 2 per group → quarter-finals; **second draw held** to separate same-group pairs
- Key principle: second draw after group stage ensures same-group separation. Pairing not fixed/predetermined — randomized draw with constraint.

### FIVB Volleyball World Championship 2025

- 32 teams → 8 pools of 4; top 2 per pool → Round of 16
- Round of 16 seeded by pool performance; same-pool separation enforced
- Exact cross-pool formula not publicly documented in detail

### Summary: What "standard" looks like internationally

| Organization | Pattern | Same-group protection | Seed protection |
|---|---|---|---|
| FIFA WC 2022 | Paired groups (A+B, C+D adjacent pairs) | Yes (can't meet until QF) | Partial (winner faces runner-up) |
| FIFA WC 2026 | Hybrid (paired + 3rd-place lottery) | Yes | Complex |
| UEFA Euro | Fixed bracket + 3rd-place table | Yes | Yes |
| BWF Olympics | Group winner only; random draw w/ constraint | Yes (second draw) | No |
| Generic (4 groups) | A1-B2, B1-A2, C1-D2, D1-C2 | Yes | Yes (winner vs runner-up) |

---

## 4. Pattern Comparison: Cyclic vs Standard Seed Bracket

### User's Cyclic Pattern
```
N groups, top 2 advance:
Match 1: A1 vs B2
Match 2: B1 vs C2
...
Match N: N1 vs A2  (wrap)
```

### Standard Seed Bracket (current BracketSeedingHelper.php)
Collects all advancers, sorts by seed_number, places into standard tournament positions [1,8,5,4,3,6,7,2] etc.

| Criterion | Cyclic Pattern | Standard Seed Bracket |
|---|---|---|
| Same-group separation | Yes — by construction | Only if seeding is known and correct |
| Seed/strength protection | No — ignores pre-tournament seeds | Yes — seed 1 separated from seed 2 |
| Predictability (admin) | High — formula is mechanical | Medium — requires seed data integrity |
| Fairness | Medium — A1 always faces B2, regardless of relative strength | High — best team protected from tough early draw |
| Fan understandability | High — simple adjacent rule | Medium |
| Handles variable group count | Yes — cyclic wrap solves odd-N | Requires power-of-2 padding (byes) |
| Requires pre-seeded athletes | No | Yes |
| Group performance rewarded | Partially (1st vs 2nd cross-group) | Depends on seed accuracy |

**Key trade-off:** Cyclic prioritizes group-separation + simplicity over seed-protection. Standard bracket prioritizes protecting strong seeds over group-separation.

**Practical concern:** If VN amateur tournaments have unreliable seed data (seed_number = 0 common), cyclic pattern may actually be *more* fair in practice than a broken seed bracket.

---

## 5. Library / Implementation References

| Library | Language | Group→KO Support | Notes |
|---|---|---|---|
| [brackets-manager.js](https://github.com/Drarig29/brackets-manager.js) | JS/TS | Round-robin → elimination chaining | Seeding from RR results; cross-group pairing not explicitly documented |
| [Tournament Generator](https://github.com/Heroyt/tournament-generator) | PHP | Yes via `progression()` API | `$group->progression($final, 0, 2)` advances top 2; cross-group ordering manual |
| [Toornament](https://developer.toornament.com) | API/SaaS | Semi-manual (rank-order placement) | v2 removed auto placement; manual outgoing-participant ordering |
| [Challonge](https://challonge.com) | SaaS | Manual seeding from group results | Feature requests for cross-pool pairing unresolved as of research date |
| [evroon/bracket](https://github.com/evroon/bracket) | Python | Full tournament system | Self-hosted; group stage support, details require code inspection |
| [Bracketeer](https://bbtheo.github.io/bracketeer/) | R | World Cup style group→KO | Demo shows 8 groups top-2 advance; pairing algorithm undocumented |

**Bottom line:** No off-the-shelf library found with built-in cyclic cross-group pairing. All require either manual seeding or custom implementation logic. The existing `BracketSeedingHelper.php` handles bracket sizing and seed-position placement — a cyclic cross-group module would be additive.

---

## 6. Edge Cases: Non-Power-of-2 Group Counts

| Groups (N) | Top-2 = 2N advancers | Power of 2? | Solution |
|---|---|---|---|
| 2 | 4 | Yes (4) | Standard |
| 3 | 6 | No | Add 2 byes or promote best 3rd-placer (→ 7) or use 8-slot w/ 2 byes |
| 4 | 8 | Yes (8) | Standard |
| 5 | 10 | No | Add 2 byes (→ 12? No — 16 slot w/ 6 byes) or take top-3 from some groups |
| 6 | 12 | No | 16-slot bracket w/ 4 byes; or promote 4 best 3rd (like UEFA → 16) |
| 7 | 14 | No | 16-slot w/ 2 byes |
| 8 | 16 | Yes (16) | Standard |

**Approach 1 — Byes:** Add bye slots. Cyclic pattern still works; lowest-seeded advancers get byes. Disadvantage: some groups get easier first-round path.

**Approach 2 — Best N-th placed (UEFA model):** Supplement with best 3rd-placers to reach power-of-2. Requires cross-group ranking of 3rd-placers by points/GD/goals. Complex but fairer. Used by: UEFA Euro (4 best-3rd), FIFA WC 2026 (8 best-3rd).

**Approach 3 — Preliminary round:** Pair the "extra" advancers in a play-in round. E.g., 6 groups → 12 advancers → play-in round for 4 spots → 8-team QF.

**Recommendation for this project:** With typical VN tournament sizes (3–8 groups), Approach 1 (byes) is simplest to implement. Cyclic wrap still works — just assign byes to positions that would face the lowest-ranked qualifiers.

---

## 7. Recommendation for This Project

**Current state:** `BracketSeedingHelper` uses standard seed-sorted bracket. This is correct *if* `seed_number` data is reliable. In VN amateur context, many athletes have `seed_number = 0`.

**For cross-group pairing change, recommended approach:**

1. **Collect advancers grouped by source group** (not globally sorted by seed)
2. **Apply cyclic pairing:** for i = 0..N-1, pair `groups[i].first vs groups[(i+1) % N].second`
3. **Build bracket slots** from these pairings directly (pairs → match 1, 2, 3...)
4. **Handle non-power-of-2:** add byes for remaining slots, assign to lowest-ranked advancers

**When to use cyclic vs seed-based:**
- Use cyclic if: seed data unreliable, group separation is priority, TD wants mechanical transparency
- Use seed-based if: all athletes have valid seed numbers, protecting top seeds is priority

**Hybrid option:** Use cyclic for pairing (group separation), but within each match pair, honor seeding for home/away assignment or bracket half placement.

**Scope of change to existing code:**
- `BracketSeedingHelper::collectSeededAthletes()` — currently flattens all groups, loses group identity. Need new method: `collectGroupedAdvancers()` returning `array<groupIndex, [1st, 2nd]>`
- `arrangeSeedsIntoBracket()` — replace standard position-map logic with cyclic pairing loop
- `calculateBracketSize()` — no change needed (still rounds up to power-of-2)

---

## Unresolved Questions

1. **Official PPA Tour Asia seeding rules** for VN events: `ppatour-asia.com/selection-seeding` page was inaccessible. May clarify if professional standard specifies cross-group pairing.

2. **PVNA/Vietnam Pickleball Federation formal regulations:** No publicly accessible PDF rulebook found. May exist internally at PVNA. Worth requesting directly.

3. **Tie-breaking for cyclic pairing with byes:** Which advancers get byes in 3-group (6 advancers, 8 slots) scenario? Best overall 1st-placers? Or best performers from group stage? No standard found.

4. **Does the cyclic pattern guarantee bracket balance for all N?** For N=3 (groups A,B,C): A1-B2, B1-C2, C1-A2 → 6 players, 8-slot with 2 byes. Byes go to which 2 of the 6? Needs explicit rule.

5. **User's requirement on bracket half separation:** Should A1 and A2 be on opposite bracket halves (standard guarantee: group teammates can't meet before final)? Cyclic pairing places them in separate matches R1 but doesn't guarantee bracket-half separation in all N configurations.

---

*Sources consulted: FIFA.com, Wikipedia (UEFA Euro 2024 KO, 2026 WC KO), BracketsNinja, Score7 KB, Toornament docs, PVNA/pickleballvna.org, thegioipickleball.com, AFPickleball AOPC 2024, PPA Tour Asia, USA Pickleball, BWF (Olympics 2024), FIVB Volleyball WC 2025, GitHub (Heroyt/tournament-generator, Drarig29/brackets-manager.js)*
