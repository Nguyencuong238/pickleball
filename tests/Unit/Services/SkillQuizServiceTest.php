<?php

namespace Tests\Unit\Services;

use App\Models\SkillDomain;
use App\Models\SkillQuestion;
use App\Models\SkillQuizAnswer;
use App\Models\SkillQuizAttempt;
use App\Models\User;
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
    public function it_allows_first_time_quiz(): void
    {
        $result = $this->service->canTakeQuiz($this->user);

        $this->assertTrue($result['allowed']);
        $this->assertNull($result['reason']);
    }

    /** @test */
    public function it_enforces_cooldown_after_quiz(): void
    {
        // Create completed attempt
        SkillQuizAttempt::factory()->completed()->create([
            'user_id' => $this->user->id,
            'completed_at' => now(),
        ]);
        $this->user->update(['skill_quiz_count' => 1]);

        $result = $this->service->canTakeQuiz($this->user);

        $this->assertFalse($result['allowed']);
        $this->assertEquals('cooldown', $result['reason']);
    }

    /** @test */
    public function it_allows_quiz_for_calibrated_users(): void
    {
        $this->user->update([
            'total_ocr_matches' => 25,
            'skill_quiz_count' => 1,
        ]);

        SkillQuizAttempt::factory()->completed()->create([
            'user_id' => $this->user->id,
            'completed_at' => now()->subDays(5),
        ]);

        $result = $this->service->canTakeQuiz($this->user);

        $this->assertTrue($result['allowed']);
        $this->assertEquals('calibrated', $result['reason']);
    }

    /** @test */
    public function it_calculates_domain_scores_correctly(): void
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
    public function it_calculates_weighted_score_correctly(): void
    {
        // Based on weights from seeder:
        // rules: 0.10, consistency: 0.20, serve_return: 0.15
        // dink_net: 0.20, reset_defense: 0.20, tactics: 0.15
        $domainScores = [
            'rules' => 80,        // 10% weight = 8
            'consistency' => 60,  // 20% weight = 12
            'serve_return' => 70, // 15% weight = 10.5
            'dink_net' => 50,     // 20% weight = 10
            'reset_defense' => 40,// 20% weight = 8
            'tactics' => 90,      // 15% weight = 13.5
        ];
        // Total = 62

        $result = $this->service->calculateWeightedScore($domainScores);

        $this->assertEqualsWithDelta(62.0, $result, 0.1);
    }

    /** @test */
    public function it_converts_quiz_to_elo_correctly(): void
    {
        // Test boundary values
        $this->assertEquals(850, $this->service->quizToElo(25));
        $this->assertEquals(1500, $this->service->quizToElo(95));

        // Test middle value (60% -> ~1175)
        $elo60 = $this->service->quizToElo(60);
        $this->assertEqualsWithDelta(1175, $elo60, 10);

        // Test clamping
        $this->assertEquals(850, $this->service->quizToElo(10)); // Below min
        $this->assertEquals(1500, $this->service->quizToElo(100)); // Above max
    }

    /** @test */
    public function it_detects_cross_validation_issues(): void
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
    public function it_validates_quiz_time(): void
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
    public function it_applies_elo_caps(): void
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
    public function it_limits_flag_adjustment(): void
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
    public function it_maps_elo_to_skill_level(): void
    {
        $this->assertEquals('2.0 - 2.5', $this->service->eloToSkillLevel(750));
        $this->assertEquals('2.5', $this->service->eloToSkillLevel(850));
        $this->assertEquals('3.2 - 3.5', $this->service->eloToSkillLevel(1050));
        $this->assertEquals('3.8 - 4.0', $this->service->eloToSkillLevel(1150));
        $this->assertEquals('5.8 - 6.0', $this->service->eloToSkillLevel(1550));
        $this->assertEquals('6.0+', $this->service->eloToSkillLevel(1650));
    }

    /** @test */
    public function it_starts_new_quiz_attempt(): void
    {
        $attempt = $this->service->startQuiz($this->user);

        $this->assertInstanceOf(SkillQuizAttempt::class, $attempt);
        $this->assertEquals($this->user->id, $attempt->user_id);
        $this->assertEquals(SkillQuizAttempt::STATUS_IN_PROGRESS, $attempt->status);
        $this->assertNotNull($attempt->started_at);
    }

    /** @test */
    public function it_returns_existing_in_progress_attempt(): void
    {
        $existingAttempt = SkillQuizAttempt::factory()->create([
            'user_id' => $this->user->id,
            'status' => SkillQuizAttempt::STATUS_IN_PROGRESS,
            'started_at' => now()->subMinutes(5),
        ]);

        $attempt = $this->service->startQuiz($this->user);

        $this->assertEquals($existingAttempt->id, $attempt->id);
    }

    /** @test */
    public function it_records_answer_correctly(): void
    {
        $attempt = $this->service->startQuiz($this->user);
        $question = SkillQuestion::first();

        $answer = $this->service->recordAnswer($attempt, $question->id, 2, 15);

        $this->assertDatabaseHas('skill_quiz_answers', [
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'answer_value' => 2,
            'time_spent_seconds' => 15,
        ]);
    }

    /** @test */
    public function it_throws_on_invalid_answer_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $attempt = $this->service->startQuiz($this->user);
        $question = SkillQuestion::first();

        $this->service->recordAnswer($attempt, $question->id, 5); // Invalid
    }

    /** @test */
    public function it_throws_on_completed_quiz_answer(): void
    {
        $this->expectException(\RuntimeException::class);

        $attempt = SkillQuizAttempt::factory()->completed()->create([
            'user_id' => $this->user->id,
        ]);
        $question = SkillQuestion::first();

        $this->service->recordAnswer($attempt, $question->id, 2);
    }

    /** @test */
    public function it_gets_questions(): void
    {
        $questions = $this->service->getQuestions();

        $this->assertCount(36, $questions);

        // Check structure
        $first = $questions[0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('domain_key', $first);
        $this->assertArrayHasKey('question', $first);
    }

    /** @test */
    public function it_gets_questions_grouped_by_domain(): void
    {
        $grouped = $this->service->getQuestionsGroupedByDomain();

        $this->assertCount(6, $grouped);
        $this->assertArrayHasKey('rules', $grouped);
        $this->assertArrayHasKey('consistency', $grouped);

        // Each domain has 6 questions
        foreach ($grouped as $domain) {
            $this->assertCount(6, $domain['questions']);
        }
    }

    /** @test */
    public function it_provides_recommendations_for_weak_domains(): void
    {
        $scores = [
            'rules' => 30,        // Very weak
            'consistency' => 45,  // Weak
            'serve_return' => 60, // Medium
            'dink_net' => 80,     // Strong
            'reset_defense' => 70,// Good
            'tactics' => 55,      // Medium-weak
        ];

        $recommendations = $this->service->getRecommendations($scores);

        // Should get recommendations for weakest domains (rules, consistency, tactics)
        $this->assertLessThanOrEqual(3, count($recommendations));

        // First should be the weakest
        $this->assertEquals('rules', $recommendations[0]['domain_key']);
        $this->assertEquals('high', $recommendations[0]['priority']);
    }

    /** @test */
    public function it_gets_attempt_progress(): void
    {
        $attempt = $this->service->startQuiz($this->user);

        // Answer 5 questions
        $questions = SkillQuestion::take(5)->get();
        foreach ($questions as $q) {
            $this->service->recordAnswer($attempt, $q->id, 2);
        }

        $progress = $this->service->getAttemptProgress($attempt);

        $this->assertEquals(36, $progress['total_questions']);
        $this->assertEquals(5, $progress['answered_questions']);
        $this->assertEqualsWithDelta(13.9, $progress['progress_percent'], 0.5);
    }
}
