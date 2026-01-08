# Phase 9: Testing & Validation

**Parent Plan**: [plan.md](./plan.md)
**Dependencies**: [Phase 8: Admin Panel](./phase-08-admin-panel.md)
**Related Docs**: [code-standards.md](../../docs/code-standards.md)

## Overview

| Field | Value |
|-------|-------|
| Date | 2025-12-05 |
| Description | Write tests and validate OPRS implementation |
| Priority | High |
| Implementation Status | Pending |
| Review Status | Pending |

## Key Insights

1. Focus on core OPRS calculation accuracy
2. Test all three component integrations
3. Test level threshold boundaries
4. Test API endpoints with various scenarios
5. Validate data integrity

## Requirements

### Functional
- Unit tests for OprsService
- Unit tests for ChallengeService
- Unit tests for CommunityService
- Feature tests for API endpoints
- Integration tests for workflows

### Non-Functional
- 80%+ code coverage for services
- All edge cases covered
- Performance testing for leaderboard

## Architecture

### Test Organization

```
tests/
├── Unit/
│   ├── Services/
│   │   ├── OprsServiceTest.php
│   │   ├── ChallengeServiceTest.php
│   │   └── CommunityServiceTest.php
│   └── Models/
│       ├── ChallengeResultTest.php
│       └── CommunityActivityTest.php
├── Feature/
│   ├── Api/
│   │   ├── OprsApiTest.php
│   │   ├── ChallengeApiTest.php
│   │   └── CommunityApiTest.php
│   └── Admin/
│       └── OprsAdminTest.php
└── Integration/
    └── OprsWorkflowTest.php
```

## Related Code Files

| File | Action | Purpose |
|------|--------|---------|
| `tests/Unit/Services/OprsServiceTest.php` | Create | OPRS calculation tests |
| `tests/Unit/Services/ChallengeServiceTest.php` | Create | Challenge logic tests |
| `tests/Unit/Services/CommunityServiceTest.php` | Create | Community logic tests |
| `tests/Feature/Api/OprsApiTest.php` | Create | API endpoint tests |
| `tests/Feature/Admin/OprsAdminTest.php` | Create | Admin function tests |

## Implementation Steps

### Step 1: OprsService Unit Tests

