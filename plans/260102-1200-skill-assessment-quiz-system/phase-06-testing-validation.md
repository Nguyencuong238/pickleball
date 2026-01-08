# Phase 6: Testing & Validation

**Date**: 2026-01-02
**Priority**: High
**Status**: COMPLETED
**Completed**: 2026-01-03
**Depends on**: Phase 1-5

## Context Links
- Reference: `tests/Feature/`
- Reference: `tests/Unit/`

## Overview

Create comprehensive tests for skill quiz system: unit tests for scoring logic, integration tests for API, and validation tests for anti-fraud measures.

## Requirements

### Test Coverage
1. Unit tests for SkillQuizService
2. Integration tests for API endpoints
3. Edge case testing
4. Anti-fraud validation tests
5. Database seeder tests

## Related Code Files

### Create
- `tests/Unit/Services/SkillQuizServiceTest.php`
- `tests/Feature/Api/SkillQuizApiTest.php`
- `tests/Feature/SkillQuizFlowTest.php`

## Implementation Steps

### Step 1: Unit Tests for SkillQuizService

```php
<?php

namespace Tests\Unit\Services;

use App\Models\SkillDomain;
use App\Models\SkillQuestion;
use App\Models\SkillQuizAnswer;
use App\Models\SkillQuizAttempt;
use App\Models\User;
use App\Services\OprsService;
use App\Services\SkillQuizService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillQuizServiceTest extends TestCase
{
    use RefreshDatabase;

    private SkillQuizService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SkillDomainSeeder::class);
        $this->seed(\Database\Seeders\SkillQuestionSeeder::class);

        $this->service = app(SkillQuizService::class);
        $this->user = User::factory()->create([
            'elo_rating' => 1000,
            'skill_quiz_count' => 0,
        ]);
    }

    /** @test */
    public function it_allows_first_time_quiz()
    {
        $result = $this->service->canTakeQuiz($this->user);

        $this->assertTrue($result['allowed']);
        $this->assertNull($result['reason']);
    }

    /** @test */
    public function it_enforces_cooldown_after_quiz()
    {
        // Create completed attempt
        SkillQuizAttempt::factory()->create([
            'user_id' => $this->user->id,
            'status' => SkillQuizAttempt::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
        $this->user->update(['skill_quiz_count' => 1]);

        $result = $this->service->canTakeQuiz($this->user);

        $this->assertFalse($result['allowed']);
        $this->assertEquals('cooldown', $result['reason']);
        $this->assertEquals(30, $result['days_remaining']);
    }

    /** @test */
    public function it_allows_quiz_for_calibrated_users()
    {
        $this->user->update([
            'total_ocr_matches' => 25,
            'skill_quiz_count' => 1,
        ]);

        SkillQuizAttempt::factory()->create([
            'user_id' => $this->user->id,
            'status' => SkillQuizAttempt::STATUS_COMPLETED,
            'completed_at' => now()->subDays(5),
        ]);

        $result = $this->service->canTakeQuiz($this->user);

        $this->assertTrue($result['allowed']);
        $this->assertEquals('calibrated', $result['reason']);
    }

    /** @test */
    public function it_calculates_domain_scores_correctly()
    {
        $attempt = SkillQuizAttempt::factory()->create([
            'user_id' => $this->user->id,
            'status' => SkillQuizAttempt::STATUS_IN_PROGRESS,
        ]);

        // Add answers for all questions (all value = 2)
        $questions = SkillQuestion::all();
        foreach ($questions as $question) {
            SkillQuizAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'answer_value' => 2,
                'answered_at' => now(),
            ]);
        }

        $scores = $this->service->calculateDomainScores($attempt);

        // All domains should be 66.67% (12/18 * 100)
        foreach ($scores as $score) {
            $this->assertEqualsWithDelta(66.67, $score, 0.1);
        }
    }

    /** @test */
    public function it_calculates_weighted_score_correctly()
    {
        $domainScores = [
            'rules' => 80,        // 10% weight = 8
            'consistency' => 60,   // 20% weight = 12
            'serve_return' => 70,  // 15% weight = 10.5
            'dink_net' => 50,      // 20% weight = 10
            'reset_defense' => 40, // 20% weight = 8
            'tactics' => 90,       // 15% weight = 13.5
        ];
        // Total = 62

        $result = $this->service->calculateWeightedScore($domainScores);

        $this->assertEqualsWithDelta(62.0, $result, 0.1);
    }

    /** @test */
    public function it_converts_quiz_to_elo_correctly()
    {
        // Test boundary values
        $this->assertEquals(850, $this->service->quizToElo(25));
        $this->assertEquals(1500, $this->service->quizToElo(95));

        // Test middle value
        $this->assertEqualsWithDelta(1175, $this->service->quizToElo(60), 5);

        // Test clamping
        $this->assertEquals(850, $this->service->quizToElo(10)); // Below min
        $this->assertEquals(1500, $this->service->quizToElo(100)); // Above max
    }

    /** @test */
    public function it_detects_cross_validation_issues()
    {
        // Rule 1: Tactics high + Consistency low
        $scores1 = [
            'rules' => 50,
            'consistency' => 40, // < 55
            'serve_return' => 50,
            'dink_net' => 50,
            'reset_defense' => 50,
            'tactics' => 80, // >= 70
        ];

        $flags = $this->service->validateCrossLogic($scores1);

        $this->assertCount(1, $flags);
        $this->assertEquals('INCONSISTENT', $flags[0]['type']);
        $this->assertEquals(-80, $flags[0]['adjustment']);
    }

    /** @test */
    public function it_validates_quiz_time()
    {
        // Too fast
        $result1 = $this->service->validateQuizTime(120); // 2 min
        $this->assertCount(1, $result1['flags']);
        $this->assertEquals('TOO_FAST', $result1['flags'][0]['type']);

        // Too slow
        $result2 = $this->service->validateQuizTime(1000); // 16+ min
        $this->assertCount(1, $result2['flags']);
        $this->assertEquals('TOO_SLOW', $result2['flags'][0]['type']);

        // Normal
        $result3 = $this->service->validateQuizTime(540); // 9 min
        $this->assertEmpty($result3['flags']);
    }

    /** @test */
    public function it_applies_elo_caps()
    {
        // Low consistency should cap at 1050
        $scores = [
            'rules' => 80,
            'consistency' => 40, // < 50
            'serve_return' => 80,
            'dink_net' => 80,
            'reset_defense' => 80,
            'tactics' => 80,
        ];

        $result = $this->service->applyEloCaps(1300, $scores);

        $this->assertEquals(1050, $result);
    }

    /** @test */
    public function it_limits_flag_adjustment()
    {
        $flags = [
            ['adjustment' => -80],
            ['adjustment' => -60],
            ['adjustment' => -50],
        ];
        // Total = -190, but should be capped at -150

        $result = $this->service->applyFlags(1200, $flags);

        $this->assertEquals(1050, $result); // 1200 - 150
    }

    /** @test */
    public function it_maps_elo_to_skill_level()
    {
        $this->assertEquals('2.0 - 2.5', $this->service->eloToSkillLevel(750));
        $this->assertEquals('2.5', $this->service->eloToSkillLevel(850));
        $this->assertEquals('3.2 - 3.5', $this->service->eloToSkillLevel(1050));
        $this->assertEquals('3.8 - 4.0', $this->service->eloToSkillLevel(1150));
        $this->assertEquals('5.8 - 6.0', $this->service->eloToSkillLevel(1550));
        $this->assertEquals('6.0+', $this->service->eloToSkillLevel(1650));
    }
}
```

