<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoCommentController extends Controller
{
    public function store(Request $request, Video $video)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:video_comments,id',
        ]);

        $comment = VideoComment::create([
            'video_id' => $video->id,
            'user_id' => Auth::id(),
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        return response()->json([
            'success' => true,
            'comment' => $this->formatComment($comment),
        ]);
    }

    public function destroy(VideoComment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(['success' => true]);
    }

    public function likeComment(VideoComment $comment)
    {
        $user = Auth::user();

        if ($comment->likedByUsers()->where('user_id', $user->id)->exists()) {
            $comment->likedByUsers()->detach($user->id);
            $liked = false;
        } else {
            $comment->likedByUsers()->attach($user->id);
            $liked = true;
        }

        $comment->update(['likes_count' => $comment->likedByUsers()->count()]);

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $comment->likes_count,
        ]);
    }

    private function formatComment(VideoComment $comment)
    {
        return [
            'id' => $comment->id,
            'user' => $comment->user,
            'content' => $comment->content,
            'likes_count' => $comment->likes_count,
            'created_at' => $comment->created_at->diffForHumans(),
            'is_liked' => $comment->isLikedBy(Auth::user()),
        ];
    }
}
