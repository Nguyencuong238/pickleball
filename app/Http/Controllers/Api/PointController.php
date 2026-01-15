<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PointSubmissionResource;
use App\Http\Resources\PointTaskResource;
use App\Http\Resources\PointTransactionResource;
use App\Http\Resources\SpecialChallengeResource;
use App\Models\PointSubmission;
use App\Models\PointTask;
use App\Models\SpecialChallenge;
use App\Services\PointEarningService;
use App\Services\PointSubmissionService;
use App\Services\SocialVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PointController extends Controller
{
    public function __construct(
        private PointEarningService $pointEarningService,
        private PointSubmissionService $submissionService,
        private SocialVerificationService $socialVerificationService
    ) {}

    /**
     * Get available tasks with eligibility
     *
     * GET /api/points/tasks
     */
    public function tasks(): JsonResponse
    {
        $user = auth()->user();
        $tasks = $this->pointEarningService->getAllAvailableTasks($user);

        $resources = $tasks->map(function ($item) {
            return (new PointTaskResource($item['task']))
                ->setEligibility($item['can_earn'], $item['reason']);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'tasks' => $resources,
                'social_status' => $this->socialVerificationService->getVerificationStatus($user),
            ],
        ]);
    }

    /**
     * Get wallet balance
     *
     * GET /api/points/balance
     */
    public function balance(): JsonResponse
    {
        $user = auth()->user();
        $wallet = $user->wallet;

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $wallet ? $wallet->points : 0,
                'formatted_balance' => $wallet ? $wallet->getFormattedPoints() : '0',
            ],
        ]);
    }

    /**
     * Get transaction history
     *
     * GET /api/points/history
     */
    public function history(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = min((int) $request->input('per_page', 20), 50);

        $transactions = $user->pointTransactions()
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => PointTransactionResource::collection($transactions),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ],
            ],
        ]);
    }

    /**
     * Get user submissions
     *
     * GET /api/points/submissions
     */
    public function submissions(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = min((int) $request->input('per_page', 20), 50);

        // Validate status parameter using Laravel validation
        $request->validate([
            'status' => 'nullable|in:pending,approved,rejected',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $status = $request->input('status');

        // Get pending count BEFORE pagination to avoid N+1
        $pendingCount = PointSubmission::where('user_id', $user->id)
            ->where('status', PointSubmission::STATUS_PENDING)
            ->count();

        $query = PointSubmission::with('pointTask')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        $submissions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'submissions' => PointSubmissionResource::collection($submissions),
                'pending_count' => $pendingCount,
                'pagination' => [
                    'current_page' => $submissions->currentPage(),
                    'last_page' => $submissions->lastPage(),
                    'per_page' => $submissions->perPage(),
                    'total' => $submissions->total(),
                ],
            ],
        ]);
    }

    /**
     * Submit proof for approval
     *
     * POST /api/points/submissions
     */
    public function submit(Request $request): JsonResponse
    {
        $user = auth()->user();

        $request->validate([
            'task_code' => 'required|string|exists:point_tasks,code',
        ]);

        $task = PointTask::findByCode($request->input('task_code'));

        if (!$task || !$task->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Nhiem vu khong tim thay hoac khong hoat dong',
            ], 404);
        }

        // Authorization check
        if (!$user->can('submit', $task)) {
            return response()->json([
                'success' => false,
                'message' => 'Ban khong du dieu kien de nop yeu cau nay',
            ], 403);
        }

        // Validate proof data based on type
        $proofRules = $this->getProofValidationRules($task);
        $request->validate($proofRules);

        try {
            // Build proof data with transaction wrapper for safety
            $submission = DB::transaction(function () use ($request, $task, $user) {
                $proofData = $this->buildProofData($request, $task);
                return $this->submissionService->submit($user, $task->code, $proofData);
            });

            return response()->json([
                'success' => true,
                'message' => 'Yeu cau da duoc tao thanh cong. Dang cho admin duyet.',
                'data' => [
                    'submission' => new PointSubmissionResource($submission->load('pointTask')),
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::warning('Point submission failed', [
                'user_id' => $user->id,
                'task_code' => $task->code,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get active special challenges
     *
     * GET /api/points/challenges
     */
    public function challenges(): JsonResponse
    {
        $challenges = SpecialChallenge::ongoing()
            ->orderBy('end_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'challenges' => SpecialChallengeResource::collection($challenges),
            ],
        ]);
    }

    /**
     * Get validation rules for proof data
     *
     * @return array<string, mixed>
     */
    private function getProofValidationRules(PointTask $task): array
    {
        return match ($task->proof_type) {
            PointTask::PROOF_IMAGE => [
                'images' => 'required|array|min:1|max:5',
                'images.*' => 'required|string|max:10485760', // ~10MB base64 limit
            ],
            PointTask::PROOF_LINK => [
                'url' => 'required|url|max:500',
            ],
            PointTask::PROOF_QR_CODE => [
                'qr_data' => 'required|string|min:10|max:255',
            ],
            default => [],
        };
    }

    /**
     * Build proof data from request
     *
     * @return array<string, mixed>
     */
    private function buildProofData(Request $request, PointTask $task): array
    {
        $proofData = ['type' => $task->proof_type];

        switch ($task->proof_type) {
            case PointTask::PROOF_IMAGE:
                $paths = [];
                $userId = auth()->id();

                foreach ($request->input('images') as $base64) {
                    // Validate and decode base64 image
                    $imageData = $this->decodeBase64Image($base64);

                    if ($imageData === null) {
                        throw new \InvalidArgumentException('Dinh dang hinh anh khong hop le');
                    }

                    // Validate image size (max 5MB)
                    if (strlen($imageData) > 5 * 1024 * 1024) {
                        throw new \InvalidArgumentException('Hinh anh qua lon (toi da 5MB)');
                    }

                    // Generate unique filename and save
                    $filename = uniqid('proof_') . '.jpg';
                    $path = "point-submissions/{$userId}/{$filename}";
                    Storage::disk('public')->put($path, $imageData);
                    $paths[] = $path;
                }

                $proofData['paths'] = $paths;
                break;

            case PointTask::PROOF_LINK:
                // Sanitize URL
                $url = filter_var($request->input('url'), FILTER_SANITIZE_URL);
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    throw new \InvalidArgumentException('URL khong hop le');
                }

                // Check if this is a social task and validate uniqueness
                $socialInfo = $this->getSocialPlatformFromTask($task->code);
                if ($socialInfo !== null) {
                    $user = auth()->user();
                    // Check if user already has this platform verified
                    if ($this->socialVerificationService->isProfileVerified($user, $socialInfo['platform'])) {
                        throw new \InvalidArgumentException('Ban da xac minh ' . ucfirst($socialInfo['platform']) . ' roi');
                    }
                    // Check if URL is already used by another user
                    if (!$this->socialVerificationService->isUrlAvailable($url, $user->id)) {
                        throw new \InvalidArgumentException('URL nay da duoc su dung boi nguoi dung khac');
                    }
                }

                $proofData['url'] = $url;
                break;

            case PointTask::PROOF_QR_CODE:
                $qrData = trim($request->input('qr_data'));
                // Basic QR code validation - check length and format
                if (strlen($qrData) < 10 || strlen($qrData) > 255) {
                    throw new \InvalidArgumentException('Ma QR khong hop le');
                }
                $proofData['qr_data'] = $qrData;
                break;
        }

        // Special challenge ID
        if ($task->code === PointTask::CODE_SPECIAL_CHALLENGE && $request->filled('challenge_id')) {
            $challengeId = (int) $request->input('challenge_id');
            $challenge = SpecialChallenge::find($challengeId);

            if (!$challenge || !$challenge->isOngoing()) {
                throw new \InvalidArgumentException('Thach thuc khong tim thay hoac da ket thuc');
            }

            if ($challenge->hasReachedLimit()) {
                throw new \InvalidArgumentException('Thach thuc da dat gioi han nguoi tham gia');
            }

            $proofData['challenge_id'] = $challengeId;
        }

        return $proofData;
    }

    /**
     * Decode, validate, and sanitize base64 image
     * Includes DoS protection (size limits), image bomb detection, and EXIF stripping
     */
    private function decodeBase64Image(string $base64): ?string
    {
        // Remove data URI scheme if present
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);

        // DoS protection: Validate base64 size BEFORE decode (~7MB base64 = ~5MB decoded)
        if (strlen($base64) > 7 * 1024 * 1024) {
            return null;
        }

        // Decode
        $decoded = base64_decode($base64, true);

        if ($decoded === false) {
            return null;
        }

        // Validate it's actually an image by checking magic bytes
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($decoded);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes, true)) {
            return null;
        }

        // Image bomb detection + EXIF stripping: Re-encode through GD
        $sanitized = $this->sanitizeImage($decoded, $mimeType);

        return $sanitized;
    }

    /**
     * Sanitize image: detect image bombs by dimension check, strip EXIF metadata
     */
    private function sanitizeImage(string $imageData, string $mimeType): ?string
    {
        // Create GD image from string (validates image is actually renderable)
        $image = @imagecreatefromstring($imageData);

        if ($image === false) {
            return null;
        }

        // Image bomb detection: check dimensions (max 4000x4000 pixels)
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > 4000 || $height > 4000 || $width * $height > 16000000) {
            imagedestroy($image);
            return null;
        }

        // Re-encode to JPEG to strip all EXIF/metadata
        ob_start();
        imagejpeg($image, null, 85);
        $sanitized = ob_get_clean();

        imagedestroy($image);

        return $sanitized !== false ? $sanitized : null;
    }

    /**
     * Check if task code is a social verification task
     *
     * @return array{platform: string}|null
     */
    private function getSocialPlatformFromTask(string $taskCode): ?array
    {
        $mapping = [
            PointTask::CODE_JOIN_FB_GROUP => ['platform' => 'facebook'],
            PointTask::CODE_FOLLOW_FB_PAGE => ['platform' => 'facebook'],
            PointTask::CODE_SUBSCRIBE_YOUTUBE => ['platform' => 'youtube'],
            PointTask::CODE_FOLLOW_TIKTOK => ['platform' => 'tiktok'],
        ];

        return $mapping[$taskCode] ?? null;
    }
}
