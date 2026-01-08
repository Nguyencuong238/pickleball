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
