<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClubPostRequest;
use App\Http\Requests\UpdateClubPostRequest;
use App\Models\Club;
use App\Models\ClubPost;
use App\Services\ClubPostMediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;
use Exception;

class ClubPostController extends Controller
{
    public function __construct(
        private ClubPostMediaService $mediaService
    ) {}

    /**
     * Display posts for club.
     */
    public function index(Club $club, Request $request)
    {
        $user = Auth::user();

        $query = $club->posts()
            ->with(['author', 'media', 'reactions'])
            ->withCount('allComments')
            ->feed();

        // Filter by visibility for non-members
        if (!$user || !$club->isMember($user)) {
            $query->public();
        }

        $posts = $query->paginate(config('club_posts.feed.per_page'));

        return response()->json([
            'success' => true,
            'posts' => $posts->items(),
            'pagination' => [
                'total' => $posts->total(),
                'per_page' => $posts->perPage(),
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'has_more' => $posts->hasMorePages(),
            ]
        ]);
    }

    /**
     * Display the specified post.
     */
    public function show(Club $club, ClubPost $post)
    {
        $this->authorize('view', $post);

        $post->load(['author', 'media', 'reactions', 'comments.user', 'comments.replies.user']);

        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    }

    /**
     * Store a newly created post.
     */
    public function store(StoreClubPostRequest $request, Club $club)
    {
        $this->authorize('create', [ClubPost::class, $club]);

        try {
            DB::beginTransaction();

            // Sanitize content
            $content = $this->sanitizeContent($request->content);

            $post = ClubPost::create([
                'club_id' => $club->id,
                'user_id' => Auth::id(),
                'content' => $content,
                'visibility' => $request->visibility,
            ]);

            // Handle media
            $mediaType = $request->media_type;
            if ($mediaType === 'images' && $request->hasFile('images')) {
                $this->mediaService->handleUpload($post, $request->file('images'), 'image');
            } elseif ($mediaType === 'video' && $request->hasFile('video')) {
                $this->mediaService->handleUpload($post, [$request->file('video')], 'video');
            } elseif ($mediaType === 'youtube' && $request->youtube_url) {
                $this->mediaService->handleYoutube($post, $request->youtube_url);
            }

            DB::commit();

            $post->load(['author', 'media', 'reactions']);

            return response()->json([
                'success' => true,
                'data' => $post,
                'message' => 'Đăng bài thành công',
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đăng bài: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified post.
     */
    public function update(UpdateClubPostRequest $request, Club $club, ClubPost $post)
    {
        $this->authorize('update', $post);

        try {
            DB::beginTransaction();

            $content = $this->sanitizeContent($request->content);

            $post->update([
                'content' => $content,
                'visibility' => $request->visibility,
                'edited_at' => now(),
            ]);

            // Handle media update
            $keepIds = $request->keep_media_ids ?? [];
            $mediaType = $request->media_type;

            if ($mediaType === 'images' && $request->hasFile('images')) {
                $this->mediaService->updatePostMedia($post, $request->file('images'), 'image', null, $keepIds);
            } elseif ($mediaType === 'video' && $request->hasFile('video')) {
                $this->mediaService->updatePostMedia($post, [$request->file('video')], 'video', null, $keepIds);
            } elseif ($mediaType === 'youtube' && $request->youtube_url) {
                $this->mediaService->updatePostMedia($post, null, null, $request->youtube_url, $keepIds);
            } else {
                // Just remove unchecked media
                $this->mediaService->updatePostMedia($post, null, null, null, $keepIds);
            }

            DB::commit();

            $post->load(['author', 'media', 'reactions']);

            return response()->json([
                'success' => true,
                'data' => $post,
                'message' => 'Cập nhật thành công',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Club $club, ClubPost $post)
    {
        $this->authorize('delete', $post);

        $post->delete(); // Soft delete, keep media

        return response()->json([
            'success' => true,
            'message' => 'Xóa bài viết thành công',
        ]);
    }

    /**
     * Toggle pin status.
     */
    public function togglePin(Club $club, ClubPost $post)
    {
        $this->authorize('pin', $post);

        $post->update([
            'is_pinned' => !$post->is_pinned,
            'pinned_at' => !$post->is_pinned ? now() : null,
            'pinned_by' => !$post->is_pinned ? Auth::id() : null,
        ]);

        return response()->json([
            'success' => true,
            'is_pinned' => $post->is_pinned,
            'message' => $post->is_pinned ? 'Đã ghim bài viết' : 'Đã bỏ ghim',
        ]);
    }

    /**
     * Sanitize HTML content using HTMLPurifier
     */
    private function sanitizeContent(string $content): string
    {
        return Purifier::clean($content, 'club_posts');
    }
}
