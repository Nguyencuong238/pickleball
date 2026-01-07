<?php

namespace App\Services;

use App\Models\OprVerificationRequest;
use App\Models\OprsHistory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class OprVerificationService
{
    /**
     * Create a new verification request
     *
     * @param User $user The user requesting verification
     * @param array{media?: array, links?: array, notes?: string} $data Request data
     * @return OprVerificationRequest
     * @throws InvalidArgumentException If user cannot request verification
     */
    public function createRequest(User $user, array $data): OprVerificationRequest
    {
        if (!$user->canRequestVerification()) {
            throw new InvalidArgumentException('Bạn không thể yêu cầu xác minh lúc này');
        }

        return OprVerificationRequest::create([
            'user_id' => $user->id,
            'status' => OprVerificationRequest::STATUS_PENDING,
            'media_paths' => $data['media_paths'] ?? [],
            'links' => $data['links'] ?? [],
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Approve a verification request
     *
     * @param OprVerificationRequest $request The request to approve
     * @param User $verifier The user approving the request
     * @param string|null $notes Optional notes from verifier
     * @throws InvalidArgumentException If verifier lacks permission
     * @throws RuntimeException If request is not pending
     */
    public function approve(
        OprVerificationRequest $request,
        User $verifier,
        ?string $notes = null
    ): void {
        if (!$verifier->canVerifyElo()) {
            throw new InvalidArgumentException('Bạn không có quyền xác minh');
        }

        if (!$request->isPending()) {
            throw new RuntimeException('Yêu cầu này không ở trạng thái chờ duyệt');
        }

        DB::transaction(function () use ($request, $verifier, $notes) {
            // Update request status
            $request->update([
                'status' => OprVerificationRequest::STATUS_APPROVED,
                'verifier_id' => $verifier->id,
                'verifier_notes' => $notes,
                'verified_at' => now(),
            ]);

            // Update user's verification status
            $user = $request->user;
            $user->update([
                'is_elo_verified' => true,
                'elo_is_provisional' => false,
            ]);

            // Record in OPRS history
            OprsHistory::create([
                'user_id' => $user->id,
                'elo_score' => $user->elo_rating,
                'challenge_score' => $user->challenge_score,
                'community_score' => $user->community_score,
                'total_oprs' => $user->total_oprs,
                'opr_level' => $user->opr_level,
                'change_reason' => OprsHistory::REASON_ELO_VERIFIED,
                'metadata' => [
                    'verification_request_id' => $request->id,
                    'verifier_id' => $verifier->id,
                    'verifier_name' => $verifier->name,
                ],
            ]);
        });
    }

    /**
     * Reject a verification request
     *
     * @param OprVerificationRequest $request The request to reject
     * @param User $verifier The user rejecting the request
     * @param string $notes Rejection reason (required)
     * @throws InvalidArgumentException If verifier lacks permission or notes empty
     * @throws RuntimeException If request is not pending
     */
    public function reject(
        OprVerificationRequest $request,
        User $verifier,
        string $notes
    ): void {
        if (!$verifier->canVerifyElo()) {
            throw new InvalidArgumentException('Bạn không có quyền xác minh');
        }

        if (!$request->isPending()) {
            throw new RuntimeException('Yêu cầu này không ở trạng thái chờ duyệt');
        }

        if (empty(trim($notes))) {
            throw new InvalidArgumentException('Vui lòng nhập lý do từ chối');
        }

        $request->update([
            'status' => OprVerificationRequest::STATUS_REJECTED,
            'verifier_id' => $verifier->id,
            'verifier_notes' => $notes,
            'verified_at' => now(),
        ]);
    }

    /**
     * Get pending verification requests
     *
     * @param int $limit Number of records per page
     * @param int $offset Number of records to skip
     * @return Collection<int, OprVerificationRequest>
     */
    public function getPendingRequests(int $limit = 20, int $offset = 0): Collection
    {
        return OprVerificationRequest::pending()
            ->with(['user'])
            ->orderBy('created_at', 'asc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get paginated verification requests with optional filters
     *
     * @param string|null $status Filter by status
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function getRequests(?string $status = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = OprVerificationRequest::with(['user', 'verifier'])
            ->orderBy('created_at', 'desc');

        if ($status && in_array($status, [
            OprVerificationRequest::STATUS_PENDING,
            OprVerificationRequest::STATUS_APPROVED,
            OprVerificationRequest::STATUS_REJECTED,
        ])) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get verification statistics
     *
     * @return array{pending: int, approved: int, rejected: int, total: int}
     */
    public function getStats(): array
    {
        return [
            'pending' => OprVerificationRequest::where('status', OprVerificationRequest::STATUS_PENDING)->count(),
            'approved' => OprVerificationRequest::where('status', OprVerificationRequest::STATUS_APPROVED)->count(),
            'rejected' => OprVerificationRequest::where('status', OprVerificationRequest::STATUS_REJECTED)->count(),
            'total' => OprVerificationRequest::count(),
        ];
    }

    /**
     * Get a single verification request by ID
     *
     * @param string $id UUID of the request
     * @return OprVerificationRequest|null
     */
    public function getRequest(string $id): ?OprVerificationRequest
    {
        return OprVerificationRequest::with(['user', 'verifier', 'media'])
            ->find($id);
    }

    /**
     * Get verification requests for a specific user
     *
     * @param User $user
     * @return Collection<int, OprVerificationRequest>
     */
    public function getUserRequests(User $user): Collection
    {
        return OprVerificationRequest::forUser($user->id)
            ->with(['verifier'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Check if user has recently been rejected and needs cooldown
     *
     * @param User $user
     * @param int $cooldownDays
     * @return bool
     */
    public function isInCooldownPeriod(User $user, int $cooldownDays = 7): bool
    {
        $lastRejected = OprVerificationRequest::forUser($user->id)
            ->where('status', OprVerificationRequest::STATUS_REJECTED)
            ->latest()
            ->first();

        if (!$lastRejected) {
            return false;
        }

        return $lastRejected->verified_at->addDays($cooldownDays)->isFuture();
    }
}
