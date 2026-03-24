<?php

namespace App\Services;

use App\Models\ClubActivity;
use App\Models\ClubActivityParticipant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ClubActivityService
{
    /**
     * RSVP: xac nhan tham gia hoac them vao danh sach cho
     */
    public function rsvp(ClubActivity $activity, User $user): ClubActivityParticipant
    {
        if (!$activity->userCanJoin($user)) {
            throw new InvalidArgumentException('Trình độ không phù hợp với yêu cầu.');
        }

        $existing = $activity->participants()->where('user_id', $user->id)->first();
        if ($existing && $existing->status !== 'cancelled') {
            throw new InvalidArgumentException('Bạn đã đăng ký tham gia.');
        }

        return DB::transaction(function () use ($activity, $user, $existing) {
            // Lock to prevent race condition on spot count
            $confirmedCount = $activity->confirmedParticipants()->lockForUpdate()->count();
            $isFull = $activity->max_participants && $confirmedCount >= $activity->max_participants;

            $status = $isFull ? 'waitlisted' : 'confirmed';
            $waitlistPos = $status === 'waitlisted'
                ? ($activity->waitlistedParticipants()->max('waitlist_position') ?? 0) + 1
                : null;

            if ($existing) {
                $existing->update([
                    'status' => $status,
                    'waitlist_position' => $waitlistPos,
                    'responded_at' => now(),
                ]);
                return $existing->fresh();
            }

            return $activity->participants()->create([
                'user_id' => $user->id,
                'status' => $status,
                'waitlist_position' => $waitlistPos,
                'responded_at' => now(),
            ]);
        });
    }

    /**
     * Check-in nguoi choi qua QR (open play)
     */
    public function checkinByPhone(ClubActivity $activity, User $user): ClubActivityParticipant
    {
        return DB::transaction(function () use ($activity, $user) {
            // Auto-add to club if not member
            $club = $activity->club;
            if (!$club->members()->where('user_id', $user->id)->exists()) {
                $club->members()->attach($user->id, [
                    'role' => 'member',
                    'joined_at' => now(),
                ]);
            }

            // Check if already participant
            $existing = $activity->participants()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->checked_in_at) {
                // Already checked in, just return
                return $existing;
            }

            $nextPos = ($activity->participants()->lockForUpdate()->max('queue_position') ?? 0) + 1;

            if ($existing) {
                $existing->update([
                    'checked_in_at' => now(),
                    'current_status' => 'queued',
                    'queue_position' => $nextPos,
                ]);
                return $existing->fresh();
            }

            return $activity->participants()->create([
                'user_id' => $user->id,
                'status' => 'confirmed',
                'responded_at' => now(),
                'checked_in_at' => now(),
                'current_status' => 'queued',
                'queue_position' => $nextPos,
            ]);
        });
    }

    /**
     * Huy dang ky va tu dong nang hang nguoi cho
     */
    public function cancelRsvp(ClubActivity $activity, User $user): void
    {
        DB::transaction(function () use ($activity, $user) {
            $participant = $activity->participants()
                ->where('user_id', $user->id)
                ->firstOrFail();

            $wasConfirmed = $participant->status === 'confirmed';
            $participant->update(['status' => 'cancelled', 'waitlist_position' => null]);

            if ($wasConfirmed) {
                $this->promoteFromWaitlist($activity);
            }
        });
    }

    /**
     * Nang hang nguoi dau tien trong danh sach cho
     */
    public function promoteFromWaitlist(ClubActivity $activity): void
    {
        // Re-check capacity with lock to prevent race conditions
        if ($activity->max_participants) {
            $confirmedCount = $activity->confirmedParticipants()->lockForUpdate()->count();
            if ($confirmedCount >= $activity->max_participants) {
                return;
            }
        }

        $next = $activity->waitlistedParticipants()->lockForUpdate()->first();
        if ($next) {
            $next->update(['status' => 'confirmed', 'waitlist_position' => null]);
        }
    }

    /**
     * Tao buoi choi tu dong tu recurring template
     */
    public function createRecurringInstance(ClubActivity $template, Carbon $date): ClubActivity
    {
        return $template->club->activities()->create([
            'type' => 'recurring',
            'created_by' => $template->created_by,
            'title' => $template->title,
            'description' => $template->description,
            'activity_date' => $date->copy()->setTimeFromTimeString(
                $template->activity_date->format('H:i:s')
            ),
            'end_time' => $template->end_time,
            'location' => $template->location,
            'max_participants' => $template->max_participants,
            'parent_activity_id' => $template->id,
            'recurrence_day' => $template->recurrence_day,
            'auto_approve' => $template->auto_approve,
            'min_skill_level' => $template->min_skill_level,
            'max_skill_level' => $template->max_skill_level,
            'status' => 'upcoming',
        ]);
    }
}