```php
<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\OprsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OprsServiceTest extends TestCase
{
    use RefreshDatabase;

    private OprsService $oprsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oprsService = app(OprsService::class);
    }

    /** @test */
    public function it_calculates_oprs_with_correct_weights(): void
    {
        $user = User::factory()->create([
            'elo_rating' => 1000,
            'challenge_score' => 100,
            'community_score' => 50,
        ]);

        $oprs = $this->oprsService->calculateOprs($user);

        // OPRS = (0.7 * 1000) + (0.2 * 100) + (0.1 * 50)
        // OPRS = 700 + 20 + 5 = 725
        $this->assertEquals(725.0, $oprs);
    }

    /** @test */
    public function it_calculates_oprs_with_zero_components(): void
    {
        $user = User::factory()->create([
            'elo_rating' => 1000,
            'challenge_score' => 0,
            'community_score' => 0,
        ]);

        $oprs = $this->oprsService->calculateOprs($user);

        // OPRS = (0.7 * 1000) + 0 + 0 = 700
        $this->assertEquals(700.0, $oprs);
    }

    /**
     * @test
     * @dataProvider oprLevelProvider
     */
    public function it_calculates_correct_opr_level(float $oprs, string $expectedLevel): void
    {
        $level = $this->oprsService->calculateOprLevel($oprs);
        $this->assertEquals($expectedLevel, $level);
    }

    public static function oprLevelProvider(): array
    {
        return [
            'beginner low' => [0, '1.0'],
            'beginner high' => [599, '1.0'],
            'novice low' => [600, '2.0'],
            'novice high' => [899, '2.0'],
            'intermediate low' => [900, '3.0'],
            'intermediate high' => [1099, '3.0'],
            'upper intermediate low' => [1100, '3.5'],
            'upper intermediate high' => [1349, '3.5'],
            'advanced low' => [1350, '4.0'],
            'advanced high' => [1599, '4.0'],
            'pro low' => [1600, '4.5'],
            'pro high' => [1849, '4.5'],
            'elite' => [1850, '5.0+'],
            'elite high' => [3000, '5.0+'],
        ];
    }

    /** @test */
    public function it_updates_user_oprs_and_records_history(): void
    {
        $user = User::factory()->create([
            'elo_rating' => 1000,
            'challenge_score' => 100,
            'community_score' => 50,
            'total_oprs' => 0,
        ]);

        $this->oprsService->updateUserOprs($user, 'test_reason');

        $user->refresh();

        $this->assertEquals(725.0, $user->total_oprs);
        $this->assertEquals('2.0', $user->opr_level);

        $this->assertDatabaseHas('oprs_histories', [
            'user_id' => $user->id,
            'total_oprs' => 725.0,
            'opr_level' => '2.0',
            'change_reason' => 'test_reason',
        ]);
    }

    /** @test */
    public function it_returns_correct_oprs_breakdown(): void
    {
        $user = User::factory()->create([
            'elo_rating' => 1500,
            'challenge_score' => 200,
            'community_score' => 100,
            'total_oprs' => 1100, // Precalculated
            'opr_level' => '3.5',
        ]);

        $breakdown = $this->oprsService->getOprsBreakdown($user);

        $this->assertEquals(1500, $breakdown['elo']['raw']);
        $this->assertEquals(1050, $breakdown['elo']['weighted']); // 0.7 * 1500
        $this->assertEquals(200, $breakdown['challenge']['raw']);
        $this->assertEquals(40, $breakdown['challenge']['weighted']); // 0.2 * 200
        $this->assertEquals(100, $breakdown['community']['raw']);
        $this->assertEquals(10, $breakdown['community']['weighted']); // 0.1 * 100
    }

    /** @test */
    public function it_estimates_oprs_change_correctly(): void
    {
        $user = User::factory()->create([
            'total_oprs' => 1000,
        ]);

        $estimate = $this->oprsService->estimateOprsChange($user, 'elo', 100);

        // Change = 100 * 0.7 = 70
        $this->assertEquals(70.0, $estimate['change']);
        $this->assertEquals(1070.0, $estimate['after']);
    }
}
```

### Step 2: ChallengeService Unit Tests

```php
<?php

namespace Tests\Unit\Services;

use App\Models\ChallengeResult;
use App\Models\User;
use App\Services\ChallengeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallengeServiceTest extends TestCase
{
    use RefreshDatabase;

    private ChallengeService $challengeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->challengeService = app(ChallengeService::class);
    }

    /** @test */
    public function it_submits_passing_challenge_and_awards_points(): void
    {
        $user = User::factory()->create([
            'challenge_score' => 0,
        ]);

        $result = $this->challengeService->submitChallenge(
            $user,
            ChallengeResult::TYPE_DINKING_RALLY,
            20 // Threshold is 20
        );

        $this->assertTrue($result->passed);
        $this->assertEquals(10, $result->points_earned);

        $user->refresh();
        $this->assertEquals(10, $user->challenge_score);
    }

    /** @test */
    public function it_submits_failing_challenge_without_points(): void
    {
        $user = User::factory()->create([
            'challenge_score' => 0,
        ]);

        $result = $this->challengeService->submitChallenge(
            $user,
            ChallengeResult::TYPE_DINKING_RALLY,
            15 // Below threshold of 20
        );

        $this->assertFalse($result->passed);
        $this->assertEquals(0, $result->points_earned);

        $user->refresh();
        $this->assertEquals(0, $user->challenge_score);
    }

    /** @test */
    public function it_enforces_monthly_test_limit(): void
    {
        $user = User::factory()->create();

        // First submission should work
        $result1 = $this->challengeService->submitChallenge(
            $user,
            ChallengeResult::TYPE_MONTHLY_TEST,
            80
        );
        $this->assertInstanceOf(ChallengeResult::class, $result1);

        // Second submission same month should fail
        $this->expectException(\InvalidArgumentException::class);
        $this->challengeService->submitChallenge(
            $user,
            ChallengeResult::TYPE_MONTHLY_TEST,
            85
        );
    }

    /** @test */
    public function it_allows_monthly_test_in_new_month(): void
    {
        $user = User::factory()->create();

        // Create old monthly test
        ChallengeResult::factory()->create([
            'user_id' => $user->id,
            'challenge_type' => ChallengeResult::TYPE_MONTHLY_TEST,
            'created_at' => Carbon::now()->subMonth(),
        ]);

        // Should allow new submission
        $result = $this->challengeService->submitChallenge(
            $user,
            ChallengeResult::TYPE_MONTHLY_TEST,
            80
        );

        $this->assertInstanceOf(ChallengeResult::class, $result);
    }

    /**
     * @test
     * @dataProvider challengeThresholdProvider
     */
    public function it_checks_pass_threshold_correctly(
        string $type,
        int $score,
        bool $shouldPass
    ): void {
        $user = User::factory()->create();

        $result = $this->challengeService->submitChallenge($user, $type, $score);

        $this->assertEquals($shouldPass, $result->passed);
    }

    public static function challengeThresholdProvider(): array
    {
        return [
            'dinking pass' => [ChallengeResult::TYPE_DINKING_RALLY, 20, true],
            'dinking fail' => [ChallengeResult::TYPE_DINKING_RALLY, 19, false],
            'drop shot pass' => [ChallengeResult::TYPE_DROP_SHOT, 5, true],
            'drop shot fail' => [ChallengeResult::TYPE_DROP_SHOT, 4, false],
            'serve pass' => [ChallengeResult::TYPE_SERVE_ACCURACY, 7, true],
            'serve fail' => [ChallengeResult::TYPE_SERVE_ACCURACY, 6, false],
            'monthly pass' => [ChallengeResult::TYPE_MONTHLY_TEST, 70, true],
            'monthly fail' => [ChallengeResult::TYPE_MONTHLY_TEST, 69, false],
        ];
    }
}
```

