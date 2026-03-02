<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubActivityParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubActivityRsvpTest extends TestCase
{
    use RefreshDatabase;

    private Club $club;
    private User $creator;
    private User $member;
    private ClubActivity $activity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creator = User::factory()->create();
        $this->club = Club::factory()->create(['user_id' => $this->creator->id]);
        $this->member = User::factory()->create();

        // Add creator as club member with 'creator' role
        $this->club->members()->attach($this->creator->id, ['role' => 'creator']);
        // Add member with 'member' role
        $this->club->members()->attach($this->member->id, ['role' => 'member']);

        $this->activity = ClubActivity::factory()->create([
            'club_id' => $this->club->id,
            'created_by' => $this->creator->id,
        ]);
    }

    public function test_member_can_rsvp_to_activity(): void
    {
        $response = $this->actingAs($this->member)
            ->postJson(route('clubs.activities.rsvp', [$this->club, $this->activity]));

        $response->assertOk()
            ->assertJson(['success' => true, 'status' => 'confirmed']);

        $this->assertDatabaseHas('club_activity_participants', [
            'club_activity_id' => $this->activity->id,
            'user_id' => $this->member->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_non_member_cannot_rsvp(): void
    {
        $outsider = User::factory()->create();

        $response = $this->actingAs($outsider)
            ->postJson(route('clubs.activities.rsvp', [$this->club, $this->activity]));

        $response->assertForbidden();
    }

    public function test_member_can_cancel_rsvp(): void
    {
        ClubActivityParticipant::factory()->create([
            'club_activity_id' => $this->activity->id,
            'user_id' => $this->member->id,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->member)
            ->deleteJson(route('clubs.activities.cancel-rsvp', [$this->club, $this->activity]));

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('club_activity_participants', [
            'club_activity_id' => $this->activity->id,
            'user_id' => $this->member->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_participants_endpoint_returns_correct_data(): void
    {
        ClubActivityParticipant::factory()->count(2)->create([
            'club_activity_id' => $this->activity->id,
            'status' => 'confirmed',
        ]);
        ClubActivityParticipant::factory()->waitlisted(1)->create([
            'club_activity_id' => $this->activity->id,
        ]);

        $response = $this->actingAs($this->member)
            ->getJson(route('clubs.activities.participants', [$this->club, $this->activity]));

        $response->assertOk()
            ->assertJsonCount(2, 'confirmed')
            ->assertJsonCount(1, 'waitlisted')
            ->assertJsonStructure(['confirmed', 'waitlisted', 'spots_left']);
    }
}
