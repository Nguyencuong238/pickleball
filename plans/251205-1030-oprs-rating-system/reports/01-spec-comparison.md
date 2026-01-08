# OPRS Plan vs Specification Comparison

**Date**: 2025-12-05
**Status**: Review Complete

## Summary

Found **3 discrepancies** between plan and OPRS specification that need correction.

---

## 1. K-Factor Values - MISMATCH

### Spec Requirements
| Condition | K-Factor |
|-----------|----------|
| New player (< 30 matches) | **30** |
| Experienced player | **24** |
| Pro (Elo > 1800) | **20** |

### Current Implementation (EloService.php)
| Condition | K-Factor |
|-----------|----------|
| 0-30 matches | **40** |
| 31-100 matches | **24** |
| 100+ matches | **16** |

### Discrepancies
1. New player K-factor: Spec = 30, Current = 40
2. Pro check missing: Spec uses Elo > 1800 threshold, Current uses match count
3. Experienced K-factor: Spec = 24, Current = 16 for 100+

### Action Required
Update `EloService.php` K-factor logic:
```php
private const K_NEW_PLAYER = 30;      // < 30 matches (was 40)
private const K_REGULAR = 24;         // 30+ matches
private const K_PRO = 20;             // Elo > 1800

public function getKFactor(User $user): int
{
    if ($user->total_ocr_matches < 30) {
        return self::K_NEW_PLAYER;
    }
    if ($user->elo_rating > 1800) {
        return self::K_PRO;
    }
    return self::K_REGULAR;
}
```

---

## 2. Default Starting Elo - NEEDS VERIFICATION

### Spec Requirements
- Default starting Elo: **1000**

### Current Implementation
- User migration shows: `$table->integer('elo_rating')->default(1000)`

### Status: MATCHES

---

## 3. OPR Level Thresholds - MATCHES

### Spec Requirements
| OPR Level | OPRS Range |
|-----------|------------|
| 1.0 | < 600 |
| 2.0 | 600-899 |
| 3.0 | 900-1099 |
| 3.5 | 1100-1349 |
| 4.0 | 1350-1599 |
| 4.5 | 1600-1849 |
| 5.0+ | >= 1850 |

### Plan Implementation
```php
public const OPR_LEVELS = [
    '1.0' => ['min' => 0, 'max' => 599],
    '2.0' => ['min' => 600, 'max' => 899],
    '3.0' => ['min' => 900, 'max' => 1099],
    '3.5' => ['min' => 1100, 'max' => 1349],
    '4.0' => ['min' => 1350, 'max' => 1599],
    '4.5' => ['min' => 1600, 'max' => 1849],
    '5.0+' => ['min' => 1850, 'max' => PHP_INT_MAX],
];
```

### Status: MATCHES

---

## 4. OPRS Formula - MATCHES

### Spec
```
OPRS = (0.7 x Elo) + (0.2 x Challenge) + (0.1 x Community)
```

### Plan
```php
public const WEIGHT_ELO = 0.7;
public const WEIGHT_CHALLENGE = 0.2;
public const WEIGHT_COMMUNITY = 0.1;
```

### Status: MATCHES

---

## 5. Challenge Types & Points - MATCHES

### Spec Requirements
| Challenge | Points | Threshold |
|-----------|--------|-----------|
| Dinking Rally | +10 | 20 rallies |
| Drop Shot | +8 | 5/10 shots |
| Serve Accuracy | +6 | 7/10 serves |
| Monthly Test | +30-50 | Score-based |

### Plan Implementation (phase-02)
```php
public const POINTS = [
    self::TYPE_DINKING_RALLY => 10,
    self::TYPE_DROP_SHOT => 8,
    self::TYPE_SERVE_ACCURACY => 6,
    self::TYPE_MONTHLY_TEST => ['min' => 30, 'max' => 50],
];

public const THRESHOLDS = [
    self::TYPE_DINKING_RALLY => ['rallies' => 20],
    self::TYPE_DROP_SHOT => ['success' => 5, 'total' => 10],
    self::TYPE_SERVE_ACCURACY => ['success' => 7, 'total' => 10],
    self::TYPE_MONTHLY_TEST => ['score' => 70],
];
```

