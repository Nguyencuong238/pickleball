<?php

namespace App\Services;

use App\Models\ClubActivity;
use App\Models\ClubActivityParticipant;
use App\Models\GemTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class ClubActivityService
{
    /**
     * RSVP: xác nhận tham gia hoặc thêm vào danh sách chờ.
     * Thu Gems khi confirmed + activity có phí.
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
            $confirmedCount = $activity->confirmedParticipants()->lockForUpdate()->count();
            $isFull = $activity->max_participants && $confirmedCount >= $activity->max_participants;

            $status = $isFull ? 'waitlisted' : 'confirmed';
            $waitlistPos = $status === 'waitlisted'
                ? ($activity->waitlistedParticipants()->max('waitlist_position') ?? 0) + 1
                : null;

            $gemTxId = null;
            if ($status === 'confirmed' && $activity->hasFee()) {
                $gemTx = $this->chargeGems($activity, $user);
                $gemTxId = $gemTx->id;
            }

            $data = [
                'status' => $status,
                'waitlist_position' => $waitlistPos,
                'gem_transaction_id' => $gemTxId,
                'responded_at' => now(),
            ];

            if ($existing) {
                $existing->update($data);
                return $existing->fresh();
            }

            return $activity->participants()->create(array_merge(
                ['user_id' => $user->id],
                $data,
            ));
        });
    }

    /**
     * Check-in người chơi qua QR (open play).
     * Thu Gems khi user chưa RSVP trước + activity có phí.
     */
    public function checkinByPhone(ClubActivity $activity, User $user): ClubActivityParticipant
    {
        return DB::transaction(function () use ($activity, $user) {
            $club = $activity->club;
            if (!$club->members()->where('user_id', $user->id)->exists()) {
                $club->members()->attach($user->id, [
                    'role' => 'member',
                    'joined_at' => now(),
                ]);
            }

            $existing = $activity->participants()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->checked_in_at) {
                return $existing;
            }

            $nextPos = ($activity->participants()->lockForUpdate()->max('queue_position') ?? 0) + 1;

            // User đã RSVP trước (đã thu phí) -> chỉ check-in
            if ($existing) {
                $existing->update([
                    'checked_in_at' => now(),
                    'current_status' => 'queued',
                    'queue_position' => $nextPos,
                ]);
                return $existing->fresh();
            }

            // User mới (chưa RSVP) -> thu Gems nếu activity có phí
            $gemTxId = null;
            if ($activity->hasFee()) {
                $gemTx = $this->chargeGems($activity, $user);
                $gemTxId = $gemTx->id;
            }

            return $activity->participants()->create([
                'user_id' => $user->id,
                'status' => 'confirmed',
                'gem_transaction_id' => $gemTxId,
                'responded_at' => now(),
                'checked_in_at' => now(),
                'current_status' => 'queued',
                'queue_position' => $nextPos,
            ]);
        });
    }

    /**
     * Hủy đăng ký. Hoàn Gems nếu confirmed + chưa bắt đầu.
     * Return số Gems đã hoàn.
     */
    public function cancelRsvp(ClubActivity $activity, User $user): array
    {
        return DB::transaction(function () use ($activity, $user) {
            $participant = $activity->participants()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $wasConfirmed = $participant->status === 'confirmed';
            $gemsRefunded = 0;

            // Hoàn Gems nếu confirmed + activity có phí + chưa bắt đầu
            if ($wasConfirmed && $activity->hasFee() && $activity->activity_date > now()) {
                $gemsRefunded = $this->refundGems($participant);
            }

            $participant->update(['status' => 'cancelled', 'waitlist_position' => null]);

            if ($wasConfirmed) {
                $this->promoteFromWaitlist($activity);
            }

            return ['gems_refunded' => $gemsRefunded];
        });
    }

    /**
     * Nâng hạng người chờ. Loop qua waitlist, skip nếu không đủ Gems.
     */
    public function promoteFromWaitlist(ClubActivity $activity): void
    {
        if ($activity->max_participants) {
            $confirmedCount = $activity->confirmedParticipants()->lockForUpdate()->count();
            if ($confirmedCount >= $activity->max_participants) {
                return;
            }
        }

        $waitlisted = $activity->waitlistedParticipants()->lockForUpdate()->get();

        foreach ($waitlisted as $next) {
            if (!$activity->hasFee()) {
                $next->update(['status' => 'confirmed', 'waitlist_position' => null]);
                return;
            }

            try {
                $gemTx = $this->chargeGems($activity, $next->user);
                $next->update([
                    'status' => 'confirmed',
                    'waitlist_position' => null,
                    'gem_transaction_id' => $gemTx->id,
                ]);
                return;
            } catch (RuntimeException) {
                // Không đủ Gems -> cancel và thử người tiếp
                $next->update(['status' => 'cancelled', 'waitlist_position' => null]);
            }
        }
    }

    /**
     * Tạo buổi chơi tự động từ recurring template
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
            'fee_gems' => $template->fee_gems,
            'recurrence_day' => $template->recurrence_day,
            'auto_approve' => $template->auto_approve,
            'min_skill_level' => $template->min_skill_level,
            'max_skill_level' => $template->max_skill_level,
            'status' => 'upcoming',
        ]);
    }

    /**
     * Thu Gems từ user cho activity.
     * - Flag ON: dùng GemPaymentProcessor (transfer → owner CLB).
     * - Flag OFF: fallback sang mô hình đốt cũ (deduct).
     *
     * @throws RuntimeException khi không đủ Gems
     */
    private function chargeGems(ClubActivity $activity, User $user): GemTransaction
    {
        if (config('gems.transfer_enabled')) {
            [$debitTx, ] = app(GemPaymentProcessor::class)->pay($activity, $user);
            return $debitTx;
        }
        return $this->legacyChargeGems($activity, $user);
    }

    private function legacyChargeGems(ClubActivity $activity, User $user): GemTransaction
    {
        $walletService = app(GemWalletService::class);
        $gemTx = $walletService->deduct(
            $user,
            $activity->fee_gems,
            ClubActivity::class,
            $activity->id,
            "Phí tham gia: {$activity->title}"
        );

        app(GemCashbackService::class)->award($gemTx);

        return $gemTx;
    }

    /**
     * Hoàn Gems cho participant. Return số Gems đã hoàn.
     * Hỗ trợ cả luồng mới (clawback) lẫn legacy (credit-only).
     */
    private function refundGems(ClubActivityParticipant $participant): int
    {
        if (!$participant->gem_transaction_id) {
            return 0;
        }

        $originalTx = GemTransaction::find($participant->gem_transaction_id);
        if (!$originalTx || $originalTx->status !== GemTransaction::STATUS_COMPLETED || $originalTx->type !== GemTransaction::TYPE_PAYMENT) {
            return 0;
        }

        $walletService = app(GemWalletService::class);
        [$refundTx, ] = $walletService->refund($originalTx);

        return (int) $refundTx->amount;
    }
}
