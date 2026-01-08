# Phase 04: Controllers & Services

**Priority:** Critical
**Status:** Pending
**Depends on:** Phase 03

---

## Context

Follow existing patterns from:
- `ClubController` - route model binding with Club
- `VideoCommentController` - AJAX JSON responses
- `ProfileService` - file upload handling

---

## Related Files

**Create:**
- `app/Http/Controllers/Front/ClubPostController.php`
- `app/Http/Controllers/Front/ClubPostReactionController.php`
- `app/Http/Controllers/Front/ClubPostCommentController.php`
- `app/Http/Requests/StoreClubPostRequest.php`
- `app/Http/Requests/UpdateClubPostRequest.php`
- `app/Services/ClubPostMediaService.php`
- `config/club_posts.php`

**Modify:**
- `routes/web.php` - add post routes

---

## Implementation Steps

### Step 1: Create config file

```php
// config/club_posts.php
<?php

return [
    'disk' => env('CLUB_POSTS_DISK', 'public'),

    'content' => [
        'max_length' => 5000,
        'allowed_tags' => '<p><br><strong><em><s><a><ul><ol><li>',
    ],

    'images' => [
        'max_count' => 10,
        'max_size' => 5 * 1024, // 5MB in KB
        'allowed_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    ],

    'videos' => [
        'max_count' => 1,
        'max_size' => 50 * 1024, // 50MB in KB
        'allowed_mimes' => ['mp4', 'mov', 'webm'],
    ],

    'feed' => [
        'per_page' => 10,
    ],
];
```

### Step 2: Create ClubPostMediaService

```php
// app/Services/ClubPostMediaService.php
<?php

namespace App\Services;

use App\Models\ClubPost;
use App\Models\ClubPostMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClubPostMediaService
{
    private string $disk;

    public function __construct()
    {
        $this->disk = config('club_posts.disk', 'public');
    }

    /**
     * Handle media upload for a post
     */
    public function handleUpload(ClubPost $post, array $files, string $type): void
    {
        $order = 0;
        foreach ($files as $file) {
            $path = $this->storeFile($file, $type);

            ClubPostMedia::create([
                'club_post_id' => $post->id,
                'type' => $type,
                'path' => $path,
                'disk' => $this->disk,
                'size' => $file->getSize(),
                'order' => $order++,
            ]);
        }
    }

    /**
     * Handle YouTube URL
     */
    public function handleYoutube(ClubPost $post, string $url): void
    {
        ClubPostMedia::create([
            'club_post_id' => $post->id,
            'type' => 'youtube',
            'youtube_url' => $url,
            'disk' => $this->disk,
            'order' => 0,
        ]);
    }

    /**
     * Store uploaded file
     */
    private function storeFile(UploadedFile $file, string $type): string
    {
        $folder = $type === 'image' ? 'club-posts/images' : 'club-posts/videos';
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($folder, $filename, $this->disk);
    }

    /**
     * Delete all media for a post
     */
    public function deletePostMedia(ClubPost $post): void
    {
        foreach ($post->media as $media) {
            $this->deleteMedia($media);
        }
    }

    /**
     * Delete single media item
     */
    public function deleteMedia(ClubPostMedia $media): void
    {
        if ($media->path && Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($media->disk)->delete($media->path);
        }
        $media->delete();
    }

    /**
     * Update post media (delete old, add new)
     */
    public function updatePostMedia(ClubPost $post, ?array $files, ?string $type, ?string $youtubeUrl, array $keepMediaIds = []): void
    {
        // Delete media not in keepMediaIds
        foreach ($post->media as $media) {
            if (!in_array($media->id, $keepMediaIds)) {
                $this->deleteMedia($media);
            }
        }

        // Add new media
        if ($files && $type && $type !== 'youtube') {
            $this->handleUpload($post, $files, $type);
        } elseif ($youtubeUrl) {
            $this->handleYoutube($post, $youtubeUrl);
        }
    }
}
```

### Step 3: Create Form Requests

```php
// app/Http/Requests/StoreClubPostRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClubPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Handled by policy in controller
    }

    public function rules(): array
    {
        $imageConfig = config('club_posts.images');
        $videoConfig = config('club_posts.videos');

        return [
            'content' => 'required|string|max:' . config('club_posts.content.max_length'),
            'visibility' => 'required|in:public,members_only',
            'media_type' => 'nullable|in:images,video,youtube',
            'images' => 'nullable|array|max:' . $imageConfig['max_count'],
            'images.*' => 'image|mimes:' . implode(',', $imageConfig['allowed_mimes']) . '|max:' . $imageConfig['max_size'],
            'video' => 'nullable|file|mimes:' . implode(',', $videoConfig['allowed_mimes']) . '|max:' . $videoConfig['max_size'],
            'youtube_url' => 'nullable|url|regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Noi dung bai viet khong duoc de trong',
            'content.max' => 'Noi dung khong duoc vuot qua :max ky tu',
            'images.max' => 'Toi da :max anh',
            'images.*.max' => 'Moi anh khong duoc vuot qua 5MB',
            'video.max' => 'Video khong duoc vuot qua 50MB',
            'youtube_url.regex' => 'URL YouTube khong hop le',
        ];
    }
}
```

