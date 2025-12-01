<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoLikeController extends Controller
{
    public function toggle(Video $video)
    {
        $user = Auth::user();

        $like = VideoLike::where('video_id', $video->id)
            ->where('user_id', $user->id)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            VideoLike::create([
                'video_id' => $video->id,
                'user_id' => $user->id,
            ]);
            $liked = true;
        }

        $likesCount = $video->likes()->count();

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $likesCount,
        ]);
    }
}
