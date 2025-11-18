<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Stadium;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Store a new review
     */
    public function store(Request $request)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để đánh giá sân',
            ], 401);
        }

        // Get stadium ID from request
        $stadium_id = $request->input('stadium_id');
        $stadium = Stadium::findOrFail($stadium_id);

        // Validate input
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
            'stadium_id' => 'required|exists:stadiums,id',
        ]);

        $user = auth()->user();

        // Check if user already reviewed this stadium
        $existingReview = Review::where('user_id', $user->id)
            ->where('stadium_id', $stadium_id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã đánh giá sân này rồi',
            ], 422);
        }

        // Create review
        $review = Review::create([
            'user_id' => $user->id,
            'stadium_id' => $stadium_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_verified' => true, // Có thể set true nếu xác minh được booking
        ]);

        // Update stadium average rating
        $stadium->updateAverageRating();

        return response()->json([
            'success' => true,
            'message' => 'Đánh giá của bạn đã được gửi thành công!',
            'review' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'user_name' => $user->name,
                'created_at' => $review->created_at->diffForHumans(),
            ],
            'updated_rating' => round($stadium->reviews()->verified()->avg('rating') ?? 0, 1),
            'total_reviews' => $stadium->reviews()->verified()->count(),
        ]);
    }

    /**
     * Update a review
     */
    public function update(Request $request, Review $review)
    {
        // Check if user owns this review
        if ($review->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền cập nhật đánh giá này',
            ], 403);
        }

        // Validate input
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Update review
        $review->update($validated);

        // Update stadium average rating
        $review->stadium->updateAverageRating();

        return response()->json([
            'success' => true,
            'message' => 'Đánh giá của bạn đã được cập nhật thành công!',
            'review' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'updated_at' => $review->updated_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Delete a review
     */
    public function destroy(Review $review)
    {
        // Check if user owns this review
        if ($review->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa đánh giá này',
            ], 403);
        }

        $stadium = $review->stadium;
        $review->delete();

        // Update stadium average rating
        $stadium->updateAverageRating();

        return response()->json([
            'success' => true,
            'message' => 'Đánh giá của bạn đã được xóa thành công',
            'updated_rating' => round($stadium->reviews()->verified()->avg('rating') ?? 0, 1),
            'total_reviews' => $stadium->reviews()->verified()->count(),
        ]);
    }

    /**
     * Get reviews for a stadium (with pagination)
     */
    public function getStadiumReviews(Request $request, Stadium $stadium)
    {
        $filter = $request->input('filter', 'verified'); // all, verified, helpful
        $sortBy = $request->input('sort', 'recent'); // recent, helpful, rating_high, rating_low

        $query = $stadium->reviews()->with('user');

        // Filter
        if ($filter === 'verified') {
            $query->where('is_verified', true);
        }

        // Sort
        switch ($sortBy) {
            case 'helpful':
                $query->orderBy('helpful_count', 'desc')
                    ->orderBy('created_at', 'desc');
                break;
            case 'rating_high':
                $query->orderBy('rating', 'desc')
                    ->orderBy('created_at', 'desc');
                break;
            case 'rating_low':
                $query->orderBy('rating', 'asc')
                    ->orderBy('created_at', 'desc');
                break;
            default: // recent
                $query->orderBy('created_at', 'desc');
        }

        $reviews = $query->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'user' => [
                        'id' => $review->user->id,
                        'name' => $review->user->name,
                        'avatar' => $review->user->avatar ?? null,
                    ],
                    'is_verified' => $review->is_verified,
                    'helpful_count' => $review->helpful_count,
                    'created_at' => $review->created_at,
                    'is_owner' => auth()->check() && $review->user_id === auth()->id(),
                ];
            })->toArray(),
            'pagination' => [
                'total' => $reviews->total(),
                'per_page' => $reviews->perPage(),
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'has_next_page' => $reviews->hasMorePages(),
            ],
        ]);
    }

    /**
     * Get rating summary (count by star)
     */
    public function getRatingSummary(Stadium $stadium)
    {
        $reviews = $stadium->reviews()->verified()->get();

        $distribution = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'total_reviews' => $reviews->count(),
                'average_rating' => round($reviews->avg('rating') ?? 0, 1),
                'distribution' => $distribution,
            ],
        ]);
    }

    /**
     * Mark review as helpful
     */
    public function markHelpful(Review $review)
    {
        // Check if user hasn't already marked as helpful
        // (In production, you'd track this in a separate table)

        $review->helpful_count += 1;
        $review->save();

        return response()->json([
            'success' => true,
            'data' => [
                'helpful_count' => $review->helpful_count,
            ],
        ]);
    }
}