```php
// app/Http/Requests/UpdateClubPostRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClubPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Handled by policy in controller
    }

    public function rules(): array
    {
        $imageConfig = config('club_posts.images');
        $videoConfig = config('club_posts.videos');

        return [
            'content' => 'required|string|max:' . config('club_posts.content.max_length'),
            'visibility' => 'required|in:public,members_only',
            'media_type' => 'nullable|in:images,video,youtube',
            'keep_media_ids' => 'nullable|array',
            'keep_media_ids.*' => 'integer|exists:club_post_media,id',
            'images' => 'nullable|array|max:' . $imageConfig['max_count'],
            'images.*' => 'image|mimes:' . implode(',', $imageConfig['allowed_mimes']) . '|max:' . $imageConfig['max_size'],
            'video' => 'nullable|file|mimes:' . implode(',', $videoConfig['allowed_mimes']) . '|max:' . $videoConfig['max_size'],
            'youtube_url' => 'nullable|url|regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/',
        ];
    }
}
```

### Step 4: Create ClubPostController

```php
// app/Http/Controllers/Front/ClubPostController.php
<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClubPostRequest;
use App\Http\Requests\UpdateClubPostRequest;
use App\Models\Club;
use App\Models\ClubPost;
use App\Services\ClubPostMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClubPostController extends Controller
{
    public function __construct(
        private ClubPostMediaService $mediaService
    ) {}

    /**
     * Show single post (for SEO & direct link)
     */
    public function show(Club $club, ClubPost $post)
    {
        $this->authorize('view', $post);

        $post->load(['author', 'media', 'reactions', 'comments.user', 'comments.replies.user']);

        // For AJAX request
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'post' => $post,
            ]);
        }

        // For SEO - render full page
        return view('clubs.posts.show', compact('club', 'post'));
    }

    /**
     * List posts for feed (AJAX)
     */
    public function index(Club $club, Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = $club->posts()
            ->with(['author', 'media', 'reactions'])
            ->withCount('allComments')
            ->feed();

        // Filter by visibility for non-members
        if (!$user || !$club->members()->where('user_id', $user->id)->exists()) {
            $query->public();
        }

        $posts = $query->paginate(config('club_posts.feed.per_page'));

        return response()->json([
            'success' => true,
            'posts' => $posts->items(),
            'hasMore' => $posts->hasMorePages(),
            'nextPage' => $posts->currentPage() + 1,
        ]);
    }

    /**
     * Store new post
     */
    public function store(StoreClubPostRequest $request, Club $club): JsonResponse
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
                'post' => $post,
                'message' => 'Dang bai thanh cong',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Loi khi dang bai: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update post
     */
    public function update(UpdateClubPostRequest $request, Club $club, ClubPost $post): JsonResponse
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
                'post' => $post,
                'message' => 'Cap nhat thanh cong',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Loi khi cap nhat: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete post
     */
    public function destroy(Club $club, ClubPost $post): JsonResponse
    {
        $this->authorize('delete', $post);

        $post->delete(); // Soft delete, keep media

        return response()->json([
            'success' => true,
            'message' => 'Xoa bai viet thanh cong',
        ]);
    }

    /**
     * Toggle pin status
     */
    public function togglePin(Club $club, ClubPost $post): JsonResponse
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
            'message' => $post->is_pinned ? 'Da ghim bai viet' : 'Da bo ghim',
        ]);
    }

    /**
     * Sanitize HTML content
     */
    private function sanitizeContent(string $content): string
    {
        $allowedTags = config('club_posts.content.allowed_tags');
        return strip_tags($content, $allowedTags);
    }
}
```

### Step 5: Create ClubPostReactionController

```php
// app/Http/Controllers/Front/ClubPostReactionController.php
<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ClubPost;
use App\Models\ClubPostReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClubPostReactionController extends Controller
{
    /**
     * Add or change reaction
     */
    public function store(Request $request, ClubPost $post): JsonResponse
    {
        $this->authorize('react', $post);

        $request->validate([
            'type' => 'required|in:like,love,fire',
        ]);

        $userId = Auth::id();
        $type = $request->type;

        // Find existing reaction
        $existing = ClubPostReaction::where('club_post_id', $post->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            if ($existing->type === $type) {
                // Same type = remove reaction
                $existing->delete();
                $reacted = false;
                $currentType = null;
            } else {
                // Different type = change reaction
                $existing->update(['type' => $type]);
                $reacted = true;
                $currentType = $type;
            }
        } else {
            // New reaction
            ClubPostReaction::create([
                'club_post_id' => $post->id,
                'user_id' => $userId,
                'type' => $type,
            ]);
            $reacted = true;
            $currentType = $type;
        }

        return response()->json([
            'success' => true,
            'reacted' => $reacted,
            'type' => $currentType,
            'counts' => $post->fresh()->reactionCounts,
            'total' => $post->fresh()->totalReactions,
        ]);
    }

    /**
     * Remove reaction
     */
    public function destroy(ClubPost $post): JsonResponse
    {
        ClubPostReaction::where('club_post_id', $post->id)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json([
            'success' => true,
            'counts' => $post->fresh()->reactionCounts,
            'total' => $post->fresh()->totalReactions,
        ]);
    }
}
```