### Status: MATCHES

---

## 6. Community Activities & Points - MATCHES

### Spec Requirements
| Activity | Points | Limit |
|----------|--------|-------|
| Check-in | +2 | Unlimited |
| Event participation | +5 | Per event |
| Referral | +10 | Unlimited |
| 5 matches/week | +5 | 1x/week |
| Monthly challenge | +15 | 1x/month |

### Plan Implementation (phase-02)
```php
public const POINTS = [
    self::TYPE_CHECK_IN => 2,
    self::TYPE_EVENT => 5,
    self::TYPE_REFERRAL => 10,
    self::TYPE_WEEKLY_MATCHES => 5,
    self::TYPE_MONTHLY_CHALLENGE => 15,
];
```

### Status: MATCHES

---

## 7. Match Types for Elo - PARTIAL MATCH

### Spec Requirements
| Match Type | Counts for Elo |
|------------|----------------|
| Official OnePickleball | Yes |
| Partner tournaments | Yes |
| OCR (challenge matches) | Yes |
| Ranked challenge (supervised) | Yes |
| Friendly matches | No |

### Plan Implementation
Added `match_category` enum in phase-01:
```php
$table->enum('match_category', [
    'official',
    'partner',
    'ocr',
    'ranked_challenge'
])->default('ocr');
```

### Status: MATCHES (friendly matches excluded by not being tracked)

---

## 8. Elo Rank Tiers - MISMATCH

### Spec (OPR Levels - for skill display)
Uses OPR Level 1.0-5.0+ system

### Current OCR Implementation (for Elo display)
| Rank | Elo Range |
|------|-----------|
| Bronze | 0-1099 |
| Silver | 1100-1299 |
| Gold | 1300-1499 |
| Platinum | 1500-1699 |
| Diamond | 1700-1899 |
| Master | 1900-2099 |
| Grandmaster | 2100+ |

### Consideration
The existing Elo rank system (Bronze-Grandmaster) is separate from OPR Level (1.0-5.0+).
- **OPR Level** = Based on total OPRS (Elo + Challenge + Community)
- **Elo Rank** = Based on Elo rating only (existing system)

### Status: OK - Two separate ranking displays

---

## Required Changes Summary

### Must Fix (Before Implementation)

1. **Update K-Factor in EloService.php**
   - Change K_NEW_PLAYER: 40 -> 30
   - Add Pro check: Elo > 1800 -> K=20
   - Change K_EXPERIENCED: 16 -> 24 (for regular players)

### Optional Enhancements

1. Add match category filtering for Elo calculation (only count official/partner/ocr/ranked matches)

---

## Updated Phase Files Needed

1. **phase-03-oprs-service.md** - Add note about K-factor fix
2. **phase-01-database-schema.md** - Already includes match_category

---

## Verification Checklist

| Item | Spec | Plan | Status |
|------|------|------|--------|
| OPRS Formula | 0.7/0.2/0.1 | 0.7/0.2/0.1 | MATCH |
| OPR Levels | 1.0-5.0+ | 1.0-5.0+ | MATCH |
| Level thresholds | <600 to 1850+ | <600 to 1850+ | MATCH |
| K-factor new | 30 | 40 (current) | FIX NEEDED |
| K-factor regular | 24 | 24 | MATCH |
| K-factor pro | 20 (>1800 Elo) | 16 (>100 matches) | FIX NEEDED |
| Challenges | 4 types | 4 types | MATCH |
| Challenge points | 10/8/6/30-50 | 10/8/6/30-50 | MATCH |
| Activities | 5 types | 5 types | MATCH |
| Activity points | 2/5/10/5/15 | 2/5/10/5/15 | MATCH |
| Default Elo | 1000 | 1000 | MATCH |
