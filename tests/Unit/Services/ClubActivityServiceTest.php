<?php

namespace Tests\Unit\Services;

use App\Models\Club;
use App\Models\ClubActivity;
use App\Models\ClubActivityParticipant;
use App\Models\User;
use App\Services\ClubActivityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ClubActivityServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClubActivityService $service;
    private Club $club;
    private User $creator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ClubActivityService();
        $this->creator = User::factory()->create();
        $this->club = Club::factory()->create(['user_id' => $this->creator->id]);
    }

    public function test_rsvp_confirms_when_spots_available(): void
    {
        $activity = ClubActivity::factory()->create([
            'club_id' => $this->club->id,
            'created_by' => $this->creator->id,
            'max_participants' => 10,
        ]);
        $user = User::factory()->create();

        $participant = $this->service->rsvp($activity, $user);

        $this->assertEquals('confirmed', $participant->status);
        $this->assertNull($participant->waitlist_position);
    }

    public function test_rsvp_waitlists_when_full(): void
    {
        $activity = ClubActivity::factory()->full()->create([
            'club_id' => $this->club->id,
            'created_by' => $this->creator->id,
        ]);

        // Fill up spots (max_participants = 2)
        ClubActivityParticipant::factory()->count(2)->create([
            'club_activity_id' => $activity->id,
            'status' => 'confirmed',
        ]);

        $user = User::factory()->create();
        $participant = $this->service->rsvp($activity, $user);

        $this->assertEquals('waitlisted', $participant->status);
        $this->assertEquals(1, $participant->waitlist_position);
    }

    public function test_cancel_rsvp_promotes_waitlisted(): void
    {
        $activity = ClubActivity::factory()->full()->create([
            'club_id' => $this->club->id,
            'created_by' => $this->creator->id,
        ]);

        $confirmedUser = User::factory()->create();
        $waitlistedUser = User::factory()->create();

        ClubActivityParticipant::factory()->create([
            'club_activity_id' => $activity->id,
            'user_id' => $confirmedUser->id,
            'status' => 'confirmed',
        ]);
        ClubActivityParticipant::factory()->create([
            'club_activity_id' => $activity->id,
            'user_id' => User::factory()->create()->id,
            'status' => 'confirmed',
        ]);
        ClubActivityParticipant::factory()->waitlisted(1)->create([
            'club_activity_id' => $activity->id,
            'user_id' => $waitlistedUser->id,
        ]);

        $this->service->cancelRsvp($activity, $confirmedUser);

        $promoted = $activity->participants()->where('user_id', $waitlistedUser->id)->first();
        $this->assertEquals('confirmed', $promoted->status);
        $this->assertNull($promoted->waitlist_position);
    }

    public function test_rsvp_rejects_wrong_skill_level(): void
    {
        $activity = ClubActivity::factory()->withSkillRange(3.0, 5.0)->create([
            'club_id' => $this->club->id,
            'created_by' => $this->creator->id,
        ]);

        $lowSkillUser = User::factory()->create(['opr_level' => 2.0]);

        $this->expectException(InvalidArgumentException::class);
        $this->service->rsvp($activity, $lowSkillUser);
    }

    public function test_rsvp_prevents_duplicate(): void
    {
        $activity = ClubActivity::factory()->create([
            'club_id' => $this->club->id,
            'created_by' => $this->creator->id,
        ]);
        $user = User::factory()->create();

        $this->service->rsvp($activity, $user);

        $this->expectException(InvalidArgumentException::class);
        $this->service->rsvp($activity, $user);
    }

    public function test_create_recurring_instance(): void
    {
        $template = ClubActivity::factory()->recurring(Carbon::MONDAY)->create([
            'club_id' => $this->club->id,
            'created_by' => $this->creator->id,
            'title' => 'Buoi tap hang tuan',
            'activity_date' => Carbon::parse('next monday 18:00'),
        ]);

        $targetDate = Carbon::parse('next monday');
        $instance = $this->service->createRecurringInstance($template, $targetDate);

        $this->assertEquals($template->title, $instance->title);
        $this->assertEquals($template->id, $instance->parent_activity_id);
        $this->assertEquals('recurring', $instance->type);
        $this->assertEquals('upcoming', $instance->status);
        $this->assertEquals($targetDate->format('Y-m-d'), $instance->activity_date->format('Y-m-d'));
    }
}