### Step 2: Feature Tests for API

```php
<?php

namespace Tests\Feature\Api;

use App\Models\SkillQuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SkillQuizApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SkillDomainSeeder::class);
        $this->seed(\Database\Seeders\SkillQuestionSeeder::class);

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function user_can_check_eligibility()
    {
        $response = $this->getJson('/api/skill-quiz/eligibility');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'allowed' => true,
                    'quiz_count' => 0,
                ],
            ]);
    }

    /** @test */
    public function user_can_start_quiz()
    {
        $response = $this->postJson('/api/skill-quiz/start');

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'attempt_id',
                    'started_at',
                    'timeout_seconds',
                    'questions',
                    'total_questions',
                    'answer_scale',
                ],
            ]);

        $this->assertEquals(36, $response->json('data.total_questions'));
        $this->assertDatabaseHas('skill_quiz_attempts', [
            'user_id' => $this->user->id,
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function user_can_record_answer()
    {
        // Start quiz first
        $startResponse = $this->postJson('/api/skill-quiz/start');
        $attemptId = $startResponse->json('data.attempt_id');
        $questionId = $startResponse->json('data.questions.0.id');

        $response = $this->postJson('/api/skill-quiz/answer', [
            'attempt_id' => $attemptId,
            'question_id' => $questionId,
            'answer_value' => 2,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'question_id' => $questionId,
                    'answer_value' => 2,
                ],
            ]);

        $this->assertDatabaseHas('skill_quiz_answers', [
            'attempt_id' => $attemptId,
            'question_id' => $questionId,
            'answer_value' => 2,
        ]);
    }

    /** @test */
    public function user_cannot_submit_with_insufficient_answers()
    {
        $startResponse = $this->postJson('/api/skill-quiz/start');
        $attemptId = $startResponse->json('data.attempt_id');

        // Answer only 10 questions
        foreach (array_slice($startResponse->json('data.questions'), 0, 10) as $q) {
            $this->postJson('/api/skill-quiz/answer', [
                'attempt_id' => $attemptId,
                'question_id' => $q['id'],
                'answer_value' => 2,
            ]);
        }

        $response = $this->postJson('/api/skill-quiz/submit', [
            'attempt_id' => $attemptId,
        ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function user_can_submit_completed_quiz()
    {
        $startResponse = $this->postJson('/api/skill-quiz/start');
        $attemptId = $startResponse->json('data.attempt_id');
        $questions = $startResponse->json('data.questions');

        // Answer all 36 questions
        foreach ($questions as $q) {
            $this->postJson('/api/skill-quiz/answer', [
                'attempt_id' => $attemptId,
                'question_id' => $q['id'],
                'answer_value' => 2,
            ]);
        }

        // Wait a bit to avoid TOO_FAST flag
        sleep(1);

        $response = $this->postJson('/api/skill-quiz/submit', [
            'attempt_id' => $attemptId,
        ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'final_elo',
                    'quiz_percent',
                    'domain_scores',
                    'flags',
                    'skill_level',
                    'is_provisional',
                ],
            ]);

        // Check user ELO updated
        $this->user->refresh();
        $this->assertEquals($response->json('data.final_elo'), $this->user->elo_rating);
    }

    /** @test */
    public function user_can_get_result()
    {
        // Create completed attempt
        $attempt = SkillQuizAttempt::factory()->create([
            'user_id' => $this->user->id,
            'status' => SkillQuizAttempt::STATUS_COMPLETED,
            'completed_at' => now(),
            'domain_scores' => ['rules' => 70, 'consistency' => 60],
            'quiz_percent' => 65,
            'final_elo' => 1150,
        ]);

        $response = $this->getJson("/api/skill-quiz/result/{$attempt->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'attempt_id' => $attempt->id,
                    'final_elo' => 1150,
                ],
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access()
    {
        Sanctum::actingAs(null);

        $this->getJson('/api/skill-quiz/eligibility')
            ->assertUnauthorized();
    }

    /** @test */
    public function user_cannot_access_others_result()
    {
        $otherUser = User::factory()->create();
        $attempt = SkillQuizAttempt::factory()->create([
            'user_id' => $otherUser->id,
            'status' => SkillQuizAttempt::STATUS_COMPLETED,
        ]);

        $response = $this->getJson("/api/skill-quiz/result/{$attempt->id}");

        $response->assertNotFound();
    }
}
```

