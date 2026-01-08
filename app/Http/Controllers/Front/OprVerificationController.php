<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\OprVerificationRequest;
use App\Services\OprVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OprVerificationController extends Controller
{
    public function __construct(
        private OprVerificationService $verificationService
    ) {}

    /**
     * Show verification request form
     */
    public function create(): View|RedirectResponse
    {
        $user = auth()->user();

        if (!$user->hasCompletedSkillQuiz()) {
            return redirect()->route('skill-quiz.index')
                ->with('error', 'Vui lòng hoàn thành Quiz đánh giá kỹ năng trước khi yêu cầu xác minh');
        }

        if ($user->is_elo_verified) {
            return redirect()->route('ocr.profile', $user)
                ->with('info', 'ELO của bạn đã được xác minh');
        }

        $pendingRequest = $user->oprVerificationRequests()
            ->where('status', OprVerificationRequest::STATUS_PENDING)
            ->first();

        $rejectedRequest = $user->oprVerificationRequests()
            ->where('status', OprVerificationRequest::STATUS_REJECTED)
            ->latest()
            ->first();

        return view('front.opr.verification-form', [
            'user' => $user,
            'pendingRequest' => $pendingRequest,
            'rejectedRequest' => $rejectedRequest,
            'maxImages' => OprVerificationRequest::MAX_IMAGES,
            'maxVideos' => OprVerificationRequest::MAX_VIDEOS,
        ]);
    }

    /**
     * Store verification request
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'notes' => 'nullable|string|max:2000',
            'images' => 'nullable|array|max:' . OprVerificationRequest::MAX_IMAGES,
            'images.*' => 'image|mimes:jpeg,png,webp|max:5120',
            'videos' => 'nullable|array|max:' . OprVerificationRequest::MAX_VIDEOS,
            'videos.*' => 'mimetypes:video/mp4,video/quicktime,video/webm|max:51200',
            'links' => 'nullable|array|max:5',
            'links.*.url' => 'required_with:links|url',
            'links.*.type' => 'required_with:links|in:youtube,facebook,tiktok,other',
        ], [
            'images.*.max' => 'Mỗi hình ảnh không được vượt quá 5MB',
            'videos.*.max' => 'Mỗi video không được vượt quá 50MB',
            'links.*.url.url' => 'Đường dẫn không hợp lệ',
        ]);

        try {
            // Filter out empty links
            $links = collect($validated['links'] ?? [])
                ->filter(fn($link) => !empty($link['url']))
                ->values()
                ->toArray();

            $verificationRequest = $this->verificationService->createRequest($user, [
                'media_paths' => [],
                'links' => $links,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Handle image uploads via Spatie Media Library
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $verificationRequest->addMedia($image)
                        ->toMediaCollection('verification_images');
                }
            }

            // Handle video uploads via Spatie Media Library
            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $video) {
                    $verificationRequest->addMedia($video)
                        ->toMediaCollection('verification_videos');
                }
            }

            return redirect()->route('opr-verification.show', $verificationRequest)
                ->with('success', 'Yêu cầu xác minh đã được gửi. Vui lòng chờ duyệt.');

        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * View verification request status
     */
    public function show(OprVerificationRequest $request): View|RedirectResponse
    {
        $user = auth()->user();

        if ($request->user_id !== $user->id) {
            abort(403);
        }

        return view('front.opr.verification-status', [
            'verificationRequest' => $request->load(['verifier']),
            'user' => $user,
        ]);
    }
}