### Step 3: API Feature Tests

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OprsApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_user_oprs_profile(): void
    {
        $user = User::factory()->create([
            'elo_rating' => 1200,
            'challenge_score' => 50,
            'community_score' => 30,
            'total_oprs' => 853, // Precalculated
            'opr_level' => '2.0',
        ]);

        $response = $this->getJson("/api/oprs/profile/{$user->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.oprs.total', 853)
            ->assertJsonPath('data.oprs.level', '2.0')
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'oprs',
                    'components' => ['elo', 'challenge', 'community'],
                    'stats',
                ],
            ]);
    }

    /** @test */
    public function it_returns_oprs_leaderboard(): void
    {
        User::factory()->count(5)->create([
            'total_oprs' => fn() => rand(500, 1500),
        ]);

        $response = $this->getJson('/api/oprs/leaderboard');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function it_filters_leaderboard_by_level(): void
    {
        User::factory()->create(['opr_level' => '3.0', 'total_oprs' => 1000]);
        User::factory()->create(['opr_level' => '4.0', 'total_oprs' => 1400]);
        User::factory()->create(['opr_level' => '3.0', 'total_oprs' => 950]);

        $response = $this->getJson('/api/oprs/leaderboard/level/3.0');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_submits_challenge_for_authenticated_user(): void
    {
        $user = User::factory()->create(['challenge_score' => 0]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/challenges/submit', [
            'challenge_type' => 'dinking_rally',
            'score' => 25,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.passed', true)
            ->assertJsonPath('data.points_earned', 10);
    }

    /** @test */
    public function it_requires_auth_for_challenge_submission(): void
    {
        $response = $this->postJson('/api/challenges/submit', [
            'challenge_type' => 'dinking_rally',
            'score' => 25,
        ]);

        $response->assertUnauthorized();
    }

    /** @test */
    public function it_validates_challenge_submission(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/challenges/submit', [
            'challenge_type' => 'invalid_type',
            'score' => 25,
        ]);

        $response->assertUnprocessable();
    }
}
```

### Step 4: Integration Tests

```php
<?php

namespace Tests\Integration;

