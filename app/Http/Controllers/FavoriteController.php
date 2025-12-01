<?php

namespace App\Http\Controllers;

use App\Models\Stadium;
use App\Models\Favorite;
use App\Models\Instructor;
use App\Models\InstructorFavorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    /**
     * Toggle favorite status of an instructor
     */
    public function toggleInstructor(Request $request, Instructor $instructor)
    {
        // Debug: Log auth check
        Log::info('Instructor favorite toggle - Auth check', [
            'authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'instructor_id' => $instructor->id
        ]);

        // Check if user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để lưu giảng viên yêu thích',
                'authenticated' => false,
            ], 401);
        }

        $user = auth()->user();

        // Check if already favorited
        $isFavorited = InstructorFavorite::where('user_id', $user->id)
            ->where('instructor_id', $instructor->id)
            ->exists();

        if ($isFavorited) {
            // Remove from favorites
            InstructorFavorite::where('user_id', $user->id)
                ->where('instructor_id', $instructor->id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa khỏi danh sách yêu thích',
                'favorited' => false,
                'authenticated' => true,
            ]);
        } else {
            // Add to favorites
            InstructorFavorite::create([
                'user_id' => $user->id,
                'instructor_id' => $instructor->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu giảng viên yêu thích',
                'favorited' => true,
                'authenticated' => true,
            ]);
        }
    }

    /**
     * Check if instructor is favorited by current user
     */
    public function isInstructorFavorited(Instructor $instructor)
    {
        Log::info('Check instructor favorite', [
            'authenticated' => auth()->check(),
            'user_id' => auth()->id(),
        ]);

        if (!auth()->check()) {
            return response()->json([
                'favorited' => false,
                'authenticated' => false,
                'debug' => 'Not authenticated'
            ]);
        }

        $isFavorited = InstructorFavorite::where('user_id', auth()->id())
            ->where('instructor_id', $instructor->id)
            ->exists();

        return response()->json([
            'favorited' => $isFavorited,
            'authenticated' => true,
            'debug' => 'Authenticated'
        ]);
    }
}