### Step 3: Flow Test

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SkillQuizService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SkillQuizFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function complete_quiz_flow_updates_user_correctly()
    {
        $this->seed(\Database\Seeders\SkillDomainSeeder::class);
        $this->seed(\Database\Seeders\SkillQuestionSeeder::class);

        $user = User::factory()->create([
            'elo_rating' => 1000,
            'total_oprs' => 700,
            'skill_quiz_count' => 0,
        ]);

        Sanctum::actingAs($user);

        // 1. Start quiz
        $startResponse = $this->postJson('/api/skill-quiz/start');
        $this->assertTrue($startResponse->json('success'));

        $attemptId = $startResponse->json('data.attempt_id');
        $questions = $startResponse->json('data.questions');

        // 2. Answer all questions with value 3 (max)
        foreach ($questions as $q) {
            $this->postJson('/api/skill-quiz/answer', [
                'attempt_id' => $attemptId,
                'question_id' => $q['id'],
                'answer_value' => 3,
            ]);
        }

        // 3. Submit
        $submitResponse = $this->postJson('/api/skill-quiz/submit', [
            'attempt_id' => $attemptId,
        ]);

        $this->assertTrue($submitResponse->json('success'));

        // 4. Verify results
        $finalElo = $submitResponse->json('data.final_elo');
        $quizPercent = $submitResponse->json('data.quiz_percent');

        // All 3s = 100%
        $this->assertEquals(100, $quizPercent);

        // ELO should be at or near max (1500)
        $this->assertGreaterThanOrEqual(1400, $finalElo);

        // 5. Check user updated
        $user->refresh();
        $this->assertEquals($finalElo, $user->elo_rating);
        $this->assertEquals(1, $user->skill_quiz_count);
        $this->assertTrue($user->elo_is_provisional);
        $this->assertNotNull($user->last_skill_quiz_at);

        // 6. Check OPRS updated
        $this->assertNotEquals(700, $user->total_oprs);
    }
}
```

## Test Data

### Factory for SkillQuizAttempt

```php
// database/factories/SkillQuizAttemptFactory.php
<?php

