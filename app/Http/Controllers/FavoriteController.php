<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Toggle favorite status of a stadium
     */
    public function toggle(Request $request, Stadium $stadium)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để lưu sân yêu thích',
                'authenticated' => false,
            ], 401);
        }

        $user = auth()->user();

        // Check if already favorited
        $isFavorited = Favorite::where('user_id', $user->id)
            ->where('stadium_id', $stadium->id)
            ->exists();

        if ($isFavorited) {
            // Remove from favorites
            Favorite::where('user_id', $user->id)
                ->where('stadium_id', $stadium->id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa khỏi danh sách yêu thích',
                'favorited' => false,
            ]);
        } else {
            // Add to favorites
            Favorite::create([
                'user_id' => $user->id,
                'stadium_id' => $stadium->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu sân yêu thích',
                'favorited' => true,
            ]);
        }
    }

    /**
     * Check if stadium is favorited by current user
     */
    public function isFavorited(Stadium $stadium)
    {
        if (!auth()->check()) {
            return response()->json([
                'favorited' => false,
                'authenticated' => false,
            ]);
        }

        $isFavorited = Favorite::where('user_id', auth()->id())
            ->where('stadium_id', $stadium->id)
            ->exists();

        return response()->json([
            'favorited' => $isFavorited,
            'authenticated' => true,
        ]);
    }
}
