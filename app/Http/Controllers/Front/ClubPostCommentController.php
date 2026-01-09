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
        $page = $request->input('page', 1);

        $comments = $post->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'comments' => $comments->items(),
            'hasMore' => $comments->hasMorePages(),
            'currentPage' => $comments->currentPage(),
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
                    'message' => 'Bình luận cha không hợp lệ',
                ], 400);
            }
            // Only 1-level nesting
            if ($parent->parent_id !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ hỗ trợ 1 cấp trả lời',
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