namespace Database\Factories;

use App\Models\SkillQuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SkillQuizAttemptFactory extends Factory
{
    protected $model = SkillQuizAttempt::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'user_id' => User::factory(),
            'started_at' => now()->subMinutes(10),
            'completed_at' => null,
            'duration_seconds' => null,
            'status' => SkillQuizAttempt::STATUS_IN_PROGRESS,
            'domain_scores' => null,
            'quiz_percent' => null,
            'calculated_elo' => null,
            'final_elo' => null,
            'flags' => [],
            'is_provisional' => true,
        ];
    }

    public function completed(): self
    {
        return $this->state(fn() => [
            'status' => SkillQuizAttempt::STATUS_COMPLETED,
            'completed_at' => now(),
            'duration_seconds' => 540,
            'domain_scores' => [
                'rules' => 70,
                'consistency' => 65,
                'serve_return' => 60,
                'dink_net' => 55,
                'reset_defense' => 50,
                'tactics' => 60,
            ],
            'quiz_percent' => 60,
            'calculated_elo' => 1120,
            'final_elo' => 1120,
        ]);
    }
}
```

## Todo List

- [x] Create SkillQuizServiceTest (20 tests)
- [x] Create SkillQuizWebTest (9 tests)
- [x] Create SkillQuizAttemptFactory
- [x] Run all tests (29 passed, 81 assertions)
- [x] Fix migration order issues
- [N/A] API tests require JWT configuration (deferred)

## Success Criteria

- [x] All unit tests pass (20/20)
- [x] All feature tests pass (9/9)
- [x] Service methods fully tested
- [x] Edge cases covered in tests
- [x] No regressions in existing tests

## Test Scenarios

| Scenario | Expected Result |
|----------|-----------------|
| First time user | Can take quiz |
| User in cooldown | Cannot take quiz |
| User with 20+ matches | Can take quiz anytime |
| All answers = 0 | ELO ~850, skill 2.0-2.5 |
| All answers = 3 | ELO ~1500, skill 6.0 |
| Inconsistent answers | Flags applied |
| Complete in < 3 min | TOO_FAST flag |
| Complete in > 15 min | TOO_SLOW flag |
| Low consistency | ELO capped at 1050 |

## Next Steps

After Phase 6:
- Deploy to staging
- User acceptance testing
- Production deployment