use App\Models\ChallengeResult;
use App\Models\OcrMatch;
use App\Models\User;
use App\Services\ChallengeService;
use App\Services\CommunityService;
use App\Services\EloService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OprsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function oprs_updates_after_match_completion(): void
    {
        $winner = User::factory()->create([
            'elo_rating' => 1000,
            'total_oprs' => 700,
        ]);
        $loser = User::factory()->create([
            'elo_rating' => 1000,
            'total_oprs' => 700,
        ]);

        $match = OcrMatch::factory()->create([
            'challenger_id' => $winner->id,
            'opponent_id' => $loser->id,
            'status' => OcrMatch::STATUS_CONFIRMED,
            'winner_team' => 'challenger',
        ]);

        $eloService = app(EloService::class);
        $eloService->processMatchResult($match);

        $winner->refresh();
        $loser->refresh();

        // Winner should have higher OPRS
        $this->assertGreaterThan(700, $winner->total_oprs);
        // Loser should have lower OPRS
        $this->assertLessThan(700, $loser->total_oprs);
    }

    /** @test */
    public function oprs_updates_after_challenge_completion(): void
    {
        $user = User::factory()->create([
            'elo_rating' => 1000,
            'challenge_score' => 0,
            'total_oprs' => 700,
        ]);

        $challengeService = app(ChallengeService::class);
        $challengeService->submitChallenge(
            $user,
            ChallengeResult::TYPE_DINKING_RALLY,
            20
        );

        $user->refresh();

        // Challenge score should increase
        $this->assertEquals(10, $user->challenge_score);
        // OPRS should update (700 + 0.2 * 10 = 702)
        $this->assertEquals(702, $user->total_oprs);
    }

    /** @test */
    public function oprs_level_changes_at_threshold(): void
    {
        $user = User::factory()->create([
            'elo_rating' => 850, // Just below 3.0 threshold
            'challenge_score' => 0,
            'community_score' => 0,
            'total_oprs' => 595, // 0.7 * 850 = 595, level 1.0
            'opr_level' => '1.0',
        ]);

        // Increase elo to cross threshold
        $user->update(['elo_rating' => 1290]);

        app(\App\Services\OprsService::class)->updateUserOprs($user, 'test');

        $user->refresh();

        // OPRS = 0.7 * 1290 = 903, should be level 3.0
        $this->assertEquals('3.0', $user->opr_level);
    }

    /** @test */
    public function full_oprs_workflow_updates_correctly(): void
    {
        $user = User::factory()->create([
            'elo_rating' => 1000,
            'challenge_score' => 0,
            'community_score' => 0,
            'total_oprs' => 700,
            'opr_level' => '2.0',
        ]);

        // Complete a challenge (+10 challenge score)
        $challengeService = app(ChallengeService::class);
        $challengeService->submitChallenge($user, ChallengeResult::TYPE_DINKING_RALLY, 20);

        $user->refresh();
        $this->assertEquals(10, $user->challenge_score);

        // Check in at stadium (+2 community score)
        $stadium = \App\Models\Stadium::factory()->create();
        $communityService = app(CommunityService::class);
        $communityService->checkIn($user, $stadium);

        $user->refresh();
        $this->assertEquals(2, $user->community_score);

        // Final OPRS = (0.7 * 1000) + (0.2 * 10) + (0.1 * 2) = 700 + 2 + 0.2 = 702.2
        $this->assertEquals(702.2, $user->total_oprs);
    }
}
```

## Todo List

- [ ] Create OprsServiceTest
- [ ] Create ChallengeServiceTest
- [ ] Create CommunityServiceTest
- [ ] Create OprsApiTest
- [ ] Create OprsAdminTest
- [ ] Create OprsWorkflowTest
- [ ] Run full test suite
- [ ] Verify 80%+ coverage
- [ ] Fix any failing tests
- [ ] Document edge cases

## Success Criteria

1. All tests pass
2. 80%+ code coverage on services
3. OPRS formula verified correct
4. Level thresholds verified correct
5. API endpoints return expected data
6. Admin functions work correctly

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| Test data inconsistency | Medium | Use factories |
| Missing edge cases | Medium | Data providers |
| Flaky tests | Low | Refresh database |

## Security Considerations

- Test auth requirements on protected routes
- Test input validation
- Test role-based access

## Next Steps

After testing complete:
1. Code review entire implementation
2. Deploy to staging
3. Manual QA testing
4. Production deployment

## Unresolved Questions

1. Performance benchmark for leaderboard with 10k+ users?
2. Need load testing for concurrent challenge submissions?
3. Cache strategy for frequently accessed OPRS data?