### Step 6: Create ClubPostCommentController

```php
// app/Http/Controllers/Front/ClubPostCommentController.php
<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ClubPost;
use App\Models\ClubPostComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClubPostCommentController extends Controller
{
    /**
     * List comments for a post
     */
    public function index(ClubPost $post, Request $request): JsonResponse
    {
        $comments = $post->comments()
            ->with(['user', 'replies.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'comments' => $comments->items(),
            'hasMore' => $comments->hasMorePages(),
        ]);
    }

    /**
     * Store new comment
     */
    public function store(Request $request, ClubPost $post): JsonResponse
    {
        $this->authorize('comment', $post);

        $request->validate([
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:club_post_comments,id',
        ]);

        // Validate parent belongs to same post
        if ($request->parent_id) {
            $parent = ClubPostComment::find($request->parent_id);
            if ($parent->club_post_id !== $post->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comment parent khong hop le',
                ], 400);
            }
            // Only 1-level nesting
            if ($parent->parent_id !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chi ho tro 1 cap reply',
                ], 400);
            }
        }

        $comment = ClubPostComment::create([
            'club_post_id' => $post->id,
            'user_id' => Auth::id(),
            'parent_id' => $request->parent_id,
            'content' => strip_tags($request->content),
        ]);

        $comment->load('user');

        return response()->json([
            'success' => true,
            'comment' => $comment,
        ]);
    }

    /**
     * Update comment
     */
    public function update(Request $request, ClubPostComment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $comment->update([
            'content' => strip_tags($request->content),
        ]);

        return response()->json([
            'success' => true,
            'comment' => $comment,
        ]);
    }

    /**
     * Delete comment
     */
    public function destroy(ClubPostComment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
```

### Step 7: Add Routes

Add to `routes/web.php` after Club routes:

```php
// Club Posts Routes (after line 255)
Route::prefix('clubs/{club}/posts')->name('clubs.posts.')->group(function () {
    Route::get('/', [ClubPostController::class, 'index'])->name('index');
    Route::get('/{post}', [ClubPostController::class, 'show'])->name('show'); // SEO endpoint

    Route::middleware('auth')->group(function () {
        Route::post('/', [ClubPostController::class, 'store'])->name('store');
        Route::put('/{post}', [ClubPostController::class, 'update'])->name('update');
        Route::delete('/{post}', [ClubPostController::class, 'destroy'])->name('destroy');
        Route::post('/{post}/pin', [ClubPostController::class, 'togglePin'])->name('pin');
    });
});

// Club Post Reactions
Route::middleware('auth')->group(function () {
    Route::post('club-posts/{post}/reactions', [ClubPostReactionController::class, 'store'])->name('club-posts.reactions.store');
    Route::delete('club-posts/{post}/reactions', [ClubPostReactionController::class, 'destroy'])->name('club-posts.reactions.destroy');
});

// Club Post Comments
Route::prefix('club-posts/{post}/comments')->name('club-posts.comments.')->group(function () {
    Route::get('/', [ClubPostCommentController::class, 'index'])->name('index');

    Route::middleware('auth')->group(function () {
        Route::post('/', [ClubPostCommentController::class, 'store'])->name('store');
    });
});

Route::middleware('auth')->group(function () {
    Route::put('club-post-comments/{comment}', [ClubPostCommentController::class, 'update'])->name('club-post-comments.update');
    Route::delete('club-post-comments/{comment}', [ClubPostCommentController::class, 'destroy'])->name('club-post-comments.destroy');
});
```

Also add imports at top of web.php:
```php
use App\Http\Controllers\Front\ClubPostController;
use App\Http\Controllers\Front\ClubPostReactionController;
use App\Http\Controllers\Front\ClubPostCommentController;
```

---

## Todo List

- [ ] Create config/club_posts.php
- [ ] Create ClubPostMediaService
- [ ] Create StoreClubPostRequest
- [ ] Create UpdateClubPostRequest
- [ ] Create ClubPostController
- [ ] Create ClubPostReactionController
- [ ] Create ClubPostCommentController
- [ ] Add routes to web.php
- [ ] Add controller imports to web.php
- [ ] Clear route cache: `php artisan route:clear`

---

## Success Criteria

- [ ] All routes registered correctly (`php artisan route:list | grep club`)
- [ ] Posts CRUD works via API testing
- [ ] Reactions toggle works
- [ ] Comments with replies work
- [ ] Media upload stores files correctly

---

## Next Steps

Proceed to Phase 05: Frontend Views
