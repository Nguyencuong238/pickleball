# Phase 03: Views & Tests Update

## Context

- **Parent Plan**: [plan.md](./plan.md)
- **Dependencies**: [Phase 01](./phase-01-database-migration.md), [Phase 02](./phase-02-service-update.md)
- **Docs**: [Code Standards](../../docs/code-standards.md)

## Overview

| Field | Value |
|-------|-------|
| Date | 2026-01-15 |
| Description | Update views to display skill level with name, update unit tests |
| Priority | Medium |
| Implementation Status | Pending |
| Review Status | Pending |

## Key Insights

- 3 Blade views display `skill_level`
- Test at line 215-222 needs update for new return format
- Views can optionally show level name alongside number

## Requirements

1. Update unit tests for new eloToSkillLevel signature
2. Update views to handle new format
3. Optionally display level name (VN) alongside number

## Related Code Files

| File | Action |
|------|--------|
| `tests/Unit/Services/SkillQuizServiceTest.php` | Update |
| `resources/views/front/skill-quiz/result.blade.php` | Update |
| `resources/views/front/skill-quiz/index.blade.php` | Update |
| `resources/views/verifier/requests/show.blade.php` | Update |

## Implementation Steps

### Step 1: Update Unit Tests

File: `tests/Unit/Services/SkillQuizServiceTest.php`

Replace test at lines 214-223:

```php
/** @test */
public function it_maps_elo_to_skill_level_for_male(): void
{
    $this->assertEquals('2.0', $this->service->eloToSkillLevel(650, 'male'));
    $this->assertEquals('2.5', $this->service->eloToSkillLevel(750, 'male'));
    $this->assertEquals('3.0', $this->service->eloToSkillLevel(850, 'male'));
    $this->assertEquals('3.5', $this->service->eloToSkillLevel(950, 'male'));
    $this->assertEquals('4.0', $this->service->eloToSkillLevel(1050, 'male'));
    $this->assertEquals('4.5', $this->service->eloToSkillLevel(1150, 'male'));
    $this->assertEquals('5.0', $this->service->eloToSkillLevel(1250, 'male'));
    $this->assertEquals('5.5+', $this->service->eloToSkillLevel(1350, 'male'));
}

/** @test */
public function it_maps_elo_to_skill_level_for_female(): void
{
    $this->assertEquals('2.5', $this->service->eloToSkillLevel(650, 'female'));
    $this->assertEquals('3.0', $this->service->eloToSkillLevel(750, 'female'));
    $this->assertEquals('3.5', $this->service->eloToSkillLevel(850, 'female'));
    $this->assertEquals('4.0', $this->service->eloToSkillLevel(950, 'female'));
    $this->assertEquals('4.5', $this->service->eloToSkillLevel(1050, 'female'));
    $this->assertEquals('5.0', $this->service->eloToSkillLevel(1150, 'female'));
    $this->assertEquals('5.5', $this->service->eloToSkillLevel(1250, 'female'));
    $this->assertEquals('5.5+', $this->service->eloToSkillLevel(1350, 'female'));
}

/** @test */
public function it_defaults_to_male_when_gender_null(): void
{
    $this->assertEquals('3.0', $this->service->eloToSkillLevel(850, null));
    $this->assertEquals('3.0', $this->service->eloToSkillLevel(850)); // no param
}

/** @test */
public function it_returns_skill_level_name(): void
{
    $this->assertEquals('Trung cap', $this->service->getSkillLevelName('3.5', 'vi'));
    $this->assertEquals('Upper Int.', $this->service->getSkillLevelName('3.5', 'en'));
    $this->assertEquals('Dinh cao', $this->service->getSkillLevelName('5.5+', 'vi'));
}
```

### Step 2: Update Result View

File: `resources/views/front/skill-quiz/result.blade.php` (line 334)

```blade
{{-- Before --}}
<div class="summary-value">{{ $result['skill_level'] }}</div>

{{-- After --}}
<div class="summary-value">
    {{ $result['skill_level'] }}
    <span class="text-muted small">
        ({{ App\Services\SkillQuizService::SKILL_LEVEL_NAMES[$result['skill_level']]['vi'] ?? '' }})
    </span>
</div>
```

### Step 3: Update Index View

File: `resources/views/front/skill-quiz/index.blade.php` (line 395)

```blade
{{-- Before --}}
{{ $item['skill_level'] }}

{{-- After --}}
{{ $item['skill_level'] }}
```

No change needed - single number is cleaner for list view.

### Step 4: Update Verifier View

File: `resources/views/verifier/requests/show.blade.php` (line 31)

```blade
{{-- Before --}}
<span class="badge badge-info">{{ $quizResult['skill_level'] }}</span>

{{-- After --}}
<span class="badge badge-info">
    {{ $quizResult['skill_level'] }}
    ({{ App\Services\SkillQuizService::SKILL_LEVEL_NAMES[$quizResult['skill_level']]['vi'] ?? '' }})
</span>
```

### Step 5: Run Tests

```bash
php artisan test tests/Unit/Services/SkillQuizServiceTest.php
```

## Todo List

- [ ] Update `it_maps_elo_to_skill_level` test for male
- [ ] Add `it_maps_elo_to_skill_level_for_female` test
- [ ] Add `it_defaults_to_male_when_gender_null` test
- [ ] Add `it_returns_skill_level_name` test
- [ ] Update result.blade.php display
- [ ] Update show.blade.php (verifier) display
- [ ] Run tests and verify pass

## Success Criteria

- [ ] All unit tests pass
- [ ] `php artisan test` shows no failures
- [ ] Skill level displays as single number in views
- [ ] Vietnamese name shows in parentheses where applicable

## Risk Assessment

| Risk | Level | Mitigation |
|------|-------|------------|
| Test failures | Low | Clear test cases |
| View display issues | Low | Simple string change |

## Security Considerations

- No security implications
- Static constant access in views is safe

## Next Steps

After all phases complete:
1. Run full test suite
2. Manual QA on skill quiz flow
3. Consider adding gender field to profile edit form
4. Update documentation

## Unresolved Questions

1. Should gender be required for new users during registration?
2. Should existing users be prompted to set gender?
3. Need UI for setting gender in profile edit?
