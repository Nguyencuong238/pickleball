<?php

namespace Tests\Feature;

use App\Models\SkillQuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for web-based skill quiz routes
 */
class SkillQuizWebTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SkillDomainSeeder::class);
        $this->seed(\Database\Seeders\SkillQuestionSeeder::class);

        $this->user = User::factory()->create();
    }

    /** @test */
    public function guest_can_view_skill_quiz_index(): void
    {
        // Index page is public (preview for guests)
        $response = $this->get('/skill-quiz');
        $response->assertOk();
    }

    /** @test */
    public function guest_cannot_start_skill_quiz(): void
    {
        // Start page requires auth
        $response = $this->get('/skill-quiz/start');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_view_skill_quiz_index(): void
    {
        $response = $this->actingAs($this->user)->get('/skill-quiz');
        $response->assertOk();
    }

    /** @test */
    public function authenticated_user_can_view_start_page(): void
    {
        $response = $this->actingAs($this->user)->get('/skill-quiz/start');
        $response->assertOk();
    }

    /** @test */
    public function authenticated_user_can_view_quiz_page(): void
    {
        $response = $this->actingAs($this->user)->get('/skill-quiz/quiz');
        $response->assertOk();
    }

    /** @test */
    public function user_can_submit_answer_via_web_route(): void
    {
        // Create an in-progress attempt
        $attempt = SkillQuizAttempt::factory()->create([
            'user_id' => $this->user->id,
            'status' => SkillQuizAttempt::STATUS_IN_PROGRESS,
        ]);

        $question = \App\Models\SkillQuestion::first();

        $response = $this->actingAs($this->user)->postJson('/api/skill-quiz/answer', [
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'answer_value' => 2,
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('skill_quiz_answers', [
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'answer_value' => 2,
        ]);
    }

    /** @test */
    public function user_can_submit_quiz_via_web_route(): void
    {
        // Create an in-progress attempt with all answers
        $attempt = SkillQuizAttempt::factory()->create([
            'user_id' => $this->user->id,
            'status' => SkillQuizAttempt::STATUS_IN_PROGRESS,
        ]);

        // Add all 36 answers
        $questions = \App\Models\SkillQuestion::all();
        foreach ($questions as $question) {
            \App\Models\SkillQuizAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'answer_value' => 2,
                'answered_at' => now(),
            ]);
        }

        $response = $this->actingAs($this->user)->postJson('/api/skill-quiz/submit', [
            'attempt_id' => $attempt->id,
        ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'final_elo',
                    'quiz_percent',
                    'skill_level',
                ],
            ]);

        // Verify user was updated
        $this->user->refresh();
        $this->assertNotNull($this->user->elo_rating);
        $this->assertEquals(1, $this->user->skill_quiz_count);
    }

    /** @test */
    public function user_cannot_submit_incomplete_quiz(): void
    {
        $attempt = SkillQuizAttempt::factory()->create([
            'user_id' => $this->user->id,
            'status' => SkillQuizAttempt::STATUS_IN_PROGRESS,
        ]);

        // Only add 10 answers
        $questions = \App\Models\SkillQuestion::take(10)->get();
        foreach ($questions as $question) {
            \App\Models\SkillQuizAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'answer_value' => 2,
                'answered_at' => now(),
            ]);
        }

        $response = $this->actingAs($this->user)->postJson('/api/skill-quiz/submit', [
            'attempt_id' => $attempt->id,
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function user_can_view_result_page(): void
    {
        $attempt = SkillQuizAttempt::factory()->completed()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/skill-quiz/result/{$attempt->id}");
        $response->assertOk();
    }

    /** @test */
    public function user_cannot_view_others_result(): void
    {
        $otherUser = User::factory()->create();
        $attempt = SkillQuizAttempt::factory()->completed()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user)->get("/skill-quiz/result/{$attempt->id}");
        // Should redirect or show 404
        $this->assertTrue($response->status() === 302 || $response->status() === 404);
    }
}
