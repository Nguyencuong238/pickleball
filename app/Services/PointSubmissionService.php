<?php

namespace App\Services;

use App\Models\PointSubmission;
use App\Models\PointTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class PointSubmissionService
{
    public function __construct(
        private PointEarningService $pointEarningService,
        private SocialVerificationService $socialVerificationService
    ) {}

    /**
     * Submit proof for approval
     *
     * @param array<string, mixed> $proofData
     * @throws InvalidArgumentException
     */
    public function submit(User $user, string $taskCode, array $proofData): PointSubmission
    {
        $task = PointTask::findByCode($taskCode);

        if (!$task || !$task->is_active) {
            throw new InvalidArgumentException("Task not found or inactive: {$taskCode}");
        }

        if (!$task->requires_approval) {
            throw new InvalidArgumentException("Task does not require approval: {$taskCode}");
        }

        // Validate proof type
        $this->validateProofData($task, $proofData);

        // Check for existing pending submission
        $existingPending = PointSubmission::where('user_id', $user->id)
            ->where('point_task_id', $task->id)
            ->where('status', PointSubmission::STATUS_PENDING)
            ->exists();

        if ($existingPending) {
            throw new InvalidArgumentException('Bạn đã có một yêu cầu đang chờ xử lý cho nhiệm vụ này');
        }

        // Check frequency (for once-only tasks)
        if ($task->frequency === PointTask::FREQ_ONCE) {
            $alreadyApproved = PointSubmission::where('user_id', $user->id)
                ->where('point_task_id', $task->id)
                ->where('status', PointSubmission::STATUS_APPROVED)
                ->exists();

            if ($alreadyApproved) {
                throw new InvalidArgumentException('Bạn đã hoàn thành nhiệm vụ này');
            }
        }

        return PointSubmission::create([
            'user_id' => $user->id,
            'point_task_id' => $task->id,
            'status' => PointSubmission::STATUS_PENDING,
            'proof_data' => $proofData,
        ]);
    }

    /**
     * Approve submission and award points
     *
     * @throws InvalidArgumentException
     */
    public function approve(PointSubmission $submission, User $admin, ?string $notes = null): void
    {
        if (!$submission->isPending()) {
            throw new InvalidArgumentException('Yêu cầu không ở trạng thái chờ duyệt');
        }

        DB::transaction(function () use ($submission, $admin, $notes) {
            $task = $submission->pointTask;
            $user = $submission->user;

            // Update submission
            $submission->update([
                'status' => PointSubmission::STATUS_APPROVED,
                'admin_id' => $admin->id,
                'admin_notes' => $notes,
                'reviewed_at' => Carbon::now(),
                'points_awarded' => $task->points,
            ]);

            // Award points via wallet
            $wallet = $user->wallet ?? $user->wallet()->create(['points' => 0]);
            $wallet->addPoints(
                $task->points,
                'earn',
                $task->name,
                [
                    'task_code' => $task->code,
                    'task_id' => $task->id,
                    'submission_id' => $submission->id,
                    'approved_by' => $admin->id,
                ]
            );

            // Handle social profile verification
            if ($this->isSocialTask($task->code)) {
                $this->createSocialVerification($submission, $admin);
            }
        });
    }

    /**
     * Reject submission
     *
     * @throws InvalidArgumentException
     */
    public function reject(PointSubmission $submission, User $admin, string $reason): void
    {
        if (!$submission->isPending()) {
            throw new InvalidArgumentException('Yêu cầu không ở trạng thái chờ duyệt');
        }

        if (empty($reason)) {
            throw new InvalidArgumentException('Cần phải nhập lý do từ chối');
        }

        $submission->update([
            'status' => PointSubmission::STATUS_REJECTED,
            'admin_id' => $admin->id,
            'admin_notes' => $reason,
            'reviewed_at' => Carbon::now(),
        ]);
    }

    /**
     * Get pending submissions for admin
     *
     * @param array<string, mixed> $filters
     */
    public function getPendingSubmissions(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = PointSubmission::with(['user', 'pointTask'])
            ->where('status', PointSubmission::STATUS_PENDING)
            ->orderBy('created_at', 'asc');

        if (isset($filters['task_code'])) {
            $query->whereHas('pointTask', fn ($q) => $q->where('code', $filters['task_code']));
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get all submissions for admin (with filters)
     *
     * @param array<string, mixed> $filters
     */
    public function getSubmissions(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = PointSubmission::with(['user', 'pointTask', 'admin'])
            ->orderByDesc('created_at');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['task_code'])) {
            $query->whereHas('pointTask', fn ($q) => $q->where('code', $filters['task_code']));
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get user's submissions
     */
    public function getUserSubmissions(User $user, int $limit = 50): Collection
    {
        return PointSubmission::with('pointTask')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get submission stats for admin dashboard
     *
     * @return array<string, int>
     */
    public function getStats(): array
    {
        return [
            'pending' => PointSubmission::where('status', PointSubmission::STATUS_PENDING)->count(),
            'approved_today' => PointSubmission::where('status', PointSubmission::STATUS_APPROVED)
                ->whereDate('reviewed_at', Carbon::today())->count(),
            'rejected_today' => PointSubmission::where('status', PointSubmission::STATUS_REJECTED)
                ->whereDate('reviewed_at', Carbon::today())->count(),
            'total_approved' => PointSubmission::where('status', PointSubmission::STATUS_APPROVED)->count(),
            'total_rejected' => PointSubmission::where('status', PointSubmission::STATUS_REJECTED)->count(),
        ];
    }

    /**
     * Get stats by task
     *
     * @return array<string, array<string, int>>
     */
    public function getStatsByTask(): array
    {
        $tasks = PointTask::where('requires_approval', true)->get();
        $stats = [];

        foreach ($tasks as $task) {
            $stats[$task->code] = [
                'name' => $task->name,
                'pending' => PointSubmission::where('point_task_id', $task->id)
                    ->where('status', PointSubmission::STATUS_PENDING)->count(),
                'approved' => PointSubmission::where('point_task_id', $task->id)
                    ->where('status', PointSubmission::STATUS_APPROVED)->count(),
                'rejected' => PointSubmission::where('point_task_id', $task->id)
                    ->where('status', PointSubmission::STATUS_REJECTED)->count(),
            ];
        }

        return $stats;
    }

    /**
     * Validate proof data matches task requirements
     *
     * @param array<string, mixed> $proofData
     * @throws InvalidArgumentException
     */
    private function validateProofData(PointTask $task, array $proofData): void
    {
        switch ($task->proof_type) {
            case PointTask::PROOF_IMAGE:
                if (empty($proofData['paths']) || !is_array($proofData['paths'])) {
                    throw new InvalidArgumentException('Cần upload hình ảnh làm bằng chứng');
                }
                // Validate each path for security
                $this->validateImagePaths($proofData['paths']);
                break;

            case PointTask::PROOF_LINK:
                if (empty($proofData['url']) || !filter_var($proofData['url'], FILTER_VALIDATE_URL)) {
                    throw new InvalidArgumentException('Cần nhập URL hợp lệ');
                }
                // Validate URL is not already used
                if (!$this->socialVerificationService->isUrlAvailable($proofData['url'])) {
                    throw new InvalidArgumentException('URL này đã được sử dụng cho một xác minh khác');
                }
                break;

            case PointTask::PROOF_QR_CODE:
                if (empty($proofData['qr_data'])) {
                    throw new InvalidArgumentException('Cần quét mã QR');
                }
                break;
        }
    }

    /**
     * Validate image paths for security (prevent path traversal, validate file type)
     *
     * @param array<string> $paths
     * @throws InvalidArgumentException
     */
    private function validateImagePaths(array $paths): void
    {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedPrefix = 'point-submissions/';

        foreach ($paths as $path) {
            // Check for path traversal attempts
            if (str_contains($path, '..') || str_contains($path, '//')) {
                throw new InvalidArgumentException('Đường dẫn không hợp lệ: ' . basename($path));
            }

            // Validate path is within allowed directory
            if (!str_starts_with($path, $allowedPrefix)) {
                throw new InvalidArgumentException('Đường dẫn không hợp lệ: ' . basename($path));
            }

            // Validate file exists
            if (!Storage::disk(config('filesystems.default'))->exists($path)) {
                throw new InvalidArgumentException('File không tồn tại: ' . basename($path));
            }

            // Validate file is an image
            $mimeType = Storage::mimeType($path);
            if (!in_array($mimeType, $allowedMimeTypes, true)) {
                throw new InvalidArgumentException('Chỉ chấp nhận file hình ảnh (JPEG, PNG, GIF, WebP)');
            }

            // Validate file size (max 5MB)
            $size = Storage::disk(config('filesystems.default'))->size($path);
            if ($size > 5 * 1024 * 1024) {
                throw new InvalidArgumentException('File quá lớn (tối đa 5MB)');
            }
        }
    }

    /**
     * Check if task is a social verification task
     */
    private function isSocialTask(string $taskCode): bool
    {
        return in_array($taskCode, [
            PointTask::CODE_JOIN_FB_GROUP,
            PointTask::CODE_FOLLOW_FB_PAGE,
            PointTask::CODE_SUBSCRIBE_YOUTUBE,
            PointTask::CODE_FOLLOW_TIKTOK,
        ]);
    }

    /**
     * Create social profile verification after approval
     */
    private function createSocialVerification(PointSubmission $submission, User $admin): void
    {
        $taskCode = $submission->pointTask->code;
        $url = $submission->proof_data['url'] ?? null;

        if (!$url) {
            return;
        }

        $platform = match ($taskCode) {
            PointTask::CODE_JOIN_FB_GROUP, PointTask::CODE_FOLLOW_FB_PAGE => 'facebook',
            PointTask::CODE_SUBSCRIBE_YOUTUBE => 'youtube',
            PointTask::CODE_FOLLOW_TIKTOK => 'tiktok',
            default => null,
        };

        if ($platform) {
            try {
                $this->socialVerificationService->verifyProfile(
                    $submission->user,
                    $platform,
                    $url,
                    $admin
                );
            } catch (InvalidArgumentException $e) {
                // Log but don't fail the approval
                Log::warning('Social verification failed', [
                    'submission_id' => $submission->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
