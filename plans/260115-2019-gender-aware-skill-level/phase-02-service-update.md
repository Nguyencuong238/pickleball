# Phase 02: Service Update

## Context

- **Parent Plan**: [plan.md](./plan.md)
- **Dependencies**: [Phase 01](./phase-01-database-migration.md)
- **Docs**: [Code Standards](../../docs/code-standards.md)

## Overview

| Field | Value |
|-------|-------|
| Date | 2026-01-15 |
| Description | Update SkillQuizService with gender-aware skill level mapping |
| Priority | High |
| Implementation Status | Pending |
| Review Status | Pending |

## Key Insights

- Current `eloToSkillLevel()` at line 546-560 returns range strings
- Method called from 4 locations (lines 347, 605, 692)
- Need to pass User or gender to method
- Add level name constants for VN/EN display

## Requirements

1. Add ELO threshold constants for male/female
2. Add level name constants (VN and EN)
3. Update `eloToSkillLevel()` signature to accept gender
4. Return clean single number string: "3.5"
5. Add helper method for level name lookup
6. Update all callers to pass gender

## Architecture

```php
SkillQuizService
├── ELO_THRESHOLDS_MALE (const)
├── ELO_THRESHOLDS_FEMALE (const)
├── SKILL_LEVEL_NAMES (const)
├── eloToSkillLevel(int $elo, ?string $gender = 'male'): string
└── getSkillLevelName(string $level, string $locale = 'vi'): string
```

## Related Code Files

| File | Action | Lines |
|------|--------|-------|
| `app/Services/SkillQuizService.php` | Update | 546-560, 347, 605, 692 |

## Implementation Steps

### Step 1: Add Constants

Add after line 43 (after ELO_CAPS):

```php
// ELO to Skill Level thresholds
public const ELO_THRESHOLDS_MALE = [
    700  => '2.0',
    800  => '2.5',
    900  => '3.0',
    1000 => '3.5',
    1100 => '4.0',
    1200 => '4.5',
    1300 => '5.0',
];

public const ELO_THRESHOLDS_FEMALE = [
    700  => '2.5',
    800  => '3.0',
    900  => '3.5',
    1000 => '4.0',
    1100 => '4.5',
    1200 => '5.0',
    1300 => '5.5',
];

public const SKILL_LEVEL_NAMES = [
    '2.0'  => ['en' => 'Beginner',    'vi' => 'Mới chơi'],
    '2.5'  => ['en' => 'Novice',      'vi' => 'Tập sự'],
    '3.0'  => ['en' => 'Intermediate','vi' => 'Sơ cấp'],
    '3.5'  => ['en' => 'Upper Int.',  'vi' => 'Trung cấp'],
    '4.0'  => ['en' => 'Advanced',    'vi' => 'Cao cấp'],
    '4.5'  => ['en' => 'Semi-Pro',    'vi' => 'Bán chuyên'],
    '5.0'  => ['en' => 'Pro',         'vi' => 'Chuyên nghiệp'],
    '5.5+' => ['en' => 'Elite',       'vi' => 'Đỉnh cao'],
];
```

### Step 2: Update eloToSkillLevel Method

Replace lines 546-560:

```php
/**
 * Map ELO to skill level string (gender-aware)
 *
 * Female players get +0.5 level at same ELO
 * (Vietnam tournament standard: Male amateur <4.0, Female <3.5)
 */
public function eloToSkillLevel(int $elo, ?string $gender = 'male'): string
{
    $thresholds = ($gender === 'female')
        ? self::ELO_THRESHOLDS_FEMALE
        : self::ELO_THRESHOLDS_MALE;

    foreach ($thresholds as $threshold => $level) {
        if ($elo < $threshold) {
            return $level;
        }
    }

    return '5.5+';
}

/**
 * Get localized skill level name
 */
public function getSkillLevelName(string $level, string $locale = 'vi'): string
{
    return self::SKILL_LEVEL_NAMES[$level][$locale]
        ?? self::SKILL_LEVEL_NAMES['2.0'][$locale];
}
```

### Step 3: Update Callers

#### Location 1: Line 347 (finalizeQuiz return)

```php
// Before
'skill_level' => $this->eloToSkillLevel($elo),

// After
'skill_level' => $this->eloToSkillLevel($elo, $user->gender),
```

#### Location 2: Line 605 (getResult return)

```php
// Before
'skill_level' => $this->eloToSkillLevel($attempt->final_elo),

// After
'skill_level' => $this->eloToSkillLevel($attempt->final_elo, $attempt->user->gender),
```

#### Location 3: Line 692 (getUserHistory map)

Note: `$user` is the method parameter, `$attempt` doesn't have user loaded.

```php
// Before
'skill_level' => $this->eloToSkillLevel($attempt->final_elo),

// After (use $user->gender from method param, not $attempt->user)
'skill_level' => $this->eloToSkillLevel($attempt->final_elo, $user->gender),
```

## Todo List

- [ ] Add ELO_THRESHOLDS_MALE constant
- [ ] Add ELO_THRESHOLDS_FEMALE constant
- [ ] Add SKILL_LEVEL_NAMES constant
- [ ] Update eloToSkillLevel() method signature
- [ ] Implement gender-aware threshold lookup
- [ ] Add getSkillLevelName() helper
- [ ] Update caller at line 347
- [ ] Update caller at line 605
- [ ] Update caller at line 692

## Success Criteria

- [ ] `eloToSkillLevel(900, 'male')` returns "3.0"
- [ ] `eloToSkillLevel(900, 'female')` returns "3.5"
- [ ] `eloToSkillLevel(1200, null)` returns "4.5" (defaults to male)
- [ ] `getSkillLevelName('3.5', 'vi')` returns "Trung cấp"
- [ ] All callers pass correct gender

## Risk Assessment

| Risk | Level | Mitigation |
|------|-------|------------|
| Breaking existing calls | Low | Default gender='male' |
| Incorrect levels | Medium | Unit tests verify mapping |

## Security Considerations

- No security implications
- Gender is read-only in this context

## Next Steps

After completion, proceed to [Phase 03: Views & Tests](./phase-03-views-tests.md)
